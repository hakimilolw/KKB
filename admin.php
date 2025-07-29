<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKB Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link.active { background-color: #e5e7eb; font-weight: 600; }
        .stat-card { transition: transform 0.2s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .modal { transition: opacity 0.25s ease; }
        .modal-content { transition: transform 0.25s ease; }
        #sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="relative min-h-screen md:flex">
        <aside id="sidebar" class="bg-white w-64 shadow-md absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 z-30">
            <div class="p-4 border-b">
                <h1 class="text-xl font-bold text-gray-800">KKB Admin</h1>
            </div>
            <nav class="mt-4">
                <a href="#" id="nav-dashboard" class="sidebar-link active flex items-center px-4 py-3 text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-chart-line w-6"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" id="nav-menu" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-book-open w-6"></i>
                    <span>Menu Management</span>
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-2xl font-semibold text-gray-800" id="page-title">Sales Dashboard</h2>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                
                <div id="dashboard-view">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="stat-card bg-white p-5 rounded-lg shadow">
                            <h4 class="text-gray-500">Today's Sales</h4>
                            <p class="text-3xl font-bold" id="sales-today">RM0.00</p>
                        </div>
                        <div class="stat-card bg-white p-5 rounded-lg shadow">
                            <h4 class="text-gray-500">This Week's Sales</h4>
                            <p class="text-3xl font-bold" id="sales-week">RM0.00</p>
                        </div>
                        <div class="stat-card bg-white p-5 rounded-lg shadow">
                            <h4 class="text-gray-500">This Month's Sales</h4>
                            <p class="text-3xl font-bold" id="sales-month">RM0.00</p>
                        </div>
                        <div class="stat-card bg-white p-5 rounded-lg shadow">
                            <h4 class="text-gray-500">This Year's Sales</h4>
                            <p class="text-3xl font-bold" id="sales-year">RM0.00</p>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="font-semibold mb-4">Sales Overview</h3>
                        <div class="bg-gray-200 h-64 flex items-center justify-center rounded">
                            <p class="text-gray-500">Sales chart would be displayed here.</p>
                        </div>
                    </div>
                </div>

                <div id="menu-view" class="hidden">
                     </div>
            </main>
        </div>
    </div>

    <div id="item-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
         </div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // --- All existing DOM element selectors remain the same ---
    const navDashboard = document.getElementById('nav-dashboard');
    const navMenu = document.getElementById('nav-menu');
    // ... etc.

    // --- DATA FETCHING & RENDERING ---
    async function initializeApp() {
        await fetchSalesData();
        await fetchCategories();
        await fetchMenuItems();
    }
    
    async function fetchSalesData() {
        try {
            const response = await fetch('api/menu.php?action=get-sales-summary');
            const result = await response.json();
            if (result.success) {
                renderSalesDashboard(result.data);
            }
        } catch (error) {
            console.error('Error fetching sales data:', error);
        }
    }

    function renderSalesDashboard(sales) {
        // Helper to format numbers as currency
        const formatCurrency = (num) => `RM${parseFloat(num).toFixed(2)}`;

        document.getElementById('sales-today').textContent = formatCurrency(sales.today);
        document.getElementById('sales-week').textContent = formatCurrency(sales.week);
        document.getElementById('sales-month').textContent = formatCurrency(sales.month);
        document.getElementById('sales-year').textContent = formatCurrency(sales.year);
    }
    
    // --- All other functions for Menu Management remain the same ---
    // (fetchCategories, fetchMenuItems, renderMenuTable, openItemModal, etc.)
    

    // --- UI NAVIGATION ---
    function showView(view) {
        // This function remains the same as before
        document.getElementById('dashboard-view').classList.add('hidden');
        document.getElementById('menu-view').classList.add('hidden');
        navDashboard.classList.remove('active');
        navMenu.classList.remove('active');

        if (view === 'dashboard') {
            document.getElementById('dashboard-view').classList.remove('hidden');
            navDashboard.classList.add('active');
            document.getElementById('page-title').textContent = 'Sales Dashboard';
            fetchSalesData(); // Refresh sales data when switching to this view
        } else if (view === 'menu') {
            document.getElementById('menu-view').classList.remove('hidden');
            navMenu.classList.add('active');
            document.getElementById('page-title').textContent = 'Menu Management';
        }
    }

    // --- EVENT LISTENERS ---
    navDashboard.addEventListener('click', (e) => { e.preventDefault(); showView('dashboard'); });
    navMenu.addEventListener('click', (e) => { e.preventDefault(); showView('menu'); });
    // All other event listeners for the menu management section remain the same

    // --- INITIALIZE APP ---
    initializeApp();
});
</script>

</body>
</html>