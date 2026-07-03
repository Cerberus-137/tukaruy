// Global variables
let selectedCarriers = ['all'];
let selectedStatuses = [];
let currentCursor = null;
let currentTnId = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupFilterButtons();
    console.log('Tukeruy initialized');
    
    // Auto-load data on page load with default filters
    setTimeout(() => {
        autoLoadInitialData();
    }, 500);
});

// Auto load initial data
function autoLoadInitialData() {
    console.log('Auto-loading initial data...');
    const filters = {}; // Empty filters = get all
    performSearch(filters);
}

// Setup filter buttons
function setupFilterButtons() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type;
            const value = this.dataset.value;
            
            if (type === 'carrier') {
                handleCarrierClick(this, value);
            } else if (type === 'status') {
                handleStatusClick(this, value);
            }
        });
    });
}

// Handle carrier button click
function handleCarrierClick(btn, value) {
    if (value === 'all') {
        // Select all, deselect others
        document.querySelectorAll('[data-type="carrier"]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedCarriers = ['all'];
    } else {
        // Deselect "all" first
        document.querySelector('[data-type="carrier"][data-value="all"]').classList.remove('active');
        
        // Toggle this carrier
        if (btn.classList.contains('active')) {
            btn.classList.remove('active');
            selectedCarriers = selectedCarriers.filter(c => c !== value);
        } else {
            btn.classList.add('active');
            selectedCarriers.push(value);
        }
        
        // If nothing selected, select all
        if (selectedCarriers.length === 0) {
            document.querySelector('[data-type="carrier"][data-value="all"]').classList.add('active');
            selectedCarriers = ['all'];
        }
    }
}

// Handle status button click
function handleStatusClick(btn, value) {
    if (btn.classList.contains('active')) {
        btn.classList.remove('active');
        selectedStatuses = selectedStatuses.filter(s => s !== value);
    } else {
        btn.classList.add('active');
        selectedStatuses.push(value);
    }
}

// Apply filters and search
function applyFilters() {
    const filters = {};
    
    // Carriers
    if (selectedCarriers.length > 0 && !selectedCarriers.includes('all')) {
        filters.carrier = selectedCarriers;
    }
    
    // Statuses
    if (selectedStatuses.length > 0) {
        filters.status = selectedStatuses;
    }
    
    // Origin
    const originCountry = document.getElementById('origin_country').value.trim().toUpperCase();
    const originCity = document.getElementById('origin_city').value.trim().toUpperCase();
    if (originCountry) filters.origin_country = originCountry;
    if (originCity) filters.origin_city = originCity;
    
    // Destination
    const destCountry = document.getElementById('dest_country').value.trim().toUpperCase();
    const destCity = document.getElementById('dest_city').value.trim().toUpperCase();
    if (destCountry) filters.dest_country = destCountry;
    if (destCity) filters.dest_city = destCity;
    
    // Ship dates
    const shipFrom = document.getElementById('ship_from').value;
    const shipTo = document.getElementById('ship_to').value;
    if (shipFrom) filters.ship_from = shipFrom;
    if (shipTo) filters.ship_to = shipTo;
    
    // Delivery dates
    const deliveryFrom = document.getElementById('delivery_from').value;
    const deliveryTo = document.getElementById('delivery_to').value;
    if (deliveryFrom) filters.delivery_from = deliveryFrom;
    if (deliveryTo) filters.delivery_to = deliveryTo;
    
    console.log('Filters:', filters);
    
    // Reset cursor for new search
    currentCursor = null;
    
    // Perform search
    performSearch(filters);
}

// Perform search
async function performSearch(filters, append = false) {
    const tbody = document.getElementById('results-table');
    const loadMore = document.getElementById('load-more');
    const resultCount = document.getElementById('result-count');
    
    if (!append) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-purple-500"></i></td></tr>';
    }
    
    try {
        const response = await fetch('api/search.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                filters: filters,
                page_size: 25,
                cursor: currentCursor
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error);
        }
        
        if (!append) {
            tbody.innerHTML = '';
        }
        
        if (data.results.length === 0 && !append) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-12 text-gray-500"><i class="fas fa-inbox text-3xl mb-3"></i><div>Tidak ada hasil ditemukan</div></td></tr>';
            loadMore.classList.add('hidden');
            resultCount.textContent = '0 hasil';
            return;
        }
        
        // Render results
        data.results.forEach(item => {
            const row = document.createElement('tr');
            row.className = 'border-b border-dark-400 hover:bg-dark-200 transition';
            row.innerHTML = `
                <td class="py-4 px-4">
                    <div class="flex items-center space-x-2">
                        <span class="font-mono text-xs bg-dark-300 px-2 py-1 rounded">${item.carrier}</span>
                    </div>
                </td>
                <td class="py-4 px-4">
                    <span class="status-badge ${item.status_class}">${item.status}</span>
                </td>
                <td class="py-4 px-4 text-sm">${item.origin || 'N/A'}</td>
                <td class="py-4 px-4 text-sm">${item.destination}</td>
                                <td class="py-4 px-4 text-sm text-gray-400">
                                    ${item.ship_date || 'Belum dikirim'}
                                    ${item.est_delivery_date ? '<br><small>Est: ' + item.est_delivery_date + '</small>' : ''}
                                </td>
                <td class="py-4 px-4 text-sm">${item.weight}</td>
                <td class="py-4 px-4 text-right">
                    <button onclick="showRevealModal('${item.tn_id}', ${item.reveal_cost})" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        Dapatkan
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
        
        // Update count
        const total = data.total >= 100 ? '100+' : data.total;
        resultCount.textContent = `${total} hasil`;
        
        // Handle pagination
        if (data.next_cursor) {
            currentCursor = data.next_cursor;
            loadMore.classList.remove('hidden');
        } else {
            loadMore.classList.add('hidden');
        }
        
        // Store current filters for load more
        window.currentFilters = filters;
        
    } catch (error) {
        console.error('Search error:', error);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-12 text-red-500"><i class="fas fa-exclamation-triangle text-3xl mb-3"></i><div>${error.message}</div></td></tr>`;
        loadMore.classList.add('hidden');
    }
}

// Load more results
function loadMore() {
    if (currentCursor && window.currentFilters) {
        performSearch(window.currentFilters, true);
    }
}

// Reset filters
function resetFilters() {
    // Reset buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector('[data-type="carrier"][data-value="all"]').classList.add('active');
    
    // Reset inputs
    document.getElementById('origin_country').value = '';
    document.getElementById('origin_city').value = '';
    document.getElementById('dest_country').value = '';
    document.getElementById('dest_city').value = '';
    document.getElementById('ship_from').value = '';
    document.getElementById('ship_to').value = '';
    document.getElementById('delivery_from').value = '';
    document.getElementById('delivery_to').value = '';
    
    // Reset variables
    selectedCarriers = ['all'];
    selectedStatuses = [];
    currentCursor = null;
    
    // Clear table
    document.getElementById('results-table').innerHTML = '<tr><td colspan="7" class="text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-purple-500"></i><div class="mt-3 text-gray-500">Memuat data...</div></td></tr>';
    document.getElementById('result-count').textContent = '~100 hasil';
    document.getElementById('load-more').classList.add('hidden');
    
    // Auto-reload data
    setTimeout(() => {
        autoLoadInitialData();
    }, 300);
}

// Show reveal modal
function showRevealModal(tnId, cost) {
    currentTnId = tnId;
    const modal = document.getElementById('reveal-modal');
    const content = document.getElementById('modal-content');
    
    content.innerHTML = `
        <div class="bg-dark-300 rounded-lg p-4">
            <div class="flex justify-between mb-2">
                <span class="text-sm text-gray-400">ID Resi:</span>
                <span class="font-mono text-sm">${tnId}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-gray-400">Biaya:</span>
                <span class="font-semibold">${cost} kredit</span>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Set confirm button action
    document.getElementById('confirm-btn').onclick = () => revealTracking(tnId);
}

// Close modal
function closeModal() {
    document.getElementById('reveal-modal').classList.add('hidden');
    document.getElementById('reveal-modal').classList.remove('flex');
    currentTnId = null;
}

// Reveal tracking number
async function revealTracking(tnId) {
    const btn = document.getElementById('confirm-btn');
    const content = document.getElementById('modal-content');
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    btn.disabled = true;
    
    try {
        const response = await fetch('api/reveal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tn_id: tnId })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error);
        }
        
        // Update credits
        document.getElementById('credits-display').textContent = data.credits_remaining.toLocaleString();
        
        // Show success
        content.innerHTML = `
            <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4">
                <div class="flex items-center space-x-2 mb-3">
                    <i class="fas fa-check-circle text-green-400"></i>
                    <span class="font-semibold text-green-400">Berhasil!</span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-400">Nomor Resi:</span>
                        <span class="font-mono font-bold">${data.tracking_number}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-400">Kurir:</span>
                        <span class="font-semibold">${data.carrier}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-400">Status:</span>
                        <span class="font-semibold">${data.status}</span>
                    </div>
                </div>
            </div>
        `;
        
        btn.innerHTML = 'Tutup';
        btn.onclick = closeModal;
        btn.disabled = false;
        
    } catch (error) {
        content.innerHTML = `
            <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                    <span class="text-red-400">${error.message}</span>
                </div>
            </div>
        `;
        
        btn.innerHTML = 'Konfirmasi';
        btn.disabled = false;
    }
}
