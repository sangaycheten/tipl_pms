@extends('master')
@section('page-title', 'View Common Goals')
@section('page-header', 'View Common Goals')

@section('pagestyles')
<style>
    /* ── Goal cards (right column) ───────────────────────── */
    .cg-view-card { border:1px solid rgba(255,255,255,0.2); border-radius:6px; margin-bottom:20px; overflow:hidden; }
    .cg-view-header { background:rgba(0,0,0,0.25); color:#fff; padding:10px 16px;
        display:flex; align-items:center; justify-content:space-between; }
    .cg-tasks-table { width:100%; border-collapse:collapse; font-size:0.88rem; }
    .cg-tasks-table thead tr { background:rgba(0,0,0,0.18); }
    .cg-tasks-table th { padding:8px 12px; border-bottom:1px solid rgba(255,255,255,0.2); color:#fff; font-weight:600; }
    .cg-tasks-table td { padding:7px 12px; border-bottom:1px solid rgba(255,255,255,0.1); vertical-align:top; color:#fff; }
    .cg-tasks-table tr:last-child td { border-bottom:none; }
    .cg-tasks-table tfoot tr { background:rgba(0,0,0,0.18); }
    .cg-tasks-table tfoot td { color:#fff; font-weight:600; border-bottom:none; }

    /* ── Status badges ───────────────────────────────────── */
    .badge-draft     { background:#f0ad4e; color:#fff; border-radius:30px; padding:3px 10px; font-size:0.75rem; font-weight:600; }
    .badge-published { background:#5cb85c; color:#fff; border-radius:30px; padding:3px 10px; font-size:0.75rem; font-weight:600; }

    /* ── Assigned employees card (left column) ───────────── */
    .emp-panel { background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);
        border-radius:6px; padding:14px 16px; }
    .emp-panel-title { color:#fff; font-weight:600; font-size:0.95rem; margin-bottom:10px; }
    .dept-group-label { font-weight:600; color:rgba(255,255,255,0.75); font-size:0.82rem;
        margin:8px 0 4px 0; padding-bottom:2px; border-bottom:1px solid rgba(255,255,255,0.2); }
    .emp-badge { display:inline-flex; align-items:center; background:rgba(255,255,255,0.15);
        border:1px solid rgba(255,255,255,0.3); color:#fff; border-radius:30px;
        padding:3px 10px; font-size:0.78rem; margin:2px 3px; }
    .emp-badge small { opacity:.75; }

    /* ── Title area ──────────────────────────────────────── */
    .cg-page-title { color:#fff; margin-bottom:2px; }
    .cg-page-year  { color:rgba(255,255,255,0.7); font-size:0.85rem; }
</style>
@endsection

@section('content')
<div class="row">
<div class="col-sm-12">
<div class="col-sm-12 card" style="padding:16px 18px;">

    @if(Session::has('successmessage'))
    <div class="alert alert-success mb-3">{{ Session::get('successmessage') }}</div>
    @endif
    @if(Session::has('infomessage'))
    <div class="alert alert-info mb-3">{{ Session::get('infomessage') }}</div>
    @endif

    {{-- Lock banner --}}
    @if($isLocked)
    <div class="alert alert-warning mb-3" style="font-size:0.88rem;">
        <i class="fa fa-lock mr-1"></i>
        <strong>View only.</strong>
        One or more assigned employees have started their self-rating — this common goal set can no longer be edited or deleted.
    </div>
    @endif

    {{-- Action bar --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('commongoal.index', ['year' => $commonGoal->Year]) }}"
           class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <div>
            @if(!$isLocked)
            <a href="{{ route('commongoal.edit', $commonGoal->Id) }}"
               class="btn btn-warning btn-sm">
                <i class="fa fa-pencil"></i> Edit
            </a>
            <form method="POST" action="{{ route('commongoal.publish', $commonGoal->Id) }}"
                  onsubmit="return confirm('{{ $publishConfirmMsg }}')"
                  style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-{{ $commonGoal->Status === 'published' ? 'info' : 'success' }} btn-sm ml-1">
                    <i class="fa fa-{{ $commonGoal->Status === 'published' ? 'refresh' : 'check' }}"></i>
                    {{ $commonGoal->Status === 'published' ? 'Re-push to Employees' : 'Publish Now' }}
                </button>
            </form>
            <form method="POST" action="{{ route('commongoal.destroy', $commonGoal->Id) }}"
                  onsubmit="return confirm('Delete this common goal set?')"
                  style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm ml-1">
                    <i class="fa fa-trash"></i> Delete
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Header info --}}
    <div class="mb-3">
        <h5 class="cg-page-title">
            {{ $commonGoal->Title ?: 'Common Goals ' . $commonGoal->Year }}
            &nbsp;
            <span class="{{ $commonGoal->Status === 'published' ? 'badge-published' : 'badge-draft' }}">
                {{ strtoupper($commonGoal->Status) }}
            </span>
        </h5>
        <span class="cg-page-year">Year: {{ $commonGoal->Year }}</span>
    </div>

    <div class="row">
        {{-- LEFT: Assigned employees --}}
        <div class="col-md-4">
            <div class="emp-panel">
                <div class="emp-panel-title">
                    <i class="fa fa-users"></i>
                    Assigned Employees
                    <span class="badge badge-light ml-1" style="color:#226b86;">
                        {{ $assignedEmployees->count() }}
                    </span>
                </div>

                @if($assignedEmployees->isEmpty())
                    <p style="color:rgba(255,255,255,0.65); font-size:0.85rem;">No employees assigned.</p>
                @else
                    @php $byDept = $assignedEmployees->groupBy('DeptName'); @endphp
                    @foreach($byDept as $deptName => $emps)
                        <div class="dept-group-label">
                            <i class="fa fa-building-o"></i> {{ $deptName }}
                        </div>
                        @foreach($emps as $emp)
                            <span class="emp-badge">
                                {{ $emp->Name }}
                                @if($emp->EmpId)
                                    <small style="margin-left:4px;">({{ $emp->EmpId }})</small>
                                @endif
                                @if($emp->SectionName)
                                    <small style="margin-left:4px;">· {{ $emp->SectionName }}</small>
                                @endif
                            </span>
                        @endforeach
                    @endforeach
                @endif
            </div>
        </div>

        {{-- RIGHT: Goals --}}
        <div class="col-md-8">
            @forelse($goalDetails as $detail)
            @php
                $goalNum = (int)($detail->DisplayOrder / 1000);
                $taskNum = 0;
            @endphp
            <div class="cg-view-card">
                <div class="cg-view-header">
                    <strong>Goal {{ $goalNum }}: {{ $detail->Description }}</strong>
                    <span style="font-size:0.82rem; opacity:0.85;">
                        Score: {{ number_format($detail->Weightage, 2) }}
                        &nbsp;|&nbsp;
                        @if($detail->InH1 && $detail->InH2) H1 &amp; H2
                        @elseif($detail->InH1) H1
                        @elseif($detail->InH2) H2
                        @else &mdash;
                        @endif
                    </span>
                </div>
                <table class="cg-tasks-table">
                    <thead>
                        <tr>
                            <th style="width:80px; text-align:center;">Task No.</th>
                            <th>Description</th>
                            <th style="width:120px;">Weightage</th>
                            <th style="width:150px;">Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detail->targets as $task)
                        @php $taskNum++ @endphp
                        <tr>
                            <td class="text-center">{{ $goalNum }}.{{ $taskNum }}</td>
                            <td>{{ $task->Description }}</td>
                            <td>{{ number_format($task->Weightage, 2) }}</td>
                            <td>{{ $task->Target !== '-' ? $task->Target : '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center" style="padding:14px; color:rgba(255,255,255,0.6);">
                                No tasks defined.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($detail->targets->count())
                    <tfoot>
                        <tr>
                            <td colspan="2" style="padding:7px 12px; text-align:right;">Total Weightage</td>
                            <td style="padding:7px 12px;">
                                {{ number_format($detail->targets->sum('Weightage'), 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            @empty
            <div class="text-center" style="padding:40px; color:rgba(255,255,255,0.65);">
                No goals defined for this set.
            </div>
            @endforelse
        </div>
    </div>

</div>
</div>
</div>
@endsection
