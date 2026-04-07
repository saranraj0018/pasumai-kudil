<x-layouts.app>
    <div class="mx-auto mt-2 bg-white shadow p-6 rounded">
        <h2 class="text-xl font-bold mb-6">Download Report</h2>
        <form method="POST" action="{{ route('export_report') }}" id="reportForm">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label>From Date</label>
                    <input type="date" name="from_date" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label>To Date</label>
                    <input type="date" name="to_date" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label>User</label>
                    <select name="user_id" class="w-full border rounded px-3 py-2 choice-select">
                        <option value="">All Users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Type<span class="text-red-500">*</span></label>
                    <select name="type" id="type" class="w-full border rounded px-3 py-2 choice-select"
                        required>
                        <option value="">Please Select Type</option>
                        <option value="grocery">Grocery</option>
                        <option value="milk">Milk</option>
                    </select>
                </div>
                <div>
                    <label>Report Type<span class="text-red-500">*</span></label>
                    <select name="report_type" id="report_type" class="w-full border rounded px-3 py-2 choice-select"
                        required>
                        <option value="">Please Select Report Type</option>
                        <option value="detailed">Detailed</option>
                        <option value="summary">Summary</option>
                        <option value="daily">Daily</option>
                    </select>
                </div>
                <div>
                    <label>View Type<span class="text-red-500">*</span></label>
                    <select name="view_type" id="view_type" class="w-full border rounded px-3 py-2 choice-select"
                        required>
                        <option value="">Please Select View Type</option>
                        <option value="view">View</option>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 text-center">
                <button type="submit" class="bg-[#ab5f00] text-white px-6 py-2 rounded">
                    Generate
                </button>
            </div>
        </form>
        @if (!empty($filters) && !empty($data))
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Report View</h2>

                <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                    <table class="min-w-full border border-gray-200">

                        <thead class="bg-gray-100 text-gray-700 text-sm uppercase">

                            @if ($filters['report_type'] == 'summary')
                                <tr>
                                    <th class="px-4 py-3 border">Name</th>
                                    <th class="px-4 py-3 border text-center">Total Qty</th>
                                    <th class="px-4 py-3 border text-right">Total Amount</th>
                                </tr>
                            @endif

                            @if ($filters['report_type'] == 'daily')
                                <tr>
                                    <th class="px-4 py-3 border">Date</th>
                                    <th class="px-4 py-3 border text-center">Total Qty</th>
                                    <th class="px-4 py-3 border text-right">Total Amount</th>
                                </tr>
                            @endif

                            @if ($filters['report_type'] == 'detailed')
                                @if ($filters['type'] === 'grocery')
                                    <tr>
                                        <th class="px-4 py-3 border">Order ID</th>
                                        <th class="px-4 py-3 border text-left">User</th>
                                        <th class="px-4 py-3 border text-left">User Name</th>
                                        <th class="px-4 py-3 border text-left">Qty</th>
                                        <th class="px-4 py-3 border text-left">Price</th>
                                        <th class="px-4 py-3 border text-left">Total</th>
                                        <th class="px-4 py-3 border">Date</th>
                                    </tr>
                                @endif
                                @if ($filters['type'] === 'milk')
                                    <tr>
                                        <th class="px-4 py-3 border text-left">User Name</th>
                                        <th class="px-4 py-3 border text-left">Name</th>
                                        <th class="px-4 py-3 border text-center">Qty</th>
                                        <th class="px-4 py-3 border text-left">Price</th>
                                        <th class="px-4 py-3 border text-left">Pack</th>
                                        <th class="px-4 py-3 border text-left">Delivery Status</th>
                                        <th class="px-4 py-3 border">Date</th>
                                    </tr>
                                @endif
                            @endif
                        </thead>

                        <tbody class="text-gray-700 text-sm">
                                      @php
    $rows = (new \App\Exports\ReportExport(
        $data,
        $filters['type'],
        $filters['report_type']
    ))->collection();
@endphp

                            @foreach ($rows as $row)
                                <tr class="hover:bg-gray-50 transition">
                                    @foreach ($row as $col)
                                        <td class="px-4 py-2 border">
                                            {{ $col }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
<script>
    $(document).ready(function() {
        $(document).on("submit", "#reportForm", function(e) {
            let type = $("#type").val();
            let view_type = $("#view_type").val();
            let report_type = $("#report_type").val();
            if (type == "" || type == undefined) {
                e.preventDefault();
                showToast("Please Select Type field!", "error", 2000);
            }

            if (report_type == "" || report_type == undefined) {
                e.preventDefault();
                showToast("Please Select Report Type!", "error", 2000);
            }

            if (view_type == "" || view_type == undefined) {
                e.preventDefault();
                showToast("Please Select View Type!", "error", 2000);
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const elements = document.querySelectorAll(".choice-select");
        elements.forEach(function(el) {
            new Choices(el, {
                searchEnabled: true,
                itemSelectText: "",
                shouldSort: false,
                allowHTML: true,
            });
        });
    });
</script>
