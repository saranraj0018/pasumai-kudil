<x-layouts.app>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">

    <style>
        .gd-wrap {
            --cream: #FAF6EE;
            --ink: #33261B;
            --ink-soft: #7A6A58;
            --grocery: #2F6B1F;
            --grocery-bright: #48A128;
            --dairy: #8A6238;
            --dairy-light: #C9A66B;
            --accent: #E8590C;
            --card: #FFFFFF;
            --border: #E7DFD0;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
        }

        .gd-wrap h1, .gd-wrap h2, .gd-wrap .gd-serif {
            font-family: 'Fraunces', serif;
        }

        .gd-wrap .gd-mono {
            font-family: 'IBM Plex Mono', monospace;
        }

        /* ---- Top ticket bar ---- */
        .gd-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            background: var(--ink);
            border-radius: 14px;
            padding: 1.1rem 1.5rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }
        .gd-topbar::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 14px 14px;
            pointer-events: none;
        }
        .gd-topbar .gd-title {
            color: #FAF6EE;
            font-size: 1.4rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        .gd-topbar .gd-title .gd-loc {
            color: var(--grocery-bright);
            font-weight: 500;
        }
        .gd-filters {
            display: flex;
            gap: 0.6rem;
            position: relative;
            z-index: 1;
        }
        .gd-select {
            position: relative;
        }
        .gd-select select {
            appearance: none;
            background: #FAF6EE;
            color: var(--ink);
            border: none;
            border-radius: 999px;
            padding: 0.55rem 2.2rem 0.55rem 1.1rem;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 2px 0 rgba(0,0,0,0.15);
            cursor: pointer;
            background-image: linear-gradient(45deg, transparent 50%, var(--dairy) 50%), linear-gradient(135deg, var(--dairy) 50%, transparent 50%);
            background-position: calc(100% - 16px) center, calc(100% - 11px) center;
            background-size: 5px 5px, 5px 5px;
            background-repeat: no-repeat;
        }

        /* ---- Shelf edge divider: signature motif ---- */
        .gd-shelf-edge {
            height: 10px;
            background-image: linear-gradient(135deg, var(--edge-color, var(--dairy)) 25%, transparent 25%),
                               linear-gradient(225deg, var(--edge-color, var(--dairy)) 25%, transparent 25%);
            background-size: 14px 14px;
            background-position: 0 0;
            border-radius: 10px 10px 0 0;
        }

        /* ---- Stat tags ---- */
        .gd-tags {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.9rem;
        }
        @media (min-width: 768px) {
            .gd-tags { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        }
        .gd-tag {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.1rem 1.1rem 1rem;
            position: relative;
        }
        .gd-tag::before {
            content: "";
            position: absolute;
            top: 12px;
            right: 12px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            border: 2px solid var(--tag-color, var(--grocery));
        }
        .gd-tag .gd-tab {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #fff;
            background: var(--tag-color, var(--grocery));
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            margin-bottom: 0.6rem;
        }
        .gd-tag .gd-label {
            color: var(--ink-soft);
            font-size: 0.8rem;
            margin-bottom: 0.15rem;
        }
        .gd-tag .gd-value {
            font-size: 1.7rem;
            font-weight: 600;
            color: var(--ink);
            line-height: 1.15;
            min-height: 1.9rem;
        }
        .gd-tag .gd-value.is-loading {
            color: transparent;
            background: linear-gradient(90deg, #EFE8D9 25%, #F7F1E4 37%, #EFE8D9 63%);
            background-size: 400% 100%;
            animation: gd-shimmer 1.4s ease infinite;
            border-radius: 6px;
        }
        @keyframes gd-shimmer {
            0% { background-position: 100% 50%; }
            100% { background-position: 0 50%; }
        }

        /* ---- Chart cards ---- */
        .gd-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        .gd-card-head {
            padding: 0.85rem 1.1rem;
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 0.98rem;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .gd-card-head .gd-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--dot-color, var(--grocery));
            display: inline-block;
        }
        .gd-card-body { padding: 1.1rem; }

        /* ---- Section heading ---- */
        .gd-section-title {
            display: flex;
            align-items: baseline;
            gap: 0.6rem;
            margin: 2.2rem 0 1rem;
        }
        .gd-section-title h2 {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--ink);
            margin: 0;
        }
        .gd-section-title .gd-rule {
            flex: 1;
            height: 1px;
            background: repeating-linear-gradient(90deg, var(--border) 0 6px, transparent 6px 10px);
        }

        .contents { display: contents; }

        .gd-empty {
            text-align: center;
            color: var(--ink-soft);
            font-style: italic;
        }

        /* ---- Hub tag cards ---- */
        .gd-hub {
            background: var(--cream);
            border: 1px dashed var(--dairy-light);
            border-radius: 10px;
            padding: 1.1rem;
            position: relative;
        }
        .gd-hub::before {
            content: "";
            position: absolute;
            top: -6px;
            left: 18px;
            width: 12px;
            height: 12px;
            background: #fff;
            border: 1px dashed var(--dairy-light);
            border-radius: 50%;
        }

        /* ---- Receipt-style subscription table ---- */
        .gd-receipt {
            width: 100%;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 0.8rem;
            border-collapse: collapse;
        }
        .gd-receipt thead th {
            text-align: left;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 0.68rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--ink-soft);
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--ink);
        }
        .gd-receipt tbody tr td {
            padding: 0.55rem 0.4rem;
            border-bottom: 1px dashed var(--border);
        }
        .gd-receipt tbody tr:last-child td { border-bottom: none; }
        .gd-receipt tbody tr:hover { background: var(--cream); }
        .gd-receipt .gd-plan-name {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            color: var(--dairy);
        }
        .gd-empty-row td {
            text-align: center;
            padding: 1.5rem 0.4rem;
            color: var(--ink-soft);
            font-family: 'Inter', sans-serif;
            font-style: italic;
        }
    </style>

    <div class="gd-wrap">

        <div class="gd-topbar">
            <h1 class="gd-title">
                Grocery <span class="gd-loc">{{ $grocerry_location->name ?? 'Overview' }}</span>
            </h1>
            <div class="gd-filters">
                <div class="gd-select">
                    <select id="month_filter">
                        <option value="">All Months</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="gd-select">
                    <select id="year_filter">
                        <option value="">All Years</option>
                        @php
                            $gdStartYear = $available_years->min() ?? now()->year;
                            $gdEndYear = $available_years->max() ?? now()->year;
                        @endphp
                        @for ($y = $gdEndYear; $y >= $gdStartYear; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>

        <!-- Stat tags -->
        <div class="gd-tags">
            <div class="gd-tag" style="--tag-color: var(--grocery);">
                <span class="gd-tab">Catalog</span>
                <p class="gd-label">Total Products</p>
                <h2 class="gd-value is-loading" id="total_products">&nbsp;</h2>
            </div>
            <div class="gd-tag" style="--tag-color: var(--accent);">
                <span class="gd-tab">Orders</span>
                <p class="gd-label">Total Orders</p>
                <h2 class="gd-value is-loading" id="total_orders">&nbsp;</h2>
            </div>
            <div class="gd-tag" style="--tag-color: var(--dairy);">
                <span class="gd-tab">People</span>
                <p class="gd-label">Total Users</p>
                <h2 class="gd-value is-loading" id="total_users">&nbsp;</h2>
            </div>
            <div class="gd-tag" style="--tag-color: var(--grocery-bright);">
                <span class="gd-tab">Revenue</span>
                <p class="gd-label">Ordered Price</p>
                <h2 class="gd-value is-loading" id="ordered_price">&nbsp;</h2>
            </div>
            <div class="gd-tag" style="--tag-color: #B3261E;">
                <span class="gd-tab">Margin</span>
                <p class="gd-label">Total Profit</p>
                <h2 class="gd-value is-loading" id="total_profit">&nbsp;</h2>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
            <div class="gd-card">
                <div class="gd-shelf-edge" style="--edge-color: var(--grocery);"></div>
                <div class="gd-card-head"><span class="gd-dot" style="--dot-color: var(--grocery);"></span>Category Wise Sales</div>
                <div class="gd-card-body"><canvas id="categoryChart"></canvas></div>
            </div>
            <div class="gd-card">
                <div class="gd-shelf-edge" style="--edge-color: var(--grocery-bright);"></div>
                <div class="gd-card-head"><span class="gd-dot" style="--dot-color: var(--grocery-bright);"></span>Order Status Wise</div>
                <div class="gd-card-body" style="height: 300px;"><canvas id="orderChart"></canvas></div>
            </div>
            <div class="gd-card md:col-span-2">
                <div class="gd-shelf-edge" style="--edge-color: var(--dairy);"></div>
                <div class="gd-card-head"><span class="gd-dot" style="--dot-color: var(--dairy);"></span>Monthly Revenue</div>
                <div class="gd-card-body" style="height: 300px;"><canvas id="revenueChart"></canvas></div>
            </div>
        </div>

        <!-- Milk section -->
        <div class="gd-section-title">
            <h2 class="gd-serif">Milk</h2>
            <span class="gd-rule"></span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div class="gd-tag" style="--tag-color: var(--dairy-light);">
                <span class="gd-tab">Milk</span>
                <p class="gd-label">Milk Users</p>
                <h2 class="gd-value is-loading" id="active_users">&nbsp;</h2>
            </div>
            <div class="gd-tag" style="--tag-color: var(--accent);">
                <span class="gd-tab">Fleet</span>
                <p class="gd-label">Delivery Partner</p>
                <h2 class="gd-value is-loading" id="delivery_partner">&nbsp;</h2>
            </div>
            <div id="milk_hubs_container" class="contents"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6 mb-8">
            <div class="gd-card">
                <div class="gd-shelf-edge" style="--edge-color: var(--dairy);"></div>
                <div class="gd-card-head"><span class="gd-dot" style="--dot-color: var(--dairy);"></span>Subscription Plans</div>
                <div class="gd-card-body">
                    <div class="overflow-x-auto">
                        <table class="gd-receipt">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Plan</th>
                                    <th>Per Day</th>
                                    <th>Duration</th>
                                    <th>Subscribers</th>
                                </tr>
                            </thead>
                            <tbody id="subscription_table">
                                <tr class="gd-empty-row"><td colspan="5">Loading plans…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="gd-card">
                <div class="gd-shelf-edge" style="--edge-color: var(--accent);"></div>
                <div class="gd-card-head"><span class="gd-dot" style="--dot-color: var(--accent);"></span>Ticket Status Wise</div>
                <div class="gd-card-body" style="height: 300px;"><canvas id="ticketChart"></canvas></div>
            </div>
        </div>

    </div>

</x-layouts.app>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let categoryChart, ticketChart, orderChart, revenueChart;

    const gdInr = (n) => '₹ ' + Number(n || 0).toLocaleString('en-IN', { maximumFractionDigits: 2 });
    const gdNum = (n) => Number(n || 0).toLocaleString('en-IN');

    function gdSetLoading(on) {
        document.querySelectorAll('.gd-value').forEach(el => {
            if (on) el.classList.add('is-loading');
            else el.classList.remove('is-loading');
        });
    }

    const gdFont = { family: 'Inter', size: 12 };

    function loadDashboard(month = '', year = '') {
        gdSetLoading(true);

        $.ajax({
            url: "dashboard-data",
            type: "GET",
            data: { month: month, year: year },
            success: function (res) {
                $("#total_products").text(gdNum(res.total_products));
                $("#total_orders").text(gdNum(res.total_orders));
                $("#total_users").text(gdNum(res.total_users));
                $("#active_users").text(gdNum(res.active_users));
                $("#ordered_price").text(gdInr(res.ordered_amount));
                $("#total_profit").text(gdInr(res.total_profit));
                $("#delivery_partner").text(gdNum(res.delivery_partner));
                gdSetLoading(false);

                // Category Wise Sales
                if (categoryChart) categoryChart.destroy();
                categoryChart = new Chart(document.getElementById('categoryChart'), {
                    type: 'bar',
                    data: {
                        labels: res.category_labels,
                        datasets: [{
                            label: 'Sales',
                            data: res.category_data,
                            backgroundColor: '#48A128',
                            borderColor: '#2F6B1F',
                            borderWidth: 1,
                            borderRadius: 6,
                            maxBarThickness: 42
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { font: gdFont }, grid: { color: '#EFE8D9' } },
                            x: { ticks: { font: gdFont }, grid: { display: false } }
                        }
                    }
                });

                // Ticket Status Wise
                if (ticketChart) ticketChart.destroy();
                ticketChart = new Chart(document.getElementById('ticketChart'), {
                    type: 'doughnut',
                    data: {
                        labels: res.ticket_labels,
                        datasets: [{
                            label: 'Tickets',
                            data: res.ticket_data,
                            backgroundColor: ['#E8590C', '#C9A66B', '#8A6238', '#33261B'],
                            borderWidth: 2,
                            borderColor: '#FFFFFF'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { font: gdFont, usePointStyle: true } } },
                        cutout: '65%'
                    }
                });

                // Order Status Wise
                if (orderChart) orderChart.destroy();
                orderChart = new Chart(document.getElementById('orderChart'), {
                    type: 'pie',
                    data: {
                        labels: res.order_labels,
                        datasets: [{
                            data: res.order_data,
                            backgroundColor: ['#38b000', '#48A128', '#2F6B1F', '#8A6238', '#E8590C', '#B3261E'],
                            borderWidth: 2,
                            borderColor: '#FFFFFF'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { font: gdFont, usePointStyle: true } } }
                    }
                });

                // Monthly Revenue
                if (revenueChart) revenueChart.destroy();
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                let revenueData = new Array(12).fill(0);
                for (const m in res.monthlyRevenue) {
                    revenueData[m - 1] = res.monthlyRevenue[m];
                }
                revenueChart = new Chart(document.getElementById('revenueChart'), {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Revenue',
                            data: revenueData,
                            borderColor: '#8A6238',
                            backgroundColor: 'rgba(138,98,56,0.12)',
                            pointBackgroundColor: '#8A6238',
                            fill: true,
                            tension: 0.35
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { font: gdFont, callback: (v) => gdInr(v) }, grid: { color: '#EFE8D9' } },
                            x: { ticks: { font: gdFont }, grid: { display: false } }
                        }
                    }
                });

                // Subscription plans (receipt table)
                let table = "";
                if (res.subscriptionPlan && res.subscriptionPlan.length) {
                    res.subscriptionPlan.forEach((plan, index) => {
                        table += `
<tr>
    <td>${index + 1}</td>
    <td class="gd-plan-name">${plan.name}</td>
    <td>${gdInr(plan.amount)}</td>
    <td>${plan.duration_unit ? plan.duration + ' ' + plan.duration_unit : plan.duration}</td>
    <td>${gdNum(plan.users)}</td>
</tr>`;
                    });
                } else {
                    table = `<tr class="gd-empty-row"><td colspan="5">No subscription plans for this period.</td></tr>`;
                }
                $("#subscription_table").html(table);

                // Milk hubs (filtered by month/year now, so render dynamically)
                let hubsHtml = "";
                if (res.milk_hubs && res.milk_hubs.length) {
                    res.milk_hubs.forEach((hub) => {
                        hubsHtml += `
<div class="gd-hub">
    <p class="gd-label" style="margin-bottom:0.1rem;">Hub</p>
    <h2 class="gd-serif" style="font-size:1.05rem;font-weight:600;color:var(--dairy);margin:0;">${hub.name}</h2>
    <p class="gd-label" style="margin-top:0.6rem;">Address</p>
    <p style="font-size:0.85rem;">${hub.address || '—'}</p>
</div>`;
                    });
                } else {
                    hubsHtml = `<div class="gd-hub gd-empty md:col-span-2">No milk hubs for this period.</div>`;
                }
                $("#milk_hubs_container").html(hubsHtml);
            },
            error: function () {
                gdSetLoading(false);
            }
        });
    }

    loadDashboard();

    $("#month_filter, #year_filter").change(function () {
        loadDashboard($("#month_filter").val(), $("#year_filter").val());
    });
</script>
