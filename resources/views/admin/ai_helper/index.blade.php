@php
    $studentAiMode = $studentAiMode ?? false;
    $lecturerAiMode = $lecturerAiMode ?? false;
    $textOnlyAiMode = $studentAiMode || $lecturerAiMode;
    $canUploadAiFiles = ! $studentAiMode;
    $aiPageTitle = $studentAiMode ? __('AI Helper (Student)') : ($lecturerAiMode ? __('AI Helper (Staff)') : __('AI Helper (Admin)'));
@endphp
@extends('layouts.app')

@section('title', $aiPageTitle)



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--text);">{{ $aiPageTitle }}</h2>
@endsection

@section('content')
<div class="ai-admin {{ $studentAiMode ? 'ai-admin--student' : ($lecturerAiMode ? 'ai-admin--lecturer' : 'ai-admin--admin') }}"
     data-ai-url="{{ route($studentAiMode ? 'student.ai-helper.ask' : ($lecturerAiMode ? 'lecturer.ai-helper.ask' : 'admin.ai-helper.ask')) }}"
     data-ai-enabled="{{ $aiEnabled ? '1' : '0' }}"
     data-ai-provider="{{ $aiProvider }}"
     data-ai-model="{{ $aiModel }}"
     data-can-edit-ai="{{ $studentAiMode ? '0' : '1' }}"
     data-ai-session-key="myhep.ai.active.{{ $studentAiMode ? 'student' : ($lecturerAiMode ? 'lecturer' : 'admin') }}.{{ (int) session('auth_user.id') }}"
     data-conversations-url="{{ url($studentAiMode ? '/student/ai-helper/conversations' : ($lecturerAiMode ? '/lecturer/ai-helper/conversations' : '/admin/ai-helper/conversations')) }}">
    <section class="ai-panel">
        <div class="ai-top-actions">
            <button type="button" class="ai-new-chat-trigger" id="aiNewConversation" aria-label="{{ __('New chat') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg><span>{{ __('New chat') }}</span>
            </button>
            <button type="button" class="ai-history-trigger" id="aiHistoryTrigger" aria-expanded="false" aria-controls="aiHistoryPanel" aria-label="{{ __('Conversation history') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></svg><span>{{ __('History') }}</span>
            </button>
            @if($canUploadAiFiles)
            <button type="button" class="ai-quick-action" id="aiUploadShortcut" aria-label="{{ __('Upload PDF or image') }}">
                <span class="ai-quick-action-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </span>
                <span class="ai-quick-label">{{ __('Upload PDF or image') }}</span>
            </button>
            @if(!$studentAiMode)
            <button type="button" class="ai-quick-action" id="paperworkTemplateShortcut">
                <span class="ai-quick-action-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </span>
                <span class="ai-quick-label">{{ __('Paperwork Template') }}</span>
            </button>
            <button type="button" class="ai-quick-action" id="programReportTemplateShortcut">
                <span class="ai-quick-action-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="18" x2="9" y2="15"/><line x1="15" y1="18" x2="15" y2="9"/></svg>
                </span>
                <span class="ai-quick-label">{{ __('Program Report Template') }}</span>
            </button>
            @endif
            @endif
        </div>
        <div class="ai-head">
            <div>
                <h3>{{ __('MyHEP AI') }}</h3>
                <p class="ai-sub">{{ $studentAiMode ? __('Ask about your scholarship, offenses, payments, rules, or student portal steps.') : ($lecturerAiMode ? __('Get text guidance and summaries from records available to your lecturer category.') : __('Use templates and filters to generate actionable admin outputs quickly.')) }}</p>
            </div>
            <div class="ai-badges">
                <span class="ai-badge">BETA</span>
                <span class="ai-badge">{{ strtoupper($aiProvider) }}</span>
                <span class="ai-badge" id="aiClock">--:--</span>
            </div>
        </div>

        <div class="ai-chat-log" id="aiChatLog" aria-live="polite">
            <div class="ai-empty-state" id="aiEmptyState">
                <span class="ai-empty-orb">@include('partials.ai_helper_icon')</span>
                <h3>{{ __('What should we focus on?') }}</h3>
                <p>{{ $studentAiMode ? __('Ask about your own scholarship, offenses, payments, rules, or MyHEP portal steps.') : ($lecturerAiMode ? __('Research any topic, analyze attached documents, or summarize the anonymized records available to your lecturer category.') : __('Research any topic, analyze attached documents, or work with authorized MyHEP records.')) }}</p>
                <div class="ai-empty-chips">
                    @if($studentAiMode)
                    <button type="button" class="ai-empty-chip" data-template="{{ __('What can the AI Helper help me with?') }}">{{ __('Overview') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('What is my current scholarship status?') }}">{{ __('My scholarship') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Explain my offense and payment records.') }}">{{ __('Payments') }}</button>
                    @elseif($lecturerAiMode)
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Summarize the records available to my lecturer category.') }}">{{ __('Category summary') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('What lecturer tasks can you help me with?') }}">{{ __('Lecturer guidance') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Prepare an anonymized monthly summary.') }}">{{ __('Monthly summary') }}</button>
                    @else
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Generate Monthly Report') }}">{{ __('Monthly report') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Review Pending Fine Applications') }}">{{ __('Pending fines') }}</button>
                    <button type="button" class="ai-empty-chip" data-template="{{ __('Summarize Scholarship Status') }}">{{ __('Scholarship summary') }}</button>
                    @endif
                </div>
            </div>
        </div>

        <div class="ai-compose">
            <div class="ai-compose-frame">
            <div class="ai-compose-context" id="aiComposeContext" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 12h12m-4-4 4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/></svg><blockquote id="aiComposeContextText"></blockquote><button type="button" id="aiComposeContextClear" aria-label="{{ __('Remove selected context') }}">&times;</button></div>
            @if($canUploadAiFiles)
            <input id="reportAttachment" type="file" accept="application/pdf,.docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,image/webp,text/csv,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.xlsx" multiple hidden>
            <div class="ai-compose-attachments" id="attachmentPreview" aria-live="polite"></div>
            @endif
            <div class="ai-compose-row">
                <textarea class="ai-input" id="aiInput" rows="1" placeholder="{{ __('Ask MyHEP AI anything') }}"></textarea>
                @unless($textOnlyAiMode)
                @endunless
                <button type="button" class="ai-send" id="aiSendBtn" aria-label="{{ __('Send') }}" @disabled(!$aiEnabled)>↑</button>
            </div>
            </div>
            <p class="ai-hint">{{ __('Enter sends. Shift+Enter adds newline. Independently verify AI-generated conclusions.') }}</p>
        </div>
    </section>

    <button type="button" class="ai-history-backdrop" id="aiHistoryBackdrop" aria-label="{{ __('Close conversation history') }}"></button>
    <aside class="ai-history-panel" id="aiHistoryPanel" aria-hidden="true">
        <div class="ai-history-head">
            <div class="ai-history-brand">
                <strong>@include('partials.ai_helper_icon') <span>{{ __('AI History') }}</span></strong>
                <button type="button" class="ai-history-icon-btn" id="aiHistoryClose" aria-label="{{ __('Close') }}">×</button>
            </div>
        </div>

        <div class="ai-history-tabs">
            <button type="button" class="ai-history-tab is-active" data-history-tab="chats">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;margin-right:4px;" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>{{ __('Chats') }} ({{ count($aiConversations ?? []) }})
            </button>
            <button type="button" class="ai-history-tab" data-history-tab="paperwork">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;margin-right:4px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>{{ __('Paperwork') }} ({{ count($initialPaperworks ?? []) }})
            </button>
            <button type="button" class="ai-history-tab" data-history-tab="reports">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;display:inline-block;vertical-align:-2px;margin-right:4px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="18" x2="9" y2="15"/><line x1="15" y1="18" x2="15" y2="9"/></svg>{{ __('Reports') }} ({{ count($initialReports ?? []) }})
            </button>
        </div>

        <!-- Chats View -->
        <div id="aiHistoryChatsView" style="display:flex;flex-direction:column;flex:1 1 auto;overflow:hidden;">
            <div class="ai-history-primary-actions">
                <button type="button" class="ai-history-primary-action" id="aiHistorySearchButton"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4" stroke-linecap="round"/></svg><span>{{ __('Search chats') }}</span></button>
            </div>
            <label class="ai-history-search" id="aiHistorySearch"><input id="aiHistorySearchInput" type="search" placeholder="{{ __('Search conversations') }}" autocomplete="off"></label>
            <div class="ai-history-section-label">{{ __('Recents') }}</div>
            <div class="ai-history-list" id="aiHistoryList">
                @forelse($aiConversations ?? [] as $conversation)
                <div class="ai-history-item" data-conversation-id="{{ $conversation['id'] }}" data-title="{{ $conversation['title'] }}">
                    <span class="ai-history-title">{{ $conversation['title'] }}</span>
                    <span class="ai-history-time">{{ $conversation['last_message_at'] ?? $conversation['updated_at'] }}</span>
                    <span class="ai-history-item-actions"><button type="button" data-history-rename aria-label="{{ __('Rename') }}">✎</button><button type="button" data-history-delete aria-label="{{ __('Delete') }}">×</button></span>
                </div>
                @empty
                <p class="ai-history-empty">{{ __('Your AI conversations will appear here after you send a message.') }}</p>
                @endforelse
            </div>
            <div class="ai-history-foot">
                <p class="ai-history-retention">{{ __('Inactive conversations are automatically deleted after :days days.', ['days' => config('ai.conversation_retention_days', 30)]) }}</p>
                <button type="button" class="ai-history-delete-all" id="aiDeleteAllHistory">{{ __('Delete all AI history') }}</button>
            </div>
        </div>

        <!-- Paperwork History View -->
        <div id="aiHistoryPaperworkView" style="display:none;flex-direction:column;flex:1 1 auto;overflow:hidden;">
            <div class="ai-history-section-label" style="display:flex;justify-content:space-between;align-items:center;">
                <span>{{ __('Paperwork History') }} ({{ count($initialPaperworks ?? []) }})</span>
                <button type="button" id="refreshPaperworkHistory" style="background:transparent;border:none;color:var(--se-primary);font-size:0.72rem;cursor:pointer;font-weight:750;display:inline-flex;align-items:center;gap:3px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    {{ __('Refresh') }}
                </button>
            </div>
            <div class="ai-history-list" id="aiPaperworkHistoryList" style="flex:1;overflow-y:auto;padding:8px;gap:8px;">
                @forelse($initialPaperworks ?? [] as $pw)
                <div class="ai-history-item" style="padding:0.75rem 0.85rem;border:1px solid rgba(255,255,255,0.08);border-radius:12px;margin-bottom:0.6rem;background:rgba(255,255,255,0.03);display:block;">
                    <div style="font-weight:750;font-size:0.82rem;line-height:1.35;margin-bottom:4px;color:var(--text, #fff);">${pw['title']}</div>
                    <div style="font-size:0.68rem;color:var(--text-muted, #aaa);margin-bottom:8px;">{{ $pw['date_text'] }} · {{ $pw['venue'] }} · <span title="{{ $pw['created_at_date'] }}">{{ $pw['created_at'] }}</span></div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                        @if($pw['docx_url'])<a href="{{ $pw['docx_url'] }}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:var(--se-primary);color:#fff;font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> DOCX</a>@endif
                        @if($pw['pdf_url'])<a href="{{ $pw['pdf_url'] }}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:rgba(255,255,255,0.12);color:var(--text, #fff);font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12h4"/><path d="M10 16h4"/></svg> PDF</a>@endif
                        <button type="button" data-delete-paperwork="{{ $pw['id'] }}" style="margin-left:auto;border:none;background:transparent;color:#ef4444;font-size:0.85rem;cursor:pointer;padding:2px 6px;display:flex;align-items:center;" title="{{ __('Delete') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                    </div>
                </div>
                @empty
                <p class="ai-history-empty">{{ __('No paperwork history found.') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Program Reports History View -->
        <div id="aiHistoryReportsView" style="display:none;flex-direction:column;flex:1 1 auto;overflow:hidden;">
            <div class="ai-history-section-label" style="display:flex;justify-content:space-between;align-items:center;">
                <span>{{ __('Program Reports History') }} ({{ count($initialReports ?? []) }})</span>
                <button type="button" id="refreshReportsHistory" style="background:transparent;border:none;color:var(--se-primary);font-size:0.72rem;cursor:pointer;font-weight:750;display:inline-flex;align-items:center;gap:3px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    {{ __('Refresh') }}
                </button>
            </div>
            <div class="ai-history-list" id="aiPaperworkHistoryList" style="flex:1;overflow-y:auto;padding:8px;gap:8px;">
                @forelse($initialPaperworks ?? [] as $pw)
                <div class="ai-history-item" style="padding:0.75rem 0.85rem;border:1px solid rgba(255,255,255,0.08);border-radius:12px;margin-bottom:0.6rem;background:rgba(255,255,255,0.03);display:block;">
                    <div style="font-weight:750;font-size:0.82rem;line-height:1.35;margin-bottom:4px;color:var(--text, #fff);">{{ $pw['title'] }}</div>
                    <div style="font-size:0.68rem;color:var(--text-muted, #aaa);margin-bottom:8px;">{{ $pw['date_text'] }} · {{ $pw['venue'] }} · <span title="{{ $pw['created_at_date'] }}">{{ $pw['created_at'] }}</span></div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                        @if($pw['docx_url'])<a href="{{ $pw['docx_url'] }}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:var(--se-primary);color:#fff;font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> DOCX</a>@endif
                        @if($pw['pdf_url'])<a href="{{ $pw['pdf_url'] }}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:rgba(255,255,255,0.12);color:var(--text, #fff);font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12h4"/><path d="M10 16h4"/></svg> PDF</a>@endif
                        <button type="button" data-delete-paperwork="{{ $pw['id'] }}" style="margin-left:auto;border:none;background:transparent;color:#ef4444;font-size:0.85rem;cursor:pointer;padding:2px 6px;display:flex;align-items:center;" title="{{ __('Padam') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                    </div>
                </div>
                @empty
                <p class="ai-history-empty">{{ __('Tiada sejarah kertas kerja dijumpai.') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Program Reports History View -->
        <div id="aiHistoryReportsView" style="display:none;flex-direction:column;flex:1 1 auto;overflow:hidden;">
            <div class="ai-history-section-label" style="display:flex;justify-content:space-between;align-items:center;">
                <span>{{ __('Sejarah Laporan Program') }} ({{ count($initialReports ?? []) }})</span>
                <button type="button" id="refreshReportsHistory" style="background:transparent;border:none;color:var(--se-primary);font-size:0.72rem;cursor:pointer;font-weight:750;display:inline-flex;align-items:center;gap:3px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    {{ __('Muat Semula') }}
                </button>
            </div>
            <div class="ai-history-list" id="aiReportsHistoryList" style="flex:1;overflow-y:auto;padding:8px;gap:8px;">
                @forelse($initialReports ?? [] as $rep)
                <div class="ai-history-item" style="padding:0.75rem 0.85rem;border:1px solid rgba(255,255,255,0.08);border-radius:12px;margin-bottom:0.6rem;background:rgba(255,255,255,0.03);display:block;">
                    <div style="font-weight:750;font-size:0.82rem;line-height:1.35;margin-bottom:4px;color:var(--text, #fff);">{{ $rep['title'] }}</div>
                    <div style="font-size:0.68rem;color:var(--text-muted, #aaa);margin-bottom:8px;">{{ $rep['venue'] }} · <span style="text-transform:uppercase;font-weight:700;color:var(--se-primary);">{{ $rep['status'] ?? 'draft' }}</span> · <span title="{{ $rep['created_at_date'] }}">{{ $rep['created_at'] }}</span></div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                        @if($rep['docx_url'])<a href="{{ $rep['docx_url'] }}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:var(--se-primary);color:#fff;font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> DOCX</a>@endif
                        @if($rep['pdf_url'])<a href="{{ $rep['pdf_url'] }}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:rgba(255,255,255,0.12);color:var(--text, #fff);font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12h4"/><path d="M10 16h4"/></svg> PDF</a>@endif
                        @if($rep['operations_url'])<a href="{{ $rep['operations_url'] }}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:rgba(255,255,255,0.08);color:var(--text, #fff);font-size:0.7rem;font-weight:600;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> {{ __('Operasi') }}</a>@endif
                    </div>
                </div>
                @empty
                <p class="ai-history-empty">{{ __('Tiada sejarah laporan program dijumpai.') }}</p>
                @endforelse
            </div>
        </div>
    </aside>

    <div class="ai-selection-tools" id="aiSelectionTools" role="toolbar" aria-label="{{ __('AI text actions') }}" aria-hidden="true">
        <button type="button" id="aiAskSelection">{{ __('Ask MyHEP AI') }}</button>
        <button type="button" id="aiWriteSelection">{{ __('Start writing') }}</button>
    </div>

    <div class="ai-confirm" id="aiConfirmDialog" role="dialog" aria-modal="true" aria-labelledby="aiConfirmTitle" aria-describedby="aiConfirmMessage" aria-hidden="true">
        <button type="button" class="ai-confirm-backdrop" data-confirm-cancel tabindex="-1" aria-label="{{ __('Cancel') }}"></button>
        <div class="ai-confirm-card">
            <div class="ai-confirm-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V4h6v3m-9 0 1 13h10l1-13M10 11v5m4-5v5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            <h3 id="aiConfirmTitle">{{ __('Delete conversation?') }}</h3>
            <p id="aiConfirmMessage"></p>
            <div class="ai-confirm-actions">
                <button type="button" class="ai-confirm-button" data-confirm-cancel>{{ __('Cancel') }}</button>
                <button type="button" class="ai-confirm-button ai-confirm-button--danger" id="aiConfirmAccept">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>

    @if(!$studentAiMode)
    <!-- Paperwork Generator Modal -->
    <div class="ai-confirm" id="paperworkDialog" role="dialog" aria-modal="true" aria-labelledby="paperworkDialogTitle" aria-hidden="true">
        <button type="button" class="ai-confirm-backdrop" data-paperwork-close tabindex="-1" aria-label="{{ __('Close') }}"></button>
        <div class="ai-confirm-card" data-lenis-prevent style="text-align:left;">
            <div class="ai-modal-header">
                <div class="ai-modal-title-box">
                    <div class="ai-modal-icon-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                    <div class="ai-modal-title-copy">
                        <h3 id="paperworkDialogTitle">{{ __('AI Program Paperwork Generator') }}</h3>
                        <p>{{ __('Fill in the program details below. AI will generate a complete official Politeknik Besut paperwork (Standard 2025 format).') }}</p>
                    </div>
                </div>
                <button type="button" class="ai-modal-close-btn" data-paperwork-close aria-label="{{ __('Close') }}">✕</button>
            </div>

            <form id="paperworkForm" class="ai-modal-form" method="POST" action="{{ route($lecturerAiMode ? 'lecturer.ai-helper.paperwork.generate' : 'admin.ai-helper.paperwork.generate') }}" enctype="multipart/form-data">
                @csrf

                <div class="ai-modal-body">
                    @if(count($ownedPrograms ?? []) > 0)
                    <div class="ai-form-card">
                        <div class="ops-field">
                            <label for="paperworkPresetProgram">
                                <span>{{ __('Choose from My Programs (Optional - Auto-fill)') }}</span>
                            </label>
                            <select id="paperworkPresetProgram" class="ai-modal-select">
                                <option value="">{{ __('-- New Program / Manual Entry --') }}</option>
                                @foreach($ownedPrograms as $op)
                                    <option value="{{ $op->id }}" data-title="{{ $op->title }}" data-venue="{{ $op->venue }}" data-date="{{ $op->starts_at ? date('d.m.Y', strtotime($op->starts_at)) : '' }}">
                                        {{ $op->title }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="program_id" id="paperworkProgramId" value="">
                        </div>
                    </div>
                    @endif

                    <div class="ai-form-card">
                        <div class="ai-form-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span>{{ __('Program Details') }}</span>
                        </div>
                        <div class="ai-form-grid">
                            <div class="ops-field ai-form-col-full">
                                <label for="paperworkTitle">
                                    <span>{{ __('1. Program Title *') }}</span>
                                </label>
                                <input id="paperworkTitle" name="title" type="text" required placeholder="{{ __('e.g. Student Leadership & Empowerment Workshop 2025') }}" class="ai-modal-input">
                            </div>

                            <div class="ops-field">
                                <label for="paperworkDate">
                                    <span>{{ __('2. Program Date *') }}</span>
                                </label>
                                <input id="paperworkDate" name="date_text" type="text" required placeholder="{{ __('e.g. 15 March 2025 (Saturday)') }}" class="ai-modal-input">
                            </div>

                            <div class="ops-field">
                                <label for="paperworkVenue">
                                    <span>{{ __('3. Program Venue *') }}</span>
                                </label>
                                <input id="paperworkVenue" name="venue" type="text" required placeholder="{{ __('e.g. Main Lecture Hall, Politeknik Besut') }}" class="ai-modal-input">
                            </div>

                            <div class="ops-field">
                                <label for="paperworkOrganizer">
                                    <span>{{ __('4. Organizer / Department *') }}</span>
                                </label>
                                <input id="paperworkOrganizer" name="organizer" type="text" required placeholder="{{ __('e.g. Student Affairs Department / JTMK') }}" class="ai-modal-input">
                            </div>

                            <div class="ops-field">
                                <label for="paperworkTargetGroup">
                                    <span>{{ __('5. Target Group *') }}</span>
                                </label>
                                <input id="paperworkTargetGroup" name="target_group" type="text" required placeholder="{{ __('e.g. Semester 1 - 5 Students') }}" class="ai-modal-input">
                            </div>

                            <div class="ops-field ai-form-col-full">
                                <label for="paperworkParticipants">
                                    <span>{{ __('6. Number of Participants *') }}</span>
                                </label>
                                <input id="paperworkParticipants" name="participant_count" type="text" required placeholder="{{ __('e.g. 50 participants') }}" class="ai-modal-input">
                            </div>
                        </div>
                    </div>

                    <!-- AJK Program -->
                    <div class="ai-form-card">
                        <div class="ops-field program-report-upload">
                            <div class="program-report-upload-head">
                                <label for="paperworkAjkFile">
                                    <span>{{ __('7. Upload Committee List (PDF / Word) or Type Manually') }}</span>
                                </label>
                                <label class="program-report-add-file" for="paperworkAjkFile">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span>{{ __('Choose Committee File') }}</span>
                                </label>
                            </div>
                            <input class="program-report-file-input" id="paperworkAjkFile" name="ajk_file" type="file" accept=".pdf,.docx,.doc,.txt">
                            <div id="paperworkAjkFileName" style="font-size:0.75rem;color:var(--se-primary);font-weight:750;display:none;margin-top:4px;"></div>
                            <textarea name="ajk_text" id="paperworkAjkText" rows="3" placeholder="{{ __("Type committee list (Optional if file attached):\nProgram Director: ..., Secretary: ..., Advisor: ..., Committee Members: ...") }}" class="ai-modal-textarea" style="margin-top:6px;"></textarea>
                        </div>
                    </div>

                    <!-- Aturcara Program -->
                    <div class="ai-form-card">
                        <div class="ops-field">
                            <label for="paperworkItinerary">
                                <span>{{ __('8. Program Schedule / Itinerary') }}</span>
                            </label>
                            <textarea id="paperworkItinerary" name="itinerary" rows="4" placeholder="{{ __("Tentative program schedule:\n08:00 AM - Participant Registration\n08:30 AM - Briefing & Ice Breaking\n10:30 AM - Morning Refreshments\n11:00 AM - Hands-on Workshop\n01:00 PM - Lunch & Prayer Break\n02:30 PM - Project Presentation & Closing Ceremony") }}" class="ai-modal-textarea"></textarea>
                        </div>
                    </div>

                    <!-- Perincian Kewangan -->
                    <div class="ai-form-card">
                        <div class="ops-field">
                            <label for="paperworkFinancial">
                                <span>{{ __('9. Budget & Financial Estimation') }}</span>
                            </label>
                            <textarea id="paperworkFinancial" name="financial_details" rows="4" placeholder="{{ __("Estimated budget details:\n1. Participant Meals: RM10.00 x 50 pax = RM500.00 (OS29000)\n2. Speaker Token of Appreciation: RM150.00 x 1 = RM150.00 (OS29000)\nTotal Estimated: RM650.00 (Source: Government / OS29000)") }}" class="ai-modal-textarea"></textarea>
                        </div>
                    </div>

                    <!-- Output Format -->
                    <div class="ai-form-card">
                        <div class="ops-field">
                            <label for="paperworkOutput">
                                <span>{{ __('10. Document Format') }}</span>
                            </label>
                            <select id="paperworkOutput" name="output_format" required class="ai-modal-select">
                                <option value="both">{{ __('DOCX and PDF (Recommended)') }}</option>
                                <option value="docx">{{ __('DOCX (Editable)') }}</option>
                                <option value="pdf">{{ __('PDF (Print Ready)') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="program-report-progress" id="paperworkProgress" role="status" aria-live="polite">
                        <strong>{{ __('Generating Official Paperwork...') }}</strong>
                        <ol>
                            <li data-paperwork-progress-step>{{ __('Reading Polibesut 2025 standard paperwork template') }}</li>
                            <li data-paperwork-progress-step>{{ __('Analyzing program details & committee list') }}</li>
                            <li data-paperwork-progress-step>{{ __('Structuring objectives, impact & budget allocation') }}</li>
                            <li data-paperwork-progress-step>{{ __('Generating DOCX & PDF output files') }}</li>
                        </ol>
                    </div>
                </div>

                <div class="ai-modal-footer">
                    <button type="button" class="ai-modal-btn" data-paperwork-close>{{ __('Cancel') }}</button>
                    <button id="paperworkSubmit" type="submit" class="ai-modal-btn ai-modal-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>{{ __('Generate Paperwork') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Paperwork Complete Dialog -->
    @if(session('generated_paperwork'))
    @php
        $gp = session('generated_paperwork');
    @endphp
    <div class="ai-confirm is-open" id="paperworkCompleteDialog" role="dialog" aria-modal="true" aria-labelledby="paperworkCompleteTitle" aria-hidden="false">
        <button type="button" class="ai-confirm-backdrop" data-paperwork-complete-close tabindex="-1" aria-label="{{ __('Close') }}"></button>
        <div class="ai-confirm-card program-report-complete-card" data-lenis-prevent>
            <div class="program-report-complete-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.5l4.2 4.2L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            <h3 id="paperworkCompleteTitle">{{ __('Paperwork Generated Successfully!') }}</h3>
            <p>{{ __('Official Politeknik Besut Paperwork (Standard 2025 Format) has been successfully generated.') }}</p>
            <div class="program-report-complete-program"><span>{{ __('Program') }}</span><span aria-hidden="true">&middot;</span><strong>{{ $gp['title'] }}</strong></div>
            <div class="program-report-complete-files">
                @if($gp['docx_url'])<a href="{{ $gp['docx_url'] }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;margin-right:4px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>{{ __('Download Editable DOCX') }}</a>@endif
                @if($gp['pdf_url'])<a href="{{ $gp['pdf_url'] }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;display:inline-block;vertical-align:-2px;margin-right:4px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12h4"/><path d="M10 16h4"/></svg>{{ __('Download PDF') }}</a>@endif
            </div>
            <p class="program-report-complete-note">{{ __('Please review the details before printing or submitting for official approval.') }}</p>
            <div class="ai-confirm-actions">
                <button type="button" class="ai-confirm-button is-primary" data-paperwork-complete-close>{{ __('Close') }}</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Program Report Modal -->
    <div class="ai-confirm" id="programReportDialog" role="dialog" aria-modal="true" aria-labelledby="programReportDialogTitle" aria-hidden="true">
        <button type="button" class="ai-confirm-backdrop" data-program-report-close tabindex="-1" aria-label="{{ __('Close') }}"></button>
        <div class="ai-confirm-card" data-lenis-prevent style="text-align:left;">
            <div class="ai-modal-header">
                <div class="ai-modal-title-box">
                    <div class="ai-modal-icon-badge">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="18" x2="9" y2="15"/><line x1="15" y1="18" x2="15" y2="9"/></svg>
                    </div>
                    <div class="ai-modal-title-copy">
                        <h3 id="programReportDialogTitle">{{ __('AI Program Report Generator') }}</h3>
                        <p>{{ __('Select a program and documents. MyHEP automatically incorporates attendance records and feedback surveys, and saves the official report.') }}</p>
                    </div>
                </div>
                <button type="button" class="ai-modal-close-btn" data-program-report-close aria-label="{{ __('Close') }}">✕</button>
            </div>

            <form id="programReportForm" class="ai-modal-form" method="post" enctype="multipart/form-data">
                @csrf
                <div class="ai-modal-body">
                    <div class="ai-form-card">
                        <div class="ops-field">
                            <label for="programReportProgram">
                                <span>{{ __('My Program') }}</span>
                            </label>
                            <select id="programReportProgram" required class="ai-modal-select">
                                <option value="">{{ __('-- Select Program --') }}</option>
                                @foreach($ownedPrograms as $ownedProgram)
                                    <option value="{{ $ownedProgram->id }}" data-action="{{ route('admin.programs.report.generate', $ownedProgram->id) }}" data-requires-paperwork="{{ ($ownedProgram->registration_type ?? 'approved_program') === 'attendance_only_activity' ? '0' : '1' }}">{{ $ownedProgram->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ai-form-card program-report-upload" id="programReportPaperworkField">
                        @if(count($initialPaperworks ?? []) > 0)
                        <div style="margin-bottom:8px;padding:10px 12px;border-radius:12px;background:color-mix(in srgb,var(--surface) 90%,var(--se-primary-soft));border:1px solid color-mix(in srgb,var(--se-primary) 28%,var(--border));">
                            <label for="programReportSavedPaperwork" style="font-size:0.78rem;font-weight:750;color:var(--text);display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;color:var(--se-primary);" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                <span>{{ __('Select from AI-Generated Paperwork (No re-upload needed)') }}</span>
                            </label>
                            <select id="programReportSavedPaperwork" name="saved_paperwork_id" class="ai-modal-select" style="padding:8px 34px 8px 12px;font-size:0.82rem;">
                                <option value="">{{ __('-- Select Paperwork from History (Optional) --') }}</option>
                                @foreach($initialPaperworks as $pw)
                                    <option value="{{ $pw['id'] }}">{{ $pw['title'] }} ({{ $pw['created_at_date'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="program-report-upload-head">
                            <label for="programReportPaperwork">
                                <span>{{ __('Or Upload Approved Paperwork (PDF / Word)') }}</span>
                            </label>
                            <label class="program-report-add-file" for="programReportPaperwork">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span>{{ __('Choose File') }}</span>
                            </label>
                        </div>
                        <input class="program-report-file-input" id="programReportPaperwork" name="paperwork_file" type="file" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                        <div class="program-report-attachments is-empty" id="programReportPaperworkPreview" data-drop-zone="paperwork" role="button" tabindex="0" aria-label="{{ __('Add or drop approved paperwork') }}" aria-live="polite"></div>
                        <small style="font-size:0.72rem;color:var(--text-muted);display:block;margin-top:2px;">{{ __('Required for approved programs. Attendance-only activities do not require paperwork.') }}</small>
                    </div>

                    <div class="ai-form-card program-report-upload">
                        <div class="program-report-upload-head">
                            <label for="programReportImages">
                                <span>{{ __('Program / Activity Photos') }}</span>
                            </label>
                            <label class="program-report-add-file" for="programReportImages">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-linecap="round"/><rect x="3" y="3" width="18" height="18" rx="4"/></svg>
                                <span>{{ __('Add Photos') }}</span>
                            </label>
                        </div>
                        <input class="program-report-file-input" id="programReportImages" name="program_images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required>
                        <div class="program-report-attachments is-empty" id="programReportImagesPreview" data-drop-zone="images" role="button" tabindex="0" aria-label="{{ __('Add or drop activity images') }}" aria-live="polite"></div>
                        <small class="program-report-upload-status" id="programReportImagesStatus" style="font-size:0.72rem;color:var(--text-muted);display:block;margin-top:2px;">{{ __('Add up to 8 activity photos. You can choose more images again without replacing the current selection.') }}</small>
                    </div>

                    <div class="ai-form-card">
                        <div class="ops-field">
                            <label for="programReportOutput">
                                <span>{{ __('Document Format') }}</span>
                            </label>
                            <select id="programReportOutput" name="output_format" required class="ai-modal-select">
                                <option value="both">{{ __('DOCX and PDF (Recommended)') }}</option>
                                <option value="docx">DOCX</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                    </div>

                    <div class="program-report-progress" id="programReportProgress" role="status" aria-live="polite">
                        <strong>{{ __('Generating Official Program Report...') }}</strong>
                        <ol>
                            <li data-report-progress-step>{{ __('Uploading source documents and photos') }}</li>
                            <li data-report-progress-step>{{ __('Analyzing program records & attendance metrics') }}</li>
                            <li data-report-progress-step>{{ __('Structuring report template & formatting') }}</li>
                            <li data-report-progress-step>{{ __('Finalizing and saving report files') }}</li>
                        </ol>
                    </div>
                </div>

                <div class="ai-modal-footer">
                    <button type="button" class="ai-modal-btn" data-program-report-close>{{ __('Cancel') }}</button>
                    <button id="programReportSubmit" type="submit" class="ai-modal-btn ai-modal-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>{{ __('Generate Program Report') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(session('generated_report'))
    @php
        $generatedReport = session('generated_report');
    @endphp
    <div class="ai-confirm is-open" id="programReportCompleteDialog" role="dialog" aria-modal="true" aria-labelledby="programReportCompleteTitle" aria-describedby="programReportCompleteDescription" aria-hidden="false">
        <button type="button" class="ai-confirm-backdrop" data-program-report-complete-close tabindex="-1" aria-label="{{ __('Close') }}"></button>
        <div class="ai-confirm-card program-report-complete-card" data-lenis-prevent>
            <div class="program-report-complete-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.5l4.2 4.2L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            <h3 id="programReportCompleteTitle">{{ __('Program report generated successfully') }}</h3>
            <p id="programReportCompleteDescription">{{ __('Your report files were saved successfully. Download and review them before submitting the final report for approval.') }}</p>
            <div class="program-report-complete-program"><span>{{ __('Program') }}</span><span aria-hidden="true">&middot;</span><strong>{{ $generatedReport['program_title'] }}</strong></div>
            <div class="program-report-complete-files">
                @if($generatedReport['docx_url'])<a href="{{ $generatedReport['docx_url'] }}">{{ __('Download editable DOCX') }}</a>@endif
                @if($generatedReport['pdf_url'])<a href="{{ $generatedReport['pdf_url'] }}">{{ __('Download review PDF') }}</a>@endif
            </div>
            <p class="program-report-complete-note">{{ __('Generation creates a draft. Check the content, formatting, blank pages, names, dates, images, and signatures before submission.') }}</p>
            <div class="ai-confirm-actions">
                <button type="button" class="ai-confirm-button" data-program-report-complete-close>{{ __('Close') }}</button>
                <a class="ai-confirm-button is-primary" href="{{ $generatedReport['operations_url'] }}">{{ __('Review report workflow') }}</a>
                <a class="ai-confirm-button" href="{{ $generatedReport['details_url'] }}">{{ __('View program') }}</a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
@php
    $aiScriptText = [
        'ready' => __('Ready. Choose a task template or enter a custom request.'),
        'missingKey' => __('AI API key is not configured. Add an API key in .env, then clear config cache if needed.'),
        'scope' => __('Scope: students, scholarships, offenses, applications'),
        'thinking' => __('Thinking...'),
        'failed' => __('AI request failed.'),
        'empty' => __('No answer was returned.'),
        'unreachable' => __('AI service could not be reached.'),
        'draftPrompt' => __('Draft a short announcement for students based on the latest issue or pending action. Include title and body.'),
    ];
@endphp
<script>
(() => {
    const root = document.querySelector('.ai-admin');
    const clockNode = document.getElementById('aiClock');
    const stampNode = document.getElementById('aiStamp');
    const chatLog = document.getElementById('aiChatLog');
    const input = document.getElementById('aiInput');
    const sendBtn = document.getElementById('aiSendBtn');
    const attachmentInput = document.getElementById('reportAttachment');
    const attachmentPreview = document.getElementById('attachmentPreview');
    let selectedAttachments = [];
    let attachmentObjectUrls = [];
    const uploadShortcut = document.getElementById('aiUploadShortcut');
    const paperworkShortcut = document.getElementById('paperworkTemplateShortcut');
    const paperworkDialog = document.getElementById('paperworkDialog');
    const paperworkForm = document.getElementById('paperworkForm');
    const paperworkPresetProgram = document.getElementById('paperworkPresetProgram');
    const paperworkProgramId = document.getElementById('paperworkProgramId');
    const paperworkTitle = document.getElementById('paperworkTitle');
    const paperworkDate = document.getElementById('paperworkDate');
    const paperworkVenue = document.getElementById('paperworkVenue');
    const paperworkAjkFile = document.getElementById('paperworkAjkFile');
    const paperworkAjkFileName = document.getElementById('paperworkAjkFileName');
    const paperworkProgress = document.getElementById('paperworkProgress');
    const paperworkSubmit = document.getElementById('paperworkSubmit');
    const programReportShortcut = document.getElementById('programReportTemplateShortcut');
    const programReportDialog = document.getElementById('programReportDialog');
    const programReportProgram = document.getElementById('programReportProgram');
    const programReportForm = document.getElementById('programReportForm');
    const programReportPaperwork = document.getElementById('programReportPaperwork');
    const programReportPaperworkField = document.getElementById('programReportPaperworkField');
    const programReportPaperworkPreview = document.getElementById('programReportPaperworkPreview');
    const programReportImages = document.getElementById('programReportImages');
    const programReportImagesPreview = document.getElementById('programReportImagesPreview');
    const programReportImagesStatus = document.getElementById('programReportImagesStatus');
    const programReportProgress = document.getElementById('programReportProgress');
    const programReportSubmit = document.getElementById('programReportSubmit');
    let programReportSelectedImages = [];
    let programReportImageUrls = [];
    const historyTrigger = document.getElementById('aiHistoryTrigger');
    const historyPanel = document.getElementById('aiHistoryPanel');
    const historyBackdrop = document.getElementById('aiHistoryBackdrop');
    const historyClose = document.getElementById('aiHistoryClose');
    const historyList = document.getElementById('aiHistoryList');
    const newConversationBtn = document.getElementById('aiNewConversation');
    const deleteAllHistoryBtn = document.getElementById('aiDeleteAllHistory');
    const historySearchButton = document.getElementById('aiHistorySearchButton');
    const historySearch = document.getElementById('aiHistorySearch');
    const historySearchInput = document.getElementById('aiHistorySearchInput');
    const confirmDialog = document.getElementById('aiConfirmDialog');
    const confirmTitle = document.getElementById('aiConfirmTitle');
    const confirmMessage = document.getElementById('aiConfirmMessage');
    const confirmAccept = document.getElementById('aiConfirmAccept');
    const selectionTools = document.getElementById('aiSelectionTools');
    const askSelection = document.getElementById('aiAskSelection');
    const writeSelection = document.getElementById('aiWriteSelection');
    const composeContext = document.getElementById('aiComposeContext');
    const composeContextText = document.getElementById('aiComposeContextText');
    const composeContextClear = document.getElementById('aiComposeContextClear');
    const locale = @json(app()->getLocale() === 'ms' ? 'ms-MY' : 'en-GB');
    let lastRequest = null;
    let lastAnswer = '';
    let currentConversationId = null;
    let requestInFlight = false;
    let selectedAiText = '';
    let selectedAiArticle = null;
    let composerSelectionContext = '';
    const emptyStateMarkup = chatLog?.innerHTML || '';
    let confirmResolver = null;

    const closeConfirm = (accepted = false) => {
        if (!confirmDialog?.classList.contains('is-open')) return;
        confirmDialog.classList.remove('is-open');
        confirmDialog.setAttribute('aria-hidden', 'true');
        const resolve = confirmResolver;
        confirmResolver = null;
        resolve?.(accepted);
    };
    const requestConfirmation = ({title, message, acceptLabel}) => new Promise(resolve => {
        if (!confirmDialog) return resolve(false);
        confirmResolver = resolve;
        confirmTitle.textContent = title;
        confirmMessage.textContent = message;
        confirmAccept.textContent = acceptLabel;
        confirmDialog.classList.add('is-open');
        confirmDialog.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => confirmAccept.focus());
    });
    confirmDialog?.querySelectorAll('[data-confirm-cancel]').forEach(button => button.addEventListener('click', () => closeConfirm(false)));
    confirmAccept?.addEventListener('click', () => closeConfirm(true));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && confirmDialog?.classList.contains('is-open')) {
            event.preventDefault();
            closeConfirm(false);
        }
    });

    const tick = () => {
        const now = new Date();
        if (clockNode) clockNode.textContent = now.toLocaleTimeString(locale, {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
        if (stampNode) {
            stampNode.textContent = now.toLocaleString(locale, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }
    };
    tick();
    setInterval(tick, 1000);

    if (!root || !chatLog || !input || !sendBtn) return;

    const aiText = {!! json_encode($aiScriptText, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    const initialMessage = root.dataset.aiEnabled === '1'
        ? aiText.ready
        : aiText.missingKey;
    const scopeText = aiText.scope;
    const providerText = root.dataset.aiProvider?.toUpperCase() || 'AI';
    const modelText = root.dataset.aiModel || '-';
    const metaText = `${scopeText} · ${providerText} / ${modelText}`;

    const syncChatFades = () => {
        const maxScroll = Math.max(0, chatLog.scrollHeight - chatLog.clientHeight);
        chatLog.classList.toggle('can-scroll-up', chatLog.scrollTop > 6);
        chatLog.classList.toggle('can-scroll-down', chatLog.scrollTop < maxScroll - 6);
    };
    const scrollChat = () => {
        chatLog.scrollTop = chatLog.scrollHeight;
        requestAnimationFrame(syncChatFades);
    };
    chatLog.addEventListener('scroll', syncChatFades, { passive:true });
    window.addEventListener('resize', syncChatFades, { passive:true });
    const attachmentTypeLabel = file => {
        const extension = file.name.split('.').pop()?.toLowerCase();
        if (extension === 'pdf') return 'PDF';
        if (extension === 'csv') return 'CSV';
        if (extension === 'xlsx') return 'XLSX';
        return file.type.startsWith('image/') ? (file.type.split('/')[1] || 'IMAGE').toUpperCase() : 'FILE';
    };

    const addMessage = (type, text, meta = '', attachments = [], messageId = null) => {
        const article = document.createElement('article');
        article.className = `msg ${type}`;
        const messageText = String(text || '');
        const isReport = type === 'ai' && (messageText.length > 650 || /(?:report|summary|findings|recommendations|laporan|ringkasan|cadangan)/iu.test(messageText));
        if (messageId) article.dataset.messageId = String(messageId);
        if (type === 'ai') {
            article.dataset.rawContent = messageText;
            article.classList.add(isReport ? 'is-report' : 'is-conversation');
        }
        if (type === 'ai') {
            article.classList.add('msg-rich');
            const lines = String(text).replace(/\r\n?/g, '\n').split('\n');
            let list = null;
            let metaGrid = null;
            let hasContent = false;
            const appendInline = (node, value) => {
                String(value).split(/(`[^`]+`|\*\*[^*]+\*\*|\*[^*]+\*)/g).filter(Boolean).forEach(part => {
                    if (part.startsWith('`') && part.endsWith('`')) {
                        const code = document.createElement('code');
                        code.textContent = part.slice(1, -1);
                        node.appendChild(code);
                    } else if (part.startsWith('**') && part.endsWith('**')) {
                        const strong = document.createElement('strong');
                        strong.textContent = part.slice(2, -2);
                        node.appendChild(strong);
                    } else if (part.startsWith('*') && part.endsWith('*')) {
                        const em = document.createElement('em');
                        em.textContent = part.slice(1, -1);
                        node.appendChild(em);
                    } else node.appendChild(document.createTextNode(part));
                });
            };
            const appendTable = (start) => {
                const rows = [];
                let cursor = start;
                while (cursor < lines.length && /^\s*\|.+\|\s*$/.test(lines[cursor])) {
                    rows.push(lines[cursor].trim().slice(1, -1).split('|').map(cell => cell.trim()));
                    cursor++;
                }
                if (rows.length < 2 || !rows[1].every(cell => /^:?-{3,}:?$/.test(cell))) return start;
                const wrap = document.createElement('div'); wrap.className = 'report-table-wrap';
                const table = document.createElement('table');
                const thead = document.createElement('thead'); const headRow = document.createElement('tr');
                rows[0].forEach(value => { const th = document.createElement('th'); appendInline(th, value); headRow.appendChild(th); });
                thead.appendChild(headRow); table.appendChild(thead);
                const tbody = document.createElement('tbody');
                rows.slice(2).forEach(row => { const tr = document.createElement('tr'); row.forEach(value => { const td = document.createElement('td'); appendInline(td, value); tr.appendChild(td); }); tbody.appendChild(tr); });
                table.appendChild(tbody); wrap.appendChild(table); article.appendChild(wrap); hasContent = true;
                return cursor - 1;
            };
            lines.forEach((raw, index) => {
                const line = raw.trim();
                if (!line) { list = null; metaGrid = null; return; }
                if (/^\s*\|.+\|\s*$/.test(raw)) { if (index > 0 && /^\s*\|.+\|\s*$/.test(lines[index - 1])) return; appendTable(index); list = null; metaGrid = null; return; }
                if (/^(?:-{3,}|_{3,}|\*{3,})$/.test(line)) { article.appendChild(document.createElement('hr')); list = null; metaGrid = null; hasContent = true; return; }
                const markdownHeading = line.match(/^(#{1,4})\s+(.+)$/);
                if (markdownHeading) { const h = document.createElement(markdownHeading[1].length === 1 ? 'h3' : 'h4'); appendInline(h, markdownHeading[2]); article.appendChild(h); list = null; metaGrid = null; hasContent = true; return; }
                const boldHeading = line.match(/^\*\*(.+)\*\*$/);
                if (boldHeading) { const h = document.createElement(hasContent ? 'h4' : 'h3'); h.textContent = boldHeading[1]; article.appendChild(h); list = null; metaGrid = null; hasContent = true; return; }
                const metadata = line.match(/^\*{1,2}([^:*]{2,40}):\*{1,2}\s*(.+)$/) || line.match(/^\*\*([^:*]{2,40}):\*\*\s*(.+)$/);
                if (metadata) {
                    if (!metaGrid) { metaGrid = document.createElement('div'); metaGrid.className = 'report-meta'; article.appendChild(metaGrid); }
                    const label = document.createElement('span'); label.className = 'report-meta-label'; label.textContent = metadata[1];
                    const value = document.createElement('span'); value.className = 'report-meta-value'; appendInline(value, metadata[2]);
                    metaGrid.append(label, value); list = null; hasContent = true; return;
                }
                const bullet = line.match(/^[-*]\s*(.+)$/);
                const numbered = line.match(/^\d+[.)]\s+(.+)$/);
                if (bullet || numbered) { const tag = numbered ? 'ol' : 'ul'; if (!list || list.tagName.toLowerCase() !== tag) { list = document.createElement(tag); article.appendChild(list); } const li = document.createElement('li'); appendInline(li, (bullet || numbered)[1]); list.appendChild(li); metaGrid = null; hasContent = true; return; }
                const clean = line.replace(/^\*([^*].*):\*\*\s*/, '$1: ');
                const isTitle = !hasContent && clean.length <= 100 && !/[.!?]$/.test(clean);
                const node = document.createElement(isTitle ? 'h3' : 'p'); appendInline(node, clean); article.appendChild(node); list = null; metaGrid = null; hasContent = true;
            });
        } else {
            if (type === 'user' && attachments.length) {
                article.classList.add('has-attachments');
                const attachmentList = document.createElement('div');
                attachmentList.className = 'ai-sent-attachments';
                attachments.forEach(file => {
                    const card = document.createElement('div');
                    card.className = 'ai-sent-attachment';
                    const fileType = attachmentTypeLabel(file);
                    const typeNode = document.createElement('span'); typeNode.className = 'ai-sent-attachment-type'; typeNode.textContent = fileType;
                    const nameNode = document.createElement('span'); nameNode.className = 'ai-sent-attachment-name'; nameNode.textContent = file.name;
                    if (file.type.startsWith('image/')) {
                        card.classList.add('is-image');
                        card.style.backgroundImage = `url(${URL.createObjectURL(file)})`;
                    }
                    card.append(typeNode, nameNode);
                    attachmentList.appendChild(card);
                });
                article.appendChild(attachmentList);
            }
            const pre = document.createElement('pre');
            pre.textContent = text;
            article.appendChild(pre);
        }
        if (meta) {
            const metaNode = document.createElement('span');
            metaNode.className = 'msg-meta';
            metaNode.textContent = meta;
            article.appendChild(metaNode);
        }
        if (isReport) {
            const content = document.createElement('div');
            content.className = 'ai-report-content';
            while (article.firstChild) content.appendChild(article.firstChild);
            const kicker = document.createElement('div');
            kicker.className = 'ai-report-kicker';
            kicker.textContent = @json(__('MyHEP AI report'));
            article.append(kicker, content);
            if (messageText.length > 900) {
                article.classList.add('is-collapsed');
                const toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'ai-report-toggle';
                toggle.dataset.toggleReport = '';
                toggle.textContent = @json(__('Show full report'));
                article.appendChild(toggle);
            }
        }
        if (type === 'ai') {
            chatLog.querySelectorAll('.ai-message-actions').forEach(actions => { actions.hidden = true; });
            const actions = document.createElement('div');
            actions.className = 'ai-message-actions';
            actions.innerHTML = `<button type="button" class="ai-message-action" data-copy-ai-message><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="11" height="11" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg><span>${@json(__('Copy'))}</span></button>${root.dataset.canEditAi === '1' && messageId ? `<button type="button" class="ai-message-action" data-edit-ai-message><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/></svg><span>${@json(__('Edit'))}</span></button>` : ''}<button type="button" class="ai-message-action" data-regenerate-ai-message><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 11a8 8 0 1 0-2.34 5.66L20 14"/><path d="M20 7v4h-4"/></svg><span>${@json(__('Regenerate'))}</span></button>`;
            article.appendChild(actions);
        }
        chatLog.appendChild(article);
        scrollChat();
        return article;
    };

    const activeConversationStorageKey = root.dataset.aiSessionKey;
    const rememberActiveConversation = id => {
        try {
            if (id) sessionStorage.setItem(activeConversationStorageKey, String(id));
            else sessionStorage.removeItem(activeConversationStorageKey);
        } catch (_) {}
    };
    const rememberedConversation = () => {
        try { return sessionStorage.getItem(activeConversationStorageKey); }
        catch (_) { return null; }
    };

    const resetChat = (forgetActive = true) => {
        closeExpandedEditor();
        currentConversationId = null;
        lastRequest = null;
        lastAnswer = '';
        if (forgetActive) rememberActiveConversation(null);
        historyList?.querySelectorAll('.ai-history-item').forEach(item => item.classList.remove('is-active'));
        root.classList.remove('has-chat');
        chatLog.innerHTML = root.dataset.aiEnabled === '1' ? emptyStateMarkup : '';
        syncChatFades();
        if (root.dataset.aiEnabled !== '1') addMessage('error', initialMessage, metaText);
    };

    const setHistoryOpen = (open) => {
        historyPanel?.classList.toggle('is-open', open);
        historyBackdrop?.classList.toggle('is-open', open);
        historyPanel?.setAttribute('aria-hidden', open ? 'false' : 'true');
        historyTrigger?.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const conversationUrl = id => `${root.dataset.conversationsUrl}/${id}`;
    const upsertConversation = conversation => {
        if (!historyList || !conversation) return;
        historyList.querySelector('.ai-history-empty')?.remove();
        let item = historyList.querySelector(`[data-conversation-id="${conversation.id}"]`);
        if (!item) {
            item = document.createElement('div');
            item.className = 'ai-history-item';
            item.dataset.conversationId = conversation.id;
            item.innerHTML = '<span class="ai-history-title"></span><span class="ai-history-time"></span><span class="ai-history-item-actions"><button type="button" data-history-rename aria-label="Rename">✎</button><button type="button" data-history-delete aria-label="Delete">×</button></span>';
        }
        item.dataset.title = conversation.title || '';
        item.querySelector('.ai-history-title').textContent = conversation.title || @json(__('Untitled conversation'));
        item.querySelector('.ai-history-time').textContent = conversation.last_message_at || conversation.updated_at || '';
        historyList.prepend(item);
        historyList.querySelectorAll('.ai-history-item').forEach(node => node.classList.toggle('is-active', String(node.dataset.conversationId) === String(currentConversationId)));
    };
    const loadConversation = async id => {
        closeExpandedEditor();
        const response = await fetch(conversationUrl(id), {headers:{'Accept':'application/json'},credentials:'same-origin'});
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(payload.message || @json(__('Conversation could not be loaded.')));
        currentConversationId = payload.conversation.id;
        rememberActiveConversation(currentConversationId);
        root.classList.add('has-chat');
        chatLog.innerHTML = '';
        lastAnswer = '';
        (payload.messages || []).forEach(message => {
            const type = message.role === 'assistant' ? 'ai' : 'user';
            addMessage(type, message.content, '', [], message.id);
            if (type === 'ai') lastAnswer = message.content;
            if (type === 'user') lastRequest = {message: message.content, template: null, filters: filters()};
        });
        upsertConversation(payload.conversation);
        setHistoryOpen(false);
        input.focus();
    };
    historyTrigger?.addEventListener('click', () => setHistoryOpen(true));
    historyClose?.addEventListener('click', () => setHistoryOpen(false));
    historyBackdrop?.addEventListener('click', () => setHistoryOpen(false));
    newConversationBtn?.addEventListener('click', () => { resetChat(); setHistoryOpen(false); input.focus(); });
    historySearchButton?.addEventListener('click', () => {
        historySearch?.classList.toggle('is-visible');
        if (historySearch?.classList.contains('is-visible')) historySearchInput?.focus();
    });
    historySearchInput?.addEventListener('input', () => {
        const query = historySearchInput.value.trim().toLocaleLowerCase();
        historyList?.querySelectorAll('.ai-history-item').forEach(item => {
            item.hidden = query !== '' && !String(item.dataset.title || '').toLocaleLowerCase().includes(query);
        });
    });
    const hideSelectionTools = () => {
        selectionTools?.classList.remove('is-open');
        selectionTools?.setAttribute('aria-hidden', 'true');
    };
    const openMessageEditor = (article, selectedText = '') => {
        const raw = article.dataset.rawContent || '';
        hideSelectionTools();
        window.getSelection()?.removeAllRanges();
        article.classList.add('is-writing');
        article.innerHTML = `<div class="ai-writing-head"><span class="ai-writing-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/></svg>${@json(__('Edit'))}</span><div class="ai-writing-tools"><button type="button" data-copy-writing aria-label="${@json(__('Copy'))}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="11" height="11" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg></button><button type="button" data-expand-writing aria-label="${@json(__('Open editor'))}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 4h6v6M20 4l-7 7M10 20H4v-6M4 20l7-7"/></svg></button></div></div><div class="ai-writing-body"><div class="ai-edit-prompt"><input type="text" placeholder="${@json(__('Ask for changes'))}" aria-label="${@json(__('Ask for changes'))}"><button type="button" data-request-ai-edit aria-label="${@json(__('Apply AI changes'))}">↑</button></div><textarea class="ai-message-editor" aria-label="${@json(__('Edit AI response'))}"></textarea><div class="ai-edit-actions"><button type="button" data-cancel-ai-edit>${@json(__('Cancel'))}</button><button type="button" class="is-primary" data-save-ai-edit>${@json(__('Save changes'))}</button></div></div>`;
        const editor = article.querySelector('.ai-message-editor');
        editor.value = raw;
        editor.focus();
        const start = selectedText ? raw.indexOf(selectedText) : -1;
        if (start >= 0) editor.setSelectionRange(start, start + selectedText.length);
    };
    const closeExpandedEditor = () => {
        chatLog?.querySelectorAll('.msg.ai.is-writing.is-expanded').forEach(article => article.classList.remove('is-expanded'));
        document.body.classList.remove('ai-writing-expanded');
    };
    chatLog?.addEventListener('mouseup', () => {
        if (root.dataset.canEditAi !== '1') return;
        requestAnimationFrame(() => {
            const selection = window.getSelection();
            const text = selection?.toString().trim() || '';
            const range = selection?.rangeCount ? selection.getRangeAt(0) : null;
            const article = range?.commonAncestorContainer?.nodeType === Node.ELEMENT_NODE
                ? range.commonAncestorContainer.closest?.('.msg.ai[data-message-id]')
                : range?.commonAncestorContainer?.parentElement?.closest('.msg.ai[data-message-id]');
            if (!text || !range || !article || article.querySelector('.ai-message-editor')) return hideSelectionTools();
            selectedAiText = text;
            selectedAiArticle = article;
            const rect = range.getBoundingClientRect();
            selectionTools.style.left = `${Math.max(8, Math.min(window.innerWidth - 250, rect.left + (rect.width / 2) - 120))}px`;
            selectionTools.style.top = `${Math.max(8, rect.top - 48)}px`;
            selectionTools.classList.add('is-open');
            selectionTools.setAttribute('aria-hidden', 'false');
        });
    });
    askSelection?.addEventListener('click', () => {
        if (!selectedAiText) return;
        composerSelectionContext = selectedAiText;
        composeContextText.textContent = `“${selectedAiText}”`;
        composeContext.hidden = false;
        composeContext.classList.add('is-visible');
        hideSelectionTools();
        window.getSelection()?.removeAllRanges();
        input.value = '';
        input.placeholder = @json(__('Ask MyHEP AI about the selected text'));
        input.focus();
    });
    composeContextClear?.addEventListener('click', () => {
        composerSelectionContext = '';
        composeContext.classList.remove('is-visible');
        composeContext.hidden = true;
        composeContextText.textContent = '';
        input.placeholder = @json(__('Ask MyHEP AI anything'));
        input.focus();
    });
    writeSelection?.addEventListener('click', () => {
        if (selectedAiArticle) openMessageEditor(selectedAiArticle, selectedAiText);
    });
    chatLog?.addEventListener('scroll', hideSelectionTools, {passive:true});
    document.addEventListener('mousedown', event => {
        if (!selectionTools?.contains(event.target) && !event.target.closest?.('.msg.ai[data-message-id]')) hideSelectionTools();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && document.body.classList.contains('ai-writing-expanded')) closeExpandedEditor();
    });
    chatLog?.addEventListener('click', async event => {
        const article = event.target.closest('.msg.ai');
        if (!article) return;
        if (event.target.closest('[data-toggle-report]')) {
            const collapsed = article.classList.toggle('is-collapsed');
            event.target.closest('[data-toggle-report]').textContent = collapsed ? @json(__('Show full report')) : @json(__('Show less'));
            syncChatFades();
            return;
        }
        if (event.target.closest('[data-copy-ai-message]')) {
            await navigator.clipboard?.writeText(article.dataset.rawContent || '').catch(() => {});
            return;
        }
        if (event.target.closest('[data-regenerate-ai-message]')) {
            if (!requestInFlight && lastRequest) send(lastRequest.message, lastRequest.template, lastRequest.selectedContext || '');
            return;
        }
        if (event.target.closest('[data-edit-ai-message]')) {
            if (root.dataset.canEditAi === '1' && article.dataset.messageId) openMessageEditor(article);
            return;
        }
        if (root.dataset.canEditAi !== '1') return;
        if (event.target.closest('[data-copy-writing]')) {
            await navigator.clipboard?.writeText(article.querySelector('.ai-message-editor')?.value || '').catch(() => {});
            return;
        }
        if (event.target.closest('[data-expand-writing]')) {
            const expanded = !article.classList.contains('is-expanded');
            closeExpandedEditor();
            article.classList.toggle('is-expanded', expanded);
            document.body.classList.toggle('ai-writing-expanded', expanded);
            return;
        }
        if (event.target.closest('[data-cancel-ai-edit]')) {
            if (currentConversationId) await loadConversation(currentConversationId);
            return;
        }
        if (event.target.closest('[data-request-ai-edit]')) {
            const instruction = article.querySelector('.ai-edit-prompt input')?.value.trim();
            const context = article.querySelector('.ai-message-editor')?.value.trim();
            if (!instruction || !context || !currentConversationId || requestInFlight) return;
            await loadConversation(currentConversationId);
            send(instruction, null, context);
            return;
        }
        if (event.target.closest('[data-save-ai-edit]')) {
            const editor = article.querySelector('.ai-message-editor');
            const content = editor?.value.trim();
            if (!content || !currentConversationId) return;
            const save = event.target.closest('[data-save-ai-edit]');
            save.disabled = true;
            const response = await fetch(`${conversationUrl(currentConversationId)}/messages/${article.dataset.messageId}`, {method:'PATCH',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},credentials:'same-origin',body:JSON.stringify({content})});
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) { save.disabled = false; return addMessage('error', payload.message || @json(__('AI response could not be updated.'))); }
            await loadConversation(currentConversationId);
        }
    });
    historyList?.addEventListener('click', async event => {
        const item = event.target.closest('.ai-history-item');
        if (!item) return;
        const id = item.dataset.conversationId;
        try {
            if (event.target.closest('[data-history-delete]')) {
                event.stopPropagation();
                if (!await requestConfirmation({
                    title: @json(__('Delete conversation?')),
                    message: @json(__('This conversation and all of its messages will be permanently deleted.')),
                    acceptLabel: @json(__('Delete conversation')),
                })) return;
                const response = await fetch(conversationUrl(id), {method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken},credentials:'same-origin'});
                if (!response.ok) throw new Error(@json(__('Conversation could not be deleted.')));
                item.remove();
                if (String(currentConversationId) === String(id)) resetChat();
                if (!historyList.querySelector('.ai-history-item')) historyList.innerHTML = `<p class="ai-history-empty">${@json(__('Your AI conversations will appear here after you send a message.'))}</p>`;
                return;
            }
            if (event.target.closest('[data-history-rename]')) {
                event.stopPropagation();
                const title = prompt(@json(__('Rename conversation')), item.dataset.title || '');
                if (!title?.trim()) return;
                const response = await fetch(conversationUrl(id), {method:'PATCH',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},credentials:'same-origin',body:JSON.stringify({title:title.trim()})});
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(payload.message || @json(__('Conversation could not be renamed.')));
                upsertConversation(payload.conversation);
                return;
            }
            await loadConversation(id);
        } catch (error) { addMessage('error', error.message || aiText.failed); }
    });
    deleteAllHistoryBtn?.addEventListener('click', async () => {
        if (!await requestConfirmation({
            title: @json(__('Delete all AI history?')),
            message: @json(__('This permanently deletes every AI conversation in your history. This action cannot be undone.')),
            acceptLabel: @json(__('Delete all history')),
        })) return;
        const response = await fetch(root.dataset.conversationsUrl, {method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken},credentials:'same-origin'});
        if (!response.ok) return addMessage('error', @json(__('AI history could not be deleted.')));
        historyList.innerHTML = `<p class="ai-history-empty">${@json(__('Your AI conversations will appear here after you send a message.'))}</p>`;
        resetChat();
        setHistoryOpen(false);
    });

    uploadShortcut?.addEventListener('click', () => { if (attachmentInput) attachmentInput.value = ''; attachmentInput?.click(); });

    const filters = () => ({});

    const setAttachmentFiles = files => {
        if (!attachmentInput || typeof DataTransfer === 'undefined') return;
        const transfer = new DataTransfer();
        files.forEach(file => transfer.items.add(file));
        attachmentInput.files = transfer.files;
    };
    const showAttachments = (limitReached = false) => {
        if (!attachmentPreview) return;
        attachmentObjectUrls.forEach(url => URL.revokeObjectURL(url));
        attachmentObjectUrls = [];
        attachmentPreview.replaceChildren();
        const files = selectedAttachments;
        attachmentPreview.classList.toggle('is-visible', files.length > 0 || limitReached);
        files.forEach((file, index) => {
            const card = document.createElement('article'); card.className = 'ai-compose-attachment';
            const thumb = document.createElement('div'); thumb.className = 'ai-compose-attachment-thumb';
            const fileType = attachmentTypeLabel(file);
            thumb.textContent = fileType;
            if (file.type.startsWith('image/')) {
                card.classList.add('is-image');
                const objectUrl = URL.createObjectURL(file); attachmentObjectUrls.push(objectUrl);
                thumb.textContent = ''; thumb.style.backgroundImage = `url(${objectUrl})`;
            }
            const copy = document.createElement('div'); copy.className = 'ai-compose-attachment-copy';
            const name = document.createElement('span'); name.className = 'ai-compose-attachment-name'; name.textContent = file.name;
            const details = document.createElement('span'); details.className = 'ai-compose-attachment-meta'; details.textContent = `${fileType} · ${(file.size / 1024 / 1024).toFixed(2)} MB`;
            copy.append(name, details);
            const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'ai-compose-attachment-remove'; remove.setAttribute('aria-label', @json(__('Remove attachment'))); remove.textContent = '×';
            remove.addEventListener('click', () => {
                selectedAttachments = selectedAttachments.filter((_, fileIndex) => fileIndex !== index);
                setAttachmentFiles(selectedAttachments);
                showAttachments();
            });
            card.append(thumb, copy, remove); attachmentPreview.appendChild(card);
        });
        if (limitReached) {
            const notice = document.createElement('span'); notice.className = 'ai-compose-attachment-limit'; notice.textContent = @json(__('Only the first 10 files were selected.'));
            attachmentPreview.appendChild(notice);
        }
    };
    const clearComposerAttachments = () => {
        selectedAttachments = [];
        attachmentObjectUrls.forEach(url => URL.revokeObjectURL(url));
        attachmentObjectUrls = [];
        if (attachmentInput) attachmentInput.value = '';
        attachmentPreview?.replaceChildren();
        attachmentPreview?.classList.remove('is-visible');
    };
    attachmentInput?.addEventListener('change', () => {
        const incomingFiles = Array.from(attachmentInput.files || []);
        const seen = new Set(selectedAttachments.map(file => `${file.name}:${file.size}:${file.lastModified}:${file.type}`));
        incomingFiles.forEach(file => {
            const key = `${file.name}:${file.size}:${file.lastModified}:${file.type}`;
            if (!seen.has(key)) { selectedAttachments.push(file); seen.add(key); }
        });
        const limitReached = selectedAttachments.length > 10;
        selectedAttachments = selectedAttachments.slice(0, 10);
        setAttachmentFiles(selectedAttachments);
        showAttachments(limitReached);
    });
    const programReportFileKey = file => `${file.name}:${file.size}:${file.lastModified}:${file.type}`;
    const programReportFileSize = bytes => {
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };
    const setProgramReportInputFiles = (inputNode, files) => {
        if (!inputNode || typeof DataTransfer === 'undefined') return;
        const transfer = new DataTransfer();
        files.forEach(file => transfer.items.add(file));
        inputNode.files = transfer.files;
    };
    const createProgramReportAttachment = (file, index, kind) => {
        const card = document.createElement('article');
        card.className = 'program-report-attachment';

        const preview = document.createElement('span');
        preview.className = 'program-report-attachment-preview';
        if (kind === 'image') {
            const imageUrl = URL.createObjectURL(file);
            programReportImageUrls.push(imageUrl);
            const image = document.createElement('img');
            image.src = imageUrl;
            image.alt = '';
            preview.appendChild(image);
        } else {
            preview.textContent = (file.name.split('.').pop() || 'FILE').slice(0, 5).toUpperCase();
        }

        const details = document.createElement('span');
        const name = document.createElement('span');
        name.className = 'program-report-attachment-name';
        name.textContent = file.name;
        const size = document.createElement('span');
        size.className = 'program-report-attachment-size';
        size.textContent = programReportFileSize(file.size);
        details.append(name, size);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'program-report-attachment-remove';
        remove.setAttribute('aria-label', `${@json(__('Remove'))} ${file.name}`);
        remove.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>';
        remove.addEventListener('click', () => {
            if (kind === 'image') {
                programReportSelectedImages.splice(index, 1);
                setProgramReportInputFiles(programReportImages, programReportSelectedImages);
                renderProgramReportImages();
            } else {
                programReportPaperwork.value = '';
                renderProgramReportPaperwork();
            }
        });

        card.append(preview, details, remove);
        return card;
    };
    const renderProgramReportPaperwork = () => {
        if (!programReportPaperworkPreview) return;
        programReportPaperworkPreview.replaceChildren();
        const file = programReportPaperwork?.files?.[0];
        programReportPaperworkPreview.classList.toggle('is-empty', !file);
        if (file) {
            programReportPaperworkPreview.appendChild(createProgramReportAttachment(file, 0, 'paperwork'));
            return;
        }
        const empty = document.createElement('p');
        empty.className = 'program-report-attachments-empty';
        empty.textContent = @json(__('Drop a PDF or DOCX here, or click Add paperwork.'));
        programReportPaperworkPreview.appendChild(empty);
    };
    const renderProgramReportImages = () => {
        if (!programReportImagesPreview) return;
        programReportImageUrls.forEach(url => URL.revokeObjectURL(url));
        programReportImageUrls = [];
        programReportImagesPreview.replaceChildren();
        programReportImagesPreview.classList.toggle('is-empty', programReportSelectedImages.length === 0);
        if (programReportSelectedImages.length) {
            programReportSelectedImages.forEach((file, index) => programReportImagesPreview.appendChild(createProgramReportAttachment(file, index, 'image')));
        } else {
            const empty = document.createElement('p');
            empty.className = 'program-report-attachments-empty';
            empty.textContent = @json(__('Drop activity images here, or click Add images.'));
            programReportImagesPreview.appendChild(empty);
        }
        if (programReportImagesStatus) {
            programReportImagesStatus.classList.remove('is-error');
            programReportImagesStatus.textContent = programReportSelectedImages.length
                ? `${programReportSelectedImages.length} / 8 ${@json(__('images selected'))}`
                : @json(__('Add up to 8 activity images. You can choose more images again without replacing the current selection.'));
        }
    };
    const addProgramReportImages = incoming => {
        const seen = new Set(programReportSelectedImages.map(programReportFileKey));
        let rejectedForLimit = 0;
        let rejectedForType = 0;
        incoming.forEach(file => {
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                rejectedForType += 1;
                return;
            }
            const key = programReportFileKey(file);
            if (!seen.has(key) && programReportSelectedImages.length < 8) {
                programReportSelectedImages.push(file);
                seen.add(key);
            } else if (!seen.has(key)) {
                rejectedForLimit += 1;
            }
        });
        setProgramReportInputFiles(programReportImages, programReportSelectedImages);
        renderProgramReportImages();
        if ((rejectedForLimit > 0 || rejectedForType > 0) && programReportImagesStatus) {
            programReportImagesStatus.classList.add('is-error');
            programReportImagesStatus.textContent = rejectedForType > 0
                ? @json(__('Only JPG, PNG, and WEBP images are accepted. Unsupported files were not added.'))
                : @json(__('Maximum 8 activity images. Extra files were not added.'));
        }
    };
    programReportPaperwork?.addEventListener('change', renderProgramReportPaperwork);
    programReportImages?.addEventListener('change', () => {
        addProgramReportImages(Array.from(programReportImages.files || []));
    });
    const bindProgramReportDropZone = (zone, inputNode, kind) => {
        if (!zone || !inputNode) return;
        const stopDragEvent = event => {
            event.preventDefault();
            event.stopPropagation();
        };
        ['dragenter', 'dragover'].forEach(type => zone.addEventListener(type, event => {
            stopDragEvent(event);
            zone.classList.add('is-dragging');
            if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy';
        }));
        ['dragleave', 'drop'].forEach(type => zone.addEventListener(type, event => {
            stopDragEvent(event);
            zone.classList.remove('is-dragging');
        }));
        zone.addEventListener('drop', event => {
            const files = Array.from(event.dataTransfer?.files || []);
            if (!files.length) return;
            if (kind === 'images') {
                addProgramReportImages(files);
                return;
            }
            const file = files[0];
            const isPaperwork = file.type === 'application/pdf'
                || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                || /\.(pdf|docx)$/i.test(file.name);
            if (!isPaperwork) {
                renderProgramReportPaperwork();
                const error = document.createElement('p');
                error.className = 'program-report-attachments-empty is-error';
                error.textContent = @json(__('Only PDF or DOCX paperwork is accepted.'));
                programReportPaperworkPreview.appendChild(error);
                return;
            }
            setProgramReportInputFiles(programReportPaperwork, [file]);
            renderProgramReportPaperwork();
        });
        zone.addEventListener('click', event => {
            if (!event.target.closest('.program-report-attachment-remove')) inputNode.click();
        });
        zone.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                inputNode.click();
            }
        });
    };
    bindProgramReportDropZone(programReportPaperworkPreview, programReportPaperwork, 'paperwork');
    bindProgramReportDropZone(programReportImagesPreview, programReportImages, 'images');
    renderProgramReportPaperwork();
    renderProgramReportImages();
    const setProgramReportOpen = open => {
        programReportDialog?.classList.toggle('is-open', open);
        programReportDialog?.setAttribute('aria-hidden', open ? 'false' : 'true');
    };
    const programReportSavedPaperwork = document.getElementById('programReportSavedPaperwork');
    const syncProgramReportRequirements = () => {
        const requiresPaperwork = programReportProgram?.selectedOptions?.[0]?.dataset.requiresPaperwork === '1';
        const hasSavedPaperwork = Boolean(programReportSavedPaperwork?.value);
        if (programReportPaperwork) programReportPaperwork.required = requiresPaperwork && !hasSavedPaperwork;
        if (programReportPaperworkField) programReportPaperworkField.hidden = !requiresPaperwork && Boolean(programReportProgram?.value);
    };
    programReportProgram?.addEventListener('change', syncProgramReportRequirements);
    programReportSavedPaperwork?.addEventListener('change', syncProgramReportRequirements);
    programReportShortcut?.addEventListener('click', () => setProgramReportOpen(true));
    document.querySelectorAll('[data-program-report-close]').forEach(button => button.addEventListener('click', () => setProgramReportOpen(false)));
    const programReportCompleteDialog = document.getElementById('programReportCompleteDialog');
    document.querySelectorAll('[data-program-report-complete-close]').forEach(button => button.addEventListener('click', () => {
        programReportCompleteDialog?.classList.remove('is-open');
        programReportCompleteDialog?.setAttribute('aria-hidden', 'true');
    }));
    programReportForm?.addEventListener('submit', event => {
        const action = programReportProgram?.selectedOptions?.[0]?.dataset.action;
        if (!action) { event.preventDefault(); return; }
        programReportForm.action = action;
        programReportSubmit.disabled = true;
        programReportSubmit.textContent = @json(__('Generating...'));
        programReportProgress?.classList.add('is-active');
        const progressSteps = Array.from(programReportProgress?.querySelectorAll('[data-report-progress-step]') || []);
        progressSteps.forEach(step => step.classList.remove('is-active', 'is-done'));
        progressSteps[0]?.classList.add('is-active');
        [900, 2200, 4200].forEach((delay, index) => window.setTimeout(() => {
            progressSteps[index]?.classList.remove('is-active');
            progressSteps[index]?.classList.add('is-done');
            progressSteps[index + 1]?.classList.add('is-active');
        }, delay));
    });
    const requestedProgramReport = new URLSearchParams(window.location.search).get('program_report');
    if (requestedProgramReport && programReportProgram) {
        const option = Array.from(programReportProgram.options).find(item => item.value === requestedProgramReport);
        if (option) {
            programReportProgram.value = requestedProgramReport;
            syncProgramReportRequirements();
            setProgramReportOpen(true);
        }
    }

    // --- Paperwork Generator Handlers ---
    const setPaperworkOpen = open => {
        paperworkDialog?.classList.toggle('is-open', open);
        paperworkDialog?.setAttribute('aria-hidden', open ? 'false' : 'true');
    };
    paperworkShortcut?.addEventListener('click', () => setPaperworkOpen(true));
    document.querySelectorAll('[data-paperwork-close]').forEach(button => button.addEventListener('click', () => setPaperworkOpen(false)));
    document.querySelectorAll('[data-paperwork-complete-close]').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('paperworkCompleteDialog')?.classList.remove('is-open');
            document.getElementById('paperworkCompleteDialog')?.setAttribute('aria-hidden', 'true');
        });
    });

    paperworkPresetProgram?.addEventListener('change', () => {
        const opt = paperworkPresetProgram.selectedOptions[0];
        if (!opt || !opt.value) {
            if (paperworkProgramId) paperworkProgramId.value = '';
            return;
        }
        if (paperworkProgramId) paperworkProgramId.value = opt.value;
        if (paperworkTitle && opt.dataset.title) paperworkTitle.value = opt.dataset.title;
        if (paperworkVenue && opt.dataset.venue) paperworkVenue.value = opt.dataset.venue;
        if (paperworkDate && opt.dataset.date) paperworkDate.value = opt.dataset.date;
    });

    paperworkAjkFile?.addEventListener('change', () => {
        const file = paperworkAjkFile.files?.[0];
        if (paperworkAjkFileName) {
            if (file) {
                paperworkAjkFileName.style.display = 'block';
                paperworkAjkFileName.textContent = `✓ ${file.name} (${programReportFileSize(file.size)})`;
            } else {
                paperworkAjkFileName.style.display = 'none';
                paperworkAjkFileName.textContent = '';
            }
        }
    });

    paperworkForm?.addEventListener('submit', () => {
        if (paperworkSubmit) {
            paperworkSubmit.disabled = true;
            paperworkSubmit.textContent = @json(__('Generating Paperwork...'));
        }
        paperworkProgress?.classList.add('is-active');
        const progressSteps = Array.from(paperworkProgress?.querySelectorAll('[data-paperwork-progress-step]') || []);
        progressSteps.forEach(step => step.classList.remove('is-active', 'is-done'));
        progressSteps[0]?.classList.add('is-active');
        [1000, 2500, 4500].forEach((delay, index) => window.setTimeout(() => {
            progressSteps[index]?.classList.remove('is-active');
            progressSteps[index]?.classList.add('is-done');
            progressSteps[index + 1]?.classList.add('is-active');
        }, delay));
    });

    // --- History Tabs & Loaders ---
    const historyTabs = document.querySelectorAll('.ai-history-tab');
    const historyChatsView = document.getElementById('aiHistoryChatsView');
    const historyPaperworkView = document.getElementById('aiHistoryPaperworkView');
    const historyReportsView = document.getElementById('aiHistoryReportsView');
    const paperworkHistoryUrl = @json(route($lecturerAiMode ? 'lecturer.ai-helper.paperwork.history' : 'admin.ai-helper.paperwork.history'));
    const reportsHistoryUrl = @json(route($lecturerAiMode ? 'lecturer.ai-helper.reports.history' : 'admin.ai-helper.reports.history'));

    const loadPaperworkHistory = async () => {
        const list = document.getElementById('aiPaperworkHistoryList');
        if (!list) return;
        list.innerHTML = '<div style="padding:1rem;text-align:center;color:var(--text-muted);font-size:0.78rem;">' + @json(__('Loading records...')) + '</div>';
        try {
            const res = await fetch(paperworkHistoryUrl, {headers:{'Accept':'application/json'},credentials:'same-origin'});
            const data = await res.json();
            const items = data.items || [];
            if (!items.length) {
                list.innerHTML = '<p class="ai-history-empty">' + @json(__('No paperwork history found.')) + '</p>';
                return;
            }
            list.innerHTML = '';
            items.forEach(item => {
                const card = document.createElement('div');
                card.className = 'ai-history-item';
                card.style.cssText = 'padding:0.75rem 0.85rem;border:1px solid rgba(255,255,255,0.08);border-radius:12px;margin-bottom:0.6rem;background:rgba(255,255,255,0.03);display:block;';
                card.innerHTML = `
                    <div style="font-weight:750;font-size:0.82rem;line-height:1.35;margin-bottom:4px;color:var(--text);">${item.title}</div>
                    <div style="font-size:0.68rem;color:var(--text-muted);margin-bottom:8px;">${item.date_text || ''} · ${item.venue || ''} · <span title="${item.created_at_date}">${item.created_at}</span></div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                        ${item.docx_url ? `<a href="${item.docx_url}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:var(--se-primary);color:#fff;font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> DOCX</a>` : ''}
                        ${item.pdf_url ? `<a href="${item.pdf_url}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:rgba(255,255,255,0.12);color:var(--text);font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12h4"/><path d="M10 16h4"/></svg> PDF</a>` : ''}
                        <button type="button" data-delete-paperwork="${item.id}" style="margin-left:auto;border:none;background:transparent;color:#ef4444;font-size:0.85rem;cursor:pointer;padding:2px 6px;display:flex;align-items:center;" title="${@json(__('Delete'))}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                    </div>
                `;
                list.appendChild(card);
            });
        } catch (e) {
            list.innerHTML = '<p class="ai-history-empty" style="color:#ef4444;">' + @json(__('Failed to load records.')) + '</p>';
        }
    };

    document.getElementById('aiPaperworkHistoryList')?.addEventListener('click', async (event) => {
        const delBtn = event.target.closest('[data-delete-paperwork]');
        if (!delBtn) return;
        const id = delBtn.dataset.deletePaperwork;
        if (!confirm(@json(__('Are you sure you want to delete this paperwork?')))) return;
        try {
            const deleteUrl = `${root.dataset.conversationsUrl.replace('/conversations', '')}/paperwork/${id}`;
            const res = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {'Accept':'application/json','X-CSRF-TOKEN':csrfToken},
                credentials: 'same-origin'
            });
            if (res.ok) {
                delBtn.closest('.ai-history-item')?.remove();
            }
        } catch (_) {}
    });

    const loadReportsHistory = async () => {
        const list = document.getElementById('aiReportsHistoryList');
        if (!list) return;
        list.innerHTML = '<div style="padding:1rem;text-align:center;color:var(--text-muted);font-size:0.78rem;">' + @json(__('Loading records...')) + '</div>';
        try {
            const res = await fetch(reportsHistoryUrl, {headers:{'Accept':'application/json'},credentials:'same-origin'});
            const data = await res.json();
            const items = data.items || [];
            if (!items.length) {
                list.innerHTML = '<p class="ai-history-empty">' + @json(__('No program report history found.')) + '</p>';
                return;
            }
            list.innerHTML = '';
            items.forEach(item => {
                const card = document.createElement('div');
                card.className = 'ai-history-item';
                card.style.cssText = 'padding:0.75rem 0.85rem;border:1px solid rgba(255,255,255,0.08);border-radius:12px;margin-bottom:0.6rem;background:rgba(255,255,255,0.03);display:block;';
                card.innerHTML = `
                    <div style="font-weight:750;font-size:0.82rem;line-height:1.35;margin-bottom:4px;color:var(--text);">${item.title}</div>
                    <div style="font-size:0.68rem;color:var(--text-muted);margin-bottom:8px;">${item.venue || ''} · <span style="text-transform:uppercase;font-weight:700;color:var(--se-primary);">${item.status || 'draft'}</span> · <span title="${item.created_at_date}">${item.created_at}</span></div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                        ${item.docx_url ? `<a href="${item.docx_url}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:var(--se-primary);color:#fff;font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> DOCX</a>` : ''}
                        ${item.pdf_url ? `<a href="${item.pdf_url}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:rgba(255,255,255,0.12);color:var(--text);font-size:0.7rem;font-weight:750;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M10 12h4"/><path d="M10 16h4"/></svg> PDF</a>` : ''}
                        ${item.operations_url ? `<a href="${item.operations_url}" style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;background:rgba(255,255,255,0.08);color:var(--text);font-size:0.7rem;font-weight:600;text-decoration:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> ${@json(__('Operations'))}</a>` : ''}
                    </div>
                `;
                list.appendChild(card);
            });
        } catch (e) {
            list.innerHTML = '<p class="ai-history-empty" style="color:#ef4444;">' + @json(__('Failed to load records.')) + '</p>';
        }
    };

    const switchHistoryTab = (tabName) => {
        historyTabs.forEach(t => t.classList.toggle('is-active', t.dataset.historyTab === tabName));
        if (historyChatsView) historyChatsView.style.display = tabName === 'chats' ? 'flex' : 'none';
        if (historyPaperworkView) historyPaperworkView.style.display = tabName === 'paperwork' ? 'flex' : 'none';
        if (historyReportsView) historyReportsView.style.display = tabName === 'reports' ? 'flex' : 'none';
        if (tabName === 'paperwork') loadPaperworkHistory();
        if (tabName === 'reports') loadReportsHistory();
    };

    historyTabs.forEach(tab => {
        tab.addEventListener('click', () => switchHistoryTab(tab.dataset.historyTab));
    });

    document.getElementById('refreshPaperworkHistory')?.addEventListener('click', loadPaperworkHistory);
    document.getElementById('refreshReportsHistory')?.addEventListener('click', loadReportsHistory);

    const setBusy = (busy) => {
        requestInFlight = busy;
        sendBtn.disabled = busy || root.dataset.aiEnabled !== '1';
        document.querySelectorAll('.task-btn').forEach((button) => button.disabled = busy);
        input.disabled = busy;
        chatLog.querySelectorAll('[data-regenerate-ai-message]').forEach(button => {
            button.disabled = busy || !lastRequest;
            button.setAttribute('aria-busy', busy ? 'true' : 'false');
        });
    };

    const send = async (message = input.value.trim(), template = null, context = composerSelectionContext) => {
        if (requestInFlight || !message || root.dataset.aiEnabled !== '1') return;

        const selectedContext = context;
        lastRequest = { message, template, filters: filters(), selectedContext };
        if (!root.classList.contains('has-chat')) {
            root.classList.add('has-chat');
            chatLog.innerHTML = '';
        }
        // Snapshot the File objects for the request and move their visual cards
        // out of the composer before waiting for the AI response.
        const sentAttachments = [...selectedAttachments];
        clearComposerAttachments();
        addMessage('user', message, '', sentAttachments);
        input.value = '';
        setBusy(true);
        const loading = addMessage('ai loading', aiText.thinking);

        try {
            const requestBody = new FormData();
            requestBody.append('message', lastRequest.message);
            if (selectedContext) requestBody.append('selected_context', selectedContext);
            if (currentConversationId) requestBody.append('conversation_id', currentConversationId);
            if (lastRequest.template) requestBody.append('template', lastRequest.template);
            Object.entries(lastRequest.filters).forEach(([key, value]) => {
                if (value) requestBody.append(`filters[${key}]`, value);
            });
            sentAttachments.forEach(attachment => requestBody.append('attachments[]', attachment));
            if (selectedContext) composeContextClear?.click();

            const response = await fetch(root.dataset.aiUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                credentials: 'same-origin',
                body: requestBody,
            });

            const payload = await response.json().catch(() => ({}));
            loading.remove();

            if (!response.ok) {
                throw new Error(payload.message || aiText.failed);
            }

            lastAnswer = payload.answer || '';
            if (payload.conversation) {
                currentConversationId = payload.conversation.id;
                rememberActiveConversation(currentConversationId);
                upsertConversation(payload.conversation);
            }
            addMessage('ai', lastAnswer || aiText.empty, '', [], payload.assistant_message_id);
        } catch (error) {
            loading.remove();
            addMessage('error', error.message || aiText.unreachable);
        } finally {
            setBusy(false);
            input.focus();
        }
    };

    resetChat(false);
    const activeConversationId = rememberedConversation();
    if (activeConversationId) {
        loadConversation(activeConversationId).catch(() => resetChat());
    }

    sendBtn.addEventListener('click', () => send());
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            send();
        }
    });

    root.addEventListener('click', event => {
        const button = event.target.closest('[data-template]');
        if (!button || !root.contains(button)) return;
        const template = button.dataset.template;
        input.value = template;
        send(template, template);
    });

})();
</script>
@endpush
