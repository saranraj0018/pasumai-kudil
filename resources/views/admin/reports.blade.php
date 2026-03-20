<x-layouts.app>
    <div class="mx-auto mt-2 bg-white shadow p-6 rounded">
        <h2 class="text-xl font-bold mb-6">Download Report</h2>
        <form method="POST" action="{{ route('export_report') }}" id="reportForm">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium">From Date</label>
                    <input type="date" name="from_date" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">To Date</label>
                    <input type="date" name="to_date" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">User</label>
                    <select name="user_id" class="w-full border rounded px-3 py-2 choice-select">
                        <option value="">All Users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Type</label>
                    <select name="type" id="type" class="w-full border rounded px-3 py-2 choice-select">
                        <option value="">Select</option>
                        <option value="grocery">Grocery</option>
                        <option value="milk">Milk</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-center">
                <button type="submit" class="bg-[#ab5f00] text-white px-6 py-2 rounded hover:bg-[#ab6f00]">
                    Download Report
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
<script>
    $(document).ready(function() {
        $(document).on("submit", "#reportForm", function(e) {
            let type = $("#type").val();
            if(type == "" || type == undefined)
            {
                e.preventDefault();
                showToast("Please Select Type field!", "error", 2000);
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
