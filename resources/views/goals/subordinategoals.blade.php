@php
    // Group flat rows by goal Id, preserving display order
    $goals = $goalTargets->groupBy('Id')->sortBy(fn($rows) => $rows->first()->DisplayOrder);
@endphp

<style>
    .sg-modal-body   { background:#f4f6f9; padding:20px; }
    .sg-goal-card    { background:#fff; border-radius:7px; border-left:4px solid #2196F3;
                       box-shadow:0 1px 4px rgba(0,0,0,0.09); margin-bottom:16px; overflow:hidden; }
    .sg-goal-header  { padding:12px 16px 8px; border-bottom:1px solid #f0f2f5; }
    .sg-goal-title   { font-weight:700; font-size:0.97rem; color:#1a1a2e; margin-bottom:3px; }
    .sg-goal-meta    { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .sg-badge        { display:inline-block; padding:2px 9px; border-radius:12px; font-size:0.74rem; font-weight:600; }
    .sg-badge-score  { background:#e3f0ff; color:#1565c0; }
    .sg-badge-cycle  { background:#e8f5e9; color:#2e7d32; }
    .sg-table        { margin-bottom:0 !important; }
    .sg-table thead th {
        background:#f8f9fa !important; color:#555 !important;
        font-size:0.77rem !important; font-weight:600 !important;
        padding:7px 10px !important; border-color:#eee !important;
        white-space:nowrap;
    }
    .sg-table tbody td {
        font-size:0.83rem !important; color:#333 !important;
        background:#fff !important; padding:7px 10px !important;
        border-color:#f0f2f5 !important; vertical-align:middle !important;
    }
    .sg-table tfoot td {
        background:#f8f9fa !important; font-weight:700 !important;
        font-size:0.82rem !important; color:#333 !important;
        border-top:2px solid #dee2e6 !important; padding:7px 10px !important;
    }
    .sg-pill         { display:inline-block; padding:1px 7px; border-radius:4px; font-size:0.78rem; font-weight:600; }
    .sg-pill-self    { background:#e8f5e9; color:#2e7d32; }
    .sg-pill-l1      { background:#e3f2fd; color:#1565c0; }
    .sg-pill-target  { background:#fff8e1; color:#e65100; }
    .sg-remarks      { font-size:0.78rem; color:#777; font-style:italic; }
    .sg-summary-bar  { display:flex; gap:24px; padding:10px 16px; background:#f8f9fa;
                       border-top:1px solid #eee; font-size:0.82rem; flex-wrap:wrap; }
    .sg-summary-bar strong { color:#333; }
</style>

<div class="modal-header" style="background:#1976D2; border:none; padding:14px 20px;">
    <h5 class="modal-title" style="color:#fff; font-size:0.97rem; font-weight:700; margin:0;">
        <i class="fa fa-bullseye mr-2"></i> Performance Goals &mdash; {{ $Employee ?? 'Employee' }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:0.85;">
        <span>&times;</span>
    </button>
</div>

<div class="modal-body sg-modal-body">

    @if($goalTargets->isEmpty())
        <div class="text-center text-muted py-4">
            <i class="fa fa-info-circle fa-2x mb-2 d-block" style="opacity:.4;"></i>
            No goals found.
        </div>
    @else
        @foreach($goals as $goalId => $rows)
            @php
                $first       = $rows->first();
                $selfTotal   = $rows->sum(fn($r) => (float)$r->SelfScore);
                $l1Total     = $rows->sum(fn($r) => (float)$r->Level1Score);
                $weightTotal = $rows->sum(fn($r) => (float)$r->TargetWeightage);
            @endphp

            <div class="sg-goal-card">
                <div class="sg-goal-header">
                    <div class="sg-goal-meta mb-1">
                        <span class="sg-badge sg-badge-cycle">{{ $first->GoalTarget }}</span>
                        <span class="sg-badge sg-badge-score">Score: {{ number_format($first->GoalWeightage, 2) }}</span>
                    </div>
                    <div class="sg-goal-title">{{ $first->GoalDescription }}</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered sg-table">
                        <thead>
                            <tr>
                                <th style="width:32px;" class="text-center">#</th>
                                <th>Task Description</th>
                                <th style="width:80px;" class="text-right">Weight</th>
                                <th style="width:80px;" class="text-center">Target</th>
                                <th style="width:90px;" class="text-right">Self Score</th>
                                <th style="min-width:130px;">Self Remarks</th>
                                <th style="width:90px;" class="text-right">L1 Score</th>
                                <th style="min-width:130px;">L1 Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $i => $row)
                                <tr>
                                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                                    <td>{{ $row->TargetDescription }}</td>
                                    <td class="text-right">{{ number_format($row->TargetWeightage, 2) }}</td>
                                    <td class="text-center">
                                        @if($row->TargetValue && $row->TargetValue !== '-')
                                            <span class="sg-pill sg-pill-target">{{ $row->TargetValue }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <span class="sg-pill sg-pill-self">
                                            {{ $row->SelfScore !== null ? number_format($row->SelfScore, 2) : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="sg-remarks">{{ $row->SelfRemarks ?: '—' }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="sg-pill sg-pill-l1">
                                            {{ $row->Level1Score !== null ? number_format($row->Level1Score, 2) : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="sg-remarks">{{ $row->Level1Remarks ?: '—' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-right" style="color:#666;">Totals</td>
                                <td class="text-right">{{ number_format($weightTotal, 2) }}</td>
                                <td></td>
                                <td class="text-right">
                                    <span class="sg-pill sg-pill-self">{{ number_format($selfTotal, 2) }}</span>
                                </td>
                                <td></td>
                                <td class="text-right">
                                    <span class="sg-pill sg-pill-l1">{{ number_format($l1Total, 2) }}</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach

        {{-- Overall summary --}}
        @php
            $grandSelf = $goalTargets->sum(fn($r) => (float)$r->SelfScore);
            $grandL1   = $goalTargets->sum(fn($r) => (float)$r->Level1Score);
            $grandW    = $goals->sum(fn($rows) => (float)$rows->first()->GoalWeightage);
        @endphp
        <div class="sg-goal-card" style="border-left-color:#43a047; margin-bottom:0;">
            <div class="sg-summary-bar">
                <span>Total Goal Score:&nbsp;<strong>{{ number_format($grandW, 2) }}</strong></span>
                <span style="color:#2e7d32;">
                    <i class="fa fa-user mr-1"></i> Self Total:&nbsp;<strong>{{ number_format($grandSelf, 2) }}</strong>
                </span>
                <span style="color:#1565c0;">
                    <i class="fa fa-star mr-1"></i> L1 Total:&nbsp;<strong>{{ number_format($grandL1, 2) }}</strong>
                </span>
            </div>
        </div>
    @endif

</div>

<div class="modal-footer" style="background:#fff; border-top:1px solid #f0f2f5; padding:10px 20px;">
    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
        <i class="fa fa-times mr-1"></i> Close
    </button>
</div>
