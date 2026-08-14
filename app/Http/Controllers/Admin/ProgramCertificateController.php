<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateProgramCertificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgramCertificateController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable','string','max:150'], 'status' => ['nullable', Rule::in(['pending','generating','ready','failed'])], 'program_id' => ['nullable','integer']]);
        $query = DB::table('program_certificates')->join('programs','programs.id','=','program_certificates.program_id')
            ->select('program_certificates.*','programs.title as program_title');
        if (filled($filters['q'] ?? null)) {
            $q = trim($filters['q']);
            $query->where(fn($builder) => $builder->where('program_certificates.matric_no','like',"%{$q}%")
                ->orWhere('program_certificates.student_name','like',"%{$q}%")
                ->orWhere('program_certificates.serial_no','like',"%{$q}%")
                ->orWhere('programs.title','like',"%{$q}%"));
        }
        if (filled($filters['status'] ?? null)) $query->where('program_certificates.status',$filters['status']);
        if (filled($filters['program_id'] ?? null)) $query->where('program_certificates.program_id',$filters['program_id']);
        return view('admin.programs.certificates', [
            'certificates' => $query->orderByDesc('program_certificates.updated_at')->paginate(30)->withQueryString(),
            'programs' => DB::table('programs')->orderBy('title')->get(['id','title']),
            'filters' => $filters,
            'stats' => DB::table('program_certificates')->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total','status'),
        ]);
    }

    public function generate(Request $request, int $program): RedirectResponse
    {
        $validated = $request->validate([
            'certificate_template' => ['nullable', Rule::in(['standard_placeholder'])],
        ]);
        $templateKey = $validated['certificate_template'] ?? 'standard_placeholder';
        $item = DB::table('programs')->where('id',$program)->first();
        abort_unless($item,404);
        $authId = (int) session('auth_user.id');
        $role = (string) session('auth_user.admin_role');
        abort_unless((int)$item->created_by === $authId || in_array($role,['system_admin','student_affairs_head'],true),403);
        if (! (bool) ($item->certificate_enabled ?? true)) {
            return back()->withErrors(['certificates' => __('This program awards participation points only and does not provide certificates.')]);
        }

        DB::table('programs')->where('id', $program)->update([
            'certificate_template' => $templateKey,
            'updated_at' => now(),
        ]);

        $eligible = DB::table('program_attendances')->join('students','students.id','=','program_attendances.student_id')
            ->where('program_attendances.program_id',$program)->where('program_attendances.attendee_type','internal')
            ->where('program_attendances.validation_status','valid')->whereNotNull('students.matric_no')
            ->select('program_attendances.id as attendance_id','students.id as student_id','students.matric_no','students.full_name')->get();
        if ($eligible->isEmpty()) return back()->withErrors(['certificates'=>__('No eligible internal students with valid attendance were found.')]);

        $queued = [];
        DB::transaction(function () use ($eligible,$item,$authId,$templateKey,&$queued): void {
            foreach ($eligible as $student) {
                $existing = DB::table('program_certificates')->where('program_id',$item->id)->where('student_id',$student->student_id)->first();
                if ($existing && $existing->status === 'ready') continue;
                $id = $existing?->id ?? DB::table('program_certificates')->insertGetId([
                    'program_id'=>$item->id,'program_attendance_id'=>$student->attendance_id,'student_id'=>$student->student_id,
                    'matric_no'=>$student->matric_no,'student_name'=>$student->full_name,
                    'serial_no'=>'SE-'.date('Y').'-'.str_pad((string)$item->id,5,'0',STR_PAD_LEFT).'-'.str_pad((string)$student->student_id,6,'0',STR_PAD_LEFT),
                    'template_key'=>$templateKey,
                    'status'=>'pending','disk'=>'local','generated_by'=>$authId,'created_at'=>now(),'updated_at'=>now(),
                ]);
                if ($existing) DB::table('program_certificates')->where('id',$id)->update(['template_key'=>$templateKey,'status'=>'pending','failure_reason'=>null,'updated_at'=>now()]);
                $queued[]=$id;
            }
        });
        foreach ($queued as $id) GenerateProgramCertificate::dispatch($id)->onQueue('certificates');
        return back()->with('success', trans_choice(':count certificate queued.|:count certificates queued.',count($queued),['count'=>count($queued)]));
    }

    public function download(int $certificate)
    {
        $item = DB::table('program_certificates')->where('id',$certificate)->first();
        abort_unless($item && $item->status === 'ready' && $item->path && Storage::disk($item->disk)->exists($item->path),404);
        return Storage::disk($item->disk)->download($item->path,$item->matric_no.' - Certificate.pdf');
    }
}
