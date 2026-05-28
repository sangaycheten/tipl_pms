@extends('master')
@section('page-title', 'Edit Common Goals')
@section('page-header', 'Edit Common Goals')

@section('pagestyles')
<style>
    #goalsAccordion .collapse label,
    #goalsAccordion .collapse .small,
    #goalsAccordion .collapse span:not(.badge):not(.goal-warn) { color:#333!important; }
    #goalsAccordion .collapse input.form-control,
    #goalsAccordion .collapse select.custom-select { color:#333!important; }

    .picker-wrap { border:1px solid #d0dce4; border-radius:6px; overflow:hidden; }
    .picker-dept-bar {
        display:flex; align-items:center; gap:10px;
        padding:9px 14px; background:#f0f5f8; border-bottom:1px solid #d0dce4;
        cursor:pointer; user-select:none;
    }
    .picker-dept-bar:hover { background:#e4edf4; }
    .picker-dept-bar input[type=checkbox] { width:16px; height:16px; cursor:pointer; flex-shrink:0; }
    .picker-dept-bar .dept-name { font-weight:600; color:#226b86; flex:1; font-size:0.9rem; }
    .picker-dept-bar .dept-count { font-size:0.75rem; color:#fff; background:#226b86;
        border-radius:30px; padding:2px 8px; min-width:28px; text-align:center; }
    .picker-dept-bar .chevron { color:#226b86; font-size:0.8rem; transition:transform .2s; }
    .picker-dept-bar.open .chevron { transform:rotate(180deg); }

    .picker-dept-body { padding:0 0 6px 0; display:none; background:#fff; }
    .picker-dept-body.open { display:block; }

    .picker-section-bar {
        display:flex; align-items:center; gap:8px;
        padding:7px 14px 7px 28px; background:#f8fbfd;
        border-top:1px solid #eaf0f5; cursor:pointer;
    }
    .picker-section-bar:hover { background:#eaf0f5; }
    .picker-section-bar input[type=checkbox] { width:15px; height:15px; cursor:pointer; flex-shrink:0; }
    .picker-section-bar .sec-name { font-size:0.85rem; color:#444; flex:1; }
    .picker-section-bar .sec-chevron { color:#888; font-size:0.75rem; transition:transform .2s; }
    .picker-section-bar.open .sec-chevron { transform:rotate(180deg); }

    .picker-section-body { display:none; padding:4px 0; }
    .picker-section-body.open { display:block; }

    .picker-emp-row { display:flex; align-items:center; gap:8px; padding:5px 14px 5px 50px; }
    .picker-emp-row:hover { background:#f5faff; }
    .picker-emp-row input[type=checkbox] { width:14px; height:14px; cursor:pointer; flex-shrink:0; }
    .picker-emp-row label { font-size:0.83rem; color:#333; margin:0; cursor:pointer; flex:1; }
    .picker-emp-row .emp-id { font-size:0.75rem; color:#888; }
    .picker-no-section { padding:4px 0 4px 28px; }
    .picker-loading { padding:14px 20px; color:#888; font-size:0.85rem; }

    .selected-pills { display:flex; flex-wrap:wrap; gap:6px; min-height:36px;
        padding:6px 10px; border:1px solid #d0dce4; border-radius:6px; background:#f9fbfd; }
    .pill { display:inline-flex; align-items:center; gap:4px; background:#226b86;
        color:#fff; border-radius:30px; padding:3px 10px; font-size:0.78rem; white-space:nowrap; }
    .pill .pill-x { cursor:pointer; font-size:1rem; line-height:1; opacity:0.7;
        border:none; background:transparent; color:#fff; padding:0 0 0 4px; }
    .pill .pill-x:hover { opacity:1; }
    .picker-toolbar { display:flex; gap:6px; margin-bottom:6px; align-items:center; }
    .picker-search { flex:1; padding:5px 10px; border:1px solid #cdd6dc;
        border-radius:4px; font-size:0.85rem; color:#333; outline:none; }
    .picker-search:focus { border-color:#226b86; }
</style>
@endsection

@section('content')
<div class="row">
<div class="col-sm-12">
<div class="col-sm-12 card" style="padding:16px 18px;">

<form method="POST" action="{{ route('commongoal.update', $commonGoal->Id) }}" id="goalForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="save_action" id="saveAction" value="draft">
    <input type="hidden" name="year" value="{{ $commonGoal->Year }}">

    {{-- TOP TOOLBAR --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-warning">
            <i class="fa fa-info-circle"></i>
            Edit employees, goals, then save.
        </span>
        <div>
            <a href="{{ route('commongoal.index', ['year' => $commonGoal->Year]) }}"
               class="btn btn-secondary btn-sm">Cancel</a>
            <button type="button" class="btn btn-primary btn-sm ml-1" onclick="addGoal()">
                <i class="fa fa-plus"></i> Add Goal
            </button>
            <button type="submit" class="btn btn-default btn-sm ml-1"
                    onclick="document.getElementById('saveAction').value='draft'"
                    style="border:1px solid #999;">
                <i class="fa fa-floppy-o"></i> Save as Draft
            </button>
            <button type="submit" class="btn btn-success btn-sm ml-1"
                    onclick="document.getElementById('saveAction').value='publish'">
                <i class="fa fa-check"></i> Publish Goals
            </button>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="row">
        {{-- LEFT: Employee Picker --}}
        <div class="col-md-4">
            <div class="card" style="padding:14px 16px; border:1px solid #d0dce4;">
                <h6 style="color:#226b86; margin-bottom:10px;">
                    <i class="fa fa-users"></i> Assign to Employees
                </h6>
                <div class="mb-2" style="font-size:0.8rem; color:#666;">
                    Selected: <span id="selectedCount">0</span> employee(s)
                </div>
                <div class="selected-pills mb-2" id="selectedPills">
                    <span class="text-muted" id="pillsPlaceholder" style="font-size:0.8rem;line-height:28px;">
                        None selected
                    </span>
                </div>
                <div class="picker-toolbar mb-2">
                    <input type="text" class="picker-search" id="empSearch"
                           placeholder="Search department or employee…"
                           oninput="filterPicker(this.value)">
                    <button type="button" class="btn btn-sm btn-link p-0 ml-1"
                            style="color:#226b86;white-space:nowrap;" onclick="selectAllVisible()">All</button>
                    <button type="button" class="btn btn-sm btn-link p-0 ml-1"
                            style="color:#dc3545;white-space:nowrap;" onclick="clearAll()">Clear</button>
                </div>
                <div class="picker-wrap" id="deptTree">
                    <div class="picker-loading"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Goals --}}
        <div class="col-md-8">
            <div id="goalsAccordion"></div>
        </div>
    </div>

</form>

</div>
</div>
</div>
@endsection

@section('pagescripts')
@include('commongoals._employeepicker', [
    'departments'       => $departments,
    'preSelectedEmpIds' => $preSelectedDeptIds,
    'preSelectedEmps'   => $assignedEmpDetails,
])
@include('commongoals._goalscript', [
    'existingGoals'  => $goalsJson,
    'nextGoalNumber' => $nextGoalNumber,
    'selectedYear'   => $commonGoal->Year,
])
@endsection
