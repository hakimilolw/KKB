<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kopi Ka'au Bilem - Premium Coffee & More</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background-color: #1a1a1a; color: #f3f4f6; }
        .brand-red { color: #dc2626; }
        .sidebar-button { transition: all 0.3s ease; border-left: 4px solid transparent; }
        .sidebar-button.active { border-left-color: #dc2626; background-color: #2d2d2d; color: #f3f4f6; }
        .sidebar-button:not(.active) { color: #9ca3af; }
        .menu-section { scroll-margin-top: 90px; }
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; z-index: 40; }
        #cart-button-container { position: fixed; bottom: 65px; left: 0; right: 0; z-index: 30; }
        .modal { z-index: 50; transition: opacity 0.3s ease; overscroll-behavior: contain; }
        #item-modal { z-index: 50; } #cart-modal { z-index: 60; } #orders-modal { z-index: 80; }
        .modal-content { transition: transform 0.3s ease; max-height: 100vh; }
        .page { position: absolute; top: 0; left: 0; right: 0; bottom: 0; transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out; opacity: 1; }
        .page.hidden-right { transform: translateX(100%); opacity: 0; pointer-events: none; }
        .page.hidden-left { transform: translateX(-100%); opacity: 0; pointer-events: none; }
        .loyalty-cup.filled { color: #f59e0b; }
    </style>
</head>
<body class="antialiased">

    <div class="max-w-lg mx-auto min-h-screen relative overflow-x-hidden">
        <div id="menu-page" class="page bg-[#111] flex flex-col">
            <header class="sticky top-0 z-20 bg-[#111] p-4 flex items-center space-x-4 border-b border-gray-700/50 flex-shrink-0">
                <img src="https://placehold.co/50x50/dc2626/FFFFFF?text=KKB" alt="Kopi Ka'au Bilem Logo" class="w-12 h-12 rounded-full object-cover">
                <div><h1 class="text-xl font-bold text-white">Kopi Ka'au Bilem</h1><p class="text-sm text-gray-400">Coffee for all.</p></div>
            </header>
            <div class="flex flex-grow overflow-hidden">
                <aside class="w-1/4 bg-gray-900/50 p-2 overflow-y-auto"><nav id="sidebar-nav" class="space-y-2 sticky top-4"></nav></aside>
                <main id="menu-content" class="w-3/4 p-4 space-y-8 overflow-y-auto pb-32">
                    <div id="loading" class="text-center py-10"><i class="fas fa-spinner fa-spin text-4xl"></i><p class="mt-2">Loading Menu...</p></div>
                </main>
            </div>
        </div>
        
        <div id="account-page" class="page bg-[#111] hidden-right flex flex-col">
            <header class="p-4 flex-shrink-0 border-b border-gray-700 relative flex items-center justify-center"><h2 class="text-2xl font-bold">Account</h2></header>
            <div id="account-content" class="overflow-y-auto flex-grow p-4 space-y-6 pb-32">
                </div>
        </div>
    </div>

    <div id="cart-button-container" class="px-4 hidden">
        <button id="view-cart-btn" class="w-full max-w-lg mx-auto bg-red-700 text-white font-bold py-3 px-4 rounded-lg hover:bg-red-800 flex justify-between items-center shadow-lg"></button>
    </div>

    <nav class="bottom-nav bg-gray-900/80 backdrop-blur-sm border-t border-gray-700 h-[65px]">
        <div class="max-w-lg mx-auto h-full flex items-center justify-around text-gray-400">
            <a href="#" id="nav-menu" class="text-center brand-red"><i class="fas fa-mug-saucer text-2xl"></i><span class="block text-xs font-bold">Menu</span></a>
            <a href="#" id="nav-orders" class="text-center hover:brand-red"><i class="fas fa-receipt text-2xl"></i><span class="block text-xs">Orders</span></a>
            <a href="#" id="nav-account" class="text-center hover:brand-red"><i class="fas fa-user text-2xl"></i><span class="block text-xs">Account</span></a>
        </div>
    </nav>
    
    <div id="item-modal" class="modal fixed inset-0 bg-black bg-opacity-70 hidden flex items-end opacity-0">
        <div class="modal-content bg-gray-800 rounded-t-2xl w-full transform translate-y-full max-h-[90vh] flex flex-col">
            </div>
    </div>
    <div id="cart-modal" class="modal fixed inset-0 bg-black bg-opacity-70 hidden flex items-end opacity-0">
        <div class="modal-content bg-gray-800 rounded-t-2xl w-full p-4 transform translate-y-full">
            </div>
    </div>
    <div id="orders-modal" class="modal fixed inset-0 bg-black bg-opacity-70 hidden flex items-end opacity-0">
        <div class="modal-content bg-gray-800 rounded-t-2xl w-full p-4 transform translate-y-full max-h-[80vh] flex flex-col">
            <h3 class="text-xl font-bold mb-4 flex-shrink-0">Your Recent Orders</h3>
            <div id="orders-list" class="overflow-y-auto"></div>
        </div>
    </div>
    <div id="confirmation-modal" class="modal fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center opacity-0 z-[90]">
        <div class="modal-content bg-gray-800 rounded-lg p-6 text-center transform scale-95">
             <i class="fas fa-check-circle text-5xl text-green-500 mb-4"></i>
             <h3 class="text-2xl font-bold">Order Placed!</h3>
             <p class="text-gray-400">Your order is being prepared.</p>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- DOM ELEMENTS ---
    const sidebarNav = document.getElementById('sidebar-nav');
    const menuContent = document.getElementById('menu-content');
    const loadingIndicator = document.getElementById('loading');
    const itemModal = document.getElementById('item-modal');
    const cartModal = document.getElementById('cart-modal');
    const ordersModal = document.getElementById('orders-modal');
    const confirmationModal = document.getElementById('confirmation-modal');
    const cartButtonContainer = document.getElementById('cart-button-container');
    const viewCartBtn = document.getElementById('view-cart-btn');
    const menuPage = document.getElementById('menu-page');
    const accountPage = document.getElementById('account-page');
    const navMenu = document.getElementById('nav-menu');
    const navOrders = document.getElementById('nav-orders');
    const navAccount = document.getElementById('nav-account');

    // --- APP STATE ---
    let menuDataCache = [];
    let currentItem = {};
    let cart = [];

    // --- DATA FETCHING & RENDERING ---
    async function initializeMenu() {
        try {
            const response = await fetch('api/menu.php?action=get-full-menu');
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            menuDataCache = await response.json();
            renderSidebar(menuDataCache);
            renderMenu(menuDataCache);
            loadingIndicator.classList.add('hidden');
        } catch (error) {
            console.error("Failed to load menu:", error);
            loadingIndicator.innerHTML = `<p class="text-red-500">Could not load the menu.</p>`;
        }
    }

    function renderSidebar(categories) {
        sidebarNav.innerHTML = categories.map((cat, index) => `<a href="#category-${cat.slug}" class="sidebar-button block p-2 rounded-md font-semibold text-sm ${index === 0 ? 'active' : ''}">${cat.name}</a>`).join('');
    }

    function renderMenu(data) {
        menuContent.innerHTML = '';
        data.forEach(category => {
            if (category.items.length === 0) return;
            const section = document.createElement('section');
            section.id = `category-${category.slug}`;
            section.className = 'menu-section';
            
            const itemsHTML = category.items.map(item => {
                const price = item.type === 'food' ? (item.priceDisplay || 'N/A') : `RM${parseFloat(item.basePrice).toFixed(2)}`;
                return `
                    <div class="menu-item-div flex items-start space-x-4 cursor-pointer hover:bg-gray-800/50 p-2 rounded-lg" data-item-id="${item.id}">
                        <img src="${item.imageUrl}" alt="${item.name}" class="w-20 h-20 rounded-md object-cover flex-shrink-0">
                        <div class="flex-grow">
                            <h4 class="font-bold text-white">${item.name}</h4>
                            ${item.subcategory ? `<p class="text-xs text-gray-400">${item.subcategory}</p>` : ''}
                            <p class="font-semibold mt-2">${price}</p>
                        </div>
                    </div>`;
            }).join('');
            section.innerHTML = `<h3 class="text-2xl font-extrabold text-white mb-4">${category.name}</h3><div class="space-y-4">${itemsHTML}</div>`;
            menuContent.appendChild(section);
        });
    }

    async function fetchAndShowItemDetails(itemId) {
        const modalContent = itemModal.querySelector('.modal-content');
        toggleModal(itemModal, true);
        modalContent.innerHTML = `<div class="text-center p-8"><i class="fas fa-spinner fa-spin text-3xl"></i></div>`;
        try {
            const response = await fetch(`api/menu.php?action=get-item-details&id=${itemId}`);
            if (!response.ok) throw new Error('Item details not found');
            const data = await response.json();
            openItemModal(data);
        } catch (error) {
            console.error(error);
            modalContent.innerHTML = `<p class="text-red-500 p-8 text-center">Could not load item options.</p>`;
        }
    }

    function openItemModal(data) {
        currentItem = data;
        const modalContent = itemModal.querySelector('.modal-content');
        const customizationsHTML = data.customizations.map(cat => {
            const inputType = cat.selection_type === 'single' ? 'radio' : 'checkbox';
            const optionsHTML = cat.options.map((opt, index) => `
                <label class="flex justify-between items-center p-3 bg-gray-700 rounded-lg">
                    <span>${opt.name}</span>
                    <div class="flex items-center space-x-3">
                        <span class="font-semibold">+RM${parseFloat(opt.price_adjustment).toFixed(2)}</span>
                        <input type="${inputType}" name="cat_${cat.id}" value="${opt.name}" data-price="${opt.price_adjustment}" 
                               class="form-${inputType} h-5 w-5 bg-gray-900 border-gray-600 text-red-600 focus:ring-red-500"
                               ${index === 0 && inputType === 'radio' ? 'checked' : ''}>
                    </div>
                </label>`).join('');
            return `<div class="space-y-2"><h4 class="font-bold text-lg">${cat.name}</h4><div class="space-y-2">${optionsHTML}</div></div>`;
        }).join('');

        modalContent.innerHTML = `
            <div class="overflow-y-auto px-4 pt-4">
                <img src="${data.details.imageUrl}" class="w-full h-48 object-cover rounded-lg mb-4">
                <h3 class="text-2xl font-bold mb-4">${data.details.name}</h3>
                <form id="item-customization-form" class="space-y-6">${customizationsHTML}</form>
                <div class="mt-6 space-y-2">
                    <h4 class="font-bold text-lg">Comments</h4>
                    <textarea id="item-comment" class="w-full bg-gray-700 rounded-lg p-2 text-white border border-gray-600" placeholder="e.g., less sweet, no ice..."></textarea>
                </div>
            </div>
            <div class="sticky bottom-0 bg-gray-800 p-4 mt-4 border-t border-gray-700 flex justify-between items-center flex-shrink-0">
                <p id="modal-total-price" class="text-2xl font-bold">RM0.00</p>
                <button id="add-to-cart-btn" type="button" class="bg-red-600 text-white font-bold py-3 px-6 rounded-lg">Add to Cart</button>
            </div>`;
        updateModalPrice();
    }

    function updateModalPrice() {
        const form = document.getElementById('item-customization-form');
        if (!form) return;
        const priceEl = document.getElementById('modal-total-price');
        let basePrice = parseFloat(currentItem.details.basePrice);
        let addonsPrice = 0;
        form.querySelectorAll('input:checked').forEach(input => { addonsPrice += parseFloat(input.dataset.price); });
        priceEl.textContent = `RM${(basePrice + addonsPrice).toFixed(2)}`;
    }

    function addToCart() {
        const form = document.getElementById('item-customization-form');
        if (!form) return;
        let finalPrice = parseFloat(currentItem.details.basePrice);
        const customizations = [];
        form.querySelectorAll('input:checked').forEach(input => {
            const price = parseFloat(input.dataset.price);
            finalPrice += price;
            if (input.value.toLowerCase().includes('(standard)') === false) {
                customizations.push(input.value);
            }
        });
        const cartItem = {
            id: currentItem.details.id,
            name: currentItem.details.name,
            quantity: 1,
            finalPrice: finalPrice,
            customizations: customizations,
            comment: form.parentElement.querySelector('#item-comment').value
        };
        cart.push(cartItem);
        updateCartButton();
        toggleModal(itemModal, false);
    }
    
    function renderCart() {
        const modalContent = cartModal.querySelector('.modal-content');
        if (cart.length === 0) {
            modalContent.innerHTML = `<p class="text-center text-gray-400 py-8">Your cart is empty.</p>`;
        } else {
            const total = cart.reduce((sum, item) => sum + (item.finalPrice * item.quantity), 0);
            modalContent.innerHTML = `
                <h3 class="text-xl font-bold mb-4">Your Order</h3>
                <div class="space-y-4 mb-4 max-h-64 overflow-y-auto">
                    ${cart.map(item => `
                        <div>
                            <div class="flex justify-between items-start">
                                <p class="font-semibold">${item.quantity}x ${item.name}</p>
                                <p class="font-bold">RM${(item.quantity * item.finalPrice).toFixed(2)}</p>
                            </div>
                            <div class="pl-4 text-xs text-gray-400">
                                ${item.customizations.map(c => `<div>- ${c}</div>`).join('')}
                                ${item.comment ? `<div><em>"${item.comment}"</em></div>` : ''}
                            </div>
                        </div>`).join('')}
                </div>
                <div class="border-t border-gray-700 pt-4 flex justify-between font-bold text-xl">
                    <span>Total</span>
                    <span>RM${total.toFixed(2)}</span>
                </div>
                <button id="place-order-btn" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg mt-6">Proceed to Payment</button>`;
        }
        toggleModal(cartModal, true);
    }
    
    function updateCartButton() {
        if (cart.length === 0) {
            cartButtonContainer.classList.add('hidden');
            return;
        }
        const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
        const total = cart.reduce((sum, item) => sum + (item.finalPrice * item.quantity), 0);
        viewCartBtn.innerHTML = `<span>${itemCount} item(s)</span><span class="font-bold">View Cart (RM${total.toFixed(2)})</span>`;
        cartButtonContainer.classList.remove('hidden');
    }

    function toggleModal(modal, show) {
        const content = modal.querySelector('.modal-content');
        if (show) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                if(content) content.classList.remove('translate-y-full', 'scale-95');
            }, 10);
        } else {
            modal.classList.add('opacity-0');
            if(content) content.classList.add('translate-y-full', 'scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    }
    
    function showPage(pageId) {
        document.querySelectorAll('.bottom-nav a').forEach(n => n.classList.remove('brand-red'));
        if (pageId === 'menu') {
            menuPage.classList.remove('hidden-left');
            accountPage.classList.remove('hidden-left');
            accountPage.classList.add('hidden-right');
            navMenu.classList.add('brand-red');
        } else if (pageId === 'account') {
            fetchMyProfile();
            accountPage.classList.remove('hidden-right', 'hidden-left');
            menuPage.classList.add('hidden-left');
            navAccount.classList.add('brand-red');
        } else if (pageId === 'orders') {
            fetchMyOrders();
            navOrders.classList.add('brand-red');
        }
    }

    async function placeOrder() {
        const total = cart.reduce((sum, item) => sum + (item.finalPrice * item.quantity), 0);
        const orderData = { total: total, items: cart };
        try {
            const response = await fetch('api/menu.php?action=place-online-order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.message || 'Failed to place order.');
            cart = [];
            updateCartButton();
            toggleModal(cartModal, false);
            toggleModal(confirmationModal, true);
            setTimeout(() => toggleModal(confirmationModal, false), 2000);
        } catch(error) { alert('Error: ' + error.message); }
    }

    async function fetchMyOrders() {
        const listEl = document.getElementById('orders-list');
        listEl.innerHTML = `<p class="text-gray-400 text-center py-4">Loading orders...</p>`;
        toggleModal(ordersModal, true);
        try {
            const response = await fetch('api/menu.php?action=get-my-orders');
            const orders = await response.json();
            renderMyOrders(orders);
        } catch(error) { 
            console.error('Error fetching orders:', error); 
            listEl.innerHTML = `<p class="text-red-500 text-center py-4">Could not load orders.</p>`;
        }
    }
    
    function renderMyOrders(orders) {
        const listEl = document.getElementById('orders-list');
        if (orders.length === 0) {
            listEl.innerHTML = `<p class="text-gray-400 text-center py-4">You have no recent orders.</p>`;
        } else {
            listEl.innerHTML = orders.map(order => `
                <div class="border-b border-gray-700 py-3">
                    <div class="flex justify-between items-center"><p class="font-bold">Order #${order.id}</p><p class="px-2 py-1 text-xs rounded-full ${order.status === 'New' ? 'bg-blue-500' : 'bg-green-500'}">${order.status}</p></div>
                    <p class="text-sm text-gray-400 mt-1">${order.items_summary}</p>
                    <div class="flex justify-between text-sm mt-2"><span>${new Date(order.created_at).toLocaleString()}</span><span class="font-bold">RM${parseFloat(order.total_amount).toFixed(2)}</span></div>
                </div>`).join('');
        }
    }

    async function fetchMyProfile() {
        try {
            const response = await fetch('api/menu.php?action=get-my-profile');
            const profile = await response.json();
            renderProfile(profile);
        } catch (error) { console.error('Error fetching profile:', error); }
    }
    
    function renderProfile(profile) {
        const contentEl = document.getElementById('account-content');
        const cupsHTML = Array.from({length: 10}, (_, i) => `<i class="fas fa-coffee loyalty-cup ${i < profile.loyaltyProgress ? 'filled' : ''}"></i>`).join('');
        contentEl.innerHTML = `
            <div class="flex items-center space-x-4">
                <img src="https://placehold.co/64x64/ffffff/1a1a1a?text=${profile.name.charAt(0)}" class="w-16 h-16 rounded-full" alt="User Avatar">
                <div><h3 class="text-xl font-bold">${profile.name}</h3><p class="text-gray-400">${profile.phone}</p></div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 grid grid-cols-2 divide-x divide-gray-700 text-center">
                <div><p class="text-sm text-gray-400">Balance</p><p class="text-2xl font-bold">RM${profile.balance.toFixed(2)}</p></div>
                <div><p class="text-sm text-gray-400">Points</p><p class="text-2xl font-bold">${profile.points}</p></div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4">
                <h4 class="font-semibold mb-3">Loyalty Card: Buy 10, Get 1 Free!</h4>
                <div class="grid grid-cols-5 gap-4 text-3xl text-center mb-3">${cupsHTML}</div>
            </div>`;
    }

    // --- EVENT LISTENERS ---
    navMenu.addEventListener('click', (e) => { e.preventDefault(); showPage('menu'); });
    navOrders.addEventListener('click', (e) => { e.preventDefault(); showPage('orders'); });
    navAccount.addEventListener('click', (e) => { e.preventDefault(); showPage('account'); });

    menuContent.addEventListener('click', (e) => {
        const itemDiv = e.target.closest('.menu-item-div');
        if (itemDiv) fetchAndShowItemDetails(itemDiv.dataset.itemId);
    });

    itemModal.addEventListener('click', (e) => {
        if (e.target.matches('input')) updateModalPrice();
        if (e.target.id === 'add-to-cart-btn') addToCart();
    });
    
    cartModal.addEventListener('click', (e) => { if (e.target.id === 'place-order-btn') placeOrder(); });
    viewCartBtn.addEventListener('click', renderCart);
    
    [itemModal, cartModal, ordersModal, confirmationModal].forEach(modal => {
        modal.addEventListener('click', (e) => { if (e.target === modal) toggleModal(modal, false); });
    });

    // --- INITIALIZE THE APP ---
    initializeMenu(); 
});
</script>
</body>
</html>