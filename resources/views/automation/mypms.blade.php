@extends('master')
@section('page-title', 'My PMS Goals')
@section('page-header', 'My PMS Goals')

@section('pagestyles')
<style>
    .goal-card {
        background-color: #fff;
        border-radius: 6px;
        border-left: 4px solid #2196F3;
        box-shadow: 0 1px 4px rgba(0,0,0,0.12);
        margin-bottom: 20px;
        padding: 16px 20px;
        color: #333;
    }
    .goal-card.locked { border-left-color: #aaa; opacity: 0.82; }
    .goal-card .goal-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }
    .goal-card .goal-number {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2196F3;
        margin-right: 10px;
    }
    .goal-card.locked .goal-number { color: #888; }
    .goal-card .goal-title {
        font-size: 1rem;
        font-weight: 600;
        color: #222;
        margin-bottom: 4px;
    }
    .goal-card .goal-stats {
        font-size: 0.82rem;
        color: #666;
        margin-bottom: 10px;
    }
    .goal-card .goal-stats strong { color: #333; }
    .badge-half {
        background-color: #e3f2fd;
        color: #1565c0;
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 600;
        margin-right: 6px;
    }
    .badge-year {
        background-color: #f1f8e9;
        color: #33691e;
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 600;
        margin-right: 6px;
    }
    .target-badge {
        display: inline-block;
        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 4px;
        padding: 2px 7px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .goal-toolbar {
        background-color: #fff;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .score-bar-wrap {
        background: #e9ecef;
        border-radius: 4px;
        height: 6px;
        width: 180px;
        margin-top: 6px;
        overflow: hidden;
    }
    .score-bar-fill { background: #2196F3; height: 100%; border-radius: 4px; }
    .self-rating-section {
        background: #f8f9ff;
        border: 1px solid #e3eaff;
        border-radius: 6px;
        padding: 12px 14px;
        margin-top: 14px;
    }
    .self-rating-section label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #444;
        margin-bottom: 4px;
    }
    .self-rating-section .form-control {
        color: #333;
        font-size: 0.85rem;
        background: #fff;
    }
    table.task-table { background-color: #fff; color: #333; font-size: 0.85rem; }
    table.task-table thead th {
        background-color: #226b86;
        color: #fff;
        border-color: #1a5570;
        font-weight: 600;
        font-size: 0.82rem;
    }
    table.task-table td { color: #333; border-color: #dee2e6; vertical-align: middle; }
    table.task-table .self-input { color: #333; font-size: 0.83rem; background: #fff; }
    .goal-self-remarks textarea { resize: vertical; min-height: 60px; }

    /* H1 / H2 section dividers */
    .cycle-section-header {
        display: flex;
        align-items: center;
        margin: 24px 0 14px;
        font-weight: 700;
        font-size: 0.95rem;
        color: #444;
    }
    .cycle-section-header::before,
    .cycle-section-header::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #dee2e6;
        margin: 0 12px;
    }
    .cycle-badge-h1 {
        background: #e3f2fd;
        color: #1565c0;
        padding: 4px 14px;
        border-radius: 14px;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    .cycle-badge-h2 {
        background: #fce4ec;
        color: #c62828;
        padding: 4px 14px;
        border-radius: 14px;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    .cycle-locked-notice {
        display: inline-block;
        background: #f5f5f5;
        color: #777;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
    }
    /* Red border when self-score hits max */
    .score-at-max { border-color: #dc3545 !important; }
    /* Achievement dropdown */
    .task-achievement { font-size: 0.83rem; color: #333; background: #fff; }
    /* Remarks state indicators */
    .remarks-required { border-color: #ff9800 !important; background: #fffde7 !important; }
    .remarks-error    { border-color: #dc3545 !important; background: #fff5f5 !important; }
    /* Goal type source badges */
    .badge-common-goal  { background:#fff3e0; color:#e65100; border:1px solid #ffcc80;
        padding:2px 9px; border-radius:12px; font-size:0.75rem; font-weight:600; margin-right:5px; }
    .badge-section-goal { background:#f3e5f5; color:#6a1b9a; border:1px solid #ce93d8;
        padding:2px 9px; border-radius:12px; font-size:0.75rem; font-weight:600; margin-right:5px; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-12 card" style="padding: 16px 18px; background:#fff; color:#333;">

            @if(Session::has('successmessage'))
                <div class="alert alert-success mb-3">{{ Session::get('successmessage') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @php
                $h1Submitted = $h1Status === 1;
                $h2Submitted = $h2Status === 1;
                $h1Goals     = $goalDetails->filter(fn($g) => (bool)$g->InH1);
                $h2Goals     = $goalDetails->filter(fn($g) => (bool)$g->InH2);

                // Approval status per cycle (compute before locked so inputs respect approval gate)
                // July master covers H1 (and "both" goals); January master covers pure H2
                $julyApp  = $julyMaster ? (int)$julyMaster->ApprovalStatus : 2;
                $julyBy   = $julyMaster ? $julyMaster->GoalSetBy           : null;
                $julyNote = $julyMaster ? $julyMaster->ApprovalRemark      : null;
                $janApp   = $janMaster  ? (int)$janMaster->ApprovalStatus  : 2;
                $janBy    = $janMaster  ? $janMaster->GoalSetBy            : null;
                $janNote  = $janMaster  ? $janMaster->ApprovalRemark       : null;

                // H1 needs approval when its master exists and is not yet approved (any ApprovalStatus ≠ 2).
                // GoalSetBy is NOT checked: supervisor-draft goals are excluded from $goalDetails already,
                // so all masters here are either supervisor-published (ApprovalStatus=2, unlocked) or
                // individual/fallback (any status). Old NULL-GoalSetBy records have ApprovalStatus=2 by
                // DB default and are treated as legacy-approved.
                $h1NeedsApproval = $julyMaster !== null && $julyApp !== 2 && $h1Goals->isNotEmpty();
                // H2 needs approval when July master blocks "both" goals OR January master blocks pure H2
                $h2JulyBlocked   = $julyMaster !== null && $julyApp !== 2
                                   && $h2Goals->filter(fn($g) => (bool)$g->InH1)->isNotEmpty();
                $h2JanBlocked    = $janMaster !== null && $janApp !== 2
                                   && $h2Goals->filter(fn($g) => !(bool)$g->InH1)->isNotEmpty();
                $h2NeedsApproval = $h2JulyBlocked || $h2JanBlocked;

                // Inputs are locked when submitted, outside active cycle, OR awaiting approval
                $h1Locked = $h1Submitted || $activeCycle !== 'H1' || $h1NeedsApproval;
                $h2Locked = $h2Submitted || $activeCycle !== 'H2' || $h2NeedsApproval;

                // Effective approval status to display per cycle
                $h1AppStatus = $h1NeedsApproval ? $julyApp : 2;
                $h1AppNote   = $julyNote;
                $h2AppStatus = $h2NeedsApproval ? ($h2JulyBlocked ? $julyApp : $janApp) : 2;
                $h2AppNote   = $h2JulyBlocked ? $julyNote : $janNote;

                // Whether individual has goals in draft/rejected state that can be submitted for approval
                $anyPendingApprovalSubmit = ($julyBy === 'individual' && $julyApp === 0 && $h1Goals->isNotEmpty())
                                          || ($janBy === 'individual' && $janApp === 0 && $h2Goals->isNotEmpty());
            @endphp

            {{-- ── Approval notices ── --}}
            @if($h1NeedsApproval || $h2NeedsApproval)
                @php
                    $pendingApprovalCycles = collect();
                    if ($h1NeedsApproval && $h1AppStatus === 1) $pendingApprovalCycles->push('H1');
                    if ($h2NeedsApproval && $h2AppStatus === 1) $pendingApprovalCycles->push('H2');
                    $rejectedCycles = collect();
                    if ($h1NeedsApproval && $h1AppStatus === 3) $rejectedCycles->push('H1');
                    if ($h2NeedsApproval && $h2AppStatus === 3) $rejectedCycles->push('H2');
                    $draftCycles = collect();
                    if ($h1NeedsApproval && $h1AppStatus === 0) $draftCycles->push('H1');
                    if ($h2NeedsApproval && $h2AppStatus === 0) $draftCycles->push('H2');
                @endphp
                @if($pendingApprovalCycles->isNotEmpty())
                    <div class="alert alert-warning mb-3" style="font-size:0.88rem;">
                        <i class="fa fa-clock-o mr-1"></i>
                        Your <strong>{{ $pendingApprovalCycles->join(' & ') }}</strong> goals have been submitted and are awaiting your supervisor's approval.
                        You will be able to enter your self-rating once your supervisor approves your goals.
                    </div>
                @endif
                @if($rejectedCycles->isNotEmpty())
                    <div class="alert alert-danger mb-3" style="font-size:0.88rem;">
                        <i class="fa fa-times-circle mr-1"></i>
                        <strong>{{ $rejectedCycles->join(' & ') }} goals were rejected by your supervisor.</strong>
                        Please
                        @if($goalId)
                            <a href="{{ route('goals.edit', $goalId) }}">edit your goals</a>
                        @else
                            <a href="{{ route('goals.create') }}">set your goals</a>
                        @endif
                        then resubmit for approval.
                        @if($h1AppNote && $h1AppStatus === 3)
                            <div class="mt-1"><strong>Remark:</strong> {{ $h1AppNote }}</div>
                        @endif
                        @if($h2AppNote && $h2AppStatus === 3 && $h2AppNote !== $h1AppNote)
                            <div class="mt-1"><strong>Remark:</strong> {{ $h2AppNote }}</div>
                        @endif
                    </div>
                @endif
            @endif

            {{-- Submit for Approval button (shown when individual has goals in draft/rejected state) --}}
            @php
                $h1NeedsApprovalSubmit = $julyBy === 'individual' && in_array($julyApp, [0, 3]) && $h1Goals->isNotEmpty();
                $h2NeedsApprovalSubmit = $janBy  === 'individual' && in_array($janApp,  [0, 3]) && $h2Goals->isNotEmpty();
                $canSubmitForApproval  = $h1NeedsApprovalSubmit || $h2NeedsApprovalSubmit;
                $approvalCycles        = array_values(array_filter(['H1' => $h1NeedsApprovalSubmit ? 'H1' : null, 'H2' => $h2NeedsApprovalSubmit ? 'H2' : null]));
                $inAnyWindow           = $inH1Window || $inH2Window;
                $h1WinLabel            = date('M j', strtotime(date(CONST_PMSSETTING_FIRSTPMSSTARTDATE))).' – '.date('M j', strtotime(date(CONST_PMSSETTING_FIRSTPMSENDDATE)));
                $h2WinLabel            = date('M j', strtotime(date(CONST_PMSSETTING_SECONDPMSSTARTDATE))).' – '.date('M j', strtotime(date(CONST_PMSSETTING_SECONDPMSENDDATE)));
            @endphp
            @if($canSubmitForApproval)
                <form method="POST" action="{{ route('individualpmsgoal.save') }}" class="mb-3" id="approvalSubmitForm">
                    @csrf
                    <input type="hidden" name="submission_action" value="request_approval">
                    <input type="hidden" name="submission_cycle" value="">
                    <div id="approval-submit-error" class="alert alert-danger d-none mb-2" style="font-size:0.87rem;padding:8px 12px;"></div>
                    <div class="d-flex justify-content-end align-items-center" style="gap:10px;">
                        @if(!$inAnyWindow)
                        <span style="font-size:0.82rem;color:rgba(255,255,255,0.7);">
                            <i class="fa fa-calendar mr-1"></i>
                            Window: H1 {{ $h1WinLabel }} &bull; H2 {{ $h2WinLabel }}
                        </span>
                        @endif
                        <button type="button" class="btn btn-warning btn-sm" id="btn-submit-approval"
                                data-cycles="{{ implode(',', $approvalCycles) }}"
                                {{ $inAnyWindow ? '' : 'disabled' }}
                                title="{{ $inAnyWindow ? '' : 'Submission not open. Window: H1 '.$h1WinLabel.' · H2 '.$h2WinLabel }}">
                            <i class="fa fa-paper-plane mr-1"></i> Submit Goals for Supervisor Approval
                        </button>
                    </div>
                </form>
            @endif

            @if(!$inAnyWindow && !$h1Submitted && !$h2Submitted && $goalDetails->isNotEmpty())
                <div class="alert alert-info mb-3" style="font-size:0.87rem;">
                    <i class="fa fa-calendar mr-1"></i>
                    <strong>Submission window is currently closed.</strong>
                    You can save drafts anytime. Scoring submissions open during:
                    H1 &mdash; <strong>{{ $h1WinLabel }}</strong> &nbsp;&bull;&nbsp;
                    H2 &mdash; <strong>{{ $h2WinLabel }}</strong>
                </div>
            @endif

            {{-- Status / active-cycle notice --}}
            @if($h1Submitted && $h2Submitted)
                <div class="alert alert-success mb-3" style="font-size:0.88rem;">
                    <i class="fa fa-check-circle mr-1"></i>
                    <strong>Both H1 and H2 self-ratings have been submitted.</strong>
                    No further changes are allowed.
                </div>
            @elseif($h1Submitted)
                <div class="alert alert-info mb-3" style="font-size:0.88rem;">
                    <i class="fa fa-check-circle mr-1"></i>
                    <strong>H1 self-rating submitted.</strong>
                    H2 rating will open in January.
                </div>
            @elseif($h2Submitted)
                <div class="alert alert-info mb-3" style="font-size:0.88rem;">
                    <i class="fa fa-check-circle mr-1"></i>
                    <strong>H2 self-rating submitted.</strong>
                    H1 rating will open in July.
                </div>
            @elseif($activeCycle === 'H1')
                <div class="alert alert-info mb-3" style="font-size:0.88rem;">
                    <i class="fa fa-info-circle mr-1"></i>
                    <strong>H1 Rating is now open</strong> (July). You may enter self-scores for H1 goals.
                    H2 inputs are locked until January.
                </div>
            @elseif($activeCycle === 'H2')
                <div class="alert alert-info mb-3" style="font-size:0.88rem;">
                    <i class="fa fa-info-circle mr-1"></i>
                    <strong>H2 Rating is now open</strong> (January). You may enter self-scores for H2 goals.
                    H1 inputs are locked.
                </div>
            @else
                <div class="alert alert-warning mb-3" style="font-size:0.88rem;">
                    <i class="fa fa-lock mr-1"></i>
                    <strong>Self-rating is currently closed.</strong>
                    H1 rating opens in July and H2 rating opens in January.
                </div>
            @endif

            {{-- ── Goal-setting toolbar (only when supervisor hasn't set goals for this year) ── --}}
            @php
                $supervisorH1Set = collect($supervisorCycles)->contains(fn($c) => $c['half'] === 1);
                $supervisorH2Set = collect($supervisorCycles)->contains(fn($c) => $c['half'] === 2);
                $supervisorSetAll = $supervisorH1Set && $supervisorH2Set;
                // Pending = subordinate submitted but supervisor hasn't approved yet — block all edits
                $anyIndividualPending = ($julyBy === 'individual' && $julyApp === 1)
                                     || ($janBy === 'individual' && $janApp === 1);
                // Can set goals when supervisor hasn't covered all cycles, self-rating not submitted, and not pending approval
                $canSetGoals  = !$supervisorSetAll && !($h1Status === 1 && $h2Status === 1) && !$anyIndividualPending;
                // Show edit button when individual has their own goals in draft(0) or rejected(3) — not when pending(1) or approved(2)
                $hasIndividualGoals = ($julyBy === 'individual') || ($janBy === 'individual');
                $goalsEditable = !$anyIndividualPending
                    && (($julyBy === 'individual' && in_array($julyApp, [0, 3]))
                        || ($janBy === 'individual' && in_array($janApp, [0, 3])));
            @endphp

            @if($canSetGoals || $goalsEditable || ($hasIndividualGoals && ($supervisorH1Set || $supervisorH2Set)))
            <div class="goal-toolbar mb-3" style="background:#f8f9ff;border:1px solid #e3eaff;border-radius:6px;padding:12px 16px;">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
                    <span style="font-size:0.88rem;color:#444;font-weight:600;">
                        <i class="fa fa-bullseye mr-1" style="color:#226b86;"></i> My Goals
                    </span>
                    <div class="d-flex flex-wrap" style="gap:8px;">
                        @if($goalsEditable && $goalId)
                            <a href="{{ route('goals.edit', $goalId) }}" class="btn btn-default btn-sm">
                                <i class="fa fa-pencil mr-1"></i> Edit My Goals
                            </a>
                        @endif
                        @if($canSetGoals)
                            @if(!$supervisorH1Set || !$supervisorH2Set)
                                <a href="{{ route('goals.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus mr-1"></i> Add New Goal
                                </a>
                                <a href="{{ route('goals.import') }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-upload mr-1"></i> Import Goals
                                </a>
                            @endif
                        @endif
                        @if($supervisorH1Set && $supervisorH2Set)
                            <span class="text-muted" style="font-size:0.82rem;align-self:center;">
                                <i class="fa fa-info-circle mr-1"></i>
                                Goals set by your supervisor for {{ $selectedYear }}.
                            </span>
                        @elseif($supervisorH1Set || $supervisorH2Set)
                            <span class="text-muted" style="font-size:0.82rem;align-self:center;">
                                <i class="fa fa-info-circle mr-1"></i>
                                @if($supervisorH1Set) H1 @else H2 @endif goals set by supervisor — you may set the other cycle.
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Year filter — separate GET form so it never triggers the POST save --}}
            <form method="GET" action="{{ route('individualpmsgoal') }}" id="yearFilterForm">
                <div class="goal-toolbar">
                    <div class="d-flex align-items-center flex-wrap">
                        {{-- H1 stats --}}
                        <span class="mr-3" style="font-size:0.88rem;">
                            <span class="cycle-badge-h1" style="padding:2px 8px;font-size:0.78rem;">H1</span>
                            &nbsp;<strong style="color:#333;">{{ $h1Goals->count() }}</strong>
                            <span style="color:#666;">goal(s)</span>
                            &nbsp;&bull;&nbsp;Score:&nbsp;<strong style="color:#333;">{{ number_format($h1Goals->sum('Weightage'), 2) }}</strong>
                        </span>
                        {{-- H2 stats --}}
                        <span style="font-size:0.88rem;">
                            <span class="cycle-badge-h2" style="padding:2px 8px;font-size:0.78rem;">H2</span>
                            &nbsp;<strong style="color:#333;">{{ $h2Goals->count() }}</strong>
                            <span style="color:#666;">goal(s)</span>
                            &nbsp;&bull;&nbsp;Score:&nbsp;<strong style="color:#333;">{{ number_format($h2Goals->sum('Weightage'), 2) }}</strong>
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2" style="color:#555;font-size:.85rem;">Year</label>
                        <select name="year" class="form-control form-control-sm"
                                style="width:90px;" onchange="document.getElementById('yearFilterForm').submit()">
                            @foreach(range(now()->year, now()->year - 3, -1) as $y)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('individualpmsgoal.save') }}" id="selfRatingForm">
                @csrf
                <input type="hidden" name="goal_master_id" value="{{ $goalId }}">
                <input type="hidden" name="submission_cycle" value="{{ $activeCycle }}">

                {{-- ═══ H1 SECTION ═══ --}}
                @if($h1Goals->isNotEmpty())
                    <div class="cycle-section-header">
                        <span class="cycle-badge-h1">
                            <i class="fa fa-calendar-o mr-1"></i>H1 Goals
                            <small style="font-weight:400;">&nbsp;(January – June)</small>
                        </span>
                        @if($h1Locked)
                            <span class="cycle-locked-notice"><i class="fa fa-lock"></i> Locked</span>
                        @endif
                    </div>

                    @foreach($h1Goals as $goal)
                        @php
                            $goalNumber    = $loop->iteration;
                            $halfYearLabel = match(true) {
                                (bool)$goal->InH1 && (bool)$goal->InH2 => 'H1 & H2',
                                (bool)$goal->InH1                       => 'H1',
                                (bool)$goal->InH2                       => 'H2',
                                default                                  => 'Full Year',
                            };
                            $taskWeightSum = $goal->targets->sum('Weightage');
                            $pct           = $goal->Weightage > 0
                                                 ? min(100, ($taskWeightSum / $goal->Weightage) * 100)
                                                 : 0;
                        @endphp
                        <div class="goal-card {{ $h1Locked ? 'locked' : '' }}"
                             data-cycle-section="H1" data-weightage="{{ $goal->Weightage }}">
                            <div class="goal-meta">
                                <span class="goal-number">#{{ $goalNumber }}</span>
                                <span class="badge-half">{{ $halfYearLabel }}</span>
                                <span class="badge-year">{{ $goal->Year }}</span>
                                @if((int)$goal->GoalType === 2)
                                    <span class="badge-common-goal"><i class="fa fa-users mr-1"></i>Common Goal</span>
                                @else
                                    <span class="badge-section-goal"><i class="fa fa-sitemap mr-1"></i>Section Goal</span>
                                @endif
                                @if($h1Locked)
                                    <span class="cycle-locked-notice ml-1"><i class="fa fa-lock"></i></span>
                                @endif
                            </div>
                            <div class="goal-title">{{ $goal->Description }}</div>
                            <div class="goal-stats">
                                Total Score: <strong>{{ number_format($goal->Weightage, 2) }}</strong>
                                &nbsp;&bull;&nbsp;
                                {{ $goal->targets->count() }} task(s)
                                &nbsp;&bull;&nbsp;
                                Task weightage sum: <strong>{{ number_format($taskWeightSum, 2) }}</strong>
                            </div>
                            <div class="score-bar-wrap">
                                <div class="score-bar-fill" style="width:{{ $pct }}%;"></div>
                            </div>

                            @if($goal->targets->isNotEmpty())
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-bordered task-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:40px;">#</th>
                                                <th>Task Description</th>
                                                <th style="width:90px;">Weightage</th>
                                                <th style="width:110px;">Target</th>
                                                <th style="width:150px;">Achievement</th>
                                                <th style="width:110px;">Self Score</th>
                                                <th>Self Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($goal->targets as $task)
                                                @php $achVal = old('goals.'.$goal->Id.'.tasks.'.$task->Id.'.achievement', $task->Achievement); @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $task->Description }}</td>
                                                    <td>{{ number_format($task->Weightage, 2) }}</td>
                                                    <td><span class="target-badge">{{ $task->Target }}</span></td>
                                                    <td>
                                                        <select name="goals[{{ $goal->Id }}][tasks][{{ $task->Id }}][achievement]"
                                                                class="form-control form-control-sm self-input task-achievement"
                                                                {{ $h1Locked ? 'disabled' : '' }}>
                                                            <option value="">-- Select --</option>
                                                            @foreach(['Achieved','Partially Achieved','Not Achieved','Ongoing'] as $opt)
                                                                <option value="{{ $opt }}" {{ $achVal == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               name="goals[{{ $goal->Id }}][tasks][{{ $task->Id }}][self_score]"
                                                               class="form-control form-control-sm self-input task-self-score"
                                                               data-goal-id="{{ $goal->Id }}"
                                                               data-section="H1"
                                                               value="{{ old('goals.'.$goal->Id.'.tasks.'.$task->Id.'.self_score', $task->SelfScore) }}"
                                                               min="0"
                                                               max="{{ $task->Weightage }}"
                                                               step="0.01"
                                                               placeholder="0.00"
                                                               {{ $h1Locked ? 'disabled' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                               name="goals[{{ $goal->Id }}][tasks][{{ $task->Id }}][self_remarks]"
                                                               class="form-control form-control-sm self-input task-self-remarks"
                                                               value="{{ old('goals.'.$goal->Id.'.tasks.'.$task->Id.'.self_remarks', $task->SelfRemarks) }}"
                                                               placeholder="Enter remarks"
                                                               {{ $h1Locked ? 'disabled' : '' }}>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <div class="self-rating-section">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Overall Self Score (out of {{ number_format($goal->Weightage, 2) }})</label>
                                        <input type="number"
                                               name="goals[{{ $goal->Id }}][self_score]"
                                               id="overall-self-score-{{ $goal->Id }}-H1"
                                               class="form-control form-control-sm goal-overall-score"
                                               data-goal-id="{{ $goal->Id }}"
                                               data-section="H1"
                                               value="{{ old('goals.'.$goal->Id.'.self_score', $goal->SelfScore) }}"
                                               min="0"
                                               max="{{ $goal->Weightage }}"
                                               step="0.01"
                                               placeholder="0.00"
                                               readonly
                                               style="background:#e9ecef;cursor:not-allowed;"
                                               title="Auto-calculated from task self scores">
                                    </div>
                                    <div class="col-md-9 goal-self-remarks">
                                        <label>Overall Self Remarks</label>
                                        <textarea name="goals[{{ $goal->Id }}][self_remarks]"
                                                  class="form-control"
                                                  rows="2"
                                                  placeholder="Enter your overall remarks for this goal"
                                                  {{ $h1Locked ? 'disabled' : '' }}>{{ old('goals.'.$goal->Id.'.self_remarks', $goal->SelfRemarks) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- H1 action buttons --}}
                    @if($h1NeedsApproval)
                        <div class="d-flex justify-content-end mt-2 mb-1">
                            @if($h1AppStatus === 1)
                                <span class="badge badge-warning" style="font-size:0.82rem;padding:5px 12px;color:#333;">
                                    <i class="fa fa-clock-o mr-1"></i> Pending Supervisor Approval
                                </span>
                            @elseif($h1AppStatus === 3)
                                <span class="badge badge-danger" style="font-size:0.82rem;padding:5px 12px;">
                                    <i class="fa fa-times-circle mr-1"></i> Goals Rejected — Revise &amp; Resubmit
                                </span>
                            @else
                                <span class="badge badge-secondary" style="font-size:0.82rem;padding:5px 12px;">
                                    <i class="fa fa-info-circle mr-1"></i> Submit Goals for Approval First
                                </span>
                            @endif
                        </div>
                    @elseif($activeCycle === 'H1' && !$h1Submitted && $h1Goals->isNotEmpty())
                        <div id="h1-submit-error" class="alert alert-danger mt-2 d-none" style="font-size:0.87rem;padding:8px 12px;"></div>
                        <div class="d-flex justify-content-end mt-2 mb-1" style="gap:8px;">
                            <button type="submit" name="submission_action" value="draft" class="btn btn-secondary btn-sm">
                                <i class="fa fa-save mr-1"></i> Save H1 Draft
                            </button>
                            <button type="button" class="btn btn-success btn-sm btn-submit-cycle" data-cycle="H1">
                                <i class="fa fa-paper-plane mr-1"></i> Submit H1
                            </button>
                        </div>
                    @elseif($h1Submitted)
                        <div class="d-flex justify-content-end mt-2 mb-1">
                            <span class="badge badge-success" style="font-size:0.82rem;padding:5px 12px;">
                                <i class="fa fa-check-circle mr-1"></i> H1 Submitted
                            </span>
                        </div>
                    @endif
                @endif

                {{-- ═══ H2 SECTION ═══ --}}
                @if($h2Goals->isNotEmpty())
                    <div class="cycle-section-header">
                        <span class="cycle-badge-h2">
                            <i class="fa fa-calendar-o mr-1"></i>H2 Goals
                            <small style="font-weight:400;">&nbsp;(July – December)</small>
                        </span>
                        @if($h2Locked)
                            <span class="cycle-locked-notice"><i class="fa fa-lock"></i> Locked</span>
                        @endif
                    </div>

                    @foreach($h2Goals as $goal)
                        @php
                            $goalNumber    = $loop->iteration;
                            $halfYearLabel = match(true) {
                                (bool)$goal->InH1 && (bool)$goal->InH2 => 'H1 & H2',
                                (bool)$goal->InH1                       => 'H1',
                                (bool)$goal->InH2                       => 'H2',
                                default                                  => 'Full Year',
                            };
                            $taskWeightSum = $goal->targets->sum('Weightage');
                            $pct           = $goal->Weightage > 0
                                                 ? min(100, ($taskWeightSum / $goal->Weightage) * 100)
                                                 : 0;
                        @endphp
                        <div class="goal-card {{ $h2Locked ? 'locked' : '' }}"
                             data-cycle-section="H2" data-weightage="{{ $goal->Weightage }}">
                            <div class="goal-meta">
                                <span class="goal-number">#{{ $goalNumber }}</span>
                                <span class="badge-half">{{ $halfYearLabel }}</span>
                                <span class="badge-year">{{ $goal->Year }}</span>
                                @if((int)$goal->GoalType === 2)
                                    <span class="badge-common-goal"><i class="fa fa-users mr-1"></i>Common Goal</span>
                                @else
                                    <span class="badge-section-goal"><i class="fa fa-sitemap mr-1"></i>Section Goal</span>
                                @endif
                                @if($h2Locked)
                                    <span class="cycle-locked-notice ml-1"><i class="fa fa-lock"></i></span>
                                @endif
                            </div>
                            <div class="goal-title">{{ $goal->Description }}</div>
                            <div class="goal-stats">
                                Total Score: <strong>{{ number_format($goal->Weightage, 2) }}</strong>
                                &nbsp;&bull;&nbsp;
                                {{ $goal->targets->count() }} task(s)
                                &nbsp;&bull;&nbsp;
                                Task weightage sum: <strong>{{ number_format($taskWeightSum, 2) }}</strong>
                            </div>
                            <div class="score-bar-wrap">
                                <div class="score-bar-fill" style="width:{{ $pct }}%;"></div>
                            </div>

                            @if($goal->targets->isNotEmpty())
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-bordered task-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:40px;">#</th>
                                                <th>Task Description</th>
                                                <th style="width:90px;">Weightage</th>
                                                <th style="width:110px;">Target</th>
                                                <th style="width:150px;">Achievement</th>
                                                <th style="width:110px;">Self Score</th>
                                                <th>Self Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($goal->targets as $task)
                                                @php $achVal = old('goals.'.$goal->Id.'.tasks.'.$task->Id.'.achievement', $task->Achievement); @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $task->Description }}</td>
                                                    <td>{{ number_format($task->Weightage, 2) }}</td>
                                                    <td><span class="target-badge">{{ $task->Target }}</span></td>
                                                    <td>
                                                        <select name="goals[{{ $goal->Id }}][tasks][{{ $task->Id }}][achievement]"
                                                                class="form-control form-control-sm self-input task-achievement"
                                                                {{ $h2Locked ? 'disabled' : '' }}>
                                                            <option value="">-- Select --</option>
                                                            @foreach(['Achieved','Partially Achieved','Not Achieved','Ongoing'] as $opt)
                                                                <option value="{{ $opt }}" {{ $achVal == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               name="goals[{{ $goal->Id }}][tasks][{{ $task->Id }}][self_score]"
                                                               class="form-control form-control-sm self-input task-self-score"
                                                               data-goal-id="{{ $goal->Id }}"
                                                               data-section="H2"
                                                               value="{{ old('goals.'.$goal->Id.'.tasks.'.$task->Id.'.self_score', $task->SelfScore) }}"
                                                               min="0"
                                                               max="{{ $task->Weightage }}"
                                                               step="0.01"
                                                               placeholder="0.00"
                                                               {{ $h2Locked ? 'disabled' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                               name="goals[{{ $goal->Id }}][tasks][{{ $task->Id }}][self_remarks]"
                                                               class="form-control form-control-sm self-input task-self-remarks"
                                                               value="{{ old('goals.'.$goal->Id.'.tasks.'.$task->Id.'.self_remarks', $task->SelfRemarks) }}"
                                                               placeholder="Enter remarks"
                                                               {{ $h2Locked ? 'disabled' : '' }}>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <div class="self-rating-section">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Overall Self Score (out of {{ number_format($goal->Weightage, 2) }})</label>
                                        <input type="number"
                                               name="goals[{{ $goal->Id }}][self_score]"
                                               id="overall-self-score-{{ $goal->Id }}-H2"
                                               class="form-control form-control-sm goal-overall-score"
                                               data-goal-id="{{ $goal->Id }}"
                                               data-section="H2"
                                               value="{{ old('goals.'.$goal->Id.'.self_score', $goal->SelfScore) }}"
                                               min="0"
                                               max="{{ $goal->Weightage }}"
                                               step="0.01"
                                               placeholder="0.00"
                                               readonly
                                               style="background:#e9ecef;cursor:not-allowed;"
                                               title="Auto-calculated from task self scores">
                                    </div>
                                    <div class="col-md-9 goal-self-remarks">
                                        <label>Overall Self Remarks</label>
                                        <textarea name="goals[{{ $goal->Id }}][self_remarks]"
                                                  class="form-control"
                                                  rows="2"
                                                  placeholder="Enter your overall remarks for this goal"
                                                  {{ $h2Locked ? 'disabled' : '' }}>{{ old('goals.'.$goal->Id.'.self_remarks', $goal->SelfRemarks) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- H2 action buttons --}}
                    @if($h2NeedsApproval)
                        <div class="d-flex justify-content-end mt-2 mb-1">
                            @if($h2AppStatus === 1)
                                <span class="badge badge-warning" style="font-size:0.82rem;padding:5px 12px;color:#333;">
                                    <i class="fa fa-clock-o mr-1"></i> Pending Supervisor Approval
                                </span>
                            @elseif($h2AppStatus === 3)
                                <span class="badge badge-danger" style="font-size:0.82rem;padding:5px 12px;">
                                    <i class="fa fa-times-circle mr-1"></i> Goals Rejected — Revise &amp; Resubmit
                                </span>
                            @else
                                <span class="badge badge-secondary" style="font-size:0.82rem;padding:5px 12px;">
                                    <i class="fa fa-info-circle mr-1"></i> Submit Goals for Approval First
                                </span>
                            @endif
                        </div>
                    @elseif($activeCycle === 'H2' && !$h2Submitted && $h2Goals->isNotEmpty())
                        <div id="h2-submit-error" class="alert alert-danger mt-2 d-none" style="font-size:0.87rem;padding:8px 12px;"></div>
                        <div class="d-flex justify-content-end mt-2 mb-1" style="gap:8px;">
                            <button type="submit" name="submission_action" value="draft" class="btn btn-secondary btn-sm">
                                <i class="fa fa-save mr-1"></i> Save H2 Draft
                            </button>
                            <button type="button" class="btn btn-success btn-sm btn-submit-cycle" data-cycle="H2">
                                <i class="fa fa-paper-plane mr-1"></i> Submit H2
                            </button>
                        </div>
                    @elseif($h2Submitted)
                        <div class="d-flex justify-content-end mt-2 mb-1">
                            <span class="badge badge-success" style="font-size:0.82rem;padding:5px 12px;">
                                <i class="fa fa-check-circle mr-1"></i> H2 Submitted
                            </span>
                        </div>
                    @endif
                @endif

                {{-- Empty state --}}
                @if($h1Goals->isEmpty() && $h2Goals->isEmpty())
                    <div style="background:#fff;border-radius:6px;padding:40px;text-align:center;color:#999;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                        <i class="fa fa-bullseye fa-3x" style="opacity:.3;margin-bottom:12px;display:block;"></i>
                        No goals found for {{ $selectedYear }}.
                        <br>
                        @if($supervisorSetAll)
                            <small>Your supervisor has not defined goals for this year yet.</small>
                        @else
                            <small>
                                No goals set yet. Use <strong>Add New Goal</strong> or <strong>Import Goals</strong> above
                                to set your own goals for {{ $selectedYear }}, then submit for supervisor approval.
                            </small>
                        @endif
                    </div>
                @endif

            </form>

        </div>
    </div>
</div>

{{-- Submission confirmation modal --}}
<div class="modal fade" id="submitConfirmModal" tabindex="-1" role="dialog" aria-labelledby="submitConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:420px;">
        <div class="modal-content" style="border:none;border-radius:12px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.18);">
            <div class="modal-body text-center" style="padding:36px 32px 20px;">
                {{-- Warning icon circle --}}
                <div style="width:72px;height:72px;border-radius:50%;background:#fff8e1;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <i class="fa fa-exclamation-triangle" style="color:#f59e0b;font-size:32px;"></i>
                </div>
                <h5 id="submitModalTitle" style="font-weight:700;color:#1a1a1a;margin-bottom:10px;font-size:1.1rem;">
                    Confirm Submission
                </h5>
                <p id="submitModalMessage" style="color:#666;font-size:0.88rem;line-height:1.6;margin-bottom:0;">
                    Once submitted, you cannot make further changes to this self-rating.
                </p>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f0f0f0;justify-content:center;padding:16px 32px 28px;gap:12px;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        style="min-width:110px;border-radius:6px;font-size:0.88rem;">
                    <i class="fa fa-times mr-1"></i> Cancel
                </button>
                <button type="button" id="submitConfirmBtn"
                        class="btn btn-success"
                        style="min-width:130px;border-radius:6px;font-size:0.88rem;font-weight:600;">
                    <i class="fa fa-paper-plane mr-1"></i> Yes, Submit
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('pagescripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Supervisor-approval submission: validate total weightage = 100 per cycle ──
    var approvalBtn = document.getElementById('btn-submit-approval');
    if (approvalBtn) {
        approvalBtn.addEventListener('click', function () {
            var cycles    = (this.dataset.cycles || '').split(',').filter(Boolean);
            var errEl     = document.getElementById('approval-submit-error');
            var errors    = [];

            cycles.forEach(function (cycle) {
                var totalWeight = 0;
                document.querySelectorAll('.goal-card[data-cycle-section="' + cycle + '"]').forEach(function (card) {
                    totalWeight += parseFloat(card.dataset.weightage) || 0;
                });
                totalWeight = Math.round(totalWeight * 100) / 100;
                if (Math.abs(totalWeight - 100) > 0.005) {
                    errors.push('<strong>' + cycle + '</strong>: total goal score is <strong>' +
                        totalWeight.toFixed(2) + '</strong> (must be exactly <strong>100</strong>).');
                }
            });

            if (errors.length > 0) {
                errEl.innerHTML = '<i class="fa fa-exclamation-triangle mr-1"></i> ' +
                    'Cannot submit for approval — please fix the following:<br>' + errors.join('<br>');
                errEl.classList.remove('d-none');
                errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            errEl.classList.add('d-none');
            document.getElementById('approvalSubmitForm').submit();
        });
    }

    // ── Submission confirmation modal ──────────────────────────────────────
    var pendingCycle = null;

    document.querySelectorAll('.btn-submit-cycle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            pendingCycle = this.dataset.cycle;

            // Clear any previous error
            var errEl = document.getElementById(pendingCycle.toLowerCase() + '-submit-error');
            if (errEl) { errEl.textContent = ''; errEl.classList.add('d-none'); }

            // Validate total weightage = 100 for this cycle
            var totalWeight = 0;
            document.querySelectorAll('.goal-card[data-cycle-section="' + pendingCycle + '"]').forEach(function (card) {
                totalWeight += parseFloat(card.dataset.weightage) || 0;
            });
            totalWeight = Math.round(totalWeight * 100) / 100;

            if (Math.abs(totalWeight - 100) > 0.005) {
                if (errEl) {
                    errEl.innerHTML = '<i class="fa fa-exclamation-triangle mr-1"></i>' +
                        'Cannot submit <strong>' + pendingCycle + '</strong>: total goal score must be exactly ' +
                        '<strong>100</strong>. Current total is <strong>' + totalWeight.toFixed(2) + '</strong>.';
                    errEl.classList.remove('d-none');
                    errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            document.getElementById('submitModalTitle').textContent =
                'Submit ' + pendingCycle + ' Self-Rating?';
            document.getElementById('submitModalMessage').textContent =
                'Once you submit your ' + pendingCycle + ' self-rating, no further changes can be made. ' +
                'Please ensure all scores and remarks are complete before proceeding.';
            $('#submitConfirmModal').modal('show');
        });
    });

    document.getElementById('submitConfirmBtn').addEventListener('click', function () {
        $('#submitConfirmModal').modal('hide');
        if (!pendingCycle) return;
        var form = document.getElementById('selfRatingForm');
        // Update submission_cycle in case it differs (belt-and-suspenders)
        var cycleInput = form.querySelector('[name="submission_cycle"]');
        if (cycleInput) cycleInput.value = pendingCycle;
        // Inject submission_action=submit and submit the form
        var actionInput = document.createElement('input');
        actionInput.type  = 'hidden';
        actionInput.name  = 'submission_action';
        actionInput.value = 'submit';
        form.appendChild(actionInput);
        form.submit();
    });


    // ── Self-score: clamp to max and recalculate goal total ───────────────
    document.querySelectorAll('.task-self-score').forEach(function (input) {
        input.addEventListener('input', function () {
            var max = parseFloat(this.getAttribute('max'));
            var val = parseFloat(this.value) || 0;
            if (val > max) {
                this.value = max.toFixed(2);
                this.classList.add('score-at-max');
            } else {
                this.classList.remove('score-at-max');
            }
            recalcGoalSelfScore(this.dataset.goalId, this.dataset.section);
        });
    });

    function recalcGoalSelfScore(goalId, section) {
        var sum = 0;
        document.querySelectorAll(
            '.task-self-score[data-goal-id="' + goalId + '"][data-section="' + section + '"]'
        ).forEach(function (el) {
            sum += parseFloat(el.value) || 0;
        });
        sum = Math.round(sum * 100) / 100;
        var overall = document.getElementById('overall-self-score-' + goalId + '-' + section);
        if (overall) {
            overall.value = sum.toFixed(2);
        }
    }

    // Run once on load to reflect any pre-filled values
    document.querySelectorAll('.goal-overall-score').forEach(function (el) {
        recalcGoalSelfScore(el.dataset.goalId, el.dataset.section);
    });

    // ── Achievement → remarks mandatory ───────────────────────────────────
    function toggleRemarksRequired(sel) {
        var row = sel.closest('tr');
        if (!row) return;
        var remarks = row.querySelector('.task-self-remarks');
        if (!remarks) return;
        if (sel.value && sel.value !== 'Achieved') {
            remarks.classList.add('remarks-required');
            remarks.setAttribute('placeholder', 'Remarks required');
        } else {
            remarks.classList.remove('remarks-required');
            remarks.classList.remove('remarks-error');
            remarks.setAttribute('placeholder', 'Enter remarks');
        }
    }

    document.querySelectorAll('.task-achievement').forEach(function (sel) {
        sel.addEventListener('change', function () { toggleRemarksRequired(this); });
        toggleRemarksRequired(sel);
    });

    // ── Form-submit validation ─────────────────────────────────────────────
    var form = document.getElementById('selfRatingForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            var hasError = false;
            document.querySelectorAll('.task-achievement:not([disabled])').forEach(function (sel) {
                var row = sel.closest('tr');
                if (!row) return;
                var remarks = row.querySelector('.task-self-remarks');
                if (!remarks) return;
                if (sel.value && sel.value !== 'Achieved' && !remarks.value.trim()) {
                    remarks.classList.add('remarks-error');
                    hasError = true;
                } else {
                    remarks.classList.remove('remarks-error');
                }
            });
            if (hasError) {
                e.preventDefault();
                alert('Please enter remarks for all tasks that are not "Achieved".');
                document.querySelector('.remarks-error').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

});
</script>
@stop
