// Filter functionality
export function initializeFilters() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            updateFilterStyles(this);
            filterRelationships(this.dataset.status);
        });
    });
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
        row.style.display = (status === 'all' || row.dataset.status === status) ? '' : 'none';
    });
}