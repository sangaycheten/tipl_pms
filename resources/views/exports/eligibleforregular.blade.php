<?php $var = 'Round of Last Reward'; ?>
<table class="table table-condensed table-bordered">
    <thead>
        <tr>
            <th colspan="7">Eligible for Regular Promotion According to Grade Step</th>
        </tr>
        <tr>
            <th>Sl #</th>
            <th>Employee</th>
            <th>Designation</th>
            <th>Grade Step</th>
            <th>Last Reward in PMS</th>
            <th>Requirement for RSP</th>
            <th>Achievement</th>
            <th>Achievement (D.O.R)</th>
        </tr>
    </thead>
    <tbody>
        <?php $slNo = 1; ?>
        @forelse($regularPromotionEligible as $data)
            <tr>
                <td>{{ $slNo++ }}</td>
                <td>{{ $data->Employee }} (EmpId: {{ $data->EmpId }})</td>
                <td>{{ $data->Designation }} - {{ $data->Section }} ({{ $data->Department }})</td>
                <td>{{ $data->GradeStep }}</td>
                <td>{{ $data->$var ? 'Round ' . $data->$var : '' }}</td>
                <td><em>{{ $data->Requirement }} </em></td>
                <td>{{ $data->Achieved }} &nbsp;</td>
                <td>{{ $data->AchievementDOR }}&nbsp;</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No data to display!</td>
            </tr>
        @endforelse
    </tbody>
</table>
