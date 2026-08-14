<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProgramParticipationPointController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $students = DB::table('program_attendances')
            ->join('students', 'students.id', '=', 'program_attendances.student_id')
            ->join('programs', 'programs.id', '=', 'program_attendances.program_id')
            ->where('program_attendances.attendee_type', 'internal')
            ->where('program_attendances.validation_status', 'valid')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subquery) use ($search): void {
                    $subquery->where('students.full_name', 'like', "%{$search}%")
                        ->orWhere('students.matric_no', 'like', "%{$search}%")
                        ->orWhere('students.program', 'like', "%{$search}%");
                });
            })
            ->groupBy('students.id', 'students.full_name', 'students.matric_no', 'students.program')
            ->select([
                'students.id',
                'students.full_name',
                'students.matric_no',
                'students.program',
                DB::raw('COUNT(DISTINCT program_attendances.program_id) as programs_joined'),
                DB::raw('COALESCE(SUM(programs.participation_points), 0) as total_points'),
            ])
            ->orderByDesc('total_points')
            ->orderByDesc('programs_joined')
            ->orderBy('students.full_name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.programs.participation-points', compact('students', 'search'));
    }
}
