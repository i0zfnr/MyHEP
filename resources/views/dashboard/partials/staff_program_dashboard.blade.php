@php
    $programCounts = $programDashboard['counts'] ?? [];
    $programStatuses = collect($programDashboard['status_distribution'] ?? []);
    $programTotal = max(1, (int) ($programCounts['total'] ?? 0));
    $statusLabels = [
        'draft' => __('Draft'), 'pending_deputy' => __('Deputy Review'),
        'pending_director' => __('Director Approval'), 'approved' => __('Approved'),
        'in_progress' => __('In Progress'), 'completed' => __('Completed'), 'rejected' => __('Rejected'),
    ];
@endphp

<section class="staff-program-board" aria-labelledby="staffProgramTitle">
    <div class="program-board-head">
        <div><span>{{ __('Program Management') }}</span><h2 id="staffProgramTitle">{{ __('My Program Overview') }}</h2><p>{{ __('Live progress for programs you direct and approvals assigned to you.') }}</p></div>
        <a href="{{ route('admin.programs.index') }}" class="btn-ghost">{{ __('Open Program Management') }} →</a>
    </div>

    <div class="stats-grid">
        @foreach([
            ['total_students', __('Total Students'), 'accent'],
            ['total', __('My Programs'), 'blue'], ['draft', __('Drafts'), 'gold'],
            ['pending_deputy', __('Deputy Review'), 'gold'], ['pending_director', __('Director Approval'), 'red'],
            ['approved', __('Approved'), 'accent'], ['in_progress', __('In Progress'), 'blue'],
            ['completed', __('Completed'), 'gold'], ['review_tasks', __('My Review Tasks'), 'red'],
        ] as [$key, $label, $tone])
            <article class="stat-card {{ $tone }}"><div class="stat-label">{{ $label }}</div><div class="stat-value">{{ number_format($programCounts[$key] ?? 0) }}</div></article>
        @endforeach
    </div>

    <div class="program-viz-grid">
        <article class="program-viz-card">
            <div class="program-viz-title"><span>{{ __('Workflow') }}</span><h3>{{ __('Program Status Distribution') }}</h3></div>
            <div class="program-status-bars">
                @foreach($programStatuses as $item)
                    @php $width = ($item['value'] / $programTotal) * 100; @endphp
                    <div class="program-status-row">
                        <div><span>{{ $statusLabels[$item['status']] ?? __(ucwords(str_replace('_', ' ', $item['status']))) }}</span><strong>{{ $item['value'] }}</strong></div>
                        <div class="program-status-track"><i style="width:{{ $item['value'] > 0 ? max(4, $width) : 0 }}%"></i></div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="program-viz-card">
            <div class="program-viz-title"><span>{{ __('Six Months') }}</span><h3>{{ __('Program Activity') }}</h3></div>
            <div class="program-trend">
                @foreach($programDashboard['trend'] ?? [] as $period)
                    <div class="program-trend-group" title="{{ $period['label'] }}: {{ $period['created'] }} created, {{ $period['approved'] }} approved">
                        <div class="program-trend-stage"><i style="height:{{ $period['created_height'] }}%"></i><i class="approved" style="height:{{ $period['approved_height'] }}%"></i></div>
                        <span>{{ $period['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="program-viz-legend"><span><i></i>{{ __('Created') }}</span><span><i class="approved"></i>{{ __('Approved') }}</span></div>
        </article>
    </div>

    <article class="data-card">
        <div class="data-card-head"><strong>{{ __('Recently Updated Programs') }}</strong><a class="btn-ghost" href="{{ route('admin.programs.index') }}">{{ __('View All') }} →</a></div>
        @if(($programDashboard['recent'] ?? collect())->isEmpty())
            <div class="empty-state">{{ __('No programs yet. Create your first program in Program Management.') }}</div>
        @else
            <div style="overflow-x:auto"><table><thead><tr><th>{{ __('Program') }}</th><th>{{ __('Branch') }}</th><th>{{ __('Program Date') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>
            @foreach($programDashboard['recent'] as $program)
                <tr><td><a href="{{ route('admin.programs.show', $program->id) }}" style="font-weight:700;color:var(--c-text-primary)">{{ $program->title }}</a></td><td>{{ strtoupper($program->approval_branch ?: '-') }}</td><td>{{ $program->starts_at ? \Illuminate\Support\Carbon::parse($program->starts_at)->format('d M Y') : '-' }}</td><td><span class="badge">{{ $statusLabels[$program->status] ?? __(ucwords(str_replace('_', ' ', $program->status))) }}</span></td></tr>
            @endforeach
            </tbody></table></div>
        @endif
    </article>
</section>

@push('styles')
<style>
.staff-program-board{display:grid;gap:1.25rem}.program-board-head{display:flex;align-items:end;justify-content:space-between;gap:1rem}.program-board-head span,.program-viz-title span{color:var(--c-accent);font-size:.7rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.program-board-head h2,.program-viz-title h3{margin:.2rem 0;color:var(--c-text-primary)}.program-board-head p{margin:0;color:var(--c-text-secondary)}.program-viz-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.program-viz-card{background:linear-gradient(145deg,var(--c-surface),color-mix(in srgb,var(--c-accent) 5%,var(--c-surface)));border:1px solid color-mix(in srgb,var(--c-accent) 24%,var(--c-border));border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow-sm)}.program-status-bars{display:grid;gap:.7rem;margin-top:1rem}.program-status-row>div:first-child{display:flex;justify-content:space-between;font-size:.78rem;color:var(--c-text-secondary)}.program-status-track{height:8px;margin-top:.35rem;border-radius:999px;background:var(--c-surface-2);overflow:hidden}.program-status-track i{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,var(--c-accent),color-mix(in srgb,var(--c-accent) 54%,#fff));box-shadow:0 0 14px color-mix(in srgb,var(--c-accent) 45%,transparent)}.program-trend{display:flex;height:190px;align-items:end;gap:.8rem;margin-top:1rem;padding-top:1rem;border-bottom:1px solid var(--c-border)}.program-trend-group{flex:1;display:grid;height:100%;grid-template-rows:1fr auto;gap:.4rem;text-align:center;color:var(--c-text-muted);font-size:.7rem}.program-trend-stage{display:flex;align-items:end;justify-content:center;gap:4px}.program-trend-stage i{width:12px;min-height:2px;border-radius:5px 5px 0 0;background:var(--c-accent);box-shadow:0 0 12px color-mix(in srgb,var(--c-accent) 35%,transparent)}.program-trend-stage i.approved{background:var(--c-gold)}.program-viz-legend{display:flex;gap:1rem;margin-top:.8rem;font-size:.75rem;color:var(--c-text-secondary)}.program-viz-legend span{display:flex;align-items:center;gap:.35rem}.program-viz-legend i{width:8px;height:8px;border-radius:50%;background:var(--c-accent)}.program-viz-legend i.approved{background:var(--c-gold)}@media(max-width:800px){.program-viz-grid{grid-template-columns:1fr}.program-board-head{align-items:start;flex-direction:column}}
</style>
@endpush
