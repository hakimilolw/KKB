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
        .modal { transition: opacity 0.25s ease; }
        .modal-content { transition: transform 0.25s ease; }
        #sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="relative min-h-screen md:flex">
        <!-- Mobile Menu Button -->
        <div class="md:hidden flex justify-between items-center bg-white p-4 shadow-md">
            <h1 class="text-xl font-bold text-gray-800">KKB Admin</h1>
            <button id="mobile-menu-button" class="text-gray-800 focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>

        <!-- Sidebar -->
        <aside id="sidebar" class="bg-white w-64 shadow-md absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 z-30">
            <div class="p-4 border-b hidden md:block"><h1 class="text-xl font-bold text-gray-800">KKB Admin</h1></div>
            <nav class="mt-4">
                <a href="#" id="nav-dashboard" class="sidebar-link active flex items-center px-4 py-3 text-gray-700 hover:bg-gray-200"><i class="fas fa-chart-line w-6"></i><span>Dashboard</span></a>
                <a href="#" id="nav-menu" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-200"><i class="fas fa-book-open w-6"></i><span>Menu Management</span></a>
                <a href="#" id="nav-addons" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-200"><i class="fas fa-puzzle-piece w-6"></i><span>Add-ons</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4"><h2 class="text-2xl font-semibold text-gray-800" id="page-title">Sales Dashboard</h2></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                
                <!-- Dashboard View -->
                <div id="dashboard-view">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white p-5 rounded-lg shadow"><h4 class="text-gray-500">Today's Sales</h4><p class="text-3xl font-bold" id="sales-today">RM0.00</p></div>
                        <div class="bg-white p-5 rounded-lg shadow"><h4 class="text-gray-500">This Week's Sales</h4><p class="text-3xl font-bold" id="sales-week">RM0.00</p></div>
                        <div class="bg-white p-5 rounded-lg shadow"><h4 class="text-gray-500">This Month's Sales</h4><p class="text-3xl font-bold" id="sales-month">RM0.00</p></div>
                        <div class="bg-white p-5 rounded-lg shadow"><h4 class="text-gray-500">This Year's Sales</h4><p class="text-3xl font-bold" id="sales-year">RM0.00</p></div>
                    </div>
                </div>

                <!-- Menu Management View -->
                <div id="menu-view" class="hidden">
                    <div class="flex justify-end mb-4"><button id="add-item-btn" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Add New Menu Item</button></div>
                    <div class="bg-white shadow-md rounded-lg overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th><th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th></tr></thead><tbody id="menu-table-body" class="bg-white divide-y divide-gray-200"></tbody></table></div>
                </div>

                <!-- Add-on Management View -->
                <div id="addons-view" class="hidden">
                    <div class="flex justify-end mb-4"><button id="add-addon-category-btn" class="bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700"><i class="fas fa-plus mr-2"></i>Add New Category</button></div>
                    <div id="addon-categories-container" class="space-y-6"></div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Item Modal -->
    <div id="item-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-lg p-6 transform scale-95 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center border-b pb-3 flex-shrink-0"><h3 class="text-xl font-semibold text-gray-800" id="modal-title">Add New Item</h3><button id="close-modal-btn" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button></div>
            <form id="item-form" class="mt-4 space-y-4 overflow-y-auto flex-grow pr-2">
                <input type="hidden" id="item-id" name="item-id">
                <input type="hidden" id="existing-image-url" name="existing-image-url">
                
                <div><label for="item-name" class="block text-sm font-medium text-gray-700">Item Name</label><input type="text" id="item-name" name="item-name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></div>
                <div><label for="item-category" class="block text-sm font-medium text-gray-700">Category</label><select id="item-category" name="item-category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></select></div>
                <div><label for="item-subcategory" class="block text-sm font-medium text-gray-700">Subcategory (Optional)</label><input type="text" id="item-subcategory" name="item-subcategory" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                <div><label for="item-type" class="block text-sm font-medium text-gray-700">Item Type</label><select id="item-type" name="item-type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required><option value="drink">Drink (with sizes)</option><option value="hot-drink">Hot Drink (no sizes)</option><option value="food">Food Item</option></select></div>
                <div id="base-price-group"><label for="item-base-price" class="block text-sm font-medium text-gray-700">Base Price (e.g., 12.00)</label><input type="number" step="0.01" id="item-base-price" name="item-base-price" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                <div id="price-display-group" class="hidden"><label for="item-price-display" class="block text-sm font-medium text-gray-700">Price Display (e.g., RM8.50)</label><input type="text" id="item-price-display" name="item-price-display" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></div>
                
                <div><label for="item-image" class="block text-sm font-medium text-gray-700">Item Image</label><input type="file" id="item-image" name="item-image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"><img id="image-preview" src="" class="mt-2 h-20 rounded-md hidden"></div>
                
                <div class="border-t pt-4"><h4 class="font-semibold text-lg mb-2">Link Add-on Categories</h4><div id="item-addons-container" class="space-y-2 max-h-40 overflow-y-auto border p-2 rounded-md"></div></div>
            </form>
            <div class="flex justify-end pt-4 border-t mt-auto flex-shrink-0"><button type="button" id="cancel-btn" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancel</button><button type="submit" form="item-form" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Save Item</button></div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- DOM ELEMENTS ---
    const sidebar = document.getElementById('sidebar');
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const navDashboard = document.getElementById('nav-dashboard');
    const navMenu = document.getElementById('nav-menu');
    const navAddons = document.getElementById('nav-addons');
    const dashboardView = document.getElementById('dashboard-view');
    const menuView = document.getElementById('menu-view');
    const addonsView = document.getElementById('addons-view');
    const pageTitle = document.getElementById('page-title');
    const menuTableBody = document.getElementById('menu-table-body');
    const itemModal = document.getElementById('item-modal');
    const itemForm = document.getElementById('item-form');
    const categorySelect = document.getElementById('item-category');
    const typeSelect = document.getElementById('item-type');
    const basePriceGroup = document.getElementById('base-price-group');
    const priceDisplayGroup = document.getElementById('price-display-group');
    const modalTitle = document.getElementById('modal-title');
    const addonCategoriesContainer = document.getElementById('addon-categories-container');
    const itemAddonsContainer = document.getElementById('item-addons-container');
    const imagePreview = document.getElementById('image-preview');

    let categoriesCache = [];
    let allAddonsCache = [];

    // --- INITIALIZATION ---
    async function initializeApp() {
        await Promise.all([fetchSalesData(), fetchCategories(), fetchMenuItems(), fetchAllAddons()]);
    }
    
    // --- DATA FETCHING & RENDERING ---
    async function fetchSalesData() {
        try {
            const response = await fetch('api/menu.php?action=get-sales-summary');
            const result = await response.json();
            if (result.success) renderSalesDashboard(result.data);
        } catch (error) { console.error('Error fetching sales data:', error); }
    }

    function renderSalesDashboard(sales) {
        const formatCurrency = (num) => `RM${parseFloat(num).toFixed(2)}`;
        document.getElementById('sales-today').textContent = formatCurrency(sales.today);
        document.getElementById('sales-week').textContent = formatCurrency(sales.week);
        document.getElementById('sales-month').textContent = formatCurrency(sales.month);
        document.getElementById('sales-year').textContent = formatCurrency(sales.year);
    }
    
    async function fetchCategories() {
        try {
            const response = await fetch('api/menu.php?action=get-categories');
            categoriesCache = await response.json();
            categorySelect.innerHTML = categoriesCache.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        } catch (error) { console.error('Error fetching categories:', error); }
    }

    async function fetchMenuItems() {
        menuTableBody.innerHTML = `<tr><td colspan="4" class="text-center p-6 text-gray-500">Loading...</td></tr>`;
        try {
            const response = await fetch('api/menu.php?action=get-items');
            const items = await response.json();
            renderMenuTable(items);
        } catch (error) {
            console.error('Error fetching menu items:', error);
            menuTableBody.innerHTML = `<tr><td colspan="4" class="text-center p-6 text-red-500">Could not load menu.</td></tr>`;
        }
    }
    
    async function fetchAllAddons() {
        try {
            const response = await fetch('api/menu.php?action=get-all-addons');
            allAddonsCache = await response.json();
            renderAddonManagementView();
        } catch(error) { console.error("Error fetching all addons:", error); }
    }

    function renderMenuTable(items) {
        menuTableBody.innerHTML = '';
        if (items.length === 0) {
             menuTableBody.innerHTML = `<tr><td colspan="4" class="text-center p-6 text-gray-500">No menu items found. Click 'Add New Menu Item' to begin.</td></tr>`;
             return;
        }
        items.forEach(item => {
            const price = item.type === 'food' ? (item.priceDisplay || 'N/A') : `RM${parseFloat(item.basePrice).toFixed(2)}`;
            const row = document.createElement('tr');
            row.dataset.item = JSON.stringify(item); 
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.category_name || 'N/A'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${price}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="edit-btn text-blue-600 hover:text-blue-900" data-id="${item.id}">Edit</button>
                    <button class="delete-btn text-red-600 hover:text-red-900 ml-4" data-id="${item.id}">Delete</button>
                </td>`;
            menuTableBody.appendChild(row);
        });
    }

    function renderAddonManagementView() {
        addonCategoriesContainer.innerHTML = '';
        allAddonsCache.forEach(cat => {
            const optionsHTML = cat.options.map(opt => `<li class="flex justify-between items-center p-2"><span>${opt.name} (+RM${parseFloat(opt.price_adjustment).toFixed(2)})</span></li>`).join('');
            const categoryCard = document.createElement('div');
            categoryCard.className = 'bg-white p-4 rounded-lg shadow';
            categoryCard.innerHTML = `
                <div class="flex justify-between items-center border-b pb-2 mb-2">
                    <h3 class="text-lg font-bold">${cat.name} <span class="text-sm font-normal text-gray-500">(${cat.selection_type})</span></h3>
                </div>
                <ul class="divide-y">${optionsHTML.length > 0 ? optionsHTML : '<li class="p-2 text-gray-500">No options added.</li>'}</ul>`;
            addonCategoriesContainer.appendChild(categoryCard);
        });
    }

    // --- MODAL & FORM LOGIC ---
    async function openItemModal(item = null) {
        itemForm.reset();
        document.getElementById('item-id').value = item ? item.id : '';
        document.getElementById('existing-image-url').value = item ? item.imageUrl : '';
        modalTitle.textContent = item ? 'Edit Item' : 'Add New Item';
        imagePreview.classList.add('hidden');
        
        if (item) {
            document.getElementById('item-name').value = item.name;
            document.getElementById('item-category').value = item.category_id;
            document.getElementById('item-subcategory').value = item.subcategory || '';
            document.getElementById('item-type').value = item.type;
            if (item.imageUrl) {
                imagePreview.src = '../' + item.imageUrl; // Adjust path for display
                imagePreview.classList.remove('hidden');
            }
            if (item.type === 'food') {
                document.getElementById('item-price-display').value = item.priceDisplay;
            } else {
                document.getElementById('item-base-price').value = item.basePrice;
            }
        }

        itemAddonsContainer.innerHTML = allAddonsCache.map(cat => `
            <label class="flex items-center space-x-2"><input type="checkbox" name="addons[]" value="${cat.id}" class="rounded"><span>${cat.name}</span></label>`).join('');

        if (item) {
            try {
                const response = await fetch(`api/menu.php?action=get-item-addons&id=${item.id}`);
                const linkedAddonIds = await response.json();
                linkedAddonIds.forEach(id => {
                    const checkbox = itemAddonsContainer.querySelector(`input[value="${id}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            } catch (error) { console.error("Could not fetch item addons:", error); }
        }
        
        togglePriceFields();
        toggleModal(itemModal, true);
    }

    function togglePriceFields() {
        const selectedType = typeSelect.value;
        basePriceGroup.classList.toggle('hidden', selectedType === 'food');
        priceDisplayGroup.classList.toggle('hidden', selectedType !== 'food');
    }

    function toggleModal(modal, show) {
        const content = modal.querySelector('.modal-content');
        if (show) {
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.remove('opacity-0'); content.classList.remove('scale-95'); }, 10);
        } else {
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 250);
        }
    }

    // --- UI NAVIGATION ---
    function showView(view) {
        dashboardView.classList.add('hidden');
        menuView.classList.add('hidden');
        addonsView.classList.add('hidden');
        navDashboard.classList.remove('active');
        navMenu.classList.remove('active');
        navAddons.classList.remove('active');

        if (view === 'dashboard') {
            dashboardView.classList.remove('hidden');
            navDashboard.classList.add('active');
            pageTitle.textContent = 'Sales Dashboard';
        } else if (view === 'menu') {
            menuView.classList.remove('hidden');
            navMenu.classList.add('active');
            pageTitle.textContent = 'Menu Management';
        } else if (view === 'addons') {
            addonsView.classList.remove('hidden');
            navAddons.classList.add('active');
            pageTitle.textContent = 'Add-on Management';
        }
        
        // Auto-hide sidebar on mobile after navigation
        if (window.innerWidth < 768) {
            sidebar.classList.add('-translate-x-full');
        }
    }

    // --- EVENT LISTENERS ---
    mobileMenuButton.addEventListener('click', () => sidebar.classList.toggle('-translate-x-full'));
    navDashboard.addEventListener('click', (e) => { e.preventDefault(); showView('dashboard'); });
    navMenu.addEventListener('click', (e) => { e.preventDefault(); showView('menu'); });
    navAddons.addEventListener('click', (e) => { e.preventDefault(); showView('addons'); });
    
    menuTableBody.addEventListener('click', async (e) => {
        if (e.target.classList.contains('edit-btn')) {
            const row = e.target.closest('tr');
            const itemData = JSON.parse(row.dataset.item);
            openItemModal(itemData);
        }
        if (e.target.classList.contains('delete-btn')) {
            const id = e.target.dataset.id;
            const name = e.target.closest('tr').cells[0].textContent;
            if (confirm(`Are you sure you want to delete ${name}?`)) {
                const response = await fetch('api/menu.php?action=delete-item', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id })
                });
                const result = await response.json();
                if (result.success) fetchMenuItems();
                else alert('Error deleting item.');
            }
        }
    });
    
    itemForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(itemForm);
        const url = formData.get('item-id') ? `api/menu.php?action=update-item` : `api/menu.php?action=add-item`;
        try {
            const response = await fetch(url, { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) {
                toggleModal(itemModal, false);
                await fetchMenuItems();
            } else {
                alert('Failed to save item: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Form submission error:', error);
            alert('An error occurred while saving.');
        }
    });
        
    document.getElementById('add-item-btn').addEventListener('click', () => openItemModal());
    document.getElementById('close-modal-btn').addEventListener('click', () => toggleModal(itemModal, false));
    document.getElementById('cancel-btn').addEventListener('click', () => toggleModal(itemModal, false));
    typeSelect.addEventListener('change', togglePriceFields);
    
    initializeApp();
});
</script>

</body>
</html>
