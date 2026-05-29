@extends('master')
@section('page-title', 'Edit Weightages')
@section('page-header', 'Edit Weightages')

@section('pagestyles')
<style>
    .emp-block { border:1px solid rgba(255,255,255,0.2); border-radius:6px; margin-bottom:24px; overflow:hidden; }
    .emp-block-header { background:rgba(0,0,0,0.3); color:#fff; padding:10px 16px;
        display:flex; align-items:center; justify-content:space-between; }
    .emp-block-body { padding:14px 16px; }
    .goal-section { margin-bottom:18px; }
    .goal-label { color:#fff; font-weight:600; font-size:0.9rem; margin-bottom:6px; }
    .goal-weight-row { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
    .goal-weight-row label { color:rgba(255,255,255,0.8); font-size:0.82rem; min-width:120px; }
    .tasks-table { width:100%; border-collapse:collapse; font-size:0.85rem; margin-top:6px; }
    .tasks-table th { background:rgba(0,0,0,0.2); color:#fff; padding:7px 10px; border-bottom:1px solid rgba(255,255,255,0.15); text-align:left; }
    .tasks-table td { color:#fff; padding:7px 10px; border-bottom:1px solid rgba(255,255,255,0.08); vertical-align:middle; }
    .tasks-table tr:last-child td { border-bottom:none; }
    .locked-notice { color:rgba(255,255,255,0.55); font-style:italic; font-size:0.85rem; padding:8px 0; }
    input[type="number"].wt-input { width:90px; padding:4px 8px; border-radius:4px; border:1px solid rgba(255,255,255,0.35);
        background:rgba(255,255,255,0.12); color:#fff; font-size:0.88rem; text-align:right; }
    input[type="number"].wt-input:focus { outline:none; border-color:#5bc0de; background:rgba(255,255,255,0.2); }
    input[type="number"].wt-input[disabled] { opacity:0.45; cursor:not-allowed; }
    .badge-locked    { background:#d9534f; color:#fff; border-radius:30px; padding:3px 9px; font-size:0.75rem; }
    .badge-editable  { background:#5cb85c; color:#fff; border-radius:30px; padding:3px 9px; font-size:0.75rem; }
</style>
@endsection

@section('content')
<div class="row">
<div class="col-sm-12">
<div class="col-sm-12 card" style="padding:16px 18px;">

    @if(Session::has('successmessage'))
    <div class="alert alert-success mb-3">{{ Session::get('successmessage') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('commongoal.show', $commonGoal->Id) }}"
           class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h5 style="color:#fff; margin:0;">
            Edit Weightages &mdash;
            <span style="font-size:0.85rem; opacity:0.8;">
                {{ $commonGoal->Title ?: 'Common Goals ' . $commonGoal->Year }}
                ({{ $commonGoal->Year }})
            </span>
        </h5>
    </div>

    <div class="alert alert-info mb-4" style="font-size:0.85rem;">
        <i class="fa fa-info-circle"></i>
        Goal and task descriptions are read-only here. Only weightages can be changed.
        Employees who have already started self-rating are shown as locked.
    </div>

    @if(empty($employeeData))
        <p style="color:rgba(255,255,255,0.65); text-align:center; padding:30px;">
            No employees assigned to this common goal set.
        </p>
    @else

    <form method="POST" action="{{ route('commongoal.weightages.update', $commonGoal->Id) }}">
        @csrf

        @foreach($employeeData as $row)
        @php $emp = $row['employee']; $isLocked = $row['isLocked']; @endphp
        <div class="emp-block">
            <div class="emp-block-header">
                <span>
                    <i class="fa fa-user"></i>
                    <strong>{{ $emp->Name }}</strong>
                    @if($emp->EmpId) <small style="opacity:.75;">({{ $emp->EmpId }})</small> @endif
                    &nbsp;&middot;&nbsp;
                    <small style="opacity:.75;">{{ $emp->DeptName }}{{ $emp->SectionName ? ' / '.$emp->SectionName : '' }}</small>
                </span>
                <span>
                    @if($isLocked)
                        <span class="badge-locked"><i class="fa fa-lock"></i> Locked — self-rating started</span>
                    @else
                        <span class="badge-editable"><i class="fa fa-pencil"></i> Editable</span>
                    @endif
                </span>
            </div>

            <div class="emp-block-body">
                @if($row['goalDetails']->isEmpty())
                    <p class="locked-notice">No goals published to this employee yet.</p>
                @else
                    @foreach($row['goalDetails'] as $detail)
                    @php $goalNum = (int)($detail->DisplayOrder / 1000); @endphp
                    <div class="goal-section">
                        <div class="goal-label">
                            Goal {{ $goalNum }}: {{ $detail->Description }}
                        </div>
                        <div class="goal-weight-row">
                            <label>Goal Weightage</label>
                            <input type="number"
                                   name="goal_weight[{{ $detail->Id }}]"
                                   class="wt-input"
                                   value="{{ number_format($detail->Weightage, 2, '.', '') }}"
                                   step="0.01" min="0" max="100"
                                   {{ $isLocked ? 'disabled' : '' }}>
                            @if($isLocked)
                                {{-- hidden so locked values are not submitted --}}
                            @endif
                        </div>

                        @if($detail->targets->isNotEmpty())
                        <table class="tasks-table">
                            <thead>
                                <tr>
                                    <th style="width:60px;">No.</th>
                                    <th>Task Description</th>
                                    <th style="width:100px;">Target</th>
                                    <th style="width:110px;">Weightage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detail->targets as $ti => $task)
                                <tr>
                                    <td>{{ $goalNum }}.{{ $ti + 1 }}</td>
                                    <td>{{ $task->Description }}</td>
                                    <td>{{ $task->Target !== '-' ? $task->Target : '—' }}</td>
                                    <td>
                                        <input type="number"
                                               name="task_weight[{{ $task->Id }}]"
                                               class="wt-input"
                                               value="{{ number_format($task->Weightage, 2, '.', '') }}"
                                               step="0.01" min="0" max="100"
                                               {{ $isLocked ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-end mt-2 mb-4">
            <a href="{{ route('commongoal.show', $commonGoal->Id) }}"
               class="btn btn-secondary btn-sm mr-2">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa fa-save"></i> Save Weightages
            </button>
        </div>
    </form>

    @endif

</div>
</div>
</div>
@endsection
