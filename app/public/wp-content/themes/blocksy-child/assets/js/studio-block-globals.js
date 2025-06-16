// Studio Block Editor Globals
// This file provides global variables for Studio blocks in the editor

(function() {
    'use strict';
    
    // Ensure window.studioAdmin is available
    if (typeof window.studioAdmin === 'undefined') {
        window.studioAdmin = {};
    }
    
    // Log that globals are loaded for debugging
    console.log('Studio Block Globals loaded:', window.studioAdmin);
})();
