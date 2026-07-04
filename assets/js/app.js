// Global variables
let selectedCarriers = ['all'];
let selectedStatuses = [];
let currentCursor = null;
let currentTnId = null;
let autoApply = true;
let searchTimeout = null;

// US States data
const usStates = [
    { code: 'AL', name: 'Alabama' }, { code: 'AK', name: 'Alaska' }, { code: 'AZ', name: 'Arizona' },
    { code: 'AR', name: 'Arkansas' }, { code: 'CA', name: 'California' }, { code: 'CO', name: 'Colorado' },
    { code: 'CT', name: 'Connecticut' }, { code: 'DE', name: 'Delaware' }, { code: 'FL', name: 'Florida' },
    { code: 'GA', name: 'Georgia' }, { code: 'HI', name: 'Hawaii' }, { code: 'ID', name: 'Idaho' },
    { code: 'IL', name: 'Illinois' }, { code: 'IN', name: 'Indiana' }, { code: 'IA', name: 'Iowa' },
    { code: 'KS', name: 'Kansas' }, { code: 'KY', name: 'Kentucky' }, { code: 'LA', name: 'Louisiana' },
    { code: 'ME', name: 'Maine' }, { code: 'MD', name: 'Maryland' }, { code: 'MA', name: 'Massachusetts' },
    { code: 'MI', name: 'Michigan' }, { code: 'MN', name: 'Minnesota' }, { code: 'MS', name: 'Mississippi' },
    { code: 'MO', name: 'Missouri' }, { code: 'MT', name: 'Montana' }, { code: 'NE', name: 'Nebraska' },
    { code: 'NV', name: 'Nevada' }, { code: 'NH', name: 'New Hampshire' }, { code: 'NJ', name: 'New Jersey' },
    { code: 'NM', name: 'New Mexico' }, { code: 'NY', name: 'New York' }, { code: 'NC', name: 'North Carolina' },
    { code: 'ND', name: 'North Dakota' }, { code: 'OH', name: 'Ohio' }, { code: 'OK', name: 'Oklahoma' },
    { code: 'OR', name: 'Oregon' }, { code: 'PA', name: 'Pennsylvania' }, { code: 'RI', name: 'Rhode Island' },
    { code: 'SC', name: 'South Carolina' }, { code: 'SD', name: 'South Dakota' }, { code: 'TN', name: 'Tennessee' },
    { code: 'TX', name: 'Texas' }, { code: 'UT', name: 'Utah' }, { code: 'VT', name: 'Vermont' },
    { code: 'VA', name: 'Virginia' }, { code: 'WA', name: 'Washington' }, { code: 'WV', name: 'West Virginia' },
    { code: 'WI', name: 'Wisconsin' }, { code: 'WY', name: 'Wyoming' }
];

// Country data
const countries = [
    { code: 'US', name: 'United States' }, { code: 'GB', name: 'United Kingdom' }, { code: 'CA', name: 'Canada' },
    { code: 'AU', name: 'Australia' }, { code: 'DE', name: 'Germany' }, { code: 'FR', name: 'France' },
    { code: 'IT', name: 'Italy' }, { code: 'ES', name: 'Spain' }, { code: 'NL', name: 'Netherlands' },
    { code: 'BE', name: 'Belgium' }, { code: 'SE', name: 'Sweden' }, { code: 'NO', name: 'Norway' },
    { code: 'DK', name: 'Denmark' }, { code: 'FI', name: 'Finland' }, { code: 'CH', name: 'Switzerland' },
    { code: 'AT', name: 'Austria' }, { code: 'PL', name: 'Poland' }, { code: 'CZ', name: 'Czech Republic' },
    { code: 'IE', name: 'Ireland' }, { code: 'PT', name: 'Portugal' }, { code: 'GR', name: 'Greece' },
    { code: 'HU', name: 'Hungary' }, { code: 'RO', name: 'Romania' }, { code: 'BG', name: 'Bulgaria' },
    { code: 'HR', name: 'Croatia' }, { code: 'SK', name: 'Slovakia' }, { code: 'SI', name: 'Slovenia' },
    { code: 'LT', name: 'Lithuania' }, { code: 'LV', name: 'Latvia' }, { code: 'EE', name: 'Estonia' },
    { code: 'JP', name: 'Japan' }, { code: 'CN', name: 'China' }, { code: 'KR', name: 'South Korea' },
    { code: 'IN', name: 'India' }, { code: 'SG', name: 'Singapore' }, { code: 'MY', name: 'Malaysia' },
    { code: 'TH', name: 'Thailand' }, { code: 'VN', name: 'Vietnam' }, { code: 'PH', name: 'Philippines' },
    { code: 'ID', name: 'Indonesia' }, { code: 'HK', name: 'Hong Kong' }, { code: 'TW', name: 'Taiwan' },
    { code: 'NZ', name: 'New Zealand' }, { code: 'BR', name: 'Brazil' }, { code: 'MX', name: 'Mexico' },
    { code: 'AR', name: 'Argentina' }, { code: 'CL', name: 'Chile' }, { code: 'CO', name: 'Colombia' },
    { code: 'PE', name: 'Peru' }, { code: 'ZA', name: 'South Africa' }, { code: 'EG', name: 'Egypt' },
    { code: 'NG', name: 'Nigeria' }, { code: 'KE', name: 'Kenya' }, { code: 'AE', name: 'United Arab Emirates' },
    { code: 'SA', name: 'Saudi Arabia' }, { code: 'IL', name: 'Israel' }, { code: 'TR', name: 'Turkey' },
    { code: 'RU', name: 'Russia' }, { code: 'UA', name: 'Ukraine' }
];
// City data by country
const citiesByCountry = {
    'ID': ['JAKARTA', 'SURABAYA', 'BANDUNG', 'MEDAN', 'SEMARANG', 'MAKASSAR', 'PALEMBANG',
           'TANGERANG', 'BOGOR', 'BATAM', 'PEKANBARU', 'BANDAR LAMPUNG', 'MALANG', 'PADANG',
           'DENPASAR', 'SAMARINDA', 'BANJARMASIN', 'JAMBI', 'CIREBON', 'SURAKARTA', 'BALIKPAPAN',
           'PONTIANAK', 'MANADO', 'YOGYAKARTA'],
    'US': ['NEW YORK, NY', 'LOS ANGELES, CA', 'CHICAGO, IL', 'HOUSTON, TX', 'PHOENIX, AZ',
           'PHILADELPHIA, PA', 'SAN ANTONIO, TX', 'SAN DIEGO, CA', 'DALLAS, TX', 'SAN JOSE, CA',
           'AUSTIN, TX', 'JACKSONVILLE, FL', 'FORT WORTH, TX', 'COLUMBUS, OH', 'CHARLOTTE, NC',
           'SAN FRANCISCO, CA', 'INDIANAPOLIS, IN', 'SEATTLE, WA', 'DENVER, CO', 'WASHINGTON, DC',
           'BOSTON, MA', 'EL PASO, TX', 'NASHVILLE, TN', 'DETROIT, MI', 'OKLAHOMA CITY, OK',
           'PORTLAND, OR', 'LAS VEGAS, NV', 'MEMPHIS, TN', 'LOUISVILLE, KY', 'BALTIMORE, MD',
           'MILWAUKEE, WI', 'ALBUQUERQUE, NM', 'TUCSON, AZ', 'FRESNO, CA', 'MESA, AZ',
           'SACRAMENTO, CA', 'ATLANTA, GA', 'KANSAS CITY, MO', 'COLORADO SPRINGS, CO', 'MIAMI, FL',
           'RALEIGH, NC', 'OMAHA, NE', 'LONG BEACH, CA', 'VIRGINIA BEACH, VA', 'OAKLAND, CA'],
    'GB': ['LONDON', 'BIRMINGHAM', 'MANCHESTER', 'LEEDS', 'GLASGOW', 'LIVERPOOL', 'NEWCASTLE',
           'SHEFFIELD', 'BRISTOL', 'EDINBURGH', 'LEICESTER', 'NOTTINGHAM', 'COVENTRY', 'HULL',
           'BRADFORD', 'CARDIFF', 'BELFAST', 'STOKE-ON-TRENT', 'WOLVERHAMPTON', 'PLYMOUTH',
           'DERBY', 'SOUTHAMPTON', 'PORTSMOUTH', 'BRIGHTON', 'READING'],
    'AU': ['SYDNEY, NSW', 'MELBOURNE, VIC', 'BRISBANE, QLD', 'PERTH, WA', 'ADELAIDE, SA',
           'GOLD COAST, QLD', 'NEWCASTLE, NSW', 'CANBERRA, ACT', 'SUNSHINE COAST, QLD',
           'WOLLONGONG, NSW', 'HOBART, TAS', 'GEELONG, VIC', 'TOWNSVILLE, QLD', 'CAIRNS, QLD',
           'DARWIN, NT', 'TOOWOOMBA, QLD', 'BALLARAT, VIC', 'BENDIGO, VIC', 'LAUNCESTON, TAS'],
    'CA': ['TORONTO, ON', 'MONTREAL, QC', 'VANCOUVER, BC', 'CALGARY, AB', 'EDMONTON, AB',
           'OTTAWA, ON', 'WINNIPEG, MB', 'QUEBEC CITY, QC', 'HAMILTON, ON', 'KITCHENER, ON',
           'LONDON, ON', 'VICTORIA, BC', 'HALIFAX, NS', 'OSHAWA, ON', 'WINDSOR, ON',
           'SASKATOON, SK', 'REGINA, SK', 'ST. JOHN\'S, NL', 'KELOWNA, BC', 'BARRIE, ON'],
    'JP': ['TOKYO', 'OSAKA', 'YOKOHAMA', 'NAGOYA', 'SAPPORO', 'FUKUOKA', 'KOBE', 'KYOTO',
           'KAWASAKI', 'SAITAMA', 'HIROSHIMA', 'SENDAI', 'KITAKYUSHU', 'CHIBA', 'SAKAI',
           'NIIGATA', 'HAMAMATSU', 'KUMAMOTO', 'SAGAMIHARA', 'SHIZUOKA', 'OKAYAMA'],
    'SG': ['SINGAPORE'],
    'MY': ['KUALA LUMPUR', 'GEORGE TOWN', 'IPOH', 'SHAH ALAM', 'PETALING JAYA', 'JOHOR BAHRU',
           'MALACCA CITY', 'KOTA KINABALU', 'KUCHING', 'KUANTAN', 'SEREMBAN', 'ALOR SETAR',
           'KOTA BHARU', 'KUALA TERENGGANU', 'SANDAKAN'],
    'TH': ['BANGKOK', 'NONTHABURI', 'PAK KRET', 'HAT YAI', 'CHIANG MAI', 'PHUKET CITY',
           'NAKHON RATCHASIMA', 'UDON THANI', 'SURAT THANI', 'KHON KAEN', 'NAKHON SI THAMMARAT',
           'PATTAYA', 'CHIANG RAI', 'SONGKHLA', 'LAMPANG'],
    'PH': ['MANILA', 'QUEZON CITY', 'DAVAO CITY', 'CALOOCAN', 'CEBU CITY', 'ZAMBOANGA CITY',
           'ANTIPOLO', 'PASIG', 'TAGUIG', 'CAGAYAN DE ORO', 'PARAÑAQUE', 'VALENZUELA',
           'DASMARIÑAS', 'LAS PIÑAS', 'GENERAL SANTOS', 'MAKATI', 'BACOLOD', 'BACOOR',
           'ILOILO CITY', 'MUNTINLUPA', 'SAN JOSE DEL MONTE', 'MARIKINA'],
    'DE': ['BERLIN', 'HAMBURG', 'MUNICH', 'COLOGNE', 'FRANKFURT', 'STUTTGART', 'DÜSSELDORF',
           'DORTMUND', 'ESSEN', 'LEIPZIG', 'BREMEN', 'DRESDEN', 'HANOVER', 'NUREMBERG',
           'DUISBURG', 'BOCHUM', 'WUPPERTAL', 'BIELEFELD', 'BONN', 'MÜNSTER'],
    'FR': ['PARIS', 'MARSEILLE', 'LYON', 'TOULOUSE', 'NICE', 'NANTES', 'STRASBOURG',
           'MONTPELLIER', 'BORDEAUX', 'LILLE', 'RENNES', 'REIMS', 'LE HAVRE', 'SAINT-ÉTIENNE',
           'TOULON', 'GRENOBLE', 'DIJON', 'NÎMES', 'ANGERS', 'VILLEURBANNE']
};
// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupFilterButtons();
    setupCountryDropdown();
    setupOriginCountryDropdown();
    setupDestCityDropdown();
    setupOriginCityDropdown();
    setupDestStateDropdown();
    setupAutoApplyToggle();
    setupFilterChangeListeners();
    console.log('Tukeruy initialized');
    
    // Load stats in background (non-blocking)
    loadStats();
    
    // Auto-load data on page load with default filters (US)
    setTimeout(() => {
        autoLoadInitialData();
    }, 500);
});

// Load stats asynchronously
async function loadStats() {
    try {
        const response = await fetch('api/stats.php');
        const data = await response.json();
        
        if (data.success && data.stats) {
            document.getElementById('stat-total').textContent = data.stats.total >= 100 ? '100+' : data.stats.total;
            document.getElementById('stat-fedex').textContent = data.stats.fedex >= 100 ? '100+' : data.stats.fedex;
            document.getElementById('stat-dhl').textContent = data.stats.dhl >= 100 ? '100+' : data.stats.dhl;
            document.getElementById('stat-ups').textContent = data.stats.ups >= 100 ? '100+' : data.stats.ups;
        } else {
            // Fallback to zeros
            document.getElementById('stat-total').textContent = '0';
            document.getElementById('stat-fedex').textContent = '0';
            document.getElementById('stat-dhl').textContent = '0';
            document.getElementById('stat-ups').textContent = '0';
        }
    } catch (error) {
        console.error('Failed to load stats:', error);
        document.getElementById('stat-total').textContent = '0';
        document.getElementById('stat-fedex').textContent = '0';
        document.getElementById('stat-dhl').textContent = '0';
        document.getElementById('stat-ups').textContent = '0';
    }
}

// Auto load initial data
function autoLoadInitialData() {
    console.log('Auto-loading initial data with default US filter...');
    const filters = { dest_country: 'US' };
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
        document.querySelectorAll('[data-type="carrier"]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedCarriers = ['all'];
    } else {
        document.querySelector('[data-type="carrier"][data-value="all"]').classList.remove('active');
        
        if (btn.classList.contains('active')) {
            btn.classList.remove('active');
            selectedCarriers = selectedCarriers.filter(c => c !== value);
        } else {
            btn.classList.add('active');
            selectedCarriers.push(value);
        }
        
        if (selectedCarriers.length === 0) {
            document.querySelector('[data-type="carrier"][data-value="all"]').classList.add('active');
            selectedCarriers = ['all'];
        }
    }
    
    // Auto-apply if enabled
    if (autoApply) {
        debounceSearch();
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
    
    // Auto-apply if enabled
    if (autoApply) {
        debounceSearch();
    }
}

// Setup country dropdown
function setupCountryDropdown() {
    const trigger = document.getElementById('country-dropdown-trigger');
    const menu = document.getElementById('country-dropdown-menu');
    const searchInput = document.getElementById('country-search');
    const countryList = document.getElementById('country-list');
    const hiddenInput = document.getElementById('dest_country');
    const display = document.getElementById('selected-country-display');
    
    if (!trigger || !menu || !searchInput || !countryList || !hiddenInput || !display) return;
    
    // Populate country list
    renderCountryList(countries);
    
    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        menu.classList.toggle('hidden');
        if (!menu.classList.contains('hidden')) {
            searchInput.focus();
        }
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const filtered = countries.filter(c => 
            c.name.toLowerCase().includes(query) || 
            c.code.toLowerCase().includes(query)
        );
        renderCountryList(filtered);
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
    
    // Render country list
    function renderCountryList(countryArray) {
        countryList.innerHTML = '';
        
        countryArray.forEach(country => {
            const item = document.createElement('div');
            item.className = 'country-item';
            if (country.code === hiddenInput.value) {
                item.classList.add('selected');
            }
            item.innerHTML = `${country.name} <span class="text-gray-500 text-xs">(${country.code})</span>`;
            item.addEventListener('click', function() {
                selectCountry(country);
            });
            countryList.appendChild(item);
        });
    }
    
    // Select country
    function selectCountry(country) {
        hiddenInput.value = country.code;
        display.textContent = `${country.name} (${country.code})`;
        menu.classList.add('hidden');
        searchInput.value = '';
        renderCountryList(countries);
        
        // Load cities and states for destination
        loadDestinationCities(country.code);
        loadDestinationStates(country.code);
        
        // Show notification
        showNotification(`Country changed to ${country.name}`, 'info');
        
        // Auto-apply if enabled
        if (autoApply) {
            debounceSearch();
        }
    }
}
// Setup origin country dropdown
function setupOriginCountryDropdown() {
    const trigger = document.getElementById('origin-country-dropdown-trigger');
    const menu = document.getElementById('origin-country-dropdown-menu');
    const searchInput = document.getElementById('origin-country-search');
    const countryList = document.getElementById('origin-country-list');
    const hiddenInput = document.getElementById('origin_country');
    const display = document.getElementById('selected-origin-country-display');
    
    if (!trigger || !menu || !searchInput || !countryList || !hiddenInput || !display) return;
    
    // Add "Any country" option
    const countriesWithAny = [{ code: '', name: 'Any country' }, ...countries];
    
    // Populate country list
    renderOriginCountryList(countriesWithAny);
    
    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        menu.classList.toggle('hidden');
        if (!menu.classList.contains('hidden')) {
            searchInput.focus();
        }
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const filtered = countriesWithAny.filter(c => 
            c.name.toLowerCase().includes(query) || 
            c.code.toLowerCase().includes(query)
        );
        renderOriginCountryList(filtered);
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
    
    // Render country list
    function renderOriginCountryList(countryArray) {
        countryList.innerHTML = '';
        
        countryArray.forEach(country => {
            const item = document.createElement('div');
            item.className = 'country-item';
            if (country.code === hiddenInput.value) {
                item.classList.add('selected');
            }
            item.innerHTML = country.code ? 
                `${country.name} <span class="text-gray-500 text-xs">(${country.code})</span>` :
                country.name;
            item.addEventListener('click', function() {
                selectOriginCountry(country);
            });
            countryList.appendChild(item);
        });
    }
    
    // Select country
    function selectOriginCountry(country) {
        hiddenInput.value = country.code;
        display.textContent = country.code ? `${country.name} (${country.code})` : country.name;
        display.classList.toggle('text-gray-400', !country.code);
        menu.classList.add('hidden');
        searchInput.value = '';
        renderOriginCountryList(countriesWithAny);
        
        // Load cities for origin
        if (country.code) {
            loadOriginCities(country.code);
            showNotification(`Origin country changed to ${country.name}`, 'info');
        } else {
            // Clear city selection
            const cityInput = document.getElementById('origin_city');
            const cityDisplay = document.getElementById('selected-origin-city-display');
            const cityList = document.getElementById('origin-city-list');
            
            if (cityInput) cityInput.value = '';
            if (cityDisplay) {
                cityDisplay.textContent = 'Any city';
                cityDisplay.classList.add('text-gray-400');
            }
            if (cityList) cityList.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">Select a country first</div>';
        }
        
        // Auto-apply if enabled
        if (autoApply) {
            debounceSearch();
        }
    }
}
// Load destination cities
function loadDestinationCities(countryCode) {
    const cities = citiesByCountry[countryCode] || [];
    const cityList = document.getElementById('dest-city-list');
    const hiddenInput = document.getElementById('dest_city');
    
    if (!cityList || !hiddenInput) return;
    
    cityList.innerHTML = '';
    
    if (cities.length === 0) {
        cityList.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No cities available</div>';
        return;
    }
    
    // Add "Any city" option
    const anyItem = document.createElement('div');
    anyItem.className = 'country-item';
    if (!hiddenInput.value) {
        anyItem.classList.add('selected');
    }
    anyItem.textContent = 'Any city';
    anyItem.addEventListener('click', function() {
        hiddenInput.value = '';
        const display = document.getElementById('selected-dest-city-display');
        if (display) {
            display.textContent = 'Any city';
            display.classList.add('text-gray-400');
        }
        const menu = document.getElementById('dest-city-dropdown-menu');
        if (menu) menu.classList.add('hidden');
        const search = document.getElementById('dest-city-search');
        if (search) search.value = '';
        loadDestinationCities(countryCode);
    });
    cityList.appendChild(anyItem);
    
    // Add cities
    cities.forEach(city => {
        const item = document.createElement('div');
        item.className = 'country-item';
        if (city === hiddenInput.value) {
            item.classList.add('selected');
        }
        item.textContent = city;
        item.addEventListener('click', function() {
            hiddenInput.value = city;
            const display = document.getElementById('selected-dest-city-display');
            if (display) {
                display.textContent = city;
                display.classList.remove('text-gray-400');
            }
            const menu = document.getElementById('dest-city-dropdown-menu');
            if (menu) menu.classList.add('hidden');
            const search = document.getElementById('dest-city-search');
            if (search) search.value = '';
            loadDestinationCities(countryCode);
            showNotification(`City changed to ${city}`, 'info');
            
            // Auto-apply if enabled
            if (autoApply) {
                debounceSearch();
            }
        });
        cityList.appendChild(item);
    });
}

// Load origin cities
function loadOriginCities(countryCode) {
    const cities = citiesByCountry[countryCode] || [];
    const cityList = document.getElementById('origin-city-list');
    const hiddenInput = document.getElementById('origin_city');
    
    if (!cityList || !hiddenInput) return;
    
    cityList.innerHTML = '';
    
    if (cities.length === 0) {
        cityList.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No cities available</div>';
        return;
    }
    
    // Add "Any city" option
    const anyItem = document.createElement('div');
    anyItem.className = 'country-item';
    if (!hiddenInput.value) {
        anyItem.classList.add('selected');
    }
    anyItem.textContent = 'Any city';
    anyItem.addEventListener('click', function() {
        hiddenInput.value = '';
        const display = document.getElementById('selected-origin-city-display');
        if (display) {
            display.textContent = 'Any city';
            display.classList.add('text-gray-400');
        }
        const menu = document.getElementById('origin-city-dropdown-menu');
        if (menu) menu.classList.add('hidden');
        const search = document.getElementById('origin-city-search');
        if (search) search.value = '';
        loadOriginCities(countryCode);
    });
    cityList.appendChild(anyItem);
    
    // Add cities
    cities.forEach(city => {
        const item = document.createElement('div');
        item.className = 'country-item';
        if (city === hiddenInput.value) {
            item.classList.add('selected');
        }
        item.textContent = city;
        item.addEventListener('click', function() {
            hiddenInput.value = city;
            const display = document.getElementById('selected-origin-city-display');
            if (display) {
                display.textContent = city;
                display.classList.remove('text-gray-400');
            }
            const menu = document.getElementById('origin-city-dropdown-menu');
            if (menu) menu.classList.add('hidden');
            const search = document.getElementById('origin-city-search');
            if (search) search.value = '';
            loadOriginCities(countryCode);
            showNotification(`Origin city changed to ${city}`, 'info');
            
            // Auto-apply if enabled
            if (autoApply) {
                debounceSearch();
            }
        });
        cityList.appendChild(item);
    });
}
// Setup destination city dropdown
function setupDestCityDropdown() {
    const trigger = document.getElementById('dest-city-dropdown-trigger');
    const menu = document.getElementById('dest-city-dropdown-menu');
    const searchInput = document.getElementById('dest-city-search');
    const cityList = document.getElementById('dest-city-list');
    
    if (!trigger || !menu || !searchInput || !cityList) return;
    
    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        const countryCode = document.getElementById('dest_country')?.value;
        if (countryCode) {
            menu.classList.toggle('hidden');
            if (!menu.classList.contains('hidden')) {
                searchInput.focus();
            }
        }
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const countryCode = document.getElementById('dest_country')?.value;
        const cities = citiesByCountry[countryCode] || [];
        const filtered = cities.filter(c => c.toLowerCase().includes(query));
        
        cityList.innerHTML = '';
        
        // Add "Any city" if matches
        if ('any city'.includes(query)) {
            const anyItem = document.createElement('div');
            anyItem.className = 'country-item';
            anyItem.textContent = 'Any city';
            anyItem.addEventListener('click', function() {
                const hiddenInput = document.getElementById('dest_city');
                const display = document.getElementById('selected-dest-city-display');
                if (hiddenInput) hiddenInput.value = '';
                if (display) {
                    display.textContent = 'Any city';
                    display.classList.add('text-gray-400');
                }
                menu.classList.add('hidden');
                searchInput.value = '';
                loadDestinationCities(countryCode);
            });
            cityList.appendChild(anyItem);
        }
        
        filtered.forEach(city => {
            const item = document.createElement('div');
            item.className = 'country-item';
            item.textContent = city;
            item.addEventListener('click', function() {
                const hiddenInput = document.getElementById('dest_city');
                const display = document.getElementById('selected-dest-city-display');
                if (hiddenInput) hiddenInput.value = city;
                if (display) {
                    display.textContent = city;
                    display.classList.remove('text-gray-400');
                }
                menu.classList.add('hidden');
                searchInput.value = '';
                loadDestinationCities(countryCode);
                
                // Auto-apply if enabled
                if (autoApply) {
                    debounceSearch();
                }
            });
            cityList.appendChild(item);
        });
        
        if (cityList.children.length === 0) {
            cityList.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No cities found</div>';
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
}

// Setup origin city dropdown
function setupOriginCityDropdown() {
    const trigger = document.getElementById('origin-city-dropdown-trigger');
    const menu = document.getElementById('origin-city-dropdown-menu');
    const searchInput = document.getElementById('origin-city-search');
    const cityList = document.getElementById('origin-city-list');
    
    if (!trigger || !menu || !searchInput || !cityList) return;
    
    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        const countryCode = document.getElementById('origin_country')?.value;
        if (countryCode) {
            menu.classList.toggle('hidden');
            if (!menu.classList.contains('hidden')) {
                searchInput.focus();
            }
        }
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const countryCode = document.getElementById('origin_country')?.value;
        const cities = citiesByCountry[countryCode] || [];
        const filtered = cities.filter(c => c.toLowerCase().includes(query));
        
        cityList.innerHTML = '';
        
        // Add "Any city" if matches
        if ('any city'.includes(query)) {
            const anyItem = document.createElement('div');
            anyItem.className = 'country-item';
            anyItem.textContent = 'Any city';
            anyItem.addEventListener('click', function() {
                const hiddenInput = document.getElementById('origin_city');
                const display = document.getElementById('selected-origin-city-display');
                if (hiddenInput) hiddenInput.value = '';
                if (display) {
                    display.textContent = 'Any city';
                    display.classList.add('text-gray-400');
                }
                menu.classList.add('hidden');
                searchInput.value = '';
                loadOriginCities(countryCode);
            });
            cityList.appendChild(anyItem);
        }
        
        filtered.forEach(city => {
            const item = document.createElement('div');
            item.className = 'country-item';
            item.textContent = city;
            item.addEventListener('click', function() {
                const hiddenInput = document.getElementById('origin_city');
                const display = document.getElementById('selected-origin-city-display');
                if (hiddenInput) hiddenInput.value = city;
                if (display) {
                    display.textContent = city;
                    display.classList.remove('text-gray-400');
                }
                menu.classList.add('hidden');
                searchInput.value = '';
                loadOriginCities(countryCode);
                
                // Auto-apply if enabled
                if (autoApply) {
                    debounceSearch();
                }
            });
            cityList.appendChild(item);
        });
        
        if (cityList.children.length === 0) {
            cityList.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No cities found</div>';
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
}
// Setup destination state dropdown
function setupDestStateDropdown() {
    const trigger = document.getElementById('dest-state-dropdown-trigger');
    const menu = document.getElementById('dest-state-dropdown-menu');
    const searchInput = document.getElementById('dest-state-search');
    const stateList = document.getElementById('dest-state-list');
    const hiddenInput = document.getElementById('dest_state');
    const display = document.getElementById('selected-dest-state-display');
    
    if (!trigger || !menu || !searchInput || !stateList || !hiddenInput || !display) return;
    
    // Toggle dropdown
    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        const countryCode = document.getElementById('dest_country')?.value;
        if (countryCode === 'US') {
            menu.classList.toggle('hidden');
            if (!menu.classList.contains('hidden')) {
                searchInput.focus();
            }
        }
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const filtered = usStates.filter(s => 
            s.name.toLowerCase().includes(query) || 
            s.code.toLowerCase().includes(query)
        );
        renderStateList(filtered);
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!trigger.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
    
    // Render state list
    function renderStateList(stateArray) {
        stateList.innerHTML = '';
        
        // Add "Any state" option
        const anyItem = document.createElement('div');
        anyItem.className = 'country-item';
        if (!hiddenInput.value) {
            anyItem.classList.add('selected');
        }
        anyItem.textContent = 'Any state';
        anyItem.addEventListener('click', function() {
            selectState({ code: '', name: 'Any state' });
        });
        stateList.appendChild(anyItem);
        
        stateArray.forEach(state => {
            const item = document.createElement('div');
            item.className = 'country-item';
            if (state.code === hiddenInput.value) {
                item.classList.add('selected');
            }
            item.innerHTML = `${state.name} <span class="text-gray-500 text-xs">(${state.code})</span>`;
            item.addEventListener('click', function() {
                selectState(state);
            });
            stateList.appendChild(item);
        });
    }
    
    // Select state
    function selectState(state) {
        hiddenInput.value = state.code;
        display.textContent = state.code ? `${state.name} (${state.code})` : state.name;
        display.classList.toggle('text-gray-400', !state.code);
        menu.classList.add('hidden');
        searchInput.value = '';
        renderStateList(usStates);
        
        if (state.code) {
            showNotification(`State changed to ${state.name}`, 'info');
        }
        
        // Auto-apply if enabled
        if (autoApply) {
            debounceSearch();
        }
    }
    
    // Initialize
    renderStateList(usStates);
}

// Load destination states
function loadDestinationStates(countryCode) {
    const stateList = document.getElementById('dest-state-list');
    const hiddenInput = document.getElementById('dest_state');
    const display = document.getElementById('selected-dest-state-display');
    const trigger = document.getElementById('dest-state-dropdown-trigger');
    
    if (!stateList || !hiddenInput || !display || !trigger) return;
    
    if (countryCode === 'US') {
        // Show state dropdown for US
        trigger.classList.remove('opacity-50', 'cursor-not-allowed');
        stateList.innerHTML = '';
        
        // Add "Any state" option
        const anyItem = document.createElement('div');
        anyItem.className = 'country-item selected';
        anyItem.textContent = 'Any state';
        stateList.appendChild(anyItem);
        
        // Add states
        usStates.forEach(state => {
            const item = document.createElement('div');
            item.className = 'country-item';
            item.innerHTML = `${state.name} <span class="text-gray-500 text-xs">(${state.code})</span>`;
            stateList.appendChild(item);
        });
    } else {
        // Hide/disable state dropdown for non-US
        trigger.classList.add('opacity-50', 'cursor-not-allowed');
        hiddenInput.value = '';
        display.textContent = 'Any state';
        display.classList.add('text-gray-400');
        stateList.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">Only available for US</div>';
    }
}
// Setup auto-apply toggle
function setupAutoApplyToggle() {
    const toggle = document.getElementById('auto-apply');
    if (!toggle) return;
    
    const slider = toggle.nextElementSibling?.querySelector('div');
    if (!slider) return;
    
    // Update visual state
    function updateToggle(enabled) {
        if (enabled) {
            slider.style.transform = 'translateX(16px)';
            slider.classList.remove('bg-gray-400');
            slider.classList.add('bg-purple-500');
        } else {
            slider.style.transform = 'translateX(0)';
            slider.classList.remove('bg-purple-500');
            slider.classList.add('bg-gray-400');
        }
    }
    
    // Click handler
    toggle.nextElementSibling.addEventListener('click', function() {
        autoApply = !autoApply;
        toggle.checked = autoApply;
        updateToggle(autoApply);
        
        showNotification(autoApply ? 'Auto-apply enabled' : 'Auto-apply disabled', 'info');
    });
    
    // Initialize
    updateToggle(autoApply);
}

// Setup filter change listeners
function setupFilterChangeListeners() {
    // Date inputs
    ['ship_from', 'ship_to', 'delivery_from', 'delivery_to', 'dest_zip'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('change', function() {
                if (autoApply) {
                    debounceSearch();
                }
            });
        }
    });
}

// Debounced search
function debounceSearch() {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    searchTimeout = setTimeout(() => {
        applyFiltersAuto();
    }, 800);
}

// Auto-apply filters (no notification)
function applyFiltersAuto() {
    const filters = gatherFilters();
    currentCursor = null;
    performSearch(filters);
}

// Gather filters from UI
function gatherFilters() {
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
    const originCountry = document.getElementById('origin_country')?.value?.trim()?.toUpperCase();
    const originCity = document.getElementById('origin_city')?.value?.trim()?.toUpperCase();
    if (originCountry) filters.origin_country = originCountry;
    if (originCity) filters.origin_city = originCity;
    
    // Destination
    const destCountry = document.getElementById('dest_country')?.value?.trim()?.toUpperCase();
    const destState = document.getElementById('dest_state')?.value?.trim()?.toUpperCase();
    const destCity = document.getElementById('dest_city')?.value?.trim()?.toUpperCase();
    const destZip = document.getElementById('dest_zip')?.value?.trim();
    if (destCountry) filters.dest_country = destCountry;
    if (destState) filters.dest_state = destState;
    if (destCity) filters.dest_city = destCity;
    if (destZip) filters.dest_zip = destZip;
    
    // Ship dates
    const shipFrom = document.getElementById('ship_from')?.value;
    const shipTo = document.getElementById('ship_to')?.value;
    if (shipFrom) filters.ship_from = shipFrom;
    if (shipTo) filters.ship_to = shipTo;
    
    // Delivery dates
    const deliveryFrom = document.getElementById('delivery_from')?.value;
    const deliveryTo = document.getElementById('delivery_to')?.value;
    if (deliveryFrom) filters.delivery_from = deliveryFrom;
    if (deliveryTo) filters.delivery_to = deliveryTo;
    
    return filters;
}

// Apply filters and search
function applyFilters() {
    const filters = gatherFilters();
    console.log('Filters:', filters);
    
    // Reset cursor for new search
    currentCursor = null;
    
    // Show notification
    showNotification('Applying filters...', 'info');
    
    // Perform search
    performSearch(filters);
}
// Perform search
async function performSearch(filters, append = false) {
    try {
        // Show loading state
        const tableBody = document.getElementById('results-table');
        if (!tableBody) return;
        
        if (!append) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-12">
                        <i class="fas fa-spinner fa-spin text-3xl text-purple-500"></i>
                        <div class="mt-3 text-gray-500">Searching...</div>
                    </td>
                </tr>
            `;
        }
        
        // Add cursor if appending
        if (append && currentCursor) {
            filters.cursor = currentCursor;
        }
        
        // Make API request
        const response = await fetch('api/search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(filters)
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Search failed');
        }
        
        // Update results
        displayResults(data.results, append);
        
        // Update cursor for pagination
        currentCursor = data.next_cursor;
        
        // Update load more button
        const loadMoreBtn = document.getElementById('load-more');
        if (loadMoreBtn) {
            if (currentCursor) {
                loadMoreBtn.classList.remove('hidden');
            } else {
                loadMoreBtn.classList.add('hidden');
            }
        }
        
        // Update result count
        const resultCount = document.getElementById('result-count');
        if (resultCount && data.total !== undefined) {
            resultCount.textContent = data.total >= 100 ? '100+ hasil' : `${data.total} hasil`;
        }
        
    } catch (error) {
        console.error('Search error:', error);
        
        const tableBody = document.getElementById('results-table');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                        <div class="mt-3 text-red-400">Error: ${error.message}</div>
                        <button onclick="applyFilters()" class="mt-2 text-purple-400 hover:text-purple-300 text-sm">Try again</button>
                    </td>
                </tr>
            `;
        }
    }
}

// Display search results
function displayResults(results, append = false) {
    const tableBody = document.getElementById('results-table');
    if (!tableBody) return;
    
    if (!append) {
        tableBody.innerHTML = '';
    }
    
    if (!results || results.length === 0) {
        if (!append) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-12">
                        <i class="fas fa-search text-3xl text-gray-500"></i>
                        <div class="mt-3 text-gray-500">No results found</div>
                        <div class="text-sm text-gray-600 mt-1">Try adjusting your filters</div>
                    </td>
                </tr>
            `;
        }
        return;
    }
    
    results.forEach(result => {
        const row = createResultRow(result);
        tableBody.appendChild(row);
    });
}

// Create result table row
function createResultRow(result) {
    const row = document.createElement('tr');
    row.className = 'border-b border-dark-500/30 hover:bg-dark-300/20 transition';
    
    const carrierBadgeClass = getCarrierBadgeClass(result.carrier);
    const statusBadgeClass = getStatusBadgeClass(result.status);
    const origin = formatLocation(result.origin);
    const destination = formatLocation(result.dest);
    const shipDate = result.ship_date ? new Date(result.ship_date).toLocaleDateString() : 'N/A';
    const weight = result.weight_grams ? `${(result.weight_grams / 1000).toFixed(2)} kg` : 'N/A';
    
    row.innerHTML = `
        <td class="py-3 px-4">
            <span class="inline-block px-2 py-1 text-xs font-medium rounded ${carrierBadgeClass}">
                ${result.carrier.toUpperCase()}
            </span>
        </td>
        <td class="py-3 px-4">
            <span class="status-badge ${statusBadgeClass}">
                ${formatStatus(result.status)}
            </span>
        </td>
        <td class="py-3 px-4 text-sm">${origin}</td>
        <td class="py-3 px-4 text-sm">${destination}</td>
        <td class="py-3 px-4 text-sm text-gray-400">${shipDate}</td>
        <td class="py-3 px-4 text-sm text-gray-400">${weight}</td>
        <td class="py-3 px-4 text-right">
            <button onclick="showRevealModal('${result.tn_id}', ${result.reveal_cost_credits})" 
                    class="bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">
                Get TN
            </button>
        </td>
    `;
    
    return row;
}
// Format location
function formatLocation(location) {
    if (!location) return 'Unknown';
    
    let result = location.city || 'Unknown';
    if (location.state) {
        result += `, ${location.state}`;
    }
    if (location.country) {
        result += `, ${location.country}`;
    }
    return result;
}

// Format status
function formatStatus(status) {
    const statusMap = {
        'pre-transit': 'Pra Kirim',
        'transit': 'Transit',
        'delivered': 'Terkirim'
    };
    return statusMap[status] || status;
}

// Get status badge class
function getStatusBadgeClass(status) {
    const classes = {
        'pre-transit': 'badge-pre-transit',
        'transit': 'badge-transit',
        'delivered': 'badge-delivered'
    };
    return classes[status] || 'badge-pre-transit';
}

// Get carrier badge class
function getCarrierBadgeClass(carrier) {
    const classes = {
        'fedex': 'bg-blue-500/20 text-blue-400',
        'ups': 'bg-green-500/20 text-green-400',
        'dhl': 'bg-yellow-500/20 text-yellow-400'
    };
    return classes[carrier] || 'bg-gray-500/20 text-gray-400';
}

// Load more results
function loadMore() {
    if (currentCursor) {
        const filters = gatherFilters();
        performSearch(filters, true);
    }
}

// Show reveal modal
function showRevealModal(tnId, cost) {
    currentTnId = tnId;
    
    const modalContent = document.getElementById('modal-content');
    if (!modalContent) return;
    
    modalContent.innerHTML = `
        <div class="flex items-center space-x-3 p-4 bg-dark-300 rounded-lg">
            <i class="fas fa-box text-purple-400 text-lg"></i>
            <div>
                <div class="font-medium">Tracking Number ID: ${tnId}</div>
                <div class="text-sm text-gray-400">Cost: ${cost} credit${cost > 1 ? 's' : ''}</div>
            </div>
        </div>
    `;
    
    const confirmBtn = document.getElementById('confirm-btn');
    if (confirmBtn) {
        confirmBtn.onclick = () => revealTrackingNumber(tnId);
    }
    
    const modal = document.getElementById('reveal-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

// Close modal
function closeModal() {
    const modal = document.getElementById('reveal-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    currentTnId = null;
}

// Reveal tracking number
async function revealTrackingNumber(tnId) {
    try {
        const modalContent = document.getElementById('modal-content');
        if (!modalContent) return;
        
        // Show loading in modal
        modalContent.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin text-2xl text-purple-500"></i>
                <div class="mt-2 text-gray-400">Revealing tracking number...</div>
            </div>
        `;
        
        const response = await fetch('api/reveal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ tn_id: tnId })
        });
        
        const data = await response.json();
        
        if (data.success && data.result) {
            const result = data.result;
            
            // Show success with tracking number
            modalContent.innerHTML = `
                <div class="space-y-4">
                    <div class="text-center">
                        <i class="fas fa-check-circle text-4xl text-green-500"></i>
                        <h4 class="text-lg font-semibold mt-2">Success!</h4>
                    </div>
                    <div class="bg-dark-300 rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-sm text-gray-400 mb-1">Tracking Number</div>
                            <div class="text-2xl font-mono font-bold text-white select-all">${result.tracking_number}</div>
                            <div class="text-sm text-gray-400 mt-2">${result.carrier.toUpperCase()}</div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 text-center">
                        Credits remaining: ${data.credits_remaining}
                    </div>
                </div>
            `;
            
            // Update user credits display
            const creditsDisplay = document.getElementById('credits-display');
            if (creditsDisplay) {
                creditsDisplay.textContent = data.credits_remaining;
            }
            
            // Change confirm button to close
            const confirmBtn = document.getElementById('confirm-btn');
            if (confirmBtn) {
                confirmBtn.textContent = 'Close';
                confirmBtn.onclick = closeModal;
            }
            
        } else {
            throw new Error(data.error || 'Failed to reveal tracking number');
        }
        
    } catch (error) {
        console.error('Reveal error:', error);
        
        const modalContent = document.getElementById('modal-content');
        if (modalContent) {
            modalContent.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                    <div class="mt-2 text-red-400">Error: ${error.message}</div>
                    <button onclick="revealTrackingNumber('${tnId}')" class="mt-3 text-purple-400 hover:text-purple-300 text-sm">
                        Try again
                    </button>
                </div>
            `;
        }
    }
}
// Reset filters
function resetFilters() {
    // Reset carrier selection
    selectedCarriers = ['all'];
    document.querySelectorAll('[data-type="carrier"]').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.value === 'all');
    });
    
    // Reset status selection
    selectedStatuses = [];
    document.querySelectorAll('[data-type="status"]').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Reset dropdowns
    const originCountry = document.getElementById('origin_country');
    const originCity = document.getElementById('origin_city');
    const destCountry = document.getElementById('dest_country');
    const destState = document.getElementById('dest_state');
    const destCity = document.getElementById('dest_city');
    const destZip = document.getElementById('dest_zip');
    
    if (originCountry) originCountry.value = '';
    if (originCity) originCity.value = '';
    if (destCountry) destCountry.value = 'US';
    if (destState) destState.value = '';
    if (destCity) destCity.value = '';
    if (destZip) destZip.value = '';
    
    // Reset displays
    const originCountryDisplay = document.getElementById('selected-origin-country-display');
    const originCityDisplay = document.getElementById('selected-origin-city-display');
    const countryDisplay = document.getElementById('selected-country-display');
    const destStateDisplay = document.getElementById('selected-dest-state-display');
    const destCityDisplay = document.getElementById('selected-dest-city-display');
    
    if (originCountryDisplay) {
        originCountryDisplay.textContent = 'Any country';
        originCountryDisplay.classList.add('text-gray-400');
    }
    if (originCityDisplay) {
        originCityDisplay.textContent = 'Any city';
        originCityDisplay.classList.add('text-gray-400');
    }
    if (countryDisplay) {
        countryDisplay.textContent = 'United States (US)';
    }
    if (destStateDisplay) {
        destStateDisplay.textContent = 'Any state';
        destStateDisplay.classList.add('text-gray-400');
    }
    if (destCityDisplay) {
        destCityDisplay.textContent = 'Any city';
        destCityDisplay.classList.add('text-gray-400');
    }
    
    // Reset dates
    const shipFrom = document.getElementById('ship_from');
    const shipTo = document.getElementById('ship_to');
    const deliveryFrom = document.getElementById('delivery_from');
    const deliveryTo = document.getElementById('delivery_to');
    
    if (shipFrom) shipFrom.value = '';
    if (shipTo) shipTo.value = '';
    if (deliveryFrom) deliveryFrom.value = '';
    if (deliveryTo) deliveryTo.value = '';
    
    // Load default data
    loadDestinationCities('US');
    loadDestinationStates('US');
    
    // Show notification
    showNotification('Filters reset', 'info');
    
    // Auto-apply if enabled
    if (autoApply) {
        setTimeout(() => {
            applyFiltersAuto();
        }, 500);
    }
}

// Show history modal
function showHistoryModal() {
    const modal = document.getElementById('history-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loadHistory();
    }
}

// Close history modal
function closeHistoryModal() {
    const modal = document.getElementById('history-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Load history
async function loadHistory() {
    try {
        const response = await fetch('api/account.php?history=true');
        const data = await response.json();
        
        if (data.success && data.history) {
            renderHistory(data.history);
        } else {
            const historyContent = document.getElementById('history-content');
            if (historyContent) {
                historyContent.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-circle text-3xl text-red-500"></i>
                        <div class="mt-3 text-gray-500">Failed to load history</div>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Failed to load history:', error);
        const historyContent = document.getElementById('history-content');
        if (historyContent) {
            historyContent.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-circle text-3xl text-red-500"></i>
                    <div class="mt-3 text-gray-500">Error loading history</div>
                </div>
            `;
        }
    }
}

// Render history
function renderHistory(history) {
    const container = document.getElementById('history-content');
    if (!container) return;
    
    if (history.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-history text-3xl text-gray-500"></i>
                <div class="mt-3 text-gray-500">No history found</div>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-400">
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Tracking Number</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Carrier</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Destination</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Revealed</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-400 uppercase">Credits</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    history.forEach(item => {
        const revealedDate = new Date(item.revealed_at).toLocaleDateString();
        html += `
            <tr class="border-b border-dark-500/30 hover:bg-dark-300/20">
                <td class="py-3 px-4 font-mono text-sm">${item.tracking_number}</td>
                <td class="py-3 px-4">
                    <span class="inline-block px-2 py-1 text-xs font-medium rounded ${getCarrierBadgeClass(item.carrier)}">
                        ${item.carrier.toUpperCase()}
                    </span>
                </td>
                <td class="py-3 px-4 text-sm">${formatDestination(item.dest)}</td>
                <td class="py-3 px-4 text-sm text-gray-400">${revealedDate}</td>
                <td class="py-3 px-4 text-sm">${item.credits_charged}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

// Format destination
function formatDestination(dest) {
    let result = dest.city || 'Unknown City';
    if (dest.state) {
        result += `, ${dest.state}`;
    }
    if (dest.country) {
        result += `, ${dest.country}`;
    }
    return result;
}

// Show notification
function showNotification(message, type = 'info') {
    const colors = {
        info: 'rgba(139, 92, 246, 0.9)',
        success: 'rgba(34, 197, 94, 0.9)',
        error: 'rgba(239, 68, 68, 0.9)'
    };
    
    const notification = document.createElement('div');
    notification.className = 'badge-info';
    notification.style.background = colors[type];
    notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span class="text-sm font-medium">${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transition = 'all 0.3s ease-out';
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}