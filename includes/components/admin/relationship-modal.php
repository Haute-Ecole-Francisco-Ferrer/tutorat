<?php
function renderRelationshipModal() {
    ?>
    <div id="detailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <h3 class="text-lg font-bold mb-4">Détails de la relation</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div id="modalContent" class="space-y-4">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Changer le statut :</label>
                            <div class="flex items-center gap-4">
                                <select id="modalStatusSelect" 
                                        class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <option value="pending">En attente</option>
                                    <option value="accepted">Actif</option>
                                    <option value="archived">Archivé</option>
                                </select>
                                <button onclick="updateModalStatus()" 
                                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm">
                                    Appliquer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}