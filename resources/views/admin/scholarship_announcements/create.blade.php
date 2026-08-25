@extends('layouts.app')

@section('title', __('Add Scholarship Announcement'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Add Scholarship Announcement') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(isset($errors) && $errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.scholarship-announcements.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--admin-accent);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                {{ __('Announcement Information') }}
            </h2>
            <div class="body">

                <!-- Title & Type -->
                <div class="grid grid-2">
                    <div>
                        <label for="title">{{ __('Title') }} <span style="color:#b91c1c;">*</span></label>
                        <input id="title" type="text" name="title" value="{{ old('title') }}" placeholder="{{ __('e.g. State Government Scholarship 2026') }}" required>
                    </div>
                    <div>
                        <label for="type">{{ __('Type') }} <span style="color:#b91c1c;">*</span></label>
                        <select id="type" name="type" required>
                            <option value="scholarship" {{ old('type') === 'scholarship' ? 'selected' : '' }}>{{ __('Scholarship') }}</option>
                            <option value="welfare" {{ old('type') === 'welfare' ? 'selected' : '' }}>{{ __('Welfare / Financial Aid') }}</option>
                            <option value="general" {{ old('type', 'general') === 'general' ? 'selected' : '' }}>{{ __('General Announcement') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Body -->
                <div style="margin-top:14px;">
                    <label for="body">{{ __('Description') }} <span style="color:#b91c1c;">*</span></label>
                    <textarea id="body" name="body" rows="7" placeholder="{{ __('Enter detailed announcement information, requirements, application deadlines, and key notes...') }}" required>{{ old('body') }}</textarea>
                </div>

                <div class="section-divider"></div>

                <!-- Poster Image -->
                <div>
                    <label>{{ __('Poster Image') }} <span class="field-optional">({{ __('Optional') }})</span></label>
                    <div class="poster-zone" id="posterZone">
                        <input type="file" id="posterImageInput" name="poster_image" accept="image/jpeg,image/png,image/webp">
                        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="poster-zone__svg">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                        <div class="poster-zone__text">{{ __('Click or drag poster image here') }}</div>
                        <div class="poster-zone__hint">{{ __('Supported formats: JPG, PNG or WEBP · Maximum size: 4MB') }}</div>
                    </div>
                    <div id="posterPreview">
                        <img id="posterPreviewImg" src="" alt="Poster preview">
                        <button type="button" id="posterRemoveBtn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            {{ __('Remove Image') }}
                        </button>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- Contact Info -->
                <div class="grid grid-2">
                    <div>
                        <label for="contact_email">{{ __('Contact Email') }} <span class="field-optional">({{ __('Optional') }})</span></label>
                        <input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="contact@example.edu.my">
                        <p class="field-hint">{{ __('Students can reach out via this email address.') }}</p>
                    </div>
                    <div>
                        <label for="contact_phone">{{ __('Contact Phone Number') }} <span class="field-optional">({{ __('Optional') }})</span></label>
                        <input id="contact_phone" type="tel" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="+6012-3456789">
                        <p class="field-hint">{{ __('Phone or WhatsApp contact number for inquiries.') }}</p>
                    </div>
                </div>

                <div class="section-divider"></div>

                <!-- Link -->
                <div class="grid grid-2">
                    <div>
                        <label for="link_url">{{ __('Link URL') }} <span class="field-optional">({{ __('Optional') }})</span></label>
                        <input id="link_url" type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://scholarship.example.gov.my">
                        <p class="field-hint">{{ __('External website URL for application or more info.') }}</p>
                    </div>
                    <div>
                        <label for="link_label">{{ __('Link Button Label') }} <span class="field-optional">({{ __('Optional') }})</span></label>
                        <input id="link_label" type="text" name="link_label" value="{{ old('link_label') }}" placeholder="{{ __('e.g. Apply Now / Visit Portal') }}">
                        <p class="field-hint">{{ __('Text shown on the action button for students.') }}</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ __('Save Announcement') }}
            </button>
            <a class="btn" href="{{ route('admin.scholarship-announcements.index') }}">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>

<script>
(function () {
    const input    = document.getElementById('posterImageInput');
    const preview  = document.getElementById('posterPreview');
    const img      = document.getElementById('posterPreviewImg');
    const removeBtn= document.getElementById('posterRemoveBtn');
    const zone     = document.getElementById('posterZone');

    input.addEventListener('change', function () {
        if (!this.files || !this.files[0]) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            img.src = e.target.result;
            preview.style.display = 'block';
            zone.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    });

    removeBtn.addEventListener('click', function () {
        input.value = '';
        img.src = '';
        preview.style.display = 'none';
        zone.style.display = 'block';
    });
})();
</script>
@endsection
