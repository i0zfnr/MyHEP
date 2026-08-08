<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaptopController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        $laptops = DB::table('jhep_laptops as laptops')
            ->leftJoin('jhep_laptop_loans as loans', function ($join): void {
                $join->on('loans.laptop_id', '=', 'laptops.id')->whereNull('loans.returned_at');
            })
            ->leftJoin('admins as staff', 'staff.id', '=', 'loans.staff_id')
            ->select('laptops.*', 'loans.borrowed_at', 'staff.full_name as borrower_name')
            ->when($status !== '', fn ($query) => $query->where('laptops.status', $status))
            ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search): void {
                $nested->where('laptops.name', 'like', "%{$search}%")
                    ->orWhere('laptops.asset_code', 'like', "%{$search}%")
                    ->orWhere('staff.full_name', 'like', "%{$search}%");
            }))
            ->orderBy('laptops.asset_code')
            ->get();

        $history = DB::table('jhep_laptop_loans as loans')
            ->join('jhep_laptops as laptops', 'laptops.id', '=', 'loans.laptop_id')
            ->join('admins as staff', 'staff.id', '=', 'loans.staff_id')
            ->select('loans.*', 'laptops.name as laptop_name', 'laptops.asset_code', 'staff.full_name as staff_name')
            ->latest('loans.borrowed_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.laptops.index', compact('laptops', 'history', 'status', 'search'));
    }

    public function scan(Request $request): View
    {
        $staffId = (int) $request->session()->get('auth_user.id');
        $currentLoans = DB::table('jhep_laptop_loans as loans')
            ->join('jhep_laptops as laptops', 'laptops.id', '=', 'loans.laptop_id')
            ->where('loans.staff_id', $staffId)
            ->whereNull('loans.returned_at')
            ->select('loans.borrowed_at', 'laptops.name', 'laptops.asset_code')
            ->orderBy('loans.borrowed_at')
            ->get();

        return view('admin.laptops.scan', [
            'currentLoans' => $currentLoans,
            'initialToken' => $request->string('token')->toString(),
        ]);
    }

    public function processScan(Request $request): JsonResponse
    {
        $validated = $request->validate(['token' => ['required', 'uuid']]);
        $staffId = (int) $request->session()->get('auth_user.id');

        return DB::transaction(function () use ($validated, $staffId): JsonResponse {
            $laptop = DB::table('jhep_laptops')->where('qr_token', $validated['token'])->lockForUpdate()->first();
            if (!$laptop || !$laptop->is_active) {
                return response()->json(['message' => 'This laptop QR code is not active.'], 404);
            }

            $activeLoan = DB::table('jhep_laptop_loans')->where('laptop_id', $laptop->id)->whereNull('returned_at')->lockForUpdate()->first();
            if ($activeLoan && (int) $activeLoan->staff_id !== $staffId) {
                $borrower = DB::table('admins')->where('id', $activeLoan->staff_id)->value('full_name') ?: 'another staff member';

                return response()->json([
                    'message' => "{$laptop->name} is currently borrowed by {$borrower}.",
                    'action' => 'blocked',
                ], 409);
            }

            if ($activeLoan) {
                DB::table('jhep_laptop_loans')->where('id', $activeLoan->id)->update(['returned_at' => now(), 'updated_at' => now()]);
                DB::table('jhep_laptops')->where('id', $laptop->id)->update(['status' => 'available', 'updated_at' => now()]);
                auditLog('laptop.returned', 'jhep_laptop', (int) $laptop->id, "{$laptop->name} returned by staff {$staffId}");

                return response()->json(['message' => "{$laptop->name} returned successfully.", 'action' => 'returned']);
            }

            DB::table('jhep_laptop_loans')->insert([
                'laptop_id' => $laptop->id,
                'staff_id' => $staffId,
                'borrowed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('jhep_laptops')->where('id', $laptop->id)->update(['status' => 'borrowed', 'updated_at' => now()]);
            auditLog('laptop.borrowed', 'jhep_laptop', (int) $laptop->id, "{$laptop->name} borrowed by staff {$staffId}");

            return response()->json(['message' => "{$laptop->name} borrowed successfully.", 'action' => 'borrowed']);
        });
    }
}
