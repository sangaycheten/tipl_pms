<script>
const H1_TARGETS = ['Q1','Q1M1','Q1M2','Q1M3','Q2','Q2M1','Q2M2','Q2M3'];
const H2_TARGETS = ['Q3','Q3M1','Q3M2','Q3M3','Q4','Q4M1','Q4M2','Q4M3'];
const ALL_TARGETS = H1_TARGETS.concat(H2_TARGETS);

let goalIndex   = 0;
let nextGoalNum = {{ (int)$nextGoalNumber }};
let taskIndexes = {};

// ── Target option helpers ─────────────────────────────────────────────────────
function getTargetOpts(gIdx) {
    const h1 = document.getElementById('inH1-' + gIdx);
    const h2 = document.getElementById('inH2-' + gIdx);
    let opts = [];
    if (h1 && h1.checked) opts = opts.concat(H1_TARGETS);
    if (h2 && h2.checked) opts = opts.concat(H2_TARGETS);
    opts.push('Custom');
    return opts;
}
function buildTargetOptsHtml(gIdx, selected) {
    return '<option value="">- None -</option>' +
        getTargetOpts(gIdx)
            .map(o => '<option value="' + o + '"' + (selected === o ? ' selected' : '') + '>' + o + '</option>')
            .join('');
}
function refreshGoalTargetSelects(gIdx) {
    document.querySelectorAll('.target-sel[data-gidx="' + gIdx + '"]').forEach(sel => {
        const cur = sel.value;
        sel.innerHTML = buildTargetOptsHtml(gIdx, cur);
    });
}

// ── Goal summary bar ──────────────────────────────────────────────────────────
function updateGoalSummary(gIdx) {
    const numEl  = document.querySelector('[name="goals[' + gIdx + '][goal_number]"]');
    const descEl = document.querySelector('[name="goals[' + gIdx + '][description]"]');
    const h1El   = document.getElementById('inH1-' + gIdx);
    const h2El   = document.getElementById('inH2-' + gIdx);
    const num  = numEl  ? numEl.value  : '?';
    const desc = descEl ? descEl.value : '';
    const h1   = h1El   ? h1El.checked : false;
    const h2   = h2El   ? h2El.checked : false;
    const hy   = h1 && h2 ? 'H1 & H2' : h1 ? 'H1' : h2 ? 'H2' : '-';
    const el   = document.getElementById('g-summary-' + gIdx);
    if (!el) return;
    const bs = 'background:#fff;color:#226b86;padding:2px 6px;border-radius:3px;font-size:11px;font-weight:bold;margin-left:6px;';
    el.innerHTML = 'Goal ' + num
        + (desc ? ' &mdash; ' + desc : '')
        + ' <span style="' + bs + '">' + hy + '</span>';
}

// ── Toggle accordion ──────────────────────────────────────────────────────────
function toggleGoal(gIdx) {
    const el = document.getElementById('gc-' + gIdx);
    if (!el) return;
    const open = el.classList.toggle('show');
    const ch = document.getElementById('chevron-' + gIdx);
    if (ch) ch.className = open ? 'fa fa-chevron-up' : 'fa fa-chevron-down';
}

// ── Goal HTML template ────────────────────────────────────────────────────────
function goalTemplate(gIdx, goalNum) {
    const year = {{ (int)$selectedYear }};
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
    +       '<div class="col-md-7"><label class="small mb-1">Description</label>'
    +         '<input type="text" name="goals[' + gIdx + '][description]" class="form-control form-control-sm" required'
    +                ' oninput="updateGoalSummary(' + gIdx + ')">'
    +       '</div>'
    +       '<div class="col-md-1"><label class="small mb-1">Year</label>'
    +         '<input type="number" name="goals[' + gIdx + '][year]" class="form-control form-control-sm"'
    +                ' value="' + year + '" readonly>'
    +       '</div>'
    +       '<div class="col-md-3"><label class="small mb-1">Period</label>'
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

    +     '<div class="alert alert-info mb-2" style="font-size:0.8rem;padding:6px 12px;margin:0 0 8px;">'
    +       '<i class="fa fa-info-circle mr-1"></i>'
    +       'Goal score and task scores will be assigned by the respective section supervisor.'
    +     '</div>'

    +     '<table style="width:100%;border-collapse:collapse;margin-top:8px;">'
    +       '<thead><tr style="background:#226b86;color:#fff;">'
    +         '<th style="width:80px;padding:6px 8px;text-align:center;">No.</th>'
    +         '<th style="padding:6px 8px;">Task Description</th>'
    +         '<th style="width:200px;padding:6px 8px;">Target</th>'
    +       '</tr></thead>'
    +       '<tbody id="tasks-' + gIdx + '"></tbody>'
    +       '<tfoot><tr style="background:#226b86;color:#fff;">'
    +         '<td colspan="3" style="padding:4px 8px;">'
    +           '<button type="button" onclick="addTask(' + gIdx + ')"'
    +                   ' style="height:28px;padding:0 12px;border:1px solid #fff;background:transparent;color:#fff;cursor:pointer;font-size:13px;border-radius:3px;">'
    +             '<i class="fa fa-plus"></i> Add Task'
    +           '</button>'
    +         '</td>'
    +       '</tr></tfoot>'
    +     '</table>'
    +   '</div>'
    + '</div>';
}

// ── Task HTML template ────────────────────────────────────────────────────────
function taskTemplate(gIdx, tIdx, taskNum) {
    return ''
    + '<td style="padding:4px 6px;text-align:center;width:80px;">'
    +   '<input type="hidden" name="goals[' + gIdx + '][tasks][' + tIdx + '][task_number]" value="' + taskNum + '">'
    +   '<small style="display:block;color:#666;margin-bottom:2px;">' + taskNum + '</small>'
    +   '<button type="button" onclick="removeTask(' + gIdx + ', ' + tIdx + ')"'
    +           ' style="width:28px;height:28px;border:1px solid #dc3545;background:#dc3545;color:#fff;cursor:pointer;font-size:16px;line-height:1;border-radius:3px;">&#8722;</button>'
    + '</td>'
    + '<td style="padding:4px 6px;">'
    +   '<input type="text" name="goals[' + gIdx + '][tasks][' + tIdx + '][description]"'
    +          ' class="form-control form-control-sm" required style="color:#333;">'
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

// ── Add / Remove helpers ──────────────────────────────────────────────────────
function addGoal() {
    const gIdx = goalIndex;
    const div  = document.createElement('div');
    div.className = 'mb-2';
    div.id = 'goal-item-' + gIdx;
    div.innerHTML = goalTemplate(gIdx, nextGoalNum);
    document.getElementById('goalsAccordion').appendChild(div);
    if (typeof $ !== 'undefined') $('#gc-' + gIdx).collapse({ toggle: false });
    taskIndexes[gIdx] = 0;
    addTask(gIdx);
    goalIndex++;
    nextGoalNum++;
}

function removeGoal(gIdx) {
    const el = document.getElementById('goal-item-' + gIdx);
    if (el) el.remove();
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
    tr.querySelector('.target-sel[data-gidx="' + gIdx + '"]').innerHTML = buildTargetOptsHtml(gIdx, '');
    taskIndexes[gIdx]++;
}

function removeTask(gIdx, tIdx) {
    const el = document.getElementById('task-' + gIdx + '-' + tIdx);
    if (el) el.remove();
}

// ── Custom target toggle ──────────────────────────────────────────────────────
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('target-sel')) {
        const gIdx   = e.target.dataset.gidx;
        const tIdx   = e.target.dataset.tidx;
        const custom = document.getElementById('tc-' + gIdx + '-' + tIdx);
        if (custom) custom.classList.toggle('d-none', e.target.value !== 'Custom');
    }
});

// ── Pre-populate existing goals (for edit mode) ───────────────────────────────
const existingGoals = {!! json_encode($existingGoals) !!};

existingGoals.forEach(function(g) {
    const gIdx = goalIndex;
    const div  = document.createElement('div');
    div.className = 'mb-2';
    div.id = 'goal-item-' + gIdx;
    div.innerHTML = goalTemplate(gIdx, g.goal_number);
    document.getElementById('goalsAccordion').appendChild(div);
    if (typeof $ !== 'undefined') $('#gc-' + gIdx).collapse({ toggle: false });
    taskIndexes[gIdx] = 0;

    document.querySelector('[name="goals[' + gIdx + '][goal_number]"]').value = g.goal_number;
    document.querySelector('[name="goals[' + gIdx + '][description]"]').value = g.description;
    document.querySelector('[name="goals[' + gIdx + '][year]"]').value        = g.year;

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
    goalIndex++;
    nextGoalNum = Math.max(nextGoalNum, g.goal_number + 1);
});

// Auto-open blank form for create mode
if (existingGoals.length === 0) {
    addGoal();
}
</script>
