// Status update functionality
export async function updateStatus(relationshipId, newStatus) {
    try {
        const response = await fetch('/admin/api/update-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                relationship_id: relationshipId,
                status: newStatus
            })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Failed to update status');
        }

        // Update UI
        const row = document.querySelector(`[data-relationship-id="${relationshipId}"]`);
        if (row) {
            row.dataset.status = newStatus;
            const statusCell = row.querySelector('.status-cell');
            if (statusCell) {
                const statusSpan = statusCell.querySelector('span');
                statusSpan.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(newStatus)}`;
                statusSpan.textContent = getStatusLabel(newStatus);
            }
        }

        return data;
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Failed to update status: ' + error.message);
        throw error;
    }
}

function getStatusClass(status) {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'accepted':
            return 'bg-green-100 text-green-800';
        case 'archived':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'pending':
            return 'En attente';
        case 'accepted':
            return 'Actif';
        case 'archived':
            return 'Archivé';
        default:
            return status;
    }
}