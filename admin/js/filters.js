// Filter functionality
export function initializeFilters() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            updateFilterStyles(this);
            filterRelationships(this.dataset.status);
        });
    });
    
    // Filter to show only active relationships by default
    filterRelationships('accepted');
}

function updateFilterStyles(selectedButton) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-blue-500', 'text-white');
        btn.classList.add('hover:bg-gray-100');
    });
    selectedButton.classList.add('active', 'bg-blue-500', 'text-white');
    selectedButton.classList.remove('hover:bg-gray-100');
}

function filterRelationships(status) {
    document.querySelectorAll('.relationship-row').forEach(row => {
        // Treat empty status as 'pending'
        const rowStatus = row.dataset.status || 'pending';
        row.style.display = (status === 'all' || rowStatus === status) ? '' : 'none';
    });
}
