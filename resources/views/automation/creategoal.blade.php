@extends('master')
@section('page-title', 'Create Goals')
@section('page-header', 'Create Goals')
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-12 card" style="padding: 16px 18px;">

            <form method="POST" action="{{ route('goals.store') }}" id="goalForm">
                @csrf
                <input type="hidden" name="save_action" id="saveAction" value="draft">

                @if($employeeId)
                    <input type="hidden" name="employee_id" value="{{ $employeeId }}">
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-warning">Add one or more goals, then save all at once.</span>
                    <div>
                        <a href="{{ route('goals.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                        <button type="button" class="btn btn-primary btn-sm ml-1" onclick="addGoal()">
                            <i class="fa fa-plus"></i> Add Multiple Goals
                        </button>
                        <button type="submit" class="btn btn-secondary btn-sm ml-1"
                                onclick="document.getElementById('saveAction').value='draft'">
                            <i class="fa fa-floppy-o"></i> Save as Draft
                        </button>
                        @if($isIndividual)
                        <button type="submit" class="btn btn-warning btn-sm ml-1"
                                onclick="document.getElementById('saveAction').value='submit'">
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

                <div id="goalsAccordion"></div>

            </form>

        </div>
    </div>
</div>
@stop

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
@stop

@section('pagescripts')
<script>
const H1_TARGETS = ['Q1','Q1M1','Q1M2','Q1M3','Q2','Q2M1','Q2M2','Q2M3'];
const H2_TARGETS = ['Q3','Q3M1','Q3M2','Q3M3','Q4','Q4M1','Q4M2','Q4M3'];

let goalIndex  = 0;
let nextGoalNum = {{ $nextGoalNumber }};
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
}

function updateWeightSummary(gIdx) {
    const sum = Array.from(document.querySelectorAll('.task-weight[data-gidx="' + gIdx + '"]'))
                     .reduce(function(a, el) { return a + (parseFloat(el.value) || 0); }, 0);
    const roundedSum = Math.round(sum * 100) / 100;

    const totalEl = document.querySelector('[name="goals[' + gIdx + '][total_score]"]');
    const goalTotal = totalEl ? (parseFloat(totalEl.value) || 0) : 0;

    const weightEl = document.getElementById('weight-' + gIdx);
    const warnEl   = document.getElementById('weight-warn-' + gIdx);

    if (weightEl) {
        weightEl.value = roundedSum.toFixed(2);
        if (goalTotal > 0 && roundedSum > goalTotal) {
            weightEl.style.background = '#ff9999';
        } else if (goalTotal > 0 && roundedSum === goalTotal) {
            weightEl.style.background = '#99ffbb';
        } else {
            weightEl.style.background = '#b0c8d4';
        }
    }

    if (warnEl) {
        if (goalTotal === 0) {
            warnEl.innerHTML = '';
        } else if (roundedSum > goalTotal) {
            var over = (Math.round((roundedSum - goalTotal) * 100) / 100).toFixed(2);
            warnEl.innerHTML = '<span class="goal-warn" style="color:#fff;font-weight:bold;">&#9888; Over by ' + over + '</span>';
        } else if (roundedSum === goalTotal) {
            warnEl.innerHTML = '<span class="goal-warn" style="color:#fff;font-weight:bold;">&#10003; Matched</span>';
        } else {
            var remaining = (Math.round((goalTotal - roundedSum) * 100) / 100).toFixed(2);
            warnEl.innerHTML = '<span class="goal-warn" style="color:#fff;">Remaining: ' + remaining + '</span>';
        }
    }
}

function toggleGoal(gIdx) {
    const el = document.getElementById('gc-' + gIdx);
    if (!el) return;
    const open = el.classList.toggle('show');
    const chevron = document.getElementById('chevron-' + gIdx);
    if (chevron) {
        chevron.className = open ? 'fa fa-chevron-up' : 'fa fa-chevron-down';
    }
}

function addGoal() {
    const gIdx = goalIndex;
    const accordion = document.getElementById('goalsAccordion');
    const div = document.createElement('div');
    div.className = 'mb-2';
    div.id = 'goal-item-' + gIdx;
    div.innerHTML = goalTemplate(gIdx, nextGoalNum);
    accordion.appendChild(div);
    $('#gc-' + gIdx).collapse({ toggle: false });
    taskIndexes[gIdx] = 0;
    addTask(gIdx);
    goalIndex++;
    nextGoalNum++;
}

function removeGoal(gIdx) {
    const el = document.getElementById('goal-item-' + gIdx);
    if (el) el.remove();
}

function goalTemplate(gIdx, goalNum) {
    const year = new Date().getFullYear();
    return ''
    + '<div style="display:flex;align-items:center;border-radius:4px 4px 0 0;overflow:hidden;">'
    +   '<div id="gh-' + gIdx + '"'
    +       ' onclick="toggleGoal(' + gIdx + ')"'
    +       ' style="flex:1 1 auto;display:flex;align-items:center;padding:8px 12px;background:#226b86;color:#fff;cursor:pointer;">'
    +     '<span style="flex:1 1 auto;"><strong id="g-summary-' + gIdx + '">Goal ' + goalNum + '</strong></span>'
    +     '<i class="fa fa-chevron-up" id="chevron-' + gIdx + '"></i>'
    +   '</div>'
    +   '<button type="button" class="btn btn-sm btn-danger"'
    +           ' style="flex-shrink:0;"'
    +           ' onclick="removeGoal(' + gIdx + ')">'
    +     '<i class="fa fa-trash"></i>'
    +   '</button>'
    + '</div>'
    + '<div id="gc-' + gIdx + '" class="collapse show"'
    +     ' style="border:1px solid #226b86;border-top:none;border-radius:0 0 4px 4px;background:#fff;">'
    +   '<div class="p-3" style="color:#333!important;">'

    +     '<div class="row mb-3 align-items-end">'
    +       '<div class="col-md-1">'
    +         '<label class="small mb-1">Goal No</label>'
    +         '<input type="number" name="goals[' + gIdx + '][goal_number]"'
    +                ' class="form-control form-control-sm"'
    +                ' value="' + goalNum + '" min="1" required'
    +                ' oninput="updateGoalSummary(' + gIdx + ')">'
    +       '</div>'
    +       '<div class="col-md-5">'
    +         '<label class="small mb-1">Description</label>'
    +         '<input type="text" name="goals[' + gIdx + '][description]"'
    +                ' class="form-control form-control-sm" required'
    +                ' oninput="updateGoalSummary(' + gIdx + ')">'
    +       '</div>'
    +       '<div class="col-md-2">'
    +         '<label class="small mb-1">Total Score</label>'
    +         '<input type="number" name="goals[' + gIdx + '][total_score]"'
    +                ' class="form-control form-control-sm"'
    +                ' step="0.01" min="0" max="100" required'
    +                ' oninput="updateGoalSummary(' + gIdx + '); updateWeightSummary(' + gIdx + ')">'
    +       '</div>'
    +       '<div class="col-md-1">'
    +         '<label class="small mb-1">Year</label>'
    +         '<input type="number" name="goals[' + gIdx + '][year]"'
    +                ' class="form-control form-control-sm"'
    +                ' value="' + year + '" required>'
    +       '</div>'
    +       '<div class="col-md-3">'
    +         '<label class="small mb-1">Goal Target</label>'
    +         '<div class="d-flex mt-1">'
    +           '<div class="form-check mr-3">'
    +             '<input class="form-check-input" type="checkbox"'
    +                    ' name="goals[' + gIdx + '][in_h1]" value="1" id="inH1-' + gIdx + '"'
    +                    ' onchange="refreshGoalTargetSelects(' + gIdx + '); updateGoalSummary(' + gIdx + ')">'
    +             '<label class="form-check-label small" for="inH1-' + gIdx + '">H1 (Q1+Q2)</label>'
    +           '</div>'
    +           '<div class="form-check">'
    +             '<input class="form-check-input" type="checkbox"'
    +                    ' name="goals[' + gIdx + '][in_h2]" value="1" id="inH2-' + gIdx + '"'
    +                    ' onchange="refreshGoalTargetSelects(' + gIdx + '); updateGoalSummary(' + gIdx + ')">'
    +             '<label class="form-check-label small" for="inH2-' + gIdx + '">H2 (Q3+Q4)</label>'
    +           '</div>'
    +         '</div>'
    +       '</div>'
    +     '</div>'

    +     '<table style="width:100%;border-collapse:collapse;margin-top:8px;">'
    +       '<thead>'
    +         '<tr style="background:#226b86;color:#fff;">'
    +           '<th style="width:80px;padding:6px 8px;text-align:center;">No.</th>'
    +           '<th style="padding:6px 8px;text-align:left;">Description</th>'
    +           '<th style="width:130px;padding:6px 8px;text-align:left;">Weightage (W)</th>'
    +           '<th style="width:180px;padding:6px 8px;text-align:left;">Target (T)</th>'
    +         '</tr>'
    +       '</thead>'
    +       '<tbody id="tasks-' + gIdx + '"></tbody>'
    +       '<tfoot>'
    +         '<tr style="background:#226b86;color:#fff;">'
    +           '<td style="padding:4px 6px;text-align:center;">'
    +             '<button type="button" onclick="addTask(' + gIdx + ')"'
    +                     ' style="width:28px;height:28px;border:1px solid #fff;background:transparent;color:#fff;cursor:pointer;font-size:18px;line-height:1;border-radius:3px;">+</button>'
    +           '</td>'
    +           '<td style="padding:6px 8px;text-align:right;font-weight:bold;">Total</td>'
    +           '<td style="padding:4px 6px;">'
    +             '<input type="text" id="weight-' + gIdx + '" readonly value="0.00"'
    +                    ' style="width:100%;background:#b0c8d4;border:none;padding:3px 6px;text-align:right;border-radius:3px;color:#333;">'
    +           '</td>'
    +           '<td style="padding:4px 8px;" id="weight-warn-' + gIdx + '"></td>'
    +         '</tr>'
    +       '</tfoot>'
    +     '</table>'

    +   '</div>'
    + '</div>';
}

function addTask(gIdx) {
    const tbody = document.getElementById('tasks-' + gIdx);
    const tIdx = taskIndexes[gIdx];
    const numEl = document.querySelector('[name="goals[' + gIdx + '][goal_number]"]');
    const goalNum = numEl ? numEl.value : '?';
    const taskNum = goalNum + '.' + (tIdx + 1);
    const tr = document.createElement('tr');
    tr.id = 'task-' + gIdx + '-' + tIdx;
    tr.innerHTML = taskTemplate(gIdx, tIdx, taskNum);
    tbody.appendChild(tr);
    tr.querySelector('.target-sel[data-gidx="' + gIdx + '"]').innerHTML = buildTargetOptsHtml(gIdx, '');
    taskIndexes[gIdx]++;
    updateWeightSummary(gIdx);
}

function removeTask(gIdx, tIdx) {
    const el = document.getElementById('task-' + gIdx + '-' + tIdx);
    if (el) el.remove();
    updateWeightSummary(gIdx);
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
    +           ' data-gidx="' + gIdx + '" data-tidx="' + tIdx + '" style="color:#333;">'
    +   '</select>'
    +   '<input type="text" name="goals[' + gIdx + '][tasks][' + tIdx + '][target_custom]"'
    +          ' id="tc-' + gIdx + '-' + tIdx + '"'
    +          ' class="form-control form-control-sm mt-1 d-none"'
    +          ' placeholder="Custom target" style="color:#333;">'
    + '</td>';
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('target-sel')) {
        const gIdx = e.target.dataset.gidx;
        const tIdx = e.target.dataset.tidx;
        const custom = document.getElementById('tc-' + gIdx + '-' + tIdx);
        if (custom) custom.classList.toggle('d-none', e.target.value !== 'Custom');
    }
});

addGoal();
</script>
@stop
