import { initializeFilters } from './filters.js';
import { initializeModal } from './modal.js';
import { updateStatus } from './status.js';

document.addEventListener('DOMContentLoaded', () => {
    initializeFilters();
    initializeModal();
    
    // Make updateStatus available globally
    window.updateStatus = updateStatus;
});