// ========================================
// SMART CITY DATA MANAGEMENT SYSTEM PROJECT - WITH BACKEND SYNC
// ========================================

// Global data object - will be populated from database
const data = {
    traffic: [],
    parking: [],
    waste: [],
    energy: [],
    pollution: [],
    emergency: [],
    authorities: [],
    administrators: [],
    citizens: []
};

const titles = {
    traffic: 'Smart City Traffic Management',
    parking: 'Smart Parking - Available Spots',
    waste: 'Waste Management - Categories',
    energy: 'Smart Energy Monitoring System',
    pollution: 'Air Quality & Pollution Monitoring',
    emergency: 'Emergency Response - Active Incidents',
    authorities: 'City Authorities Management',
    administrators: 'City Administrators',
    citizens: 'Citizens Registry'
};

// Table field configurations for add/edit forms
const tableFields = {
    traffic: ['location', 'vehicleCount', 'congestionLevel', 'accidents', 'timestamp', 'weatherCondition', 'speedAvg'],
    parking: ['location', 'totalSpots', 'availableSpots', 'occupiedSpots', 'parkingType', 'hourlyRate', 'status'],
    waste: ['wasteCategory', 'description', 'recyclingRate', 'colorCode', 'disposalMethod'],
    energy: ['area', 'energyConsumed', 'waterUsage', 'gasUsage', 'date', 'status', 'cost', 'peakHours'],
    pollution: ['location', 'aqi', 'pm25', 'co2Level', 'noiseLevel', 'status', 'timestamp', 'alertLevel'],
    emergency: ['type', 'location', 'reportedBy', 'status', 'priority', 'timestamp', 'assignedUnit', 'casualties'],
    authorities: ['department', 'head', 'contact', 'email', 'address', 'employees', 'budget'],
    administrators: ['name', 'role', 'department', 'email', 'phone', 'status', 'lastLogin'],
    citizens: ['name', 'age', 'address', 'phone', 'email', 'registeredDate']
};

let currentPage = 'home';
let selectedItem = null;
let selectedTable = null;

// ========================================
// INITIALIZE ON PAGE LOAD
// ========================================

document.addEventListener('DOMContentLoaded', async function() {
    setupEventListeners();
    
    // Load data from backend
    await loadAllData();
    
    // Show home page
    showPage('home');
    
    // Initialize charts after data is loaded
    setTimeout(() => {
        initializeCharts();
    }, 500);
});

// ========================================
// EVENT LISTENERS SETUP
// ========================================

function setupEventListeners() {
    // Navigation items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            const page = this.getAttribute('data-page');
            showPage(page);
            updateActiveNav(this);
        });
    });

    // Feature cards
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('click', function() {
            const page = this.getAttribute('data-page');
            showPage(page);
            updateActiveNavByPage(page);
        });
    });

    // Home page buttons
    const addBtnHome = document.getElementById('addBtnHome');
    if (addBtnHome) {
        addBtnHome.addEventListener('click', () => openAddModal());
    }

    const searchBtnHome = document.getElementById('searchBtnHome');
    const searchBarHome = document.getElementById('searchBarHome');
    if (searchBtnHome && searchBarHome) {
        searchBtnHome.addEventListener('click', function() {
            searchBarHome.style.display = searchBarHome.style.display === 'none' ? 'block' : 'none';
            if (searchBarHome.style.display === 'block') {
                document.getElementById('searchInputHome').focus();
            }
        });
    }

    // Data page buttons
    const addBtnTop = document.getElementById('addBtnTop');
    if (addBtnTop) {
        addBtnTop.addEventListener('click', () => openAddModal());
    }

    const searchBtnTop = document.getElementById('searchBtnTop');
    const searchBarContainer = document.getElementById('searchBarContainer');
    if (searchBtnTop && searchBarContainer) {
        searchBtnTop.addEventListener('click', function() {
            searchBarContainer.style.display = searchBarContainer.style.display === 'none' ? 'block' : 'none';
            if (searchBarContainer.style.display === 'block') {
                document.getElementById('searchInput').focus();
            }
        });
    }

    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            filterTable(e.target.value);
        });
    }

    // Mobile menu
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    }

    // Refresh button
    const refreshBtnTop = document.getElementById('refreshBtnTop');
    if (refreshBtnTop) {
        refreshBtnTop.addEventListener('click', async () => {
            await loadAllData();
            if (currentPage !== 'home') {
                loadTableData(currentPage);
            }
            alert('✅ Data refreshed from database!');
        });
    }
}

// ========================================
// PAGE NAVIGATION
// ========================================

function showPage(page) {
    currentPage = page;
    
    if (page === 'home') {
        document.getElementById('homePage').classList.add('active');
        document.getElementById('dataPage').classList.remove('active');
    } else {
        document.getElementById('homePage').classList.remove('active');
        document.getElementById('dataPage').classList.add('active');
        loadTableData(page);
    }
}

function updateActiveNav(element) {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    element.classList.add('active');
}

function updateActiveNavByPage(page) {
    document.querySelectorAll('.nav-item').forEach(item => {
        if (item.getAttribute('data-page') === page) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
}

// ========================================
// TABLE DATA MANAGEMENT
// ========================================

function loadTableData(page) {
    const pageData = data[page];
    
    // Check if data exists
    if (!pageData || pageData.length === 0) {
        console.warn('No data for page:', page);
        // Try to load data from backend
        getTableData(page).then(() => {
            if (data[page] && data[page].length > 0) {
                loadTableData(page); // Retry after loading
            }
        });
        return;
    }

    document.getElementById('pageTitle').textContent = titles[page];
    selectedTable = page;

    const columns = Object.keys(pageData[0]).filter(key => key !== 'id');
    
    const thead = document.getElementById('tableHead');
    thead.innerHTML = '';
    const headerRow = document.createElement('tr');
    
    columns.forEach(col => {
        const th = document.createElement('th');
        th.textContent = col.charAt(0).toUpperCase() + col.slice(1).replace(/([A-Z])/g, ' $1');
        headerRow.appendChild(th);
    });
    
    const actionTh = document.createElement('th');
    actionTh.textContent = 'Actions';
    headerRow.appendChild(actionTh);
    thead.appendChild(headerRow);

    renderTableBody(pageData, columns);
}

function renderTableBody(pageData, columns) {
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = '';

    pageData.forEach(item => {
        const row = document.createElement('tr');
        
        columns.forEach(col => {
            const td = document.createElement('td');
            td.textContent = item[col];
            row.appendChild(td);
        });

        const actionTd = document.createElement('td');
        actionTd.innerHTML = `
            <div class="action-buttons-table">
                <button class="edit-btn" onclick="openEditModal(${item.id})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="delete-btn" onclick="openDeleteModal(${item.id})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        row.appendChild(actionTd);
        tbody.appendChild(row);
    });
}

function filterTable(searchTerm) {
    const pageData = data[currentPage];
    if (!pageData) return;

    const columns = Object.keys(pageData[0]).filter(key => key !== 'id');
    
    const filtered = pageData.filter(item => {
        return columns.some(col => {
            return String(item[col]).toLowerCase().includes(searchTerm.toLowerCase());
        });
    });

    renderTableBody(filtered, columns);
}

// ========================================
// MODAL FUNCTIONS - ADD
// ========================================

function openAddModal() {
    const modal = document.getElementById('addModal');
    const modalBody = modal.querySelector('.modal-body');
    
    // Clear previous content
    modalBody.innerHTML = '';
    
    if (currentPage === 'home') {
        // Show module selector
        modalBody.innerHTML = `
            <label for="moduleSelect">Select Module:</label>
            <select id="moduleSelect" onchange="loadAddFormFields()">
                <option value="">-- Select Module --</option>
                <option value="traffic">Traffic Management</option>
                <option value="parking">Smart Parking</option>
                <option value="waste">Waste Management</option>
                <option value="energy">Energy Monitoring</option>
                <option value="pollution">Air Quality</option>
                <option value="emergency">Emergency Response</option>
                <option value="authorities">City Authorities</option>
                <option value="administrators">City Administrators</option>
                <option value="citizens">Citizens</option>
            </select>
            <div id="dynamicFormFields"></div>
        `;
    } else {
        // Load fields directly for current page
        selectedTable = currentPage;
        modalBody.innerHTML = '<h4 style="margin-bottom: 1rem; color: #1e293b;">Add New ' + titles[currentPage].split(' - ')[0] + '</h4><div id="dynamicFormFields"></div>';
        generateFormFields();
    }
    
    modal.classList.add('active');
}

function loadAddFormFields() {
    const moduleSelect = document.getElementById('moduleSelect');
    selectedTable = moduleSelect.value;
    
    if (selectedTable) {
        generateFormFields();
    } else {
        document.getElementById('dynamicFormFields').innerHTML = '';
    }
}

function generateFormFields() {
    const fields = tableFields[selectedTable];
    const container = document.getElementById('dynamicFormFields');
    
    if (!fields) return;
    
    container.innerHTML = '';
    
    fields.forEach(field => {
        const fieldGroup = document.createElement('div');
        fieldGroup.style.marginBottom = '1rem';
        
        const label = document.createElement('label');
        label.textContent = field.charAt(0).toUpperCase() + field.slice(1).replace(/([A-Z])/g, ' $1');
        label.setAttribute('for', 'add_' + field);
        label.style.display = 'block';
        label.style.marginBottom = '0.5rem';
        label.style.fontWeight = '600';
        label.style.color = '#475569';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.id = 'add_' + field;
        input.placeholder = 'Enter ' + field.replace(/([A-Z])/g, ' $1').toLowerCase();
        input.required = true;
        input.style.width = '100%';
        input.style.padding = '0.875rem 1rem';
        input.style.border = '2px solid #e2e8f0';
        input.style.borderRadius = '12px';
        input.style.fontSize = '1rem';
        
        fieldGroup.appendChild(label);
        fieldGroup.appendChild(input);
        container.appendChild(fieldGroup);
    });
}

async function confirmAdd() {
    if (!selectedTable) {
        alert('⚠️ Please select a module first!');
        return;
    }
    
    const fields = tableFields[selectedTable];
    const newRecord = {};
    
    // Collect form data
    fields.forEach(field => {
        const input = document.getElementById('add_' + field);
        if (input) {
            newRecord[field] = input.value;
        }
    });
    
    // Validate
    const missingFields = fields.filter(field => !newRecord[field]);
    if (missingFields.length > 0) {
        alert('⚠️ Please fill in all fields!');
        return;
    }
    
    // Send to backend
    const result = await createRecord(selectedTable, newRecord);
    
    if (result.success) {
        alert('✅ ' + result.message);
        closeModal('addModal');
        
        // Reload data
        await getTableData(selectedTable);
        
        if (currentPage === selectedTable) {
            loadTableData(currentPage);
        }
        
        // Update charts if on home page
        if (currentPage === 'home') {
            initializeCharts();
        }
    } else {
        alert('❌ ' + result.message);
    }
}

// ========================================
// MODAL FUNCTIONS - EDIT
// ========================================

function openEditModal(id) {
    // Make sure ID is a number
    selectedItem = parseInt(id);
    
    // Check if data is loaded
    if (!data[currentPage] || data[currentPage].length === 0) {
        alert('⚠️ Data not loaded yet. Please wait...');
        console.error('Data not loaded for table:', currentPage);
        return;
    }
    
    // Find the record
    const record = data[currentPage].find(item => parseInt(item.id) === selectedItem);
    
    if (!record) {
        console.error('Record not found. ID:', selectedItem, 'Available IDs:', data[currentPage].map(i => i.id));
        alert('❌ Record not found! ID: ' + selectedItem);
        return;
    }
    
    const modal = document.getElementById('editModal');
    const modalBody = modal.querySelector('.modal-body');
    
    // Clear previous content
    modalBody.innerHTML = '<h4 style="margin-bottom: 1rem; color: #1e293b;">Edit ' + titles[currentPage].split(' - ')[0] + '</h4>';
    
    const fields = tableFields[currentPage];
    
    fields.forEach(field => {
        const fieldGroup = document.createElement('div');
        fieldGroup.style.marginBottom = '1rem';
        
        const label = document.createElement('label');
        label.textContent = field.charAt(0).toUpperCase() + field.slice(1).replace(/([A-Z])/g, ' $1');
        label.setAttribute('for', 'edit_' + field);
        label.style.display = 'block';
        label.style.marginBottom = '0.5rem';
        label.style.fontWeight = '600';
        label.style.color = '#475569';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.id = 'edit_' + field;
        input.value = record[field] || '';
        input.placeholder = 'Enter ' + field.replace(/([A-Z])/g, ' $1').toLowerCase();
        input.required = true;
        input.style.width = '100%';
        input.style.padding = '0.875rem 1rem';
        input.style.border = '2px solid #e2e8f0';
        input.style.borderRadius = '12px';
        input.style.fontSize = '1rem';
        
        fieldGroup.appendChild(label);
        fieldGroup.appendChild(input);
        modalBody.appendChild(fieldGroup);
    });
    
    modal.classList.add('active');
}

async function confirmEdit() {
    if (!selectedItem || !currentPage) {
        alert('⚠️ No item selected!');
        return;
    }
    
    const fields = tableFields[currentPage];
    const updatedRecord = {};
    
    // Collect form data
    fields.forEach(field => {
        const input = document.getElementById('edit_' + field);
        if (input) {
            updatedRecord[field] = input.value;
        }
    });
    
    // Validate
    const missingFields = fields.filter(field => !updatedRecord[field]);
    if (missingFields.length > 0) {
        alert('⚠️ Please fill in all fields!');
        return;
    }
    
    // Send to backend
    const result = await updateRecord(currentPage, selectedItem, updatedRecord);
    
    if (result.success) {
        alert('✅ ' + result.message);
        closeModal('editModal');
        
        // Reload data
        await getTableData(currentPage);
        loadTableData(currentPage);
        
        // Update charts if needed
        if (currentPage === 'home') {
            initializeCharts();
        }
    } else {
        alert('❌ ' + result.message);
    }
}

// ========================================
// MODAL FUNCTIONS - DELETE
// ========================================

function openDeleteModal(id) {
    // Make sure ID is a number
    selectedItem = parseInt(id);
    
    // Check if data is loaded
    if (!data[currentPage] || data[currentPage].length === 0) {
        alert('⚠️ Data not loaded yet. Please wait...');
        return;
    }
    
    // Verify record exists
    const record = data[currentPage].find(item => parseInt(item.id) === selectedItem);
    if (!record) {
        console.error('Record not found for delete. ID:', selectedItem);
        alert('❌ Record not found! ID: ' + selectedItem);
        return;
    }
    
    document.getElementById('deleteModal').classList.add('active');
}

async function confirmDelete() {
    if (!selectedItem || !currentPage) {
        alert('⚠️ No item selected!');
        return;
    }
    
    // Send to backend
    const result = await deleteRecord(currentPage, selectedItem);
    
    if (result.success) {
        alert('✅ ' + result.message);
        closeModal('deleteModal');
        
        // Reload data
        await getTableData(currentPage);
        loadTableData(currentPage);
        
        // Update charts if needed
        if (currentPage === 'home') {
            initializeCharts();
        }
    } else {
        alert('❌ ' + result.message);
    }
}

// ========================================
// MODAL CLOSE
// ========================================

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    selectedItem = null;
    selectedTable = null;
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
        selectedItem = null;
        selectedTable = null;
    }
}

// ========================================
// SIDE PANEL FUNCTIONS
// ========================================

function openPanel(id) {
    closeAllPanels();
    document.getElementById(id).classList.add('active');
}

function closePanel(id) {
    document.getElementById(id).classList.remove('active');
}

function closeAllPanels() {
    document.querySelectorAll('.side-panel').forEach(panel => panel.classList.remove('active'));
}

function saveSettings() {
    alert("Settings saved successfully!");
    closePanel('settingsPanel');
}

// ========================================
// MOBILE MENU
// ========================================

function toggleMobileMenu() {
    const navMenu = document.getElementById('navMenu');
    if (navMenu.style.display === 'flex') {
        navMenu.style.display = 'none';
    } else {
        navMenu.style.display = 'flex';
        navMenu.style.flexDirection = 'column';
        navMenu.style.position = 'absolute';
        navMenu.style.top = '100%';
        navMenu.style.left = '0';
        navMenu.style.right = '0';
        navMenu.style.backgroundColor = '#667eea';
        navMenu.style.padding = '1rem';
        navMenu.style.zIndex = '999';
    }
}

// ========================================
// CHART INITIALIZATION
// ========================================

function initializeCharts() {
    initTrafficChart();
    initEnergyChart();
    initPollutionChart();
    initEmergencyChart();
}

function initTrafficChart() {
    const ctx = document.getElementById('trafficChart');
    if (!ctx || !data.traffic || data.traffic.length === 0) return;

    // Destroy existing chart if it exists
    if (ctx.chart) {
        ctx.chart.destroy();
    }

    const trafficLocations = data.traffic.map(t => t.location.substring(0, 15) + '...');
    const trafficCounts = data.traffic.map(t => parseInt(t.vehicleCount) || 0);
    
    ctx.chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: trafficLocations,
            datasets: [{
                label: 'Vehicle Count',
                data: trafficCounts,
                backgroundColor: trafficCounts.map(count => {
                    if (count > 250) return 'rgba(239, 68, 68, 0.8)';
                    if (count > 150) return 'rgba(251, 191, 36, 0.8)';
                    return 'rgba(34, 197, 94, 0.8)';
                }),
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

function initEnergyChart() {
    const ctx = document.getElementById('energyChart');
    if (!ctx || !data.energy || data.energy.length === 0) return;

    if (ctx.chart) {
        ctx.chart.destroy();
    }

    const energyAreas = data.energy.map(e => e.area);
    const energyValues = data.energy.map(e => {
        const value = e.energyConsumed.toString().replace(/[^\d]/g, '');
        return parseInt(value) || 0;
    });
    
    ctx.chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: energyAreas,
            datasets: [{
                label: 'Energy (kWh)',
                data: energyValues,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(168, 85, 247, 0.8)'
                ],
                borderColor: '#fff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 15,
                        font: { size: 11 }
                    }
                }
            }
        }
    });
}

function initPollutionChart() {
    const ctx = document.getElementById('pollutionChart');
    if (!ctx || !data.pollution || data.pollution.length === 0) return;

    if (ctx.chart) {
        ctx.chart.destroy();
    }

    const pollutionLocations = data.pollution.map(p => p.location);
    const aqiValues = data.pollution.map(p => parseInt(p.aqi) || 0);
    
    ctx.chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: pollutionLocations,
            datasets: [{
                label: 'Air Quality Index',
                data: aqiValues,
                backgroundColor: aqiValues.map(val => {
                    if (val <= 50) return 'rgba(34, 197, 94, 0.8)';
                    if (val <= 100) return 'rgba(251, 191, 36, 0.8)';
                    if (val <= 150) return 'rgba(251, 146, 60, 0.8)';
                    return 'rgba(239, 68, 68, 0.8)';
                }),
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

function initEmergencyChart() {
    const ctx = document.getElementById('emergencyChart');
    if (!ctx || !data.emergency || data.emergency.length === 0) return;

    if (ctx.chart) {
        ctx.chart.destroy();
    }

    const incidents = data.emergency.map(e => e.type);
    const responseTimes = incidents.map(() => Math.random() * 40 + 5); // Mock data
    
    ctx.chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: incidents,
            datasets: [{
                label: 'Response Time (minutes)',
                data: responseTimes,
                backgroundColor: 'rgba(139, 92, 246, 0.2)',
                borderColor: 'rgb(139, 92, 246)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(139, 92, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

Chart.defaults.devicePixelRatio = 2;

// ========================================
// USER AUTHENTICATION CHECK
// ========================================

const loggedInUser = JSON.parse(localStorage.getItem("loggedInUser"));

if (!loggedInUser) {
    window.location.href = "login.html";
} else {
    const usernameDisplay = document.getElementById("usernameDisplay");
    if (usernameDisplay) {
        usernameDisplay.textContent = loggedInUser.username.charAt(0).toUpperCase() + loggedInUser.username.slice(1);
    }

    if (loggedInUser.role === "admin") {
        const navMenu = document.getElementById("navMenu");
        const adminBtn = document.createElement('button');
        adminBtn.className = 'nav-item';
        adminBtn.style.background = 'rgba(16, 185, 129, 0.2)';
        adminBtn.style.border = '2px solid rgba(16, 185, 129, 0.5)';
        adminBtn.innerHTML = '<i class="fas fa-user-shield"></i><span>Admin Panel</span>';
        adminBtn.onclick = () => window.location.href = 'admin-dashboard.html';
        navMenu.appendChild(adminBtn);
    }
}

function logout() {
    if (confirm("Are you sure you want to logout?")) {
        localStorage.removeItem("loggedInUser");
        window.location.href = "logout.html";
    }
}

console.log('✅ Smart City Data Management System Loaded');
console.log('👤 Logged in as:', loggedInUser.username, '(' + loggedInUser.role + ')');