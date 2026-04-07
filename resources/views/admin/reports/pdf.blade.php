
<div style="text-align: center; margin-bottom: 10px;">
    <img src="{{ public_path('/pasumai.png') }}" height="60">
</div>

<h2 style="text-align:center;">
    @if ($filters['type'] == 'grocerry')
         Grocerry Report
    @endif

    @if ($filters['type'] == 'milk')
         Report Report
    @endif
    </h2>

<table width="100%" border="1" cellspacing="0" cellpadding="5">

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
