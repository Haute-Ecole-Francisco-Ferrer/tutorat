// Modal functionality
let currentRelationship = null;

export function initializeModal() {
    // Expose functions to window object
    window.showDetails = showDetails;
    window.closeModal = closeModal;
    window.updateModalStatus = updateModalStatus;
}

function showDetails(relationship) {
    currentRelationship = relationship;
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('modalContent');
    const statusSelect = document.getElementById('modalStatusSelect');
    
    content.innerHTML = generateModalContent(relationship);
    statusSelect.value = relationship.status;
    modal.classList.remove('hidden');
}

function closeModal() {
    currentRelationship = null;
    document.getElementById('detailsModal').classList.add('hidden');
}

async function updateModalStatus() {
    if (!currentRelationship) return;

    const statusSelect = document.getElementById('modalStatusSelect');
    const newStatus = statusSelect.value;

    try {
        // Call the updateStatus function
        await window.updateStatus(currentRelationship.id, newStatus);
        
        // Update the current relationship object
        currentRelationship.status = newStatus;
        
        // Update the modal content
        const content = document.getElementById('modalContent');
        content.innerHTML = generateModalContent(currentRelationship);
        
        // Update the table row status
        const row = document.querySelector(`[data-relationship-id="${currentRelationship.id}"]`);
        if (row) {
            row.dataset.status = newStatus;
            const statusCell = row.querySelector('.status-cell');
            if (statusCell) {
                const select = statusCell.querySelector('select');
                const span = statusCell.querySelector('span');
                if (select) select.value = newStatus;
                if (span) {
                    span.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(newStatus)}`;
                    span.textContent = getStatusLabel(newStatus);
                }
            }
        }
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Failed to update status: ' + error.message);
    }
}

function generateModalContent(relationship) {
    const statusClass = getStatusClass(relationship.status);
    const statusLabel = getStatusLabel(relationship.status);

    return `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h4 class="font-semibold">Tuteur</h4>
                    <p>${relationship.tutor_firstname} ${relationship.tutor_lastname}</p>
                </div>
                <div>
                    <h4 class="font-semibold">Tutoré</h4>
                    <p>${relationship.tutee_firstname} ${relationship.tutee_lastname}</p>
                </div>
            </div>
            <div>
                <h4 class="font-semibold">Matière</h4>
                <p>${relationship.subject_name}</p>
            </div>
            <div>
                <h4 class="font-semibold">Statut actuel</h4>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                    ${statusLabel}
                </span>
            </div>
            <div>
                <h4 class="font-semibold">Message du tutoré</h4>
                <p class="whitespace-pre-wrap">${relationship.message || 'Aucun message'}</p>
            </div>
            ${relationship.tutor_response ? `
                <div>
                    <h4 class="font-semibold">Réponse du tuteur</h4>
                    <p class="whitespace-pre-wrap">${relationship.tutor_response}</p>
                </div>
            ` : ''}
            <div>
                <h4 class="font-semibold">Dates</h4>
                <p>Créé le : ${formatDate(relationship.created_at)}</p>
                ${relationship.archived_at ? `<p>Archivé le : ${formatDate(relationship.archived_at)}</p>` : ''}
            </div>
        </div>
    `;
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
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