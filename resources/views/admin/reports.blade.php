<x-layouts.app>
    @php
        $activeTab = $active_tab ?? 'sales';
    @endphp
    <div class="mx-auto mt-2 bg-white shadow p-6 rounded">
        <h2 class="text-xl font-bold mb-6">Reports</h2>

        {{-- Tab Switcher --}}
        <div class="flex border-b mb-6">
            <button type="button" id="tab-btn-sales"
                class="report-tab-btn px-4 py-2 font-semibold border-b-2 {{ $activeTab == 'sales' ? 'border-[#ab5f00] text-[#ab5f00]' : 'border-transparent text-gray-500' }}">
                Sales Report
            </button>
            <button type="button" id="tab-btn-product"
                class="report-tab-btn px-4 py-2 font-semibold border-b-2 {{ $activeTab == 'product' ? 'border-[#ab5f00] text-[#ab5f00]' : 'border-transparent text-gray-500' }}">
                Product Performance Report
            </button>
        </div>

        {{-- ============ TAB 1: EXISTING SALES / MILK REPORT ============ --}}
        <div id="panel-sales" class="report-panel {{ $activeTab == 'sales' ? '' : 'hidden' }}">
            <form method="POST" action="{{ route('export_report') }}" id="reportForm">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label>From Date</label>
                        <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label>To Date</label>
                        <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label>User</label>
                        <select name="user_id" class="w-full border rounded px-3 py-2 choice-select">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Type<span class="text-red-500">*</span></label>
                        <select name="type" id="type" class="w-full border rounded px-3 py-2 choice-select"
                            required>
                            <option value="">Please Select Type</option>
                            <option value="grocery" {{ ($filters['type'] ?? '') == 'grocery' ? 'selected' : '' }}>
                                Grocery</option>
                            <option value="milk" {{ ($filters['type'] ?? '') == 'milk' ? 'selected' : '' }}>Milk
                            </option>
                        </select>
                    </div>
                    <div>
                        <label>Report Type<span class="text-red-500">*</span></label>
                        <select name="report_type" id="report_type"
                            class="w-full border rounded px-3 py-2 choice-select" required>
                            <option value="">Please Select Report Type</option>
                            <option value="detailed"
                                {{ ($filters['report_type'] ?? '') == 'detailed' ? 'selected' : '' }}>Detailed
                            </option>
                            <option value="summary"
                                {{ ($filters['report_type'] ?? '') == 'summary' ? 'selected' : '' }}>Summary
                            </option>
                            <option value="daily" {{ ($filters['report_type'] ?? '') == 'daily' ? 'selected' : '' }}>
                                Daily</option>
                        </select>
                    </div>
                    <div>
                        <label>View Type<span class="text-red-500">*</span></label>
                        <select name="view_type" id="view_type" class="w-full border rounded px-3 py-2 choice-select"
                            required>
                            <option value="">Please Select View Type</option>
                            <option value="view" {{ ($filters['view_type'] ?? '') == 'view' ? 'selected' : '' }}>
                                View</option>
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

            @if ($activeTab == 'sales' && !empty($filters) && !empty($data))
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
                                        $filters['report_type'],
                                    ))->collection();
                                @endphp

                                @forelse ($rows as $row)
                                    <tr class="hover:bg-gray-50 transition">
                                        @foreach ($row as $col)
                                            <td class="px-4 py-2 border">
                                                {{ $col }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-4 py-4 border text-center text-gray-400" colspan="10">No
                                            records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- ============ TAB 2: PRODUCT PERFORMANCE REPORT ============ --}}
        <div id="panel-product" class="report-panel {{ $activeTab == 'product' ? '' : 'hidden' }}">
            <form method="POST" action="{{ route('export_product_report') }}" id="productReportForm">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <label>Product<span class="text-red-500">*</span></label>
                        <select name="product_id" id="product_id"
                            class="w-full border rounded px-3 py-2 choice-select" required>
                            <option value="">Please Select Product</option>
                            <option value="all"
                                {{ ($filters['product_id'] ?? '') == 'all' ? 'selected' : '' }}>All Products
                            </option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}"
                                    {{ ($filters['product_id'] ?? '') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>From Date</label>
                        <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label>To Date</label>
                        <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label>View Type<span class="text-red-500">*</span></label>
                        <select name="view_type" id="product_view_type"
                            class="w-full border rounded px-3 py-2 choice-select" required>
                            <option value="">Please Select View Type</option>
                            <option value="view" {{ ($filters['view_type'] ?? '') == 'view' ? 'selected' : '' }}>
                                View</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    Only <strong>delivered</strong> orders are included in this report.
                </p>
                <div class="mt-6 text-center">
                    <button type="submit" class="bg-[#ab5f00] text-white px-6 py-2 rounded">
                        Generate
                    </button>
                </div>
            </form>

            @if ($activeTab == 'product' && !empty($summary))
                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">
                        {{ ($is_all_products ?? false) ? 'All Products' : ($product->name ?? '') }} — Performance
                        Summary</h2>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                        <div class="bg-gray-50 border rounded-lg p-4 text-center">
                            <div class="text-xs uppercase text-gray-500">Orders</div>
                            <div class="text-2xl font-bold text-gray-800">{{ $summary['total_orders'] }}</div>
                        </div>
                        <div class="bg-gray-50 border rounded-lg p-4 text-center">
                            <div class="text-xs uppercase text-gray-500">Qty Sold</div>
                            <div class="text-2xl font-bold text-gray-800">{{ $summary['total_quantity'] }}</div>
                        </div>
                        <div class="bg-blue-50 border rounded-lg p-4 text-center">
                            <div class="text-xs uppercase text-gray-500">Total Sales</div>
                            <div class="text-2xl font-bold text-blue-700">
                                {{ number_format($summary['total_sales'], 2) }}</div>
                        </div>
                        <div class="bg-yellow-50 border rounded-lg p-4 text-center">
                            <div class="text-xs uppercase text-gray-500">Total Cost</div>
                            <div class="text-2xl font-bold text-yellow-700">
                                {{ number_format($summary['total_cost'], 2) }}</div>
                        </div>
                        <div class="bg-green-50 border rounded-lg p-4 text-center">
                            <div class="text-xs uppercase text-gray-500">Total Profit</div>
                            <div class="text-2xl font-bold text-green-700">
                                {{ number_format($summary['total_profit'], 2) }}</div>
                        </div>
                    </div>

                    @if ($is_all_products ?? false)
                        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-100 text-gray-700 text-sm uppercase">
                                    <tr>
                                        <th class="px-4 py-3 border text-center">S.No</th>
                                        <th class="px-4 py-3 border">Product</th>
                                        <th class="px-4 py-3 border text-center">Orders</th>
                                        <th class="px-4 py-3 border text-center">Qty Sold</th>
                                        <th class="px-4 py-3 border text-right">Sales</th>
                                        <th class="px-4 py-3 border text-right">Cost</th>
                                        <th class="px-4 py-3 border text-right">Profit</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 text-sm">
                                    @forelse ($by_product as $row)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-2 border text-center">{{ $loop->iteration }}</td>
                                            <td class="px-4 py-2 border">{{ $row['name'] }}</td>
                                            <td class="px-4 py-2 border text-center">{{ $row['orders'] }}</td>
                                            <td class="px-4 py-2 border text-center">{{ $row['quantity'] }}</td>
                                            <td class="px-4 py-2 border text-right">
                                                {{ number_format($row['sales'], 2) }}</td>
                                            <td class="px-4 py-2 border text-right">
                                                {{ number_format($row['cost'], 2) }}</td>
                                            <td class="px-4 py-2 border text-right">
                                                {{ number_format($row['profit'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-4 py-4 border text-center text-gray-400" colspan="7">No
                                                delivered orders found in the selected range.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-100 text-gray-700 text-sm uppercase">
                                    <tr>
                                        <th class="px-4 py-3 border">Date</th>
                                        <th class="px-4 py-3 border text-center">Orders</th>
                                        <th class="px-4 py-3 border text-center">Qty Sold</th>
                                        <th class="px-4 py-3 border text-right">Sales</th>
                                        <th class="px-4 py-3 border text-right">Cost</th>
                                        <th class="px-4 py-3 border text-right">Profit</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 text-sm">
                                    @forelse ($daily as $row)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-2 border">{{ $row['date'] }}</td>
                                            <td class="px-4 py-2 border text-center">{{ $row['orders'] }}</td>
                                            <td class="px-4 py-2 border text-center">{{ $row['quantity'] }}</td>
                                            <td class="px-4 py-2 border text-right">
                                                {{ number_format($row['sales'], 2) }}
                                            </td>
                                            <td class="px-4 py-2 border text-right">
                                                {{ number_format($row['cost'], 2) }}
                                            </td>
                                            <td class="px-4 py-2 border text-right">
                                                {{ number_format($row['profit'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-4 py-4 border text-center text-gray-400" colspan="6">No
                                                delivered orders found for this product in the selected range.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>
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

        $(document).on("submit", "#productReportForm", function(e) {
            let product_id = $("#product_id").val();
            let view_type = $("#product_view_type").val();
            if (product_id == "" || product_id == undefined) {
                e.preventDefault();
                showToast("Please Select Product!", "error", 2000);
            }

            if (view_type == "" || view_type == undefined) {
                e.preventDefault();
                showToast("Please Select View Type!", "error", 2000);
            }
        });

        $(".report-tab-btn").on("click", function() {
            let isSales = this.id === "tab-btn-sales";
            $("#panel-sales").toggleClass("hidden", !isSales);
            $("#panel-product").toggleClass("hidden", isSales);
            $(".report-tab-btn").removeClass("border-[#ab5f00] text-[#ab5f00]").addClass(
                "border-transparent text-gray-500");
            $(this).removeClass("border-transparent text-gray-500").addClass(
                "border-[#ab5f00] text-[#ab5f00]");
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
