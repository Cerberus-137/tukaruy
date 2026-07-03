let currentCursor = null;
let currentFilters = {};
let selectedCarriers = ['all'];
let selectedStatuses = [];

// Country data
const countries = [
    { code: 'US', name: 'United States' },
    { code: 'GB', name: 'United Kingdom' },
    { code: 'CA', name: 'Canada' },
    { code: 'MX', name: 'Mexico' },
    { code: 'DE', name: 'Germany' },
    { code: 'FR', name: 'France' },
    { code: 'IT', name: 'Italy' },
    { code: 'ES', name: 'Spain' },
    { code: 'NL', name: 'Netherlands' },
    { code: 'BE', name: 'Belgium' },
    { code: 'CH', name: 'Switzerland' },
    { code: 'AT', name: 'Austria' },
    { code: 'PL', name: 'Poland' },
    { code: 'SE', name: 'Sweden' },
    { code: 'NO', name: 'Norway' },
    { code: 'DK', name: 'Denmark' },
    { code: 'FI', name: 'Finland' },
    { code: 'IE', name: 'Ireland' },
    { code: 'PT', name: 'Portugal' },
    { code: 'GR', name: 'Greece' },
    { code: 'CZ', name: 'Czech Republic' },
    { code: 'HU', name: 'Hungary' },
    { code: 'RO', name: 'Romania' },
    { code: 'AU', name: 'Australia' },
    { code: 'NZ', name: 'New Zealand' },
    { code: 'SG', name: 'Singapore' },
    { code: 'HK', name: 'Hong Kong' },
    { code: 'JP', name: 'Japan' },
    { code: 'KR', name: 'South Korea' },
    { code: 'CN', name: 'China' },
    { code: 'IN', name: 'India' },
    { code: 'ID', name: 'Indonesia' },
    { code: 'TH', name: 'Thailand' },
    { code: 'MY', name: 'Malaysia' },
    { code: 'PH', name: 'Philippines' },
    { code: 'VN', name: 'Vietnam' },
    { code: 'AE', name: 'United Arab Emirates' },
    { code: 'SA', name: 'Saudi Arabia' },
    { code: 'IL', name: 'Israel' },
    { code: 'ZA', name: 'South Africa' },
    { code: 'BR', name: 'Brazil' },
    { code: 'AR', name: 'Argentina' },
    { code: 'CL', name: 'Chile' },
    { code: 'CO', name: 'Colombia' }
];

// US States
const usStates = [
    'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
    'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
    'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
    'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
    'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY'
];

// Initialize country selects
function initializeCountrySelects() {
    const originSelect = document.getElementById('origin_country');
    const destSelect = document.getElementById('dest_country');
    const destStateSelect = document.querySelector('select[name="dest_state"]');
    
    countries.forEach(country => {
        const option1 = new Option(`${country.name} (${country.code})`, country.code);
        const option2 = new Option(`${country.name} (${country.code})`, country.code);
        originSelect.add(option1);
        destSelect.add(option2);
    });
    
    // Add US states
    usStates.forEach(state => {
        destStateSelect.add(new Option(state, state));
    });
}

// Filter countries in dropdown
function filterCountries(type) {
    const searchInput = document.getElementById(`${type}-search`);
    const select = document.getElementById(`${type}_country`);
    const filter = searchInput.value.toUpperCase();
    
    for (let i = 1; i < select.options.length; i++) {
        const option = select.options[i];
        const txtValue = option.textContent || option.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    }
}

// Toggle carrier button
function toggleCarrier(btn) {
    const value = btn.dataset.value;
    
    if (value === 'all') {
        // If clicking "All", deselect others
        document.querySelectorAll('.carrier-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedCarriers = ['all'];
    } else {
        // Deselect "All" first
        document.querySelector('.carrier-btn[data-value="all"]').classList.remove('active');
        
        // Toggle this carrier
        if (btn.classList.contains('active')) {
            btn.classList.remove('active');
            selectedCarriers = selectedCarriers.filter(c => c !== value);
        } else {
            btn.classList.add('active');
            selectedCarriers.push(value);
        }
        
        // If nothing selected, select "All"
        if (selectedCarriers.length === 0 || (selectedCarriers.length === 1 && selectedCarriers[0] === 'all')) {
            document.querySelector('.carrier-btn[data-value="all"]').classList.add('active');
            selectedCarriers = ['all'];
        }
    }
}

// Toggle status button
function toggleStatus(btn) {
    const value = btn.dataset.value;
    
    if (btn.classList.contains('active')) {
        btn.classList.remove('active');
        selectedStatuses = selectedStatuses.filter(s => s !== value);
    } else {
        btn.classList.add('active');
        selectedStatuses.push(value);
    }
}

// Toggle more options
function toggleMoreOptions() {
    const moreOptions = document.getElementById('more-options');
    const isHidden = moreOptions.classList.contains('hidden');
    
    if (isHidden) {
        moreOptions.classList.remove('hidden');
    } else {
        moreOptions.classList.add('hidden');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCountrySelects();
    
    // Auto-search on page load with default filters
    setTimeout(() => {
        performSearch();
    }, 500);
});

// Toggle sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const filterContent = document.getElementById('filter-content');
    const filterTitle = document.getElementById('filter-title');
    
    if (sidebar.classList.contains('sidebar-expanded')) {
        sidebar.classList.remove('sidebar-expanded');
        sidebar.classList.add('sidebar-collapsed');
        filterContent.style.display = 'none';
        filterTitle.style.display = 'none';
    } else {
        sidebar.classList.remove('sidebar-collapsed');
        sidebar.classList.add('sidebar-expanded');
        filterContent.style.display = 'block';
        filterTitle.style.display = 'block';
    }
}

// Collect current filters
function collectFilters() {
    const filters = {};
    
    // Get carrier filters
    if (selectedCarriers.length > 0 && !selectedCarriers.includes('all')) {
        filters.carrier = selectedCarriers;
    }
    
    // Get status filters
    if (selectedStatuses.length > 0) {
        filters.status = selectedStatuses;
    }
    
    // Get origin
    const originCountry = document.getElementById('origin_country')?.value;
    const originCity = document.querySelector('input[name="origin_city"]')?.value;
    
    if (originCountry) {
        filters.origin_country = originCountry;
    }
    if (originCity) {
        filters.origin_city = originCity;
    }
    
    // Get destination
    const destCountry = document.getElementById('dest_country')?.value;
    const destZip = document.querySelector('input[name="dest_zip"]')?.value;
    const destState = document.querySelector('select[name="dest_state"]')?.value;
    
    if (destCountry) {
        filters.dest_country = destCountry;
    }
    if (destZip) {
        filters.dest_zip = destZip;
    }
    if (destState) {
        filters.dest_state = destState;
    }
    
    // Get ship date range
    const shipFrom = document.querySelector('input[name="ship_from"]')?.value;
    const shipTo = document.querySelector('input[name="ship_to"]')?.value;
    
    if (shipFrom) {
        filters.ship_from = shipFrom;
    }
    if (shipTo) {
        filters.ship_to = shipTo;
    }
    
    // Get delivery date range
    const deliveryFrom = document.querySelector('input[name="delivery_from"]')?.value;
    const deliveryTo = document.querySelector('input[name="delivery_to"]')?.value;
    
    if (deliveryFrom) {
        filters.delivery_from = deliveryFrom;
    }
    if (deliveryTo) {
        filters.delivery_to = deliveryTo;
    }
    
    // Get weight range
    const weightMin = document.querySelector('input[name="weight_min"]')?.value;
    const weightMax = document.querySelector('input[name="weight_max"]')?.value;
    
    if (weightMin) {
        filters.weight_min = weightMin;
    }
    if (weightMax) {
        filters.weight_max = weightMax;
    }
    
    // Get advanced options
    if (document.querySelector('input[name="signature_required"]')?.checked) {
        filters.signature_required = true;
    }
    if (document.querySelector('input[name="photo_confirmed"]')?.checked) {
        filters.photo_confirmed = true;
    }
    
    return filters;
}

// Apply filters and search
function applyFilters() {
    currentFilters = collectFilters();
    currentCursor = null;
    performSearch();
}

// Search tracking numbers
function searchTracking() {
    const searchInput = document.getElementById('search-input').value.trim();
    
    // Collect all current filters
    currentFilters = collectFilters();
    
    // Add search input as destination city
    if (searchInput) {
        currentFilters.dest_city = searchInput;
    }
    
    currentCursor = null;
    performSearch();
}

// Perform the actual search
async function performSearch() {
    const tbody = document.getElementById('tracking-results');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const resultCount = document.getElementById('result-count');
    
    if (!currentCursor) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-purple-500"></i></td></tr>';
    }
    
    try {
        const response = await fetch('api/search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                filters: currentFilters,
                page_size: 25,
                cursor: currentCursor
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error);
        }
        
        if (!currentCursor) {
            tbody.innerHTML = '';
        }
        
        if (data.results.length === 0 && !currentCursor) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-12 text-gray-500"><i class="fas fa-inbox text-3xl mb-3"></i><div>No tracking numbers found</div></td></tr>';
            loadMoreBtn.classList.add('hidden');
            return;
        }
        
        // Append results
        data.results.forEach(item => {
            const row = document.createElement('tr');
            row.className = 'border-b border-dark-400 hover:bg-dark-200 transition';
            row.innerHTML = `
                <td class="py-4 px-4">
                    <div class="flex items-center space-x-2">
                        <span class="font-mono text-xs bg-dark-300 px-2 py-1 rounded">${item.carrier}</span>
                        <span class="text-xs text-gray-500">${item.service}</span>
                    </div>
                </td>
                <td class="py-4 px-4">
                    <span class="status-badge ${item.status_class}">${item.status}</span>
                </td>
                <td class="py-4 px-4 text-sm">${item.origin}</td>
                <td class="py-4 px-4 text-sm">${item.destination}</td>
                <td class="py-4 px-4 text-sm text-gray-400">${item.ship_date}<br><small>${item.est_delivery_date}</small></td>
                <td class="py-4 px-4 text-sm">${item.weight}</td>
                <td class="py-4 px-4 text-right">
                    <button onclick="showRevealModal('${item.tn_id}', ${item.reveal_cost})" class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        Get TN
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
        
        // Update result count
        const total = data.total >= 100 ? '100+' : data.total;
        resultCount.textContent = `${total} matches`;
        
        // Handle pagination
        if (data.next_cursor) {
            currentCursor = data.next_cursor;
            loadMoreBtn.classList.remove('hidden');
        } else {
            loadMoreBtn.classList.add('hidden');
        }
        
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-12 text-red-500"><i class="fas fa-exclamation-triangle text-3xl mb-3"></i><div>${error.message}</div></td></tr>`;
        loadMoreBtn.classList.add('hidden');
    }
}

// Load more results
function loadMore() {
    if (currentCursor) {
        performSearch();
    }
}

// Show reveal modal
let currentTnId = null;
function showRevealModal(tnId, cost) {
    currentTnId = tnId;
    const modal = document.getElementById('reveal-modal');
    const content = document.getElementById('reveal-content');
    
    content.innerHTML = `
        <div class="bg-dark-300 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-400">TN ID:</span>
                <span class="font-mono text-sm">${tnId}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Cost:</span>
                <span class="font-semibold">${cost} credit${cost > 1 ? 's' : ''}</span>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Close reveal modal
function closeRevealModal() {
    const modal = document.getElementById('reveal-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    currentTnId = null;
}

// Confirm reveal
document.getElementById('confirm-reveal-btn')?.addEventListener('click', async function() {
    if (!currentTnId) return;
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Revealing...';
    btn.disabled = true;
    
    try {
        const response = await fetch('api/reveal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tn_id: currentTnId
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error);
        }
        
        // Update credits display
        document.getElementById('credits-display').textContent = data.credits_remaining.toLocaleString();
        
        // Show success
        document.getElementById('reveal-content').innerHTML = `
            <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-4">
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-check-circle text-green-400"></i>
                    <span class="font-semibold text-green-400">Successfully Revealed</span>
                </div>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Tracking Number:</span>
                        <span class="font-mono font-bold text-lg">${data.tracking_number}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Carrier:</span>
                        <span class="font-semibold">${data.carrier}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Status:</span>
                        <span class="font-semibold">${data.status}</span>
                    </div>
                </div>
            </div>
        `;
        
        btn.innerHTML = 'Close';
        btn.onclick = closeRevealModal;
        
    } catch (error) {
        document.getElementById('reveal-content').innerHTML = `
            <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                    <span class="text-red-400">${error.message}</span>
                </div>
            </div>
        `;
        
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Enter key for search
document.getElementById('search-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchTracking();
    }
});


// Reset all filters
function resetFilters() {
    // Reset carriers
    selectedCarriers = ['all'];
    document.querySelectorAll('.carrier-btn').forEach(btn => {
        if (btn.dataset.value === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Reset statuses
    selectedStatuses = [];
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Reset all inputs
    document.getElementById('origin_country').value = '';
    document.querySelector('input[name="origin_city"]').value = '';
    document.getElementById('dest_country').value = '';
    document.querySelector('input[name="dest_zip"]').value = '';
    document.querySelector('select[name="dest_state"]').value = '';
    document.querySelector('input[name="ship_from"]').value = '';
    document.querySelector('input[name="ship_to"]').value = '';
    document.querySelector('input[name="delivery_from"]').value = '';
    document.querySelector('input[name="delivery_to"]').value = '';
    document.querySelector('input[name="weight_min"]').value = '';
    document.querySelector('input[name="weight_max"]').value = '';
    document.querySelector('input[name="signature_required"]').checked = false;
    document.querySelector('input[name="photo_confirmed"]').checked = false;
    document.getElementById('search-input').value = '';
    
    // Clear search inputs
    document.getElementById('origin-search').value = '';
    document.getElementById('dest-search').value = '';
    
    // Show all countries again
    filterCountries('origin');
    filterCountries('dest');
    
    // Apply reset
    currentFilters = {};
    currentCursor = null;
    performSearch();
}

// Real-time filter update (optional - auto search on change)
function setupAutoSearch() {
    // Auto-search when changing important filters
    const autoSearchElements = [
        'select[name="dest_country"]',
        'select[name="origin_country"]',
        'input[name="ship_from"]',
        'input[name="ship_to"]',
        'input[name="delivery_from"]',
        'input[name="delivery_to"]'
    ];
    
    autoSearchElements.forEach(selector => {
        const element = document.querySelector(selector);
        if (element) {
            element.addEventListener('change', function() {
                // Debounce to avoid too many requests
                clearTimeout(window.searchTimeout);
                window.searchTimeout = setTimeout(() => {
                    applyFilters();
                }, 800);
            });
        }
    });
}

// Call setup after DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupAutoSearch();
});
