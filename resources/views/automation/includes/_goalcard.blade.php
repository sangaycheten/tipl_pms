@php
    $halfYearLabel = match(true) {
        (bool)$goal->InH1 && (bool)$goal->InH2 => 'H1 & H2',
        (bool)$goal->InH1                       => 'H1',
        (bool)$goal->InH2                       => 'H2',
        default                                 => 'Full Year',
    };
    $taskWeightageSum = $goal->targets->sum('Weightage');
    $hasMismatch      = round($taskWeightageSum, 2) != round($goal->Weightage, 2);
    $pct              = $goal->Weightage > 0
                            ? min(100, ($taskWeightageSum / $goal->Weightage) * 100)
                            : 0;


@endphp

<div class="goal-card {{ $isSubmitted ? 'view-only' : '' }}">
    <div class="goal-meta">
        <span class="goal-number">#{{ $goalNumber }}</span>
        <span class="badge-half">{{ $halfYearLabel }}</span>
        <span class="badge-year">{{ $goal->Year }}</span>
        @if((int)$goal->GoalType === 2)
            <span class="badge-common-goal"><i class="fa fa-users mr-1"></i>Common Goal</span>
        @else
            <span class="badge-section-goal"><i class="fa fa-sitemap mr-1"></i>Section Goal</span>
        @endif
    </div>
    <div class="goal-title">{{ $goal->Description }}</div>
    <div class="goal-stats">
        Total Score: <strong>{{ number_format($goal->Weightage, 2) }}</strong>
        &nbsp;&bull;&nbsp;
        {{ $goal->targets->count() }} task(s)
        &nbsp;&bull;&nbsp;
        Task weightage sum: <strong>{{ number_format($taskWeightageSum, 2) }}</strong>
        @if($hasMismatch)
            <span class="text-warn-mismatch ml-2">
                <i class="fa fa-exclamation-triangle"></i> Weightage mismatch
            </span>
        @endif
    </div>
    <div class="score-bar-wrap">
        <div class="score-bar-fill" style="width:{{ $pct }}%;"></div>
    </div>

    @if($goal->targets->isNotEmpty())
        <div class="table-responsive mt-3">
            <table class="table table-sm table-bordered task-table mb-0">
                <thead>
                    <tr>
                        <th style="width:36px;">#</th>
                        <th>Task Description</th>
                        <th style="width:90px;" class="text-right">Weightage</th>
                        <th style="width:120px;">Target</th>
                        @if($isSubmitted)
                            <th style="width:130px;">Achievement</th>
                            <th style="width:105px;" class="text-right">Self Score</th>
                        @endif
                        @if($isAppraised)
                            <th style="width:105px;" class="text-right">L1 Score</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($goal->targets as $task)
                        @php
                            $l1Score = null;
                            if ($isAppraised) {
                                $l1Score = $isMultiple
                                    ? ($l1AvgScores->get($task->Id)?->AvgL1Score ?? null)
                                    : $task->Level1Score;
                            }
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>{{ $task->Description }}</td>
                            <td class="text-right">{{ number_format($task->Weightage, 2) }}</td>
                            <td>
                                @if($task->Target)
                                    <span class="target-badge">{{ $task->Target }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @if($isSubmitted)
                                <td>
                                    @if($task->Achievement)
                                        <span style="background:#fff8e1;color:#f57f17;padding:2px 7px;border-radius:4px;font-size:0.78rem;font-weight:600;">
                                            {{ $task->Achievement }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <span style="background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:4px;font-size:0.82rem;font-weight:600;">
                                        {{ $task->SelfScore !== null ? number_format($task->SelfScore, 2) : '—' }}
                                    </span>
                                </td>
                            @endif
                            @if($isAppraised)
                                <td class="text-right">
                                    @if($l1Score !== null)
                                        <span style="background:#e3f2fd;color:#1565c0;padding:2px 8px;border-radius:4px;font-size:0.82rem;font-weight:600;">
                                            {{ number_format($l1Score, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                @if($isSubmitted || $isAppraised)
                    <tfoot>
                        <tr style="background:#f8f9fa;">
                            {{-- Label spans: #, Description, Weightage --}}
                            <td colspan="3" class="text-right"
                                style="font-size:0.79rem;color:#777;font-weight:700;border-top:2px solid #dee2e6;">
                                Totals
                            </td>
                            {{-- Target: blank --}}
                            <td style="border-top:2px solid #dee2e6;"></td>
                            @if($isSubmitted)
                                {{-- Achievement: blank --}}
                                <td style="border-top:2px solid #dee2e6;"></td>
                                {{-- Self Score total --}}
                                <td class="text-right" style="border-top:2px solid #dee2e6;">
                                    <span style="background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:4px;font-size:0.82rem;font-weight:700;">
                                        {{ number_format($goal->targets->sum('SelfScore'), 2) }}
                                    </span>
                                </td>
                            @endif
                            @if($isAppraised)
                                @php
                                    $l1Total = $isMultiple
                                        ? $goal->targets->sum(fn($t) => $l1AvgScores->get($t->Id)?->AvgL1Score ?? 0)
                                        : $goal->targets->sum('Level1Score');
                                @endphp
                                {{-- L1 Score total --}}
                                <td class="text-right" style="border-top:2px solid #dee2e6;">
                                    <span style="background:#e3f2fd;color:#1565c0;padding:2px 8px;border-radius:4px;font-size:0.82rem;font-weight:700;">
                                        {{ number_format($l1Total, 2) }}
                                    </span>
                                </td>
                            @endif
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif
</div>
