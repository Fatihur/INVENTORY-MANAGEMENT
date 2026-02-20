<x-layouts.app>
<div>
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">
                <i class="fas fa-tachometer-alt" style="color: #3498db;"></i> Dashboard Overview
            </h2>
            <p style="font-size: 12px; color: #7f8c8d; margin: 0;">
                Welcome back, {{ auth()->user()->name }}! Here's what's happening today.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="fetchDashboardData()" class="btn btn-default" title="Refresh Data">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
        <div class="panel stat-card" style="cursor: pointer;">
            <div class="panel-body" style="text-align: center; padding: 20px;">
                <div style="font-size: 32px; margin-bottom: 8px;">
                    <i class="fas fa-box" style="color: #3498db;"></i>
                </div>
                <div style="font-size: 24px; font-weight: bold;" id="totalProducts">
                    <div class="skeleton" style="height: 24px; width: 60px; margin: 0 auto;"></div>
                </div>
                <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">
                    <i class="fas fa-cube"></i> Total Products
                </div>
            </div>
        </div>
        <div class="panel stat-card" style="cursor: pointer;">
            <div class="panel-body" style="text-align: center; padding: 20px;">
                <div style="font-size: 32px; margin-bottom: 8px;">
                    <i class="fas fa-users" style="color: #27ae60;"></i>
                </div>
                <div style="font-size: 24px; font-weight: bold; color: #27ae60;" id="totalCustomers">
                    <div class="skeleton" style="height: 24px; width: 60px; margin: 0 auto;"></div>
                </div>
                <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">
                    <i class="fas fa-user-check"></i> Total Customers
                </div>
            </div>
        </div>
        <div class="panel stat-card" style="cursor: pointer;">
            <div class="panel-body" style="text-align: center; padding: 20px;">
                <div style="font-size: 32px; margin-bottom: 8px;">
                    <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i>
                </div>
                <div style="font-size: 24px; font-weight: bold; color: #f39c12;" id="lowStockProducts">
                    <div class="skeleton" style="height: 24px; width: 60px; margin: 0 auto;"></div>
                </div>
                <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">
                    <i class="fas fa-layer-group"></i> Low Stock Items
                </div>
            </div>
        </div>
        <div class="panel stat-card" style="cursor: pointer;">
            <div class="panel-body" style="text-align: center; padding: 20px;">
                <div style="font-size: 32px; margin-bottom: 8px;">
                    <i class="fas fa-clock" style="color: #e74c3c;"></i>
                </div>
                <div style="font-size: 24px; font-weight: bold; color: #e74c3c;" id="expiringBatches">
                    <div class="skeleton" style="height: 24px; width: 60px; margin: 0 auto;"></div>
                </div>
                <div style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">
                    <i class="fas fa-calendar-times"></i> Expiring Soon
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="panel" style="margin-bottom: 20px;">
        <div class="panel-header">
            <i class="fas fa-bolt" style="color: #f39c12;"></i> Quick Actions
        </div>
        <div class="panel-body">
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                @can('sales-orders.create')
                <a href="{{ route('sales-orders.index') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Go to Sales Orders
                </a>
                @endcan
                @can('purchase-orders.create')
                <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-file-invoice"></i> New Purchase Order
                </a>
                @endcan
                @can('products.create')
                <a href="{{ route('products.create') }}" class="btn btn-default">
                    <i class="fas fa-box"></i> Add Product
                </a>
                @endcan
                <a href="{{ route('reports.index') }}" class="btn btn-warning">
                    <i class="fas fa-chart-bar"></i> View Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
        <!-- Sales Trend Chart -->
        <div class="panel">
            <div class="panel-header">
                <span><i class="fas fa-chart-line" style="color: #3498db;"></i> Sales Trend - Last 30 Days</span>
                <span class="badge badge-info" id="salesLoading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </span>
            </div>
            <div class="panel-body">
                <div style="height: 250px;">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products Chart -->
        <div class="panel">
            <div class="panel-header">
                <span><i class="fas fa-trophy" style="color: #f39c12;"></i> Top Products - By Units Sold</span>
                <span class="badge badge-info" id="topLoading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </span>
            </div>
            <div class="panel-body">
                <div style="height: 250px;">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Levels Table -->
    <div class="panel" style="margin-bottom: 20px;">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fas fa-chart-bar" style="color: #9b59b6;"></i> Stock Levels Overview</span>
            <a href="{{ route('stock.index') }}" class="action-link">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div style="padding: 0; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-cube"></i> Product</th>
                        <th><i class="fas fa-barcode"></i> Code</th>
                        <th class="text-right"><i class="fas fa-warehouse"></i> Total Qty</th>
                        <th class="text-right"><i class="fas fa-minus-circle"></i> Min Stock</th>
                        <th><i class="fas fa-info-circle"></i> Status</th>
                    </tr>
                </thead>
                <tbody id="stockLevelsBody">
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #7f8c8d;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Loading stock levels...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="panel">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fas fa-shopping-cart" style="color: #27ae60;"></i> Recent Orders - Latest Transactions</span>
            <a href="{{ route('sales-orders.index') }}" class="action-link">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div style="padding: 0; overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> Order Number</th>
                        <th><i class="fas fa-user"></i> Customer</th>
                        <th><i class="fas fa-calendar"></i> Date</th>
                        <th class="text-right"><i class="fas fa-money-bill"></i> Total</th>
                        <th><i class="fas fa-flag"></i> Status</th>
                    </tr>
                </thead>
                <tbody id="recentOrdersBody">
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #7f8c8d;">
                            <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Loading recent orders...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Fetch Dashboard Data
    async function fetchDashboardData() {
        try {
            // Show loading states
            document.getElementById('salesLoading').style.display = 'inline-block';
            document.getElementById('topLoading').style.display = 'inline-block';

            const statsRes = await fetch('/api/v1/dashboard/statistics');
            const stats = await statsRes.json();
            if (stats.success) {
                document.getElementById('totalProducts').textContent = stats.data.total_products.toLocaleString();
                document.getElementById('totalCustomers').textContent = stats.data.total_customers.toLocaleString();
                document.getElementById('lowStockProducts').textContent = stats.data.low_stock_products.toLocaleString();
                document.getElementById('expiringBatches').textContent = stats.data.expiring_batches.toLocaleString();
            }

            const trendRes = await fetch('/api/v1/dashboard/sales-trend?days=30');
            const trend = await trendRes.json();
            if (trend.success) {
                renderSalesTrendChart(trend.data);
            }

            const topRes = await fetch('/api/v1/dashboard/top-products?limit=10');
            const top = await topRes.json();
            if (top.success) {
                renderTopProductsChart(top.data);
            }

            const stockRes = await fetch('/api/v1/dashboard/stock-levels');
            const stock = await stockRes.json();
            if (stock.success) {
                renderStockLevels(stock.data);
            }

            const ordersRes = await fetch('/api/v1/dashboard/recent-orders');
            const orders = await ordersRes.json();
            if (orders.success) {
                renderRecentOrders(orders.data);
            }
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
            showToast('Failed to load dashboard data', 'error');
        } finally {
            // Hide loading states
            document.getElementById('salesLoading').style.display = 'none';
            document.getElementById('topLoading').style.display = 'none';
        }
    }

    // Render Sales Trend Chart
    let salesTrendChart;
    function renderSalesTrendChart(data) {
        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        if (salesTrendChart) {
            salesTrendChart.destroy();
        }

        const labels = data.map(item => new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        const values = data.map(item => parseFloat(item.total));

        const gradient = ctx.createLinearGradient(0, 0, 0, 250);
        gradient.addColorStop(0, 'rgba(52, 152, 219, 0.3)');
        gradient.addColorStop(1, 'rgba(52, 152, 219, 0.0)');

        salesTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales',
                    data: values,
                    borderColor: '#3498db',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#3498db',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            font: { size: 10 },
                            callback: function(value) {
                                return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            }
                        }
                    }
                }
            }
        });
    }

    // Render Top Products Chart
    let topProductsChart;
    function renderTopProductsChart(data) {
        const ctx = document.getElementById('topProductsChart').getContext('2d');
        if (topProductsChart) {
            topProductsChart.destroy();
        }

        const labels = data.map(item => item.name.length > 20 ? item.name.substring(0, 20) + '...' : item.name);
        const values = data.map(item => item.total_sold);

        topProductsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Units Sold',
                    data: values,
                    backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e', '#7f8c8d', '#27ae60'],
                    borderRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    // Render Stock Levels
    function renderStockLevels(data) {
        const tbody = document.getElementById('stockLevelsBody');
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-box-open"></i></div>
                        <p>No stock data available</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.slice(0, 10).map(item => {
            let badgeClass = 'badge-success';
            let statusText = '<i class="fas fa-check-circle"></i> In Stock';
            let iconColor = '#27ae60';

            if (item.total_qty == 0) {
                badgeClass = 'badge-danger';
                statusText = '<i class="fas fa-times-circle"></i> Out of Stock';
                iconColor = '#e74c3c';
            } else if (item.total_qty <= item.min_stock) {
                badgeClass = 'badge-warning';
                statusText = '<i class="fas fa-exclamation-triangle"></i> Low Stock';
                iconColor = '#f39c12';
            }

            return `
                <tr>
                    <td><strong>${item.name}</strong></td>
                    <td style="color: #7f8c8d; font-family: monospace;">${item.code}</td>
                    <td class="text-right" style="font-weight: bold; font-size: 13px;">${item.total_qty}</td>
                    <td class="text-right" style="color: #7f8c8d;">${item.min_stock}</td>
                    <td><span class="badge ${badgeClass}">${statusText}</span></td>
                </tr>
            `;
        }).join('');
    }

    // Render Recent Orders
    function renderRecentOrders(data) {
        const tbody = document.getElementById('recentOrdersBody');
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-shopping-basket"></i></div>
                        <p>No recent orders</p>
                    </td>
                </tr>
            `;
            return;
        }

        const statusBadges = {
            'draft': ['badge-warning', '<i class="fas fa-pencil-alt"></i> Draft'],
            'confirmed': ['badge-info', '<i class="fas fa-check"></i> Confirmed'],
            'processing': ['badge-warning', '<i class="fas fa-cog"></i> Processing'],
            'shipped': ['badge-info', '<i class="fas fa-shipping-fast"></i> Shipped'],
            'completed': ['badge-success', '<i class="fas fa-check-circle"></i> Completed'],
            'cancelled': ['badge-danger', '<i class="fas fa-times-circle"></i> Cancelled'],
        };

        tbody.innerHTML = data.slice(0, 10).map(order => {
            const [badgeClass, statusText] = statusBadges[order.status] || ['badge-warning', order.status];
            return `
                <tr>
                    <td><strong style="color: #3498db;">${order.so_number}</strong></td>
                    <td>${order.customer?.name || '-'}</td>
                    <td style="color: #7f8c8d;">${new Date(order.order_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                    <td class="text-right"><strong>Rp ${parseInt(order.total_amount).toLocaleString()}</strong></td>
                    <td><span class="badge ${badgeClass}">${statusText}</span></td>
                </tr>
            `;
        }).join('');
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        fetchDashboardData();
    });
</script>
</x-layouts.app>
