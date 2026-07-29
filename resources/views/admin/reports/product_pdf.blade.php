<div style="text-align: center; margin-bottom: 10px;">
    <img src="{{ public_path('/pasumai.png') }}" height="60">
</div>

<h2 style="text-align:center;">Product Performance Report</h2>
<h3 style="text-align:center;">{{ $is_all_products ? 'All Products' : $product->name }}</h3>

@if (!empty($filters['from_date']) && !empty($filters['to_date']))
    <p style="text-align:center;">{{ $filters['from_date'] }} to {{ $filters['to_date'] }}</p>
@endif

<table width="100%" border="1" cellspacing="0" cellpadding="5" style="margin-bottom: 15px;">
    <tr>
        <th>Total Orders</th>
        <th>Total Quantity Sold</th>
        <th>Total Sales</th>
        <th>Total Cost</th>
        <th>Total Profit</th>
    </tr>
    <tr>
        <td>{{ $summary['total_orders'] }}</td>
        <td>{{ $summary['total_quantity'] }}</td>
        <td>{{ number_format($summary['total_sales'], 2) }}</td>
        <td>{{ number_format($summary['total_cost'], 2) }}</td>
        <td>{{ number_format($summary['total_profit'], 2) }}</td>
    </tr>
</table>

@if ($is_all_products)
    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th>Product</th>
                <th>Orders</th>
                <th>Quantity Sold</th>
                <th>Sales Amount</th>
                <th>Cost Amount</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($by_product as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['orders'] }}</td>
                    <td>{{ $row['quantity'] }}</td>
                    <td>{{ number_format($row['sales'], 2) }}</td>
                    <td>{{ number_format($row['cost'], 2) }}</td>
                    <td>{{ number_format($row['profit'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th>Date</th>
                <th>Orders</th>
                <th>Quantity Sold</th>
                <th>Sales Amount</th>
                <th>Cost Amount</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($daily as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['orders'] }}</td>
                    <td>{{ $row['quantity'] }}</td>
                    <td>{{ number_format($row['sales'], 2) }}</td>
                    <td>{{ number_format($row['cost'], 2) }}</td>
                    <td>{{ number_format($row['profit'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
