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
    <section class="cert-template-hero cert-template-hero--compact">
        <div>
            <p>{{ __('Certificate Studio') }}</p>
            <h1>{{ __('Create a certificate template') }}</h1>
                <span>{{ __('Upload a blank certificate design. Choose the details to add, then drag each field into position on the preview.') }}</span>
        </div>
        <a class="btn" href="{{ $programId ? route('admin.programs.operations', $programId) : route('admin.programs.index') }}">{{ $programId ? __('Back to Program Operations') : __('Back to Program Management') }}</a>
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
        @if($programId)<input type="hidden" name="program_id" value="{{ $programId }}">@endif
        <section class="cert-editor-panel">
            <div class="cert-editor-title">
                <div>
                    <p>{{ __('New Template') }}</p>
                    <h2>{{ __('Upload certificate PDF') }}</h2>
                </div>
            </div>

            <label class="cert-field">
                <span>{{ __('Blank design (PDF, PNG, or JPG)') }}</span>
                <input id="templatePdfInput" type="file" name="template_pdf" accept="application/pdf,image/png,image/jpeg" required>
            </label>

                <button id="analyzeCertificateTemplate" class="btn cert-ai-btn" type="button" data-analyze-url="{{ route('admin.program-certificate-templates.analyze') }}" hidden>
                {{ __('Find existing Name and IC text (optional)') }}
            </button>
            <div id="certAiStatus" class="cert-ai-status" role="status" aria-live="polite"></div>

            @php
                $fieldDefaults = [
                    'name' => ['key' => 'student_name', 'label' => __('Student Name'), 'prefix' => 'name', 'x' => '74', 'y' => '76', 'w' => '150', 'font' => '18', 'default' => true],
                    'ic' => ['key' => 'ic_no', 'label' => __('IC Number'), 'prefix' => 'ic', 'x' => '74', 'y' => '88', 'w' => '150', 'font' => '14', 'default' => true],
                    'matric' => ['key' => 'matric_no', 'label' => __('Matric Number'), 'prefix' => 'matric', 'x' => '74', 'y' => '100', 'w' => '150', 'font' => '12', 'default' => true],
                    'program' => ['key' => 'program_title', 'label' => __('Program Name'), 'prefix' => 'program', 'x' => '74', 'y' => '112', 'w' => '150', 'font' => '14', 'default' => true],
                    'date' => ['key' => 'program_date', 'label' => __('Program Date'), 'prefix' => 'date', 'x' => '74', 'y' => '124', 'w' => '150', 'font' => '12', 'default' => true],
                    'venue' => ['key' => 'program_venue', 'label' => __('Venue'), 'prefix' => 'venue', 'x' => '74', 'y' => '136', 'w' => '150', 'font' => '12', 'default' => true],
                ];
            @endphp

            @foreach($fieldDefaults as $field)
                <input type="hidden" name="field_settings[{{ $field['key'] }}][x_mm]" data-field-input="{{ $field['prefix'] }}_x" value="{{ old('field_settings.'.$field['key'].'.x_mm', $field['x']) }}">
                <input type="hidden" name="field_settings[{{ $field['key'] }}][y_mm]" data-field-input="{{ $field['prefix'] }}_y" value="{{ old('field_settings.'.$field['key'].'.y_mm', $field['y']) }}">
                <input type="hidden" name="field_settings[{{ $field['key'] }}][width_mm]" data-field-input="{{ $field['prefix'] }}_w" value="{{ old('field_settings.'.$field['key'].'.width_mm', $field['w']) }}">
                <input type="hidden" name="field_settings[{{ $field['key'] }}][font_size]" data-field-input="{{ $field['prefix'] }}_font" value="{{ old('field_settings.'.$field['key'].'.font_size', $field['font']) }}">
                <input type="hidden" name="included_fields[]" value="{{ $field['key'] }}" @if(!$field['default']) disabled @endif data-included-field="{{ $field['key'] }}">
            @endforeach
            @foreach(['name' => ['x' => '74', 'y' => '76', 'w' => '150', 'h' => '10'], 'ic' => ['x' => '74', 'y' => '88', 'w' => '150', 'h' => '8']] as $prefix => $coverDefaults)
                <input type="hidden" name="{{ $prefix }}_cover_x_mm" data-field-input="{{ $prefix }}_cover_x" value="{{ old($prefix.'_cover_x_mm', $coverDefaults['x']) }}">
                <input type="hidden" name="{{ $prefix }}_cover_y_mm" data-field-input="{{ $prefix }}_cover_y" value="{{ old($prefix.'_cover_y_mm', $coverDefaults['y']) }}">
                <input type="hidden" name="{{ $prefix }}_cover_width_mm" data-field-input="{{ $prefix }}_cover_w" value="{{ old($prefix.'_cover_width_mm', $coverDefaults['w']) }}">
                <input type="hidden" name="{{ $prefix }}_cover_height_mm" data-field-input="{{ $prefix }}_cover_h" value="{{ old($prefix.'_cover_height_mm', $coverDefaults['h']) }}">
                <input type="hidden" name="{{ $prefix }}_cover_color" data-field-input="{{ $prefix }}_cover_color" value="{{ old($prefix.'_cover_color', '#f4ebd6') }}">
            @endforeach
            <input type="hidden" name="ai_cleaned" data-ai-cleaned value="{{ old('ai_cleaned', '0') }}">

            <details class="cert-advanced">
                <summary>{{ __('Advanced adjustments') }}</summary>
                <p class="cert-editor-help">{{ __('Choose the fields to print. Drag each field on the preview to position it.') }}</p>
                <label class="cert-field">
                    <span>{{ __('Template name') }}</span>
                    <input name="name" value="{{ old('name') }}" required placeholder="{{ __('Example: Batik Run Participation') }}">
                </label>
                <label class="cert-field">
                    <span>{{ __('PDF page') }}</span>
                    <input type="number" name="source_page" value="{{ old('source_page', 1) }}" min="1" required>
                </label>
                <div class="cert-field-list">
                    @foreach($fieldDefaults as $field)
                        <label class="cert-field-toggle"><input type="checkbox" data-toggle-field="{{ $field['key'] }}" @checked($field['default'])> {{ $field['label'] }}</label>
                    @endforeach
                    <input type="hidden" name="included_fields[]" value="institution_logo" data-included-field="institution_logo">
                    <label class="cert-field-toggle"><input type="checkbox" data-toggle-field="institution_logo" checked> {{ __('Politeknik Besut logo') }}</label>
                    <input type="hidden" name="field_settings[institution_logo][x_mm]" data-field-input="logo_x" value="120">
                    <input type="hidden" name="field_settings[institution_logo][y_mm]" data-field-input="logo_y" value="12">
                    <input type="hidden" name="field_settings[institution_logo][width_mm]" data-field-input="logo_w" value="55">
                    <input type="hidden" name="field_settings[institution_logo][height_mm]" data-field-input="logo_h" value="24">
                </div>
                <div class="cert-controls">
                    @foreach($fieldDefaults as $field)
                        <fieldset class="cert-control-group">
                            <legend>{{ $field['label'] }}</legend>
                            <label>{{ __('Width') }} <input type="number" step=".1" data-field-input="{{ $field['prefix'] }}_w" value="{{ old('field_settings.'.$field['key'].'.width_mm', $field['w']) }}"></label>
                            <label>{{ __('Font') }} <input type="number" data-field-input="{{ $field['prefix'] }}_font" value="{{ old('field_settings.'.$field['key'].'.font_size', $field['font']) }}"></label>
                        </fieldset>
                    @endforeach
                    <fieldset class="cert-control-group"><legend>{{ __('Institution logo') }}</legend><label>{{ __('Width') }} <input type="number" step=".1" data-field-input="logo_w" value="55"></label><label>{{ __('Height') }} <input type="number" step=".1" data-field-input="logo_h" value="24"></label></fieldset>
                </div>
                <div class="cert-cover-settings">
                    <label><input type="checkbox" name="cover_background" value="1" @checked(old('cover_background'))> {{ __('Cover existing placeholder text') }}</label>
                    <div>
                        <input type="number" step=".1" name="cover_x_mm" value="{{ old('cover_x_mm', '83') }}" placeholder="X">
                        <input type="number" step=".1" name="cover_y_mm" value="{{ old('cover_y_mm', '68') }}" placeholder="Y">
                        <input type="number" step=".1" name="cover_width_mm" value="{{ old('cover_width_mm', '131') }}" placeholder="{{ __('Width') }}">
                        <input type="number" step=".1" name="cover_height_mm" value="{{ old('cover_height_mm', '26') }}" placeholder="{{ __('Height') }}">
                        <input name="cover_color" value="{{ old('cover_color', '#f4ebd6') }}" placeholder="#f4ebd6">
                    </div>
                </div>
            </details>

            <button id="saveCertificateTemplate" class="btn btn-primary cert-save-btn" type="submit" disabled>{{ __('Approve & Save Template') }}</button>
        </section>

        <section class="cert-preview-panel">
            <div class="cert-editor-title">
                <div>
                    <p>{{ __('Preview') }}</p>
                    <h2>{{ __('Certificate layout') }}</h2>
                </div>
                <span id="certPreviewStatus">{{ __('Upload a PDF to preview') }}</span>
            </div>

            <div id="certCanvas" class="cert-canvas" data-page-width-mm="297" data-page-height-mm="210">
                <div class="cert-empty-preview">
                    <strong>{{ __('PDF preview will appear here') }}</strong>
                    <span>{{ __('Upload a design-only PDF, select the fields, and drag them into place.') }}</span>
                </div>
                <canvas id="certPdfCanvas" hidden></canvas>
                <span class="cert-clean-cover" data-cover-for="name" aria-hidden="true"></span>
                <span class="cert-clean-cover" data-cover-for="ic" aria-hidden="true"></span>
                @foreach($fieldDefaults as $field)
                    <button type="button" class="cert-drag-field" data-cert-field="{{ $field['key'] }}" data-prefix="{{ $field['prefix'] }}" @if(!$field['default']) hidden @endif>{{ $field['label'] }}</button>
                @endforeach
                <button type="button" class="cert-drag-field cert-drag-field--logo" data-cert-field="institution_logo" data-prefix="logo"><img src="{{ asset('images/logo-politeknik-besut.png') }}" alt="">{{ __('Logo') }}</button>
            </div>
        </section>
    </form>

    <div data-ajax-page-fragment="certificate-templates">
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
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        @php
                            $canManageTemplate = (int) ($template->created_by ?? 0) === (int) session('auth_user.id')
                                || in_array((string) session('auth_user.admin_role'), ['system_admin', 'student_affairs_head'], true);
                        @endphp
                        <tr>
                            <td><strong>{{ $template->name }}</strong><br><small>{{ $template->slug }}</small></td>
                            <td>{{ $template->original_filename }}<br><a href="{{ route('admin.program-certificate-templates.preview', $template->id) }}" target="_blank" rel="noopener">{{ __('Preview clean master') }}</a></td>
                            <td>{{ __('Page :page of :total', ['page' => $template->source_page, 'total' => $template->page_count]) }}</td>
                            <td>{{ $template->creator_name ?: '—' }}</td>
                            <td><span class="badge">{{ $template->is_active ? __('Active') : __('Inactive') }}</span></td>
                            <td>
                                @if($canManageTemplate)
                                    <div class="cert-template-actions">
                                        <details>
                                            <summary class="btn">{{ __('Rename') }}</summary>
                                            <form method="post" action="{{ route('admin.program-certificate-templates.rename', $template->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input name="name" value="{{ $template->name }}" maxlength="120" required aria-label="{{ __('Template name') }}">
                                                <button class="btn btn-primary" type="submit">{{ __('Save') }}</button>
                                            </form>
                                        </details>
                                        <form method="post" action="{{ route('admin.program-certificate-templates.destroy', $template->id) }}" onsubmit="return confirm('{{ __('Delete this certificate template and its private PDF files?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn danger" type="submit">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                @else
                                    <span aria-hidden="true">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">{{ __('No uploaded certificate templates yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $templates->links() }}
    </div>
</main>

<style>
.cert-template-page{max-width:1240px!important}.cert-template-hero--compact{padding:1rem 1.2rem!important}.cert-template-hero--compact h1{font-size:1.25rem}.cert-template-hero--compact span{font-size:.9rem}.cert-editor{grid-template-columns:minmax(320px,380px) minmax(0,1fr)!important}.cert-editor-panel{padding:1rem!important}.cert-advanced{border:1px solid var(--border,#eadac8);border-radius:14px;padding:.8rem;background:color-mix(in srgb,var(--surface,#fff) 90%,transparent)}.cert-advanced summary{font-weight:900;cursor:pointer}.cert-advanced[open] summary{margin-bottom:.75rem}.cert-advanced .cert-controls{margin-top:.75rem}.cert-save-btn{width:100%;justify-content:center}.cert-save-btn:disabled{opacity:.5;cursor:not-allowed}.cert-canvas:not(.has-pdf){aspect-ratio:auto!important;min-height:190px;box-shadow:none!important}.cert-canvas:not(.has-pdf) .cert-drag-field{display:none}.cert-canvas.has-pdf{max-height:560px!important}.cert-preview-panel{padding:1rem!important}
.cert-ai-btn{width:100%;justify-content:center;background:linear-gradient(135deg,#7c58bd,#b99150);color:#fff;border:0}.cert-ai-btn:disabled{opacity:.6;cursor:wait}.cert-ai-status{min-height:1.25rem;font-size:.84rem;font-weight:750;color:var(--text-muted,#746b62)}.cert-ai-status.success{color:#047857}.cert-ai-status.error{color:#b91c1c}.cert-clean-cover{display:none;position:absolute;z-index:2}.cert-canvas.is-detected .cert-clean-cover{display:block}.cert-drag-field{min-width:76px;max-width:150px;transform:translateX(-50%);padding:.25rem .5rem!important;font-size:.7rem!important;line-height:1.1;text-align:center;white-space:nowrap;overflow:hidden}.cert-field-toggle{display:inline-flex;align-items:center;gap:.3rem;border:1px solid var(--border,#eadac8);border-radius:999px;padding:.4rem .65rem;font-size:.78rem;font-weight:750}.cert-field-toggle input{accent-color:var(--accent,#b99150)}.cert-drag-field--logo{min-width:0!important;max-width:none!important;display:grid;place-content:center;gap:.1rem}.cert-drag-field--logo img{max-width:100%;max-height:75%;object-fit:contain}
.cert-table-wrap table{table-layout:fixed;min-width:960px}.cert-table-wrap th:nth-child(1){width:25%}.cert-table-wrap th:nth-child(2){width:20%}.cert-table-wrap th:nth-child(3){width:12%}.cert-table-wrap th:nth-child(4){width:17%}.cert-table-wrap th:nth-child(5){width:10%}.cert-table-wrap th:nth-child(6){width:16%}.cert-table-wrap td{vertical-align:middle;overflow-wrap:anywhere}.cert-template-actions{display:flex;align-items:flex-start;gap:.4rem;flex-wrap:nowrap}.cert-template-actions>form{margin:0}.cert-template-actions .btn{min-height:38px;padding:.5rem .7rem;white-space:nowrap}.cert-template-actions details{flex:0 0 auto}.cert-template-actions summary{list-style:none;cursor:pointer}.cert-template-actions summary::-webkit-details-marker{display:none}.cert-template-actions details[open]{flex:1 1 100%;min-width:0}.cert-template-actions details[open] summary{width:max-content}.cert-template-actions details[open]~form{display:none}.cert-template-actions details form{display:grid;grid-template-columns:minmax(0,1fr);gap:.4rem;width:100%;min-width:0;margin-top:.45rem;padding:.55rem;border:1px solid var(--border,#eadac8);border-radius:12px;background:color-mix(in srgb,var(--surface,#fff) 94%,var(--accent,#b99150) 6%)}.cert-template-actions details form .btn{width:100%}.cert-template-actions details input{min-width:0;width:100%;border:1px solid var(--border,#eadac8);border-radius:9px;padding:.55rem .65rem;color:var(--text-primary,#241d18);background:var(--surface,#fff)}.cert-template-actions .danger{color:#b91c1c;border-color:rgba(185,28,28,.3)}
.cert-template-page{max-width:1500px;margin:0 auto;padding:1.25rem;display:grid;gap:1rem}.cert-template-hero,.cert-editor,.cert-saved{border:1px solid var(--border,#eadac8);background:color-mix(in srgb,var(--surface,#fff) 86%,transparent);box-shadow:0 16px 42px rgba(56,42,27,.08);border-radius:20px}.cert-template-hero{padding:1.35rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem}.cert-template-hero p,.cert-editor-title p{margin:0 0 .25rem;color:var(--accent,#b99150);font-size:.72rem;font-weight:900;letter-spacing:.09em;text-transform:uppercase}.cert-template-hero h1,.cert-editor-title h2{margin:0;color:var(--text-primary,#241d18)}.cert-template-hero span,.cert-editor-help,.cert-saved-head span{display:block;color:var(--text-muted,#746b62);line-height:1.5}.cert-alert{padding:1rem;border-radius:16px;border:1px solid}.cert-alert.success{border-color:rgba(16,185,129,.35);color:#047857;background:rgba(16,185,129,.08)}.cert-alert.error{border-color:rgba(239,68,68,.35);color:#b91c1c;background:rgba(239,68,68,.08)}.cert-alert ul{margin:.5rem 0 0;padding-left:1.2rem}.cert-editor{display:grid;grid-template-columns:minmax(360px,460px) minmax(0,1fr);overflow:hidden}.cert-editor-panel{padding:1.25rem;display:grid;gap:1rem;border-right:1px solid var(--border,#eadac8)}.cert-editor-title{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem}.cert-editor-title.compact{margin-top:.5rem}.cert-editor-title h2{font-size:1.1rem}.cert-field{display:grid;gap:.35rem;font-weight:800;color:var(--text-secondary,#746b62)}.cert-field input,.cert-controls input,.cert-cover-settings input{width:100%;border:1px solid var(--border,#eadac8);border-radius:12px;padding:.7rem .85rem;background:var(--surface,#fff);color:var(--text-primary,#241d18)}.cert-field-list{display:flex;gap:.5rem;flex-wrap:wrap}.cert-field-pill{border:1px solid var(--border,#eadac8);background:var(--surface,#fff);color:var(--text-primary,#241d18);border-radius:999px;padding:.5rem .75rem;font-weight:850;cursor:pointer}.cert-field-pill.active{background:color-mix(in srgb,var(--accent,#b99150) 20%,var(--surface,#fff));border-color:var(--accent,#b99150);color:var(--accent-strong,#8a6024)}.cert-controls{display:grid;gap:.75rem}.cert-control-group{border:1px solid var(--border,#eadac8);border-radius:14px;padding:.75rem;display:grid;grid-template-columns:1fr 1fr;gap:.65rem}.cert-control-group legend{font-weight:900;color:var(--text-primary,#241d18);padding:0 .35rem}.cert-control-group label{font-size:.8rem;font-weight:800;color:var(--text-muted,#746b62)}.cert-cover-settings{border:1px solid var(--border,#eadac8);border-radius:14px;padding:.8rem;background:color-mix(in srgb,var(--accent,#b99150) 6%,transparent)}.cert-cover-settings summary{font-weight:900;cursor:pointer}.cert-cover-settings div{display:grid;grid-template-columns:repeat(5,1fr);gap:.5rem;margin-top:.75rem}.cert-preview-panel{padding:1.25rem;display:grid;grid-template-rows:auto auto;align-content:start;gap:1rem;min-width:0}.cert-preview-panel .cert-editor-title span{border:1px solid var(--border,#eadac8);border-radius:999px;padding:.45rem .75rem;color:var(--text-muted,#746b62);font-size:.8rem;font-weight:800}.cert-canvas{position:relative;aspect-ratio:297/210;width:100%;max-height:78vh;margin:0 auto;border-radius:16px;overflow:hidden;background:#f7efe3;border:1px solid color-mix(in srgb,var(--accent,#b99150) 45%,transparent);box-shadow:0 20px 50px rgba(0,0,0,.18)}.cert-canvas canvas{position:absolute;inset:0;width:100%;height:100%;border:0;background:#f8f1e7;object-fit:contain}.cert-empty-preview{position:absolute;inset:0;display:grid;place-content:center;text-align:center;gap:.35rem;color:#6f6256;padding:2rem}.cert-empty-preview span{color:#8c8175}.cert-drag-field{position:absolute;left:25%;top:36%;transform:translate(-50%,-50%);z-index:3;border:1px solid rgba(185,145,80,.7);background:rgba(255,250,240,.92);color:#241d18;border-radius:999px;padding:.42rem .72rem;font-weight:900;cursor:grab;box-shadow:0 8px 22px rgba(0,0,0,.18);touch-action:none}.cert-drag-field.small{font-size:.78rem}.cert-drag-field.tiny{font-size:.7rem}.cert-drag-field.is-active{outline:3px solid rgba(124,88,189,.35);background:#fff4cc}.cert-saved{padding:0;overflow:hidden}.cert-saved-head{padding:1rem 1.25rem;border-bottom:1px solid var(--border,#eadac8);display:flex;justify-content:space-between;gap:1rem}.cert-saved-head h2{margin:0}.cert-table-wrap{overflow:auto}.cert-table-wrap table{width:100%;border-collapse:collapse}.cert-table-wrap td[colspan]{padding:2rem;text-align:center;color:var(--text-muted,#746b62)}@media(max-width:980px){.cert-editor{grid-template-columns:1fr}.cert-editor-panel{border-right:0;border-bottom:1px solid var(--border,#eadac8)}.cert-template-hero{display:grid}.cert-cover-settings div{grid-template-columns:1fr 1fr}.cert-template-page{padding:.8rem}.cert-canvas{max-height:none}.cert-control-group{grid-template-columns:1fr 1fr}}@media(max-width:560px){.cert-control-group,.cert-cover-settings div{grid-template-columns:1fr}}
</style>
@endsection
