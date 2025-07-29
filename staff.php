<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKB Staff POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .pos-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
        .order-item:last-child { border-bottom: none; }
        .category-tab.active { border-bottom-color: #3b82f6; color: #3b82f6; font-weight: 600; }
        .modal { transition: opacity 0.25s ease; }
        .modal-content { transition: transform 0.25s ease; }
        .color-swatch { width: 40px; height: 40px; border-radius: 50%; cursor: pointer; border: 3px solid transparent; transition: border-color 0.2s ease; }
        .color-swatch.selected { border-color: #3b82f6; }
        .order-card-new { animation: flash-green 1s ease-out; }
        @keyframes flash-green {
            0% { background-color: #d1fae5; }
            100% { background-color: #f9fafb; } /* Tailwind gray-50 */
        }
    </style>
</head>
<body class="bg-gray-200 h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white shadow-md w-full flex-shrink-0">
        <div class="px-4 py-2 flex justify-between items-center">
            <div class="flex items-center">
                <img src="https://placehold.co/40x40/dc2626/FFFFFF?text=KKB" alt="Logo" class="rounded-full mr-3">
                <h1 class="text-xl font-bold text-gray-800">KKB POS</h1>
            </div>
            <div class="flex items-center">
                <button id="history-btn" class="text-gray-600 hover:text-blue-600 mr-4 text-xl" title="Order History"><i class="fas fa-history"></i></button>
                <button id="settings-btn" class="text-gray-600 hover:text-blue-600 mr-4 text-xl" title="Settings"><i class="fas fa-cog"></i></button>
                <span class="font-semibold mr-4" id="current-time"></span>
                <span class="font-semibold">Staff: Jane Doe</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-grow flex flex-col md:flex-row overflow-hidden">
        <!-- Order Sidebar -->
        <aside class="w-full md:w-1/3 bg-white flex flex-col p-4 shadow-lg">
            <div class="border-b pb-2 mb-4"><h2 class="text-lg font-bold">Current Order</h2></div>
            <div id="order-items" class="flex-grow overflow-y-auto"><p class="text-gray-500 text-center mt-10">No items in order.</p></div>
            <div class="flex-shrink-0 pt-4 border-t">
                <div class="flex justify-between font-bold text-xl mb-4"><span>Total</span><span id="total">RM0.00</span></div>
                <button id="pay-btn" class="bg-green-500 text-white font-bold py-3 rounded-lg w-full hover:bg-green-600">PAY</button>
            </div>
        </aside>

        <!-- Menu & Orders -->
        <main class="w-full md:w-2/3 flex flex-col bg-gray-100">
            <div class="flex-shrink-0 bg-white border-b border-gray-200"><div id="category-tabs" class="flex space-x-4 px-4 overflow-x-auto"></div></div>
            <div class="flex-grow p-4 overflow-y-auto"><div id="menu-grid" class="pos-grid grid gap-3"></div></div>
            <div class="flex-shrink-0 h-1/3 bg-white p-4 border-t-2 border-gray-300 overflow-hidden flex flex-col">
                <h3 class="text-lg font-bold mb-2">Incoming Orders</h3>
                <div id="orders-queue" class="flex-grow overflow-y-auto space-y-2"><p class="text-gray-500 text-center mt-4">No new online orders.</p></div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <div id="pos-item-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50"><div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md transform scale-95 max-h-[90vh] flex flex-col"></div></div>
    <div id="settings-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-md transform scale-95">
             <div class="p-4 border-b flex justify-between items-center"><h3 class="text-xl font-semibold">Settings</h3><button id="close-settings-btn" class="text-2xl">&times;</button></div>
            <div class="p-4 space-y-4">
                <h4 class="font-bold">Menu Button Color</h4>
                <div id="color-swatches" class="flex items-center space-x-3">
                    <div class="color-swatch bg-white border-gray-300" data-bg="bg-white" data-text="text-gray-800"></div>
                    <div class="color-swatch bg-blue-500" data-bg="bg-blue-500" data-text="text-white"></div>
                    <div class="color-swatch bg-red-500" data-bg="bg-red-500" data-text="text-white"></div>
                    <div class="color-swatch bg-gray-800" data-bg="bg-gray-800" data-text="text-white"></div>
                </div>
            </div>
        </div>
    </div>
    <div id="history-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="modal-content bg-white rounded-lg shadow-xl w-full max-w-4xl transform scale-95 max-h-[90vh] flex flex-col">
            <div class="p-4 border-b flex justify-between items-center"><h3 class="text-xl font-semibold">Order History</h3><button id="close-history-btn" class="text-2xl">&times;</button></div>
            <div id="history-content" class="p-4 overflow-y-auto"></div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- DOM ELEMENTS ---
    const historyBtn = document.getElementById('history-btn');
    const historyModal = document.getElementById('history-modal');
    const menuGrid = document.getElementById('menu-grid');
    const categoryTabsContainer = document.getElementById('category-tabs');
    const posItemModal = document.getElementById('pos-item-modal');
    const settingsModal = document.getElementById('settings-modal');
    const settingsBtn = document.getElementById('settings-btn');
    const orderItemsContainer = document.getElementById('order-items');
    const totalEl = document.getElementById('total');
    const payBtn = document.getElementById('pay-btn');
    const ordersQueueContainer = document.getElementById('orders-queue');
    const timeEl = document.getElementById('current-time');
    const colorSwatchesContainer = document.getElementById('color-swatches');

    // --- APP STATE ---
    let menuItemsCache = [];
    let categoriesCache = [];
    let currentOrder = [];
    let currentItem = {};
    let posTheme = { bg: 'bg-white', text: 'text-gray-800' };
    let lastKnownOrderIds = new Set();
    let audioCtx; // To be initialized on first user interaction

    // --- INITIALIZATION ---
    async function initializePOS() {
        loadThemeSettings();
        await Promise.all([fetchCategories(), fetchMenuItems(), fetchOnlineOrders(true)]); // Initial fetch
        renderCategoryTabs();
        renderMenuGrid();
        setInterval(fetchOnlineOrders, 5000); // Refresh every 5 seconds
        updateTime();
        setInterval(updateTime, 1000);
    }

    // --- SOUND ALERT FUNCTION ---
    function playNotificationSound() {
        if (!audioCtx) return; // Audio context not ready
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // A nice, clear beep (A5 note)
        gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);
        oscillator.start(audioCtx.currentTime);
        oscillator.stop(audioCtx.currentTime + 0.5);
    }
    
    // Initialize Audio Context on first user interaction to comply with browser policies
    function initAudio() {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
    }
    document.body.addEventListener('click', initAudio, { once: true });


    // --- THEME & SETTINGS FUNCTIONS ---
    function loadThemeSettings() {
        const savedTheme = localStorage.getItem('posTheme');
        if (savedTheme) posTheme = JSON.parse(savedTheme);
        updateSelectedSwatch();
    }

    function saveThemeSettings(bg, text) {
        posTheme = { bg, text };
        localStorage.setItem('posTheme', JSON.stringify(posTheme));
        updateSelectedSwatch();
        renderMenuGrid();
    }
    
    function updateSelectedSwatch() {
        document.querySelectorAll('.color-swatch').forEach(swatch => {
            swatch.classList.remove('selected');
            if (swatch.dataset.bg === posTheme.bg) swatch.classList.add('selected');
        });
    }

    // --- DATA FETCHING & RENDERING ---
    async function fetchCategories() {
        try {
            const response = await fetch('api/menu.php?action=get-categories');
            categoriesCache = await response.json();
        } catch (error) { console.error("Error fetching categories:", error); }
    }

    async function fetchMenuItems() {
        try {
            const response = await fetch('api/menu.php?action=get-items');
            menuItemsCache = await response.json();
        } catch (error) { console.error("Error fetching menu items:", error); }
    }

    async function fetchOnlineOrders(isInitialLoad = false) {
        try {
            const response = await fetch('api/menu.php?action=get-online-orders');
            const onlineOrders = await response.json();
            
            // Check for new orders
            const incomingOrderIds = new Set(onlineOrders.map(o => o.id));
            if (!isInitialLoad && incomingOrderIds.size > lastKnownOrderIds.size) {
                const newOrderFound = [...incomingOrderIds].some(id => !lastKnownOrderIds.has(id));
                if (newOrderFound) {
                    playNotificationSound();
                }
            }
            lastKnownOrderIds = incomingOrderIds;

            renderOrderQueue(onlineOrders);
        } catch (error) { console.error("Error fetching online orders:", error); }
    }
    
    function renderCategoryTabs() {
        const categoryNames = ["All", ...categoriesCache.map(c => c.name)];
        categoryTabsContainer.innerHTML = categoryNames.map((name, index) => `<button class="category-tab py-3 px-2 text-sm text-gray-500 border-b-2 border-transparent ${index === 0 ? 'active' : ''}" data-category-name="${name}">${name}</button>`).join('');
    }

    function renderMenuGrid() {
        const activeTab = categoryTabsContainer.querySelector('.active');
        if (!activeTab || menuItemsCache.length === 0) {
            menuGrid.innerHTML = '<p class="text-gray-500">Select a category to see menu items.</p>';
            return;
        }
        const selectedCategoryName = activeTab.dataset.categoryName;
        const selectedCategory = categoriesCache.find(c => c.name === selectedCategoryName);
        const itemsToRender = selectedCategoryName === "All" ? menuItemsCache : menuItemsCache.filter(item => item.category_id == selectedCategory.id);
        if (itemsToRender.length === 0) {
            menuGrid.innerHTML = '<p class="text-gray-500">No items in this category.</p>';
            return;
        }
        menuGrid.innerHTML = itemsToRender.map(item => {
            const price = item.type === 'food' ? item.priceDisplay : `RM${parseFloat(item.basePrice).toFixed(2)}`;
            return `<button class="menu-item-btn ${posTheme.bg} ${posTheme.text} font-semibold p-3 rounded-lg shadow hover:opacity-80 flex flex-col justify-between h-28 text-left" data-item-id="${item.id}"><span>${item.name}</span><span class="text-right text-lg font-bold">${price}</span></button>`;
        }).join('');
    }
    
    function renderOrderQueue(orders) {
        if (orders.length === 0) {
            ordersQueueContainer.innerHTML = `<p class="text-gray-500 text-center mt-4">No new online orders.</p>`;
            return;
        }
        ordersQueueContainer.innerHTML = orders.map(order => `
            <div class="bg-gray-100 p-3 rounded-lg flex justify-between items-center shadow-sm">
                <div><p class="font-bold">Order #${order.id} (Online)</p><p class="text-sm text-gray-600">${order.items_summary || 'No items summary'}</p></div>
                <button class="complete-order-btn bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-bold" data-id="${order.id}">Done</button>
            </div>`).join('');
    }

    async function fetchAndShowHistory() {
        toggleModal(historyModal, true);
        const historyContent = document.getElementById('history-content');
        historyContent.innerHTML = `<div class="text-center p-8"><i class="fas fa-spinner fa-spin text-3xl"></i></div>`;
        try {
            const response = await fetch('api/menu.php?action=get-order-history');
            const orders = await response.json();
            renderHistory(orders);
        } catch (error) {
            console.error(error);
            historyContent.innerHTML = `<p class="text-red-500 p-8 text-center">Could not load order history.</p>`;
        }
    }

    function renderHistory(orders) {
        const historyContent = document.getElementById('history-content');
        if (orders.length === 0) {
            historyContent.innerHTML = `<p class="text-center text-gray-500">No completed orders found.</p>`;
            return;
        }
        historyContent.innerHTML = `
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th></tr></thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${orders.map(order => `<tr><td class="px-6 py-4 whitespace-nowrap text-sm font-medium">#${order.id}</td><td class="px-6 py-4 whitespace-nowrap text-sm">${order.order_type}</td><td class="px-6 py-4 whitespace-nowrap text-sm">${order.items_summary}</td><td class="px-6 py-4 whitespace-nowrap text-sm font-bold">RM${parseFloat(order.total_amount).toFixed(2)}</td><td class="px-6 py-4 whitespace-nowrap text-sm">${new Date(order.created_at).toLocaleString()}</td></tr>`).join('')}
                </tbody>
            </table>`;
    }

    // --- MODAL & ORDER FUNCTIONS ---
    async function fetchAndShowItemDetails(itemId) {
        toggleModal(posItemModal, true);
        const modalContent = posItemModal.querySelector('.modal-content');
        modalContent.innerHTML = `<div class="text-center p-8"><i class="fas fa-spinner fa-spin text-3xl"></i></div>`;
        try {
            const response = await fetch(`api/menu.php?action=get-item-details&id=${itemId}`);
            const data = await response.json();
            openItemModal(data);
        } catch (error) {
            console.error(error);
            modalContent.innerHTML = `<p class="text-red-500 p-8 text-center">Could not load item options.</p>`;
        }
    }
    
    function openItemModal(data) {
        currentItem = data;
        const modalContent = posItemModal.querySelector('.modal-content');
        const customizationsHTML = data.customizations.map(cat => {
            const inputType = cat.selection_type === 'single' ? 'radio' : 'checkbox';
            const optionsHTML = cat.options.map((opt, index) => `
                <label class="flex justify-between items-center p-3 border rounded-lg">
                    <span>${opt.name}</span>
                    <div class="flex items-center space-x-3">
                        <span class="font-semibold">+RM${parseFloat(opt.price_adjustment).toFixed(2)}</span>
                        <input type="${inputType}" name="cat_${cat.id}" value="${opt.name}" data-price="${opt.price_adjustment}" class="form-${inputType} h-5 w-5 border-gray-300 text-blue-600 focus:ring-blue-500" ${index === 0 && inputType === 'radio' ? 'checked' : ''}>
                    </div>
                </label>`).join('');
            return `<div class="space-y-2"><h4 class="font-bold text-lg">${cat.name}</h4><div class="space-y-2">${optionsHTML}</div></div>`;
        }).join('');

        modalContent.innerHTML = `
            <div class="p-6 border-b"><h3 class="text-xl font-semibold">${data.details.name}</h3></div>
            <form id="pos-item-form" class="p-6 space-y-6 overflow-y-auto">${customizationsHTML}<div class="space-y-2"><h4 class="font-bold text-lg">Comments</h4><textarea id="item-comment" class="w-full border-gray-300 rounded-lg p-2" placeholder="Special requests..."></textarea></div></form>
            <div class="p-6 mt-auto border-t flex justify-between items-center bg-gray-50 rounded-b-lg">
                <p id="modal-total-price" class="text-2xl font-bold">RM0.00</p>
                <button id="add-item-to-order-btn" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg">Add to Order</button>
            </div>`;
        updateModalPrice();
    }

    function updateModalPrice() {
        const form = document.getElementById('pos-item-form');
        if (!form) return;
        const priceEl = document.getElementById('modal-total-price');
        let basePrice = parseFloat(currentItem.details.basePrice);
        let addonsPrice = 0;
        form.querySelectorAll('input:checked').forEach(input => { addonsPrice += parseFloat(input.dataset.price); });
        priceEl.textContent = `RM${(basePrice + addonsPrice).toFixed(2)}`;
    }
    
    function addToCurrentOrder() {
        const form = document.getElementById('pos-item-form');
        if (!form) return;
        let finalPrice = parseFloat(currentItem.details.basePrice);
        const customizations = [];
        form.querySelectorAll('input:checked').forEach(input => {
            const price = parseFloat(input.dataset.price);
            finalPrice += price;
            if (input.value.toLowerCase().includes('(standard)') === false) customizations.push(input.value);
        });
        currentOrder.push({
            id: currentItem.details.id, name: currentItem.details.name, quantity: 1,
            finalPrice: finalPrice, customizations: customizations,
            comment: form.querySelector('#item-comment').value
        });
        renderCurrentOrder();
        toggleModal(posItemModal, false);
    }
    
    function renderCurrentOrder() {
        if (currentOrder.length === 0) {
            orderItemsContainer.innerHTML = `<p class="text-gray-500 text-center mt-10">No items in order.</p>`;
        } else {
            orderItemsContainer.innerHTML = currentOrder.map((orderItem, index) => `
                <div class="order-item py-2 border-b">
                    <div class="flex justify-between items-center"><p class="font-semibold">${orderItem.name}</p><p class="font-bold">RM${orderItem.finalPrice.toFixed(2)}</p></div>
                    <div class="pl-4 text-xs text-gray-500">
                        ${orderItem.customizations.map(c => `<div>+ ${c}</div>`).join('')}
                        ${orderItem.comment ? `<div><em>"${orderItem.comment}"</em></div>` : ''}
                    </div>
                </div>`).join('');
        }
        const total = currentOrder.reduce((sum, item) => sum + item.finalPrice, 0);
        totalEl.textContent = `RM${total.toFixed(2)}`;
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
    
    function updateTime() {
        if(timeEl) timeEl.textContent = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    // --- EVENT LISTENERS ---
    historyBtn.addEventListener('click', fetchAndShowHistory);
    document.getElementById('close-history-btn').addEventListener('click', () => toggleModal(historyModal, false));
    historyModal.addEventListener('click', (e) => { if(e.target === historyModal) toggleModal(historyModal, false); });

    ordersQueueContainer.addEventListener('click', async (e) => {
        const button = e.target.closest('.complete-order-btn');
        if (button) {
            const orderId = button.dataset.id;
            const response = await fetch('api/menu.php?action=complete-order', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: orderId })
            });
            const result = await response.json();
            if (result.success) fetchOnlineOrders();
            else alert('Error completing order.');
        }
    });

    settingsBtn.addEventListener('click', () => toggleModal(settingsModal, true));
    document.getElementById('close-settings-btn').addEventListener('click', () => toggleModal(settingsModal, false));
    settingsModal.addEventListener('click', e => { if (e.target === settingsModal) toggleModal(settingsModal, false); });
    
    colorSwatchesContainer.addEventListener('click', e => {
        const swatch = e.target.closest('.color-swatch');
        if (swatch) saveThemeSettings(swatch.dataset.bg, swatch.dataset.text);
    });

    categoryTabsContainer.addEventListener('click', e => {
        if (e.target.classList.contains('category-tab')) {
            categoryTabsContainer.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            renderMenuGrid();
        }
    });

    menuGrid.addEventListener('click', e => {
        const button = e.target.closest('.menu-item-btn');
        if (button) fetchAndShowItemDetails(button.dataset.itemId);
    });

    posItemModal.addEventListener('click', e => {
        if (e.target.matches('input')) updateModalPrice();
        if (e.target.id === 'add-item-to-order-btn') addToCurrentOrder();
        if (e.target === posItemModal) toggleModal(posItemModal, false);
    });

    payBtn.addEventListener('click', async () => {
        if (currentOrder.length === 0) { alert('No items in order.'); return; }
        const total = currentOrder.reduce((sum, item) => sum + item.finalPrice, 0);
        const orderData = { items: currentOrder, total: total };
        const response = await fetch('api/menu.php?action=place-walkin-order', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        const result = await response.json();
        if (result.success) {
            alert(`Walk-in order #${result.orderId} processed!`);
            currentOrder = [];
            renderCurrentOrder();
        } else {
            alert('Payment failed. ' + (result.message || ''));
        }
    });

    // --- START THE APP ---
    initializePOS();
});
</script>

</body>
</html>
