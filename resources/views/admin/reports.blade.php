<x-layouts.app>
    @php
        $resultHeadings = $table_headings ?? null;
        $resultRows = $table_rows ?? null;
        $resultTitle = $table_title ?? null;
        $filters = $filters ?? [];
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

        <form method="POST" action="{{ route('export_report') }}" id="reportForm">
            @csrf
            <div class="rounded-lg border border-gray-200 mb-4">
                <div class="bg-amber-50 border-b border-amber-100 px-4 py-2">
                    <span class="text-xs font-bold uppercase tracking-wide text-[#ab5f00]">Filters</span>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Report<span
                                class="text-red-500">*</span></label>
                        <select name="report_type" id="report_type"
                            class="w-full border rounded px-3 py-2 choice-select" required>
                            <option value="">Please Select Report</option>
                            <option value="slow_moving"
                                {{ ($filters['report_type'] ?? '') == 'slow_moving' ? 'selected' : '' }}>Slow Moving
                                Item</option>
                            <option value="fast_moving"
                                {{ ($filters['report_type'] ?? '') == 'fast_moving' ? 'selected' : '' }}>Fast Moving
                                Item</option>
                            <option value="non_moving"
                                {{ ($filters['report_type'] ?? '') == 'non_moving' ? 'selected' : '' }}>Non Moving
                                Item</option>
                            <option value="day_wise_profit"
                                {{ ($filters['report_type'] ?? '') == 'day_wise_profit' ? 'selected' : '' }}>Day Wise
                                Profit</option>
                            <option value="item_wise_profit"
                                {{ ($filters['report_type'] ?? '') == 'item_wise_profit' ? 'selected' : '' }}>Item
                                Wise Profit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">From Date</label>
                        <input type="date" name="from_date" id="from_date"
                            value="{{ $filters['from_date'] ?? '' }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">To Date</label>
                        <input type="date" name="to_date" id="to_date" value="{{ $filters['to_date'] ?? '' }}"
                            class="w-full border rounded px-3 py-2">
                        <p class="hidden text-xs text-red-500 mt-1" data-date-error-for="to_date">To Date must be on
                            or after From Date.</p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">View Type<span
                                class="text-red-500">*</span></label>
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
            </div>
            <div class="flex justify-center">
                <button type="submit"
                    class="flex items-center gap-2 bg-[#ab5f00] hover:bg-[#8a4c00] text-white px-6 py-2 rounded-md font-semibold transition">
                    Generate
                </button>
            </div>
        </form>

        @if ($resultHeadings)
            @include('admin.reports.result_table', ['headings' => $resultHeadings, 'rows' => $resultRows, 'title' => $resultTitle])
        @endif
    </div>
</x-layouts.app>
<script>
    $(document).ready(function() {
        // Keep "To Date" from going earlier than "From Date".
        const fromEl = document.getElementById('from_date');
        const toEl = document.getElementById('to_date');

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

        function datesValid() {
            return !(fromEl.value && toEl.value && toEl.value < fromEl.value);
        }

        fromEl.addEventListener('change', syncMin);
        toEl.addEventListener('change', function() {
            const errorEl = document.querySelector('[data-date-error-for="to_date"]');
            if (errorEl) errorEl.classList.toggle('hidden', datesValid());
        });
        syncMin();

        $(document).on("submit", "#reportForm", function(e) {
            let report_type = $("#report_type").val();
            let view_type = $("#view_type").val();

            if (!report_type) {
                e.preventDefault();
                showToast("Please Select Report!", "error", 2000);
            }
            if (!view_type) {
                e.preventDefault();
                showToast("Please Select View Type!", "error", 2000);
            }
            if (!datesValid()) {
                e.preventDefault();
                showToast("To Date must be on or after From Date!", "error", 2000);
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
