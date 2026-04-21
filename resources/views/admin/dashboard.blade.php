<x-layouts.app>
    <div class="row mb-4">
        <div class="col-md-3">
            <select id="month_filter"
                class="form-control border-orange-400 focus:ring-[#FF6A00] border rounded-lg p-2 shadow">
                <option value="">All Months</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                @endfor
            </select>
        </div>
    </div>
    <h1 class="text-lg font-semibold">
        Grocery <i class="fa-solid fa-location-dot text-[#38b000]"></i> - <span
            class="text-[#38b000]">{{ $grocerry_location->name ?? '' }}</span>
    </h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-gray-500 text-sm">Total Products</p>
            <h2 class="text-3xl font-bold text-[#804300]" id="total_products"></h2>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-gray-500 text-sm">Total Orders</p>
            <h2 class="text-3xl font-bold text-[#804300]" id="total_orders"></h2>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-gray-500 text-sm">Total Users</p>
            <h2 class="text-3xl font-bold text-[#804300]" id="total_users"></h2>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-gray-500 text-sm">Ordered Price</p>
            <h2 class="text-3xl font-bold text-[#804300]" id="ordered_price"></h2>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-gray-500 text-sm">Total Profit</p>
            <h2 class="text-3xl font-bold text-[#804300]" id="total_profit"></h2>
        </div>
    </div>
    <br>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card shadow-lg rounded-xl">
            <div class="card-header bg-[#936639] text-black font-semibold p-2">
                Category Wise Sales
            </div>
            <div class="card-body p-4">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        <div class="card shadow-lg rounded-xl">
            <div class="card-header bg-[#a68a64] text-black font-semibold p-2">
                Order Status Wise
            </div>
            <div>
                <canvas id="orderChart" width="300" height="300"></canvas>
            </div>
        </div>
        {{-- <div class="bg-white rounded-xl shadow-md">
            <div class="border-b px-4 py-3 font-semibold text-gray-700 flex items-center justify-between">
                <span>Product Stock Summary</span>
                <i class="fa-solid fa-chart-line text-indigo-500"></i>
            </div>
            <div class="p-6 space-y-6">
                <!-- TOTAL PRODUCTS -->
                <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-gray-500 text-sm">Total Products Stock</p>
                        <h2 class="text-2xl font-bold text-gray-800" id="products_stocks_count">0</h2>
                    </div>

                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fa-solid fa-box text-indigo-600 text-lg"></i>
                    </div>
                </div>
                <!-- SOLD PERCENT -->
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm text-gray-600">Sold Percentage</span>
                        <span class="text-sm font-semibold text-green-600" id="sold_percent">0%</span>
                    </div>

                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div id="sold_progress" class="bg-green-500 h-3 rounded-full transition-all duration-700"
                            style="width:0%">
                        </div>
                    </div>
                </div>
                <!-- REMAINING STOCK -->
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm text-gray-600">Remaining Stock</span>
                        <span class="text-sm font-semibold text-blue-600" id="remaining_percent">0%</span>
                    </div>

                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div id="remaining_progress" class="bg-blue-500 h-3 rounded-full transition-all duration-700"
                            style="width:0%">
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        <div class="card shadow-lg rounded-xl">
            <div class="card-header bg-[#7f4f24] text-black font-semibold p-2">
                Monthly Revenue
            </div>
            <div class="card-body p-4">
                <canvas id="revenueChart" width="300" height="300"></canvas>
            </div>
        </div>
    </div>
    <h1 class="text-lg mt-2">Milk</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-2">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-gray-500 text-sm">Milk User</p>
            <h2 class="text-3xl font-bold text-[#804300]" id="active_users"></h2>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <p class="text-gray-500 text-sm">Delivery Partner</p>
            <h2 class="text-3xl font-bold text-[#804300]" id="delivery_partner"></h2>
        </div>
        @foreach ($milk_hubs as $hub)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
                <p class="text-gray-500 text-sm">Hub Name</p>
                <h2 class="text-xl font-bold text-[#804300]">{{ $hub->name }}</h2>
                <p class="text-gray-500 text-sm mt-2">Address</p>
                <p class="text-sm">{{ $hub->address ?? '' }}</p>
            </div>
        @endforeach
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
        <div class="card shadow-lg rounded-xl">
            <div class="card-header bg-[#7f4f24] text-black font-semibold p-2">
                Subscription Plans
            </div>
            <div class="card-body p-4">
                <div class="overflow-x-auto">
                    <table class="w-full border border-gray-300 text-center text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2">S.No</th>
                                <th class="border border-gray-300 px-4 py-2">Plan Name</th>
                                <th class="border border-gray-300 px-4 py-2">Per Day Amount</th>
                                <th class="border border-gray-300 px-4 py-2">Duration</th>
                                <th class="border border-gray-300 px-4 py-2">Total Subscribers</th>
                            </tr>
                        </thead>
                        <tbody id="subscription_table">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card shadow-lg rounded-xl">
            <div class="card-header bg-[#936639] text-black font-semibold p-2">
                Ticket Status Wise
            </div>
            <div class="card-body p-4">
                <canvas id="ticketChart" width="300" height="300"></canvas>
            </div>
        </div>
    </div>
</x-layouts.app>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let categoryChart;
    let ticketChart;
    let orderChart;
    let revenueChart;

    function loadDashboard(month = '') {
        $.ajax({
            url: "dashboard-data",
            type: "GET",
            data: {
                month: month
            },
            success: function(res) {
                $("#total_products").text(res.total_products);
                $("#total_orders").text(res.total_orders);
                $("#total_users").text(res.total_users);
                $("#active_users").text(res.active_users);
                $("#ordered_price").text(res.ordered_amount);
                $("#total_profit").text(res.total_profit);
                $("#delivery_partner").text(res.delivery_partner);
                if (categoryChart) {
                    categoryChart.destroy();
                }
                categoryChart = new Chart(
                    document.getElementById('categoryChart'), {
                        type: 'bar',
                        data: {
                            labels: res.category_labels,
                            datasets: [{
                                label: 'Sales',
                                data: res.category_data,
                                backgroundColor: "#c29979",
                                borderColor: "#8a6205",
                                borderWidth: 1,
                                borderRadius: 6
                            }]
                        },
                        options: {
                            plugins: {
                                legend: {
                                    labels: {
                                        color: "#333"
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    }
                );
                if (ticketChart) {
                    ticketChart.destroy();
                }
                ticketChart = new Chart(
                    document.getElementById('ticketChart'), {
                        type: 'doughnut',
                        data: {
                            labels: res.ticket_labels,
                            datasets: [{
                                label: "Tickets",
                                data: res.ticket_data,
                                backgroundColor: [
                                    "#b08968",
                                    "#ede0d4",
                                    "#b08968"
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            cutout: "65%"
                        }
                    }
                );
                if (orderChart) {
                    orderChart.destroy();
                }
                orderChart = new Chart(
                    document.getElementById('orderChart'), {
                        type: 'pie',
                        data: {
                            labels: res.order_labels,
                            datasets: [{
                                data: res.order_data,
                                backgroundColor: [
                                    "#38b000",
                                    "#007200",
                                    "#ccff33",
                                    "#006400",
                                    "#004b23"
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    }
                );
                if (revenueChart) {
                    revenueChart.destroy();
                }
                const months = [
                    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                ];
                let revenueData = new Array(12).fill(0);
                for (const month in res.monthlyRevenue) {
                    revenueData[month - 1] = res.monthlyRevenue[month];
                }
                revenueChart = new Chart(
                    document.getElementById('revenueChart'), {
                        type: 'line',
                        data: {
                            labels: months,
                            datasets: [{
                                label: 'Revenue',
                                data: revenueData,
                                borderColor: '#4CAF50',
                                backgroundColor: 'rgba(76,175,80,0.2)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                let table = "";

                res.subscriptionPlan.forEach((plan, index) => {
                    console.log(plan);
                    table += `
<tr>
    <td class="border border-gray-300 px-4 py-2">${index + 1}</td>
    <td class="border border-gray-300 px-4 py-2 font-semibold text-[#6f4518]">${plan.name}</td>
    <td class="border border-gray-300 px-4 py-2">₹ ${plan.amount}</td>
    <td class="border border-gray-300 px-4 py-2">${plan.duration} Months</td>
    <td class="border border-gray-300 px-4 py-2">${plan.users}</td>
</tr>
`;

                });

                $("#subscription_table").html(table);

            }
        });
    }

    loadDashboard();

    $("#month_filter").change(function() {
        let month = $(this).val();
        loadDashboard(month);
    });
</script>
