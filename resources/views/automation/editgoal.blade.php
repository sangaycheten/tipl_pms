@extends('master')
@section('page-title', 'Edit Goals')
@section('page-header', 'Edit Goals')

@section('pagestyles')
<style>
    #goalsAccordion .collapse label,
    #goalsAccordion .collapse .small,
    #goalsAccordion .collapse span:not(.badge):not(.goal-warn) {
        color: #333 !important;
    }
    #goalsAccordion .collapse input.form-control,
    #goalsAccordion .collapse select.custom-select {
        color: #333 !important;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-12 card" style="padding: 16px 18px;">

            <form method="POST" action="{{ route('goals.update', $employeeGoal->Id) }}" id="goalForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="save_action" id="saveAction" value="draft">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-warning">Edit goals below, then save all at once.</span>
                    <div>
                        <a href="{{ route('goals.show', $employeeGoal->Id) }}" class="btn btn-secondary btn-sm">Cancel</a>
                        @if($h1Status && $h2Status)
                            {{-- Both cycles submitted — no point adding more goals --}}
                            <button type="button" class="btn btn-primary btn-sm ml-1" disabled
                                    title="Cannot add goals after both H1 and H2 are submitted">
                                <i class="fa fa-plus"></i> Add Goal
                            </button>
                        @else
                            <button type="button" class="btn btn-primary btn-sm ml-1" onclick="addGoal()">
                                <i class="fa fa-plus"></i> Add Goal
                            </button>
                        @endif
                        <button type="submit" class="btn btn-secondary btn-sm ml-1"
                                onclick="document.getElementById('saveAction').value='draft'">
                            <i class="fa fa-floppy-o"></i> Save as Draft
                        </button>
                        @if($isIndividual)
                        @php $canSubmitNow = $inH1Window || $inH2Window; @endphp
                        <button type="submit" class="btn btn-warning btn-sm ml-1"
                                onclick="document.getElementById('saveAction').value='submit'"
                                {{ $canSubmitNow ? '' : 'disabled' }}
                                title="{{ $canSubmitNow ? '' : 'Submission window: H1 ('.$h1WindowLabel.') · H2 ('.$h2WindowLabel.')' }}">
                            <i class="fa fa-paper-plane"></i> Submit for Approval
                        </button>
                        @else
                        <button type="submit" class="btn btn-success btn-sm ml-1"
                                onclick="document.getElementById('saveAction').value='publish'">
                            <i class="fa fa-check"></i> Publish Goals
                        </button>
                        @endif
                    </div>
                </div>

                @if($h1Status || $h2Status)
                <div class="alert alert-warning mb-3" style="font-size:0.88rem;">
                    <i class="fa fa-lock mr-1"></i>
                    @if($h1Status && $h2Status)
                        <strong>H1 and H2 goals are submitted and locked.</strong> No editing allowed.
                    @elseif($h1Status)
                        <strong>H1 goals are submitted and locked (view only).</strong> You may still add or edit H2 goals.
                    @else
                        <strong>H2 goals are submitted and locked (view only).</strong> You may still add or edit H1 goals.
                    @endif
                </div>
                @endif

                @if($isIndividual && !($inH1Window || $inH2Window))
                <div class="alert alert-info mb-3" style="font-size:0.88rem;">
                    <i class="fa fa-calendar mr-1"></i>
                    <strong>Submission window is closed.</strong>
                    You can save your goals as a draft anytime, but submission for approval is only allowed during:
                    H1 &mdash; <strong>{{ $h1WindowLabel }}</strong> &nbsp;&bull;&nbsp;
                    H2 &mdash; <strong>{{ $h2WindowLabel }}</strong>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <div id="goalsAccordion"></div>

                {{-- ── Common goals: inside the same form ── --}}
                @if(isset($commonGoalDetails) && $commonGoalDetails->isNotEmpty())
                <div class="mt-4" style="border-top:2px dashed #e0e0e0; padding-top:18px;">

                    {{-- Grand total bar --}}
                    <div id="grand-total-bar" style="background:#226b86; color:#fff; border-radius:6px; padding:10px 16px; margin-bottom:14px; font-size:0.88rem;">
                        <strong><i class="fa fa-pie-chart mr-1"></i> Score Budget (Section + Common = 100 per cycle)</strong>
                        <div style="display:flex; gap:32px; margin-top:6px; flex-wrap:wrap;">
                            @php $hasH1 = $commonGoalDetails->where('InH1', 1)->isNotEmpty(); @endphp
                            @php $hasH2 = $commonGoalDetails->where('InH2', 1)->isNotEmpty(); @endphp
                            @if($hasH1)
                            <div>
                                H1: Section <strong id="h1-section">0</strong>
                                + Common <strong id="h1-common">0</strong>
                                = <strong id="h1-grand" style="font-size:1.05em;">0</strong>
                                <span id="h1-status" style="margin-left:4px;"></span>
                            </div>
                            @endif
                            @if($hasH2)
                            <div>
                                H2: Section <strong id="h2-section">0</strong>
                                + Common <strong id="h2-common">0</strong>
                                = <strong id="h2-grand" style="font-size:1.05em;">0</strong>
                                <span id="h2-status" style="margin-left:4px;"></span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="alert" style="background:#fff3cd; border:1px solid #ffc107; font-size:0.85rem; padding:10px 14px; border-radius:4px;">
                        <i class="fa fa-users mr-1"></i>
                        <strong>Common Goals — assign weightage below.</strong>
                        Goal description and tasks are set by the common goal owner (read-only).
                        Enter the goal weightage and task scores for this employee.
                        All goals (section + common) must total 100 per cycle.
                    </div>

                    @foreach($commonGoalDetails as $cg)
                        @php
                            $cgId = $cg->Id;
                            $halfLabel = match(true) {
                                (bool)$cg->InH1 && (bool)$cg->InH2 => 'H1 & H2',
                                (bool)$cg->InH1                     => 'H1',
                                (bool)$cg->InH2                     => 'H2',
                                default                              => 'Full Year',
                            };
                        @endphp
                        <div data-cg-goal="1" data-inh1="{{ $cg->InH1 ? '1' : '0' }}" data-inh2="{{ $cg->InH2 ? '1' : '0' }}"
                             style="border:1px solid #ff9800; border-left:4px solid #ff9800; border-radius:6px;
                                    background:#fff8e1; padding:14px 16px; margin-bottom:14px;">
                            {{-- Header --}}
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px; flex-wrap:wrap;">
                                <span style="font-weight:700; color:#555; font-size:0.85rem;">
                                    Common Goal #{{ (int)($cg->DisplayOrder / 1000) }}
                                </span>
                                <span style="background:#fff3e0; color:#e65100; border:1px solid #ffcc80;
                                      padding:2px 9px; border-radius:12px; font-size:0.75rem; font-weight:600;">
                                    <i class="fa fa-users mr-1"></i>Common Goal
                                </span>
                                <span style="background:#e3f0ff; color:#1565c0; padding:2px 9px;
                                      border-radius:12px; font-size:0.75rem; font-weight:600;">
                                    {{ $halfLabel }}
                                </span>
                                <span style="background:#f5f5f5; color:#777; padding:2px 9px;
                                      border-radius:12px; font-size:0.75rem;">
                                    {{ $cg->Year }}
                                </span>
                            </div>

                            {{-- Description (read-only) --}}
                            <div style="font-weight:600; color:#333; margin-bottom:10px; font-size:0.95rem;">
                                {{ $cg->Description }}
                            </div>

                            {{-- Goal weightage --}}
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                                <label style="font-size:0.82rem; font-weight:600; color:#555; margin:0; white-space:nowrap;">
                                    Goal Weightage:
                                </label>
                                <input type="number"
                                       name="cg_scores[{{ $cgId }}][weightage]"
                                       value="{{ $cg->Weightage > 0 ? number_format($cg->Weightage, 2, '.', '') : '' }}"
                                       placeholder="e.g. 20"
                                       step="0.01" min="0" max="100"
                                       data-cg-score="1"
                                       data-cg-detail="{{ $cgId }}"
                                       oninput="updateGrandTotal(); updateCgTaskTotal('{{ $cgId }}')"
                                       style="width:110px; border:1px solid #f0a500; border-radius:4px;
                                              padding:4px 8px; font-size:0.88rem; color:#333; background:#fff;">
                            </div>

                            {{-- Tasks --}}
                            @if($cg->targets->isNotEmpty())
                            <table style="width:100%; border-collapse:collapse; font-size:0.82rem;">
                                <thead>
                                    <tr style="background:rgba(255,152,0,0.15);">
                                        <th style="padding:6px 8px; border:1px solid #ffe0b2; width:36px; text-align:center;">#</th>
                                        <th style="padding:6px 8px; border:1px solid #ffe0b2;">Task Description</th>
                                        <th style="padding:6px 8px; border:1px solid #ffe0b2; width:110px; text-align:center;">Target</th>
                                        <th style="padding:6px 8px; border:1px solid #ffe0b2; width:140px; text-align:center;">
                                            Task Score
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cg->targets as $ti => $t)
                                    <tr>
                                        <td style="padding:6px 8px; border:1px solid #ffe0b2; text-align:center; color:#666;">{{ $ti + 1 }}</td>
                                        <td style="padding:6px 8px; border:1px solid #ffe0b2; color:#333;">{{ $t->Description }}</td>
                                        <td style="padding:6px 8px; border:1px solid #ffe0b2; text-align:center; color:#555;">{{ $t->Target ?: '—' }}</td>
                                        <td style="padding:4px 6px; border:1px solid #ffe0b2; text-align:center;">
                                            <input type="number"
                                                   name="cg_scores[{{ $cgId }}][tasks][{{ $t->Id }}]"
                                                   value="{{ $t->Weightage > 0 ? number_format($t->Weightage, 2, '.', '') : '' }}"
                                                   placeholder="0"
                                                   step="0.01" min="0"
                                                   data-cg-task="1"
                                                   data-cg-detail="{{ $cgId }}"
                                                   oninput="updateCgTaskTotal('{{ $cgId }}')"
                                                   style="width:90px; border:1px solid #f0a500; border-radius:4px;
                                                          padding:4px 6px; font-size:0.85rem; color:#333; background:#fff;">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background:rgba(255,152,0,0.2);">
                                        <td colspan="3" style="padding:5px 8px; border:1px solid #ffe0b2; text-align:right; font-weight:bold; font-size:0.82rem; color:#555;">
                                            Task Total
                                        </td>
                                        <td style="padding:4px 6px; border:1px solid #ffe0b2; text-align:center;">
                                            <input type="text" id="cg-task-total-{{ $cgId }}" readonly value="0.00"
                                                   style="width:90px; background:#b0c8d4; border:none; padding:3px 6px;
                                                          text-align:right; border-radius:3px; color:#333; font-size:0.85rem;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="padding:3px 8px; border:1px solid #ffe0b2; text-align:right; font-size:0.8rem;">
                                            <span id="cg-task-warn-{{ $cgId }}"></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            @endif
                        </div>
                    @endforeach
                </div>
                @endif

            </form>

        </div>
    </div>
</div>
@stop

@section('pagescripts')
<script>
const H1_SUBMITTED = {{ $h1Status ? 'true' : 'false' }};
const H2_SUBMITTED = {{ $h2Status ? 'true' : 'false' }};
const H1_TARGETS = ['Q1M1','Q1M2','Q1M3','Q2M1','Q2M2','Q2M3'];
const H2_TARGETS = ['Q3M1','Q3M2','Q3M3','Q4M1','Q4M2','Q4M3'];
const ALL_TARGETS = H1_TARGETS.concat(H2_TARGETS);

let goalIndex   = 0;
let nextGoalNum = 1;
let taskIndexes = {};

function getTargetOpts(gIdx) {
    const h1 = document.getElementById('inH1-' + gIdx) ? document.getElementById('inH1-' + gIdx).checked : false;
    const h2 = document.getElementById('inH2-' + gIdx) ? document.getElementById('inH2-' + gIdx).checked : false;
    let opts = [];
    if (h1) opts = opts.concat(H1_TARGETS);
    if (h2) opts = opts.concat(H2_TARGETS);
    opts.push('Custom');
    return opts;
}

function buildTargetOptsHtml(gIdx, selected) {
    return '<option value="">- None -</option>' +
        getTargetOpts(gIdx)
            .map(function(o) { return '<option value="' + o + '"' + (selected === o ? ' selected' : '') + '>' + o + '</option>'; })
            .join('');
}

function refreshGoalTargetSelects(gIdx) {
    document.querySelectorAll('.target-sel[data-gidx="' + gIdx + '"]').forEach(function(sel) {
        const cur = sel.value;
        sel.innerHTML = buildTargetOptsHtml(gIdx, cur);
    });
}

function updateGoalSummary(gIdx) {
    const numEl  = document.querySelector('[name="goals[' + gIdx + '][goal_number]"]');
    const descEl = document.querySelector('[name="goals[' + gIdx + '][description]"]');
    const scEl   = document.querySelector('[name="goals[' + gIdx + '][total_score]"]');
    const h1El   = document.getElementById('inH1-' + gIdx);
    const h2El   = document.getElementById('inH2-' + gIdx);
    const num  = numEl  ? numEl.value  : '?';
    const desc = descEl ? descEl.value : '';
    const sc   = scEl   ? scEl.value   : '';
    const h1   = h1El   ? h1El.checked : false;
    const h2   = h2El   ? h2El.checked : false;
    const hy   = h1 && h2 ? 'H1 & H2' : h1 ? 'H1' : h2 ? 'H2' : '-';
    const el   = document.getElementById('g-summary-' + gIdx);
    if (!el) return;
    const badgeStyle = 'background:#fff;color:#226b86;padding:2px 6px;border-radius:3px;font-size:11px;font-weight:bold;margin-left:6px;';
    el.innerHTML = 'Goal ' + num
        + (desc ? ' &mdash; ' + desc : '')
        + ' <span style="' + badgeStyle + '">Score: ' + (sc || 0) + '</span>'
        + ' <span style="' + badgeStyle + '">' + hy + '</span>';
    updateGrandTotal();
}

function updateWeightSummary(gIdx) {
    const sum = Array.from(document.querySelectorAll('.task-weight[data-gidx="' + gIdx + '"]'))
                     .reduce(function(a, el) { return a + (parseFloat(el.value) || 0); }, 0);
    const roundedSum = Math.round(sum * 100) / 100;
    const totalEl    = document.querySelector('[name="goals[' + gIdx + '][total_score]"]');
    const goalTotal  = totalEl ? (parseFloat(totalEl.value) || 0) : 0;
    const weightEl   = document.getElementById('weight-' + gIdx);
    const warnEl     = document.getElementById('weight-warn-' + gIdx);

    if (weightEl) {
        weightEl.value = roundedSum.toFixed(2);
        weightEl.style.background = goalTotal > 0 && roundedSum > goalTotal ? '#ff9999'
                                  : goalTotal > 0 && roundedSum === goalTotal ? '#99ffbb'
                                  : '#b0c8d4';
    }
    if (warnEl) {
        if (goalTotal === 0) {
            warnEl.innerHTML = '';
        } else if (roundedSum > goalTotal) {
            warnEl.innerHTML = '<span class="goal-warn" style="color:#fff;font-weight:bold;">&#9888; Over by ' + ((roundedSum - goalTotal).toFixed(2)) + '</span>';
        } else if (roundedSum === goalTotal) {
            warnEl.innerHTML = '<span class="goal-warn" style="color:#fff;font-weight:bold;">&#10003; Matched</span>';
        } else {
            warnEl.innerHTML = '<span class="goal-warn" style="color:#fff;">Remaining: ' + ((goalTotal - roundedSum).toFixed(2)) + '</span>';
        }
    }
}

function toggleGoal(gIdx) {
    const el = document.getElementById('gc-' + gIdx);
    if (!el) return;
    const open = el.classList.toggle('show');
    const chevron = document.getElementById('chevron-' + gIdx);
    if (chevron) chevron.className = open ? 'fa fa-chevron-up' : 'fa fa-chevron-down';
}

function goalTemplate(gIdx, goalNum) {
    const year = new Date().getFullYear();
    return ''
    + '<div style="display:flex;align-items:center;border-radius:4px 4px 0 0;overflow:hidden;">'
    +   '<div id="gh-' + gIdx + '" onclick="toggleGoal(' + gIdx + ')"'
    +       ' style="flex:1 1 auto;display:flex;align-items:center;padding:8px 12px;background:#226b86;color:#fff;cursor:pointer;">'
    +     '<span style="flex:1 1 auto;"><strong id="g-summary-' + gIdx + '">Goal ' + goalNum + '</strong></span>'
    +     '<i class="fa fa-chevron-up" id="chevron-' + gIdx + '"></i>'
    +   '</div>'
    +   '<button type="button" class="btn btn-sm btn-danger" style="flex-shrink:0;" onclick="removeGoal(' + gIdx + ')">'
    +     '<i class="fa fa-trash"></i>'
    +   '</button>'
    + '</div>'
    + '<div id="gc-' + gIdx + '" class="collapse show"'
    +     ' style="border:1px solid #226b86;border-top:none;border-radius:0 0 4px 4px;background:#fff;">'
    +   '<div class="p-3" style="color:#333!important;">'
    +     '<div class="row mb-3 align-items-end">'
    +       '<div class="col-md-1"><label class="small mb-1">Goal No</label>'
    +         '<input type="number" name="goals[' + gIdx + '][goal_number]" class="form-control form-control-sm"'
    +                ' value="' + goalNum + '" min="1" required oninput="updateGoalSummary(' + gIdx + ')">'
    +       '</div>'
    +       '<div class="col-md-5"><label class="small mb-1">Description</label>'
    +         '<input type="text" name="goals[' + gIdx + '][description]" class="form-control form-control-sm" required'
    +                ' oninput="updateGoalSummary(' + gIdx + ')">'
    +       '</div>'
    +       '<div class="col-md-2"><label class="small mb-1">Total Score</label>'
    +         '<input type="number" name="goals[' + gIdx + '][total_score]" class="form-control form-control-sm"'
    +                ' step="0.01" min="0" max="100" required'
    +                ' oninput="updateGoalSummary(' + gIdx + '); updateWeightSummary(' + gIdx + ')">'
    +       '</div>'
    +       '<div class="col-md-1"><label class="small mb-1">Year</label>'
    +         '<input type="number" name="goals[' + gIdx + '][year]" class="form-control form-control-sm"'
    +                ' value="' + year + '" required>'
    +       '</div>'
    +       '<div class="col-md-3"><label class="small mb-1">Goal Target</label>'
    +         '<div class="d-flex mt-1">'
    +           '<div class="form-check mr-3">'
    +             '<input class="form-check-input" type="checkbox" name="goals[' + gIdx + '][in_h1]" value="1"'
    +                    ' id="inH1-' + gIdx + '" onchange="refreshGoalTargetSelects(' + gIdx + '); updateGoalSummary(' + gIdx + ')"'
    +                    (H1_SUBMITTED ? ' disabled title="H1 is already submitted — cannot assign new goals to H1"' : '') + '>'
    +             '<label class="form-check-label small" for="inH1-' + gIdx + '" style="' + (H1_SUBMITTED ? 'color:#999;' : '') + '">H1 (Q1+Q2)' + (H1_SUBMITTED ? ' <i class="fa fa-lock" style="font-size:10px;"></i>' : '') + '</label>'
    +           '</div>'
    +           '<div class="form-check">'
    +             '<input class="form-check-input" type="checkbox" name="goals[' + gIdx + '][in_h2]" value="1"'
    +                    ' id="inH2-' + gIdx + '" onchange="refreshGoalTargetSelects(' + gIdx + '); updateGoalSummary(' + gIdx + ')"'
    +                    (H2_SUBMITTED ? ' disabled title="H2 is already submitted — cannot assign new goals to H2"' : '') + '>'
    +             '<label class="form-check-label small" for="inH2-' + gIdx + '" style="' + (H2_SUBMITTED ? 'color:#999;' : '') + '">H2 (Q3+Q4)' + (H2_SUBMITTED ? ' <i class="fa fa-lock" style="font-size:10px;"></i>' : '') + '</label>'
    +           '</div>'
    +         '</div>'
    +       '</div>'
    +     '</div>'
    +     '<table style="width:100%;border-collapse:collapse;margin-top:8px;">'
    +       '<thead><tr style="background:#226b86;color:#fff;">'
    +         '<th style="width:80px;padding:6px 8px;text-align:center;">No.</th>'
    +         '<th style="padding:6px 8px;">Description</th>'
    +         '<th style="width:130px;padding:6px 8px;">Weightage</th>'
    +         '<th style="width:180px;padding:6px 8px;">Target</th>'
    +       '</tr></thead>'
    +       '<tbody id="tasks-' + gIdx + '"></tbody>'
    +       '<tfoot><tr style="background:#226b86;color:#fff;">'
    +         '<td style="padding:4px 6px;text-align:center;">'
    +           '<button type="button" onclick="addTask(' + gIdx + ')"'
    +                   ' style="width:28px;height:28px;border:1px solid #fff;background:transparent;color:#fff;cursor:pointer;font-size:18px;line-height:1;border-radius:3px;">+</button>'
    +         '</td>'
    +         '<td style="padding:6px 8px;text-align:right;font-weight:bold;">Total</td>'
    +         '<td style="padding:4px 6px;">'
    +           '<input type="text" id="weight-' + gIdx + '" readonly value="0.00"'
    +                  ' style="width:100%;background:#b0c8d4;border:none;padding:3px 6px;text-align:right;border-radius:3px;color:#333;">'
    +         '</td>'
    +         '<td style="padding:4px 8px;" id="weight-warn-' + gIdx + '"></td>'
    +       '</tr></tfoot>'
    +     '</table>'
    +   '</div>'
    + '</div>';
}

function taskTemplate(gIdx, tIdx, taskNum) {
    return ''
    + '<td style="padding:4px 6px;text-align:center;">'
    +   '<input type="hidden" name="goals[' + gIdx + '][tasks][' + tIdx + '][task_number]" value="' + taskNum + '">'
    +   '<small style="display:block;color:#666;margin-bottom:2px;">' + taskNum + '</small>'
    +   '<button type="button" onclick="removeTask(' + gIdx + ', ' + tIdx + ')"'
    +           ' style="width:28px;height:28px;border:1px solid #dc3545;background:#dc3545;color:#fff;cursor:pointer;font-size:16px;line-height:1;border-radius:3px;">-</button>'
    + '</td>'
    + '<td style="padding:4px 6px;">'
    +   '<input type="text" name="goals[' + gIdx + '][tasks][' + tIdx + '][description]"'
    +          ' class="form-control form-control-sm" required style="color:#333;">'
    + '</td>'
    + '<td style="padding:4px 6px;">'
    +   '<input type="number" name="goals[' + gIdx + '][tasks][' + tIdx + '][weightage]"'
    +          ' class="form-control form-control-sm task-weight" data-gidx="' + gIdx + '"'
    +          ' step="0.01" min="0" required oninput="updateWeightSummary(' + gIdx + ')" style="color:#333;">'
    + '</td>'
    + '<td style="padding:4px 6px;">'
    +   '<select name="goals[' + gIdx + '][tasks][' + tIdx + '][target]"'
    +           ' class="custom-select custom-select-sm target-sel"'
    +           ' data-gidx="' + gIdx + '" data-tidx="' + tIdx + '" style="color:#333;"></select>'
    +   '<input type="text" name="goals[' + gIdx + '][tasks][' + tIdx + '][target_custom]"'
    +          ' id="tc-' + gIdx + '-' + tIdx + '"'
    +          ' class="form-control form-control-sm mt-1 d-none"'
    +          ' placeholder="Custom target" style="color:#333;">'
    + '</td>';
}

function addGoal() {
    if (H1_SUBMITTED && H2_SUBMITTED) {
        alert('Both H1 and H2 have been submitted. No new goals can be added.');
        return;
    }
    const gIdx = goalIndex;
    const div  = document.createElement('div');
    div.className = 'mb-2';
    div.id = 'goal-item-' + gIdx;
    div.innerHTML = goalTemplate(gIdx, nextGoalNum);
    document.getElementById('goalsAccordion').appendChild(div);
    $('#gc-' + gIdx).collapse({ toggle: false });
    taskIndexes[gIdx] = 0;
    addTask(gIdx);
    goalIndex++;
    nextGoalNum++;
}

function addTask(gIdx) {
    const tbody  = document.getElementById('tasks-' + gIdx);
    const tIdx   = taskIndexes[gIdx];
    const numEl  = document.querySelector('[name="goals[' + gIdx + '][goal_number]"]');
    const goalNum = numEl ? numEl.value : '?';
    const taskNum = goalNum + '.' + (tIdx + 1);
    const tr = document.createElement('tr');
    tr.id = 'task-' + gIdx + '-' + tIdx;
    tr.innerHTML = taskTemplate(gIdx, tIdx, taskNum);
    tbody.appendChild(tr);
    tr.querySelector('.target-sel').innerHTML = buildTargetOptsHtml(gIdx, '');
    taskIndexes[gIdx]++;
    updateWeightSummary(gIdx);
}

function removeGoal(gIdx) {
    const el = document.getElementById('goal-item-' + gIdx);
    if (el) el.remove();
}

function removeTask(gIdx, tIdx) {
    const el = document.getElementById('task-' + gIdx + '-' + tIdx);
    if (el) el.remove();
    updateWeightSummary(gIdx);
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('target-sel')) {
        const gIdx = e.target.dataset.gidx;
        const tIdx = e.target.dataset.tidx;
        const custom = document.getElementById('tc-' + gIdx + '-' + tIdx);
        if (custom) custom.classList.toggle('d-none', e.target.value !== 'Custom');
    }
    // H1/H2 checkbox changes affect grand total
    if (e.target.name && e.target.name.match(/goals\[\d+\]\[in_h[12]\]/)) {
        updateGrandTotal();
    }
});

function updateCgTaskTotal(detailId) {
    const taskInputs = document.querySelectorAll('[data-cg-task][data-cg-detail="' + detailId + '"]');
    const scoreInput = document.querySelector('[data-cg-score][data-cg-detail="' + detailId + '"]');
    const totalEl    = document.getElementById('cg-task-total-' + detailId);
    const warnEl     = document.getElementById('cg-task-warn-'  + detailId);
    const taskTotal  = Array.from(taskInputs).reduce(function(s, el) { return s + (parseFloat(el.value) || 0); }, 0);
    const goalWt     = parseFloat(scoreInput ? scoreInput.value : 0) || 0;
    if (totalEl) {
        totalEl.value = taskTotal.toFixed(2);
        totalEl.style.background = goalWt > 0 && taskTotal > goalWt          ? '#ff9999'
                                 : goalWt > 0 && Math.abs(taskTotal - goalWt) < 0.005 ? '#99ffbb'
                                 : '#b0c8d4';
    }
    if (warnEl) {
        if (goalWt === 0)                              warnEl.innerHTML = '';
        else if (taskTotal > goalWt)                   warnEl.innerHTML = '<span style="color:#dc3545;font-weight:bold;">&#9888; Over by '   + (taskTotal - goalWt).toFixed(2) + '</span>';
        else if (Math.abs(taskTotal - goalWt) < 0.005) warnEl.innerHTML = '<span style="color:#28a745;font-weight:bold;">&#10003; Matched</span>';
        else                                            warnEl.innerHTML = '<span style="color:#888;">Remaining: ' + (goalWt - taskTotal).toFixed(2) + '</span>';
    }
    updateGrandTotal();
}

function updateGrandTotal() {
    let h1Sec = 0, h2Sec = 0, h1Com = 0, h2Com = 0;

    // Sum section goal scores from the accordion form
    document.querySelectorAll('#goalsAccordion [name]').forEach(function(inp) {
        const m = inp.name.match(/^goals\[(\d+)\]\[total_score\]$/);
        if (!m) return;
        const gIdx = m[1];
        const h1cb = document.getElementById('inH1-' + gIdx);
        const h2cb = document.getElementById('inH2-' + gIdx);
        const score = parseFloat(inp.value) || 0;
        if (h1cb && h1cb.checked) h1Sec += score;
        if (h2cb && h2cb.checked) h2Sec += score;
    });

    // Sum common goal scores from the common score form
    document.querySelectorAll('[data-cg-goal]').forEach(function(container) {
        const inH1  = container.dataset.inh1 === '1';
        const inH2  = container.dataset.inh2 === '1';
        const scoreInp = container.querySelector('[data-cg-score]');
        const score = parseFloat(scoreInp ? scoreInp.value : 0) || 0;
        if (inH1) h1Com += score;
        if (inH2) h2Com += score;
    });

    function setTotal(secId, comId, grandId, statusId, sec, com) {
        const grandEl  = document.getElementById(grandId);
        const secEl    = document.getElementById(secId);
        const comEl    = document.getElementById(comId);
        const statusEl = document.getElementById(statusId);
        if (!grandEl) return;
        const total = sec + com;
        const ok = Math.abs(total - 100) < 0.005;
        if (secEl)    secEl.textContent    = sec.toFixed(2);
        if (comEl)    comEl.textContent    = com.toFixed(2);
        grandEl.textContent = total.toFixed(2);
        grandEl.style.color = ok ? '#90ee90' : '#ff6b6b';
        if (statusEl) statusEl.innerHTML = ok
            ? '<span style="color:#90ee90;">&#10003; Matched</span>'
            : '<span style="color:#ff6b6b;">/ 100</span>';
    }

    setTotal('h1-section', 'h1-common', 'h1-grand', 'h1-status', h1Sec, h1Com);
    setTotal('h2-section', 'h2-common', 'h2-grand', 'h2-status', h2Sec, h2Com);
}

// ── Pre-populate with existing goals ────────────────────────────────────────
const existingGoals = {!! json_encode($goalsJson) !!};

existingGoals.forEach(function(g) {
    const gIdx = goalIndex;
    const div  = document.createElement('div');
    div.className = 'mb-2';
    div.id = 'goal-item-' + gIdx;
    div.innerHTML = goalTemplate(gIdx, g.goal_number);
    document.getElementById('goalsAccordion').appendChild(div);
    $('#gc-' + gIdx).collapse({ toggle: false });
    taskIndexes[gIdx] = 0;

    // Fill goal-level fields
    document.querySelector('[name="goals[' + gIdx + '][goal_number]"]').value  = g.goal_number;
    document.querySelector('[name="goals[' + gIdx + '][description]"]').value  = g.description;
    document.querySelector('[name="goals[' + gIdx + '][total_score]"]').value  = g.total_score;
    document.querySelector('[name="goals[' + gIdx + '][year]"]').value         = g.year;

    if (g.in_h1) document.getElementById('inH1-' + gIdx).checked = true;
    if (g.in_h2) document.getElementById('inH2-' + gIdx).checked = true;
    refreshGoalTargetSelects(gIdx);

    // Fill tasks
    g.tasks.forEach(function(task) {
        const tIdx    = taskIndexes[gIdx];
        const numEl   = document.querySelector('[name="goals[' + gIdx + '][goal_number]"]');
        const goalNum = numEl ? numEl.value : g.goal_number;
        const taskNum = goalNum + '.' + (tIdx + 1);
        const tr = document.createElement('tr');
        tr.id = 'task-' + gIdx + '-' + tIdx;
        tr.innerHTML = taskTemplate(gIdx, tIdx, taskNum);
        document.getElementById('tasks-' + gIdx).appendChild(tr);

        // Description & weightage
        tr.querySelector('[name="goals[' + gIdx + '][tasks][' + tIdx + '][description]"]').value = task.description;
        tr.querySelector('[name="goals[' + gIdx + '][tasks][' + tIdx + '][weightage]"]').value   = task.weightage;

        // Target — detect custom vs predefined
        const sel = tr.querySelector('.target-sel');
        if (ALL_TARGETS.includes(task.target)) {
            sel.innerHTML = buildTargetOptsHtml(gIdx, task.target);
        } else if (task.target && task.target !== '-') {
            sel.innerHTML = buildTargetOptsHtml(gIdx, 'Custom');
            const customInput = document.getElementById('tc-' + gIdx + '-' + tIdx);
            if (customInput) { customInput.classList.remove('d-none'); customInput.value = task.target; }
        } else {
            sel.innerHTML = buildTargetOptsHtml(gIdx, '');
        }

        taskIndexes[gIdx]++;
    });

    updateGoalSummary(gIdx);
    updateWeightSummary(gIdx);
    updateGrandTotal();

    // Lock inputs for goals in submitted cycles
    if (g.locked) {
        const panel = document.getElementById('goal-item-' + gIdx);
        panel.querySelectorAll('input, select, textarea, button').forEach(function(el) {
            el.disabled = true;
        });
        // Add locked badge to accordion header (div#gh-{gIdx})
        const header = document.getElementById('gh-' + gIdx);
        if (header) {
            const badge = document.createElement('span');
            badge.style.cssText = 'background:#fff3cd;color:#856404;border:1px solid #ffc107;border-radius:4px;padding:2px 7px;font-size:0.72rem;font-weight:bold;margin-left:8px;white-space:nowrap;';
            badge.innerHTML = '<i class="fa fa-lock"></i> Submitted — View Only';
            const summarySpan = header.querySelector('span[style*="flex"]');
            if (summarySpan) summarySpan.appendChild(badge);
            else header.insertBefore(badge, header.querySelector('i.fa-chevron-up'));
        }
    }

    goalIndex++;
    nextGoalNum = Math.max(nextGoalNum, g.goal_number + 1);
});
</script>
@stop
