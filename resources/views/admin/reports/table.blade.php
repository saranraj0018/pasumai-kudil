<h2>Report View</h2>

<table border="1" cellpadding="8" cellspacing="0">
<thead>

@if($filters['report_type'] == 'summary')
<tr>
<th>Name</th>
<th>Total Qty</th>
<th>Total Amount</th>
</tr>
@endif

@if($filters['report_type'] == 'daily')
<tr>
<th>Date</th>
<th>Total Qty</th>
<th>Total Amount</th>
</tr>
@endif

@if($filters['report_type'] == 'detailed')
<tr>
<th>ID</th>
<th>User</th>
<th>Name</th>
<th>Qty</th>
<th>Price</th>
<th>Total</th>
<th>Date</th>
</tr>
@endif

</thead>

<tbody>

@php
$rows = (new \App\Exports\ReportExport($data, $filters['type'], $filters['report_type']))->collection();
@endphp

@foreach($rows as $row)
<tr>
@foreach($row as $col)
<td>{{ $col }}</td>
@endforeach
</tr>
@endforeach

</tbody>
</table>
