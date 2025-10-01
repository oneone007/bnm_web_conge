<?php

session_start();

// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Sup Vente', 'Comptable'])) {
//     header("Location: Acess_Denied");    exit();
// }


$page_identifier = 'recouverement';

require_once 'check_permission.php';

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
                <script src="api_config.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', 'Segoe UI', 'Arial', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
            min-height: 100vh;
        }
        .card {
            transition: all 0.3s cubic-bezier(.25,.8,.25,1);
            box-shadow: 0 8px 24px 0 rgba(80, 112, 255, 0.10), 0 1.5px 4px 0 rgba(80, 112, 255, 0.10);
            border: 1.5px solid #e0e7ff;
            background: #fff;
        }
        .card:hover {
            transform: translateY(-7px) scale(1.01);
            box-shadow: 0 16px 32px 0 rgba(80, 112, 255, 0.15), 0 3px 8px 0 rgba(80, 112, 255, 0.12);
            border-color: #6366f1;
        }
        .progress-bar {
            transition: width 1s cubic-bezier(.4,0,.2,1);
        }
        .refresh-btn {
            transition: all 0.2s;
            border-radius: 6px;
            padding: 0.3rem 0.8rem;
            border: 1px solid #6366f1;
            background: #f1f5ff;
            font-weight: 600;
        }
        .refresh-btn:hover {
            background: #6366f1;
            color: #fff;
            border-color: #6366f1;
            transform: scale(1.07);
        }
        .refresh-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            background: #e0e7ff;
        }
        ::-webkit-scrollbar-thumb {
            background: #6366f1;
            border-radius: 8px;
        }
        /* Dark mode styles */
        body.dark-mode {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: #f3f4f6;
        }
        .dark-mode .card {
            background: #232946;
            border-color: #6366f1;
            box-shadow: 0 8px 24px 0 rgba(80, 112, 255, 0.18);
        }
        .dark-mode .card:hover {
            box-shadow: 0 16px 32px 0 rgba(80, 112, 255, 0.25);
        }
        .dark-mode .text-gray-800,
        .dark-mode .text-gray-700 {
            color: #f3f4f6 !important;
        }
        .dark-mode .text-gray-500 {
            color: #a5b4fc !important;
        }
        .dark-mode .bg-white {
            background: #232946 !important;
        }
        .dark-mode .bg-gray-200 {
            background: #374151 !important;
        }
        .dark-mode .progress-bar {
            background: #818cf8 !important;
        }
        .dark-mode .refresh-btn {
            color: #a5b4fc;
            background: #232946;
            border-color: #818cf8;
        }
        .dark-mode .refresh-btn:hover {
            color: #fff;
            background: #6366f1;
        }
        /* Animations */
        .fade-in {
            animation: fadeIn 0.7s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="font-sans fade-in">
    <div class="min-h-screen flex flex-col justify-center items-center px-2 py-8">
        <div class="w-full max-w-lg">
            <h1 class="text-4xl font-extrabold text-indigo-700 mb-8 flex items-center justify-center tracking-tight drop-shadow-lg">
                <i class="fas fa-bullseye mr-3 text-indigo-500 animate-pulse"></i> Objectif Mensuel
            </h1>
            <!-- Main Container - Monthly Goal -->
            <div class="card rounded-2xl p-8 mb-8 shadow-xl">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-700 flex items-center gap-2">
                        <span class="text-3xl">💵</span> Objectif Mensuel
                    </h2>
                    <!-- Edited -->
                    <div class="flex items-center gap-2">
                        <?php if (isset($_SESSION['Role']) && $_SESSION['Role'] === 'Developer'): ?>
                        <button id="editGoalBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm flex items-center gap-1 transition-colors">
                            <i class="fas fa-edit"></i>
                            <span>Modifier</span>
                        </button>
                        <?php endif; ?>
                        <button id="refreshBtn" class="refresh-btn text-indigo-600 text-sm flex items-center gap-2 shadow-sm">
                            <i class="fas fa-sync-alt"></i>
                            <span>Actualiser</span>
                        </button>
                    </div>
                <!-- Edited -->
                </div>
                <div class="space-y-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1 tracking-wide">Objectif</p>
                        <div id="monthlyGoalLoading" class="animate-pulse flex space-x-4">
                            <div class="h-8 bg-gray-200 rounded w-3/4"></div>
                        </div>
                        <p id="monthlyGoal" class="text-3xl font-extrabold text-indigo-700 hidden"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1 tracking-wide">Total Recouvrement</p>
                        <div id="totalRecoveryLoading" class="animate-pulse flex space-x-4">
                            <div class="h-8 bg-gray-200 rounded w-3/4"></div>
                        </div>
                        <p id="totalRecovery" class="text-3xl font-extrabold text-green-600 hidden"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1 tracking-wide">Pourcentage</p>
                        <div id="percentageLoading" class="animate-pulse flex space-x-4">
                            <div class="h-2.5 bg-gray-200 rounded-full w-full"></div>
                        </div>
                        <div id="percentageContainer" class="hidden flex items-center gap-3 mt-1">
                            <div class="w-full bg-gray-200 rounded-full h-3 mr-2">
                                <div id="progressBar" class="progress-bar bg-indigo-600 h-3 rounded-full"></div>
                            </div>
                            <span id="percentage" class="text-lg font-bold text-gray-700"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-2">
                <span class="text-xs text-gray-400">© 2025 BNM Dashboard</span>
            </div>
        </div>
    </div>
<!-- Edited -->
    <!-- Edit Goal Modal -->
    <div id="editGoalModal" class="fixed inset-0 bg-black bg-opacity-50 dark:bg-black dark:bg-opacity-70 hidden flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4 shadow-xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Modifier l'Objectif Mensuel</h3>
                <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label for="newGoalInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nouvelle valeur de l'objectif (DZD)
                    </label>
                    <input type="number" 
                           id="newGoalInput" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 transition-colors"
                           placeholder="Entrez la nouvelle valeur"
                           min="0"
                           step="1000">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancelBtn" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors">
                        Annuler
                    </button>
                    <button id="submitBtn" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-md disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span id="submitText">Enregistrer</span>
                        <span id="submitLoading" class="hidden ml-2">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edited -->
<script>
        // DOM Elements
        const refreshBtn = document.getElementById('refreshBtn');
        const monthlyGoal = document.getElementById('monthlyGoal');
        const monthlyGoalLoading = document.getElementById('monthlyGoalLoading');
        const totalRecovery = document.getElementById('totalRecovery');
        const totalRecoveryLoading = document.getElementById('totalRecoveryLoading');
        const percentage = document.getElementById('percentage');
        const percentageContainer = document.getElementById('percentageContainer');
        const percentageLoading = document.getElementById('percentageLoading');
        const progressBar = document.getElementById('progressBar');
        
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
        async function fetchRecouvrementData() {
            try {
                showLoading();
                const response = await fetch(API_CONFIG.getApiUrl('/recouvrement'));
                    
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
                updateUI(data.goal, data.recovery, data.percentage);
            } else {
                // Fallback to loading state if server fails
                hideLoading();
                monthlyGoal.textContent = 'Erreur de chargement';
                totalRecovery.textContent = 'Erreur de chargement';
                percentage.textContent = '0%';
                progressBar.style.width = '0%';
            }
        }

        // Manual refresh function
        async function refreshData() {
            // Add spinning animation to refresh icon
            const refreshIcon = refreshBtn.querySelector('i');
            refreshIcon.classList.add('fa-spin');
            refreshBtn.disabled = true;
            
            const data = await fetchRecouvrementData();
            if (data) {
                updateUI(data.goal, data.recovery, data.percentage);
            }
            
            // Remove spinning animation
            refreshIcon.classList.remove('fa-spin');
            refreshBtn.disabled = false;
        }

        // Event Listeners
        refreshBtn.addEventListener('click', refreshData);
        // Edited -->
        
        // Edit Goal Modal Elements
        const editGoalBtn = document.getElementById('editGoalBtn');
        const editGoalModal = document.getElementById('editGoalModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const submitBtn = document.getElementById('submitBtn');
        const newGoalInput = document.getElementById('newGoalInput');
        const submitText = document.getElementById('submitText');
        const submitLoading = document.getElementById('submitLoading');
        
        // Modal Functions
        function openModal() {
            editGoalModal.classList.remove('hidden');
            // Pre-fill with current goal value
            const currentGoalText = monthlyGoal.textContent;
            // Parse formatted number properly (handles commas, spaces, and decimals)
            const cleanNumber = currentGoalText.replace(/[^\d.,]/g, '').replace(',', '.');
            const currentGoalValue = parseFloat(cleanNumber);
            if (!isNaN(currentGoalValue)) {
                newGoalInput.value = currentGoalValue;
            }
            newGoalInput.focus();
        }
        
        function closeModal() {
            editGoalModal.classList.add('hidden');
            newGoalInput.value = '';
        }
        
        // Modal Event Listeners
        if (editGoalBtn) {
            editGoalBtn.addEventListener('click', openModal);
        }
        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        // Close modal when clicking outside
        editGoalModal.addEventListener('click', function(e) {
            if (e.target === editGoalModal) {
                closeModal();
            }
        });
        
        // Submit new goal
        submitBtn.addEventListener('click', async function() {
            const newValue = parseFloat(newGoalInput.value);
            
            if (!newValue || newValue <= 0) {
                alert('Veuillez entrer une valeur valide supérieure à 0.');
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
            
            try {
                const response = await fetch(API_CONFIG.getApiUrl('/update-monthly-goal'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        new_goal: newValue
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update the UI with the new value
                    monthlyGoal.textContent = newValue.toLocaleString('fr-FR') + ' DZD';
                    
                    // Refresh data to update percentage
                    await refreshData();
                    
                    // Close modal
                    closeModal();
                    
                    // Show success message
                    alert('Objectif mensuel mis à jour avec succès!');
                } else {
                    throw new Error(result.error || 'Erreur lors de la mise à jour');
                }
                
            } catch (error) {
                console.error('Error updating goal:', error);
                alert('Erreur lors de la mise à jour: ' + error.message);
            } finally {
                // Hide loading state
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            }
        });
        
        // Handle Enter key in input
        newGoalInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                submitBtn.click();
            }
        });
         // Edited -->
        // Initialize the app
        loadInitialData();
    </script>
</body>
</html>