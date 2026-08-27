<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateProgramCertificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use setasign\Fpdi\Fpdi;

class ProgramCertificateController extends Controller
{
    private const CERTIFICATE_TEMPLATES = ['standard_placeholder', 'batik_run_participation'];
    private const DYNAMIC_TEMPLATE_KEY = 'uploaded_pdf';

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

    public function templates(): View
    {
        $templates = DB::table('certificate_templates')
            ->leftJoin('admins', 'admins.id', '=', 'certificate_templates.created_by')
            ->select('certificate_templates.*', 'admins.full_name as creator_name')
            ->orderByDesc('certificate_templates.updated_at')
            ->paginate(20);

        return view('admin.programs.certificate_templates', compact('templates'));
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'template_pdf' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'source_page' => ['required', 'integer', 'min:1'],
            'name_x_mm' => ['required', 'numeric', 'min:0', 'max:297'],
            'name_y_mm' => ['required', 'numeric', 'min:0', 'max:210'],
            'name_width_mm' => ['required', 'numeric', 'min:20', 'max:297'],
            'name_font_size' => ['required', 'integer', 'min:8', 'max:72'],
            'matric_x_mm' => ['required', 'numeric', 'min:0', 'max:297'],
            'matric_y_mm' => ['required', 'numeric', 'min:0', 'max:210'],
            'matric_width_mm' => ['required', 'numeric', 'min:20', 'max:297'],
            'matric_font_size' => ['required', 'integer', 'min:8', 'max:72'],
            'program_x_mm' => ['required', 'numeric', 'min:0', 'max:297'],
            'program_y_mm' => ['required', 'numeric', 'min:0', 'max:210'],
            'program_width_mm' => ['required', 'numeric', 'min:20', 'max:297'],
            'program_font_size' => ['required', 'integer', 'min:8', 'max:72'],
            'date_x_mm' => ['required', 'numeric', 'min:0', 'max:297'],
            'date_y_mm' => ['required', 'numeric', 'min:0', 'max:210'],
            'date_width_mm' => ['required', 'numeric', 'min:20', 'max:297'],
            'date_font_size' => ['required', 'integer', 'min:8', 'max:72'],
            'serial_x_mm' => ['required', 'numeric', 'min:0', 'max:297'],
            'serial_y_mm' => ['required', 'numeric', 'min:0', 'max:210'],
            'serial_width_mm' => ['required', 'numeric', 'min:20', 'max:297'],
            'serial_font_size' => ['required', 'integer', 'min:7', 'max:72'],
            'cover_background' => ['nullable', 'boolean'],
            'cover_x_mm' => ['nullable', 'numeric', 'min:0', 'max:297'],
            'cover_y_mm' => ['nullable', 'numeric', 'min:0', 'max:210'],
            'cover_width_mm' => ['nullable', 'numeric', 'min:1', 'max:297'],
            'cover_height_mm' => ['nullable', 'numeric', 'min:1', 'max:210'],
            'cover_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $file = $request->file('template_pdf');
        $slugBase = Str::slug($validated['name']) ?: 'certificate-template';
        $slug = $slugBase;
        $suffix = 2;
        while (DB::table('certificate_templates')->where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.$suffix++;
        }

        $path = $file->storeAs(
            'certificate-templates',
            $slug.'-'.now()->format('YmdHis').'.pdf',
            'local'
        );

        try {
            $templateInfo = $this->inspectUploadedTemplate(Storage::disk('local')->path($path), (int) $validated['source_page']);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);

            return back()->withInput()->withErrors([
                'template_pdf' => __('This PDF cannot be used as a certificate template. Please upload a normal, unlocked PDF exported for printing. Error: :message', [
                    'message' => mb_substr($exception->getMessage(), 0, 220),
                ]),
            ]);
        }

        $pageWidth = (float) $templateInfo['width'];
        $pageHeight = (float) $templateInfo['height'];
        $this->validateFieldBounds($validated, $pageWidth, $pageHeight);

        $templateId = DB::transaction(function () use ($validated, $file, $path, $slug, $templateInfo): int {
            $templateId = DB::table('certificate_templates')->insertGetId([
                'name' => $validated['name'],
                'slug' => $slug,
                'disk' => 'local',
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'page_count' => $templateInfo['page_count'],
                'source_page' => (int) $validated['source_page'],
                'page_width_mm' => $templateInfo['width'],
                'page_height_mm' => $templateInfo['height'],
                'is_active' => true,
                'created_by' => (int) session('auth_user.id') ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $coverBackground = (bool) ($validated['cover_background'] ?? false);
            $coverColor = $validated['cover_color'] ?? '#f4ebd6';
            $cover = [
                'x' => (float) ($validated['cover_x_mm'] ?? 0),
                'y' => (float) ($validated['cover_y_mm'] ?? 0),
                'w' => (float) ($validated['cover_width_mm'] ?? 1),
                'h' => (float) ($validated['cover_height_mm'] ?? 1),
            ];

            $fields = [
                [
                    'field_key' => 'student_name',
                    'label' => 'Student Name',
                    'x_mm' => $validated['name_x_mm'],
                    'y_mm' => $validated['name_y_mm'],
                    'width_mm' => $validated['name_width_mm'],
                    'height_mm' => 9,
                    'font_size' => $validated['name_font_size'],
                    'font_weight' => 'bold',
                    'cover_background' => $coverBackground,
                ],
                [
                    'field_key' => 'matric_no',
                    'label' => 'Matric Number',
                    'x_mm' => $validated['matric_x_mm'],
                    'y_mm' => $validated['matric_y_mm'],
                    'width_mm' => $validated['matric_width_mm'],
                    'height_mm' => 7,
                    'font_size' => $validated['matric_font_size'],
                    'font_weight' => 'regular',
                    'cover_background' => false,
                ],
                [
                    'field_key' => 'program_title',
                    'label' => 'Program Name',
                    'x_mm' => $validated['program_x_mm'],
                    'y_mm' => $validated['program_y_mm'],
                    'width_mm' => $validated['program_width_mm'],
                    'height_mm' => 8,
                    'font_size' => $validated['program_font_size'],
                    'font_weight' => 'bold',
                    'cover_background' => false,
                ],
                [
                    'field_key' => 'program_date',
                    'label' => 'Program Date',
                    'x_mm' => $validated['date_x_mm'],
                    'y_mm' => $validated['date_y_mm'],
                    'width_mm' => $validated['date_width_mm'],
                    'height_mm' => 7,
                    'font_size' => $validated['date_font_size'],
                    'font_weight' => 'bold',
                    'cover_background' => false,
                ],
                [
                    'field_key' => 'serial_no',
                    'label' => 'Certificate Serial',
                    'x_mm' => $validated['serial_x_mm'],
                    'y_mm' => $validated['serial_y_mm'],
                    'width_mm' => $validated['serial_width_mm'],
                    'height_mm' => 5,
                    'font_size' => $validated['serial_font_size'],
                    'font_weight' => 'regular',
                    'cover_background' => false,
                ],
            ];

            foreach ($fields as $field) {
                DB::table('certificate_template_fields')->insert([
                    'certificate_template_id' => $templateId,
                    'field_key' => $field['field_key'],
                    'label' => $field['label'],
                    'page_number' => (int) $validated['source_page'],
                    'x_mm' => $field['x_mm'],
                    'y_mm' => $field['y_mm'],
                    'width_mm' => $field['width_mm'],
                    'height_mm' => $field['height_mm'],
                    'font_size' => $field['font_size'],
                    'font_weight' => $field['font_weight'],
                    'text_color' => '#1f1a16',
                    'alignment' => 'C',
                    'cover_background' => $field['cover_background'],
                    'cover_color' => $field['cover_background'] ? $coverColor : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($coverBackground) {
                DB::table('certificate_template_fields')->insert([
                    'certificate_template_id' => $templateId,
                    'field_key' => 'background_cover',
                    'label' => 'Placeholder Cover',
                    'page_number' => (int) $validated['source_page'],
                    'x_mm' => $cover['x'],
                    'y_mm' => $cover['y'],
                    'width_mm' => $cover['w'],
                    'height_mm' => $cover['h'],
                    'font_size' => 1,
                    'font_weight' => 'regular',
                    'text_color' => '#1f1a16',
                    'alignment' => 'C',
                    'cover_background' => true,
                    'cover_color' => $coverColor,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $templateId;
        });

        return redirect()->route('admin.program-certificate-templates.index')
            ->with('success', __('Certificate template uploaded. You can now select it in Program Operations.'));
    }

    public function previewTemplate(int $template)
    {
        $item = DB::table('certificate_templates')->where('id', $template)->where('is_active', true)->first();
        abort_unless($item, 404);
        abort_unless(Storage::disk($item->disk ?: 'local')->exists($item->file_path), 404);

        return response()->file(Storage::disk($item->disk ?: 'local')->path($item->file_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.addslashes($item->original_filename ?: 'certificate-template.pdf').'"',
        ]);
    }

    public function generate(Request $request, int $program): RedirectResponse
    {
        $validated = $request->validate([
            'certificate_template' => ['nullable', Rule::in(self::CERTIFICATE_TEMPLATES)],
            'certificate_template_id' => ['nullable', 'integer', Rule::exists('certificate_templates', 'id')->where('is_active', true)],
        ]);
        [$templateKey, $templateId] = $this->selectedTemplate($validated);
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
            'certificate_template_id' => $templateId,
            'updated_at' => now(),
        ]);

        $eligible = DB::table('program_attendances')->join('students','students.id','=','program_attendances.student_id')
            ->where('program_attendances.program_id',$program)->where('program_attendances.attendee_type','internal')
            ->where('program_attendances.validation_status','valid')->whereNotNull('students.matric_no')
            ->select('program_attendances.id as attendance_id','students.id as student_id','students.matric_no','students.full_name')->get();
        if ($eligible->isEmpty()) return back()->withErrors(['certificates'=>__('No eligible internal students with valid attendance were found.')]);

        $queued = [];
        DB::transaction(function () use ($eligible,$item,$authId,$templateKey,$templateId,&$queued): void {
            foreach ($eligible as $student) {
                $existing = DB::table('program_certificates')->where('program_id',$item->id)->where('student_id',$student->student_id)->first();
                if ($existing && $existing->status === 'ready') continue;
                $id = $existing?->id ?? DB::table('program_certificates')->insertGetId([
                    'program_id'=>$item->id,'program_attendance_id'=>$student->attendance_id,'student_id'=>$student->student_id,
                    'matric_no'=>$student->matric_no,'student_name'=>$student->full_name,
                    'serial_no'=>'SE-'.date('Y').'-'.str_pad((string)$item->id,5,'0',STR_PAD_LEFT).'-'.str_pad((string)$student->student_id,6,'0',STR_PAD_LEFT),
                    'template_key'=>$templateKey,
                    'certificate_template_id'=>$templateId,
                    'status'=>'pending','disk'=>'local','generated_by'=>$authId,'created_at'=>now(),'updated_at'=>now(),
                ]);
                if ($existing) DB::table('program_certificates')->where('id',$id)->update(['template_key'=>$templateKey,'certificate_template_id'=>$templateId,'status'=>'pending','failure_reason'=>null,'updated_at'=>now()]);
                $queued[]=$id;
            }
        });
        $generated = 0;
        $failed = 0;
        foreach ($queued as $id) {
            try {
                (new GenerateProgramCertificate($id))->handle();
                $generated++;
            } catch (\Throwable $exception) {
                (new GenerateProgramCertificate($id))->failed($exception);
                report($exception);
                $failed++;
            }
        }

        if ($failed > 0) {
            return back()->withErrors(['certificates' => __(':generated certificates generated. :failed certificates failed. Please check the failed records.', ['generated' => $generated, 'failed' => $failed])]);
        }

        return back()->with('success', trans_choice(':count certificate generated and ready for students.|:count certificates generated and ready for students.', $generated, ['count' => $generated]));
    }

    public function destroyForProgram(int $program): RedirectResponse
    {
        $item = DB::table('programs')->where('id', $program)->first();
        abort_unless($item, 404);

        $authId = (int) session('auth_user.id');
        $role = (string) session('auth_user.admin_role');
        abort_unless((int) $item->created_by === $authId || in_array($role, ['system_admin', 'student_affairs_head'], true), 403);

        $certificates = DB::table('program_certificates')
            ->where('program_id', $program)
            ->get(['id', 'disk', 'path']);

        foreach ($certificates as $certificate) {
            if ($certificate->path) {
                Storage::disk($certificate->disk ?: 'local')->delete($certificate->path);
            }
        }

        DB::table('program_certificates')->where('program_id', $program)->delete();

        return back()->with('success', trans_choice(
            ':count generated certificate deleted.|:count generated certificates deleted.',
            $certificates->count(),
            ['count' => $certificates->count()]
        ));
    }

    public function generateTest(Request $request, int $program): RedirectResponse
    {
        $validated = $request->validate([
            'certificate_template' => ['nullable', Rule::in(self::CERTIFICATE_TEMPLATES)],
            'certificate_template_id' => ['nullable', 'integer', Rule::exists('certificate_templates', 'id')->where('is_active', true)],
        ]);
        [$templateKey, $templateId] = $this->selectedTemplate($validated);
        $item = DB::table('programs')->where('id', $program)->first();
        abort_unless($item, 404);

        $authId = (int) session('auth_user.id');
        $role = (string) session('auth_user.admin_role');
        abort_unless((int) $item->created_by === $authId || in_array($role, ['system_admin', 'student_affairs_head'], true), 403);
        if (! (bool) ($item->certificate_enabled ?? true)) {
            return back()->withErrors(['certificates' => __('This program awards participation points only and does not provide certificates.')]);
        }

        $student = DB::table('program_attendances')
            ->join('students', 'students.id', '=', 'program_attendances.student_id')
            ->where('program_attendances.program_id', $program)
            ->where('program_attendances.attendee_type', 'internal')
            ->where('program_attendances.validation_status', 'valid')
            ->whereNotNull('students.matric_no')
            ->select('program_attendances.id as attendance_id', 'students.id as student_id', 'students.matric_no', 'students.full_name')
            ->orderBy('program_attendances.id')
            ->first();
        if (! $student) {
            return back()->withErrors(['certificates' => __('No eligible internal students with valid attendance were found.')]);
        }

        DB::table('programs')->where('id', $program)->update(['certificate_template' => $templateKey, 'certificate_template_id' => $templateId, 'updated_at' => now()]);
        $existing = DB::table('program_certificates')->where('program_id', $program)->where('student_id', $student->student_id)->first();
        $certificateId = $existing?->id ?? DB::table('program_certificates')->insertGetId([
            'program_id' => $program, 'program_attendance_id' => $student->attendance_id, 'student_id' => $student->student_id,
            'matric_no' => $student->matric_no, 'student_name' => $student->full_name,
            'serial_no' => 'SE-'.date('Y').'-'.str_pad((string) $program, 5, '0', STR_PAD_LEFT).'-'.str_pad((string) $student->student_id, 6, '0', STR_PAD_LEFT),
            'template_key' => $templateKey, 'certificate_template_id' => $templateId, 'status' => 'pending', 'disk' => 'local', 'generated_by' => $authId,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        if ($existing) {
            if ($existing->path) Storage::disk($existing->disk ?: 'local')->delete($existing->path);
            DB::table('program_certificates')->where('id', $certificateId)->update([
                'program_attendance_id' => $student->attendance_id, 'matric_no' => $student->matric_no,
                'student_name' => $student->full_name, 'template_key' => $templateKey, 'status' => 'pending',
                'certificate_template_id' => $templateId, 'path' => null, 'failure_reason' => null, 'generated_by' => $authId, 'generated_at' => null, 'updated_at' => now(),
            ]);
        }

        try {
            (new GenerateProgramCertificate($certificateId))->handle();
        } catch (\Throwable $exception) {
            (new GenerateProgramCertificate($certificateId))->failed($exception);
            report($exception);

            return back()->withErrors(['certificates' => __('The test certificate could not be generated. Please check the application log and try again.')]);
        }

        return redirect()->route('admin.program-certificates.index', ['program_id' => $program, 'q' => $student->matric_no])
            ->with('success', __('Test certificate generated successfully and is ready to download.'));
    }

    public function download(int $certificate)
    {
        $item = DB::table('program_certificates')->where('id',$certificate)->first();
        abort_unless($item && $item->status === 'ready' && $item->path && Storage::disk($item->disk)->exists($item->path),404);
        return Storage::disk($item->disk)->download($item->path,$item->matric_no.' - Certificate.pdf');
    }

    private function selectedTemplate(array $validated): array
    {
        if (! empty($validated['certificate_template_id'])) {
            return [self::DYNAMIC_TEMPLATE_KEY, (int) $validated['certificate_template_id']];
        }

        return [$validated['certificate_template'] ?? 'standard_placeholder', null];
    }

    private function inspectUploadedTemplate(string $path, int $sourcePage): array
    {
        $previousReporting = error_reporting(error_reporting() & ~E_DEPRECATED);

        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($path);

            if ($sourcePage > $pageCount) {
                throw new \RuntimeException(__('Selected page :page is higher than the PDF page count :count.', [
                    'page' => $sourcePage,
                    'count' => $pageCount,
                ]));
            }

            $page = $pdf->importPage($sourcePage);
            $size = $pdf->getTemplateSize($page);

            if (empty($size['width']) || empty($size['height'])) {
                throw new \RuntimeException('Could not read the PDF page size.');
            }

            return [
                'page_count' => $pageCount,
                'width' => round((float) $size['width'], 2),
                'height' => round((float) $size['height'], 2),
            ];
        } finally {
            error_reporting($previousReporting);
        }
    }

    private function validateFieldBounds(array $validated, float $pageWidth, float $pageHeight): void
    {
        $fields = [
            'name' => __('Student Name'),
            'matric' => __('Matric Number'),
            'program' => __('Program Name'),
            'date' => __('Program Date'),
            'serial' => __('Certificate Serial'),
        ];

        $errors = [];
        foreach ($fields as $prefix => $label) {
            $x = (float) $validated[$prefix.'_x_mm'];
            $y = (float) $validated[$prefix.'_y_mm'];
            $width = (float) $validated[$prefix.'_width_mm'];

            if ($x > $pageWidth || $y > $pageHeight || ($x + $width) > ($pageWidth + 1)) {
                $errors[$prefix.'_x_mm'] = __(':field is outside the PDF page area. Move it inside the template preview.', ['field' => $label]);
            }
        }

        if (! empty($validated['cover_background'])) {
            $coverX = (float) ($validated['cover_x_mm'] ?? 0);
            $coverY = (float) ($validated['cover_y_mm'] ?? 0);
            $coverWidth = (float) ($validated['cover_width_mm'] ?? 0);
            $coverHeight = (float) ($validated['cover_height_mm'] ?? 0);
            if ($coverX > $pageWidth || $coverY > $pageHeight || ($coverX + $coverWidth) > ($pageWidth + 1) || ($coverY + $coverHeight) > ($pageHeight + 1)) {
                $errors['cover_x_mm'] = __('The optional cover area is outside the PDF page area.');
            }
        }

        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }
}
