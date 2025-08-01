// SPK Form JavaScript
// Functions are available globally from base-function.js

// Define global options object for modal operations
var options = {};

// Flag to prevent double initialization
var spkFormInitialized = false;

// Debug logging untuk SPK Form
console.log('SPK Form.js loaded at:', new Date());
console.log('jQuery available:', typeof $);


// Wait for DOM ready and ensure $globalVariable is available
$(document).ready(function() {
    // Function to check if $globalVariable is available
    function checkGlobalVariable() {
        if (typeof $globalVariable === 'undefined') {
            console.log('$globalVariable not yet available, waiting...');
            setTimeout(checkGlobalVariable, 100);
            return;
        }
        
        // Check if already initialized
        if (spkFormInitialized) {
            console.log('SPK Form already initialized, skipping...');
            return;
        }
        
        console.log('$globalVariable is now available:', typeof $globalVariable);
        initializeSPKForm();
    }
    
    // Start checking for $globalVariable
    checkGlobalVariable();
});

function initializeSPKForm() {
    console.log('=== initializeSPKForm() called ===');
    
    // Set flag to prevent double initialization
    spkFormInitialized = true;
    
    // TODO: Add SPK form initialization logic here
    console.log('SPK Form module initialized successfully');
}

// TODO: Add SPK form functions here 