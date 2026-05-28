<script>
// ═══════════════════════════════════════════════════════════════════
//  Employee Picker
//  - departments: flat list from PHP
//  - On dept checkbox click: fetch sections + employees via AJAX
//  - Multi-dept, multi-section, multi-employee selection
//  - Selected employees render as pills + hidden inputs
// ═══════════════════════════════════════════════════════════════════

const ALL_DEPARTMENTS = {!! json_encode($departments->map(fn($d) => ['id' => $d->Id, 'name' => $d->Name])->values()) !!};

// Pre-selected employees from server (edit mode)
const PRE_SELECTED_EMPS = {!! json_encode(
    collect($preSelectedEmps)->map(fn($e) => [
        'id'          => $e->Id,
        'name'        => $e->Name,
        'emp_id'      => $e->EmpId,
        'dept_id'     => $e->DepartmentId,
        'dept_name'   => $e->DeptName,
        'section_id'  => $e->SectionId,
        'section_name'=> $e->SectionName,
    ])->values()
) !!};

// Internal state
const selectedEmps = {};   // { empId: { id, name, emp_id } }
const loadedDepts  = {};   // { deptId: [ { dept_id, dept_name, sections:[...], no_section_employees:[...] } ] }

const AJAX_URL = '{{ route("commongoal.employees_by_dept") }}';

// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    renderDeptTree(ALL_DEPARTMENTS, []);

    // Pre-select employees in edit mode
    if (PRE_SELECTED_EMPS.length > 0) {
        const deptIds = [...new Set(PRE_SELECTED_EMPS.map(e => e.dept_id))];

        fetch(AJAX_URL + '?dept_ids=' + deptIds.join(','))
            .then(r => r.json())
            .then(function(data) {
                data.forEach(d => { loadedDepts[d.dept_id] = d; });
                // Re-render the tree for pre-selected depts
                rerenderDepts(deptIds);
                // Tick the pre-selected employees
                PRE_SELECTED_EMPS.forEach(function(e) {
                    selectedEmps[e.id] = { id: e.id, name: e.name, emp_id: e.emp_id };
                });
                refreshPills();
                // Tick checkboxes
                PRE_SELECTED_EMPS.forEach(function(e) {
                    const cb = document.getElementById('emp-cb-' + e.id);
                    if (cb) { cb.checked = true; }
                });
                // Sync section / dept header states
                deptIds.forEach(dId => {
                    syncSectionHeaders(dId);
                    syncDeptHeader(dId);
                });
            });
    }
});

// ── Render the department list (collapsed by default) ─────────────────────────
function renderDeptTree(depts, openDeptIds) {
    const tree = document.getElementById('deptTree');
    if (!depts || depts.length === 0) {
        tree.innerHTML = '<div class="picker-loading">No departments found.</div>';
        return;
    }
    let html = '';
    depts.forEach(function(d) {
        const isOpen = openDeptIds.includes(d.id);
        html += `
        <div class="picker-dept-item" data-dept-id="${d.id}">
            <div class="picker-dept-bar${isOpen ? ' open' : ''}" onclick="toggleDept(${d.id}, this)">
                <input type="checkbox" id="dept-cb-${d.id}"
                       onclick="event.stopPropagation(); onDeptCheck(${d.id}, this.checked)"
                       data-dept-id="${d.id}">
                <span class="dept-name">${d.name}</span>
                <span class="dept-count" id="dept-count-${d.id}">0</span>
                <i class="fa fa-chevron-down chevron"></i>
            </div>
            <div class="picker-dept-body${isOpen ? ' open' : ''}" id="dept-body-${d.id}">
                <div class="picker-loading" id="dept-loading-${d.id}">
                    <i class="fa fa-spinner fa-spin"></i> Loading…
                </div>
            </div>
        </div>`;
    });
    tree.innerHTML = html;
}

// Re-render dept bodies for given dept IDs after AJAX loads
function rerenderDepts(deptIds) {
    deptIds.forEach(function(dId) {
        const data = loadedDepts[dId];
        if (!data) return;
        const body = document.getElementById('dept-body-' + dId);
        const bar  = body ? body.previousElementSibling : null;
        if (body) {
            body.innerHTML = buildDeptBodyHtml(data);
            body.classList.add('open');
        }
        if (bar) bar.classList.add('open');
    });
}

function buildDeptBodyHtml(data) {
    let html = '';

    // Sections
    data.sections.forEach(function(sec) {
        html += `
        <div class="picker-section-item" data-sec-id="${sec.sec_id}" data-dept-id="${data.dept_id}">
            <div class="picker-section-bar" onclick="toggleSection(${sec.sec_id}, this)">
                <input type="checkbox" id="sec-cb-${sec.sec_id}"
                       onclick="event.stopPropagation(); onSectionCheck(${sec.sec_id}, ${data.dept_id}, this.checked)"
                       data-sec-id="${sec.sec_id}" data-dept-id="${data.dept_id}">
                <span class="sec-name">${sec.sec_name}</span>
                <i class="fa fa-chevron-down sec-chevron"></i>
            </div>
            <div class="picker-section-body" id="sec-body-${sec.sec_id}">`;

        sec.employees.forEach(function(emp) {
            html += empRowHtml(emp, data.dept_id, sec.sec_id);
        });
        if (sec.employees.length === 0) {
            html += `<div class="picker-emp-row" style="color:#aaa;font-size:0.82rem;">No employees</div>`;
        }
        html += `</div></div>`;
    });

    // Employees without section
    if (data.no_section_employees && data.no_section_employees.length > 0) {
        html += `<div class="picker-no-section">`;
        data.no_section_employees.forEach(function(emp) {
            html += empRowHtml(emp, data.dept_id, null);
        });
        html += `</div>`;
    }

    if (data.sections.length === 0 && (!data.no_section_employees || data.no_section_employees.length === 0)) {
        html += `<div class="picker-loading" style="color:#aaa;">No employees found.</div>`;
    }

    return html;
}

function empRowHtml(emp, deptId, secId) {
    const secAttr = secId ? `data-sec-id="${secId}"` : '';
    return `
    <div class="picker-emp-row" data-emp-id="${emp.id}" data-dept-id="${deptId}" ${secAttr}>
        <input type="checkbox" id="emp-cb-${emp.id}"
               onclick="onEmpCheck(${emp.id}, '${escHtml(emp.name)}', '${escHtml(emp.emp_id || '')}', this.checked)"
               data-emp-id="${emp.id}" data-dept-id="${deptId}" ${secAttr}>
        <label for="emp-cb-${emp.id}">${escHtml(emp.name)}</label>
        <span class="emp-id">${escHtml(emp.emp_id || '')}</span>
    </div>`;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ── Toggle dept open/close, lazy-load on first open ──────────────────────────
function toggleDept(deptId, bar) {
    const body = document.getElementById('dept-body-' + deptId);
    if (!body) return;
    const isOpen = body.classList.toggle('open');
    bar.classList.toggle('open', isOpen);

    if (isOpen && !loadedDepts[deptId]) {
        loadDept(deptId);
    }
}

function loadDept(deptId) {
    const loadingEl = document.getElementById('dept-loading-' + deptId);
    fetch(AJAX_URL + '?dept_ids=' + deptId)
        .then(r => r.json())
        .then(function(data) {
            if (data && data[0]) {
                loadedDepts[deptId] = data[0];
                const body = document.getElementById('dept-body-' + deptId);
                if (body) body.innerHTML = buildDeptBodyHtml(data[0]);
                // Re-tick any already-selected employees in this dept
                Object.keys(selectedEmps).forEach(function(eId) {
                    const cb = document.getElementById('emp-cb-' + eId);
                    if (cb) cb.checked = true;
                });
                syncSectionHeaders(deptId);
                syncDeptHeader(deptId);
            }
        })
        .catch(function() {
            if (loadingEl) loadingEl.textContent = 'Failed to load.';
        });
}

// ── Department checkbox (select / deselect all employees in dept) ─────────────
function onDeptCheck(deptId, checked) {
    if (!loadedDepts[deptId]) {
        // Load first, then select all
        loadDept(deptId);
        // Open the body
        const body = document.getElementById('dept-body-' + deptId);
        const bar  = body ? body.previousElementSibling : null;
        if (body) { body.classList.add('open'); }
        if (bar)  { bar.classList.add('open'); }
        // Wait for AJAX then tick
        const interval = setInterval(function() {
            if (loadedDepts[deptId]) {
                clearInterval(interval);
                selectAllInDept(deptId, checked);
            }
        }, 100);
        return;
    }
    selectAllInDept(deptId, checked);
}

function selectAllInDept(deptId, checked) {
    const data = loadedDepts[deptId];
    if (!data) return;

    const allEmps = getAllEmpsInDept(data);
    allEmps.forEach(function(emp) {
        const cb = document.getElementById('emp-cb-' + emp.id);
        if (cb) cb.checked = checked;
        if (checked) {
            selectedEmps[emp.id] = { id: emp.id, name: emp.name, emp_id: emp.emp_id };
        } else {
            delete selectedEmps[emp.id];
        }
    });

    // Sync section checkboxes
    (data.sections || []).forEach(function(sec) {
        const sCb = document.getElementById('sec-cb-' + sec.sec_id);
        if (sCb) sCb.checked = checked;
    });

    syncDeptCounter(deptId);
    refreshPills();
}

function getAllEmpsInDept(data) {
    let emps = [];
    (data.sections || []).forEach(s => { emps = emps.concat(s.employees || []); });
    emps = emps.concat(data.no_section_employees || []);
    return emps;
}

// ── Section checkbox ─────────────────────────────────────────────────────────
function onSectionCheck(secId, deptId, checked) {
    const data = loadedDepts[deptId];
    if (!data) return;
    const sec = data.sections.find(s => s.sec_id == secId);
    if (!sec) return;

    (sec.employees || []).forEach(function(emp) {
        const cb = document.getElementById('emp-cb-' + emp.id);
        if (cb) cb.checked = checked;
        if (checked) {
            selectedEmps[emp.id] = { id: emp.id, name: emp.name, emp_id: emp.emp_id };
        } else {
            delete selectedEmps[emp.id];
        }
    });

    syncDeptHeader(deptId);
    syncDeptCounter(deptId);
    refreshPills();
}

// ── Employee checkbox ────────────────────────────────────────────────────────
function onEmpCheck(empId, name, empIdStr, checked) {
    if (checked) {
        selectedEmps[empId] = { id: empId, name: name, emp_id: empIdStr };
    } else {
        delete selectedEmps[empId];
    }
    // Figure out dept / section from checkbox
    const cb = document.getElementById('emp-cb-' + empId);
    if (cb) {
        const deptId = cb.dataset.deptId;
        const secId  = cb.dataset.secId;
        if (secId) syncSectionHeader(secId, deptId);
        if (deptId) syncDeptHeader(deptId);
        if (deptId) syncDeptCounter(deptId);
    }
    refreshPills();
}

// ── Toggle section collapse ───────────────────────────────────────────────────
function toggleSection(secId, bar) {
    const body = document.getElementById('sec-body-' + secId);
    if (!body) return;
    const open = body.classList.toggle('open');
    bar.classList.toggle('open', open);
}

// ── Sync header checkbox states ───────────────────────────────────────────────
function syncSectionHeader(secId, deptId) {
    const data = loadedDepts[deptId];
    if (!data) return;
    const sec = data.sections.find(s => s.sec_id == secId);
    if (!sec) return;
    const empIds   = (sec.employees || []).map(e => e.id);
    const allCheck = empIds.length > 0 && empIds.every(id => !!selectedEmps[id]);
    const secCb    = document.getElementById('sec-cb-' + secId);
    if (secCb) secCb.checked = allCheck;
}

function syncSectionHeaders(deptId) {
    const data = loadedDepts[deptId];
    if (!data) return;
    (data.sections || []).forEach(sec => syncSectionHeader(sec.sec_id, deptId));
}

function syncDeptHeader(deptId) {
    const data = loadedDepts[deptId];
    if (!data) return;
    const allEmps = getAllEmpsInDept(data);
    const allCheck = allEmps.length > 0 && allEmps.every(e => !!selectedEmps[e.id]);
    const dCb = document.getElementById('dept-cb-' + deptId);
    if (dCb) dCb.checked = allCheck;
}

function syncDeptCounter(deptId) {
    const data = loadedDepts[deptId];
    if (!data) return;
    const allEmps = getAllEmpsInDept(data);
    const selCount = allEmps.filter(e => !!selectedEmps[e.id]).length;
    const countEl = document.getElementById('dept-count-' + deptId);
    if (countEl) countEl.textContent = selCount;
}

// ── Pills + hidden inputs ─────────────────────────────────────────────────────
function refreshPills() {
    const container  = document.getElementById('selectedPills');
    const placeholder = document.getElementById('pillsPlaceholder');
    const countEl    = document.getElementById('selectedCount');
    const ids        = Object.keys(selectedEmps);

    // Remove old pills and hidden inputs
    container.querySelectorAll('.pill').forEach(p => p.remove());
    document.querySelectorAll('.emp-hidden-input').forEach(i => i.remove());

    if (ids.length === 0) {
        if (placeholder) { placeholder.style.display = ''; }
        if (countEl) countEl.textContent = '0';
        return;
    }
    if (placeholder) placeholder.style.display = 'none';
    if (countEl) countEl.textContent = ids.length;

    const form = document.getElementById('goalForm');
    ids.forEach(function(eId) {
        const emp = selectedEmps[eId];

        // Pill
        const pill = document.createElement('span');
        pill.className = 'pill';
        pill.innerHTML = escHtml(emp.name)
            + (emp.emp_id ? ' <small style="opacity:.7;">(' + escHtml(emp.emp_id) + ')</small>' : '')
            + '<button type="button" class="pill-x" onclick="removePill(' + emp.id + ')">×</button>';
        container.appendChild(pill);

        // Hidden input
        const inp = document.createElement('input');
        inp.type      = 'hidden';
        inp.name      = 'assigned_employees[]';
        inp.value     = emp.id;
        inp.className = 'emp-hidden-input';
        form.appendChild(inp);
    });
}

function removePill(empId) {
    delete selectedEmps[empId];
    const cb = document.getElementById('emp-cb-' + empId);
    if (cb) {
        cb.checked = false;
        const deptId = cb.dataset.deptId;
        const secId  = cb.dataset.secId;
        if (secId) syncSectionHeader(secId, deptId);
        if (deptId) { syncDeptHeader(deptId); syncDeptCounter(deptId); }
    }
    refreshPills();
}

// ── Select all / clear all ────────────────────────────────────────────────────
function selectAllVisible() {
    document.querySelectorAll('.picker-emp-row input[type=checkbox]:not(:disabled)').forEach(function(cb) {
        if (!cb.checked) {
            cb.checked = true;
            const empId  = parseInt(cb.dataset.empId);
            const label  = cb.closest('.picker-emp-row').querySelector('label');
            const empIdSpan = cb.closest('.picker-emp-row').querySelector('.emp-id');
            selectedEmps[empId] = {
                id: empId,
                name: label ? label.textContent.trim() : '',
                emp_id: empIdSpan ? empIdSpan.textContent.trim() : '',
            };
        }
    });
    // Sync all dept/section headers
    Object.keys(loadedDepts).forEach(function(dId) {
        syncSectionHeaders(dId);
        syncDeptHeader(dId);
        syncDeptCounter(dId);
    });
    refreshPills();
}

function clearAll() {
    Object.keys(selectedEmps).forEach(k => delete selectedEmps[k]);
    document.querySelectorAll('.picker-emp-row input[type=checkbox]').forEach(cb => cb.checked = false);
    document.querySelectorAll('[id^="dept-cb-"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('[id^="sec-cb-"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('[id^="dept-count-"]').forEach(el => el.textContent = '0');
    refreshPills();
}

// ── Search / filter ───────────────────────────────────────────────────────────
function filterPicker(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.picker-dept-item').forEach(function(deptEl) {
        const deptName = deptEl.querySelector('.dept-name').textContent.toLowerCase();
        let deptMatch = !q || deptName.includes(q);

        let anyVisible = false;
        deptEl.querySelectorAll('.picker-emp-row').forEach(function(row) {
            const label = row.querySelector('label');
            const empName = label ? label.textContent.toLowerCase() : '';
            const show = !q || deptMatch || empName.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) anyVisible = true;
        });

        // Show/hide sections if all employees are hidden
        deptEl.querySelectorAll('.picker-section-item').forEach(function(secEl) {
            const hasVisible = Array.from(secEl.querySelectorAll('.picker-emp-row'))
                                    .some(r => r.style.display !== 'none');
            secEl.style.display = (!q || hasVisible || deptMatch) ? '' : 'none';
        });

        deptEl.style.display = (deptMatch || anyVisible) ? '' : 'none';

        // Auto-expand if searching
        if (q && (deptMatch || anyVisible)) {
            const body = deptEl.querySelector('.picker-dept-body');
            const bar  = deptEl.querySelector('.picker-dept-bar');
            if (body && !body.classList.contains('open')) {
                body.classList.add('open');
                if (bar) bar.classList.add('open');
                if (!loadedDepts[deptEl.dataset.deptId]) {
                    loadDept(parseInt(deptEl.dataset.deptId));
                }
            }
        }
    });
}
</script>
