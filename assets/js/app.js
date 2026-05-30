// ============================================
// Main JavaScript Utilities
// Electricity Complaint Management System
// ============================================

/**
 * Show notification message
 * @param {string} message - Message to show
 * @param {string} type - Type: success, error, warning, info
 * @param {number} duration - Duration in ms
 */
function showNotification(message, type = 'info', duration = 3000) {
    const alertClass = `alert alert-${type}`;
    const alertHTML = `
        <div class="${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHTML);
    
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts[alerts.length - 1].remove();
        }
    }, duration);
}

/**
 * Format date to readable format
 * @param {string} dateString - Date string
 * @returns {string} Formatted date
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Validate email format
 * @param {string} email - Email to validate
 * @returns {boolean} Is valid email
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate password strength
 * @param {string} password - Password to check
 * @returns {object} Validation result
 */
function validatePasswordStrength(password) {
    const result = {
        isStrong: false,
        feedback: []
    };
    
    if (password.length < 8) {
        result.feedback.push('Password must be at least 8 characters');
    }
    if (!/[A-Z]/.test(password)) {
        result.feedback.push('Password must contain uppercase letter');
    }
    if (!/[a-z]/.test(password)) {
        result.feedback.push('Password must contain lowercase letter');
    }
    if (!/[0-9]/.test(password)) {
        result.feedback.push('Password must contain number');
    }
    if (!/[!@#$%^&*]/.test(password)) {
        result.feedback.push('Password must contain special character');
    }
    
    result.isStrong = result.feedback.length === 0;
    return result;
}

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Get status badge HTML
 * @param {string} status - Status value
 * @returns {string} Badge HTML
 */
function getStatusBadge(status) {
    const statusMap = {
        'Pending': 'warning',
        'Assigned': 'info',
        'In Progress': 'primary',
        'Resolved': 'success',
        'Closed': 'secondary',
        'On Hold': 'danger'
    };
    
    const type = statusMap[status] || 'secondary';
    return `<span class="badge bg-${type}">${status}</span>`;
}

/**
 * Get priority badge HTML
 * @param {string} priority - Priority value
 * @returns {string} Badge HTML
 */
function getPriorityBadge(priority) {
    const priorityMap = {
        'Critical': 'danger',
        'High': 'danger',
        'Medium': 'warning',
        'Low': 'success'
    };
    
    const type = priorityMap[priority] || 'secondary';
    return `<span class="badge bg-${type}">${priority}</span>`;
}

/**
 * Debounce function for search/input
 * @param {function} func - Function to debounce
 * @param {number} wait - Wait time in ms
 * @returns {function} Debounced function
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Deep copy object
 * @param {object} obj - Object to copy
 * @returns {object} Copied object
 */
function deepCopy(obj) {
    return JSON.parse(JSON.stringify(obj));
}

/**
 * Get query parameter from URL
 * @param {string} param - Parameter name
 * @returns {string} Parameter value
 */
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

/**
 * Confirm delete action
 * @param {string} message - Confirmation message
 * @returns {boolean} User confirmation
 */
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

/**
 * API request helper
 * @param {string} url - Endpoint URL
 * @param {object} options - Fetch options
 * @returns {Promise} API response
 */
async function apiRequest(url, options = {}) {
    try {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            ...options
        };
        
        const response = await fetch(url, defaultOptions);
        
        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Request Error:', error);
        showNotification('An error occurred. Please try again.', 'danger');
        throw error;
    }
}

/**
 * Local storage operations
 */
const Storage = {
    set: (key, value) => localStorage.setItem(key, JSON.stringify(value)),
    get: (key) => {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : null;
    },
    remove: (key) => localStorage.removeItem(key),
    clear: () => localStorage.clear()
};

/**
 * Initialize tooltips and popovers
 */
function initializeBootstrapComponents() {
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
}

/**
 * DOM ready handler
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeBootstrapComponents();
});

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showNotification,
        formatDate,
        validateEmail,
        validatePasswordStrength,
        formatCurrency,
        getStatusBadge,
        getPriorityBadge,
        debounce,
        deepCopy,
        getQueryParam,
        confirmDelete,
        apiRequest,
        Storage
    };
}
