<x-layouts.app>
    @php
        $activeTab = $active_tab ?? 'sales';
        $topTab = $activeTab === 'product' ? 'sales' : $activeTab;
    @endphp
    <div class="mx-auto mt-2 bg-white shadow p-6 rounded-xl border border-gray-200">
        <div class="flex items-center gap-2 mb-6">
            <svg class="w-6 h-6 text-[#ab5f00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 17.25v-6.75m6 6.75v-13.5m-11.25 13.5h16.5A2.25 2.25 0 0021.75 15V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75V15a2.25 2.25 0 002.25 2.25z" />
            </svg>
            <h2 class="text-xl font-bold text-gray-800">Reports</h2>
        </div>

        {{-- Segmented Tab Switcher --}}
        <div class="inline-flex flex-wrap gap-1 bg-gray-100 p-1 rounded-lg mb-6">
            <button type="button" id="tab-btn-items"
                class="report-tab-btn flex items-center gap-2 px-4 py-2 rounded-md text-sm font-semibold transition {{ $topTab == 'items' ? 'bg-white text-[#ab5f00] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                Items
            </button>
            <button type="button" id="tab-btn-sales"
                class="report-tab-btn flex items-center gap-2 px-4 py-2 rounded-md text-sm font-semibold transition {{ $topTab == 'sales' ? 'bg-white text-[#ab5f00] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
                Sales
            </button>
        </div>

        @php
            $resultHeadings = $table_headings ?? null;
            $resultRows = $table_rows ?? null;
            $resultTitle = $table_title ?? null;
        @endphp

        {{-- ============ TAB: ITEMS ============ --}}
        <div id="panel-items" class="report-panel {{ $topTab == 'items' ? '' : 'hidden' }}">
            <form method="POST" action="{{ route('export_item_list_report') }}" id="itemListForm">
                @csrf
                <div class="rounded-lg border border-gray-200   mb-4">
                    <div class="bg-amber-50 border-b border-amber-100 px-4 py-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-[#ab5f00]">Filters</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Category<span
                                    class="text-red-500">*</span></label>
                            <select name="category_id" class="w-full border rounded px-3 py-2 choice-select" required>
                                <option value="">Please Select Category</option>
                                @foreach ($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">View Type<span
                                    class="text-red-500">*</span></label>
                            <select name="view_type" class="w-full border rounded px-3 py-2 choice-select" required>
                                <option value="">Please Select View Type</option>
                                <option value="view">View</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center">
                    <button type="submit"
                        class="flex items-center gap-2 bg-[#ab5f00] hover:bg-[#8a4c00] text-white px-6 py-2 rounded-md font-semibold transition">
                        Generate
                    </button>
                </div>
            </form>

            @if ($topTab == 'items' && $resultHeadings)
                @include('admin.reports.result_table', ['headings' => $resultHeadings, 'rows' => $resultRows, 'title' => $resultTitle])
            @endif
        </div>

        {{-- ============ TAB: SALES ============ --}}
        <div id="panel-sales" class="report-panel {{ $topTab == 'sales' ? '' : 'hidden' }}">
            <div class="mb-4">
                <label class="block text-sm text-gray-600 mb-1">Report</label>
                <select id="sales_report_kind" class="w-full sm:w-80 border rounded px-3 py-2">
                    <option value="order_report">Order Report (Detailed / Summary / Daily)</option>
                    <option value="product_performance">Product Performance</option>
                    <option value="bill_details">Sale Bill Details</option>
                    <option value="item_wise_sales">Item Wise Sales</option>
                    <option value="category_wise_sales">Category Wise Sales</option>
                    <option value="sales_amount">Sales Amount (Cash / Card / GPay / Credit)</option>
                    <option value="daywise_bill_summary">DayWise Bill Summary</option>
                    <option value="item_wise_profit">Item Wise Profit</option>
                    <option value="category_wise_profit">Category Wise Profit</option>
                    <option value="day_wise_profit">Day Wise Profit</option>
                </select>
            </div>

            {{-- Order Report (existing) --}}
            <form method="POST" action="{{ route('export_report') }}" id="reportForm" class="sales-kind-form">
                @csrf
                <div class="rounded-lg border border-gray-200 mb-4">
                    <div class="bg-amber-50 border-b border-amber-100 px-4 py-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-[#ab5f00]">Filters</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">From Date</label>
                            <input type="date" name="from_date" id="sales_from_date"
                                value="{{ $filters['from_date'] ?? '' }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">To Date</label>
                            <input type="date" name="to_date" id="sales_to_date"
                                value="{{ $filters['to_date'] ?? '' }}" class="w-full border rounded px-3 py-2">
                            <p class="hidden text-xs text-red-500 mt-1" data-date-error-for="sales_to_date">To Date
                                must be on or after From Date.</p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">User</label>
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
                            <label class="block text-sm text-gray-600 mb-1">Type<span
                                    class="text-red-500">*</span></label>
                            <select name="type" id="type" class="w-full border rounded px-3 py-2 choice-select"
                                required>
                                <option value="">Please Select Type</option>
                                <option value="grocery"
                                    {{ ($filters['type'] ?? '') == 'grocery' ? 'selected' : '' }}>Grocery</option>
                                <option value="milk" {{ ($filters['type'] ?? '') == 'milk' ? 'selected' : '' }}>Milk
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200">
                    <div class="bg-gray-50 border-b border-gray-200 px-4 py-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-gray-600">Report Options</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Report Type<span
                                    class="text-red-500">*</span></label>
                            <select name="report_type" id="report_type"
                                class="w-full border rounded px-3 py-2 choice-select" required>
                                <option value="">Please Select Report Type</option>
                                <option value="detailed"
                                    {{ ($filters['report_type'] ?? '') == 'detailed' ? 'selected' : '' }}>Detailed
                                </option>
                                <option value="summary"
                                    {{ ($filters['report_type'] ?? '') == 'summary' ? 'selected' : '' }}>Summary
                                </option>
                                <option value="daily"
                                    {{ ($filters['report_type'] ?? '') == 'daily' ? 'selected' : '' }}>Daily</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">View Type<span
                                    class="text-red-500">*</span></label>
                            <select name="view_type" id="view_type"
                                class="w-full border rounded px-3 py-2 choice-select" required>
                                <option value="">Please Select View Type</option>
                                <option value="view"
                                    {{ ($filters['view_type'] ?? '') == 'view' ? 'selected' : '' }}>View</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-center">
                    <button type="submit"
                        class="flex items-center gap-2 bg-[#ab5f00] hover:bg-[#8a4c00] text-white px-6 py-2 rounded-md font-semibold transition">
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

            {{-- Product Performance (existing) --}}
            <form method="POST" action="{{ route('export_product_report') }}" id="productReportForm"
                class="sales-kind-form hidden">
                @csrf
                <div class="rounded-lg border border-gray-200 overflow-hidden mb-4">
                    <div class="bg-amber-50 border-b border-amber-100 px-4 py-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-[#ab5f00]">Filters</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Product<span
                                    class="text-red-500">*</span></label>
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
                            <label class="block text-sm text-gray-600 mb-1">From Date</label>
                            <input type="date" name="from_date" id="product_from_date"
                                value="{{ $filters['from_date'] ?? '' }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">To Date</label>
                            <input type="date" name="to_date" id="product_to_date"
                                value="{{ $filters['to_date'] ?? '' }}" class="w-full border rounded px-3 py-2">
                            <p class="hidden text-xs text-red-500 mt-1" data-date-error-for="product_to_date">To Date
                                must be on or after From Date.</p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">View Type<span
                                    class="text-red-500">*</span></label>
                            <select name="view_type" id="product_view_type"
                                class="w-full border rounded px-3 py-2 choice-select" required>
                                <option value="">Please Select View Type</option>
                                <option value="view"
                                    {{ ($filters['view_type'] ?? '') == 'view' ? 'selected' : '' }}>View</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                    </div>
                    <p class="px-4 pb-3 text-sm text-gray-500">
                        Only <strong>delivered</strong> orders are included in this report.
                    </p>
                </div>

                <div class="flex justify-center">
                    <button type="submit"
                        class="flex items-center gap-2 bg-[#ab5f00] hover:bg-[#8a4c00] text-white px-6 py-2 rounded-md font-semibold transition">
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

            {{-- Advanced Sales Reports: Bill Details / Item / Category / Payment split / Profit --}}
            <form method="POST" action="{{ route('export_sales_breakdown_report') }}" id="advancedSalesForm"
                class="sales-kind-form hidden">
                @csrf
                <input type="hidden" name="report_type" id="advancedReportType" value="bill_details">
                <div class="rounded-lg border border-gray-200 overflow-hidden mb-4">
                    <div class="bg-amber-50 border-b border-amber-100 px-4 py-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-[#ab5f00]">Filters</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">From Date</label>
                            <input type="date" name="from_date" id="advanced_from_date"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">To Date</label>
                            <input type="date" name="to_date" id="advanced_to_date"
                                class="w-full border rounded px-3 py-2">
                            <p class="hidden text-xs text-red-500 mt-1" data-date-error-for="advanced_to_date">To Date
                                must be on or after From Date.</p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">View Type<span
                                    class="text-red-500">*</span></label>
                            <select name="view_type" class="w-full border rounded px-3 py-2 choice-select" required>
                                <option value="">Please Select View Type</option>
                                <option value="view">View</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center">
                    <button type="submit"
                        class="flex items-center gap-2 bg-[#ab5f00] hover:bg-[#8a4c00] text-white px-6 py-2 rounded-md font-semibold transition">
                        Generate
                    </button>
                </div>
            </form>

            @if ($topTab == 'sales' && $resultHeadings && $activeTab == 'sales')
                @include('admin.reports.result_table', ['headings' => $resultHeadings, 'rows' => $resultRows, 'title' => $resultTitle])
            @endif
        </div>
    </div>
</x-layouts.app>
<script>
    $(document).ready(function() {
        // Keep a "To Date" from going earlier than its "From Date".
        function wireDateGuard(fromId, toId) {
            const fromEl = document.getElementById(fromId);
            const toEl = document.getElementById(toId);
            if (!fromEl || !toEl) return null;

            function syncMin() {
                if (fromEl.value) {
                    toEl.min = fromEl.value;
                    if (toEl.value && toEl.value < fromEl.value) {
                        toEl.value = '';
                    }
                } else {
                    toEl.removeAttribute('min');
                }
            }

            function isValid() {
                return !(fromEl.value && toEl.value && toEl.value < fromEl.value);
            }

            fromEl.addEventListener('change', syncMin);
            toEl.addEventListener('change', function() {
                const errorEl = document.querySelector('[data-date-error-for="' + toEl.id + '"]');
                if (errorEl) errorEl.classList.toggle('hidden', isValid());
            });

            syncMin();
            return isValid;
        }

        const salesDatesValid = wireDateGuard('sales_from_date', 'sales_to_date');
        const productDatesValid = wireDateGuard('product_from_date', 'product_to_date');
        const advancedDatesValid = wireDateGuard('advanced_from_date', 'advanced_to_date');

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
            if (salesDatesValid && !salesDatesValid()) {
                e.preventDefault();
                showToast("To Date must be on or after From Date!", "error", 2000);
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
            if (productDatesValid && !productDatesValid()) {
                e.preventDefault();
                showToast("To Date must be on or after From Date!", "error", 2000);
            }
        });

        $(document).on("submit", "#advancedSalesForm", function(e) {
            if (advancedDatesValid && !advancedDatesValid()) {
                e.preventDefault();
                showToast("To Date must be on or after From Date!", "error", 2000);
            }
        });

        // ===== Sales tab: toggle between the 4 underlying forms =====
        const advancedReportTypeMap = {
            bill_details: {route: "{{ route('export_sales_breakdown_report') }}", type: "bill_details"},
            item_wise_sales: {route: "{{ route('export_sales_breakdown_report') }}", type: "item_wise"},
            category_wise_sales: {route: "{{ route('export_sales_breakdown_report') }}", type: "category_wise"},
            sales_amount: {route: "{{ route('export_payment_summary_report') }}", type: "sales_amount"},
            daywise_bill_summary: {route: "{{ route('export_payment_summary_report') }}", type: "daywise_bill_summary"},
            item_wise_profit: {route: "{{ route('export_profit_report') }}", type: "item_wise"},
            category_wise_profit: {route: "{{ route('export_profit_report') }}", type: "category_wise"},
            day_wise_profit: {route: "{{ route('export_profit_report') }}", type: "day_wise"},
        };

        $("#sales_report_kind").on("change", function() {
            let val = $(this).val();
            $("#panel-sales .sales-kind-form").addClass("hidden");

            if (val === "order_report") {
                $("#reportForm").removeClass("hidden");
            } else if (val === "product_performance") {
                $("#productReportForm").removeClass("hidden");
            } else if (advancedReportTypeMap[val]) {
                $("#advancedSalesForm").removeClass("hidden");
                $("#advancedSalesForm").attr("action", advancedReportTypeMap[val].route);
                $("#advancedReportType").val(advancedReportTypeMap[val].type);
            }
        });

        // ===== Top-level tab switcher =====
        function activateTopTab(tab) {
            $("#panel-items, #panel-sales").addClass("hidden");
            $("#panel-" + tab).removeClass("hidden");
            $(".report-tab-btn").removeClass("bg-white text-[#ab5f00] shadow-sm").addClass("text-gray-500");
            $("#tab-btn-" + tab).removeClass("text-gray-500").addClass("bg-white text-[#ab5f00] shadow-sm");
        }

        $("#tab-btn-items").on("click", () => activateTopTab("items"));
        $("#tab-btn-sales").on("click", () => activateTopTab("sales"));

        @if ($activeTab == 'product')
            $("#sales_report_kind").val("product_performance").trigger("change");
        @endif
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
