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

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Update active state
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Filter rows
        const status = this.dataset.status;
        document.querySelectorAll('.relationship-row').forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

function showDetails(relationship) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('modalContent');
    
    content.innerHTML = `
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
            ${relationship.archive_reason ? `
                <div>
                    <h4 class="font-semibold">Raison de l'archivage</h4>
                    <p class="whitespace-pre-wrap">${relationship.archive_reason}</p>
                </div>
            ` : ''}
            <div>
                <h4 class="font-semibold">Dates</h4>
                <p>Créé le : ${formatDate(relationship.created_at)}</p>
                ${relationship.archived_at ? `<p>Archivé le : ${formatDate(relationship.archived_at)}</p>` : ''}
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}