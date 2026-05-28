@extends('master')
@section('page-title', 'View Goals')
@section('page-header', 'View Goals')

@section('pagestyles')
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
    .goal-card table.task-table {
        background-color: #fff;
        color: #333;
        font-size: 0.85rem;
    }
    .goal-card table.task-table thead th {
        background-color: #f5f7fa;
        color: #555;
        border-color: #dee2e6;
        font-weight: 600;
    }
    .goal-card table.task-table td {
        color: #444;
        border-color: #dee2e6;
        vertical-align: middle;
    }
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
    .goal-toolbar {
        background-color: #fff;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .text-warn-mismatch {
        color: #e53935;
        font-size: 0.8rem;
    }
    .target-badge {
        display: inline-block;
        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 4px;
        padding: 2px 7px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .score-bar-wrap {
        background: #e9ecef;
        border-radius: 4px;
        height: 6px;
        width: 180px;
        margin-top: 6px;
        overflow: hidden;
    }
    .score-bar-fill {
        background: #2196F3;
        height: 100%;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="col-sm-12 card" style="padding: 16px 18px; background:#fff; color:#333;">

            @if(Session::has('successmessage'))
                <div class="alert alert-success mb-3">{{ Session::get('successmessage') }}</div>
            @endif

            {{-- Toolbar --}}
            <div class="goal-toolbar">
                <div>
                    @if($employee)
                        <span style="font-weight:600;color:#333;font-size:0.95rem;">{{ $employee->Name }}</span>
                        <span style="color:#888;font-size:0.82rem;margin-left:8px;">({{ $employee->EmpId }})</span>
                    @endif
                </div>
                <div>
                    <a href="{{ route('goals.edit', $employeeGoal->Id) }}" class="btn btn-default btn-sm mr-1">
                        <i class="fa fa-pencil"></i> Edit
                    </a>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            {{-- Goal cards --}}
            @forelse($goalDetails as $goal)
                @php
                    $goalNumber       = (int)($goal->DisplayOrder / 1000);
                    $halfYearLabel    = match(true) {
                        (bool)$goal->InH1 && (bool)$goal->InH2 => 'H1 & H2',
                        (bool)$goal->InH1                       => 'H1',
                        (bool)$goal->InH2                       => 'H2',
                        default                                 => 'Full Year',
                    };
                    $taskWeightageSum = $goal->targets->sum('Weightage');
                    $hasMismatch      = round($taskWeightageSum, 2) != round($goal->Weightage, 2);
                    $pct              = $goal->Weightage > 0
                                           ? min(100, ($taskWeightageSum / $goal->Weightage) * 100)
                                           : 0;
                @endphp
                <div class="goal-card">
                    <div class="goal-meta">
                        <span class="goal-number">#{{ $goalNumber }}</span>
                        <span class="badge-half">{{ $halfYearLabel }}</span>
                        <span class="badge-year">{{ $goal->Year }}</span>
                    </div>
                    <div class="goal-title">{{ $goal->Description }}</div>
                    <div class="goal-stats">
                        Total Score: <strong>{{ number_format($goal->Weightage, 2) }}</strong>
                        &nbsp;&bull;&nbsp;
                        {{ $goal->targets->count() }} task(s)
                        &nbsp;&bull;&nbsp;
                        Task weightage sum: <strong>{{ number_format($taskWeightageSum, 2) }}</strong>
                        @if($hasMismatch)
                            <span class="text-warn-mismatch ml-2">
                                <i class="fa fa-exclamation-triangle"></i> Weightage mismatch
                            </span>
                        @endif
                    </div>
                    <div class="score-bar-wrap">
                        <div class="score-bar-fill" style="width:{{ $pct }}%;"></div>
                    </div>

                    @if($goal->targets->isNotEmpty())
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered task-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th>Task Description</th>
                                        <th style="width:110px;">Weightage</th>
                                        <th style="width:130px;">Target</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($goal->targets as $task)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $task->Description }}</td>
                                            <td>{{ number_format($task->Weightage, 2) }}</td>
                                            <td><span class="target-badge">{{ $task->Target }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @empty
                <div style="background:#fff;border-radius:6px;padding:40px;text-align:center;color:#999;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <i class="fa fa-bullseye fa-3x" style="opacity:.3;margin-bottom:12px;display:block;"></i>
                    No goals defined for this PMS period.
                </div>
            @endforelse

        </div>
    </div>
</div>
@stop
