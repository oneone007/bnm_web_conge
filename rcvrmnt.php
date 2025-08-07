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
                    <button id="refreshBtn" class="refresh-btn text-indigo-600 text-sm flex items-center gap-2 shadow-sm">
                        <i class="fas fa-sync-alt"></i>
                        <span>Actualiser</span>
                    </button>
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
        
        // Initialize the app
        loadInitialData();
    </script>
</body>
</html>