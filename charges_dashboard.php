<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Check user role if needed
if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Sup Achat', 'Sup Vente'])) {
    header("Location: Acess_Denied");    
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord des Charges</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="theme.js" defer></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .table-container {
            height: auto;
            max-height: none;
            overflow-y: visible;
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .table-container table {
            width: 100%;
            table-layout: auto;
            min-width: 100%;
            max-width: 100%;
            border-collapse: collapse;
        }

        thead {
            position: sticky;
            top: 0;
            background-color: #f3f4f6;
            z-index: 10;
        }

        th, td {
            white-space: nowrap;
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .dark .table-container {
            border-color: #374151;
        }

        .dark .table-header {
            background-color: #374151 !important;
            color: #f9fafb !important;
        }

        .dark .table-row:nth-child(odd) {
            background-color: #1f2937;
            color: #f9fafb;
        }

        .dark .table-row:nth-child(even) {
            background-color: #474d53;
            color: #ececec;
        }

        .charge-type-row {
            background-color: #dbeafe !important;
            font-weight: bold;
            cursor: pointer;
        }

        .charge-type-row:hover {
            background-color: #bfdbfe !important;
        }

        .charge-row {
            background-color: #f0f9ff !important;
            cursor: pointer;
            padding-left: 20px;
        }

        .charge-row:hover {
            background-color: #e0f2fe !important;
        }

        .detail-row {
            background-color: #fafafa !important;
            padding-left: 40px;
        }

        .invoice-row {
            background-color: #f8fafc !important;
            padding-left: 60px;
            cursor: pointer;
        }

        .invoice-row:hover {
            background-color: #f1f5f9 !important;
        }

        .line-row {
            background-color: #f9fafb !important;
            padding-left: 80px;
            font-size: 0.9em;
        }

        .dark .charge-type-row {
            background-color: #1e3a8a !important;
            color: #f9fafb;
        }

        .dark .charge-type-row:hover {
            background-color: #1e40af !important;
        }

        .dark .charge-row {
            background-color: #1e40af !important;
            color: #f9fafb;
        }

        .dark .charge-row:hover {
            background-color: #2563eb !important;
        }

        .dark .detail-row {
            background-color: #374151 !important;
            color: #f9fafb;
        }

        .dark .invoice-row {
            background-color: #1f2937 !important;
            color: #f9fafb;
        }

        .dark .invoice-row:hover {
            background-color: #374151 !important;
        }

        .dark .line-row {
            background-color: #111827 !important;
            color: #f9fafb;
        }

        .file-icon {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            color: #3b82f6;
        }

        .dark .file-icon {
            color: #60a5fa;
        }

        .folder-icon {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            color: #2563eb;
        }

        .dark .folder-icon {
            color: #3b82f6;
        }

        .expandable {
            cursor: pointer;
        }

        .expandable:hover {
            background-color: #f3f4f6;
        }

        .dark .expandable:hover {
            background-color: #374151;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #6b7280;
        }

        .dark .loading {
            color: #9ca3af;
        }

        .total-summary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .dark .total-summary {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        }


        .hidden {
            display: none;
        }

        .icon {
            width: 16px;
            height: 16px;
            margin-right: 5px;
        }

        .dark .icon {
            filter: brightness(1.2);
        }

        /* Statistics Section Styles */
        .stats-card {
            transition: all 0.3s ease;
        }

        /* Purple color usage replaced with blue throughout */
        .stats-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        }

        /* Enhanced table styling with better animations */
        .stats-table tr:hover {
            background-color: rgba(59, 130, 246, 0.05);
            transform: translateX(4px);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .dark .stats-table tr:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        /* Enhanced progress bars with shimmer effect */
        .progress-bar {
            animation: progressFill 1.2s ease-out, shimmer 2s infinite;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.4),
                transparent
            );
            transform: translateX(-100%);
            animation: shimmer 2s infinite;
        }

        .dark .progress-bar::after {
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
        }

        @keyframes progressFill {
            from { width: 0%; }
            to { width: var(--progress-width); }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Enhanced card animations with glow effect */
        .metric-card {
            background: linear-gradient(135deg, var(--card-color-start), var(--card-color-end));
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border-radius: 16px;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .dark .metric-card::before {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        }

        .metric-card:hover::before {
            left: 100%;
        }

        .metric-card:hover {
            transform: translateY(-12px) scale(1.05);
            box-shadow: 0 30px 60px rgba(59, 130, 246, 0.2);
        }

        /* Chart container enhancements */
        .chart-bg {
            background: radial-gradient(circle at 30% 30%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 70%, rgba(16, 185, 129, 0.08) 0%, transparent 50%);
            border-radius: 20px;
            position: relative;
        }

        .dark .chart-bg {
            background: radial-gradient(circle at 30% 30%, rgba(59, 130, 246, 0.12) 0%, transparent 50%),
                        radial-gradient(circle at 70% 70%, rgba(16, 185, 129, 0.12) 0%, transparent 50%);
        }

        /* Enhanced filter section animations */
        .date-container {
            animation: slideInFromTop 0.6s ease-out;
        }

        @keyframes slideInFromTop {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced button hover effects */
        .date-container button {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .date-container button:hover {
            transform: translateY(-2px) scale(1.1);
        }

        .date-container button:active {
            transform: translateY(0) scale(0.98);
        }

        /* Fade in animation */
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Slide in up animation */
        .animate-slideInUp {
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Pulse animation for loading elements */
        .animate-pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Enhanced hover effects for table rows */
        .table-container tbody tr {
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .table-container tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.05);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .dark .table-container tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.15);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        /* Dark mode for thead in main table */
        .dark thead {
            background-color: #374151;
            color: #f9fafb;
        }

        /* Dark mode for table borders */
        .dark th, .dark td {
            border-color: #4b5563;
        }

        /* Comprehensive dark mode support for all text colors */
        .dark .text-gray-500 {
            color: #9ca3af !important;
        }

        .dark .text-gray-600 {
            color: #9ca3af !important;
        }

        .dark .text-blue-500 {
            color: #60a5fa !important;
        }

        .dark .text-blue-600 {
            color: #3b82f6 !important;
        }

        .dark .text-green-600 {
            color: #34d399 !important;
        }

        .dark .text-red-600 {
            color: #f87171 !important;
        }

        .dark .text-red-700 {
            color: #ef4444 !important;
        }

        .dark .text-red-800 {
            color: #dc2626 !important;
        }

        /* Dark mode for background colors in JavaScript-generated content */
        .dark .bg-red-100 {
            background-color: #7f1d1d !important;
        }

        .dark .bg-red-50 {
            background-color: rgba(127, 29, 29, 0.2) !important;
        }

        .dark .bg-green-100 {
            background-color: #064e3b !important;
        }

        .dark .bg-yellow-100 {
            background-color: #78350f !important;
        }

        .dark .bg-blue-100 {
            background-color: #1e3a8a !important;
        }

        .dark .bg-orange-100 {
            background-color: #9a3412 !important;
        }

        /* Dark mode for progress bars in stats */
        .dark .bg-gray-200 {
            background-color: #374151 !important;
        }

        .dark .bg-gray-700 {
            background-color: #1f2937 !important;
        }

        /* Dark mode for chart icon backgrounds */
        .dark .chart-icon-bg {
            background-color: rgba(59, 130, 246, 0.2) !important;
        }

        /* Dark mode for card hover effects */
        .dark .stats-card:hover {
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.25);
        }

        /* Dark mode for loading spinner */
        .dark .animate-spin {
            border-color: #374151;
            border-bottom-color: #60a5fa;
        }

        /* Dark mode for metric card hover effects */
        .dark .metric-card:hover {
            box-shadow: 0 30px 60px rgba(59, 130, 246, 0.3);
        }

        /* Dark mode for inline SVG icons */
        .dark svg.text-blue-500 {
            color: #60a5fa !important;
        }

        .dark svg.text-green-600 {
            color: #34d399 !important;
        }

        .dark svg.text-blue-600 {
            color: #3b82f6 !important;
        }

        /* Dark mode for button text and icons inside JavaScript */
        .dark .text-xs.text-blue-500 {
            color: #60a5fa !important;
        }

        /* Ensure all table content has proper dark mode colors */
        .dark table td {
            color: #f9fafb;
        }

        .dark table th {
            color: #f9fafb;
        }

        /* Dark mode for any missed gray text */
        .dark [class*="text-gray-"] {
            color: #9ca3af !important;
        }

        /* Dark mode for focus states */
        .dark input:focus {
            border-color: #60a5fa;
            ring-color: rgba(96, 165, 250, 0.5);
        }

        .dark button:focus {
            ring-color: rgba(96, 165, 250, 0.5);
        }

        /* Comprehensive dark mode overrides for any missed elements */
        .dark td, .dark th {
            color: #f9fafb !important;
        }

        .dark td span, .dark th span {
            color: inherit;
        }

        .dark td strong, .dark th strong {
            color: #ffffff !important;
        }

        /* Override for dynamically added content */
        .dark .charge-type-row td {
            color: #f9fafb !important;
        }

        .dark .charge-row td {
            color: #f9fafb !important;
        }

        .dark .invoice-row td {
            color: #f9fafb !important;
        }

        .dark .line-row td {
            color: #f9fafb !important;
        }

        .dark .detail-row td {
            color: #f9fafb !important;
        }

        /* FontAwesome icons in dark mode */
        .dark .fas, .dark .fa {
            color: #9ca3af !important;
        }

        /* SVG icons in dark mode */
        .dark svg {
            color: #60a5fa;
        }

        /* Ensure all button text is visible in dark mode */
        .dark button {
            border-color: inherit;
        }

        /* Progress bar text in dark mode */
        .dark .progress-bar {
            color: #f9fafb;
        }

        /* Table cell content - force white text in dark mode */
        .dark table tbody tr td {
            color: #f9fafb !important;
        }

        .dark table thead tr th {
            color: #f9fafb !important;
        }

        /* Override any inline styles that might interfere */
        .dark [style*="color"] {
            color: #f9fafb !important;
        }

        /* Specific overrides for text elements */
        .dark .text-black {
            color: #f9fafb !important;
        }

        .dark [class*="text-black"] {
            color: #f9fafb !important;
        }

        /* Force dark background for main elements */
        .dark body {
            background-color: #111827 !important;
        }

        .dark .table-wrapper {
            background-color: transparent !important;
        }

        .dark .table-container {
            background-color: #1f2937 !important;
        }

        .dark #chargesTable {
            background-color: #1f2937 !important;
        }

        /* Override any remaining white backgrounds */
        .dark .bg-white {
            background-color: #1f2937 !important;
        }

        /* Ensure all divs have proper dark background */
        .dark div:not(.bg-gradient-to-br):not(.bg-gradient-to-r) {
            background-color: inherit;
        }

        /* Modal dark styling */
        .dark #detailsModal {
            color: #f9fafb;
        }

        .dark #detailsModal .bg-white {
            background-color: #1f2937 !important;
        }

        .dark #detailsModal table {
            background-color: #1f2937 !important;
            color: #f9fafb !important;
        }

        .dark #detailsModal table th {
            background-color: #374151 !important;
            color: #f9fafb !important;
        }

        .dark #detailsModal table td {
            background-color: #1f2937 !important;
            color: #f9fafb !important;
        }

        .dark #detailsModal h3,
        .dark #detailsModal h4,
        .dark #detailsModal p,
        .dark #detailsModal span,
        .dark #detailsModal div {
            color: #f9fafb !important;
        }

        /* Override any hardcoded text colors in modal */
        .dark #modalContent * {
            color: #f9fafb !important;
        }

        .dark #modalContent table * {
            color: #f9fafb !important;
        }

        /* Ensure modal backdrop in dark mode */
        .dark #detailsModal {
            background-color: rgba(0, 0, 0, 0.7);
        }

        /* Force header background in dark mode */
        .dark .bg-gradient-to-r.from-blue-50.to-indigo-50 {
            background: linear-gradient(to right, #374151, #4b5563) !important;
        }

        /* Fix hover effects in analysis table */
        .dark .hover\:bg-blue-50:hover {
            background-color: #374151 !important;
        }

        /* Override any blue-50 backgrounds in dark mode */
        .dark .bg-blue-50 {
            background-color: #374151 !important;
        }

        /* Ensure table row hover in statistics table */
        .dark tbody tr:hover {
            background-color: #374151 !important;
        }

        /* Specific override for the analysis table header */
        .dark [class*="from-blue-50"] {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .dark [class*="to-indigo-50"] {
            background: linear-gradient(to right, #374151, #4b5563) !important;
        }
    </style>
</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold text-gray-900 dark:text-white text-center">
                Tableau de Bord des Charges
            </h1>
        </div>
        

        <!-- Filter Section -->
        <div class="mb-6">
            <!-- Date Inputs -->
            <div class="date-container flex space-x-4 items-center mb-4">
                <div class="flex items-center space-x-2">
                    <label for="date_debut" class="text-sm font-semibold text-gray-900 dark:text-white">Date Début:</label>
                    <input type="date" id="date_debut" class="border rounded px-2 py-1 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="flex items-center space-x-2">
                    <label for="date_fin" class="text-sm font-semibold text-gray-900 dark:text-white">Date Fin:</label>
                    <input type="date" id="date_fin" class="border rounded px-2 py-1 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <!-- Refresh Button with Icon -->
                <button id="filterBtn" class="p-3 bg-white text-blue-500 rounded-full shadow-lg hover:shadow-xl border border-blue-500 transition duration-200 flex items-center justify-center dark:bg-gray-800 dark:text-blue-400 dark:border-blue-400 dark:hover:bg-gray-700" title="Filtrer les données">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
                    </svg>
                </button>

                <!-- Download Button -->
                <button id="downloadBtn" class="p-3 bg-green-500 text-white rounded-full shadow-lg hover:shadow-xl hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 transition duration-200 flex items-center justify-center" title="Télécharger Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </button>

                <!-- Statistics Toggle Button -->
                <button id="statsToggleBtn" class="p-3 bg-blue-500 text-white rounded-full shadow-lg hover:shadow-xl hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition duration-200 flex items-center justify-center hidden" title="Afficher/Masquer les Statistiques">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Total Summary -->
        <div id="totalSummary" class="total-summary hidden">
            <h2 class="text-2xl font-bold mb-2">Total des Charges</h2>
            <p id="totalAmount" class="text-4xl font-bold">0.00 DZD</p>
            <p id="dateRange" class="text-sm mt-2 opacity-90"></p>
        </div>

        <!-- Statistics Section -->
        <div id="statisticsSection" class="hidden mb-6 animate-fadeIn">
            <!-- Key Metrics Row -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-slideInUp">
                <!-- Types Count Card -->
                <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Types de Charges</p>
                            <p class="text-2xl font-bold" id="totalTypes">0</p>
                            <p class="text-green-200 text-xs mt-1">Catégories actives</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Charges Count Card -->
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-indigo-100 text-sm font-medium">Total Charges</p>
                            <p class="text-2xl font-bold" id="totalCharges">0</p>
                            <p class="text-indigo-200 text-xs mt-1">Postes de dépenses</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 002-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total des Factures Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Factures</p>
                            <p class="text-2xl font-bold" id="totalInvoices">0</p>
                            <p class="text-blue-200 text-xs mt-1" id="statsPeriod">-</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Average Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium" style="text-shadow: 0 1px 4px rgba(0,0,0,0.18);">Moyenne/Charge</p>
                            <p class="text-lg font-bold" id="averagePerCharge" style="text-shadow: 0 1px 4px rgba(0,0,0,0.18);">0.00 DZD</p>
                            <p class="text-blue-200 text-xs mt-1" style="text-shadow: 0 1px 4px rgba(0,0,0,0.18);">Coût moyen</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
                <!-- Doughnut Chart - Distribution -->
                <div class="xl:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Distribution des types des Charges</h3>
                        <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="relative h-80">
                        <canvas id="doughnutChart"></canvas>
                    </div>
                </div>

                <!-- Horizontal Bar Chart - Top Charges -->
                <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Types de Charges</h3>
                        <div class="bg-green-100 dark:bg-green-900 rounded-lg p-2">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="relative h-80">
                        <canvas id="horizontalBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Analysis Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 border-b border-gray-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Analyse Financière par Type
                        </h3>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span id="tableStatsInfo">-</span>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Type de Charge
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Montant Total
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Part (%)
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Nb Charges
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Moyenne
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Impact
                                </th>
                            </tr>
                        </thead>
                        <tbody id="advancedStatsTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-wrapper">
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800" style="height: auto; max-height: none; overflow-y: visible;">
                <div class="overflow-x-auto" style="overflow-y: visible;">
                    <table id="chargesTable" class="dark:bg-gray-800" style="height: 100%;">
                        <thead>
                            <tr class="table-header dark:bg-gray-700 dark:text-white">
                                <th class="px-4 py-3 text-left dark:text-white">Type de Charge / Charge / Facture / Ligne</th>
                                <th class="px-4 py-3 text-right dark:text-white">Montant (DZD)</th>
                                <th class="px-4 py-3 text-right dark:text-white">Pourcentage (%)</th>
                                <th class="px-4 py-3 text-center dark:text-white">Détails</th>
                            </tr>
                        </thead>
                        <tbody id="chargesTableBody">
                            <tr>
                                <td colspan="4" class="loading">
                                    <svg class="file-icon inline" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Sélectionnez une période et cliquez sur le bouton de rafraîchissement pour afficher les données
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Spacer below the table -->
        <div class="mb-20 py-8"></div>
         <div class="mb-20 py-8"></div>

        <!-- Details Modal -->
        <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
            <div class="flex items-start justify-center min-h-screen p-4 py-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-6xl w-full max-h-[90vh] border dark:border-gray-600 flex flex-col">
                    <!-- Modal Header - Fixed -->
                    <div class="flex-shrink-0 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 rounded-t-lg border-b border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between items-center">
                            <h3 id="modalTitle" class="text-xl font-semibold text-gray-900 dark:text-white">Détails de la charge</h3>
                            <button id="closeModal" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-2 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="sr-only">Fermer modal</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Content - Scrollable -->
                    <div class="flex-1 min-h-0 overflow-y-auto">
                        <div id="modalContent" class="p-6 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <!-- Details will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let chargesData = {};
        let expandedChargeTypes = new Set();
        let expandedCharges = new Set();
        let expandedInvoices = new Set();

        // Test server connectivity
        async function testServerConnection() {
            try {
                console.log('Testing server connection...');
                const response = await fetch('http://192.168.1.94:5000/health-check', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    mode: 'cors',
                    signal: AbortSignal.timeout(5000) // 5 second timeout
                });
                
                if (response.ok) {
                    console.log('Server connection: OK');
                    return true;
                } else {
                    console.log('Server connection: Failed with status', response.status);
                    return false;
                }
            } catch (error) {
                console.log('Server connection: Failed -', error.message);
                return false;
            }
        }

        // Check server connectivity on page load
        document.addEventListener('DOMContentLoaded', async function() {
            // Set default dates to current month
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            
            document.getElementById('date_debut').value = firstDay.toISOString().split('T')[0];
            document.getElementById('date_fin').value = today.toISOString().split('T')[0];
        });

        // Event listeners
        document.getElementById('filterBtn').addEventListener('click', fetchChargesData);
        document.getElementById('downloadBtn').addEventListener('click', downloadExcel);
        document.getElementById('closeModal').addEventListener('click', closeModal);
        document.getElementById('statsToggleBtn').addEventListener('click', toggleStatistics);

        let statsVisible = true;

        function toggleStatistics() {
            const statisticsSection = document.getElementById('statisticsSection');
            const toggleBtn = document.getElementById('statsToggleBtn');
            const toggleIcon = toggleBtn.querySelector('svg');
            
            if (statsVisible) {
                statisticsSection.classList.add('hidden');
                toggleBtn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                toggleBtn.classList.add('bg-gray-500', 'hover:bg-gray-600');
                toggleBtn.title = 'Afficher les Statistiques';
                
                // Change icon to show stats
                toggleIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                `;
                statsVisible = false;
            } else {
                statisticsSection.classList.remove('hidden');
                toggleBtn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
                toggleBtn.classList.add('bg-blue-500', 'hover:bg-blue-600');
                toggleBtn.title = 'Masquer les Statistiques';
                
                // Change icon to hide stats
                toggleIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                `;
                statsVisible = true;
            }
        }

        // Close modal when clicking outside
        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        async function fetchChargesData() {
            const dateDebut = document.getElementById('date_debut').value;
            const dateFin = document.getElementById('date_fin').value;

            if (!dateDebut || !dateFin) {
                alert('Veuillez sélectionner les dates de début et de fin');
                return;
            }

            const tableBody = document.getElementById('chargesTableBody');
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="loading text-center py-12">
                        <div class="flex items-center justify-center space-x-3">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            <svg class="file-icon inline text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-lg font-medium text-gray-600 dark:text-gray-300">Chargement des données...</span>
                        </div>
                        <div class="mt-4">
                            <div class="w-64 mx-auto bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="bg-blue-500 h-2 rounded-full animate-pulse" style="width: 45%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            `;

            try {
                const apiUrl = `http://192.168.1.94:5000/fetch-charges-dashboard?date_debut=${dateDebut}&date_fin=${dateFin}`;
                console.log('Fetching data from:', apiUrl);
                
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    mode: 'cors'
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const textResponse = await response.text();
                    console.error('Non-JSON response received:', textResponse);
                    throw new Error('Le serveur a retourné une réponse non-JSON');
                }

                const data = await response.json();
                console.log('Received data:', data);

                if (data.error) {
                    throw new Error(data.error);
                }

                chargesData = data;
                updateTotalSummary(data);
                renderTable(data);
                
                // Show statistics toggle button
                document.getElementById('statsToggleBtn').classList.remove('hidden');
            } catch (error) {
                console.error('Error fetching charges data:', error);
                
                let errorMessage = 'Erreur inconnue';
                let errorDetails = 'Veuillez vérifier votre connexion et réessayer';
                
                if (error instanceof TypeError && error.message.includes('Failed to fetch')) {
                    errorMessage = 'Erreur de connexion';
                    errorDetails = 'Impossible de se connecter au serveur (192.168.1.94:5000). Vérifiez que le serveur est en ligne et accessible.';
                } else if (error.message.includes('HTTP error')) {
                    errorMessage = 'Erreur du serveur';
                    errorDetails = error.message;
                } else if (error.message) {
                    errorMessage = 'Erreur de traitement';
                    errorDetails = error.message;
                }
                
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="loading text-center py-12">
                            <div class="flex items-center justify-center space-x-3">
                                <svg class="w-12 h-12 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-left">
                                    <p class="text-lg font-semibold text-red-600 dark:text-red-400">${errorMessage}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Impossible de récupérer les données</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button onclick="fetchChargesData()" class="bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white text-sm px-4 py-2 rounded transition duration-200">
                                    Réessayer
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }

        function updateTotalSummary(data) {
            const totalSummary = document.getElementById('totalSummary');
            const totalAmount = document.getElementById('totalAmount');
            const dateRange = document.getElementById('dateRange');

            totalAmount.textContent = formatCurrency(data.total_all_charges);
            dateRange.textContent = `Du ${data.date_debut} au ${data.date_fin}`;
            totalSummary.classList.remove('hidden');
            
            // Update statistics
            updateStatistics(data);
        }

        function updateStatistics(data) {
            const statisticsSection = document.getElementById('statisticsSection');
            
            // Calculate statistics
            const chargeTypes = Object.values(data.charges_by_type);
            const totalTypes = chargeTypes.length;
            let totalCharges = 0;
            let totalInvoicesCount = 0;
            
            // Count charges and invoices
            chargeTypes.forEach(typeData => {
                totalCharges += typeData.charges.filter(c => c.montant !== 0).length;
                
                // Count total invoices
                typeData.charges.forEach(charge => {
                    if (charge.invoice_details && charge.invoice_details.length > 0) {
                        totalInvoicesCount += charge.invoice_details.length;
                    }
                });
            });
            
            const averagePerCharge = totalCharges > 0 ? data.total_all_charges / totalCharges : 0;
            
            // Update main stats cards
            document.getElementById('totalInvoices').textContent = totalInvoicesCount;
            document.getElementById('statsPeriod').textContent = `${data.date_debut} → ${data.date_fin}`;
            document.getElementById('totalTypes').textContent = totalTypes;
            document.getElementById('totalCharges').textContent = totalCharges;
            document.getElementById('averagePerCharge').textContent = formatCurrency(averagePerCharge);
            
            // Update table info
            document.getElementById('tableStatsInfo').textContent = `${totalTypes} types • ${totalCharges} charges actives • ${totalInvoicesCount} factures`;
            
            // Update charts
            updateDoughnutChart(data);
            updateHorizontalBarChart(data);
            updateAdvancedStatsTable(data);
            
            // Show statistics section with animation
            statisticsSection.classList.remove('hidden');
            
            // Add staggered animation to cards
            const cards = statisticsSection.querySelectorAll('.grid > div');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-slideInUp');
            });
        }

        let doughnutChart = null;
        let horizontalBarChart = null;

        function updateDoughnutChart(data) {
            const ctx = document.getElementById('doughnutChart').getContext('2d');
            if (doughnutChart) {
                doughnutChart.destroy();
            }
            // Only include types with non-zero total
            const chargeTypes = Object.values(data.charges_by_type).filter(type => Math.abs(type.type_total) > 0);
            const labels = chargeTypes.map(type => type.type_name);
            const amounts = chargeTypes.map(type => Math.abs(type.type_total));
            
            // Enhanced modern color palette with gradients
            const colors = [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1',
                '#14B8A6', '#F59E0B', '#EF4444', '#22C55E', '#A855F7'
            ];
            
            const borderColors = [
                '#1D4ED8', '#059669', '#D97706', '#DC2626', '#7C3AED',
                '#0891B2', '#65A30D', '#EA580C', '#DB2777', '#4F46E5',
                '#0F766E', '#D97706', '#DC2626', '#16A34A', '#9333EA'
            ];
            
            doughnutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: amounts,
                        backgroundColor: colors.slice(0, labels.length),
                        borderWidth: 3,
                        borderColor: borderColors.slice(0, labels.length),
                        hoverBorderWidth: 5,
                        hoverBorderColor: '#ffffff',
                        hoverBackgroundColor: colors.slice(0, labels.length).map(color => color + 'DD')
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                },
                                color: document.documentElement.classList.contains('dark') ? '#6B7280' : '#000000',
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    
                                    // Check if dark mode is active
                                    const isDarkMode = document.documentElement.classList.contains('dark');
                                    const textColor = isDarkMode ? '#6B7280' : '#000000';
                                    
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const percentage = ((value / total) * 100).toFixed(3);
                                        return {
                                            text: `${label} (${percentage}%)`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].backgroundColor[i],
                                            pointStyle: 'circle',
                                            fontColor: textColor // Add this line for text color
                                        };
                                    });
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = formatCurrency(context.parsed);
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(3);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1200,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'nearest'
                    },
                    elements: {
                        arc: {
                            hoverBackgroundColor: function(ctx) {
                                const color = ctx.dataset.backgroundColor[ctx.dataIndex];
                                return color + 'CC';
                            }
                        }
                    }
                }
            });
        }

        function updateHorizontalBarChart(data) {
            const ctx = document.getElementById('horizontalBarChart').getContext('2d');
            if (horizontalBarChart) {
                horizontalBarChart.destroy();
            }
            // Get charge types with non-zero total
            const chargeTypes = Object.values(data.charges_by_type).filter(type => Math.abs(type.type_total) > 0);
            
            // Sort by amount descending
            chargeTypes.sort((a, b) => Math.abs(b.type_total) - Math.abs(a.type_total));
            
            const labels = chargeTypes.map(type => {
                const name = type.type_name.length > 30 ? type.type_name.substring(0, 30) + '...' : type.type_name;
                return name;
            });
            const amounts = chargeTypes.map(type => Math.abs(type.type_total));
            
            // Generate enhanced gradient colors with transparency
            const gradientColors = amounts.map((_, index) => {
                const baseColors = [
                    'rgba(59, 130, 246, 0.9)',   // Blue
                    'rgba(16, 185, 129, 0.9)',   // Green
                    'rgba(245, 158, 11, 0.9)',   // Yellow
                    'rgba(239, 68, 68, 0.9)',    // Red
                    'rgba(139, 92, 246, 0.9)',   // Purple
                    'rgba(6, 182, 212, 0.9)',    // Cyan
                    'rgba(132, 204, 22, 0.9)',   // Lime
                    'rgba(249, 115, 22, 0.9)'    // Orange
                ];
                return baseColors[index % baseColors.length];
            });
            
            const borderColors = amounts.map((_, index) => {
                const baseColors = [
                    'rgba(29, 78, 216, 1)',      // Blue
                    'rgba(5, 150, 105, 1)',      // Green
                    'rgba(217, 119, 6, 1)',      // Yellow
                    'rgba(220, 38, 38, 1)',      // Red
                    'rgba(124, 58, 237, 1)',     // Purple
                    'rgba(8, 145, 178, 1)',      // Cyan
                    'rgba(101, 163, 13, 1)',     // Lime
                    'rgba(234, 88, 12, 1)'       // Orange
                ];
                return baseColors[index % baseColors.length];
            });
            
            horizontalBarChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Montant (DZD)',
                        data: amounts,
                        backgroundColor: gradientColors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 20,
                        hoverBackgroundColor: gradientColors.map(color => color.replace('0.9', '1')),
                        hoverBorderColor: borderColors,
                        hoverBorderWidth: 3
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const chargeType = chargeTypes[context.dataIndex];
                                    const activeCharges = chargeType.charges.filter(c => c.montant !== 0);
                                    return [
                                        `Type: ${chargeType.type_name}`,
                                        `Montant: ${formatCurrency(context.parsed.x)}`,
                                        `Pourcentage: ${chargeType.type_percentage.toFixed(2)}%`,
                                        `Charges actives: ${activeCharges.length}`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrencyShort(value);
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        duration: 1200,
                        easing: 'easeOutQuart',
                        delay: (context) => {
                            return context.dataIndex * 100;
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'y'
                    }
                }
            });
        }

        function updateAdvancedStatsTable(data) {
            const tbody = document.getElementById('advancedStatsTableBody');
            tbody.innerHTML = '';
            
            const chargeTypes = Object.values(data.charges_by_type);
            
            // Sort by amount (descending)
            chargeTypes.sort((a, b) => Math.abs(b.type_total) - Math.abs(a.type_total));
            
            chargeTypes.forEach((typeData, index) => {
                const activeCharges = typeData.charges.filter(c => c.montant !== 0);
                const chargeCount = activeCharges.length;
                const averagePerType = chargeCount > 0 ? typeData.type_total / chargeCount : 0;
                
                // Determine impact level
                const percentage = typeData.type_percentage;
                let impactLevel, impactColor, impactText;
                
                if (percentage >= 30) {
                    impactLevel = 'Très Élevé';
                    impactColor = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                } else if (percentage >= 15) {
                    impactLevel = 'Élevé';
                    impactColor = 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
                } else if (percentage >= 5) {
                    impactLevel = 'Modéré';
                    impactColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                } else {
                    impactLevel = 'Faible';
                    impactColor = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                }
                
                const row = document.createElement('tr');
                row.className = 'hover:bg-blue-50 dark:hover:bg-gray-700 transition-all duration-300 transform hover:scale-[1.02]';
                
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center transform transition-transform duration-200 hover:translate-x-2">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-lg">
                                    <span class="text-white font-bold text-sm">${index + 1}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    ${typeData.type_name}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    ${chargeCount} charge${chargeCount > 1 ? 's' : ''}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                            ${formatCurrency(typeData.type_total)}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center">
                            <div class="w-20 bg-gray-200 rounded-full h-3  mr-3 overflow-hidden shadow-inner">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-1000 ease-out shadow-sm" 
                                     style="width: ${percentage}%; animation: progressFill 1.5s ease-out;">
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white min-w-[3rem]">
                                ${percentage.toFixed(3)}%
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            ${chargeCount}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white">
                        ${formatCurrency(averagePerType)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${impactColor}">
                            ${impactLevel}
                        </span>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }

        // Helper function for currency formatting (short version)
        function formatCurrencyShort(amount) {
            if (Math.abs(amount) >= 1000000) {
                return (amount / 1000000).toFixed(1) + 'M DZD';
            } else if (Math.abs(amount) >= 1000) {
                return (amount / 1000).toFixed(0) + 'K DZD';
            }
            return formatCurrency(amount);
        }

        function renderTable(data) {
            const tableBody = document.getElementById('chargesTableBody');
            tableBody.innerHTML = '';

            Object.entries(data.charges_by_type).forEach(([typeId, typeData]) => {
                // Ensure typeData has the expected structure
                if (!typeData || !typeData.charges) {
                    console.error(`Invalid typeData structure for type ${typeId}:`, typeData);
                    return;
                }
                
                // Skip charge types with zero total
                if (typeData.type_total === 0) {
                    return;
                }
                // Add charge type row
                const typeRow = document.createElement('tr');
                typeRow.className = 'charge-type-row expandable';
                typeRow.dataset.typeId = typeId;
                
                // Make the entire row clickable
                typeRow.style.cursor = 'pointer';
                typeRow.addEventListener('click', () => toggleChargeType(typeId));
                
                // Add different styling for charge types with zero total
                const isZeroTotal = typeData.type_total === 0;
                const additionalClass = isZeroTotal ? ' opacity-70' : '';
                
                typeRow.innerHTML = `
                    <td class="px-4 py-3 dark:text-white${additionalClass}">
                        <i class="fas fa-chevron-right icon dark:text-gray-300" id="typeIcon${typeId}"></i>
                        <svg class="folder-icon inline dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                        </svg>
                        <strong class="dark:text-white">${typeData.type_name}</strong>
                    </td>
                    <td class="px-4 py-3 text-right font-bold dark:text-white${additionalClass}">${formatCurrency(typeData.type_total)}</td>
                    <td class="px-4 py-3 text-right font-bold dark:text-white${additionalClass}">${typeData.type_percentage.toFixed(3)}%</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-gray-500 text-sm dark:text-gray-400">${typeData.charges.filter(c => c.montant !== 0).length} charges</span>
                    </td>
                `;
                tableBody.appendChild(typeRow);

                // Add charge rows (initially hidden)
                typeData.charges.forEach(charge => {
                    // Ensure charge has the expected structure
                    if (!charge) {
                        console.error(`Invalid charge structure for charge ${charge.charge_id}:`, charge);
                        return;
                    }
                    
                    // Skip charges with zero amount
                    if (charge.montant === 0) {
                        return;
                    }
                    
                    // Initialize invoice_details if it doesn't exist
                    if (!charge.invoice_details) {
                        charge.invoice_details = [];
                    }
                    
                    const chargeRow = document.createElement('tr');
                    chargeRow.className = `charge-row expandable hidden charge-type-${typeId}`;
                    chargeRow.dataset.chargeId = charge.charge_id;
                    
                    // Make the entire row clickable
                    chargeRow.style.cursor = 'pointer';
                    chargeRow.addEventListener('click', () => toggleChargeDetails(charge.charge_id));
                    
                    chargeRow.innerHTML = `
                        <td class="px-4 py-3 dark:text-white" style="padding-left: 40px;">
                            <i class="fas fa-chevron-right icon dark:text-gray-300" id="chargeIcon${charge.charge_id}"></i>
                            <svg class="file-icon inline dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                            <span class="dark:text-white">${charge.charge_name}</span>
                        </td>
                        <td class="px-4 py-3 text-right dark:text-white">${formatCurrency(charge.montant)}</td>
                        <td class="px-4 py-3 text-right dark:text-white">${charge.pourcentage.toFixed(3)}%</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-gray-500 text-sm dark:text-gray-400">${charge.invoice_details.length} factures</span>
                            <br>
                            <button onclick="showChargeDetails(${charge.charge_id}); event.stopPropagation();" 
                                    class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white text-xs px-2 py-1 rounded transition duration-200">
                                <svg class="w-3 h-3 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 0 1-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Voir détails
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(chargeRow);

                    // Add detail rows (initially hidden)
                    if (charge.invoice_details.length > 0) {
                        charge.invoice_details.forEach((invoice, invoiceIndex) => {
                            const invoiceId = `${charge.charge_id}-${invoiceIndex}`;
                            
                            // Add invoice header row
                            const invoiceRow = document.createElement('tr');
                            invoiceRow.className = `invoice-row expandable hidden charge-details-${charge.charge_id}`;
                            invoiceRow.dataset.invoiceId = invoiceId;
                            invoiceRow.addEventListener('click', () => toggleInvoiceLines(invoiceId));
                            
                            const hasLines = invoice.invoice_lines && invoice.invoice_lines.length > 0;
                            const lineCount = hasLines ? invoice.invoice_lines.length : 0;
                            
                            // Calculate the sum of lines that belong to this charge only
                            let chargeSpecificTotal = 0;
                            if (hasLines) {
                                chargeSpecificTotal = invoice.invoice_lines.reduce((sum, line) => {
                                    return sum + (line.line_net_amount || 0);
                                }, 0);
                            } else {
                                // If no lines, use the invoice total (fallback)
                                chargeSpecificTotal = invoice.invoice_total;
                            }
                            
                            invoiceRow.innerHTML = `
                                <td class="px-4 py-3 dark:text-white" style="padding-left: 60px;">
                                    <i class="fas fa-chevron-right icon dark:text-gray-300" id="invoiceIcon${invoiceId}"></i>
                                    <svg class="file-icon inline dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                            <span class="dark:text-white">${invoice.invoice_number} - ${invoice.bpartner_name}</span>
                                </td>
                                <td class="px-4 py-3 text-right dark:text-white">${formatCurrency(chargeSpecificTotal)}</td>
                                <td class="px-4 py-3 text-right dark:text-white">-</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">${invoice.date_invoiced}</span>
                                    ${hasLines ? `<br><span class="text-xs text-blue-500 dark:text-blue-400">${lineCount} ligne(s)</span>` : ''}
                                </td>
                            `;
                            tableBody.appendChild(invoiceRow);

                            // Add invoice line rows
                            if (hasLines) {
                                invoice.invoice_lines.forEach(line => {
                                    const lineRow = document.createElement('tr');
                                    lineRow.className = `line-row hidden invoice-lines-${invoiceId}`;
                                    lineRow.innerHTML = `
                                        <td class="px-4 py-3 dark:text-white" style="padding-left: 80px;">
                                            <svg class="file-icon inline dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="dark:text-white">${line.line_description || 'Description non disponible'}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right dark:text-white">${formatCurrency(line.line_net_amount)}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Qté: ${line.line_qty_entered || 0}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Total: ${formatCurrency(line.line_total_amount)}</span>
                                        </td>
                                    `;
                                    tableBody.appendChild(lineRow);
                                });
                            } else {
                                // Add a "no lines" row
                                const noLinesRow = document.createElement('tr');
                                noLinesRow.className = `line-row hidden invoice-lines-${invoiceId}`;
                                noLinesRow.innerHTML = `
                                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 italic" colspan="4" style="padding-left: 80px;">
                                        <svg class="file-icon inline dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="dark:text-gray-400">Aucune ligne de détail disponible</span>
                                    </td>
                                `;
                                tableBody.appendChild(noLinesRow);
                            }
                        });
                    } else {
                        // Add a "No invoices" row if there are no invoice details
                        const noInvoiceRow = document.createElement('tr');
                        noInvoiceRow.className = `detail-row hidden charge-details-${charge.charge_id}`;
                        noInvoiceRow.innerHTML = `
                            <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 italic" colspan="4" style="padding-left: 60px;">
                                <svg class="file-icon inline dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <span class="dark:text-gray-400">Aucune facture trouvée pour cette période</span>
                            </td>
                        `;
                        tableBody.appendChild(noInvoiceRow);
                    }
                });
            });
        }

        function toggleChargeType(typeId) {
            const chargeRows = document.querySelectorAll(`.charge-type-${typeId}`);
            const icon = document.getElementById(`typeIcon${typeId}`);
            const isExpanded = expandedChargeTypes.has(typeId);

            if (isExpanded) {
                // Collapse: hide all charges and their details
                chargeRows.forEach(row => row.classList.add('hidden'));
                icon.className = 'fas fa-chevron-right icon';
                expandedChargeTypes.delete(typeId);
                
                // Also hide all charge details for this type
                chargeRows.forEach(row => {
                    const chargeId = row.dataset.chargeId;
                    if (chargeId) {
                        const detailRows = document.querySelectorAll(`.charge-details-${chargeId}`);
                        detailRows.forEach(detailRow => detailRow.classList.add('hidden'));
                        expandedCharges.delete(chargeId);
                        const chargeIcon = document.getElementById(`chargeIcon${chargeId}`);
                        if (chargeIcon) chargeIcon.className = 'fas fa-chevron-right icon';
                        
                        // Also hide all invoice lines
                        const invoiceRows = document.querySelectorAll(`[class*="invoice-row"][class*="charge-details-${chargeId}"]`);
                        invoiceRows.forEach(invoiceRow => {
                            const invoiceId = invoiceRow.dataset.invoiceId;
                            if (invoiceId) {
                                const lineRows = document.querySelectorAll(`.invoice-lines-${invoiceId}`);
                                lineRows.forEach(lineRow => lineRow.classList.add('hidden'));
                                expandedInvoices.delete(invoiceId);
                                const invoiceIcon = document.getElementById(`invoiceIcon${invoiceId}`);
                                if (invoiceIcon) invoiceIcon.className = 'fas fa-chevron-right icon';
                            }
                        });
                    }
                });
            } else {
                // Expand: show all charges
                chargeRows.forEach(row => row.classList.remove('hidden'));
                icon.className = 'fas fa-chevron-down icon';
                expandedChargeTypes.add(typeId);
            }
        }

        function toggleChargeDetails(chargeId) {
            const detailRows = document.querySelectorAll(`.charge-details-${chargeId}`);
            const icon = document.getElementById(`chargeIcon${chargeId}`);
            const isExpanded = expandedCharges.has(chargeId);

            if (isExpanded) {
                // Collapse: hide invoice details
                detailRows.forEach(row => row.classList.add('hidden'));
                icon.className = 'fas fa-chevron-right icon';
                expandedCharges.delete(chargeId);
                
                // Also hide all invoice lines for this charge
                const invoiceRows = document.querySelectorAll(`[class*="invoice-row"][class*="charge-details-${chargeId}"]`);
                invoiceRows.forEach(invoiceRow => {
                    const invoiceId = invoiceRow.dataset.invoiceId;
                    if (invoiceId) {
                        const lineRows = document.querySelectorAll(`.invoice-lines-${invoiceId}`);
                        lineRows.forEach(lineRow => lineRow.classList.add('hidden'));
                        expandedInvoices.delete(invoiceId);
                        const invoiceIcon = document.getElementById(`invoiceIcon${invoiceId}`);
                        if (invoiceIcon) invoiceIcon.className = 'fas fa-chevron-right icon';
                    }
                });
            } else {
                // Expand: show invoice details
                detailRows.forEach(row => row.classList.remove('hidden'));
                icon.className = 'fas fa-chevron-down icon';
                expandedCharges.add(chargeId);
            }
        }

        function toggleInvoiceLines(invoiceId) {
            const lineRows = document.querySelectorAll(`.invoice-lines-${invoiceId}`);
            const icon = document.getElementById(`invoiceIcon${invoiceId}`);
            const isExpanded = expandedInvoices.has(invoiceId);

            if (isExpanded) {
                // Collapse: hide line details
                lineRows.forEach(row => row.classList.add('hidden'));
                icon.className = 'fas fa-chevron-right icon';
                expandedInvoices.delete(invoiceId);
            } else {
                // Expand: show line details
                lineRows.forEach(row => row.classList.remove('hidden'));
                icon.className = 'fas fa-chevron-down icon';
                expandedInvoices.add(invoiceId);
            }
        }

        function showChargeDetails(chargeId) {
            const charge = findChargeById(chargeId);
            if (!charge) return;

            const modal = document.getElementById('detailsModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalContent = document.getElementById('modalContent');

            modalTitle.textContent = `Détails: ${charge.charge_name}`;
            
            let tableHTML = `
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-700 z-10">
                            <tr>
                                <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700">Facture N°</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700">Date</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700">Fournisseur</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700">Description Ligne</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-right font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700">Qté</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-right font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700">Prix unitaire</th>
                                <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-right font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700">Montant Net</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (charge.invoice_details.length > 0) {
                charge.invoice_details.forEach(invoice => {
                    if (invoice.invoice_lines && invoice.invoice_lines.length > 0) {
                        // Calculate the sum of lines for this charge only
                        const chargeSpecificTotal = invoice.invoice_lines.reduce((sum, line) => {
                            return sum + (line.line_net_amount || 0);
                        }, 0);
                        
                        // Show each line
                        invoice.invoice_lines.forEach((line, lineIndex) => {
                            tableHTML += `
                                <tr class="${lineIndex === 0 ? 'border-t-2 border-blue-200 dark:border-blue-600' : ''} hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    ${lineIndex === 0 ? `
                                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white" rowspan="${invoice.invoice_lines.length + 1}">
                                            <div class="font-medium">${invoice.invoice_number}</div>
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white" rowspan="${invoice.invoice_lines.length + 1}">
                                            <div class="text-sm">${invoice.date_invoiced}</div>
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white" rowspan="${invoice.invoice_lines.length + 1}">
                                            <div class="font-medium">${invoice.bpartner_name}</div>
                                        </td>
                                    ` : ''}
                                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                        <div class="text-sm">${line.line_description || 'Description non disponible'}</div>
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-right dark:text-white">
                                        <div class="font-mono text-sm">${line.line_qty_entered || 0}</div>
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-right dark:text-white">
                                        <div class="font-mono text-sm font-medium">${formatCurrency(line.line_total_amount)}</div>
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-right dark:text-white">
                                        <div class="font-mono text-sm">${formatCurrency(line.line_net_amount)}</div>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        // Add summary row for this invoice showing total for this charge
                        tableHTML += `
                            <tr class="bg-blue-50 dark:bg-blue-900/30 font-semibold border-b-2 border-blue-200 dark:border-blue-600">
                                <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-right dark:text-white" colspan="4">
                                    <strong>💰 Total pour cette charge:</strong>
                                </td>
                                <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-right dark:text-white" colspan="3">
                                    <strong class="text-blue-600 dark:text-blue-400 font-mono text-lg">${formatCurrency(chargeSpecificTotal)}</strong>
                                </td>
                            </tr>
                        `;
                    } else {
                        // Invoice without lines
                        tableHTML += `
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">${invoice.invoice_number}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">${invoice.date_invoiced}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">${invoice.bpartner_name}</td>
                                <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-gray-500 dark:text-gray-400" colspan="5">
                                    <div class="flex items-center justify-center space-x-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Aucune ligne de détail disponible - Total: <strong class="font-mono">${formatCurrency(invoice.invoice_total)}</strong></span>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                });
            } else {
                tableHTML += `
                    <tr>
                        <td colspan="7" class="border border-gray-300 dark:border-gray-600 px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center space-y-3">
                                <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-lg font-medium">Aucune facture trouvée</p>
                                    <p class="text-sm mt-1">Il n'y a aucune facture associée à cette charge pour la période sélectionnée</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            }

            tableHTML += `
                        </tbody>
                    </table>
                </div>
            `;

            modalContent.innerHTML = tableHTML;
            modal.classList.remove('hidden');
        }

        function findChargeById(chargeId) {
            for (const typeData of Object.values(chargesData.charges_by_type)) {
                const charge = typeData.charges.find(c => c.charge_id == chargeId);
                if (charge) return charge;
            }
            return null;
        }

        function closeModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }

        function downloadExcel() {
            const dateDebut = document.getElementById('date_debut').value;
            const dateFin = document.getElementById('date_fin').value;

            if (!dateDebut || !dateFin) {
                alert('Veuillez sélectionner les dates de début et de fin');
                return;
            }

            try {
                const url = `http://192.168.1.94:5000/download-charges-dashboard-excel?date_debut=${dateDebut}&date_fin=${dateFin}`;
                console.log('Downloading Excel from:', url);
                
                // Create a temporary link element to trigger download
                const link = document.createElement('a');
                link.href = url;
                link.download = `charges_dashboard_${dateDebut}_${dateFin}.xlsx`;
                link.target = '_blank';
                
                // Add the link to the DOM, click it, and remove it
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
            } catch (error) {
                console.error('Error downloading Excel:', error);
                alert('Erreur lors du téléchargement du fichier Excel. Veuillez vérifier que le serveur est accessible.');
            }
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('fr-DZ', {
                style: 'currency',
                currency: 'DZD',
                minimumFractionDigits: 2
            }).format(amount);
        }
    </script>
</body>
</html>
