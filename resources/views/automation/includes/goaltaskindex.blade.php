<style>
    .goal-card {
        background-color: #fff;
        border-radius: 6px;
        border-left: 4px solid #2196F3;
        box-shadow: 0 1px 4px rgba(0,0,0,0.12);
        margin-bottom: 18px;
        padding: 16px 20px;
        color: #333;
    }
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
    .goal-card .goal-title {
        font-size: 1rem;
        font-weight: 600;
        color: #222;
        margin-bottom: 4px;
    }
    .goal-card .goal-stats {
        font-size: 0.82rem;
        color: #666;
        margin-bottom: 8px;
    }
    .goal-card .goal-stats strong { color: #333; }
    .goal-card .score-bar-wrap {
        background: #e9ecef;
        border-radius: 4px;
        height: 6px;
        width: 180px;
        margin-top: 6px;
        overflow: hidden;
    }
    .goal-card .score-bar-fill { background: #2196F3; height: 100%; border-radius: 4px; }
    .goal-card table.task-table { background-color: #fff; color: #333; font-size: 0.85rem; }
    .goal-card table.task-table thead th {
        background-color: #f5f7fa;
        color: #555;
        border-color: #dee2e6;
        font-weight: 600;
    }
    .goal-card table.task-table td { color: #444; border-color: #dee2e6; vertical-align: middle; }
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
    .badge-common-goal  { background:#fff3e0; color:#e65100; border:1px solid #ffcc80;
        padding:2px 9px; border-radius:12px; font-size:0.75rem; font-weight:600; margin-right:5px; }
    .badge-section-goal { background:#f3e5f5; color:#6a1b9a; border:1px solid #ce93d8;
        padding:2px 9px; border-radius:12px; font-size:0.75rem; font-weight:600; margin-right:5px; }
    .goal-toolbar {
        background-color: #fff;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .text-warn-mismatch { color: #e53935; font-size: 0.8rem; }
    .target-badge {
        display: inline-block;
        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 4px;
        padding: 2px 7px;
        font-size: 0.78rem;
        font-weight: 600;
    }
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
    .goal-card.view-only { border-left-color: #aaa; background: #fafafa; }
    .cycle-stats {
        font-size: 0.8rem;
        color: #333;
        margin-left: 10px;
        font-weight: 600;
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 2px 10px;
        white-space: nowrap;
    }
</style>

@php
    $h1Goals = $goalDetails->filter(fn($g) => (bool)$g->InH1);
    $h2Goals = $goalDetails->filter(fn($g) => (bool)$g->InH2);
@endphp

<div class="goal-toolbar">
    {{-- Action buttons + year filter --}}
    @php
        $h1Submitted  = ($h1Status ?? 0) == 1;
        $h2Submitted  = ($h2Status ?? 0) == 1;
        $bothSubmitted = $h1Submitted && $h2Submitted;
    @endphp
    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
        <div class="d-flex align-items-center flex-wrap">
            @if(!$h1Submitted && !$h2Submitted)
                <a href="{{ route('goals.create', ['employeeId' => $EmployeeId]) }}" class="btn btn-primary btn-sm mr-2">
                    <i class="fa fa-plus"></i> Add New Goal
                </a>
                <a href="{{ route('goals.import', ['employeeId' => $EmployeeId]) }}" class="btn btn-default btn-sm mr-2">
                    <i class="fa fa-upload"></i> Import Template
                </a>
            @elseif($h1Submitted || $h2Submitted)
                <button type="button" class="btn btn-primary btn-sm mr-2" disabled title="Goals cannot be added after self-rating is submitted">
                    <i class="fa fa-plus"></i> Add New Goal
                </button>
                <button type="button" class="btn btn-default btn-sm mr-2" disabled title="Goals cannot be imported after self-rating is submitted">
                    <i class="fa fa-upload"></i> Import Template
                </button>
            @endif
            @if(!empty($goalId))
                <a href="{{ route('goals.show', $goalId) }}" class="btn btn-info btn-sm mr-1">
                    <i class="fa fa-eye"></i> View
                </a>
            @endif
            @if(!empty($supervisorGoalId ?? null) && !$bothSubmitted)
                <a href="{{ route('goals.edit', $supervisorGoalId) }}" class="btn btn-default btn-sm mr-1">
                    <i class="fa fa-pencil"></i> Edit
                </a>
                <form method="POST" action="{{ route('goals.submit', $supervisorGoalId) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa fa-paper-plane"></i> Submit
                    </button>
                </form>
            @endif
            @if(!empty($goalId))
                @if($h1Submitted)
                    <span class="badge badge-success ml-2" style="font-size:0.82rem;padding:5px 10px;">
                        <i class="fa fa-check-circle mr-1"></i> H1 Submitted
                    </span>
                @endif
                @if($h2Submitted)
                    <span class="badge badge-success ml-2" style="font-size:0.82rem;padding:5px 10px;">
                        <i class="fa fa-check-circle mr-1"></i> H2 Submitted
                    </span>
                @endif
            @endif

            {{-- L1 Appraise buttons — shown only to the Level 1 appraiser after employee submits --}}
            @if($isL1Appraiser ?? false)
                @if($h1Submitted)
                    @php $l1H1Sub = $l1H1Submission ?? null; @endphp
                    @if($l1H1Sub && !is_null($l1H1Sub->SubmittedAt))
                        <a href="{{ route('goals.appraise.show', [$EmployeeId, 'h1']) }}"
                           class="btn btn-sm btn-outline-success ml-2">
                            <i class="fa fa-check-circle mr-1"></i> H1 Appraised
                        </a>
                    @else
                        <a href="{{ route('goals.appraise.show', [$EmployeeId, 'h1']) }}"
                           class="btn btn-sm btn-warning ml-2" style="color:#333;">
                            <i class="fa fa-star mr-1"></i> Appraise H1
                        </a>
                    @endif
                @endif
                @if($h2Submitted)
                    @php $l1H2Sub = $l1H2Submission ?? null; @endphp
                    @if($l1H2Sub && !is_null($l1H2Sub->SubmittedAt))
                        <a href="{{ route('goals.appraise.show', [$EmployeeId, 'h2']) }}"
                           class="btn btn-sm btn-outline-success ml-2">
                            <i class="fa fa-check-circle mr-1"></i> H2 Appraised
                        </a>
                    @else
                        <a href="{{ route('goals.appraise.show', [$EmployeeId, 'h2']) }}"
                           class="btn btn-sm btn-warning ml-2" style="color:#333;">
                            <i class="fa fa-star mr-1"></i> Appraise H2
                        </a>
                    @endif
                @endif
            @endif
        </div>
        <form method="GET" class="d-flex align-items-center">
            <label class="mb-0 mr-2" style="color:#555;font-size:.85rem;">Year</label>
            <select name="year" class="form-control form-control-sm" style="width:90px;" onchange="this.form.submit()">
                @foreach(range(now()->year, now()->year - 3, -1) as $y)
                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- H1 / H2 stats bar --}}
    @if($goalDetailsExists)
    <div class="d-flex align-items-center flex-wrap" style="margin-top:10px;padding-top:10px;border-top:1px solid #f0f0f0;gap:20px;">
        <span style="font-size:0.88rem;">
            <span class="cycle-badge-h1" style="padding:2px 8px;font-size:0.78rem;">H1</span>
            &nbsp;<strong style="color:#333;">{{ $h1Goals->count() }}</strong>
            <span style="color:#666;">goal(s)</span>
            &nbsp;&bull;&nbsp;Score:&nbsp;<strong style="color:#333;">{{ number_format($h1Goals->sum('Weightage'), 2) }}</strong>
        </span>
        <span style="font-size:0.88rem;">
            <span class="cycle-badge-h2" style="padding:2px 8px;font-size:0.78rem;">H2</span>
            &nbsp;<strong style="color:#333;">{{ $h2Goals->count() }}</strong>
            <span style="color:#666;">goal(s)</span>
            &nbsp;&bull;&nbsp;Score:&nbsp;<strong style="color:#333;">{{ number_format($h2Goals->sum('Weightage'), 2) }}</strong>
        </span>
    </div>
    @endif
</div>

{{-- ── Supervisor Goal Approval Panel ──────────────────────────────────── --}}
@if(!empty($approvalMasters) && $approvalMasters->isNotEmpty())
    @php
        $pendingMasters  = $approvalMasters->where('ApprovalStatus', 1);
        $rejectedMasters = $approvalMasters->where('ApprovalStatus', 3);
        $approvedMasters = $approvalMasters->where('ApprovalStatus', 2);
    @endphp

    @if($pendingMasters->isNotEmpty())
        <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap mb-3" style="font-size:0.88rem;gap:8px;">
            <div>
                <i class="fa fa-clock-o mr-1"></i>
                <strong>This employee has submitted their goals and is awaiting your approval.</strong>
                Review the goals below, then Approve or Reject.
            </div>
            <div class="d-flex" style="gap:8px;flex-shrink:0;">
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#approveGoalModal">
                    <i class="fa fa-check mr-1"></i> Approve
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectGoalModal">
                    <i class="fa fa-times mr-1"></i> Reject Goals
                </button>
            </div>
        </div>
    @elseif($approvedMasters->isNotEmpty())
        @if($h1Submitted && $h2Submitted)
            <div class="alert alert-success mb-3" style="font-size:0.88rem;">
                <i class="fa fa-check-circle mr-1"></i>
                <strong>H1 and H2 self-ratings have been submitted.</strong>
            </div>
        @elseif($h1Submitted)
            <div class="alert alert-success mb-3" style="font-size:0.88rem;">
                <i class="fa fa-check-circle mr-1"></i>
                <strong>H1 self-rating has been submitted.</strong>
            </div>
        @elseif($h2Submitted)
            <div class="alert alert-success mb-3" style="font-size:0.88rem;">
                <i class="fa fa-check-circle mr-1"></i>
                <strong>H2 self-rating has been submitted.</strong>
            </div>
        @else
            <div class="alert alert-success mb-3" style="font-size:0.88rem;">
                <i class="fa fa-check-circle mr-1"></i>
                <strong>Employee-set goals have been approved.</strong> The employee may now submit self-ratings.
                @if($approvedMasters->first()->ApprovalRemark)
                    <div class="mt-1"><strong>Remark:</strong> {{ $approvedMasters->first()->ApprovalRemark }}</div>
                @endif
            </div>
        @endif
    @elseif($rejectedMasters->isNotEmpty())
        <div class="alert alert-danger mb-3" style="font-size:0.88rem;">
            <i class="fa fa-times-circle mr-1"></i>
            <strong>Goals were rejected.</strong> The employee has been asked to revise and resubmit.
            @if($rejectedMasters->first()->ApprovalRemark)
                <div class="mt-1"><strong>Reason:</strong> {{ $rejectedMasters->first()->ApprovalRemark }}</div>
            @endif
        </div>
    @endif

    {{-- Approve modal --}}
    <div class="modal fade" id="approveGoalModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:440px;">
            <div class="modal-content" style="border-radius:10px;overflow:hidden;">
                <div class="modal-header" style="background:#28a745;color:#fff;border:none;padding:16px 20px;">
                    <h6 class="modal-title mb-0"><i class="fa fa-check-circle mr-2"></i>Approve Employee Goals</h6>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                </div>
                <form method="POST" action="{{ route('goals.approve') }}">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $EmployeeId }}">
                    <div class="modal-body" style="padding:20px;">
                        <p style="font-size:0.88rem;color:#444;margin-bottom:14px;">
                            You are about to approve this employee's goals. The employee will be able to enter self-ratings once approved.
                        </p>
                        <div class="form-group mb-0">
                            <label style="font-size:0.88rem;font-weight:600;">Remarks <span style="color:#888;font-weight:400;">(optional)</span></label>
                            <textarea name="remark" class="form-control" rows="3"
                                      placeholder="Any comments for the employee..."
                                      style="font-size:0.88rem;resize:vertical;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f0f0f0;justify-content:flex-end;gap:8px;padding:12px 20px;">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-check mr-1"></i> Confirm Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject modal --}}
    <div class="modal fade" id="rejectGoalModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:440px;">
            <div class="modal-content" style="border-radius:10px;overflow:hidden;">
                <div class="modal-header" style="background:#dc3545;color:#fff;border:none;padding:16px 20px;">
                    <h6 class="modal-title mb-0"><i class="fa fa-times-circle mr-2"></i>Reject Employee Goals</h6>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                </div>
                <form method="POST" action="{{ route('goals.reject') }}" id="rejectGoalForm">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $EmployeeId }}">
                    <div class="modal-body" style="padding:20px;">
                        <div class="form-group mb-0">
                            <label style="font-size:0.88rem;font-weight:600;">
                                Reason for Rejection <span style="color:#dc3545;">*</span>
                            </label>
                            <textarea name="remark" id="rejectRemark" class="form-control" rows="4"
                                      placeholder="Describe what needs to be revised..."
                                      style="font-size:0.88rem;resize:vertical;"></textarea>
                            <div id="rejectRemarkError" class="text-danger mt-1" style="font-size:0.82rem;display:none;">
                                Please provide a reason for rejection.
                            </div>
                            <small class="text-muted">The employee will see this reason when they log in.</small>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f0f0f0;justify-content:flex-end;gap:8px;padding:12px 20px;">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger btn-sm" id="rejectConfirmBtn">
                            <i class="fa fa-times mr-1"></i> Confirm Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('rejectConfirmBtn').addEventListener('click', function () {
        var remark = document.getElementById('rejectRemark').value.trim();
        var errEl  = document.getElementById('rejectRemarkError');
        if (!remark) {
            errEl.style.display = 'block';
            document.getElementById('rejectRemark').focus();
            return;
        }
        errEl.style.display = 'none';
        document.getElementById('rejectGoalForm').submit();
    });
    document.getElementById('rejectGoalModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('rejectRemark').value = '';
        document.getElementById('rejectRemarkError').style.display = 'none';
    });
    </script>
@endif

@if($goalDetailsExists)

    {{-- ═══ H1 SECTION ═══ --}}
    @if($h1Goals->isNotEmpty())
        <div class="cycle-section-header">
            <span class="cycle-badge-h1">
                <i class="fa fa-calendar-o mr-1"></i>H1 Goals
                <small style="font-weight:400;">&nbsp;(January – June)</small>
            </span>
            @if($h1Submitted)
                <span style="background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;border-radius:10px;padding:2px 10px;font-size:0.75rem;font-weight:600;margin-left:6px;white-space:nowrap;">
                    <i class="fa fa-lock mr-1"></i> Submitted — View Only
                </span>
            @endif
            <span class="cycle-stats">
                {{ $h1Goals->count() }} goal(s) &bull; Score: <strong>{{ number_format($h1Goals->sum('Weightage'), 2) }}</strong>
            </span>
        </div>

        @foreach($h1Goals as $goal)
            @include('automation.includes._goalcard', [
                'goal'        => $goal,
                'goalNumber'  => $loop->iteration,
                'isSubmitted' => $h1Submitted,
                'isAppraised' => $h1L1Appraised ?? false,
                'isMultiple'  => $isMultiple ?? false,
                'l1AvgScores' => $l1AvgScores ?? collect(),
            ])
        @endforeach
    @endif

    {{-- ═══ H2 SECTION ═══ --}}
    @if($h2Goals->isNotEmpty())
        <div class="cycle-section-header">
            <span class="cycle-badge-h2">
                <i class="fa fa-calendar-o mr-1"></i>H2 Goals
                <small style="font-weight:400;">&nbsp;(July – December)</small>
            </span>
            @if($h2Submitted)
                <span style="background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;border-radius:10px;padding:2px 10px;font-size:0.75rem;font-weight:600;margin-left:6px;white-space:nowrap;">
                    <i class="fa fa-lock mr-1"></i> Submitted — View Only
                </span>
            @endif
            <span class="cycle-stats">
                {{ $h2Goals->count() }} goal(s) &bull; Score: <strong>{{ number_format($h2Goals->sum('Weightage'), 2) }}</strong>
            </span>
        </div>

        @foreach($h2Goals as $goal)
            @include('automation.includes._goalcard', [
                'goal'        => $goal,
                'goalNumber'  => $loop->iteration,
                'isSubmitted' => $h2Submitted,
                'isAppraised' => $h2L1Appraised ?? false,
                'isMultiple'  => $isMultiple ?? false,
                'l1AvgScores' => $l1AvgScores ?? collect(),
            ])
        @endforeach
    @endif

    @if($h1Goals->isEmpty() && $h2Goals->isEmpty())
        <div style="background:#fff;border-radius:6px;padding:40px;text-align:center;color:#999;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
            <i class="fa fa-bullseye fa-3x" style="opacity:.3;margin-bottom:12px;display:block;"></i>
            No goals defined yet.
            <a href="{{ route('goals.create') }}" style="color:#2196F3;">Create your first goal</a>
            or
            <a href="{{ route('goals.import', ['employeeId' => $EmployeeId]) }}" style="color:#2196F3;">import a template</a>.
        </div>
    @endif

@endif
