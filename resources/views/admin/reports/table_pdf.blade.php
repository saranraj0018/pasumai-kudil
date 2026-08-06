
<div style="text-align: center; margin-bottom: 10px;">
    <img src="{{ public_path('/pasumai.png') }}" height="60">
</div>

<h2 style="text-align:center;">{{ $title }}</h2>
@if (!empty($subtitle))
    <p style="text-align:center;">{{ $subtitle }}</p>
@endif

<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            @foreach ($headings as $heading)
                <th>{{ $heading }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                @foreach ($row as $col)
                    <td>{{ $col }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($headings) }}" style="text-align:center;">No records found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
