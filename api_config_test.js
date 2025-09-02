// API Configuration
// This will automatically detect if we're running locally or externally
const API_CONFIG = {
    // Detect if we're running locally or externally
    getBaseUrl: function() {
        const hostname = window.location.hostname;
        
        // If accessing via localhost or local IP, use local Flask server
        if (hostname === 'localhost' || hostname === '127.0.0.1' || hostname.startsWith('192.168.')) {
            return 'http://192.168.1.94:5001';
        }
        
        // If accessing via any DDNS domain, use external Flask server
        // Use the same domain as the current page but port 5000
        if (hostname.includes('ddns.net')) {
            return `http://${hostname}:5001`;
        }
        
        // Default fallback
        return 'http://bnm.ddns.net:5001';
    },
    
    // Get full API URL
    getApiUrl: function(endpoint) {
        return this.getBaseUrl() + endpoint;
    }
};

// Export for use in other scripts
window.API_CONFIG = API_CONFIG;
