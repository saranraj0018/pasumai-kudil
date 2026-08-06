@php
    $rows = $rows ?? collect();
@endphp
<div class="mt-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ $title ?? 'Report View' }}</h2>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-100 text-gray-700 text-sm uppercase">
                <tr>
                    @foreach ($headings as $heading)
                        <th class="px-4 py-3 border">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                @forelse ($rows as $row)
                    <tr class="hover:bg-gray-50 transition">
                        @foreach ($row as $col)
                            <td class="px-4 py-2 border">{{ $col }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-4 border text-center text-gray-400" colspan="{{ count($headings) }}">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
