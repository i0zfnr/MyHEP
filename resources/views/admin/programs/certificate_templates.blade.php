@extends('layouts.app')

@section('title', __('Certificate Templates'))
@section('header')
    <h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Certificate Templates') }}</h2>
@endsection

@push('scripts')
    @vite('resources/js/certificate-template-editor.js')
@endpush

@section('content')
<main class="cert-template-page">
    <section class="cert-template-hero">
        <div>
            <p>{{ __('Certificate Studio') }}</p>
            <h1>{{ __('Upload, position fields, then save as reusable template') }}</h1>
            <span>{{ __('Upload the final PDF design. Drag the field labels on the preview, save the template, then use it in Program Operations to generate certificates for all eligible students.') }}</span>
        </div>
        <a class="btn" href="{{ route('admin.programs.index') }}">{{ __('Back to Program Management') }}</a>
    </section>

    @if(session('success'))
        <section class="cert-alert success">{{ session('success') }}</section>
    @endif

    @if($errors->any())
        <section class="cert-alert error">
            <strong>{{ __('Please fix the template details.') }}</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <form id="certificateTemplateEditor" method="post" action="{{ route('admin.program-certificate-templates.store') }}" enctype="multipart/form-data" class="cert-editor">
        @csrf
        <section class="cert-editor-panel">
            <div class="cert-editor-title">
                <div>
                    <p>{{ __('Step 1') }}</p>
                    <h2>{{ __('Template details') }}</h2>
                </div>
            </div>

            <label class="cert-field">
                <span>{{ __('Template name') }}</span>
                <input name="name" value="{{ old('name') }}" required placeholder="{{ __('Example: Batik Run Participation') }}">
            </label>

            <label class="cert-field">
                <span>{{ __('Template PDF') }}</span>
                <input id="templatePdfInput" type="file" name="template_pdf" accept="application/pdf" required>
            </label>

            <label class="cert-field">
                <span>{{ __('Source page') }}</span>
                <input type="number" name="source_page" value="{{ old('source_page', 1) }}" min="1" required>
            </label>

            <div class="cert-editor-title compact">
                <div>
                    <p>{{ __('Step 2') }}</p>
                    <h2>{{ __('Drag fields on preview') }}</h2>
                </div>
            </div>

            <div class="cert-field-list">
                <button type="button" class="cert-field-pill active" data-focus-field="student_name">{{ __('Student Name') }}</button>
                <button type="button" class="cert-field-pill" data-focus-field="matric_no">{{ __('Matric Number') }}</button>
                <button type="button" class="cert-field-pill" data-focus-field="program_title">{{ __('Program Name') }}</button>
                <button type="button" class="cert-field-pill" data-focus-field="program_date">{{ __('Program Date') }}</button>
                <button type="button" class="cert-field-pill" data-focus-field="serial_no">{{ __('Certificate Serial') }}</button>
            </div>

            <p class="cert-editor-help">{{ __('Drag a field on the preview. The X/Y position is saved automatically. Use width/font controls only when the generated text needs tuning.') }}</p>

            @php
                $fieldDefaults = [
                    'name' => ['label' => __('Student Name'), 'prefix' => 'name', 'x' => '74', 'y' => '76', 'w' => '150', 'font' => '14'],
                    'matric' => ['label' => __('Matric Number'), 'prefix' => 'matric', 'x' => '74', 'y' => '88', 'w' => '150', 'font' => '10'],
                    'program' => ['label' => __('Program Name'), 'prefix' => 'program', 'x' => '74', 'y' => '106', 'w' => '150', 'font' => '13'],
                    'date' => ['label' => __('Program Date'), 'prefix' => 'date', 'x' => '74', 'y' => '121', 'w' => '150', 'font' => '12'],
                    'serial' => ['label' => __('Certificate Serial'), 'prefix' => 'serial', 'x' => '12', 'y' => '195', 'w' => '90', 'font' => '8'],
                ];
            @endphp

            <div class="cert-controls">
                @foreach($fieldDefaults as $key => $field)
                    <fieldset class="cert-control-group">
                        <legend>{{ $field['label'] }}</legend>
                        <input type="hidden" name="{{ $field['prefix'] }}_x_mm" data-field-input="{{ $field['prefix'] }}_x" value="{{ old($field['prefix'].'_x_mm', $field['x']) }}">
                        <input type="hidden" name="{{ $field['prefix'] }}_y_mm" data-field-input="{{ $field['prefix'] }}_y" value="{{ old($field['prefix'].'_y_mm', $field['y']) }}">
                        <label>{{ __('Width') }} <input type="number" step=".1" name="{{ $field['prefix'] }}_width_mm" data-field-input="{{ $field['prefix'] }}_w" value="{{ old($field['prefix'].'_width_mm', $field['w']) }}" required></label>
                        <label>{{ __('Font') }} <input type="number" name="{{ $field['prefix'] }}_font_size" data-field-input="{{ $field['prefix'] }}_font" value="{{ old($field['prefix'].'_font_size', $field['font']) }}" required></label>
                    </fieldset>
                @endforeach
            </div>

            <details class="cert-cover-settings">
                <summary>{{ __('Optional: cover existing placeholder text') }}</summary>
                <label><input type="checkbox" name="cover_background" value="1" @checked(old('cover_background'))> {{ __('Add cover area before writing student name') }}</label>
                <div>
                    <input type="number" step=".1" name="cover_x_mm" value="{{ old('cover_x_mm', '83') }}" placeholder="X">
                    <input type="number" step=".1" name="cover_y_mm" value="{{ old('cover_y_mm', '68') }}" placeholder="Y">
                    <input type="number" step=".1" name="cover_width_mm" value="{{ old('cover_width_mm', '131') }}" placeholder="{{ __('Width') }}">
                    <input type="number" step=".1" name="cover_height_mm" value="{{ old('cover_height_mm', '26') }}" placeholder="{{ __('Height') }}">
                    <input name="cover_color" value="{{ old('cover_color', '#f4ebd6') }}" placeholder="#f4ebd6">
                </div>
            </details>

            <button class="btn btn-primary" type="submit">{{ __('Save Template') }}</button>
        </section>

        <section class="cert-preview-panel">
            <div class="cert-editor-title">
                <div>
                    <p>{{ __('Live Preview') }}</p>
                    <h2>{{ __('Move fields on the certificate') }}</h2>
                </div>
                <span id="certPreviewStatus">{{ __('Upload a PDF to preview') }}</span>
            </div>

            <div id="certCanvas" class="cert-canvas" data-page-width-mm="297" data-page-height-mm="210">
                <div class="cert-empty-preview">
                    <strong>{{ __('PDF preview will appear here') }}</strong>
                    <span>{{ __('After choosing a PDF, drag the field badges to the correct positions.') }}</span>
                </div>
                <canvas id="certPdfCanvas" hidden></canvas>
                <button type="button" class="cert-drag-field" data-cert-field="student_name" data-prefix="name">{{ __('Student Name') }}</button>
                <button type="button" class="cert-drag-field small" data-cert-field="matric_no" data-prefix="matric">{{ __('Matric Number') }}</button>
                <button type="button" class="cert-drag-field" data-cert-field="program_title" data-prefix="program">{{ __('Program Name') }}</button>
                <button type="button" class="cert-drag-field small" data-cert-field="program_date" data-prefix="date">{{ __('Program Date') }}</button>
                <button type="button" class="cert-drag-field tiny" data-cert-field="serial_no" data-prefix="serial">{{ __('Certificate Serial') }}</button>
            </div>
        </section>
    </form>

    <section class="cert-saved card">
        <div class="cert-saved-head">
            <h2>{{ __('Saved Templates') }}</h2>
            <span>{{ __('Ready to use in Program Operations') }}</span>
        </div>
        <div class="cert-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Template') }}</th>
                        <th>{{ __('PDF') }}</th>
                        <th>{{ __('Pages') }}</th>
                        <th>{{ __('Created by') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td><strong>{{ $template->name }}</strong><br><small>{{ $template->slug }}</small></td>
                            <td>{{ $template->original_filename }}</td>
                            <td>{{ __('Page :page of :total', ['page' => $template->source_page, 'total' => $template->page_count]) }}</td>
                            <td>{{ $template->creator_name ?: '—' }}</td>
                            <td><span class="badge">{{ $template->is_active ? __('Active') : __('Inactive') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">{{ __('No uploaded certificate templates yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $templates->links() }}
</main>

<style>
.cert-template-page{max-width:1500px;margin:0 auto;padding:1.25rem;display:grid;gap:1rem}.cert-template-hero,.cert-editor,.cert-saved{border:1px solid var(--border,#eadac8);background:color-mix(in srgb,var(--surface,#fff) 86%,transparent);box-shadow:0 16px 42px rgba(56,42,27,.08);border-radius:20px}.cert-template-hero{padding:1.35rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem}.cert-template-hero p,.cert-editor-title p{margin:0 0 .25rem;color:var(--accent,#b99150);font-size:.72rem;font-weight:900;letter-spacing:.09em;text-transform:uppercase}.cert-template-hero h1,.cert-editor-title h2{margin:0;color:var(--text-primary,#241d18)}.cert-template-hero span,.cert-editor-help,.cert-saved-head span{display:block;color:var(--text-muted,#746b62);line-height:1.5}.cert-alert{padding:1rem;border-radius:16px;border:1px solid}.cert-alert.success{border-color:rgba(16,185,129,.35);color:#047857;background:rgba(16,185,129,.08)}.cert-alert.error{border-color:rgba(239,68,68,.35);color:#b91c1c;background:rgba(239,68,68,.08)}.cert-alert ul{margin:.5rem 0 0;padding-left:1.2rem}.cert-editor{display:grid;grid-template-columns:minmax(360px,460px) minmax(0,1fr);overflow:hidden}.cert-editor-panel{padding:1.25rem;display:grid;gap:1rem;border-right:1px solid var(--border,#eadac8)}.cert-editor-title{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem}.cert-editor-title.compact{margin-top:.5rem}.cert-editor-title h2{font-size:1.1rem}.cert-field{display:grid;gap:.35rem;font-weight:800;color:var(--text-secondary,#746b62)}.cert-field input,.cert-controls input,.cert-cover-settings input{width:100%;border:1px solid var(--border,#eadac8);border-radius:12px;padding:.7rem .85rem;background:var(--surface,#fff);color:var(--text-primary,#241d18)}.cert-field-list{display:flex;gap:.5rem;flex-wrap:wrap}.cert-field-pill{border:1px solid var(--border,#eadac8);background:var(--surface,#fff);color:var(--text-primary,#241d18);border-radius:999px;padding:.5rem .75rem;font-weight:850;cursor:pointer}.cert-field-pill.active{background:color-mix(in srgb,var(--accent,#b99150) 20%,var(--surface,#fff));border-color:var(--accent,#b99150);color:var(--accent-strong,#8a6024)}.cert-controls{display:grid;gap:.75rem}.cert-control-group{border:1px solid var(--border,#eadac8);border-radius:14px;padding:.75rem;display:grid;grid-template-columns:1fr 1fr;gap:.65rem}.cert-control-group legend{font-weight:900;color:var(--text-primary,#241d18);padding:0 .35rem}.cert-control-group label{font-size:.8rem;font-weight:800;color:var(--text-muted,#746b62)}.cert-cover-settings{border:1px solid var(--border,#eadac8);border-radius:14px;padding:.8rem;background:color-mix(in srgb,var(--accent,#b99150) 6%,transparent)}.cert-cover-settings summary{font-weight:900;cursor:pointer}.cert-cover-settings div{display:grid;grid-template-columns:repeat(5,1fr);gap:.5rem;margin-top:.75rem}.cert-preview-panel{padding:1.25rem;display:grid;grid-template-rows:auto auto;align-content:start;gap:1rem;min-width:0}.cert-preview-panel .cert-editor-title span{border:1px solid var(--border,#eadac8);border-radius:999px;padding:.45rem .75rem;color:var(--text-muted,#746b62);font-size:.8rem;font-weight:800}.cert-canvas{position:relative;aspect-ratio:297/210;width:100%;max-height:78vh;margin:0 auto;border-radius:16px;overflow:hidden;background:#f7efe3;border:1px solid color-mix(in srgb,var(--accent,#b99150) 45%,transparent);box-shadow:0 20px 50px rgba(0,0,0,.18)}.cert-canvas canvas{position:absolute;inset:0;width:100%;height:100%;border:0;background:#f8f1e7;object-fit:contain}.cert-empty-preview{position:absolute;inset:0;display:grid;place-content:center;text-align:center;gap:.35rem;color:#6f6256;padding:2rem}.cert-empty-preview span{color:#8c8175}.cert-drag-field{position:absolute;left:25%;top:36%;transform:translate(-50%,-50%);z-index:3;border:1px solid rgba(185,145,80,.7);background:rgba(255,250,240,.92);color:#241d18;border-radius:999px;padding:.42rem .72rem;font-weight:900;cursor:grab;box-shadow:0 8px 22px rgba(0,0,0,.18);touch-action:none}.cert-drag-field.small{font-size:.78rem}.cert-drag-field.tiny{font-size:.7rem}.cert-drag-field.is-active{outline:3px solid rgba(124,88,189,.35);background:#fff4cc}.cert-saved{padding:0;overflow:hidden}.cert-saved-head{padding:1rem 1.25rem;border-bottom:1px solid var(--border,#eadac8);display:flex;justify-content:space-between;gap:1rem}.cert-saved-head h2{margin:0}.cert-table-wrap{overflow:auto}.cert-table-wrap table{width:100%;border-collapse:collapse}.cert-table-wrap td[colspan]{padding:2rem;text-align:center;color:var(--text-muted,#746b62)}@media(max-width:980px){.cert-editor{grid-template-columns:1fr}.cert-editor-panel{border-right:0;border-bottom:1px solid var(--border,#eadac8)}.cert-template-hero{display:grid}.cert-cover-settings div{grid-template-columns:1fr 1fr}.cert-template-page{padding:.8rem}.cert-canvas{max-height:none}.cert-control-group{grid-template-columns:1fr 1fr}}@media(max-width:560px){.cert-control-group,.cert-cover-settings div{grid-template-columns:1fr}}
</style>
@endsection
