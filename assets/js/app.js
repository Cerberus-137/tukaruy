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
    'US': ['NEW YORK', 'LOS ANGELES', 'CHICAGO', 'HOUSTON', 'PHOENIX',
           'PHILADELPHIA', 'SAN ANTONIO', 'SAN DIEGO', 'DALLAS', 'SAN JOSE',
           'AUSTIN', 'JACKSONVILLE', 'FORT WORTH', 'COLUMBUS', 'CHARLOTTE',
           'SAN FRANCISCO', 'INDIANAPOLIS', 'SEATTLE', 'DENVER', 'WASHINGTON',
           'BOSTON', 'EL PASO', 'NASHVILLE', 'DETROIT', 'OKLAHOMA CITY',
           'PORTLAND', 'LAS VEGAS', 'MEMPHIS', 'LOUISVILLE', 'BALTIMORE',
           'MILWAUKEE', 'ALBUQUERQUE', 'TUCSON', 'FRESNO', 'MESA',
           'SACRAMENTO', 'ATLANTA', 'KANSAS CITY', 'COLORADO SPRINGS', 'MIAMI',
           'RALEIGH', 'OMAHA', 'LONG BEACH', 'VIRGINIA BEACH', 'OAKLAND'],
    'GB': ['LONDON', 'BIRMINGHAM', 'MANCHESTER', 'LEEDS', 'GLASGOW', 'LIVERPOOL', 'NEWCASTLE',
           'SHEFFIELD', 'BRISTOL', 'EDINBURGH', 'LEICESTER', 'NOTTINGHAM', 'COVENTRY', 'HULL',
           'BRADFORD', 'CARDIFF', 'BELFAST', 'STOKE-ON-TRENT', 'WOLVERHAMPTON', 'PLYMOUTH',
           'DERBY', 'SOUTHAMPTON', 'PORTSMOUTH', 'BRIGHTON', 'READING'],
    'AU': ['SYDNEY', 'MELBOURNE', 'BRISBANE', 'PERTH', 'ADELAIDE',
           'GOLD COAST', 'NEWCASTLE', 'CANBERRA', 'SUNSHINE COAST',
           'WOLLONGONG', 'HOBART', 'GEELONG', 'TOWNSVILLE', 'CAIRNS',
           'DARWIN', 'TOOWOOMBA', 'BALLARAT', 'BENDIGO', 'LAUNCESTON'],
    'CA': ['TORONTO', 'MONTREAL', 'VANCOUVER', 'CALGARY', 'EDMONTON',
           'OTTAWA', 'WINNIPEG', 'QUEBEC CITY', 'HAMILTON', 'KITCHENER',
           'LONDON', 'VICTORIA', 'HALIFAX', 'OSHAWA', 'WINDSOR',
           'SASKATOON', 'REGINA', 'ST. JOHN\'S', 'KELOWNA', 'BARRIE'],
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
           'TOULON', 'GRENOBLE', 'DIJON', 'NÎMES', 'ANGERS', 'VILLEURBANNE'],
    'CN': ['SHANGHAI', 'BEIJING', 'GUANGZHOU', 'SHENZHEN', 'CHENGDU', 'TIANJIN', 'WUHAN',
           'HANGZHOU', 'NANJING', 'XI\'AN', 'CHONGQING', 'QINGDAO', 'SHENYANG', 'HARBIN'],
    'KR': ['SEOUL', 'BUSAN', 'INCHEON', 'DAEGU', 'DAEJEON', 'GWANGJU', 'SUWON', 'ULSAN'],
    'IN': ['MUMBAI', 'DELHI', 'BANGALORE', 'HYDERABAD', 'AHMEDABAD', 'CHENNAI', 'KOLKATA',
           'PUNE', 'JAIPUR', 'LUCKNOW', 'KANPUR', 'NAGPUR', 'INDORE', 'BHOPAL'],
    'BR': ['SÃO PAULO', 'RIO DE JANEIRO', 'BRASÍLIA', 'SALVADOR', 'FORTALEZA', 'BELO HORIZONTE',
           'MANAUS', 'CURITIBA', 'RECIFE', 'PORTO ALEGRE', 'BELÉM', 'GOIÂNIA'],
    'MX': ['MEXICO CITY', 'GUADALAJARA', 'MONTERREY', 'PUEBLA', 'TIJUANA', 'LEÓN',
           'JUÁREZ', 'ZAPOPAN', 'MÉRIDA', 'CANCÚN', 'AGUASCALIENTES'],
    'IT': ['ROME', 'MILAN', 'NAPLES', 'TURIN', 'PALERMO', 'GENOA', 'BOLOGNA', 'FLORENCE',
           'VENICE', 'VERONA', 'CATANIA', 'BARI', 'MESSINA'],
    'ES': ['MADRID', 'BARCELONA', 'VALENCIA', 'SEVILLE', 'ZARAGOZA', 'MÁLAGA', 'MURCIA',
           'PALMA', 'LAS PALMAS', 'BILBAO', 'ALICANTE', 'CÓRDOBA'],
    'NL': ['AMSTERDAM', 'ROTTERDAM', 'THE HAGUE', 'UTRECHT', 'EINDHOVEN', 'TILBURG',
           'GRONINGEN', 'ALMERE', 'BREDA', 'NIJMEGEN'],
    'AE': ['DUBAI', 'ABU DHABI', 'SHARJAH', 'AL AIN', 'AJMAN', 'RAS AL KHAIMAH'],
    'SA': ['RIYADH', 'JEDDAH', 'MECCA', 'MEDINA', 'DAMMAM', 'KHOBAR', 'TABUK'],
    'ZA': ['JOHANNESBURG', 'CAPE TOWN', 'DURBAN', 'PRETORIA', 'PORT ELIZABETH', 'BLOEMFONTEIN']
};
// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Tukeruy Track Page Initializing ===');
    
    try {
        setupFilterButtons();
        console.log('✓ Filter buttons setup');
    } catch (e) {
        console.error('✗ Filter buttons error:', e);
    }
    
    try {
        setDefaultStatus();
        console.log('✓ Default status set');
    } catch (e) {
        console.error('✗ Default status error:', e);
    }
    
    try {
        setupCountryDropdown();
        console.log('✓ Country dropdown setup');
    } catch (e) {
        console.error('✗ Country dropdown error:', e);
    }
    
    try {
        setupOriginCountryDropdown();
        console.log('✓ Origin country dropdown setup');
    } catch (e) {
        console.error('✗ Origin country dropdown error:', e);
    }
    
    try {
        setupDestCityDropdown();
        console.log('✓ Dest city dropdown setup');
    } catch (e) {
        console.error('✗ Dest city dropdown error:', e);
    }
    
    try {
        setupOriginCityDropdown();
        console.log('✓ Origin city dropdown setup');
    } catch (e) {
        console.error('✗ Origin city dropdown error:', e);
    }
    
    try {
        setupDestStateDropdown();
        console.log('✓ Dest state dropdown setup');
    } catch (e) {
        console.error('✗ Dest state dropdown error:', e);
    }
    
    try {
        setupUserMenu();
        console.log('✓ User menu setup');
    } catch (e) {
        console.error('✗ User menu error:', e);
    }
    
    try {
        setupAutoApplyToggle();
        console.log('✓ Auto-apply toggle setup');
    } catch (e) {
        console.error('✗ Auto-apply toggle error:', e);
    }
    
    try {
        setupFilterChangeListeners();
        console.log('✓ Filter change listeners setup');
    } catch (e) {
        console.error('✗ Filter change listeners error:', e);
    }
    
    try {
        setupShipDatePicker();
        console.log('✓ Ship date picker setup');
    } catch (e) {
        console.error('✗ Ship date picker error:', e);
    }
    
    // Add click event for ship date trigger button - FIXED
    try {
        const shipDateTrigger = document.getElementById('ship-date-trigger');
        if (shipDateTrigger) {
            console.log('Ship date trigger found, attaching event listener');
            shipDateTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Ship date trigger clicked!');
                toggleShipDateCalendar();
                return false;
            }, { capture: true }); // Use capture phase to catch event early
            
            // Also handle mousedown as backup
            shipDateTrigger.addEventListener('mousedown', function(e) {
                console.log('Ship date trigger mousedown');
            });
            console.log('✓ Ship date trigger event listeners attached');
        } else {
            console.error('✗ Ship date trigger button not found!');
        }
    } catch (e) {
        console.error('✗ Ship date trigger setup error:', e);
    }
    
    console.log('=== Tukeruy Track Page Initialized ===');
    
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
        const response = await fetch('api/stats');
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
    document.querySelectorAll('.filter-btn, .segmented-btn').forEach(btn => {
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

// Set default status to pre-transit on page load
function setDefaultStatus() {
    const preTransitBtn = document.querySelector('[data-type="status"][data-value="pre-transit"]');
    if (preTransitBtn && !preTransitBtn.classList.contains('active')) {
        preTransitBtn.classList.add('active');
        selectedStatuses = ['pre-transit'];
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
    const trigger = document.getElementById('dest-city-dropdown-trigger');
    
    if (!cityList || !hiddenInput || !trigger) return;
    
    // Clear existing content
    cityList.innerHTML = '';
    
    if (cities.length === 0) {
        cityList.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No cities available for this country</div>';
        trigger.classList.add('opacity-50', 'cursor-not-allowed');
        trigger.style.pointerEvents = 'none';
        return;
    }
    
    // Enable trigger
    trigger.classList.remove('opacity-50', 'cursor-not-allowed');
    trigger.style.pointerEvents = 'auto';
    
    // Add "Any city" option
    const anyItem = document.createElement('div');
    anyItem.className = 'country-item';
    if (!hiddenInput.value) {
        anyItem.classList.add('selected');
    }
    anyItem.textContent = 'Any city';
    anyItem.addEventListener('click', function(e) {
        e.stopPropagation();
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
        
        // Auto-apply if enabled
        if (autoApply) {
            debounceSearch();
        }
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
        item.addEventListener('click', function(e) {
            e.stopPropagation();
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
            showNotification(`Destination city changed to ${city}`, 'info');
            
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
    const trigger = document.getElementById('origin-city-dropdown-trigger');
    
    if (!cityList || !hiddenInput || !trigger) return;
    
    // Clear existing content
    cityList.innerHTML = '';
    
    if (cities.length === 0) {
        cityList.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">No cities available for this country</div>';
        trigger.classList.add('opacity-50', 'cursor-not-allowed');
        trigger.style.pointerEvents = 'none';
        return;
    }
    
    // Enable trigger
    trigger.classList.remove('opacity-50', 'cursor-not-allowed');
    trigger.style.pointerEvents = 'auto';
    
    // Add "Any city" option
    const anyItem = document.createElement('div');
    anyItem.className = 'country-item';
    if (!hiddenInput.value) {
        anyItem.classList.add('selected');
    }
    anyItem.textContent = 'Any city';
    anyItem.addEventListener('click', function(e) {
        e.stopPropagation();
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
        
        // Auto-apply if enabled
        if (autoApply) {
            debounceSearch();
        }
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
        item.addEventListener('click', function(e) {
            e.stopPropagation();
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

// Setup user menu - close on click and outside click
function setupUserMenu() {
    // Add small delay to ensure DOM is ready and inline scripts don't conflict
    setTimeout(() => {
        const userMenuBtn = document.getElementById('user-menu-btn');
        const userMenu = document.getElementById('user-menu');
        const userMenuLinks = document.querySelectorAll('.user-menu-link');
        
        console.log('🔧 setupUserMenu:', {
            btn: !!userMenuBtn,
            menu: !!userMenu,
            links: userMenuLinks.length
        });
        
        if (!userMenuBtn || !userMenu) {
            console.warn('⚠️ User menu elements not found');
            return;
        }
        
        // Remove any existing listeners first (in case of duplicates)
        const newBtn = userMenuBtn.cloneNode(true);
        userMenuBtn.parentNode.replaceChild(newBtn, userMenuBtn);
        
        // Get fresh reference
        const freshBtn = document.getElementById('user-menu-btn');
        const freshMenu = document.getElementById('user-menu');
        const freshLinks = document.querySelectorAll('.user-menu-link');
        
        // Toggle menu on button click
        freshBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            freshMenu.classList.toggle('hidden');
            console.log('👤 User menu toggled:', freshMenu.classList.contains('hidden') ? 'closed' : 'open');
        });
        
        // Close menu when clicking on a link
        freshLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Let the link navigate, but close the menu
                setTimeout(() => {
                    freshMenu.classList.add('hidden');
                    console.log('👤 User menu closed (link clicked)');
                }, 50);
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!freshBtn.contains(e.target) && !freshMenu.contains(e.target)) {
                freshMenu.classList.add('hidden');
            }
        });
        
        console.log('✅ User menu setup complete');
    }, 100);
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
    
    // Load available ship dates
    loadAvailableShipDates();
}

// Load available ship dates from API
async function loadAvailableShipDates() {
    try {
        const response = await fetch('api/ship-dates', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.dates) {
            // Store available dates in window for use in date picker
            window.availableShipDates = data.dates.map(d => d.date);
            console.log('Available ship dates loaded:', window.availableShipDates.length, 'dates');
            
            // You can update date pickers to highlight available dates
            // Or show counts next to dates (implement as needed)
        }
    } catch (error) {
        console.warn('Failed to load available ship dates:', error);
    }
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
        console.log('🔍 Performing search with filters:', filters);
        
        // Show loading state
        const tableBody = document.getElementById('results-table');
        if (!tableBody) {
            console.error('❌ Results table not found!');
            return;
        }
        
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
        console.log('📡 Sending request to api/search');
        const response = await fetch('api/search', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(filters)
        });
        
        console.log('📥 Response status:', response.status);
        console.log('📥 Response content-type:', response.headers.get('content-type'));
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('❌ Non-JSON response received:', text.substring(0, 500));
            
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-12">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                        <div class="mt-3 text-red-400 font-semibold">Server Error</div>
                        <div class="text-sm text-gray-500 mt-2">API returned HTML instead of JSON</div>
                        <div class="text-xs text-gray-600 mt-2 max-w-md mx-auto">
                            This usually means:<br>
                            1. The API endpoint is not being reached<br>
                            2. There's a PHP error in the API file<br>
                            3. .htaccess is redirecting incorrectly
                        </div>
                        <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded text-sm">
                            Refresh Page
                        </button>
                    </td>
                </tr>
            `;
            return;
        }
        
        const data = await response.json();
        console.log('✅ API Response received:', data);
        
        // Debug: Log the API response
        if (data.results && data.results.length > 0) {
            console.log('📊 Sample result:', data.results[0]);
        }
        
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
            resultCount.textContent = data.total >= 100 ? '~100+ matches' : `~${data.total} matches`;
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
    row.className = 'border-b border-dark-400/50 hover:bg-dark-300/30 transition';
    
    const carrierBadgeClass = getCarrierBadgeClass(result.carrier);
    const statusBadgeClass = getStatusBadgeClass(result.status);
    
    // Format origin - API may omit origin entirely if not available
    let origin = 'N/A';
    if (result.origin && typeof result.origin === 'object') {
        const originParts = [];
        if (result.origin.city) originParts.push(result.origin.city);
        if (result.origin.state) originParts.push(result.origin.state);
        if (result.origin.country) originParts.push(result.origin.country);
        if (originParts.length > 0) {
            origin = originParts.join(', ');
        }
    }
    
    // Format destination - should always be present according to API
    let destination = 'N/A';
    if (result.dest && typeof result.dest === 'object') {
        const destParts = [];
        if (result.dest.city) destParts.push(result.dest.city);
        if (result.dest.state) destParts.push(result.dest.state);
        if (result.dest.country) destParts.push(result.dest.country);
        if (destParts.length > 0) {
            destination = destParts.join(', ');
        }
    }
    
    // Format ship date
    let shipDate = 'N/A';
    if (result.ship_date) {
        try {
            const date = new Date(result.ship_date);
            shipDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        } catch (e) {
            shipDate = result.ship_date;
        }
    }
    
    // Format delivery date
    let deliveryDate = 'N/A';
    if (result.est_delivery) {
        try {
            const date = new Date(result.est_delivery);
            deliveryDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        } catch (e) {
            deliveryDate = result.est_delivery;
        }
    }
    
    // Format weight - API provides weight in grams
    let weight = 'N/A';
    if (result.weight_grams && result.weight_grams > 0) {
        // Convert grams to pounds (1 lb = 453.592 grams)
        const lbs = (result.weight_grams / 453.592).toFixed(2);
        weight = `${lbs} lbs`;
    }
    
    // Get carrier name
    const carrierName = result.carrier ? result.carrier.toUpperCase() : 'N/A';
    
    // Build match explanation (why this tracking is in results)
    let matchExplanation = [];
    if (shipDate !== 'N/A') matchExplanation.push(`Ship: ${shipDate}`);
    if (deliveryDate !== 'N/A') matchExplanation.push(`Est. Delivery: ${deliveryDate}`);
    const matchText = matchExplanation.length > 0 ? matchExplanation.join(' | ') : 'Matches filter criteria';
    
    row.innerHTML = `
        <td class="py-4 px-4">
            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md ${carrierBadgeClass}">
                ${carrierName}
            </span>
        </td>
        <td class="py-4 px-4">
            <span class="status-badge ${statusBadgeClass}">
                ${formatStatus(result.status)}
            </span>
        </td>
        <td class="py-4 px-4">
            <div class="text-sm text-gray-300">${origin}</div>
        </td>
        <td class="py-4 px-4">
            <div class="text-sm text-gray-300">${destination}</div>
        </td>
        <td class="py-4 px-4">
            <div class="text-sm text-gray-400">${shipDate}</div>
            <div class="text-xs text-gray-500 mt-1">Ship</div>
        </td>
        <td class="py-4 px-4">
            <div class="text-sm text-gray-400">${deliveryDate}</div>
            <div class="text-xs text-gray-500 mt-1">Est. Delivery</div>
        </td>
        <td class="py-4 px-4">
            <div class="text-sm text-gray-400">${weight}</div>
        </td>
        <td class="py-4 px-4">
            <div class="text-xs text-purple-300 mb-2">${matchText}</div>
        </td>
        <td class="py-4 px-4 text-right">
            <button onclick="showRevealModal('${result.tn_id}', ${result.reveal_cost_credits || 1})" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                Get TN
            </button>
        </td>
    `;
    
    return row;
}

// Format location (kept for backward compatibility but not used in createResultRow)
function formatLocation(location) {
    if (!location || typeof location !== 'object') return 'N/A';
    
    const parts = [];
    
    if (location.city) parts.push(location.city);
    if (location.state) parts.push(location.state);
    if (location.country) parts.push(location.country);
    
    return parts.length > 0 ? parts.join(', ') : 'N/A';
}

// Format status
function formatStatus(status) {
    const statusMap = {
        'pre-transit': 'Pre Transit',
        'transit': 'Transit',
        'delivered': 'Delivered'
    };
    
    // Log warning if status is missing
    if (!status) {
        console.warn('Missing status in result, defaulting to pre-transit');
        return 'Pre Transit';
    }
    
    return statusMap[status] || status;
}

// Get status badge class
function getStatusBadgeClass(status) {
    const classes = {
        'pre-transit': 'badge-pre-transit',
        'transit': 'badge-transit',
        'delivered': 'badge-delivered'
    };
    // Default to pre-transit if status is missing or unknown
    const defaultStatus = 'badge-pre-transit';
    return status && classes[status] ? classes[status] : defaultStatus;
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

// Generate carrier-specific tracking link
function generateCarrierLink(carrier, trackingNumber) {
    if (!carrier || !trackingNumber) return '';
    
    const carrierLower = carrier.toLowerCase();
    let trackingUrl = '';
    
    if (carrierLower === 'fedex') {
        trackingUrl = `https://www.fedex.com/wtrk/track/?trknbr=${trackingNumber}`;
    } else if (carrierLower === 'dhl') {
        trackingUrl = `https://www.dhl.com/en/en/home/tracking.html?tracking-id=${trackingNumber}`;
    } else if (carrierLower === 'ups') {
        trackingUrl = `https://www.ups.com/track?tracknum=${trackingNumber}`;
    }
    
    if (!trackingUrl) return '';
    
    return `<a href="${trackingUrl}" target="_blank" class="inline-flex items-center px-4 py-1.5 text-sm font-semibold rounded-md bg-blue-500/20 text-blue-300 hover:bg-blue-500/30 transition">
        <i class="fas fa-external-link-alt mr-2"></i>Track
    </a>`;
}

// Load more results
function loadMore() {
    if (currentCursor) {
        const filters = gatherFilters();
        performSearch(filters, true);
    }
}

// Show reveal modal - NEW VERSION with loading state
function showRevealModal(tnId, cost) {
    currentTnId = tnId;
    
    const modalContent = document.getElementById('modal-content');
    if (!modalContent) return;
    
    // Show loading state while fetching shipment details
    modalContent.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-3xl text-purple-500"></i>
            <div class="mt-3 text-gray-400">Loading shipment details...</div>
        </div>
    `;
    
    const modal = document.getElementById('reveal-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    // Fetch tracking number details FIRST before showing modal
    revealTrackingNumber(tnId);
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

// Reveal tracking number - ENHANCED VERSION with full shipment details
async function revealTrackingNumber(tnId) {
    try {
        const modalContent = document.getElementById('modal-content');
        if (!modalContent) return;
        
        const response = await fetch('api/reveal', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ tn_id: tnId })
        });
        
        const data = await response.json();
        
        if (data.success && data.result) {
            const result = data.result;
            
            // Format dates
            let shipDate = 'N/A';
            if (result.ship_date) {
                try {
                    const date = new Date(result.ship_date);
                    shipDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                } catch (e) {
                    shipDate = result.ship_date;
                }
            }
            
            let deliveryDate = 'N/A';
            if (result.delivery_date) {
                try {
                    const date = new Date(result.delivery_date);
                    deliveryDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                } catch (e) {
                    deliveryDate = result.delivery_date;
                }
            }
            
            // Format origin
            let origin = 'N/A';
            if (result.origin && typeof result.origin === 'object') {
                const originParts = [];
                if (result.origin.city) originParts.push(result.origin.city);
                if (result.origin.state) originParts.push(result.origin.state);
                if (result.origin.country) originParts.push(result.origin.country);
                if (originParts.length > 0) {
                    origin = originParts.join(', ');
                }
            }
            
            // Format destination
            let destination = 'N/A';
            if (result.dest && typeof result.dest === 'object') {
                const destParts = [];
                if (result.dest.city) destParts.push(result.dest.city);
                if (result.dest.state) destParts.push(result.dest.state);
                if (result.dest.country) destParts.push(result.dest.country);
                if (destParts.length > 0) {
                    destination = destParts.join(', ');
                }
            }
            
            // Format weight
            let weight = 'N/A';
            if (result.weight_grams && result.weight_grams > 0) {
                const lbs = (result.weight_grams / 453.592).toFixed(2);
                weight = `${lbs} lbs`;
            }
            
            // Get carrier badge class
            const carrierBadgeClass = getCarrierBadgeClass(result.carrier);
            const statusBadgeClass = getStatusBadgeClass(result.status);
            
            // Show detailed shipment information with tracking number
            modalContent.innerHTML = `
                <div class="space-y-4">
                    <!-- Header -->
                    <div class="text-center border-b border-gray-700 pb-4">
                        <div class="inline-flex items-center space-x-2 mb-2">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            <h4 class="text-lg font-semibold">Tracking Number Revealed</h4>
                        </div>
                    </div>
                    
                    <!-- Tracking Number -->
                    <div class="bg-gradient-to-br from-purple-500/10 to-blue-500/10 border border-purple-500/30 rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-xs text-gray-400 mb-1">TRACKING NUMBER</div>
                            <div class="text-2xl font-mono font-bold text-white select-all mb-2">${result.tracking_number}</div>
                            <div class="flex items-center justify-center gap-3 flex-wrap">
                                <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-md ${carrierBadgeClass}">
                                    ${result.carrier ? result.carrier.toUpperCase() : 'N/A'}
                                </span>
                                <!-- Carrier Tracking Links -->
                                ${generateCarrierLink(result.carrier, result.tracking_number)}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipment Timeline -->
                    <div class="bg-dark-300 rounded-lg p-4 mb-4">
                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-gray-400 uppercase font-semibold">Shipment</span>
                                <span class="text-xs text-gray-500">${shipDate} → ${deliveryDate}</span>
                            </div>
                            <div class="relative h-2 bg-dark-400 rounded-full overflow-hidden">
                                <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width: 40%; opacity: 0.8;"></div>
                                <div class="absolute top-0 right-0 h-full w-1 bg-yellow-500 rounded-full opacity: 0.8;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipment Details -->
                    <div class="bg-dark-300 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-700">
                            <span class="text-sm text-gray-400">Status</span>
                            <span class="status-badge ${statusBadgeClass}">
                                ${formatStatus(result.status)}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">Origin</span>
                            <span class="text-sm text-white font-medium text-right">${origin}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">Destination</span>
                            <span class="text-sm text-white font-medium text-right">${destination}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">Ship Date</span>
                            <span class="text-sm text-white">${shipDate}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">Delivery Date</span>
                            <span class="text-sm text-white">${deliveryDate}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">Weight</span>
                            <span class="text-sm text-white">${weight}</span>
                        </div>
                    </div>
                    
                    <!-- Credits Info -->
                    <div class="text-center pt-2">
                        <div class="inline-flex items-center space-x-2 text-xs text-gray-400">
                            <i class="fas fa-ticket"></i>
                            <span>Credits remaining: <span class="text-white font-semibold">${data.credits_remaining}</span></span>
                        </div>
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
                confirmBtn.className = 'w-full bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 text-white font-medium py-3 rounded-lg transition';
                confirmBtn.onclick = closeModal;
            }
            
            // Hide cancel button
            const cancelBtn = document.getElementById('cancel-btn');
            if (cancelBtn) {
                cancelBtn.style.display = 'none';
            }
            
        } else {
            throw new Error(data.error || 'Failed to reveal tracking number');
        }
        
    } catch (error) {
        console.error('Reveal error:', error);
        
        const modalContent = document.getElementById('modal-content');
        if (modalContent) {
            modalContent.innerHTML = `
                <div class="text-center py-6">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-500"></i>
                    <div class="mt-3 text-lg font-semibold text-red-400">Error</div>
                    <div class="mt-2 text-sm text-gray-400">${error.message}</div>
                    <button onclick="showRevealModal('${tnId}', 1)" class="mt-4 px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white text-sm font-medium rounded-lg transition">
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
        const response = await fetch('api/account?history=true');
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


// Setup Ship Date Picker - Simple Date Range Input
function setupShipDatePicker() {
    console.log('🚀 Setting up ship date range picker...');
    
    // Get input elements
    const fromInput = document.getElementById('ship-date-from');
    const toInput = document.getElementById('ship-date-to');
    
    if (!fromInput || !toInput) {
        console.error('❌ Date range inputs not found');
        return;
    }
    
    // Get today's date
    const today = new Date();
    const todayStr = formatDateForInput(today);
    
    // Set min dates
    fromInput.min = todayStr;
    toInput.min = todayStr;
    
    // Event listeners
    fromInput.addEventListener('change', function() {
        console.log('📅 From date changed:', this.value);
        toInput.min = this.value || todayStr;
    });
    
    toInput.addEventListener('change', function() {
        console.log('📅 To date changed:', this.value);
    });
    
    console.log('✅ Ship date range picker ready');
}

// Format date for HTML input
function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Toggle Ship Date Calendar Modal
window.toggleShipDateCalendar = function() {
    const modal = document.getElementById('ship-date-calendar-modal');
    if (modal.classList.contains('hidden')) {
        // Get current values
        const shipFrom = document.getElementById('ship_from').value;
        const shipTo = document.getElementById('ship_to').value;
        
        // Set current values in inputs
        const fromInput = document.getElementById('ship-date-from');
        const toInput = document.getElementById('ship-date-to');
        
        if (shipFrom) fromInput.value = shipFrom;
        if (shipTo) toInput.value = shipTo;
        
        // Update display
        if (shipFrom && shipTo) {
            updateCalendarSelectedRange([new Date(shipFrom), new Date(shipTo)]);
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    } else {
        closeShipDateCalendar();
    }
};

// Close Ship Date Calendar Modal
window.closeShipDateCalendar = function() {
    const modal = document.getElementById('ship-date-calendar-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
};

// Update selected range display
function updateCalendarSelectedRange(selectedDates) {
    const display = document.getElementById('calendar-selected-range');
    if (!display) return;
    
    if (selectedDates && selectedDates.length === 2) {
        const formatDate = (date) => {
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        };
        display.textContent = `Selected: ${formatDate(selectedDates[0])} → ${formatDate(selectedDates[1])}`;
    } else if (selectedDates && selectedDates.length === 1) {
        const formatDate = (date) => {
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        };
        display.textContent = `Selected: ${formatDate(selectedDates[0])}`;
    } else {
        display.textContent = 'No date selected';
    }
}

// Apply Ship Date Range from modal
window.applyShipDateRange = function() {
    const fromInput = document.getElementById('ship-date-from');
    const toInput = document.getElementById('ship-date-to');
    
    if (!fromInput.value || !toInput.value) {
        alert('Please select both start and end dates');
        return;
    }
    
    const shipFrom = document.getElementById('ship_from');
    const shipTo = document.getElementById('ship_to');
    const display = document.getElementById('selected-ship-date-display');
    
    // Parse dates
    const fromDate = new Date(fromInput.value);
    const toDate = new Date(toInput.value);
    
    if (fromDate > toDate) {
        alert('Start date must be before end date');
        return;
    }
    
    // Update hidden inputs
    shipFrom.value = fromInput.value;
    shipTo.value = toInput.value;
    
    // Format for display
    const formatDate = (dateStr) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };
    
    // Update display
    const fromFormatted = formatDate(fromInput.value);
    const toFormatted = formatDate(toInput.value);
    display.textContent = `${fromFormatted} - ${toFormatted}`;
    display.classList.remove('text-gray-400');
    display.classList.add('text-white');
    
    // Close modal
    closeShipDateCalendar();
    
    // Show notification
    showNotification(`Ship date range: ${fromFormatted} - ${toFormatted}`, 'info');
    
    console.log('✅ Ship date range applied:', fromInput.value, '-', toInput.value);
    
    // Auto-apply if enabled
    if (autoApply) {
        debounceSearch();
    }
};

// Quick date presets
window.selectQuickDate = function(preset) {
    const today = new Date();
    let startDate, endDate;
    
    switch(preset) {
        case 'today':
            startDate = new Date(today);
            endDate = new Date(today);
            break;
        case 'yesterday':
            startDate = new Date(today);
            startDate.setDate(startDate.getDate() - 1);
            endDate = new Date(startDate);
            break;
        case 'last7days':
            endDate = new Date(today);
            startDate = new Date(today);
            startDate.setDate(startDate.getDate() - 6);
            break;
        case 'last30days':
            endDate = new Date(today);
            startDate = new Date(today);
            startDate.setDate(startDate.getDate() - 29);
            break;
        case 'thismonth':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = new Date(today);
            break;
        case 'lastmonth':
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        default:
            return;
    }
    
    // Set dates in calendar
    shipDatePicker.setDate([startDate, endDate], false);
    tempSelectedDates = [startDate, endDate];
    updateCalendarSelectedRange(tempSelectedDates);
};



// Format count for display (e.g., 1234 -> 1.2k)
function formatCount(count) {
    if (count >= 1000) {
        return (count / 1000).toFixed(1) + 'k';
    }
    return count.toString();
}

// Clear ship date range
window.clearShipDateRange = function() {
    const shipFrom = document.getElementById('ship_from');
    const shipTo = document.getElementById('ship_to');
    const display = document.getElementById('selected-ship-date-display');
    
    if (shipFrom) shipFrom.value = '';
    if (shipTo) shipTo.value = '';
    if (display) {
        display.textContent = 'Select date range...';
        display.classList.add('text-gray-400');
    }
    
    // Clear Flatpickr selection
    if (shipDatePicker) {
        shipDatePicker.clear();
    }
    
    showNotification('Ship date range cleared', 'info');
    
    // Auto-apply if enabled
    if (autoApply) {
        debounceSearch();
    }
};
