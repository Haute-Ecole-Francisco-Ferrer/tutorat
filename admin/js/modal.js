// Modal functionality
export function initializeModal() {
    window.showDetails = showDetails;
    window.closeModal = closeModal;
}

function showDetails(relationship) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('modalContent');
    
    content.innerHTML = generateModalContent(relationship);
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}

function generateModalContent(relationship) {
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