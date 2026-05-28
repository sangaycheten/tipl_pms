@extends('master')
@section('page-title', 'L1 Appraisal — ' . ($employee->Name ?? '') . ' (' . $cycle . ')')

@section('pagestyles')
<style>
    /* ── Page wrapper ───────────────────────────────────────────── */
    .appraise-page { background:#f0f2f5; min-height:100vh; padding-bottom:40px; }

    /* ── Employee header card ───────────────────────────────────── */
    .emp-header-card {
        background:#fff;
        border-radius:8px;
        box-shadow:0 1px 4px rgba(0,0,0,0.10);
        padding:18px 24px;
        margin-bottom:20px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:12px;
    }
    .emp-avatar {
        width:46px; height:46px;
        border-radius:50%;
        background:linear-gradient(135deg,#1976D2,#42A5F5);
        color:#fff;
        font-size:1.15rem;
        font-weight:700;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
    }
    .emp-info-wrap { display:flex; align-items:center; gap:14px; }
    .emp-name {
        font-size:1.05rem;
        font-weight:700;
        color:#1a1a2e;
        margin:0 0 2px 0;
        line-height:1.3;
    }
    .emp-id {
        font-size:0.82rem;
        color:#999;
        font-weight:500;
        margin-left:6px;
    }
    .emp-meta {
        font-size:0.83rem;
        color:#666;
        margin:0;
    }
    .emp-meta .sep { margin:0 5px; color:#ccc; }

    /* ── Cycle & status badges ──────────────────────────────────── */
    .cycle-badge { display:inline-flex; align-items:center; padding:4px 14px; border-radius:20px; font-size:0.82rem; font-weight:700; }
    .cycle-badge.H1 { background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; }
    .cycle-badge.H2 { background:#fff3e0; color:#e65100; border:1px solid #ffe0b2; }
    .multiple-badge { background:#fce4ec; color:#880e4f; padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; border:1px solid #f8bbd0; }
    .submitted-badge { background:#e8f5e9; color:#2e7d32; padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; border:1px solid #c8e6c9; }

    /* ── Submitted notice ───────────────────────────────────────── */
    .submitted-notice {
        background:#e8f5e9;
        border:1px solid #a5d6a7;
        border-radius:8px;
        padding:12px 18px;
        margin-bottom:18px;
        font-size:0.88rem;
        color:#1b5e20;
        display:flex;
        align-items:center;
        gap:8px;
    }

    /* ── Goal cards ─────────────────────────────────────────────── */
    .goal-card {
        background:#fff;
        border-radius:8px;
        border-left:4px solid #2196F3;
        box-shadow:0 1px 4px rgba(0,0,0,0.09);
        margin-bottom:18px;
        overflow:hidden;
    }
    .goal-card.locked { border-left-color:#bbb; }
    .goal-card-header {
        padding:14px 20px 10px;
        border-bottom:1px solid #f0f2f5;
    }
    .goal-card-body { padding:0 0 4px; }
    .goal-meta { display:flex; align-items:center; flex-wrap:wrap; gap:7px; margin-bottom:6px; }
    .goal-number { font-size:0.75rem; color:#aaa; font-weight:700; letter-spacing:.5px; text-transform:uppercase; }
    .badge-half  { background:#e3f0ff; color:#1565c0; padding:2px 9px; border-radius:12px; font-size:0.75rem; font-weight:600; }
    .badge-year  { background:#f3f3f3; color:#666; padding:2px 9px; border-radius:12px; font-size:0.75rem; }
    .badge-common-goal  { background:#fff3e0; color:#e65100; border:1px solid #ffcc80; padding:2px 9px; border-radius:12px; font-size:0.75rem; font-weight:600; }
    .badge-section-goal { background:#f3e5f5; color:#6a1b9a; border:1px solid #ce93d8; padding:2px 9px; border-radius:12px; font-size:0.75rem; font-weight:600; }
    .goal-title  { font-weight:600; font-size:0.97rem; color:#1a1a2e; margin-bottom:4px; line-height:1.45; }
    .goal-stats  { font-size:0.8rem; color:#888; }
    .goal-stats strong { color:#444; }

    /* ── Task table ─────────────────────────────────────────────── */
    .task-table { margin-bottom:0 !important; }
    .task-table thead th {
        background:#f8f9fa !important;
        color:#555 !important;
        font-size:0.78rem !important;
        font-weight:600 !important;
        border-bottom:2px solid #e9ecef !important;
        padding:8px 10px !important;
        white-space:nowrap;
    }
    .task-table tbody td {
        vertical-align:middle !important;
        font-size:0.83rem !important;
        color:#333 !important;
        background:#fff !important;
        padding:8px 10px !important;
        border-color:#f0f2f5 !important;
    }
    .task-table tbody tr:hover td { background:#f9fbff !important; }
    .self-val {
        display:inline-block;
        background:#f0f4ff;
        padding:2px 8px;
        border-radius:4px;
        font-size:0.8rem;
        color:#333;
        max-width:200px;
        word-break:break-word;
    }
    .l1-score-input {
        border:1px solid #90caf9 !important;
        background:#fff !important;
        color:#333 !important;
        text-align:right;
    }
    .l1-score-input:focus {
        border-color:#1976D2 !important;
        box-shadow:0 0 0 2px rgba(25,118,210,0.12) !important;
    }
    .l1-remarks-input {
        background:#fff !important;
        color:#333 !important;
        border:1px solid #dde2e8 !important;
    }

    /* ── Action bar ─────────────────────────────────────────────── */
    .action-bar {
        background:#fff;
        border-radius:8px;
        box-shadow:0 1px 4px rgba(0,0,0,0.09);
        padding:14px 20px;
        display:flex;
        align-items:center;
        justify-content:flex-end;
        gap:10px;
        margin-top:4px;
        margin-bottom:24px;
    }
</style>
@endsection

@section('content')
<div class="appraise-page">
<div class="container-fluid pt-3">

    {{-- Flash messages --}}
    @if(session('successmessage'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle mr-1"></i> {{ session('successmessage') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Employee header card --}}
    <div class="emp-header-card">
        <div class="emp-info-wrap">
            <div class="emp-avatar">
                {{ strtoupper(substr($employee->Name ?? 'E', 0, 1)) }}
            </div>
            <div>
                <p class="emp-name">
                    {{ $employee->Name ?? '—' }}
                    <span class="emp-id">{{ $employee->EmpId ?? '' }}</span>
                </p>
                <p class="emp-meta">
                    <span>{{ $employee->Designation ?? '' }}</span>
                    <span class="sep">&bull;</span>
                    <span>{{ $employee->Department ?? '' }}</span>
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
            <span class="cycle-badge {{ $cycle }}">
                <i class="fa fa-calendar mr-1"></i> {{ $cycle }} Appraisal
            </span>
            @if($isMultiple)
                <span class="multiple-badge"><i class="fa fa-users mr-1"></i> Multiple L1 Appraisers</span>
            @endif
            @if($isSubmitted)
                <span class="submitted-badge"><i class="fa fa-check mr-1"></i> Submitted</span>
            @endif
            <a href="{{ route('goals.appraise.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Submitted notice --}}
    @if($isSubmitted)
        <div class="submitted-notice">
            <i class="fa fa-check-circle fa-lg"></i>
            <span>
                <strong>Appraisal submitted</strong> on
                {{ \Carbon\Carbon::parse($submission->SubmittedAt)->format('d M Y, H:i') }}.
                All scores are now locked.
            </span>
        </div>
    @endif

    {{-- Goals --}}
    @if($goalDetails->isEmpty())
        <div class="alert alert-info">
            <i class="fa fa-info-circle mr-1"></i> No goals found for {{ $cycle }}.
        </div>
    @else
        <form method="POST"
              action="{{ route('goals.appraise.save', [$employeeId, strtolower($cycle)]) }}"
              id="appraisalForm">
            @csrf

            @foreach($goalDetails as $goal)
                @php
                    $halfLabel = match(true) {
                        (bool)$goal->InH1 && (bool)$goal->InH2 => 'H1 & H2',
                        (bool)$goal->InH1                       => 'H1',
                        default                                  => 'H2',
                    };
                @endphp

                <div class="goal-card {{ $isSubmitted ? 'locked' : '' }}">
                    <div class="goal-card-header">
                        <div class="goal-meta">
                            <span class="goal-number">Goal #{{ $loop->iteration }}</span>
                            <span class="badge-half">{{ $halfLabel }}</span>
                            <span class="badge-year">{{ $goal->Year }}</span>
                            @if((int)$goal->GoalType === 2)
                                <span class="badge-common-goal"><i class="fa fa-users mr-1"></i>Common Goal</span>
                            @else
                                <span class="badge-section-goal"><i class="fa fa-sitemap mr-1"></i>Section Goal</span>
                            @endif
                        </div>
                        <div class="goal-title">{{ $goal->Description }}</div>
                        <div class="goal-stats">
                            Score: <strong>{{ number_format($goal->Weightage, 2) }}</strong>
                            &nbsp;&bull;&nbsp;
                            <strong>{{ $goal->targets->count() }}</strong> task(s)
                        </div>
                    </div>

                    @if($goal->targets->isNotEmpty())
                        <div class="goal-card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered task-table">
                                    <thead>
                                        <tr>
                                            <th style="width:36px;">#</th>
                                            <th>Task Description</th>
                                            <th style="width:75px;" class="text-center">Weight</th>
                                            <th style="width:120px;">Target</th>
                                            <th style="width:130px;">Achievement</th>
                                            <th style="width:105px;" class="text-center">Self Score</th>
                                            <th style="min-width:150px;">Self Remarks</th>
                                            <th style="width:105px;" class="text-center">L1 Score</th>
                                            <th style="min-width:160px;">L1 Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($goal->targets as $task)
                                            @php
                                                $existing  = $existingScores->get($task->Id);
                                                $l1Score   = $existing ? $existing->Level1Score   : null;
                                                $l1Remarks = $existing ? $existing->Level1Remarks : null;
                                            @endphp
                                            <tr>
                                                <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                                <td>{{ $task->Description }}</td>
                                                <td class="text-center">
                                                    <span class="self-val">{{ number_format($task->Weightage, 2) }}</span>
                                                </td>
                                                <td>
                                                    <span class="self-val">{{ $task->Target ?? '—' }}</span>
                                                </td>
                                                <td>
                                                    @if($task->Achievement)
                                                        <span class="self-val">{{ $task->Achievement }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="self-val" style="background:#e8f5e9; color:#2e7d32;">
                                                        {{ $task->SelfScore !== null ? number_format($task->SelfScore, 2) : '—' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="self-val" style="max-width:200px; display:inline-block;">
                                                        {{ $task->SelfRemarks ?: '—' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number"
                                                           name="tasks[{{ $task->Id }}][l1_score]"
                                                           class="form-control form-control-sm l1-score-input"
                                                           value="{{ old('tasks.'.$task->Id.'.l1_score', $l1Score) }}"
                                                           min="0"
                                                           max="{{ $task->Weightage }}"
                                                           step="0.01"
                                                           placeholder="0.00"
                                                           {{ $isSubmitted ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="tasks[{{ $task->Id }}][l1_remarks]"
                                                           class="form-control form-control-sm l1-remarks-input"
                                                           value="{{ old('tasks.'.$task->Id.'.l1_remarks', $l1Remarks) }}"
                                                           placeholder="Enter remarks"
                                                           {{ $isSubmitted ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Action buttons --}}
            @if(!$isSubmitted)
                <div id="appraisal-submit-error" class="alert alert-danger d-none mb-2"
                     style="font-size:0.87rem; padding:8px 14px; border-radius:6px;"></div>

                <div class="action-bar">
                    <button type="button" class="btn btn-secondary btn-sm px-4" id="btn-save-draft">
                        <i class="fa fa-floppy-o mr-1"></i> Save Draft
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-4" id="btn-submit">
                        <i class="fa fa-paper-plane mr-1"></i> Submit {{ $cycle }} Appraisal
                    </button>
                </div>
            @endif

        </form>
    @endif

</div>
</div>

{{-- Confirm submit modal --}}
<div class="modal fade" id="submitConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#1976D2; border-bottom:none; padding:16px 20px;">
                <h5 class="modal-title" style="font-size:1rem; font-weight:700; color:#fff;">
                    <i class="fa fa-paper-plane mr-2"></i> Submit {{ $cycle }} Appraisal?
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:0.8;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding:20px; color:#333; font-size:0.9rem; background:#fff;">
                Once submitted, your <strong>{{ $cycle }}</strong> appraisal scores for
                <strong>{{ $employee->Name ?? 'this employee' }}</strong> will be locked
                and cannot be changed. Please ensure all scores and remarks are complete.
            </div>
            <div class="modal-footer" style="background:#fff; border-top:1px solid #f0f2f5; padding:12px 20px;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="confirmSubmitBtn">
                    <i class="fa fa-paper-plane mr-1"></i> Yes, Submit
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    var form      = document.getElementById('appraisalForm');
    var errEl     = document.getElementById('appraisal-submit-error');
    var draftBtn  = document.getElementById('btn-save-draft');
    var submitBtn = document.getElementById('btn-submit');

    if (!form) return;

    // Clamp L1 score between 0 and max weightage
    form.querySelectorAll('.l1-score-input').forEach(function (input) {
        input.addEventListener('input', function () {
            var max = parseFloat(this.getAttribute('max'));
            var val = parseFloat(this.value);
            if (!isNaN(val) && !isNaN(max) && val > max) this.value = max.toFixed(2);
            if (!isNaN(val) && val < 0) this.value = '0.00';
        });
    });

    // Save Draft
    if (draftBtn) {
        draftBtn.addEventListener('click', function () {
            appendAction('draft');
            form.submit();
        });
    }

    // Submit — validate all L1 scores filled, then show confirm modal
    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            if (errEl) errEl.classList.add('d-none');

            var scoreInputs = form.querySelectorAll('.l1-score-input:not([disabled])');
            var missing = 0;
            scoreInputs.forEach(function (inp) {
                if (inp.value === '' || inp.value === null) missing++;
            });
            if (missing > 0) {
                if (errEl) {
                    errEl.textContent = 'Please enter L1 scores for all tasks before submitting ('
                        + missing + ' task' + (missing > 1 ? 's' : '') + ' still empty). Enter 0 if the score is zero.';
                    errEl.classList.remove('d-none');
                    errEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                return;
            }

            $('#submitConfirmModal').modal('show');
        });
    }

    // Confirm submit
    var confirmBtn = document.getElementById('confirmSubmitBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            $('#submitConfirmModal').modal('hide');
            appendAction('submit');
            form.submit();
        });
    }

    function appendAction(action) {
        var existing = form.querySelector('[name="save_action"]');
        if (existing) existing.remove();
        var inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'save_action';
        inp.value = action;
        form.appendChild(inp);
    }
});
</script>
@endsection
