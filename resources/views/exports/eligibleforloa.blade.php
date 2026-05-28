<?php $var = 'Round of Last Action'; ?>
<table class="table table-condensed table-bordered">
    <thead>
        <tr>
            <th colspan="7">Eligible for LoA</th>
        </tr>
        <tr>
            <th>Sl #</th>
            <th>Employee</th>
            <th>Designation</th>
            <th>Grade Step</th>
            <th>Last Reward in PMS</th>
            <th>Requirement for LoA</th>
            <th>Achievement</th>
        </tr>
    </thead>
    <tbody>
        <?php $slNo = 1; ?>
        @forelse($loaEligible as $data)
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
