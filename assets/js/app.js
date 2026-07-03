let currentCursor = null;
let currentFilters = {};

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

// Apply filters and search
function applyFilters() {
    const filters = {};
    
    // Get carrier filters
    const carrierChecks = document.querySelectorAll('input[name="carrier[]"]:checked');
    const carriers = Array.from(carrierChecks).map(cb => cb.value);
    if (carriers.length > 0 && !carriers.includes('all')) {
        filters.carrier = carriers;
    }
    
    // Get status filters
    const statusChecks = document.querySelectorAll('input[name="status[]"]:checked');
    const statuses = Array.from(statusChecks).map(cb => cb.value);
    if (statuses.length > 0) {
        filters.status = statuses;
    }
    
    // Get destination
    const destCountry = document.querySelector('select[name="dest_country"]').value;
    const destCity = document.querySelector('input[name="dest_city"]').value;
    
    if (destCountry) {
        filters.dest_country = destCountry;
    }
    if (destCity) {
        filters.dest_city = destCity;
    }
    
    // Get date range
    const deliveryFrom = document.querySelector('input[name="delivery_from"]').value;
    const deliveryTo = document.querySelector('input[name="delivery_to"]').value;
    
    if (deliveryFrom) {
        filters.delivery_from = deliveryFrom;
    }
    if (deliveryTo) {
        filters.delivery_to = deliveryTo;
    }
    
    // Get advanced options
    if (document.querySelector('input[name="signature_required"]').checked) {
        filters.signature_required = true;
    }
    if (document.querySelector('input[name="photo_confirmed"]').checked) {
        filters.photo_confirmed = true;
    }
    
    currentFilters = filters;
    currentCursor = null;
    performSearch();
}

// Search tracking numbers
function searchTracking() {
    const searchInput = document.getElementById('search-input').value.trim();
    
    if (searchInput) {
        // Simple parsing of search input
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

// Handle "All" carrier checkbox
document.querySelector('input[name="carrier[]"][value="all"]')?.addEventListener('change', function() {
    const otherCarriers = document.querySelectorAll('input[name="carrier[]"]:not([value="all"])');
    if (this.checked) {
        otherCarriers.forEach(cb => cb.checked = false);
    }
});

// Uncheck "All" when other carriers are selected
document.querySelectorAll('input[name="carrier[]"]:not([value="all"])')?.forEach(cb => {
    cb.addEventListener('change', function() {
        if (this.checked) {
            document.querySelector('input[name="carrier[]"][value="all"]').checked = false;
        }
    });
});

// Enter key for search
document.getElementById('search-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchTracking();
    }
});
