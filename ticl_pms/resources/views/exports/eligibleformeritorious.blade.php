<?php $var = 'Round of Last Reward'; ?>
<table class="table table-condensed table-bordered">
    <thead>
        <tr>
            <th colspan="7">Eligible for Meritorious Promotion by Outstanding Scores</th>
        </tr>
        <tr>
            <th>Sl #</th>
            <th>Employee</th>
            <th>Designation</th>
            <th>Grade Step</th>
            <th>Last Reward in PMS</th>
            <th>Requirement for MSP</th>
            <th>Achievement</th>
        </tr>
    </thead>
    <tbody>
        <?php $slNo = 1; ?>
        @forelse($outstandingEligible as $data)
            <tr>
                <td>{{ $slNo++ }}</td>
                <td>{{ $data->Employee }} (EmpId: {{ $data->EmpId }})</td>
                <td>{{ $data->Designation }} - {{ $data->Section }} ({{ $data->Department }})</td>
                <td>{{ $data->GradeStep }}</td>
                <td>{{ $data->$var ? 'Round ' . $data->$var : '' }}</td>
                <td><em>{{ $data->Requirement }} O</em></td>
                <td>{{ $data->Achieved }} O</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No data to display!</td>
            </tr>
        @endforelse
    </tbody>
</table>

<table class="table table-condensed table-bordered">
    <thead>
        <tr>
            <th colspan="7">Eligible for Meritorious Promotion by Mix of Good and Outstanding Scores</th>
        </tr>
        <tr>
            <th>Sl #</th>
            <th>Employee</th>
            <th>Designation</th>
            <th>Grade Step</th>
            <th>Last Reward in PMS</th>
            <th>Requirement for MSP</th>
            <th>Achievement</th>
        </tr>
    </thead>
    <tbody>
        <?php $slNo = 1; ?>
        @forelse($outstandingAndGoodEligible as $data)
            <tr>
                <td>{{ $slNo++ }}</td>
                <td>{{ $data->Employee }} (EmpId: {{ $data->EmpId }})</td>
                <td>{{ $data->Designation }} - {{ $data->Section }} ({{ $data->Department }})</td>
                <td>{{ $data->GradeStep }}</td>
                <td>{{ $data->$var ? 'Round ' . $data->$var : '' }}</td>
                <td><em>{{ $data->Requirement }} G and O</em></td>
                <td>{{ $data->Achieved }} G and O</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No data to display!</td>
            </tr>
        @endforelse
    </tbody>
</table>
