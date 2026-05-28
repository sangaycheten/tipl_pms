@extends('master')
@section('page-title', 'Import Goals')
@section('page-header', 'Import Goals')

@section('pagestyles')
<style>
    #goalsAccordion .collapse label,
    #goalsAccordion .collapse .small,
    #goalsAccordion .collapse span:not(.badge):not(.goal-warn) { color: #333 !important; }
    #goalsAccordion .collapse input.form-control,
    #goalsAccordion .collapse select.custom-select { color: #333 !important; }
    .import-card { background:#fff; border-radius:6px; box-shadow:0 1px 4px rgba(0,0,0,0.1); padding:28px 32px; max-width:600px; margin:0 auto; }
    .template-steps { background:#f5f7fa; border-radius:6px; padding:16px 20px; margin-bottom:20px; font-size:0.88rem; color:#444; }
    .template-steps ol { margin:0; padding-left:18px; }
    .template-steps li { margin-bottom:4px; }
</style>
@endsection

@section('content')
<div class="row">
<div class="col-sm-12">
<div class="col-sm-12 card" style="padding:16px 18px;">

    @if(Session::has('successmessage'))
        <div class="alert alert-success mb-3">{{ Session::get('successmessage') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if(!$goalsJson)
    {{-- ═══ Phase 1: Upload form ═══ --}}
    <div class="import-card">
        <h5 class="mb-3" style="color:#226b86;"><i class="fa fa-upload mr-2"></i>Import Goals from Excel</h5>

        @if(!empty($supervisorCycles))
            <div class="alert alert-warning" style="font-size:0.87rem;">
                <i class="fa fa-exclamation-triangle mr-1"></i>
                <strong>Note:</strong> Your supervisor has already set goals for
                @foreach($supervisorCycles as $c)
                    <strong>H{{ $c['half'] }} {{ $c['year'] }}</strong>@if(!$loop->last), @endif
                @endforeach.
                You cannot import goals for those cycles. Importing will only be accepted for cycles your supervisor has not set.
            </div>
        @endif

        <div class="template-steps">
            <strong>Steps:</strong>
            <ol>
                <li>Download the template file below.</li>
                <li>Fill in your goals and tasks following the sample rows.</li>
                <li>Upload the completed file and review before saving.</li>
            </ol>
            <div class="mt-2" style="font-size:0.82rem;color:#666;">
                <strong>Template columns:</strong>
                Goal No &bull; Goal Description &bull; Total Score &bull; Year &bull; H1 (Y/N) &bull; H2 (Y/N)
                &bull; Task Description &bull; Task Weightage &bull; Task Target
            </div>
        </div>

        <a href="{{ route('goals.import') }}?download=1&employeeId={{ $employeeId }}"
           class="btn btn-secondary btn-sm mb-4">
            <i class="fa fa-download mr-1"></i> Download Template
        </a>

        <form method="POST" action="{{ route('goals.import.post') }}" enctype="multipart/form-data">
            @csrf
            @if($employeeId)
                <input type="hidden" name="employee_id" value="{{ $employeeId }}">
            @endif
            <div class="form-group">
                <label style="font-size:0.9rem;font-weight:600;">PMS Year</label>
                <select name="import_year" class="custom-select custom-select-sm" style="max-width:120px;" required>
                    @php $curYear = (int)date('Y'); @endphp
                    @for($y = $curYear + 1; $y >= $curYear - 2; $y--)
                        <option value="{{ $y }}" {{ $y === $curYear ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <small class="form-text text-muted">Only goals matching this year will be imported.</small>
            </div>
            <div class="form-group">
                <label style="font-size:0.9rem;font-weight:600;">Upload completed template (.xlsx / .xls)</label>
                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="javascript:history.back()" class="btn btn-secondary btn-sm">Cancel</a>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa fa-upload mr-1"></i> Upload &amp; Preview
                </button>
            </div>
        </form>
    </div>

    @else
    {{-- ═══ Phase 2: Review accordion ═══ --}}
    <form method="POST" action="{{ route('goals.store') }}" id="goalForm">
        @csrf
        <input type="hidden" name="save_action" id="saveAction" value="draft">
        @if($employeeId)
            <input type="hidden" name="employee_id" value="{{ $employeeId }}">
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-warning" style="font-size:0.9rem;">
                Review the imported goals below, make any edits needed, then save.
            </span>
            <div>
                <a href="{{ route('goals.import', ['employeeId' => $employeeId]) }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left mr-1"></i> Re-upload
                </a>
                <button type="button" class="btn btn-primary btn-sm ml-1" onclick="addGoal()">
                    <i class="fa fa-plus"></i> Add Goal
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

        @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <div id="goalsAccordion"></div>
    </form>
    @endif

</div>
</div>
</div>
@stop

@if($goalsJson)
@section('pagescripts')
<script>
const H1_TARGETS = ['Q1','Q1M1','Q1M2','Q1M3','Q2','Q2M1','Q2M2','Q2M3'];
const H2_TARGETS = ['Q3','Q3M1','Q3M2','Q3M3','Q4','Q4M1','Q4M2','Q4M3'];
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
        getTargetOpts(gIdx).map(function(o) {
            return '<option value="' + o + '"' + (selected === o ? ' selected' : '') + '>' + o + '</option>';
        }).join('');
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
    const badge = 'background:#fff;color:#226b86;padding:2px 6px;border-radius:3px;font-size:11px;font-weight:bold;margin-left:6px;';
    el.innerHTML = 'Goal ' + num + (desc ? ' &mdash; ' + desc : '')
        + ' <span style="' + badge + '">Score: ' + (sc || 0) + '</span>'
        + ' <span style="' + badge + '">' + hy + '</span>';
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
                                  : goalTotal > 0 && roundedSum === goalTotal ? '#99ffbb' : '#b0c8d4';
    }
    if (warnEl) {
        if (!goalTotal) { warnEl.innerHTML = ''; }
        else if (roundedSum > goalTotal) { warnEl.innerHTML = '<span style="color:#fff;font-weight:bold;">&#9888; Over by ' + (roundedSum - goalTotal).toFixed(2) + '</span>'; }
        else if (roundedSum === goalTotal) { warnEl.innerHTML = '<span style="color:#fff;font-weight:bold;">&#10003; Matched</span>'; }
        else { warnEl.innerHTML = '<span style="color:#fff;">Remaining: ' + (goalTotal - roundedSum).toFixed(2) + '</span>'; }
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
    +                    ' id="inH1-' + gIdx + '" onchange="refreshGoalTargetSelects(' + gIdx + '); updateGoalSummary(' + gIdx + ')">'
    +             '<label class="form-check-label small" for="inH1-' + gIdx + '">H1 (Q1+Q2)</label>'
    +           '</div>'
    +           '<div class="form-check">'
    +             '<input class="form-check-input" type="checkbox" name="goals[' + gIdx + '][in_h2]" value="1"'
    +                    ' id="inH2-' + gIdx + '" onchange="refreshGoalTargetSelects(' + gIdx + '); updateGoalSummary(' + gIdx + ')">'
    +             '<label class="form-check-label small" for="inH2-' + gIdx + '">H2 (Q3+Q4)</label>'
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
    +   '<button type="button" onclick="removeTask(' + gIdx + ',' + tIdx + ')"'
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
    const tbody   = document.getElementById('tasks-' + gIdx);
    const tIdx    = taskIndexes[gIdx];
    const numEl   = document.querySelector('[name="goals[' + gIdx + '][goal_number]"]');
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
});

// ── Pre-populate from parsed import data ────────────────────────────────────
const importedGoals = @json($goalsArray);

importedGoals.forEach(function(g) {
    const gIdx = goalIndex;
    const div  = document.createElement('div');
    div.className = 'mb-2';
    div.id = 'goal-item-' + gIdx;
    div.innerHTML = goalTemplate(gIdx, g.goal_number);
    document.getElementById('goalsAccordion').appendChild(div);
    $('#gc-' + gIdx).collapse({ toggle: false });
    taskIndexes[gIdx] = 0;

    document.querySelector('[name="goals[' + gIdx + '][goal_number]"]').value = g.goal_number;
    document.querySelector('[name="goals[' + gIdx + '][description]"]').value  = g.description;
    document.querySelector('[name="goals[' + gIdx + '][total_score]"]').value  = g.total_score;
    document.querySelector('[name="goals[' + gIdx + '][year]"]').value         = g.year;

    if (g.in_h1) document.getElementById('inH1-' + gIdx).checked = true;
    if (g.in_h2) document.getElementById('inH2-' + gIdx).checked = true;
    refreshGoalTargetSelects(gIdx);

    g.tasks.forEach(function(task) {
        const tIdx    = taskIndexes[gIdx];
        const numEl   = document.querySelector('[name="goals[' + gIdx + '][goal_number]"]');
        const goalNum = numEl ? numEl.value : g.goal_number;
        const taskNum = goalNum + '.' + (tIdx + 1);
        const tr = document.createElement('tr');
        tr.id = 'task-' + gIdx + '-' + tIdx;
        tr.innerHTML = taskTemplate(gIdx, tIdx, taskNum);
        document.getElementById('tasks-' + gIdx).appendChild(tr);

        tr.querySelector('[name="goals[' + gIdx + '][tasks][' + tIdx + '][description]"]').value = task.description;
        tr.querySelector('[name="goals[' + gIdx + '][tasks][' + tIdx + '][weightage]"]').value   = task.weightage;

        const sel = tr.querySelector('.target-sel');
        if (ALL_TARGETS.includes(task.target)) {
            sel.innerHTML = buildTargetOptsHtml(gIdx, task.target);
        } else if (task.target && task.target !== '-') {
            sel.innerHTML = buildTargetOptsHtml(gIdx, 'Custom');
            const ci = document.getElementById('tc-' + gIdx + '-' + tIdx);
            if (ci) { ci.classList.remove('d-none'); ci.value = task.target; }
        } else {
            sel.innerHTML = buildTargetOptsHtml(gIdx, '');
        }

        taskIndexes[gIdx]++;
    });

    updateGoalSummary(gIdx);
    updateWeightSummary(gIdx);
    goalIndex++;
    nextGoalNum = Math.max(nextGoalNum, g.goal_number + 1);
});
</script>
@endsection
@endif
