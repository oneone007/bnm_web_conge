<?php

session_start();

if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Sup Vente', 'Comptable'])) {
    header("Location: Acess_Denied");    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objectif Mensuel Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="theme.js"></script>
    <style>
        .card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .modal {
            transition: opacity 0.3s ease;
        }
        .progress-bar {
            transition: width 1s ease-in-out;
        }
        .floating-input {
            transition: all 0.3s ease;
        }
        .floating-input:focus {
            transform: scale(1.02);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #1f2937;
            color: #f3f4f6;
        }

        .dark-mode .card {
            background-color: #374151;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .dark-mode .card:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.3);
        }

        .dark-mode .text-gray-800 {
            color: #f3f4f6;
        }

        .dark-mode .text-gray-700 {
            color: #d1d5db;
        }

        .dark-mode .text-gray-500 {
            color: #9ca3af;
        }

        .dark-mode .bg-white {
            background-color: #374151;
        }

        .dark-mode .bg-gray-200 {
            background-color: #4b5563;
        }

        .dark-mode .progress-bar {
            background-color: #3b82f6;
        }

        /* Dark mode modal styles */
        .dark-mode .modal > div {
            background-color: #374151;
        }

        .dark-mode .floating-input {
            background-color: #1f2937;
            border-color: #4b5563;
            color: #f3f4f6;
        }

        .dark-mode .floating-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
        }

        .dark-mode #authBtn,
        .dark-mode #saveGoal {
            background-color: #3b82f6;
        }

        .dark-mode #authBtn:hover,
        .dark-mode #saveGoal:hover {
            background-color: #2563eb;
        }

        .dark-mode #cancelEdit {
            border-color: #4b5563;
            color: #d1d5db;
        }

        .dark-mode #cancelEdit:hover {
            background-color: #374151;
        }

        .dark-mode #errorMessage {
            color: #ef4444;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-8 max-w-md">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
            <i class="fas fa-bullseye mr-3 text-indigo-600"></i> Objectif Mensuel
        </h1>
        
        <!-- Main Container - Monthly Goal -->
        <div class="card bg-white rounded-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-700 flex items-center">
                    💵Objectif Mensuel
                </h2>
                <button id="editGoalBtn" class="text-indigo-600 hover:text-indigo-800 transition-colors">
                    <i class="fas fa-edit"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Objectif</p>
                    <div id="monthlyGoalLoading" class="animate-pulse flex space-x-4">
                        <div class="h-8 bg-gray-200 rounded w-3/4"></div>
                    </div>
                    <p id="monthlyGoal" class="text-2xl font-bold text-gray-800 hidden"></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Total Recouvrement</p>
                    <div id="totalRecoveryLoading" class="animate-pulse flex space-x-4">
                        <div class="h-8 bg-gray-200 rounded w-3/4"></div>
                    </div>
                    <p id="totalRecovery" class="text-2xl font-bold text-green-600 hidden"></p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Pourcentage</p>
                    <div id="percentageLoading" class="animate-pulse flex space-x-4">
                        <div class="h-2.5 bg-gray-200 rounded-full w-full"></div>
                    </div>
                    <div id="percentageContainer" class="hidden flex items-center">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                            <div id="progressBar" class="progress-bar bg-indigo-600 h-2.5 rounded-full"></div>
                        </div>
                        <span id="percentage" class="text-sm font-medium text-gray-700"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Goal Modal -->
    <div id="editModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Modifier l'Objectif</h3>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="authSection">
                <p class="text-gray-600 mb-4">Veuillez vous authentifier pour modifier l'objectif.</p>
                
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                        <input type="text" id="username" class="floating-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                        <input type="password" id="password" class="floating-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div class="flex justify-end">
                        <button id="authBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            Authentifier
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="editSection" class="hidden">
                <div class="mb-4">
                    <label for="newGoal" class="block text-sm font-medium text-gray-700 mb-1">Nouvel Objectif Mensuel (DZD)</label>
                    <input type="number" id="newGoal" class="floating-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancelEdit" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                    <button id="saveGoal" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Enregistrer
                    </button>
                </div>
            </div>
            
            <div id="errorMessage" class="mt-3 text-sm text-red-600 hidden"></div>
        </div>
    </div>
    
<script>
        // DOM Elements
        const editGoalBtn = document.getElementById('editGoalBtn');
        const editModal = document.getElementById('editModal');
        const closeModal = document.getElementById('closeModal');
        const authBtn = document.getElementById('authBtn');
        const saveGoal = document.getElementById('saveGoal');
        const cancelEdit = document.getElementById('cancelEdit');
        const authSection = document.getElementById('authSection');
        const editSection = document.getElementById('editSection');
        const errorMessage = document.getElementById('errorMessage');
        const monthlyGoal = document.getElementById('monthlyGoal');
        const monthlyGoalLoading = document.getElementById('monthlyGoalLoading');
        const totalRecovery = document.getElementById('totalRecovery');
        const totalRecoveryLoading = document.getElementById('totalRecoveryLoading');
        const percentage = document.getElementById('percentage');
        const percentageContainer = document.getElementById('percentageContainer');
        const percentageLoading = document.getElementById('percentageLoading');
        const progressBar = document.getElementById('progressBar');
        
        // Static goal value (will be initialized from server)
        let currentGoal = 481114494.36; // Default value
        
        // Show loading states
        function showLoading() {
            monthlyGoalLoading.classList.remove('hidden');
            monthlyGoal.classList.add('hidden');
            totalRecoveryLoading.classList.remove('hidden');
            totalRecovery.classList.add('hidden');
            percentageLoading.classList.remove('hidden');
            percentageContainer.classList.add('hidden');
        }

        // Hide loading states and show data
        function hideLoading() {
            monthlyGoalLoading.classList.add('hidden');
            monthlyGoal.classList.remove('hidden');
            totalRecoveryLoading.classList.add('hidden');
            totalRecovery.classList.remove('hidden');
            percentageLoading.classList.add('hidden');
            percentageContainer.classList.remove('hidden');
        }

        // Fetch data from Flask backend
        async function fetchRecouvrementData(newGoal = null) {
            try {
                showLoading();
                const url = newGoal ? 
                    `http://192.168.1.94:5000/recouvrement?objectif=${newGoal}` :
                    `http://192.168.1.94:5000/recouvrement`;
                    
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                
                if (data.error) {
                    console.error('Error from server:', data.error);
                    return null;
                }
                
                return {
                    goal: data.OBJECTIF_MENSUEL,
                    recovery: data.TOTAL_RECOUVREMENT,
                    percentage: data.POURCENTAGE * 100
                };
            } catch (error) {
                console.error('Error fetching data:', error);
                return null;
            } finally {
                hideLoading();
            }
        }

        // Update UI with current values
        function updateUI(goal, recovery, percentageValue) {
            monthlyGoal.textContent = goal.toLocaleString('fr-FR') + ' DZD';
            totalRecovery.textContent = recovery.toLocaleString('fr-FR') + ' DZD';
            percentage.textContent = percentageValue.toFixed(0) + '%';
            progressBar.style.width = percentageValue.toFixed(0) + '%';
        }

        // Load initial data
        async function loadInitialData() {
            const data = await fetchRecouvrementData();
            if (data) {
                currentGoal = data.goal; // Get the current goal from server
                currentRecovery = data.recovery;
                currentPercentage = data.percentage;
                updateUI(currentGoal, currentRecovery, currentPercentage);
            } else {
                // Fallback to default values if server fails
                updateUI(currentGoal, 0, 0);
            }
        }

        // Event Listeners
        editGoalBtn.addEventListener('click', () => {
            editModal.classList.remove('hidden');
        });
        
        closeModal.addEventListener('click', () => {
            editModal.classList.add('hidden');
            errorMessage.classList.add('hidden');
        });
        
        authBtn.addEventListener('click', () => {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (username === '911' && password === '911') {
                authSection.classList.add('hidden');
                editSection.classList.remove('hidden');
                errorMessage.classList.add('hidden');
                document.getElementById('newGoal').value = currentGoal;
            } else {
                errorMessage.textContent = 'Identifiants incorrects. Veuillez réessayer.';
                errorMessage.classList.remove('hidden');
            }
        });
        
        saveGoal.addEventListener('click', async () => {
            const newGoalValue = parseFloat(document.getElementById('newGoal').value);
            
            if (newGoalValue && newGoalValue > 0) {
                try {
                    const data = await fetchRecouvrementData(newGoalValue);
                    if (data) {
                        currentGoal = data.goal; // Use the server's returned goal value
                        currentRecovery = data.recovery;
                        currentPercentage = data.percentage;
                        updateUI(currentGoal, currentRecovery, currentPercentage);
                        editModal.classList.add('hidden');
                    } else {
                        errorMessage.textContent = 'Erreur lors de la mise à jour des données.';
                        errorMessage.classList.remove('hidden');
                    }
                } catch (error) {
                    errorMessage.textContent = 'Erreur de connexion au serveur.';
                    errorMessage.classList.remove('hidden');
                }
            } else {
                errorMessage.textContent = 'Veuillez entrer une valeur valide.';
                errorMessage.classList.remove('hidden');
            }
        });
        
        cancelEdit.addEventListener('click', () => {
            editModal.classList.add('hidden');
            authSection.classList.remove('hidden');
            editSection.classList.add('hidden');
            errorMessage.classList.add('hidden');
            
            // Clear inputs
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
        });
        
        // Initialize the app
        loadInitialData();
        
        // Periodically refresh data (every 5 minutes)
        setInterval(async () => {
            const data = await fetchRecouvrementData();
            if (data) {
                currentGoal = data.goal; // Always get the latest goal from server
                currentRecovery = data.recovery;
                currentPercentage = data.percentage;
                updateUI(currentGoal, currentRecovery, currentPercentage);
            }
        }, 300000); // 300000 ms = 5 minutes
    </script>
</body>
</html>