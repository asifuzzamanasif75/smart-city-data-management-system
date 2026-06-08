// ========================================
// API CONFIGURATION
// ========================================

const API_CONFIG = {
    BASE_URL: new URL('.', window.location.href).href.replace(/\/$/, ''),
    ENDPOINTS: {
        API: '/api.php',
        AUTH: '/auth.php'
    }
};

// ========================================
// API FUNCTIONS
// ========================================

// Load all data from database
async function loadAllData() {
    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.API}?action=all`);
        const result = await response.json();
        
        if (result.success) {
            // Convert string IDs to numbers for all tables
            Object.keys(result.data).forEach(table => {
                data[table] = result.data[table].map(item => ({
                    ...item,
                    id: parseInt(item.id)
                }));
            });
            console.log('✅ Data loaded from database:', data);
            return data;
        } else {
            console.error('❌ Failed to load data:', result.message);
            return null;
        }
    } catch (error) {
        console.error('❌ Error loading data:', error);
        return null;
    }
}

// Get data from specific table
async function getTableData(table) {
    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.API}?table=${table}`);
        const result = await response.json();
        
        if (result.success) {
            // Convert string IDs to numbers for consistency
            data[table] = result.data.map(item => ({
                ...item,
                id: parseInt(item.id)
            }));
            console.log(`✅ Loaded ${data[table].length} records from ${table}`);
            return data[table];
        } else {
            console.error('❌ Failed to load table data:', result.message);
            return null;
        }
    } catch (error) {
        console.error('❌ Error loading table data:', error);
        return null;
    }
}

// Get single record
async function getRecord(table, id) {
    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.API}?table=${table}&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            console.error('❌ Failed to load record:', result.message);
            return null;
        }
    } catch (error) {
        console.error('❌ Error loading record:', error);
        return null;
    }
}

// Create new record
async function createRecord(table, recordData) {
    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.API}?table=${table}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(recordData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('✅ Record created:', result);
            // Reload table data
            await getTableData(table);
            return result;
        } else {
            console.error('❌ Failed to create record:', result.message);
            return result;
        }
    } catch (error) {
        console.error('❌ Error creating record:', error);
        return { success: false, message: error.message };
    }
}

// Update record
async function updateRecord(table, id, recordData) {
    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.API}?table=${table}&id=${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(recordData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('✅ Record updated:', result);
            // Reload table data
            await getTableData(table);
            return result;
        } else {
            console.error('❌ Failed to update record:', result.message);
            return result;
        }
    } catch (error) {
        console.error('❌ Error updating record:', error);
        return { success: false, message: error.message };
    }
}

// Delete record
async function deleteRecord(table, id) {
    try {
        const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.API}?table=${table}&id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('✅ Record deleted:', result);
            // Reload table data
            await getTableData(table);
            return result;
        } else {
            console.error('❌ Failed to delete record:', result.message);
            return result;
        }
    } catch (error) {
        console.error('❌ Error deleting record:', error);
        return { success: false, message: error.message };
    }
}

// ========================================
// AUTHENTICATION FUNCTIONS
// ========================================

async function loginUser(username, password) {
    try {
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('username', username);
        formData.append('password', password);
        
        const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.AUTH}`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('❌ Login error:', error);
        return { success: false, message: 'Connection error. Please check if XAMPP is running.' };
    }
}

async function registerUser(username, email, password) {
    try {
        const formData = new FormData();
        formData.append('action', 'register');
        formData.append('username', username);
        formData.append('email', email);
        formData.append('password', password);
        
        const response = await fetch(`${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.AUTH}`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('❌ Registration error:', error);
        return { success: false, message: 'Connection error. Please check if XAMPP is running.' };
    }
}

// ========================================
// INITIALIZE ON PAGE LOAD
// ========================================

document.addEventListener('DOMContentLoaded', async function() {
    console.log('🔗 Connecting to backend API...');
    console.log('📡 API URL:', API_CONFIG.BASE_URL);
    
    // Load all data from database on page load (if not on login/register page)
    if (!window.location.pathname.includes('login.html') && 
        !window.location.pathname.includes('register.html') && 
        !window.location.pathname.includes('logout.html')) {
        
        const loadedData = await loadAllData();
        
        if (loadedData) {
            console.log('✅ Database connected successfully');
            
            // Reload current page if on a specific table view
            if (currentPage && currentPage !== 'home') {
                loadTableData(currentPage);
            }
            
            // Reinitialize charts with fresh data
            if (typeof initializeCharts === 'function') {
                initializeCharts();
            }
        } else {
            console.warn('⚠️ Could not connect to database. Using local data.');
        }
    }
});

console.log('✅ API Handler loaded successfully');