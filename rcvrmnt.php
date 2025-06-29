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
        .progress-bar {
            transition: width 1s ease-in-out;
        }

        .refresh-btn {
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            transform: scale(1.05);
        }

        .refresh-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        .dark-mode .refresh-btn {
            color: #60a5fa;
        }

        .dark-mode .refresh-btn:hover {
            color: #3b82f6;
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
                <button id="refreshBtn" class="refresh-btn text-indigo-600 hover:text-indigo-800 transition-colors text-sm flex items-center space-x-1">
                    <i class="fas fa-sync-alt"></i>
                    <span>Actualiser</span>
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
                const response = await fetch('http://192.168.1.94:5000/recouvrement');
                    
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
        
        // Initialize the app
        loadInitialData();
    </script>
</body>
</html>