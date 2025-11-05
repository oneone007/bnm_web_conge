<?php
session_start();

// Check if the user is logged in and session is valid
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Restrict access for 'vente' and 'achat'
// if (isset($_SESSION['Role']) && in_array($_SESSION['Role'], ['Comptable'])) {
//     header("Location: Acess_Denied");    
//     exit();
// }
$page_identifier = 'rot_men_global';

require_once 'check_permission.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Sales & Purchase Recap</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="recap_achat_original.css">
    <script src="theme.js"></script>
    <script src="api_config.js"></script>
    <style>
        .year-tab {
            padding: 8px 16px;
            margin-right: 4px;
            border-radius: 4px;
            cursor: pointer;
            background-color: #e5e7eb;
            color: #4b5563;
        }
        .year-tab.active {
            background-color: #3b82f6;
            color: white;
        }
        
        /* Toggle button styles */
        .toggle-btn {
            border: 2px solid transparent;
            transition: all 0.3s ease;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .toggle-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .toggle-btn:hover::before {
            left: 100%;
        }
        .toggle-btn.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            border-color: #1d4ed8 !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            animation: activePulse 2s ease-in-out infinite;
        }
        .toggle-btn:not(.active) {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            border-color: #cbd5e1 !important;
            color: #475569 !important;
        }
        .toggle-btn:not(.active):hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 100%) !important;
            border-color: #94a3b8 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Dark mode toggle button styles */
        .dark .toggle-btn:not(.active) {
            background: linear-gradient(135deg, #374151 0%, #4b5563 100%) !important;
            border-color: #6b7280 !important;
            color: #f3f4f6 !important;
        }
        .dark .toggle-btn:not(.active):hover {
            background: linear-gradient(135deg, #4b5563 0%, #6b7280 100%) !important;
            border-color: #9ca3af !important;
        }
        
        /* Active button pulse animation */
        @keyframes activePulse {
            0%, 100% {
                box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            }
            50% {
                box-shadow: 0 6px 25px rgba(37, 99, 235, 0.6);
            }
        }
        
        .month-table {
            display: none;
            max-height: 80vh;
            overflow: auto;
        }
        .month-table.active {
            display: block;
            max-height: 80vh;
            overflow: auto;
        }
        .year-selector {
            display: flex;
            margin-bottom: 16px;
            overflow-x: auto;
            padding-bottom: 8px;
        }
        .month-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .month-section {
            margin-bottom: 2rem;
        }
        @media (max-width: 768px) {
            .month-grid {
                grid-template-columns: 1fr;
            }
        }
        .month-header {
            background-color: #3b82f6;
            color: white;
            font-weight: bold;
            padding: 8px 16px;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        /* Responsive layout for year summary cards */
        @media (max-width: 768px) {
            #yearSummaryContainer > div {
                min-width: 280px;
                max-width: 350px;
            }
        }
        @media (max-width: 480px) {
            #yearSummaryContainer > div {
                min-width: 260px;
                max-width: 320px;
            }
        }
        .autocomplete-suggestions {
            background-color: black;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            position: absolute;
        }
        .autocomplete-suggestions div {
            color: #000;
            background-color: white;
        }
        .autocomplete-suggestions div:hover {
            background-color: #f3f4f6;
        }
        .dark .autocomplete-suggestions {
            background-color: #374151;
            border-color: #4b5563;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        .dark .autocomplete-suggestions div {
            color: #f9fafb;
            padding: 8px 12px;
        }
        .dark .autocomplete-suggestions div:hover {
            background-color: #4b5563;
        }
        .dark .autocomplete-suggestions div {
            background-color: white;
            color: #000;
        }
        .dark .autocomplete-suggestions div:hover {
            background-color: #f3f4f6;
        }
        /* New styles for product supplier dropdown */
        #productSupplierContainer, #zoneClientContainer {
            transition: all 0.3s ease;
        }
        #recap_product_supplier, #recap_zone_client {
            background-color: white;
            color: black;
        }
        .dark #recap_product_supplier, .dark #recap_zone_client {
            background-color: #374151;
            color: white;
            border-color: #4b5563;
        }

        /* Select All option styling */
        #recap_product_supplier option[value="SELECT_ALL"],
        #recap_zone_client option[value="SELECT_ALL"] {
            background-color: #e3f2fd !important;
            font-weight: bold !important;
            border-bottom: 1px solid #90caf9;
        }
        
        .dark #recap_product_supplier option[value="SELECT_ALL"],
        .dark #recap_zone_client option[value="SELECT_ALL"] {
            background-color: #1e3a8a !important;
            color: #e3f2fd !important;
        }

        
        /* PDF download button styles */
        .pdf-download-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }
        .pdf-download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 140px;
        }
        .pdf-download-btn:hover {
            background-color: #c0392b;
            transform: translateY(-1px);
        }
        .pdf-download-btn:active {
            transform: translateY(0);
        }
        .pdf-download-btn:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }
        .pdf-icon, .spinner {
            display: flex;
            align-items: center;
        }
        .spinner svg {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .hidden {
            display: none;
        }
        .error-message {
            color: #e74c3c;
            font-size: 13px;
            text-align: center;
            max-width: 200px;
        }
        
        /* Compare Filters Section Styles */
        #compareFiltersSection {
            background: linear-gradient(to bottom, #f9fafb, #ffffff);
            border-radius: 8px;
            padding: 1.5rem;
        }
        .dark #compareFiltersSection {
            background: linear-gradient(to bottom, #1f2937, #111827);
        }
        
        /* Month dropdown styles */
        #compareMonthSelector {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        #compareMonthSelector:hover {
            border-color: #3b82f6;
            background-color: #f9fafb;
        }
        
        .dark #compareMonthSelector:hover {
            border-color: #60a5fa;
            background-color: #374151;
        }
        
        #monthDropdownMenu,
        #achatMonthDropdownMenu,
        #venteMonthDropdownMenu {
            animation: slideDown 0.2s ease-out;
            z-index: 9999 !important;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        #monthDropdownIcon,
        #achatMonthDropdownIcon,
        #venteMonthDropdownIcon {
            transition: transform 0.3s ease;
        }
        
        #monthDropdownIcon.rotate,
        #achatMonthDropdownIcon.rotate,
        #venteMonthDropdownIcon.rotate {
            transform: rotate(180deg);
        }
        
        /* Month checkbox list styles */
        #monthCheckboxList label {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            margin-bottom: 4px;
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        
        #monthCheckboxList label:hover {
            background-color: #f0f9ff;
            border-color: #3b82f6;
            transform: translateX(4px);
        }
        
        #monthCheckboxList input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #3b82f6;
        }
        
        #monthCheckboxList input[type="checkbox"]:checked + span {
            font-weight: 600;
            color: #2563eb;
        }
        
        #monthCheckboxList label:has(input[type="checkbox"]:checked) {
            background-color: #eff6ff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .dark #monthCheckboxList label {
            background-color: #374151;
            border-color: #4b5563;
            color: #f9fafb;
        }
        
        .dark #monthCheckboxList label:hover {
            background-color: #4b5563;
            border-color: #60a5fa;
        }
        
        .dark #monthCheckboxList label:has(input[type="checkbox"]:checked) {
            background-color: #1e3a8a;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        
        /* Compare product search input */
        #compareProductSearch {
            transition: all 0.2s ease;
        }
        
        #compareProductSearch:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Product suggestions dropdown */
        #compareProductSuggestions,
        #achatProductSuggestions,
        #venteProductSuggestions {
            animation: slideDown 0.2s ease-out;
            z-index: 9999 !important;
        }
        
        #compareProductSuggestions div,
        #achatProductSuggestions div,
        #venteProductSuggestions div {
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.15s ease;
            border-bottom: 1px solid #f3f4f6;
        }

        /* ===== Overrides: make Compare month selector simple (no animation/background) ===== */
        /* Disable dropdown animation for Compare */
        #monthDropdownMenu {
            animation: none !important;
        }

        /* Keep icon static (no rotate animation) for Compare */
        #monthDropdownIcon {
            transition: none !important;
        }

        /* Make month items simple: no hover background, no transform, minimal border */
        #monthCheckboxList label {
            background: transparent !important;
            border: none !important;
            padding: 6px 8px !important;
            margin-bottom: 2px !important;
            transition: none !important;
            transform: none !important;
        }

        #monthCheckboxList label:hover {
            background: transparent !important;
            border-color: transparent !important;
            transform: none !important;
        }

        #monthCheckboxList label:has(input[type="checkbox"]:checked) {
            background: transparent !important;
            border-color: transparent !important;
            box-shadow: none !important;
        }

        
        #compareProductSuggestions div:last-child,
        #achatProductSuggestions div:last-child,
        #venteProductSuggestions div:last-child {
            border-bottom: none;
        }
        
        #compareProductSuggestions div:hover,
        #achatProductSuggestions div:hover,
        #venteProductSuggestions div:hover {
            background-color: #f0f9ff;
            color: #2563eb;
            padding-left: 16px;
        }
        
        .dark #compareProductSuggestions div,
        .dark #achatProductSuggestions div,
        .dark #venteProductSuggestions div {
            border-bottom-color: #374151;
        }
        
        .dark #compareProductSuggestions div:hover,
        .dark #achatProductSuggestions div:hover,
        .dark #venteProductSuggestions div:hover {
            background-color: #1e3a8a;
            color: #93c5fd;
        }
        
        /* Selected months text styling */
        #selectedMonthsText {
            font-size: 0.9rem;
            color: #374151;
        }
        
        .dark #selectedMonthsText {
            color: #e5e7eb;
        }
        
        /* Month count badge */
        #monthCountText {
            font-weight: 500;
        }
        
        /* Enhanced scrolling styles */
        .table-container {
            max-height: 70vh;
            overflow: auto;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            width: 100%;
            overflow-x: auto;
            overflow-y: auto;
        }
        .dark .table-container {
            border-color: #4b5563;
        }
        
        /* Prevent page-level horizontal scroll */
        body {
            overflow-x: hidden;
        }
        
        /* Container restrictions */
        .container, .max-w-7xl, #dataContainer {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Year summary container - scrollable horizontally to see all years */
        #yearSummaryContainer {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 0.5rem;
            scroll-behavior: smooth;
            width: 100%;
        }
        
        /* Individual year summary card */
        #yearSummaryContainer > div {
            flex: 0 0 auto;
            min-width: fit-content;
            max-width: none;
            display: flex;
            flex-direction: column;
        }
        
        /* Individual year summary table container - fits content */
        #yearSummaryContainer .table-container {
            height: auto;
            overflow: visible;
            margin: 0 1rem 0 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: white;
            padding: 0.5rem;
            width: fit-content;
            box-shadow: none;
        }
        
        .dark #yearSummaryContainer .table-container {
            border-color: #4b5563;
            background-color: #1f2937;
            padding: 0.5rem;
            box-shadow: none;
        }
        
        /* Year summary table styling - fits content width */
        #yearSummaryContainer table {
            width: auto;
            min-width: auto;
            table-layout: auto;
            border-collapse: collapse;
        }
        
        #yearSummaryContainer table th,
        #yearSummaryContainer table td {
            padding: 6px 8px !important;
            font-size: 0.8rem !important;
            white-space: nowrap;
            text-align: left;
            border: 1px solid #e5e7eb;
        }
        
        .dark #yearSummaryContainer table th,
        .dark #yearSummaryContainer table td {
            border-color: #4b5563;
        }
        
        #yearSummaryContainer table th {
            background-color: #f3f4f6;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .dark #yearSummaryContainer table th {
            background-color: #374151;
        }
        
        /* First column (Type) sticky */
        #yearSummaryContainer table th:first-child,
        #yearSummaryContainer table td:first-child {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 5;
            font-weight: 600;
        }
        
        .dark #yearSummaryContainer table th:first-child,
        .dark #yearSummaryContainer table td:first-child {
            background-color: #1f2937;
        }
        
        #yearSummaryContainer table th:first-child {
            z-index: 15;
        }
        
        /* Column width distribution */
        #yearSummaryContainer table th:first-child,
        #yearSummaryContainer table td:first-child {
            min-width: 80px;
            max-width: 100px;
        }
        
        #yearSummaryContainer table th:not(:first-child),
        #yearSummaryContainer table td:not(:first-child) {
            min-width: 90px;
        }
        
        /* Year summary scroll shadows for visual feedback - horizontal scroll only */
        #yearSummaryContainer {
            position: relative;
        }
        
        #yearSummaryContainer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 30px;
            height: 100%;
            background: linear-gradient(to right, rgba(0,0,0,0.1), transparent);
            pointer-events: none;
            z-index: 10;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        #yearSummaryContainer::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 30px;
            height: 100%;
            background: linear-gradient(to left, rgba(0,0,0,0.1), transparent);
            pointer-events: none;
            z-index: 10;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .dark #yearSummaryContainer::before {
            background: linear-gradient(to right, rgba(255,255,255,0.1), transparent);
        }
        
        .dark #yearSummaryContainer::after {
            background: linear-gradient(to left, rgba(255,255,255,0.1), transparent);
        }
        
        /* Monthly tables scrolling */
        .month-table {
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .month-table.active {
            display: block;
            max-height: 80vh;
            overflow: auto;
        }
        
        /* Enhanced table scrolling */
        .overflow-x-auto {
            overflow-x: auto;
            overflow-y: visible;
            max-width: 100%;
        }
        
        /* Table wrapper to contain scrolling */
        .month-table {
            width: 100%;
            overflow: hidden;
        }
        
        /* Compact table cell styling */
        .compact-cell {
            white-space: nowrap;
            min-width: 120px;
            padding: 4px 8px !important;
            font-size: 0.875rem;
            line-height: 1.2;
        }
        
        /* Sticky column styles for comparison table */
        .sticky-left {
            position: sticky;
            left: 0;
            z-index: 20;
            background-color: white;
        }
        
        .dark .sticky-left {
            background-color: #1f2937;
        }
        
        /* Sticky header styles */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: white;
        }
        
        .dark .sticky-header {
            background-color: #1f2937;
        }
        
        /* When both sticky-left and sticky-header */
        .sticky-left.sticky-header {
            z-index: 30;
        }
        
        /* Add shadow to sticky columns for better visual separation */
        .sticky-left::after {
            content: '';
            position: absolute;
            top: 0;
            right: -1px;
            bottom: 0;
            width: 1px;
            background: linear-gradient(to right, rgba(0,0,0,0.1), transparent);
        }
        
        .dark .sticky-left::after {
            background: linear-gradient(to right, rgba(255,255,255,0.1), transparent);
        }
        
        /* Month data formatting */
        .month-data {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 100px;
            white-space: nowrap;
        }
        
        .month-data-item {
            font-size: 0.875rem;
            padding: 1px 4px;
            border-radius: 2px;
            text-align: right;
        }
        
        .qty-item {
            background-color: rgba(59, 130, 246, 0.1);
            color: #000000;
            font-weight: bold;
        }
        
        .total-item {
            background-color: rgba(37, 99, 235, 0.1);
            color: #000000;
            font-weight: bold;
        }
        
        .marge-item {
            background-color: rgba(245, 158, 11, 0.1);
            color: #000000;
            font-weight: bold;
        }
        
        .dark .qty-item {
            background-color: rgba(59, 130, 246, 0.2);
            color: white;
            font-weight: bold;
        }
        
        .dark .total-item {
            background-color: rgba(96, 165, 250, 0.2);
            color: white;
            font-weight: bold;
        }
        
        .dark .marge-item {
            background-color: rgba(245, 158, 11, 0.2);
            color: white;
            font-weight: bold;
        }
        
        .product-name {
            color: #000000;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .dark .product-name {
            color: white;
        }
        
        /* Sticky headers for better navigation */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: white;
        }
        
        .dark .sticky-header {
            background-color: #374151;
        }
        
        /* Sticky first column */
        .sticky-left {
            position: sticky;
            left: 0;
            z-index: 10;
        }
        
        /* Scrollbar styling */
        .table-container::-webkit-scrollbar,
        #yearSummaryContainer::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .table-container::-webkit-scrollbar-track,
        #yearSummaryContainer::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        
        .dark .table-container::-webkit-scrollbar-track,
        .dark #yearSummaryContainer::-webkit-scrollbar-track {
            background: #374151;
        }
        
        .table-container::-webkit-scrollbar-thumb,
        #yearSummaryContainer::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        .dark .table-container::-webkit-scrollbar-thumb,
        .dark #yearSummaryContainer::-webkit-scrollbar-thumb {
            background: #6b7280;
        }
        
        .table-container::-webkit-scrollbar-thumb:hover,
        #yearSummaryContainer::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .dark .table-container::-webkit-scrollbar-thumb:hover,
        .dark #yearSummaryContainer::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        
        /* Table wrapper for better mobile display */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .dark .table-wrapper {
            border-color: #4b5563;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        /* Ensure tables take full width within scrollable container */
        .table-wrapper table {
            min-width: 100%;
            white-space: nowrap;
        }
        
        /* Table horizontal scroll improvements */
        .table-container {
            width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            max-height: 70vh;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
        }
        
        /* Minimum table width to ensure readability */
        .table-container table {
            min-width: 1600px; /* Ensure table is wide enough for product + supplier + 12 months */
        }
        
        /* Better mobile responsiveness for year tabs */
        .year-selector {
            display: flex;
            margin-bottom: 16px;
            overflow-x: auto;
            padding-bottom: 8px;
            scroll-behavior: smooth;
        }
        
        /* Improved scrollbar for year selector */
        .year-selector::-webkit-scrollbar {
            height: 6px;
        }
        
        .year-selector::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        .year-selector::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .year-selector::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Pagination styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-top: 1px solid #e5e7eb;
            margin-top: 16px;
        }
        
        .dark .pagination-container {
            border-color: #4b5563;
        }
        
        .pagination-info {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .dark .pagination-info {
            color: #9ca3af;
        }
        
        .pagination-controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .pagination-btn {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            background-color: white;
            color: #374151;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .pagination-btn:hover:not(:disabled) {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-btn.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .dark .pagination-btn {
            background-color: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }
        
        .dark .pagination-btn:hover:not(:disabled) {
            background-color: #4b5563;
            border-color: #6b7280;
        }
        
        .dark .pagination-btn.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .page-size-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
        }
        
        .page-size-selector select {
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: white;
            color: #374151;
        }
        
        .dark .page-size-selector select {
            background-color: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }
        
        .client-section {
            margin-bottom: 2rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: white;
        }
        
        .dark .client-section {
            border-color: #4b5563;
            background-color: #1f2937;
        }
        
        .client-section.hidden {
            display: none;
        }

        /* Individual client table pagination */
        .client-table-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-top: 2px solid #e5e7eb;
            background-color: #f8fafc;
            font-size: 0.8rem;
            border-radius: 0 0 8px 8px;
        }
        
        .dark .client-table-pagination {
            border-color: #4b5563;
            background-color: #334155;
        }
        
        .client-pagination-info {
            color: #4b5563;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .dark .client-pagination-info {
            color: #d1d5db;
        }
        
        .client-pagination-controls {
            display: flex;
            gap: 4px;
            align-items: center;
        }
        
        .client-pagination-btn {
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            background-color: white;
            color: #374151;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.2s ease;
            min-width: 24px;
            text-align: center;
        }
        
        .client-pagination-btn:hover:not(:disabled) {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .client-pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .client-pagination-btn.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .dark .client-pagination-btn {
            background-color: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }
        
        .dark .client-pagination-btn:hover:not(:disabled) {
            background-color: #4b5563;
            border-color: #6b7280;
        }
        
        .dark .client-pagination-btn.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .client-page-size-selector {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        .client-page-size-selector select {
            padding: 2px 6px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: white;
            color: #374151;
            font-size: 0.7rem;
        }
        
        .dark .client-page-size-selector select {
            background-color: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }
        
        .product-row.hidden {
            display: none;
        }
        
        /* Main layout - prevent page scroll */
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw;
        }
        
        /* Content wrapper */
        #content {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Dashboard container */
        .dashboard-container {
            max-width: 100%;
            overflow-x: hidden;
            overflow-y: visible;
            position: relative;
        }
        
        .dashboard-container.ycheffck {
            overflow: visible;
        }
        
        /* All direct children containers */
        .bg-white, .rounded-lg, .shadow-md {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        /* Comparison tables specific scrolling */
        #compareTables .table-container {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            box-sizing: border-box;
        }
        
        /* Ensure year table containers don't overflow */
        .month-table {
            max-width: 100%;
            overflow: hidden;
        }
        
        /* Table should not exceed container */
        table {
            max-width: 100%;
            table-layout: auto;
        }
        
        /* Custom scrollbar for table containers */
        .table-container::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }
        
        .table-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 6px;
        }
        
        .table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 6px;
            border: 2px solid #f1f5f9;
        }
        
        .table-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .dark .table-container::-webkit-scrollbar-track {
            background: #1e293b;
        }
        
        .dark .table-container::-webkit-scrollbar-thumb {
            background: #475569;
            border-color: #1e293b;
        }
        
        .dark .table-container::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        
        /* Corner scrollbar styling */
        .table-container::-webkit-scrollbar-corner {
            background: #f1f5f9;
        }
        
        .dark .table-container::-webkit-scrollbar-corner {
            background: #1e293b;
        }
        
        /* Smooth scrolling behavior */
        .table-container {
            scroll-behavior: smooth;
        }
        
        /* Add scroll hint shadow */
        .table-container {
            background: 
                linear-gradient(to right, white 30%, rgba(255,255,255,0)),
                linear-gradient(to right, rgba(255,255,255,0), white 70%) 100% 0,
                radial-gradient(farthest-side at 0 50%, rgba(0,0,0,.2), rgba(0,0,0,0)),
                radial-gradient(farthest-side at 100% 50%, rgba(0,0,0,.2), rgba(0,0,0,0)) 100% 0;
            background-repeat: no-repeat;
            background-size: 40px 100%, 40px 100%, 14px 100%, 14px 100%;
            background-attachment: local, local, scroll, scroll;
        }
        
        .dark .table-container {
            background: 
                linear-gradient(to right, #1f2937 30%, rgba(31,41,55,0)),
                linear-gradient(to right, rgba(31,41,55,0), #1f2937 70%) 100% 0,
                radial-gradient(farthest-side at 0 50%, rgba(255,255,255,.2), rgba(255,255,255,0)),
                radial-gradient(farthest-side at 100% 50%, rgba(255,255,255,.2), rgba(255,255,255,0)) 100% 0;
            background-repeat: no-repeat;
            background-size: 40px 100%, 40px 100%, 14px 100%, 14px 100%;
            background-attachment: local, local, scroll, scroll;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
            <h1 class="text-5xl font-bold dark:text-white text-center">Rotation Mensuelle des Achats et Ventes</h1>
        </div>

        <!-- Filters -->
        <div class="dashboard-container ycheffck">
            <div class="search-controls bg-white dark:bg-gray-900 p-4 rounded-lg shadow-md mb-6">
                <!-- Year Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2 years">Select Years</label>
                    <div class="flex flex-wrap gap-4">
                        <?php
                        $currentYear = date('Y');
                        for ($i = $currentYear; $i >= $currentYear - 3; $i--) {
                            echo '<label class="inline-flex items-center">
                                <input type="checkbox" class="year-checkbox" value="'.$i.'">
                                <span class="ml-2 year-label years">'.$i.'</span>
                            </label>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Search Controls -->
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4 recap-grid">
                <!-- Product Search -->
                <div>
                    <label for="recap_product" class="block text-sm font-medium recap-label">Product</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_product" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="product_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
                    </div>
                </div>
                
                <!-- Product-Specific Suppliers Dropdown -->
                <div id="productSupplierContainer" class="hidden">
                    <label for="recap_product_supplier" class="block text-sm font-medium recap-label">Suppliers for Product</label>
                    <select id="recap_product_supplier" class="w-full p-2 border rounded recap-input" style="color:black" multiple size="4">
                        <option value="">Loading suppliers...</option>
                    </select>
                    <div class="mt-1 text-xs text-black-500">Click to select/deselect multiple suppliers. Use "Select All" to select all at once.</div>
                </div>

                <!-- Zone Search -->
                <div>
                    <label for="recap_zone" class="block text-sm font-medium recap-label">Zone</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_zone" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="zone_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
                    </div>
                </div>
                
                <!-- Zone-Specific Clients Dropdown -->
                <div id="zoneClientContainer" class="hidden">
                    <label for="recap_zone_client" class="block text-sm font-medium recap-label">Clients for Zone</label>
                    <select id="recap_zone_client" class="w-full p-2 border rounded recap-input" style="color:black" multiple size="4">
                        <option value="">Loading clients...</option>
                    </select>
                    <div class="mt-1 text-xs text-black-500">Click to select/deselect multiple clients. Use "Select All" to select all at once.</div>
                </div>

                <!-- All Suppliers Search -->
                <div>
                    <label for="recap_fournisseur" class="block text-sm font-medium recap-label">All Suppliers</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_fournisseur" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="fournisseur_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
                    </div>
                </div>

                <!-- All Clients Search -->
                <div>
                    <label for="recap_client" class="block text-sm font-medium recap-label">All Clients</label>
                    <div class="relative">
                        <input type="text" style="color:black" id="recap_client" placeholder="Search..." 
                                class="w-full p-2 border rounded recap-input">
                        <div id="client_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
                    </div>
                </div>
            </div>
            
            <button id="applyFilters" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition">
                Apply Filters
            </button>
            <button id="resetFilters" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded transition hidden">
                Reset
            </button>
        </div>

        <!-- PDF Download Buttons -->
        <div class="pdf-download-container">
            <button class="pdf-download-btn" id="exportAchatPdf">
                <span class="pdf-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.523 10.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.082.038a1 1 0 0 1-.146.05q-.327.11-.658 0a1 1 0 0 1-.31-.123 1 1 0 0 1-.165-.153 1 1 0 0 1-.123-.31q-.11-.327 0-.658a1 1 0 0 1 .05-.146l.038-.082q.056-.137.572-.635.27-.31.606-.645a8 8 0 0 1 .238-.459l-2.36-2.36a8 8 0 0 1-.725.725L.5 9.5l.5.5 1.642-1.642a8 8 0 0 1 .725-.725l2.36 2.36Z"/>
                        <path d="M14.5 3.5a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13Zm-13-1A1.5 1.5 0 0 0 0 4v9a1.5 1.5 0 0 0 1.5 1.5h13a1.5 1.5 0 0 0 1.5-1.5v-9a1.5 1.5 0 0 0-1.5-1.5h-13Z"/>
                    </svg>
                </span>
                <span class="btn-text">Download Achat PDF</span>
                <span class="spinner hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                        <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                    </svg>
                </span>
            </button>
            <button class="pdf-download-btn" id="exportVentePdf">
                <span class="pdf-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.523 10.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.082.038a1 1 0 0 1-.146.05q-.327.11-.658 0a1 1 0 0 1-.31-.123 1 1 0 0 1-.165-.153 1 1 0 0 1-.123-.31q-.11-.327 0-.658a1 1 0 0 1 .05-.146l.038-.082q.056-.137.572-.635.27-.31.606-.645a8 8 0 0 1 .238-.459l-2.36-2.36a8 8 0 0 1-.725.725L.5 9.5l.5.5 1.642-1.642a8 8 0 0 1 .725-.725l2.36 2.36Z"/>
                        <path d="M14.5 3.5a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13Zm-13-1A1.5 1.5 0 0 0 0 4v9a1.5 1.5 0 0 0 1.5 1.5h13a1.5 1.5 0 0 0 1.5-1.5v-9a1.5 1.5 0 0 0-1.5-1.5h-13Z"/>
                    </svg>
                </span>
                <span class="btn-text">Download Vente PDF</span>
                <span class="spinner hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                        <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                    </svg>
                </span>
            </button>
            <div class="error-message hidden" id="pdfError"></div>
        </div>

        <!-- Loading Animation -->
        <div id="loading-animation" class="flex justify-center items-center my-8 hidden">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
        </div>

        <!-- Year Summary Tables -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-center dark:text-white">Year Summary</h2>
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 text-center">
                📊 Scroll to view all year summaries
            </div>
            <div id="yearSummaryContainer" class="mb-8">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Toggle Buttons for Monthly Tables -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-center dark:text-white">Monthly Product Rotation Tables</h2>
            <div class="flex justify-center space-x-4 mb-4">
                <button id="toggleAchat" class="toggle-btn active">
                    <span class="inline-block mr-2">📦</span>
                    Achat Table
                </button>
                <button id="toggleVente" class="toggle-btn">
                    <span class="inline-block mr-2">🛒</span>
                    Vente Table
                </button>
                <button id="toggleCompare" class="toggle-btn">
                    <span class="inline-block mr-2">⚖️</span>
                    Compare Achat/Vente
                </button>
            </div>
            
            <!-- Compare Filters Section (only visible when Compare is active) -->
            <div id="compareFiltersSection" class="hidden mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold mb-4 text-center dark:text-white">🎯 Filter Options</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Product Search for Compare -->
                    <div>
                        <label for="compareProductSearch" class="block text-sm font-medium mb-2 dark:text-white">
                            🔍 Search Products
                        </label>
                        <div class="relative">
                            <input type="text" id="compareProductSearch" placeholder="Type product name..." 
                                    class="w-full p-2 border rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 pr-10"
                                    autocomplete="off">
                            <div id="compareProductSuggestions" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                                <!-- Suggestions will be populated by JavaScript -->
                            </div>
                            <span class="absolute right-3 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Filter by product name from results
                        </div>
                    </div>
                    
                    <!-- Month Filter for Compare -->
                    <div>
                        <label for="compareMonthSelector" class="block text-sm font-medium mb-2 dark:text-white">
                            📅 Select Months to Display
                        </label>
                        <div class="relative">
                            <button type="button" id="compareMonthSelector" 
                                    class="w-full p-2 border rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 text-left flex justify-between items-center">
                                <span id="selectedMonthsText">All Months Selected</span>
                                <svg class="w-5 h-5 transition-transform" id="monthDropdownIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="monthDropdownMenu" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden">
                                <div class="p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                                    <div class="flex justify-between gap-2">
                                        <button type="button" id="selectAllMonthsBtn" class="flex-1 text-xs px-3 py-1.5 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-200 rounded transition">
                                            ✓ Select All
                                        </button>
                                        <button type="button" id="deselectAllMonthsBtn" class="flex-1 text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded transition">
                                            ✗ Clear All
                                        </button>
                                    </div>
                                </div>
                                <div id="monthCheckboxList" class="p-2 grid grid-cols-3 gap-2">
                                    <!-- Month checkboxes will be generated by JavaScript (3 columns grid) -->
                                </div>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span id="monthCountText">12 months selected</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center mt-6">
                    <button id="applyCompareFilters" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-2.5 px-8 rounded-lg transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Apply Filters
                        </span>
                    </button>
                </div>
            </div>
            
            <!-- Achat Filters Section (only visible when Achat is active) -->
            <div id="achatFiltersSection" class="mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold mb-4 text-center dark:text-white">🎯 Filter Options</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Product Search for Achat -->
                    <div>
                        <label for="achatProductSearch" class="block text-sm font-medium mb-2 dark:text-white">
                            🔍 Search Products
                        </label>
                        <div class="relative">
                            <input type="text" id="achatProductSearch" placeholder="Type product name..." 
                                    class="w-full p-2 border rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 pr-10"
                                    autocomplete="off">
                            <div id="achatProductSuggestions" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                            </div>
                            <span class="absolute right-3 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Filter by product name from results
                        </div>
                    </div>
                    
                    <!-- Month Filter for Achat -->
                    <div>
                        <label for="achatMonthSelector" class="block text-sm font-medium mb-2 dark:text-white">
                            📅 Select Months to Display
                        </label>
                        <div class="relative">
                            <button type="button" id="achatMonthSelector" 
                                    class="w-full p-2 border rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 text-left flex justify-between items-center">
                                <span id="achatSelectedMonthsText">All Months Selected</span>
                                <svg class="w-5 h-5 transition-transform" id="achatMonthDropdownIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="achatMonthDropdownMenu" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden">
                                <div class="p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                                    <div class="flex justify-between gap-2">
                                        <button type="button" id="achatSelectAllMonthsBtn" class="flex-1 text-xs px-3 py-1.5 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-200 rounded transition">
                                            ✓ Select All
                                        </button>
                                        <button type="button" id="achatDeselectAllMonthsBtn" class="flex-1 text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded transition">
                                            ✗ Clear All
                                        </button>
                                    </div>
                                </div>
                                <div id="achatMonthCheckboxList" class="p-2 grid grid-cols-3 gap-2">
                                </div>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span id="achatMonthCountText">12 months selected</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center mt-6">
                    <button id="applyAchatFilters" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-2.5 px-8 rounded-lg transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Apply Filters
                        </span>
                    </button>
                </div>
            </div>
            
            <!-- Vente Filters Section (only visible when Vente is active) -->
            <div id="venteFiltersSection" class="hidden mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold mb-4 text-center dark:text-white">🎯 Filter Options</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Product Search for Vente -->
                    <div>
                        <label for="venteProductSearch" class="block text-sm font-medium mb-2 dark:text-white">
                            🔍 Search Products
                        </label>
                        <div class="relative">
                            <input type="text" id="venteProductSearch" placeholder="Type product name..." 
                                    class="w-full p-2 border rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 pr-10"
                                    autocomplete="off">
                            <div id="venteProductSuggestions" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                            </div>
                            <span class="absolute right-3 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Filter by product name from results
                        </div>
                    </div>
                    
                    <!-- Month Filter for Vente -->
                    <div>
                        <label for="venteMonthSelector" class="block text-sm font-medium mb-2 dark:text-white">
                            📅 Select Months to Display
                        </label>
                        <div class="relative">
                            <button type="button" id="venteMonthSelector" 
                                    class="w-full p-2 border rounded dark:bg-gray-700 dark:text-white dark:border-gray-600 text-left flex justify-between items-center">
                                <span id="venteSelectedMonthsText">All Months Selected</span>
                                <svg class="w-5 h-5 transition-transform" id="venteMonthDropdownIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                                <div id="venteMonthDropdownMenu" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden">
                                <div class="p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 sticky top-0 z-10">
                                    <div class="flex justify-between gap-2">
                                        <button type="button" id="venteSelectAllMonthsBtn" class="flex-1 text-xs px-3 py-1.5 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-200 rounded transition">
                                            ✓ Select All
                                        </button>
                                        <button type="button" id="venteDeselectAllMonthsBtn" class="flex-1 text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded transition">
                                            ✗ Clear All
                                        </button>
                                    </div>
                                </div>
                                <div id="venteMonthCheckboxList" class="p-2 grid grid-cols-3 gap-2">
                                </div>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span id="venteMonthCountText">12 months selected</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center mt-6">
                    <button id="applyVenteFilters" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-2.5 px-8 rounded-lg transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Apply Filters
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Data Container with Year Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
            <h2 class="text-xl font-semibold mb-4 text-center dark:text-white">Monthly Product Rotation</h2>
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-4 text-center">
                � Products are listed with their suppliers in separate columns for better readability<br>
                �📄 Tables are paginated to show 20 products at a time by default. Use pagination controls below to navigate through all products.
            </div>
            <div id="dataContainer" class="space-y-8">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
        <br> <br>
    </div>


    <script>



        // Multi-select dropdown setup function
        function setupMultiSelectDropdown(selectElement) {
            if (!selectElement) return;
            
            selectElement.addEventListener('change', function(e) {
                const options = Array.from(selectElement.options);
                const selectedValues = options.filter(option => option.selected).map(option => option.value);
                
                // Handle "Select All" functionality
                const selectAllOption = options.find(option => option.value === 'SELECT_ALL');
                if (selectAllOption) {
                    if (e.target.value === 'SELECT_ALL' && e.target.selected) {
                        // Select all options except "Select All" itself
                        options.forEach(option => {
                            if (option.value !== 'SELECT_ALL' && option.value !== '') {
                                option.selected = true;
                            }
                        });
                        selectAllOption.selected = false; // Don't keep "Select All" selected
                    } else if (!e.target.selected && e.target.value !== 'SELECT_ALL') {
                        // If any option is deselected, deselect "Select All"
                        selectAllOption.selected = false;
                    }
                }
            });
        }

        // DOM Elements
        const elements = {
            applyBtn: document.getElementById('applyFilters'),
            resetBtn: document.getElementById('resetFilters'),
            inputs: {
                fournisseur: document.getElementById('recap_fournisseur'),
                product: document.getElementById('recap_product'),
                zone: document.getElementById('recap_zone'),
                client: document.getElementById('recap_client')
            },
            suggestionBoxes: {
                fournisseur: document.getElementById('fournisseur_suggestions'),
                product: document.getElementById('product_suggestions'),
                zone: document.getElementById('zone_suggestions'),
                client: document.getElementById('client_suggestions')
            },
            yearCheckboxes: document.querySelectorAll('.year-checkbox'),
            yearSummaryContainer: document.getElementById('yearSummaryContainer'),
            productSupplierContainer: document.getElementById('productSupplierContainer'),
            productSupplierSelect: document.getElementById('recap_product_supplier'),
            zoneClientContainer: document.getElementById('zoneClientContainer'),
            zoneClientSelect: document.getElementById('recap_zone_client'),
            toggleAchat: document.getElementById('toggleAchat'),
            toggleVente: document.getElementById('toggleVente'),
            toggleCompare: document.getElementById('toggleCompare')
        };

        // Constants
        const API_ENDPOINTS = {
            download_achat_pdf: API_CONFIG.getApiUrl('/rotation_monthly_achat_pdf'),
            download_vente_pdf: API_CONFIG.getApiUrl('/rotation_monthly_vente_pdf'),
            fetchProductDataVente: API_CONFIG.getApiUrl('/rot_mont_vente'),
            fetchProductDataAchat: API_CONFIG.getApiUrl('/rotation_monthly_achat'),
            listFournisseur: API_CONFIG.getApiUrl('/listfournisseur'),
            listProduct: API_CONFIG.getApiUrl('/fetch-rotation-product-data'),
            fetchSuppliersByProduct: API_CONFIG.getApiUrl('/fetchSuppliersByProduct'),
            listRegion: API_CONFIG.getApiUrl('/listregion'),
            listClient: API_CONFIG.getApiUrl('/listclient'),
            fetchZoneClients: API_CONFIG.getApiUrl('/fetchZoneClients')
        };

        // Store product mapping (name -> id)
        let productMap = {};

        // Current view mode for monthly tables (achat or vente)
        let currentViewMode = 'achat';
        
        // Track if achat was hidden due to zone/client selection
        let achatHiddenByZoneClient = false;
        
        // Global variables to store comparison data for filtering
        let comparisonData = null;
        let comparisonYears = null;

        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        function formatNumber(num, locale = 'fr-FR') {
            return new Intl.NumberFormat(locale, {
                maximumFractionDigits: 2,
                minimumFractionDigits: 0
            }).format(num);
        }

        // Pagination state
        let currentPage = 1;
        let itemsPerPage = 20; // More items per page for product list
        let totalItems = 0;
        let allProductRows = [];

        // Initialize pagination for product rows
        function initializePagination() {
            const activeTable = document.querySelector('.month-table.active');
            if (!activeTable) return;
            
            const productRows = activeTable.querySelectorAll('.product-row:not(.totals-row)');
            allProductRows = Array.from(productRows);
            totalItems = allProductRows.length;
            updatePagination();
        }

        // Update pagination display
        function updatePagination() {
            if (totalItems === 0) return;
            
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            // Show/hide product rows based on current page
            allProductRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });

            // Update pagination controls
            updatePaginationControls(totalPages);
        }

        // Update pagination controls
        function updatePaginationControls(totalPages) {
            let existingContainer = document.querySelector('.pagination-container');
            if (existingContainer) {
                existingContainer.remove();
            }

            if (totalPages <= 1) return;

            const container = document.createElement('div');
            container.className = 'pagination-container';

            // Pagination info
            const startItem = (currentPage - 1) * itemsPerPage + 1;
            const endItem = Math.min(currentPage * itemsPerPage, totalItems);
            const info = document.createElement('div');
            info.className = 'pagination-info';
            info.textContent = `Showing ${startItem}-${endItem} of ${totalItems} products`;

            // Pagination controls
            const controls = document.createElement('div');
            controls.className = 'pagination-controls';

            // Page size selector
            const pageSizeContainer = document.createElement('div');
            pageSizeContainer.className = 'page-size-selector';
            pageSizeContainer.innerHTML = `
                <span>Show:</span>
                <select id="pageSize">
                    <option value="10" ${itemsPerPage === 10 ? 'selected' : ''}>10</option>
                    <option value="20" ${itemsPerPage === 20 ? 'selected' : ''}>20</option>
                    <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50</option>
                    <option value="100" ${itemsPerPage === 100 ? 'selected' : ''}>100</option>
                </select>
            `;

            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = 'pagination-btn';
            prevBtn.textContent = '← Previous';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination();
                }
            };

            // Page number buttons
            const maxButtons = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
            let endPage = Math.min(totalPages, startPage + maxButtons - 1);
            
            if (endPage - startPage + 1 < maxButtons) {
                startPage = Math.max(1, endPage - maxButtons + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => {
                    currentPage = i;
                    updatePagination();
                };
                controls.appendChild(pageBtn);
            }

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = 'pagination-btn';
            nextBtn.textContent = 'Next →';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    updatePagination();
                }
            };

            controls.appendChild(prevBtn);
            controls.appendChild(nextBtn);

            container.appendChild(info);
            container.appendChild(pageSizeContainer);
            container.appendChild(controls);

            // Insert pagination after the active year table container
            const dataContainer = document.getElementById('dataContainer');
            dataContainer.appendChild(container);

            // Add page size change handler
            document.getElementById('pageSize').addEventListener('change', (e) => {
                itemsPerPage = parseInt(e.target.value);
                currentPage = 1;
                updatePagination();
            });
        }

        // Reset pagination on new data
        function resetPagination() {
            currentPage = 1;
            initializePagination();
        }

        function showLoading(show) {
            document.getElementById('loading-animation').classList.toggle('hidden', !show);
            document.getElementById('dataContainer').classList.toggle('opacity-50', show);
        }

        function getSelectedYears() {
            const selectedYears = [];
            elements.yearCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedYears.push(checkbox.value);
                }
            });
            return selectedYears;
        }

        function createYearTabs(years) {
            const container = document.createElement('div');
            container.className = 'year-selector sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4';
            
            years.forEach(year => {
                const tab = document.createElement('div');
                tab.className = 'year-tab';
                tab.textContent = year;
                tab.dataset.year = year;
                tab.addEventListener('click', () => switchYear(year));
                container.appendChild(tab);
            });
            
            // Activate first tab by default
            if (years.length > 0) {
                container.querySelector('.year-tab').classList.add('active');
            }
            
            return container;
        }

        function switchYear(year) {
            // Save current scroll position
            const currentActiveTable = document.querySelector('.month-table.active');
            let scrollPosition = { top: 0, left: 0 };
            if (currentActiveTable) {
                scrollPosition = {
                    top: currentActiveTable.scrollTop,
                    left: currentActiveTable.scrollLeft
                };
            }
            
            // Update active tab
            document.querySelectorAll('.year-tab').forEach(tab => {
                tab.classList.toggle('active', tab.dataset.year === year);
            });
            
            // Show tables for selected year
            document.querySelectorAll('.month-table').forEach(table => {
                table.classList.toggle('active', table.dataset.year === year);
            });
            
            // Update pagination for the new active year
            setTimeout(() => {
                resetPagination();
                
                // Restore scroll position for new active table
                const newActiveTable = document.querySelector('.month-table.active');
                if (newActiveTable) {
                    newActiveTable.scrollTop = scrollPosition.top;
                    newActiveTable.scrollLeft = scrollPosition.left;
                }
            }, 100);
        }

        function updateYearSummaryTables(data, years) {
            const container = elements.yearSummaryContainer;
            container.innerHTML = '';

            // Inject small CSS to hide achat columns when parent has .achat-hidden
            if (!document.getElementById('achat-toggle-style')) {
                const style = document.createElement('style');
                style.id = 'achat-toggle-style';
                style.textContent = `.achat-hidden .achat-col{display:none !important} .achat-hidden .achat-table{display:none !important}`;
                document.head.appendChild(style);
            }

            // Determine current filter state
            const hasZone = elements.inputs.zone && elements.inputs.zone.value.trim() !== '';
            const hasClient = elements.inputs.client && elements.inputs.client.value.trim() !== '';
            const hasProduct = elements.inputs.product && elements.inputs.product.value.trim() !== '';
            const hasFourn = elements.inputs.fournisseur && elements.inputs.fournisseur.value.trim() !== '';

            // If no filters are active, reset the achat hidden flag
            if (!hasZone && !hasClient && !hasProduct && !hasFourn) {
                achatHiddenByZoneClient = false;
            }

            // Default: show achat if product or supplier selected; otherwise hide when zone or client selected
            // But if achat was previously hidden due to zone/client selection, keep it hidden until product/supplier is selected
            const showAchatDefault = (hasProduct || hasFourn) || (!(hasZone || hasClient) && !achatHiddenByZoneClient);

            years.forEach(year => {
                const yearData = data.years[year] || {};
                const venteData = yearData.vente || {};
                const achatData = yearData.achat || {};

                // Skip if no data for this year
                if (Object.keys(venteData).length === 0 && Object.keys(achatData).length === 0) return;

                // Create year section
                const yearSection = document.createElement('div');
                yearSection.className = 'table-container rounded-lg bg-white dark:bg-gray-800';
                if (!showAchatDefault) {
                    yearSection.classList.add('achat-hidden');
                }

                // Create year header (with small toggle button)
                const yearHeader = document.createElement('div');
                yearHeader.className = 'flex items-center justify-center p-2';
                const h2 = document.createElement('h2');
                h2.className = 'text-lg font-semibold dark:text-white';
                h2.textContent = `Year ${year}`;

                const toggleBtn = document.createElement('button');
                toggleBtn.className = 'ml-3 text-xs px-2 py-1 rounded border bg-gray-200 dark:bg-gray-700 dark:text-white';
                toggleBtn.textContent = yearSection.classList.contains('achat-hidden') ? 'Show Achat' : 'Hide Achat';
                toggleBtn.addEventListener('click', () => {
                    const isHidden = yearSection.classList.toggle('achat-hidden');
                    // Also toggle any monthly achat tables for this year
                    document.querySelectorAll(`.month-table[data-year="${year}"]`).forEach(mt => {
                        // Find achat-table inside month-table and toggle class on its container
                        if (isHidden) {
                            mt.classList.add('achat-hidden');
                        } else {
                            mt.classList.remove('achat-hidden');
                        }
                    });
                    toggleBtn.textContent = isHidden ? 'Show Achat' : 'Hide Achat';
                });

                yearHeader.appendChild(h2);
                yearHeader.appendChild(toggleBtn);

                // Create table with combined achat/vente columns (achat columns have class 'achat-col')
                let tableHTML = `
                    <div class="h-full">
                        <table class="w-full border-collapse text-sm text-left dark:text-white">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr class="table-header">
                                    <th class="border px-2 py-1 bg-white dark:bg-gray-700">Month</th>
                                    <th class="border px-2 py-1 text-right achat-col">Qty achat</th>
                                    <th class="border px-2 py-1 text-right achat-col">Total achat</th>
                                    <th class="border px-2 py-1 text-right">Qty vente</th>
                                    <th class="border px-2 py-1 text-right">Total vente</th>
                                    <th class="border px-2 py-1 text-right">Marge % vente</th>
                                </tr>
                            </thead>
                            <tbody class="dark:bg-gray-800">`;
                
                let yearlyTotals = { 
                    ACHAT_QTY: 0, ACHAT_TOTAL: 0, 
                    VENTE_QTY: 0, VENTE_TOTAL: 0, VENTE_MARGE: 0 
                };
                
                // Track valid months for vente marge average calculation
                let validVenteMonthsCount = 0;
                let yearlyVenteMargeSum = 0;
                
                // Add rows for each month
                for (let month = 1; month <= 12; month++) {
                    const monthNum = month.toString().padStart(2, '0');
                    const monthVenteData = venteData[monthNum] || { details: [] };
                    const monthAchatData = achatData[monthNum] || { details: [] };
                    
                    // Calculate month totals for achat
                    let achatTotals = { QTY: 0, TOTAL: 0 };
                    if (monthAchatData.details && Array.isArray(monthAchatData.details)) {
                        monthAchatData.details.forEach(item => {
                            achatTotals.QTY += (item.QTY || 0);
                            // Achat API uses 'CHIFFRE' instead of 'TOTAL'
                            achatTotals.TOTAL += (item.CHIFFRE || item.TOTAL || 0);
                        });
                    }
                    
                    // Calculate month totals for vente
                    let venteTotals = { QTY: 0, TOTAL: 0, MARGE: 0 };
                    let venteSupplierCount = 0;
                    
                    if (monthVenteData.details && Array.isArray(monthVenteData.details)) {
                        let monthTotalConsomation = 0;
                        
                        monthVenteData.details.forEach(item => {
                            venteTotals.QTY += (item.QTY || 0);
                            venteTotals.TOTAL += (item.TOTAL || 0);
                            
                            // Calculate CONSOMATION = TOTAL / (1 + MARGE)
                            const itemTotal = item.TOTAL || 0;
                            const itemMarge = item.MARGE || 0;
                            const itemConsomation = itemMarge > 0 ? itemTotal / (1 + itemMarge) : itemTotal;
                            monthTotalConsomation += itemConsomation;
                        });
                        
                        // Calculate margin from aggregated totals
                        if (monthTotalConsomation > 0) {
                            venteTotals.MARGE = (venteTotals.TOTAL - monthTotalConsomation) / monthTotalConsomation;
                            venteSupplierCount = 1;
                        }
                    }
                    
                    // Add to yearly totals
                    yearlyTotals.ACHAT_QTY += achatTotals.QTY;
                    yearlyTotals.ACHAT_TOTAL += achatTotals.TOTAL;
                    yearlyTotals.VENTE_QTY += venteTotals.QTY;
                    yearlyTotals.VENTE_TOTAL += venteTotals.TOTAL;
                    
                    // Only count months with vente suppliers for marge average
                    if (venteSupplierCount > 0) {
                        yearlyVenteMargeSum += venteTotals.MARGE;
                        validVenteMonthsCount++;
                    }
                    
                    tableHTML += `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="border px-2 py-1 bg-white dark:bg-gray-800">${monthNames[month - 1]}</td>
                            <td class="border px-2 py-1 text-right achat-col">${formatNumber(achatTotals.QTY)}</td>
                            <td class="border px-2 py-1 text-right achat-col">${formatNumber(achatTotals.TOTAL)}</td>
                            <td class="border px-2 py-1 text-right">${formatNumber(venteTotals.QTY)}</td>
                            <td class="border px-2 py-1 text-right">${formatNumber(venteTotals.TOTAL)}</td>
                            <td class="border px-2 py-1 text-right">${formatNumber(venteTotals.MARGE * 100)}%</td>
                        </tr>`;
                }
                
                // Calculate yearly average vente marge
                yearlyTotals.VENTE_MARGE = validVenteMonthsCount > 0 ? yearlyVenteMargeSum / validVenteMonthsCount : 0;
                
                // Add yearly total row
                tableHTML += `
                            <tr class="bg-blue-50 dark:bg-blue-900 font-semibold">
                                <td class="border px-2 py-1 bg-blue-50 dark:bg-blue-900">TOTAL</td>
                                <td class="border px-2 py-1 text-right achat-col">${formatNumber(yearlyTotals.ACHAT_QTY)}</td>
                                <td class="border px-2 py-1 text-right achat-col">${formatNumber(yearlyTotals.ACHAT_TOTAL)}</td>
                                <td class="border px-2 py-1 text-right">${formatNumber(yearlyTotals.VENTE_QTY)}</td>
                                <td class="border px-2 py-1 text-right">${formatNumber(yearlyTotals.VENTE_TOTAL)}</td>
                                <td class="border px-2 py-1 text-right">${formatNumber(yearlyTotals.VENTE_MARGE * 100)}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>`;
                
                yearSection.appendChild(yearHeader);
                yearSection.insertAdjacentHTML('beforeend', tableHTML);
                container.appendChild(yearSection);
            });
        }

function buildApiUrl(endpoint, params) {
    let url = API_ENDPOINTS[endpoint];
    
    // Add query parameters
    const urlObj = new URL(url);
    Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
            urlObj.searchParams.append(key, params[key]);
        }
    });
    
    const finalUrl = urlObj.toString();
    return finalUrl;
}

function combineVenteAchatData(venteData, achatData) {
    const combined = {};
    
    // Process vente data - API returns data directly as {year: data}
    if (venteData) {
        Object.keys(venteData).forEach(year => {
            if (!combined[year]) combined[year] = {};
            combined[year].vente = venteData[year];
        });
    }
    
    // Process achat data - API returns data directly as {year: data}
    if (achatData) {
        Object.keys(achatData).forEach(year => {
            if (!combined[year]) combined[year] = {};
            combined[year].achat = achatData[year];
        });
    }
    
    return { years: combined };
}

function createMonthlyTables(data, years, type, container) {
    years.forEach(year => {
        const yearData = data.years[year] || {};
        const typeData = yearData[type] || {};
        
        // Create year table container
        const yearTableContainer = document.createElement('div');
        yearTableContainer.className = `month-table ${year === years[0] ? 'active' : ''}`;
        yearTableContainer.dataset.year = year;
        
        // Group all data by product-supplier combination
        const productGroups = {};
        for (let month = 1; month <= 12; month++) {
            const monthNum = month.toString().padStart(2, '0');
            const monthData = typeData[monthNum] || { details: [] };
            
            monthData.details.forEach(item => {
                const productKey = `${item.PRODUIT}|${item.FOURNISSEUR}`;
                if (!productGroups[productKey]) {
                    productGroups[productKey] = {
                        name: item.PRODUIT,
                        supplier: item.FOURNISSEUR,
                        quantities: Array(12).fill(0),
                        totals: Array(12).fill(0),
                        marges: Array(12).fill(0),
                        // Keep track of individual items for margin calculation
                        monthItems: Array(12).fill(null).map(() => [])
                    };
                }
                
                const productData = productGroups[productKey];
                productData.quantities[month - 1] += (item.QTY || 0);
                // Handle different field names for achat vs vente
                const totalValue = type === 'achat' ? (item.CHIFFRE || item.TOTAL || 0) : (item.TOTAL || 0);
                productData.totals[month - 1] += totalValue;
                // Keep track of individual items for proper margin calculation
                productData.monthItems[month - 1].push({
                    TOTAL: totalValue,
                    MARGE: type === 'achat' ? 0 : (item.MARGE || 0)  // Achat doesn't have margin data
                });
            });
        }
        
        // Calculate correct margins for each product-supplier combination
        Object.values(productGroups).forEach(productData => {
            for (let month = 0; month < 12; month++) {
                const monthItems = productData.monthItems[month];
                if (monthItems.length > 0) {
                    // Calculate margin from aggregated totals for this product-supplier-month
                    let totalConsomation = 0;
                    let totalTotal = 0;
                    
                    monthItems.forEach(item => {
                        totalTotal += item.TOTAL;
                        // CONSOMATION = TOTAL / (1 + MARGE)
                        const consomation = item.MARGE > 0 ? item.TOTAL / (1 + item.MARGE) : item.TOTAL;
                        totalConsomation += consomation;
                    });
                    
                    // Calculate margin: (TOTAL - CONSOMATION) / CONSOMATION
                    productData.marges[month] = totalConsomation > 0 
                        ? (totalTotal - totalConsomation) / totalConsomation 
                        : 0;
                }
            }
            // Clean up the temporary monthItems array
            delete productData.monthItems;
        });
        
        // Convert to array and create table
        const products = Object.values(productGroups);
        if (products.length > 0) {
            const tableContainer = createProductSupplierTable(products, year, type);
            yearTableContainer.appendChild(tableContainer);
            container.appendChild(yearTableContainer);
        }
    });
}

function createComparisonTables(data, years, container) {
    // Store data globally for filtering
    comparisonData = data;
    comparisonYears = years;
    
    // Trigger event to notify that comparison data is loaded
    window.dispatchEvent(new Event('comparisonDataLoaded'));
    
    years.forEach(year => {
        const yearData = data.years[year] || {};
        const achatData = yearData.achat || {};
        const venteData = yearData.vente || {};
        
        // Create year table container
        const yearTableContainer = document.createElement('div');
        yearTableContainer.className = `month-table ${year === years[0] ? 'active' : ''}`;
        yearTableContainer.dataset.year = year;
        
        // Group all data by product-supplier combination from both achat and vente
        const productGroups = {};
        
        // Process achat data
        for (let month = 1; month <= 12; month++) {
            const monthNum = month.toString().padStart(2, '0');
            const monthData = achatData[monthNum] || { details: [] };
            
            monthData.details.forEach(item => {
                const productKey = `${item.PRODUIT}|${item.FOURNISSEUR}`;
                if (!productGroups[productKey]) {
                    productGroups[productKey] = {
                        name: item.PRODUIT,
                        supplier: item.FOURNISSEUR,
                        achatQuantities: Array(12).fill(0),
                        achatTotals: Array(12).fill(0),
                        venteQuantities: Array(12).fill(0),
                        venteTotals: Array(12).fill(0),
                        venteMarges: Array(12).fill(0),
                        // Keep track of individual items for margin calculation
                        venteMonthItems: Array(12).fill(null).map(() => [])
                    };
                }
                
                const productData = productGroups[productKey];
                productData.achatQuantities[month - 1] += (item.QTY || 0);
                productData.achatTotals[month - 1] += (item.CHIFFRE || item.TOTAL || 0);
            });
        }
        
        // Process vente data
        for (let month = 1; month <= 12; month++) {
            const monthNum = month.toString().padStart(2, '0');
            const monthData = venteData[monthNum] || { details: [] };
            
            monthData.details.forEach(item => {
                const productKey = `${item.PRODUIT}|${item.FOURNISSEUR}`;
                if (!productGroups[productKey]) {
                    productGroups[productKey] = {
                        name: item.PRODUIT,
                        supplier: item.FOURNISSEUR,
                        achatQuantities: Array(12).fill(0),
                        achatTotals: Array(12).fill(0),
                        venteQuantities: Array(12).fill(0),
                        venteTotals: Array(12).fill(0),
                        venteMarges: Array(12).fill(0),
                        // Keep track of individual items for margin calculation
                        venteMonthItems: Array(12).fill(null).map(() => [])
                    };
                }
                
                const productData = productGroups[productKey];
                productData.venteQuantities[month - 1] += (item.QTY || 0);
                productData.venteTotals[month - 1] += (item.TOTAL || 0);
                // Keep track of individual items for proper margin calculation
                productData.venteMonthItems[month - 1].push({
                    TOTAL: item.TOTAL || 0,
                    MARGE: item.MARGE || 0
                });
            });
        }
        
        // Calculate correct margins for vente data
        Object.values(productGroups).forEach(productData => {
            for (let month = 0; month < 12; month++) {
                const monthItems = productData.venteMonthItems[month];
                if (monthItems.length > 0) {
                    // Calculate margin from aggregated totals for this product-supplier-month
                    let totalConsomation = 0;
                    let totalTotal = 0;
                    
                    monthItems.forEach(item => {
                        totalTotal += item.TOTAL;
                        // CONSOMATION = TOTAL / (1 + MARGE)
                        const consomation = item.MARGE > 0 ? item.TOTAL / (1 + item.MARGE) : item.TOTAL;
                        totalConsomation += consomation;
                    });
                    
                    // Calculate margin: (TOTAL - CONSOMATION) / CONSOMATION
                    productData.venteMarges[month] = totalConsomation > 0 
                        ? (totalTotal - totalConsomation) / totalConsomation 
                        : 0;
                }
            }
            // Clean up the temporary monthItems array
            delete productData.venteMonthItems;
        });
        
        // Convert to array and create table
        const products = Object.values(productGroups);
        if (products.length > 0) {
            const tableContainer = createComparisonTable(products, year);
            yearTableContainer.appendChild(tableContainer);
            container.appendChild(yearTableContainer);
        }
    });
}

async function loadData() {
    const years = getSelectedYears();
    const fournisseurs = getSelectedSuppliers();
    const clients = getSelectedClients();
    const productName = elements.inputs.product.value;
    const zone = elements.inputs.zone.value;

    if (!years.length) {
        document.getElementById('dataContainer').innerHTML = `
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100" role="alert">
                <span class="block sm:inline">Please select at least one year</span>
            </div>`;
        elements.yearSummaryContainer.innerHTML = '';
        return;
    }

    showLoading(true);

    try {
        // Build base parameters
        const baseParams = {
            years: years.join(','),
            fournisseur: fournisseurs.join(',')
        };

        // Handle product parameter - convert name to ID if available
        if (productName) {
            const productId = productMap[productName];
            if (productId) {
                baseParams.product_id = productId;
            }
        }

        // Parameters for vente API (includes client and zone)
        const venteParams = { ...baseParams };
        if (clients.length > 0) {
            venteParams.client = clients.join(',');
        }
        if (zone) {
            venteParams.zone = zone;
        }

        // Parameters for achat API (only fournisseur, years, and product_id)
        const achatParams = { ...baseParams };

        // Fetch data
        let venteData, achatData;
        
        const venteUrl = buildApiUrl('fetchProductDataVente', venteParams);
        const achatUrl = buildApiUrl('fetchProductDataAchat', achatParams);
        
        // Check if only year is provided (no other filters)
        const onlyYearProvided = fournisseurs.length === 0 && !productName && clients.length === 0 && !zone;
        
        if (onlyYearProvided) {
            // Sequential loading: Achat first, then Vente (to prevent page slowdown)
            console.log('Loading sequentially: Achat first, then Vente...');
            
            // Fetch and display Achat data first
            const achatResponse = await fetch(achatUrl);
            achatData = await achatResponse.json();
            
            if (achatData.error) {
                throw new Error(`Achat API error: ${achatData.error}`);
            }
            
            // Display Achat data immediately
            const partialCombinedData = combineVenteAchatData({}, achatData);
            updateYearSummaryTables(partialCombinedData, years);
            
            // Then fetch Vente data
            const venteResponse = await fetch(venteUrl);
            venteData = await venteResponse.json();
            
            if (venteData.error) {
                throw new Error(`Vente API error: ${venteData.error}`);
            }
        } else {
            // Parallel loading when filters are applied (faster for filtered data)
            console.log('Loading in parallel with filters...');
            
            const [venteResponse, achatResponse] = await Promise.all([
                fetch(venteUrl),
                fetch(achatUrl)
            ]);

            venteData = await venteResponse.json();
            achatData = await achatResponse.json();

            if (venteData.error) {
                throw new Error(`Vente API error: ${venteData.error}`);
            }
            if (achatData.error) {
                throw new Error(`Achat API error: ${achatData.error}`);
            }
        }

        // Combine the data
        const combinedData = combineVenteAchatData(venteData, achatData);

        // If no achat data (no suppliers selected), automatically switch to vente view
        if (!achatData || Object.keys(achatData).length === 0) {
            currentViewMode = 'vente';
            // Update toggle button states
            elements.toggleVente.classList.add('active');
            elements.toggleAchat.classList.remove('active');
        }

        if (Object.keys(combinedData).length === 0) {
            const noDataMessage = `
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative dark:bg-yellow-900 dark:border-yellow-700 dark:text-yellow-100" role="alert">
                    <span class="block sm:inline">No data found for the selected criteria</span>
                </div>`;
            document.getElementById('dataContainer').innerHTML = noDataMessage;
            elements.yearSummaryContainer.innerHTML = noDataMessage;
            return;
        }

        // Update year summary tables with combined data
        updateYearSummaryTables(combinedData, years);

        const container = document.getElementById('dataContainer');
        container.innerHTML = '';
        
        // Add year tabs
        const yearTabs = createYearTabs(years);
        container.appendChild(yearTabs);
        
        // Create containers for achat and vente tables
        const achatContainer = document.createElement('div');
        achatContainer.id = 'achatTables';
        achatContainer.className = currentViewMode === 'achat' ? '' : 'hidden';
        
        const venteContainer = document.createElement('div');
        venteContainer.id = 'venteTables';
        venteContainer.className = currentViewMode === 'vente' ? '' : 'hidden';
        
        const compareContainer = document.createElement('div');
        compareContainer.id = 'compareTables';
        compareContainer.className = currentViewMode === 'compare' ? '' : 'hidden';
        
        container.appendChild(achatContainer);
        container.appendChild(venteContainer);
        container.appendChild(compareContainer);
        
        // Create achat tables
        createMonthlyTables(combinedData, years, 'achat', achatContainer);
        
        // Create vente tables
        createMonthlyTables(combinedData, years, 'vente', venteContainer);
        
        // Create compare tables
        createComparisonTables(combinedData, years, compareContainer);
        
        elements.resetBtn.classList.remove('hidden');
        
        // Enable PDF download buttons now that data is loaded
        document.getElementById('exportAchatPdf').disabled = false;
        document.getElementById('exportVentePdf').disabled = false;
        
        // Initialize pagination after data is loaded
        setTimeout(() => {
            resetPagination();
        }, 100);
        
    } catch (error) {
        document.getElementById('dataContainer').innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-900 dark:border-red-700 dark:text-red-100" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"> ${error.message}</span>
            </div>`;
        // Keep PDF buttons disabled on error
        document.getElementById('exportAchatPdf').disabled = true;
        document.getElementById('exportVentePdf').disabled = true;
    } finally {
        showLoading(false);
    }
}

function createProductSupplierTable(products, year, type) {
    const tableContainer = document.createElement('div');
    tableContainer.className = 'table-container overflow-auto';
    if (type === 'achat') {
        tableContainer.classList.add('achat-table');
    }

    // Create the table
    const table = document.createElement('table');
    table.className = 'min-w-full border-collapse text-sm';

    // Create header
    const thead = document.createElement('thead');
    thead.className = 'sticky-header';
    const headerRow = document.createElement('tr');

    // Product header cell
    const productHeader = document.createElement('th');
    productHeader.className = 'sticky-left sticky-header bg-white dark:bg-gray-800 border px-4 py-2 text-left z-30';
    productHeader.textContent = 'Product';
    headerRow.appendChild(productHeader);

    // Supplier header cell
    const supplierHeader = document.createElement('th');
    supplierHeader.className = 'sticky-left sticky-header bg-white dark:bg-gray-800 border px-4 py-2 text-left z-30';
    supplierHeader.textContent = 'Supplier';
    headerRow.appendChild(supplierHeader);

    // Month headers (one column per month with combined data)
    for (let month = 1; month <= 12; month++) {
        const monthHeader = document.createElement('th');
        monthHeader.className = 'border px-2 py-1 text-center bg-blue-50 dark:bg-blue-900 font-medium sticky-header compact-cell';
        const headerText = type === 'achat' ? 'Qty | Total' : 'Qty | Total | Marge';
        monthHeader.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 4px;">${monthNames[month - 1]}</div>
            <div style="font-size: 0.7rem; color: #6b7280;">${headerText}</div>
        `;
        headerRow.appendChild(monthHeader);
    }

    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Create table body
    const tbody = document.createElement('tbody');
    products.forEach(product => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 product-row';

        // Product name cell
        const nameCell = document.createElement('td');
        nameCell.className = 'sticky-left bg-white dark:bg-gray-800 border px-4 py-2 z-10';
        nameCell.style.minWidth = '200px';
        nameCell.innerHTML = `
            <div class="text-gray-900 dark:text-gray-100 font-medium">
                ${product.name}
            </div>
        `;
        row.appendChild(nameCell);

        // Supplier cell
        const supplierCell = document.createElement('td');
        supplierCell.className = 'sticky-left bg-white dark:bg-gray-800 border px-4 py-2 z-10';
        supplierCell.style.minWidth = '150px';
        supplierCell.innerHTML = `
            <div class="text-blue-600 dark:text-blue-400 font-bold">
                ${product.supplier || 'Unknown'}
            </div>
        `;
        row.appendChild(supplierCell);

        // Data cells for each month (combined format)
        for (let month = 0; month < 12; month++) {
            const dataCell = document.createElement('td');
            dataCell.className = 'border compact-cell text-center bg-gray-50 dark:bg-gray-800/50';
            
            const qty = formatNumber(product.quantities[month] || 0);
            const total = formatNumber(product.totals[month] || 0);
            const marge = formatNumber((product.marges[month] || 0) * 100);
            
            if (type === 'achat') {
                dataCell.innerHTML = `
                    <div class="month-data">
                        <div class="month-data-item qty-item">${qty}</div>
                        <div class="month-data-item total-item">${total}</div>
                    </div>
                `;
            } else {
                dataCell.innerHTML = `
                    <div class="month-data">
                        <div class="month-data-item qty-item">${qty}</div>
                        <div class="month-data-item total-item">${total}</div>
                        <div class="month-data-item marge-item">${marge}%</div>
                    </div>
                `;
            }
            row.appendChild(dataCell);
        }

        tbody.appendChild(row);
    });

    // Create totals row
    const totalsRow = document.createElement('tr');
    totalsRow.className = 'font-bold bg-gray-100 dark:bg-gray-700 totals-row';

    // Totals label
    const totalsLabel = document.createElement('td');
    totalsLabel.className = 'sticky-left bg-gray-100 dark:bg-gray-700 border px-4 py-2 z-10';
    totalsLabel.textContent = 'TOTAL';
    totalsRow.appendChild(totalsLabel);

    // Empty supplier cell for totals row
    const emptySupplierCell = document.createElement('td');
    emptySupplierCell.className = 'sticky-left bg-gray-100 dark:bg-gray-700 border px-4 py-2 z-10';
    emptySupplierCell.textContent = '';
    totalsRow.appendChild(emptySupplierCell);

    // Calculate and add totals for each month
    for (let month = 0; month < 12; month++) {
        const monthQtyTotal = products.reduce((sum, product) => sum + (product.quantities[month] || 0), 0);
        const monthTotalTotal = products.reduce((sum, product) => sum + (product.totals[month] || 0), 0);
        
        // Calculate marge from aggregated totals (same as backend logic)
        // First, calculate the aggregated CONSOMATION (TOTAL - MARGE*TOTAL) for the month
        let monthTotalConsomation = 0;
        products.forEach(product => {
            const productTotal = product.totals[month] || 0;
            const productMarge = product.marges[month] || 0;
            // CONSOMATION = TOTAL / (1 + MARGE)
            const productConsomation = productMarge > 0 ? productTotal / (1 + productMarge) : productTotal;
            monthTotalConsomation += productConsomation;
        });
        
        // Calculate margin from aggregated totals: (TOTAL - CONSOMATION) / CONSOMATION
        const monthMargeAverage = monthTotalConsomation > 0 
            ? (monthTotalTotal - monthTotalConsomation) / monthTotalConsomation
            : 0;

        const totalCell = document.createElement('td');
        totalCell.className = 'border compact-cell text-center bg-gray-200 dark:bg-gray-600';
        
        if (type === 'achat') {
            totalCell.innerHTML = `
                <div class="month-data">
                    <div class="month-data-item qty-item font-bold">${formatNumber(monthQtyTotal)}</div>
                    <div class="month-data-item total-item font-bold">${formatNumber(monthTotalTotal)}</div>
                </div>
            `;
        } else {
            totalCell.innerHTML = `
                <div class="month-data">
                    <div class="month-data-item qty-item font-bold">${formatNumber(monthQtyTotal)}</div>
                    <div class="month-data-item total-item font-bold">${formatNumber(monthTotalTotal)}</div>
                    <div class="month-data-item marge-item font-bold">${formatNumber(monthMargeAverage * 100)}%</div>
                </div>
            `;
        }
        totalsRow.appendChild(totalCell);
    }

    tbody.appendChild(totalsRow);
    table.appendChild(tbody);
    tableContainer.appendChild(table);
    return tableContainer;
}

function createComparisonTable(products, year, selectedMonths = null, productFilter = '') {
    // If no months selected, default to all months
    const monthsToShow = selectedMonths && selectedMonths.length > 0 ? selectedMonths : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    
    // Filter products by name if productFilter is provided
    let filteredProducts = products;
    if (productFilter && productFilter.trim() !== '') {
        const filterLower = productFilter.toLowerCase();
        filteredProducts = products.filter(product => 
            product.name.toLowerCase().includes(filterLower)
        );
    }
    
    const tableContainer = document.createElement('div');
    tableContainer.className = 'table-container overflow-auto';

    // Create the table
    const table = document.createElement('table');
    table.className = 'min-w-full border-collapse text-sm';

    // Create header
    const thead = document.createElement('thead');
    thead.className = 'sticky-header';
    const headerRow = document.createElement('tr');

    // Product header cell
    const productHeader = document.createElement('th');
    productHeader.className = 'sticky-left sticky-header bg-white dark:bg-gray-800 border px-4 py-2 text-left z-30';
    productHeader.textContent = 'Product';
    headerRow.appendChild(productHeader);

    // Supplier header cell
    const supplierHeader = document.createElement('th');
    supplierHeader.className = 'sticky-left sticky-header bg-white dark:bg-gray-800 border px-4 py-2 text-left z-30';
    supplierHeader.textContent = 'Supplier';
    headerRow.appendChild(supplierHeader);

    // Month headers (two columns per month: achat and vente) - only for selected months
    monthsToShow.forEach(month => {
        // Achat column for this month
        const achatHeader = document.createElement('th');
        achatHeader.className = 'border px-2 py-1 text-center bg-green-50 dark:bg-green-900 font-medium sticky-header compact-cell';
        achatHeader.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 4px;">${monthNames[month - 1]}</div>
            <div style="font-size: 0.7rem; color: #16a34a;">📦 Achat</div>
            <div style="font-size: 0.6rem; color: #6b7280;">Qty | Total | &nbsp;</div>
        `;
        headerRow.appendChild(achatHeader);
        
        // Vente column for this month
        const venteHeader = document.createElement('th');
        venteHeader.className = 'border px-2 py-1 text-center bg-blue-50 dark:bg-blue-900 font-medium sticky-header compact-cell';
        venteHeader.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 4px;">${monthNames[month - 1]}</div>
            <div style="font-size: 0.7rem; color: #2563eb;">🛒 Vente</div>
            <div style="font-size: 0.6rem; color: #6b7280;">Qty | Total | Marge</div>
        `;
        headerRow.appendChild(venteHeader);
    });

    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Create table body
    const tbody = document.createElement('tbody');
    filteredProducts.forEach(product => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700 product-row';

        // Product name cell
        const nameCell = document.createElement('td');
        nameCell.className = 'sticky-left bg-white dark:bg-gray-800 border px-4 py-2 z-10';
        nameCell.style.minWidth = '200px';
        nameCell.innerHTML = `
            <div class="text-gray-900 dark:text-gray-100 font-medium">
                ${product.name}
            </div>
        `;
        row.appendChild(nameCell);

        // Supplier cell
        const supplierCell = document.createElement('td');
        supplierCell.className = 'sticky-left bg-white dark:bg-gray-800 border px-4 py-2 z-10';
        supplierCell.style.minWidth = '150px';
        supplierCell.innerHTML = `
            <div class="text-blue-600 dark:text-blue-400 font-bold">
                ${product.supplier || 'Unknown'}
            </div>
        `;
        row.appendChild(supplierCell);

        // Data cells for each month (achat and vente columns) - only for selected months
        monthsToShow.forEach(month => {
            const monthIndex = month - 1; // Convert to 0-based index
            
            // Achat data cell
            const achatCell = document.createElement('td');
            achatCell.className = 'border compact-cell text-center bg-green-50/30 dark:bg-green-900/20';
            
            const achatQty = formatNumber(product.achatQuantities[monthIndex] || 0);
            const achatTotal = formatNumber(product.achatTotals[monthIndex] || 0);
            
            achatCell.innerHTML = `
                <div class="month-data">
                    <div class="month-data-item qty-item">${achatQty}</div>
                    <div class="month-data-item total-item">${achatTotal}</div>
                    <div class="month-data-item marge-item" style="visibility: hidden;">-</div>
                </div>
            `;
            row.appendChild(achatCell);
            
            // Vente data cell
            const venteCell = document.createElement('td');
            venteCell.className = 'border compact-cell text-center bg-blue-50/30 dark:bg-blue-900/20';
            
            const venteQty = formatNumber(product.venteQuantities[monthIndex] || 0);
            const venteTotal = formatNumber(product.venteTotals[monthIndex] || 0);
            const venteMarge = formatNumber((product.venteMarges[monthIndex] || 0) * 100);
            
            venteCell.innerHTML = `
                <div class="month-data">
                    <div class="month-data-item qty-item">${venteQty}</div>
                    <div class="month-data-item total-item">${venteTotal}</div>
                    <div class="month-data-item marge-item">${venteMarge}%</div>
                </div>
            `;
            row.appendChild(venteCell);
        });

        tbody.appendChild(row);
    });

    // Create totals row
    const totalsRow = document.createElement('tr');
    totalsRow.className = 'font-bold bg-gray-100 dark:bg-gray-700 totals-row';

    // Totals label
    const totalsLabel = document.createElement('td');
    totalsLabel.className = 'sticky-left bg-gray-100 dark:bg-gray-700 border px-4 py-2 z-10';
    totalsLabel.textContent = 'TOTAL';
    totalsRow.appendChild(totalsLabel);

    // Empty supplier cell for totals row
    const emptySupplierCell = document.createElement('td');
    emptySupplierCell.className = 'sticky-left bg-gray-100 dark:bg-gray-700 border px-4 py-2 z-10';
    emptySupplierCell.textContent = '';
    totalsRow.appendChild(emptySupplierCell);

    // Calculate and add totals for each month (achat and vente columns) - only for selected months
    monthsToShow.forEach(month => {
        const monthIndex = month - 1; // Convert to 0-based index
        
        // Achat totals
        const monthAchatQtyTotal = filteredProducts.reduce((sum, product) => sum + (product.achatQuantities[monthIndex] || 0), 0);
        const monthAchatTotalTotal = filteredProducts.reduce((sum, product) => sum + (product.achatTotals[monthIndex] || 0), 0);
        
        const achatTotalCell = document.createElement('td');
        achatTotalCell.className = 'border compact-cell text-center bg-green-100 dark:bg-green-800 font-bold';
        achatTotalCell.innerHTML = `
            <div class="month-data">
                <div class="month-data-item qty-item font-bold">${formatNumber(monthAchatQtyTotal)}</div>
                <div class="month-data-item total-item font-bold">${formatNumber(monthAchatTotalTotal)}</div>
                <div class="month-data-item marge-item font-bold" style="visibility: hidden;">-</div>
            </div>
        `;
        totalsRow.appendChild(achatTotalCell);
        
        // Vente totals
        const monthVenteQtyTotal = filteredProducts.reduce((sum, product) => sum + (product.venteQuantities[monthIndex] || 0), 0);
        const monthVenteTotalTotal = filteredProducts.reduce((sum, product) => sum + (product.venteTotals[monthIndex] || 0), 0);
        const monthVenteMargeItems = filteredProducts.map(product => product.venteMarges[monthIndex] || 0).filter(marge => marge !== 0);
        const monthVenteMargeAverage = monthVenteMargeItems.length > 0 
            ? monthVenteMargeItems.reduce((sum, marge) => sum + marge, 0) / monthVenteMargeItems.length 
            : 0;
        
        const venteTotalCell = document.createElement('td');
        venteTotalCell.className = 'border compact-cell text-center bg-blue-100 dark:bg-blue-800 font-bold';
        venteTotalCell.innerHTML = `
            <div class="month-data">
                <div class="month-data-item qty-item font-bold">${formatNumber(monthVenteQtyTotal)}</div>
                <div class="month-data-item total-item font-bold">${formatNumber(monthVenteTotalTotal)}</div>
                <div class="month-data-item marge-item font-bold">${formatNumber(monthVenteMargeAverage * 100)}%</div>
            </div>
        `;
        totalsRow.appendChild(venteTotalCell);
    });

    tbody.appendChild(totalsRow);
    table.appendChild(tbody);
    tableContainer.appendChild(table);
    return tableContainer;
}

// Update getSelectedSuppliers function to handle multiple selections properly
function getSelectedSuppliers() {
    const productSupplierSelect = elements.productSupplierSelect;
    const fournisseurInput = elements.inputs.fournisseur;
    
    // Check if product supplier dropdown is visible and has selections
    if (!elements.productSupplierContainer.classList.contains('hidden')) {
        const selectedOptions = Array.from(productSupplierSelect.selectedOptions);
        const selectedValues = selectedOptions
            .map(option => option.value)
            .filter(value => value !== '' && value !== 'SELECT_ALL'); // Filter out empty and SELECT_ALL
        if (selectedValues.length > 0) {
            return selectedValues;
        }
    }
    
    // Check fournisseur input as fallback
    if (fournisseurInput.value.trim()) {
        return [fournisseurInput.value.trim()];
    }
    
    return [];
}

// Get selected clients function to handle multiple selections properly
function getSelectedClients() {
    const zoneClientSelect = elements.zoneClientSelect;
    const clientInput = elements.inputs.client;
    
    // Check if zone client dropdown is visible and has selections
    if (!elements.zoneClientContainer.classList.contains('hidden')) {
        const selectedOptions = Array.from(zoneClientSelect.selectedOptions);
        const selectedValues = selectedOptions
            .map(option => option.value)
            .filter(value => value !== '' && value !== 'SELECT_ALL'); // Filter out empty and SELECT_ALL
        if (selectedValues.length > 0) {
            return selectedValues;
        }
    }
    
    // Check client input as fallback
    if (clientInput.value.trim()) {
        return [clientInput.value.trim()];
    }
    
    return [];
}

// Function to show Achat view when product or supplier is selected
function showAchatOnSelection() {
    // Switch to achat view
    currentViewMode = 'achat';
    const achatTables = document.getElementById('achatTables');
    const venteTables = document.getElementById('venteTables');
    const compareTables = document.getElementById('compareTables');
    const compareFiltersSection = document.getElementById('compareFiltersSection');
    const achatFiltersSection = document.getElementById('achatFiltersSection');
    const venteFiltersSection = document.getElementById('venteFiltersSection');
    
    if (achatTables) achatTables.classList.remove('hidden');
    if (venteTables) venteTables.classList.add('hidden');
    if (compareTables) compareTables.classList.add('hidden');
    if (compareFiltersSection) compareFiltersSection.classList.add('hidden');
    if (achatFiltersSection) achatFiltersSection.classList.remove('hidden');
    if (venteFiltersSection) venteFiltersSection.classList.add('hidden');
    
    elements.toggleAchat.classList.add('active');
    elements.toggleVente.classList.remove('active');
    elements.toggleCompare.classList.remove('active');
    
    // Show achat columns in year summary table
    if (elements.yearSummaryContainer) {
        elements.yearSummaryContainer.classList.remove('achat-hidden');
    }
    
    // Show the Achat toggle button
    if (elements.toggleAchat) {
        elements.toggleAchat.style.display = '';
    }
    
    // Reset the flag since achat is now explicitly shown
    achatHiddenByZoneClient = false;
}

// Function to hide Achat view when zone or client is selected
function hideAchatOnSelection() {
    // Switch to vente view
    currentViewMode = 'vente';
    const achatTables = document.getElementById('achatTables');
    const venteTables = document.getElementById('venteTables');
    const compareTables = document.getElementById('compareTables');
    const compareFiltersSection = document.getElementById('compareFiltersSection');
    const achatFiltersSection = document.getElementById('achatFiltersSection');
    const venteFiltersSection = document.getElementById('venteFiltersSection');
    
    if (venteTables) venteTables.classList.remove('hidden');
    if (achatTables) achatTables.classList.add('hidden');
    if (compareTables) compareTables.classList.add('hidden');
    if (compareFiltersSection) compareFiltersSection.classList.add('hidden');
    if (achatFiltersSection) achatFiltersSection.classList.add('hidden');
    if (venteFiltersSection) venteFiltersSection.classList.remove('hidden');
    
    elements.toggleVente.classList.add('active');
    elements.toggleAchat.classList.remove('active');
    elements.toggleCompare.classList.remove('active');
    
    // Hide achat columns in year summary table
    if (elements.yearSummaryContainer) {
        elements.yearSummaryContainer.classList.add('achat-hidden');
    }
    
    // Hide the Achat toggle button
    if (elements.toggleAchat) {
        elements.toggleAchat.style.display = 'none';
    }
    
    // Set flag to indicate achat was hidden due to zone/client selection
    achatHiddenByZoneClient = true;
}

// Update product supplier selection event handler
elements.productSupplierSelect.addEventListener('change', function() {
    const selectedValues = Array.from(this.selectedOptions).map(option => option.value);
    
    // Handle "Select All" functionality
    if (selectedValues.includes('SELECT_ALL')) {
        // Select all supplier options except the "Select All" option itself
        Array.from(this.options).forEach(option => {
            if (option.value !== 'SELECT_ALL' && option.value !== '') {
                option.selected = true;
            } else if (option.value === 'SELECT_ALL') {
                option.selected = false; // Deselect the "Select All" option after use
            }
        });
        
        // Update the visual state
        const allSelectedSuppliers = Array.from(this.selectedOptions).map(option => option.value);
        if (allSelectedSuppliers.length > 0) {
            elements.inputs.fournisseur.value = ''; // Clear the general supplier input
        }
    } else {
        // Normal selection handling
        const selectedSuppliers = selectedValues.filter(value => value !== 'SELECT_ALL' && value !== '');
        if (selectedSuppliers.length > 0) {
            elements.inputs.fournisseur.value = ''; // Clear the general supplier input
        }
    }
    
    // Show Achat when suppliers are selected
    const finalSelectedSuppliers = Array.from(this.selectedOptions).map(option => option.value).filter(value => value !== 'SELECT_ALL' && value !== '');
    if (finalSelectedSuppliers.length > 0) {
        showAchatOnSelection();
    }
});

// Update zone client selection event handler
elements.zoneClientSelect.addEventListener('change', function() {
    const selectedValues = Array.from(this.selectedOptions).map(option => option.value);
    
    // Handle "Select All" functionality
    if (selectedValues.includes('SELECT_ALL')) {
        // Select all client options except the "Select All" option itself
        Array.from(this.options).forEach(option => {
            if (option.value !== 'SELECT_ALL' && option.value !== '') {
                option.selected = true;
            } else if (option.value === 'SELECT_ALL') {
                option.selected = false; // Deselect the "Select All" option after use
            }
        });
        
        // Update the visual state
        const allSelectedClients = Array.from(this.selectedOptions).map(option => option.value);
        if (allSelectedClients.length > 0) {
            elements.inputs.client.value = ''; // Clear the general client input
        }
    } else {
        // Normal selection handling
        const selectedClients = selectedValues.filter(value => value !== 'SELECT_ALL' && value !== '');
        if (selectedClients.length > 0) {
            elements.inputs.client.value = ''; // Clear the general client input
        }
    }
    
    // Hide Achat when clients are selected
    const finalSelectedClients = Array.from(this.selectedOptions).map(option => option.value).filter(value => value !== 'SELECT_ALL' && value !== '');
    if (finalSelectedClients.length > 0) {
        hideAchatOnSelection();
    }
});

// Handle autocomplete selection for fournisseur input
elements.suggestionBoxes.fournisseur.addEventListener('click', function(e) {
    if (e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
        elements.inputs.fournisseur.value = e.target.textContent;
        elements.productSupplierSelect.selectedIndex = -1; // Clear product supplier selections
        this.classList.add('hidden');
    }
});

        // Initialize autocomplete for fournisseur and product
        async function initAutocomplete() {
            const ITEMS_PER_PAGE = 10;
            let currentFournisseurPage = 0;
            let currentProductPage = 0;
            let allFournisseurs = [];
            let allProducts = [];
            
            function showPaginatedSuggestions(filteredItems, currentPage, suggestionBox) {
                const startIdx = currentPage * ITEMS_PER_PAGE;
                const paginatedItems = filteredItems.slice(startIdx, startIdx + ITEMS_PER_PAGE);
                
                if (paginatedItems.length > 0) {
                    suggestionBox.innerHTML = paginatedItems.map(item => 
                        `<div class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">${item}</div>`
                    ).join('');
                    
                    if (filteredItems.length > ITEMS_PER_PAGE) {
                        const totalPages = Math.ceil(filteredItems.length / ITEMS_PER_PAGE);
                        suggestionBox.innerHTML += `
                            <div class="flex justify-between p-2 border-t border-gray-200 dark:border-gray-600">
                                <button class="pagination-prev px-2 py-1 rounded ${currentPage === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200 dark:hover:bg-gray-600'}" 
                                        ${currentPage === 0 ? 'disabled' : ''}>
                                    Previous
                                </button>
                                <span class="px-2 py-1">Page ${currentPage + 1} of ${totalPages}</span>
                                <button class="pagination-next px-2 py-1 rounded ${startIdx + ITEMS_PER_PAGE >= filteredItems.length ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200 dark:hover:bg-gray-600'}" 
                                        ${startIdx + ITEMS_PER_PAGE >= filteredItems.length ? 'disabled' : ''}>
                                    Next
                                </button>
                            </div>
                        `;
                    }
                    
                    suggestionBox.classList.remove('hidden');
                } else {
                    suggestionBox.classList.add('hidden');
                }
            }
            
            // Load fournisseurs
            try {
                const response = await fetch(API_ENDPOINTS.listFournisseur);
                allFournisseurs = await response.json();
                
                elements.inputs.fournisseur.addEventListener('input', () => {
                    const value = elements.inputs.fournisseur.value.toLowerCase();
                    const filtered = allFournisseurs.filter(f => f.toLowerCase().includes(value));
                    currentFournisseurPage = 0;
                    showPaginatedSuggestions(filtered, currentFournisseurPage, elements.suggestionBoxes.fournisseur);
                });
                
                elements.suggestionBoxes.fournisseur.addEventListener('click', (e) => {
                    if (e.target.classList.contains('pagination-prev')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (currentFournisseurPage > 0) {
                            currentFournisseurPage--;
                            const value = elements.inputs.fournisseur.value.toLowerCase();
                            const filtered = allFournisseurs.filter(f => f.toLowerCase().includes(value));
                            showPaginatedSuggestions(filtered, currentFournisseurPage, elements.suggestionBoxes.fournisseur);
                        }
                        return;
                    }
                    
                    if (e.target.classList.contains('pagination-next')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const value = elements.inputs.fournisseur.value.toLowerCase();
                        const filtered = allFournisseurs.filter(f => f.toLowerCase().includes(value));
                        if ((currentFournisseurPage + 1) * ITEMS_PER_PAGE < filtered.length) {
                            currentFournisseurPage++;
                            showPaginatedSuggestions(filtered, currentFournisseurPage, elements.suggestionBoxes.fournisseur);
                        }
                        return;
                    }
                    
                    if (e.target && e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
                        elements.inputs.fournisseur.value = e.target.textContent;
                        elements.suggestionBoxes.fournisseur.classList.add('hidden');
                        // Show Achat when fournisseur is selected
                        showAchatOnSelection();
                    }
                });
            } catch (error) {
            }
            
            // Load products
            try {
                const response = await fetch(API_ENDPOINTS.listProduct);
                const productsData = await response.json();
                // Store both product ID and name, but only display names in UI
                productMap = {};
                productsData.forEach(product => {
                    productMap[product.NAME] = product.M_PRODUCT_ID;
                });
                allProducts = productsData.map(product => product.NAME);
                
                elements.inputs.product.addEventListener('input', () => {
                    const value = elements.inputs.product.value.toLowerCase();
                    const filtered = allProducts.filter(p => p.toLowerCase().includes(value));
                    currentProductPage = 0;
                    showPaginatedSuggestions(filtered, currentProductPage, elements.suggestionBoxes.product);
                });
                
                // Handle product selection to load suppliers
                elements.inputs.product.addEventListener('change', async function() {
                    const productName = this.value;
                    if (productName) {
                        try {
                            const productId = productMap[productName];
                            if (!productId) {
                                elements.productSupplierContainer.classList.add('hidden');
                                return;
                            }
                            
                            elements.productSupplierSelect.disabled = true;
                            elements.productSupplierSelect.innerHTML = '<option value="">Loading suppliers...</option>';
                            
                            const response = await fetch(`${API_ENDPOINTS.fetchSuppliersByProduct}?product_id=${encodeURIComponent(productId)}`);
                            const suppliers = await response.json();
                            
                            elements.productSupplierSelect.innerHTML = '<option value="">Select a supplier</option>';
                            if (suppliers.length > 0) {
                                // Add "Select All" option
                                const selectAllOption = document.createElement('option');
                                selectAllOption.value = 'SELECT_ALL';
                                selectAllOption.textContent = '📋 Select All Suppliers';
                                selectAllOption.style.backgroundColor = '#fff3e0';
                                selectAllOption.style.fontWeight = 'bold';
                                elements.productSupplierSelect.appendChild(selectAllOption);
                                
                                // Add individual supplier options
                                suppliers.forEach(supplier => {
                                    const option = document.createElement('option');
                                    option.value = supplier;
                                    option.textContent = supplier;
                                    elements.productSupplierSelect.appendChild(option);
                                });
                                elements.productSupplierContainer.classList.remove('hidden');
                            } else {
                                elements.productSupplierContainer.classList.add('hidden');
                            }
                        } catch (error) {
                            elements.productSupplierSelect.innerHTML = '<option value="">Error loading suppliers</option>';
                        } finally {
                            elements.productSupplierSelect.disabled = false;
                        }
                    } else {
                        elements.productSupplierContainer.classList.add('hidden');
                    }
                    
                    // Show Achat when product is selected
                    if (productName) {
                        showAchatOnSelection();
                    }
                });
                
                elements.suggestionBoxes.product.addEventListener('click', (e) => {
                    if (e.target.classList.contains('pagination-prev')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (currentProductPage > 0) {
                            currentProductPage--;
                            const value = elements.inputs.product.value.toLowerCase();
                            const filtered = allProducts.filter(p => p.toLowerCase().includes(value));
                            showPaginatedSuggestions(filtered, currentProductPage, elements.suggestionBoxes.product);
                        }
                        return;
                    }
                    
                    if (e.target.classList.contains('pagination-next')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const value = elements.inputs.product.value.toLowerCase();
                        const filtered = allProducts.filter(p => p.toLowerCase().includes(value));
                        if ((currentProductPage + 1) * ITEMS_PER_PAGE < filtered.length) {
                            currentProductPage++;
                            showPaginatedSuggestions(filtered, currentProductPage, elements.suggestionBoxes.product);
                        }
                        return;
                    }
                    
                    if (e.target && e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
                        const selectedProductName = e.target.textContent;
                        const productId = productMap[selectedProductName];
                        
                        if (!productId) {
                            alert(`Cannot find product ID for "${selectedProductName}". Please select a valid product.`);
                            return;
                        }
                        
                        elements.inputs.product.value = selectedProductName;
                        elements.suggestionBoxes.product.classList.add('hidden');
                        
                        // Show supplier options if product is valid
                        elements.productSupplierContainer.classList.remove('hidden');
                        
                        // Trigger the change event to load suppliers
                        const event = new Event('change');
                        elements.inputs.product.dispatchEvent(event);
                    }
                });
            } catch (error) {
            }
            
            // Handle supplier selection from dropdown
            elements.productSupplierSelect.addEventListener('change', function() {
                if (this.value) {
                    elements.inputs.fournisseur.value = this.value;
                }
            });
            
            // Load zones
            try {
                const response = await fetch(API_ENDPOINTS.listRegion);
                const allZones = await response.json();
                let currentZonePage = 0;
                
                elements.inputs.zone.addEventListener('input', () => {
                    const value = elements.inputs.zone.value.toLowerCase();
                    const filtered = allZones.filter(z => z.toLowerCase().includes(value));
                    currentZonePage = 0;
                    showPaginatedSuggestions(filtered, currentZonePage, elements.suggestionBoxes.zone);
                });
                
                // Handle zone selection to load clients
                elements.inputs.zone.addEventListener('change', async function() {
                    const zone = this.value;
                    if (zone) {
                        try {
                            elements.zoneClientSelect.disabled = true;
                            elements.zoneClientSelect.innerHTML = '<option value="">Loading clients...</option>';
                            
                            const response = await fetch(`${API_ENDPOINTS.fetchZoneClients}?zone=${encodeURIComponent(zone)}`);
                            const clients = await response.json();
                            
                            elements.zoneClientSelect.innerHTML = '<option value="">Select a client</option>';
                            if (clients.length > 0) {
                                // Add "Select All" option
                                const selectAllOption = document.createElement('option');
                                selectAllOption.value = 'SELECT_ALL';
                                selectAllOption.textContent = '📋 Select All Clients';
                                selectAllOption.style.backgroundColor = '#e3f2fd';
                                selectAllOption.style.fontWeight = 'bold';
                                elements.zoneClientSelect.appendChild(selectAllOption);
                                
                                // Add individual client options
                                clients.forEach(client => {
                                    const option = document.createElement('option');
                                    option.value = client.CLIENT_NAME;
                                    option.textContent = client.CLIENT_NAME;
                                    elements.zoneClientSelect.appendChild(option);
                                });
                                elements.zoneClientContainer.classList.remove('hidden');
                            } else {
                                elements.zoneClientContainer.classList.add('hidden');
                            }
                        } catch (error) {
                            elements.zoneClientSelect.innerHTML = '<option value="">Error loading clients</option>';
                        } finally {
                            elements.zoneClientSelect.disabled = false;
                        }
                    } else {
                        elements.zoneClientContainer.classList.add('hidden');
                    }
                    
                    // Hide Achat when zone is selected
                    hideAchatOnSelection();
                });
                
                elements.suggestionBoxes.zone.addEventListener('click', (e) => {
                    if (e.target.classList.contains('pagination-prev')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (currentZonePage > 0) {
                            currentZonePage--;
                            const value = elements.inputs.zone.value.toLowerCase();
                            const filtered = allZones.filter(z => z.toLowerCase().includes(value));
                            showPaginatedSuggestions(filtered, currentZonePage, elements.suggestionBoxes.zone);
                        }
                        return;
                    }
                    
                    if (e.target.classList.contains('pagination-next')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const value = elements.inputs.zone.value.toLowerCase();
                        const filtered = allZones.filter(z => z.toLowerCase().includes(value));
                        if ((currentZonePage + 1) * ITEMS_PER_PAGE < filtered.length) {
                            currentZonePage++;
                            showPaginatedSuggestions(filtered, currentZonePage, elements.suggestionBoxes.zone);
                        }
                        return;
                    }
                    
                    if (e.target && e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
                        elements.inputs.zone.value = e.target.textContent;
                        elements.suggestionBoxes.zone.classList.add('hidden');
                        // Trigger the change event to load clients
                        const event = new Event('change');
                        elements.inputs.zone.dispatchEvent(event);
                    }
                });
            } catch (error) {
            }
            
            // Load clients
            try {
                const response = await fetch(API_ENDPOINTS.listClient);
                const allClients = await response.json();
                let currentClientPage = 0;
                
                elements.inputs.client.addEventListener('input', () => {
                    const value = elements.inputs.client.value.toLowerCase();
                    const filtered = allClients.filter(c => c.toLowerCase().includes(value));
                    currentClientPage = 0;
                    showPaginatedSuggestions(filtered, currentClientPage, elements.suggestionBoxes.client);
                });
                
                elements.suggestionBoxes.client.addEventListener('click', (e) => {
                    if (e.target.classList.contains('pagination-prev')) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (currentClientPage > 0) {
                            currentClientPage--;
                            const value = elements.inputs.client.value.toLowerCase();
                            const filtered = allClients.filter(c => c.toLowerCase().includes(value));
                            showPaginatedSuggestions(filtered, currentClientPage, elements.suggestionBoxes.client);
                        }
                        return;
                    }
                    
                    if (e.target.classList.contains('pagination-next')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const value = elements.inputs.client.value.toLowerCase();
                        const filtered = allClients.filter(c => c.toLowerCase().includes(value));
                        if ((currentClientPage + 1) * ITEMS_PER_PAGE < filtered.length) {
                            currentClientPage++;
                            showPaginatedSuggestions(filtered, currentClientPage, elements.suggestionBoxes.client);
                        }
                        return;
                    }
                    
                    if (e.target && e.target.textContent && !e.target.classList.contains('pagination-prev') && !e.target.classList.contains('pagination-next')) {
                        elements.inputs.client.value = e.target.textContent;
                        elements.suggestionBoxes.client.classList.add('hidden');
                        // Hide Achat when client is selected
                        hideAchatOnSelection();
                    }
                });
            } catch (error) {
            }
            
            // Handle client selection from zone dropdown
            elements.zoneClientSelect.addEventListener('change', function() {
                if (this.value) {
                    elements.inputs.client.value = this.value;
                }
            });
            
            // Hide suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!elements.inputs.fournisseur.contains(e.target) && !elements.suggestionBoxes.fournisseur.contains(e.target)) {
                    elements.suggestionBoxes.fournisseur.classList.add('hidden');
                }
                if (!elements.inputs.product.contains(e.target) && !elements.suggestionBoxes.product.contains(e.target)) {
                    elements.suggestionBoxes.product.classList.add('hidden');
                }
                if (!elements.inputs.zone.contains(e.target) && !elements.suggestionBoxes.zone.contains(e.target)) {
                    elements.suggestionBoxes.zone.classList.add('hidden');
                }
                if (!elements.inputs.client.contains(e.target) && !elements.suggestionBoxes.client.contains(e.target)) {
                    elements.suggestionBoxes.client.classList.add('hidden');
                }
            });
        }

        function resetFilters() {
            elements.yearCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            elements.inputs.fournisseur.value = '';
            elements.inputs.product.value = '';
            elements.inputs.zone.value = '';
            elements.inputs.client.value = '';
            elements.productSupplierContainer.classList.add('hidden');
            elements.zoneClientContainer.classList.add('hidden');

            document.getElementById('dataContainer').innerHTML = '';
            elements.resetBtn.classList.add('hidden');
            
            // Disable PDF download buttons when filters are reset
            document.getElementById('exportAchatPdf').disabled = true;
            document.getElementById('exportVentePdf').disabled = true;
            
            // Clear pagination
            const existingPagination = document.querySelector('.pagination-container');
            if (existingPagination) {
                existingPagination.remove();
            }
            allProductRows = [];
            totalItems = 0;
            currentPage = 1;
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize autocomplete
            initAutocomplete();
            
            // Set current year as default
            const currentYear = new Date().getFullYear();
            const currentYearCheckbox = document.querySelector(`.year-checkbox[value="${currentYear}"]`);
            if (currentYearCheckbox) {
                currentYearCheckbox.checked = true;
            }
            
            // Initially disable PDF download buttons until filters are applied
            document.getElementById('exportAchatPdf').disabled = true;
            document.getElementById('exportVentePdf').disabled = true;
            
            // Add event listeners
            elements.applyBtn.addEventListener('click', loadData);
            elements.resetBtn.addEventListener('click', resetFilters);
            
            // Add custom multi-select behavior for product supplier dropdown
            setupCustomMultiSelect();
            
            // Add keyboard navigation for tables
            setupKeyboardNavigation();
            
            // Add scroll sync for horizontal scrolling
            setupScrollSync();
        });

        // Custom multi-select functionality
        function setupCustomMultiSelect() {
            // Setup for product supplier select
            setupMultiSelectFor(elements.productSupplierSelect);
            
            // Setup for zone client select
            setupMultiSelectFor(elements.zoneClientSelect);
        }
        
        function setupMultiSelectFor(select) {
            // Override the default mousedown behavior
            select.addEventListener('mousedown', function(e) {
                e.preventDefault();
                
                const option = e.target;
                if (option.tagName === 'OPTION') {
                    // Toggle the selected state
                    option.selected = !option.selected;
                    
                    // Trigger change event
                    const changeEvent = new Event('change', { bubbles: true });
                    select.dispatchEvent(changeEvent);
                }
                
                return false;
            });
            
            // Prevent the dropdown from closing after selection
            select.addEventListener('click', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Handle keyboard navigation
            select.addEventListener('keydown', function(e) {
                if (e.code === 'Space' || e.code === 'Enter') {
                    e.preventDefault();
                    const focusedOption = select.options[select.selectedIndex];
                    if (focusedOption) {
                        focusedOption.selected = !focusedOption.selected;
                        const changeEvent = new Event('change', { bubbles: true });
                        select.dispatchEvent(changeEvent);
                    }
                }
            });
        }
        
        // Setup keyboard navigation for tables
        function setupKeyboardNavigation() {
            document.addEventListener('keydown', function(e) {
                const activeTable = document.querySelector('.month-table.active');
                if (activeTable && (e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT')) {
                    const scrollAmount = 50;
                    
                    switch(e.key) {
                        case 'ArrowLeft':
                            e.preventDefault();
                            activeTable.scrollLeft -= scrollAmount;
                            break;
                        case 'ArrowRight':
                            e.preventDefault();
                            activeTable.scrollLeft += scrollAmount;
                            break;
                        case 'ArrowUp':
                            e.preventDefault();
                            activeTable.scrollTop -= scrollAmount;
                            break;
                        case 'ArrowDown':
                            e.preventDefault();
                            activeTable.scrollTop += scrollAmount;
                            break;
                        case 'Home':
                            e.preventDefault();
                            activeTable.scrollLeft = 0;
                            break;
                        case 'End':
                            e.preventDefault();
                            activeTable.scrollLeft = activeTable.scrollWidth;
                            break;
                        case 'PageUp':
                            e.preventDefault();
                            activeTable.scrollTop -= activeTable.clientHeight * 0.8;
                            break;
                        case 'PageDown':
                            e.preventDefault();
                            activeTable.scrollTop += activeTable.clientHeight * 0.8;
                            break;
                    }
                }
            });
        }
        
        // Setup scroll synchronization between year summary tables
        function setupScrollSync() {
            // This function can be expanded to sync scroll positions between related tables
            // For now, it adds smooth scrolling behavior
            const containers = document.querySelectorAll('.table-container, .month-table');
            containers.forEach(container => {
                container.style.scrollBehavior = 'smooth';
            });
        }


        // PDF download event listeners
        document.getElementById('exportAchatPdf').addEventListener('click', async function() {
            await downloadPdf(this, 'achat');
        });

        document.getElementById('exportVentePdf').addEventListener('click', async function() {
            await downloadPdf(this, 'vente');
        });

        // PDF download function
        async function downloadPdf(btn, type) {
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner');
            const pdfIcon = btn.querySelector('.pdf-icon');
            const errorElement = document.getElementById('pdfError');
            
            try {
                // Get selected parameters
                const years = getSelectedYears();
                const fournisseurs = getSelectedSuppliers();
                const clients = getSelectedClients();
                const productName = elements.inputs.product.value;
                const zone = elements.inputs.zone.value;

                // Validate required parameters - only years are required, suppliers are optional for vente
                if (!years.length) {
                    throw new Error('Please select at least one year');
                }

                // For achat PDF, require suppliers
                if (type === 'achat' && fournisseurs.length === 0) {
                    throw new Error('Please select at least one supplier for Achat PDF');
                }

                // For compare PDF, require suppliers (since we need both achat and vente data)
                if (type === 'compare' && fournisseurs.length === 0) {
                    throw new Error('Please select at least one supplier for Compare PDF');
                }

                // Clear previous errors
                errorElement.classList.add('hidden');
                errorElement.textContent = '';
                
                // Show loading state
                btn.disabled = true;
                pdfIcon.classList.add('hidden');
                spinner.classList.remove('hidden');
                btnText.textContent = `Generating ${type} PDF...`;
                
                // Construct the URL with all parameters
                let url;
                if (type === 'compare') {
                    // For compare, use vente PDF endpoint but include both achat and vente parameters
                    url = `${API_ENDPOINTS.download_vente_pdf}?years=${years.join(',')}`;
                } else {
                    url = `${API_ENDPOINTS['download_' + type + '_pdf']}?years=${years.join(',')}`;
                }
                
                if (type === 'achat') {
                    // Achat PDF parameters
                    if (fournisseurs.length > 0) {
                        url += `&fournisseur=${fournisseurs.join(',')}`;
                    }
                    if (productName) {
                        const productId = productMap[productName];
                        if (productId) {
                            url += `&product_id=${encodeURIComponent(productId)}`;
                        } else {
                        }
                    }
                } else if (type === 'compare') {
                    // Compare PDF parameters - include both achat and vente parameters
                    if (fournisseurs.length > 0) {
                        url += `&fournisseur=${fournisseurs.join(',')}`;
                    }
                    if (clients.length > 0) {
                        url += `&client=${clients.join(',')}`;
                    }
                    if (zone) {
                        url += `&zone=${encodeURIComponent(zone)}`;
                    }
                    if (productName) {
                        const productId = productMap[productName];
                        if (productId) {
                            url += `&product_id=${encodeURIComponent(productId)}`;
                        } else {
                        }
                    }
                } else {
                    // Vente PDF parameters
                    if (fournisseurs.length > 0) {
                        url += `&fournisseur=${fournisseurs.join(',')}`;
                    }
                    if (clients.length > 0) {
                        url += `&client=${clients.join(',')}`;
                    }
                    if (zone) {
                        url += `&zone=${encodeURIComponent(zone)}`;
                    }
                    if (productName) {
                        const productId = productMap[productName];
                        if (productId) {
                            url += `&product_id=${encodeURIComponent(productId)}`;
                        } else {
                        }
                    }
                }

                // Try fetch approach first for better error handling
                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(errorText || `Failed to generate ${type} PDF`);
                    }
                    
                    const blob = await response.blob();
                    if (blob.size === 0) {
                        throw new Error(`Generated ${type} PDF is empty`);
                    }

                    // Generate a descriptive filename
                    const timestamp = new Date().toISOString().split('T')[0];
                    let fileName;
                    
                    if (type === 'achat') {
                        const supplierText = fournisseurs.length > 1 ? `${fournisseurs.length}_suppliers` : fournisseurs[0] || 'all';
                        const productText = productName ? productName.substring(0, 20) : 'all';
                        fileName = `achat_recap_${supplierText}_${productText}_${years.join('-')}_${timestamp}.pdf`;
                    } else if (type === 'compare') {
                        const supplierText = fournisseurs.length > 1 ? `${fournisseurs.length}_suppliers` : fournisseurs[0] || 'all';
                        const clientText = clients.length > 0 ? `_${clients.length}clients` : '';
                        const zoneText = zone ? `_${zone}` : '';
                        const productText = productName ? `_${productName.substring(0, 20)}` : '';
                        fileName = `compare_achat_vente_${supplierText}${zoneText}${clientText}${productText}_${years.join('-')}_${timestamp}.pdf`;
                    } else {
                        const supplierText = fournisseurs.length > 0 ? `${fournisseurs.length}_suppliers` : 'all';
                        const clientText = clients.length > 0 ? `_${clients.length}clients` : '';
                        const zoneText = zone ? `_${zone}` : '';
                        const productText = productName ? `_${productName.substring(0, 20)}` : '';
                        fileName = `vente_recap_${supplierText}${zoneText}${clientText}${productText}_${years.join('-')}_${timestamp}.pdf`;
                    }
                    
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(downloadUrl);
                } catch (fetchError) {
                    window.open(url, '_blank');
                }
                
            } catch (error) {
                errorElement.textContent = error.message;
                errorElement.classList.remove('hidden');
            } finally {
                // Reset button state
                btn.disabled = false;
                spinner.classList.add('hidden');
                pdfIcon.classList.remove('hidden');
                btnText.textContent = `Download ${type.charAt(0).toUpperCase() + type.slice(1)} PDF`;
            }
        }

        // Initialize autocomplete when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initAutocomplete();
            
            // Set up event listeners
            elements.applyBtn.addEventListener('click', loadData);
            
            elements.resetBtn.addEventListener('click', function() {
                // Reset all inputs
                elements.inputs.fournisseur.value = '';
                elements.inputs.product.value = '';
                elements.inputs.zone.value = '';
                elements.inputs.client.value = '';
                
                // Clear all checkboxes
                elements.yearCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Hide dropdowns
                elements.productSupplierContainer.classList.add('hidden');
                elements.zoneClientContainer.classList.add('hidden');
                
                // Clear containers
                document.getElementById('dataContainer').innerHTML = '';
                elements.yearSummaryContainer.innerHTML = '';
                
                // Hide reset button
                this.classList.add('hidden');
                
                // Hide all suggestion boxes
                Object.values(elements.suggestionBoxes).forEach(box => {
                    box.classList.add('hidden');
                });
            });
            
            // Set up dropdown selection handlers
            setupMultiSelectDropdown(elements.productSupplierSelect);
            setupMultiSelectDropdown(elements.zoneClientSelect);
            
            // Toggle button event listeners
            elements.toggleAchat.addEventListener('click', function() {
                // Add click animation
                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = '', 150);
                
                currentViewMode = 'achat';
                const achatTables = document.getElementById('achatTables');
                const venteTables = document.getElementById('venteTables');
                if (achatTables) achatTables.classList.remove('hidden');
                if (venteTables) venteTables.classList.add('hidden');
                elements.toggleAchat.classList.add('active');
                elements.toggleVente.classList.remove('active');
            });
            
            elements.toggleVente.addEventListener('click', function() {
                // Add click animation
                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = '', 150);
                
                currentViewMode = 'vente';
                const achatTables = document.getElementById('achatTables');
                const venteTables = document.getElementById('venteTables');
                const compareTables = document.getElementById('compareTables');
                if (venteTables) venteTables.classList.remove('hidden');
                if (achatTables) achatTables.classList.add('hidden');
                if (compareTables) compareTables.classList.add('hidden');
                elements.toggleVente.classList.add('active');
                elements.toggleAchat.classList.remove('active');
                elements.toggleCompare.classList.remove('active');
            });
            
            elements.toggleCompare.addEventListener('click', function() {
                // Add click animation
                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = '', 150);
                
                currentViewMode = 'compare';
                const achatTables = document.getElementById('achatTables');
                const venteTables = document.getElementById('venteTables');
                const compareTables = document.getElementById('compareTables');
                const compareFiltersSection = document.getElementById('compareFiltersSection');
                const achatFiltersSection = document.getElementById('achatFiltersSection');
                const venteFiltersSection = document.getElementById('venteFiltersSection');
                
                if (compareTables) compareTables.classList.remove('hidden');
                if (achatTables) achatTables.classList.add('hidden');
                if (venteTables) venteTables.classList.add('hidden');
                if (compareFiltersSection) compareFiltersSection.classList.remove('hidden');
                if (achatFiltersSection) achatFiltersSection.classList.add('hidden');
                if (venteFiltersSection) venteFiltersSection.classList.add('hidden');
                
                elements.toggleCompare.classList.add('active');
                elements.toggleAchat.classList.remove('active');
                elements.toggleVente.classList.remove('active');
            });
            
            // Show/hide appropriate filter sections when switching views
            elements.toggleAchat.addEventListener('click', function() {
                const compareFiltersSection = document.getElementById('compareFiltersSection');
                const achatFiltersSection = document.getElementById('achatFiltersSection');
                const venteFiltersSection = document.getElementById('venteFiltersSection');
                if (compareFiltersSection) compareFiltersSection.classList.add('hidden');
                if (achatFiltersSection) achatFiltersSection.classList.remove('hidden');
                if (venteFiltersSection) venteFiltersSection.classList.add('hidden');
            });
            
            elements.toggleVente.addEventListener('click', function() {
                const compareFiltersSection = document.getElementById('compareFiltersSection');
                const achatFiltersSection = document.getElementById('achatFiltersSection');
                const venteFiltersSection = document.getElementById('venteFiltersSection');
                if (compareFiltersSection) compareFiltersSection.classList.add('hidden');
                if (achatFiltersSection) achatFiltersSection.classList.add('hidden');
                if (venteFiltersSection) venteFiltersSection.classList.remove('hidden');
            });
            
            // Initialize month checkboxes in dropdown (Compare) — simple style, no hover/bg/animation
            const monthCheckboxList = document.getElementById('monthCheckboxList');
            if (monthCheckboxList) {
                monthNames.forEach((monthName, index) => {
                    const label = document.createElement('label');
                    // simple styling: aligned, padded, no hover bg or transition
                    label.className = 'flex items-center p-2 rounded cursor-pointer';
                    label.innerHTML = `
                        <input type="checkbox" value="${String(index + 1).padStart(2, '0')}" class="month-checkbox w-4 h-4 text-blue-600 rounded" checked>
                        <span class="ml-3 dark:text-white">${monthName}</span>
                    `;
                    monthCheckboxList.appendChild(label);
                });
            }
            
            // Update selected months text
            function updateSelectedMonthsText() {
                const checkboxes = document.querySelectorAll('.month-checkbox');
                const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                const selectedMonthsText = document.getElementById('selectedMonthsText');
                const monthCountText = document.getElementById('monthCountText');
                
                if (checkedCount === 12) {
                    selectedMonthsText.textContent = 'All Months Selected';
                } else if (checkedCount === 0) {
                    selectedMonthsText.textContent = 'No Months Selected';
                } else if (checkedCount === 1) {
                    const checkedMonth = Array.from(checkboxes).find(cb => cb.checked);
                    const monthIndex = parseInt(checkedMonth.value) - 1;
                    selectedMonthsText.textContent = monthNames[monthIndex];
                } else {
                    selectedMonthsText.textContent = `${checkedCount} Months Selected`;
                }
                
                monthCountText.textContent = `${checkedCount} month${checkedCount !== 1 ? 's' : ''} selected`;
            }
            
            // Month dropdown toggle
            const monthSelector = document.getElementById('compareMonthSelector');
            const monthDropdownMenu = document.getElementById('monthDropdownMenu');
            const monthDropdownIcon = document.getElementById('monthDropdownIcon');
            
            if (monthSelector && monthDropdownMenu) {
                monthSelector.addEventListener('click', function(e) {
                    e.stopPropagation();
                    monthDropdownMenu.classList.toggle('hidden');
                    monthDropdownIcon.classList.toggle('rotate');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!monthSelector.contains(e.target) && !monthDropdownMenu.contains(e.target)) {
                        monthDropdownMenu.classList.add('hidden');
                        monthDropdownIcon.classList.remove('rotate');
                    }
                });
            }
            
            // Update text when checkboxes change
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('month-checkbox')) {
                    updateSelectedMonthsText();
                }
            });
            
            // Select all months button
            const selectAllMonthsBtn = document.getElementById('selectAllMonthsBtn');
            if (selectAllMonthsBtn) {
                selectAllMonthsBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const checkboxes = document.querySelectorAll('.month-checkbox');
                    checkboxes.forEach(checkbox => checkbox.checked = true);
                    updateSelectedMonthsText();
                });
            }
            
            // Deselect all months button
            const deselectAllMonthsBtn = document.getElementById('deselectAllMonthsBtn');
            if (deselectAllMonthsBtn) {
                deselectAllMonthsBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const checkboxes = document.querySelectorAll('.month-checkbox');
                    checkboxes.forEach(checkbox => checkbox.checked = false);
                    updateSelectedMonthsText();
                });
            }
            
            // Product search autocomplete for compare section
            const compareProductSearch = document.getElementById('compareProductSearch');
            const compareProductSuggestions = document.getElementById('compareProductSuggestions');
            let availableProducts = [];
            
            if (compareProductSearch && compareProductSuggestions) {
                // Update available products when comparison data is loaded
                function updateAvailableProducts() {
                    if (!comparisonData || !comparisonYears) return;
                    
                    const productSet = new Set();
                    comparisonYears.forEach(year => {
                        const yearData = comparisonData.years[year] || {};
                        const achatData = yearData.achat || {};
                        const venteData = yearData.vente || {};
                        
                        // Get products from achat
                        Object.values(achatData).forEach(monthData => {
                            if (monthData.details) {
                                monthData.details.forEach(item => {
                                    if (item.PRODUIT) productSet.add(item.PRODUIT);
                                });
                            }
                        });
                        
                        // Get products from vente
                        Object.values(venteData).forEach(monthData => {
                            if (monthData.details) {
                                monthData.details.forEach(item => {
                                    if (item.PRODUIT) productSet.add(item.PRODUIT);
                                });
                            }
                        });
                    });
                    
                    availableProducts = Array.from(productSet).sort();
                }
                
                compareProductSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    
                    if (searchTerm.length === 0) {
                        compareProductSuggestions.classList.add('hidden');
                        return;
                    }
                    
                    // Update available products if not done yet
                    if (availableProducts.length === 0) {
                        updateAvailableProducts();
                    }
                    
                    const filtered = availableProducts.filter(product => 
                        product.toLowerCase().includes(searchTerm)
                    ).slice(0, 10); // Limit to 10 suggestions
                    
                    if (filtered.length > 0) {
                        compareProductSuggestions.innerHTML = filtered.map(product => 
                            `<div data-product="${product}">${product}</div>`
                        ).join('');
                        compareProductSuggestions.classList.remove('hidden');
                    } else {
                        compareProductSuggestions.innerHTML = '<div style="color: #9ca3af; cursor: default;">No products found</div>';
                        compareProductSuggestions.classList.remove('hidden');
                    }
                });
                
                // Handle suggestion click
                compareProductSuggestions.addEventListener('click', function(e) {
                    if (e.target.dataset.product) {
                        compareProductSearch.value = e.target.dataset.product;
                        compareProductSuggestions.classList.add('hidden');
                    }
                });
                
                // Close suggestions when clicking outside
                document.addEventListener('click', function(e) {
                    if (!compareProductSearch.contains(e.target) && !compareProductSuggestions.contains(e.target)) {
                        compareProductSuggestions.classList.add('hidden');
                    }
                });
                
                // Update products list when data is loaded
                const originalCreateComparisonTables = createComparisonTables;
                window.addEventListener('comparisonDataLoaded', function() {
                    updateAvailableProducts();
                });
            }
            
            // Apply Compare Filters button
            const applyCompareFiltersBtn = document.getElementById('applyCompareFilters');
            if (applyCompareFiltersBtn) {
                applyCompareFiltersBtn.addEventListener('click', function() {
                    if (!comparisonData || !comparisonYears) {
                        return;
                    }
                    
                    // Get selected months
                    const selectedMonths = Array.from(document.querySelectorAll('.month-checkbox:checked'))
                        .map(checkbox => parseInt(checkbox.value));
                    
                    if (selectedMonths.length === 0) {
                        alert('Please select at least one month');
                        return;
                    }
                    
                    // Get product filter
                    const productFilter = document.getElementById('compareProductSearch').value;
                    
                    // Recreate comparison tables with filters
                    const compareContainer = document.getElementById('compareTables');
                    if (compareContainer) {
                        compareContainer.innerHTML = ''; // Clear existing content
                        createComparisonTablesWithFilters(comparisonData, comparisonYears, compareContainer, selectedMonths, productFilter);
                    }
                });
            }
            
            // =======================
            // ACHAT FILTERS
            // =======================
            
            // Achat month dropdown initialization
            const achatMonthCheckboxList = document.getElementById('achatMonthCheckboxList');
            if (achatMonthCheckboxList) {
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                                  'July', 'August', 'September', 'October', 'November', 'December'];
                monthNames.forEach((monthName, index) => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer transition';
                    label.innerHTML = `
                        <input type="checkbox" value="${String(index + 1).padStart(2, '0')}" class="achat-month-checkbox w-4 h-4 text-blue-600 rounded" checked>
                        <span class="ml-3 dark:text-white">${monthName}</span>
                    `;
                    achatMonthCheckboxList.appendChild(label);
                });
            }
            
            // Achat month dropdown toggle
            const achatMonthSelector = document.getElementById('achatMonthSelector');
            const achatMonthDropdownMenu = document.getElementById('achatMonthDropdownMenu');
            const achatMonthDropdownIcon = document.getElementById('achatMonthDropdownIcon');
            
            if (achatMonthSelector && achatMonthDropdownMenu) {
                achatMonthSelector.addEventListener('click', function(e) {
                    e.stopPropagation();
                    achatMonthDropdownMenu.classList.toggle('hidden');
                    achatMonthDropdownIcon.classList.toggle('rotate');
                });
                
                document.addEventListener('click', function(e) {
                    if (!achatMonthSelector.contains(e.target) && !achatMonthDropdownMenu.contains(e.target)) {
                        achatMonthDropdownMenu.classList.add('hidden');
                        achatMonthDropdownIcon.classList.remove('rotate');
                    }
                });
            }
            
            // Achat month checkbox updates
            function updateAchatMonthsText() {
                const checked = document.querySelectorAll('.achat-month-checkbox:checked').length;
                const text = document.getElementById('achatSelectedMonthsText');
                const count = document.getElementById('achatMonthCountText');
                if (text) text.textContent = checked === 12 ? 'All Months Selected' : `${checked} month${checked !== 1 ? 's' : ''} selected`;
                if (count) count.textContent = `${checked} month${checked !== 1 ? 's' : ''} selected`;
            }
            
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('achat-month-checkbox')) {
                    updateAchatMonthsText();
                }
            });
            
            const achatSelectAllBtn = document.getElementById('achatSelectAllMonthsBtn');
            if (achatSelectAllBtn) {
                achatSelectAllBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.querySelectorAll('.achat-month-checkbox').forEach(cb => cb.checked = true);
                    updateAchatMonthsText();
                });
            }
            
            const achatClearAllBtn = document.getElementById('achatDeselectAllMonthsBtn');
            if (achatClearAllBtn) {
                achatClearAllBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.querySelectorAll('.achat-month-checkbox').forEach(cb => cb.checked = false);
                    updateAchatMonthsText();
                });
            }
            
            // =======================
            // VENTE FILTERS
            // =======================
            
            // Vente month dropdown initialization
            const venteMonthCheckboxList = document.getElementById('venteMonthCheckboxList');
            if (venteMonthCheckboxList) {
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                                  'July', 'August', 'September', 'October', 'November', 'December'];
                monthNames.forEach((monthName, index) => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer transition';
                    label.innerHTML = `
                        <input type="checkbox" value="${String(index + 1).padStart(2, '0')}" class="vente-month-checkbox w-4 h-4 text-blue-600 rounded" checked>
                        <span class="ml-3 dark:text-white">${monthName}</span>
                    `;
                    venteMonthCheckboxList.appendChild(label);
                });
            }
            
            // Vente month dropdown toggle
            const venteMonthSelector = document.getElementById('venteMonthSelector');
            const venteMonthDropdownMenu = document.getElementById('venteMonthDropdownMenu');
            const venteMonthDropdownIcon = document.getElementById('venteMonthDropdownIcon');
            
            if (venteMonthSelector && venteMonthDropdownMenu) {
                venteMonthSelector.addEventListener('click', function(e) {
                    e.stopPropagation();
                    venteMonthDropdownMenu.classList.toggle('hidden');
                    venteMonthDropdownIcon.classList.toggle('rotate');
                });
                
                document.addEventListener('click', function(e) {
                    if (!venteMonthSelector.contains(e.target) && !venteMonthDropdownMenu.contains(e.target)) {
                        venteMonthDropdownMenu.classList.add('hidden');
                        venteMonthDropdownIcon.classList.remove('rotate');
                    }
                });
            }
            
            // Vente month checkbox updates
            function updateVenteMonthsText() {
                const checked = document.querySelectorAll('.vente-month-checkbox:checked').length;
                const text = document.getElementById('venteSelectedMonthsText');
                const count = document.getElementById('venteMonthCountText');
                if (text) text.textContent = checked === 12 ? 'All Months Selected' : `${checked} month${checked !== 1 ? 's' : ''} selected`;
                if (count) count.textContent = `${checked} month${checked !== 1 ? 's' : ''} selected`;
            }
            
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('vente-month-checkbox')) {
                    updateVenteMonthsText();
                }
            });
            
            const venteSelectAllBtn = document.getElementById('venteSelectAllMonthsBtn');
            if (venteSelectAllBtn) {
                venteSelectAllBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.querySelectorAll('.vente-month-checkbox').forEach(cb => cb.checked = true);
                    updateVenteMonthsText();
                });
            }
            
            const venteClearAllBtn = document.getElementById('venteDeselectAllMonthsBtn');
            if (venteClearAllBtn) {
                venteClearAllBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.querySelectorAll('.vente-month-checkbox').forEach(cb => cb.checked = false);
                    updateVenteMonthsText();
                });
            }
            
            // =======================
            // ACHAT PRODUCT SEARCH
            // =======================
            const achatProductSearch = document.getElementById('achatProductSearch');
            const achatProductSuggestions = document.getElementById('achatProductSuggestions');
            
            if (achatProductSearch && achatProductSuggestions) {
                let achatAvailableProducts = [];
                
                // Get products from achat tables when they load
                function updateAchatProducts() {
                    const productSet = new Set();
                    const achatTables = document.getElementById('achatTables');
                    if (achatTables) {
                        const tables = achatTables.querySelectorAll('table');
                        tables.forEach(table => {
                            const rows = table.querySelectorAll('tbody tr');
                            rows.forEach(row => {
                                const firstCell = row.querySelector('td');
                                if (firstCell && firstCell.textContent.trim()) {
                                    productSet.add(firstCell.textContent.trim());
                                }
                            });
                        });
                    }
                    achatAvailableProducts = Array.from(productSet).sort();
                }
                
                // Update on page load and when tables change
                setTimeout(updateAchatProducts, 1000);
                
                achatProductSearch.addEventListener('input', function() {
                    updateAchatProducts();
                    const value = this.value.toLowerCase().trim();
                    
                    if (!value) {
                        achatProductSuggestions.classList.add('hidden');
                        return;
                    }
                    
                    const filtered = achatAvailableProducts
                        .filter(p => p.toLowerCase().includes(value))
                        .slice(0, 10);
                    
                    if (filtered.length > 0) {
                        achatProductSuggestions.innerHTML = filtered.map(product => 
                            `<div data-product="${product}">${product}</div>`
                        ).join('');
                        achatProductSuggestions.classList.remove('hidden');
                    } else {
                        achatProductSuggestions.innerHTML = '<div style="color: #9ca3af; cursor: default;">No products found</div>';
                        achatProductSuggestions.classList.remove('hidden');
                    }
                });
                
                achatProductSuggestions.addEventListener('click', function(e) {
                    if (e.target.dataset.product) {
                        achatProductSearch.value = e.target.dataset.product;
                        achatProductSuggestions.classList.add('hidden');
                    }
                });
                
                document.addEventListener('click', function(e) {
                    if (!achatProductSearch.contains(e.target) && !achatProductSuggestions.contains(e.target)) {
                        achatProductSuggestions.classList.add('hidden');
                    }
                });
            }
            
            // =======================
            // VENTE PRODUCT SEARCH
            // =======================
            const venteProductSearch = document.getElementById('venteProductSearch');
            const venteProductSuggestions = document.getElementById('venteProductSuggestions');
            
            if (venteProductSearch && venteProductSuggestions) {
                let venteAvailableProducts = [];
                
                // Get products from vente tables when they load
                function updateVenteProducts() {
                    const productSet = new Set();
                    const venteTables = document.getElementById('venteTables');
                    if (venteTables) {
                        const tables = venteTables.querySelectorAll('table');
                        tables.forEach(table => {
                            const rows = table.querySelectorAll('tbody tr');
                            rows.forEach(row => {
                                const firstCell = row.querySelector('td');
                                if (firstCell && firstCell.textContent.trim()) {
                                    productSet.add(firstCell.textContent.trim());
                                }
                            });
                        });
                    }
                    venteAvailableProducts = Array.from(productSet).sort();
                }
                
                // Update on page load and when tables change
                setTimeout(updateVenteProducts, 1000);
                
                venteProductSearch.addEventListener('input', function() {
                    updateVenteProducts();
                    const value = this.value.toLowerCase().trim();
                    
                    if (!value) {
                        venteProductSuggestions.classList.add('hidden');
                        return;
                    }
                    
                    const filtered = venteAvailableProducts
                        .filter(p => p.toLowerCase().includes(value))
                        .slice(0, 10);
                    
                    if (filtered.length > 0) {
                        venteProductSuggestions.innerHTML = filtered.map(product => 
                            `<div data-product="${product}">${product}</div>`
                        ).join('');
                        venteProductSuggestions.classList.remove('hidden');
                    } else {
                        venteProductSuggestions.innerHTML = '<div style="color: #9ca3af; cursor: default;">No products found</div>';
                        venteProductSuggestions.classList.remove('hidden');
                    }
                });
                
                venteProductSuggestions.addEventListener('click', function(e) {
                    if (e.target.dataset.product) {
                        venteProductSearch.value = e.target.dataset.product;
                        venteProductSuggestions.classList.add('hidden');
                    }
                });
                
                document.addEventListener('click', function(e) {
                    if (!venteProductSearch.contains(e.target) && !venteProductSuggestions.contains(e.target)) {
                        venteProductSuggestions.classList.add('hidden');
                    }
                });
            }
            
            // =======================
            // ACHAT APPLY FILTERS
            // =======================
            const applyAchatFiltersBtn = document.getElementById('applyAchatFilters');
            if (applyAchatFiltersBtn) {
                applyAchatFiltersBtn.addEventListener('click', function() {
                    const selectedMonths = Array.from(document.querySelectorAll('.achat-month-checkbox:checked'))
                        .map(checkbox => parseInt(checkbox.value));
                    const productFilter = document.getElementById('achatProductSearch').value.toLowerCase().trim();
                    
                    if (selectedMonths.length === 0) {
                        alert('Please select at least one month');
                        return;
                    }
                    
                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                                      'July', 'August', 'September', 'October', 'November', 'December'];
                    const selectedMonthNames = selectedMonths.map(m => monthNames[m - 1]);
                    
                    // Filter tables
                    const achatTables = document.getElementById('achatTables');
                    if (achatTables) {
                        const tables = achatTables.querySelectorAll('table');
                        tables.forEach(table => {
                            // Filter month columns
                            const headerCells = table.querySelectorAll('thead th');
                            headerCells.forEach((th, index) => {
                                // Check if this header contains a month name
                                const headerText = th.textContent;
                                let isMonthColumn = false;
                                let shouldShow = false;
                                
                                for (let i = 0; i < monthNames.length; i++) {
                                    if (headerText.includes(monthNames[i])) {
                                        isMonthColumn = true;
                                        shouldShow = selectedMonthNames.includes(monthNames[i]);
                                        break;
                                    }
                                }
                                
                                if (isMonthColumn) {
                                    th.style.display = shouldShow ? '' : 'none';
                                }
                            });
                            
                            // Filter rows by product
                            const rows = table.querySelectorAll('tbody tr');
                            rows.forEach(row => {
                                const firstCell = row.querySelector('td');
                                const productName = firstCell ? firstCell.textContent.toLowerCase() : '';
                                
                                if (productFilter && !productName.includes(productFilter)) {
                                    row.style.display = 'none';
                                } else {
                                    row.style.display = '';
                                    // Show/hide month columns in data rows
                                    const cells = row.querySelectorAll('td');
                                    cells.forEach((td, index) => {
                                        const headerText = headerCells[index] ? headerCells[index].textContent : '';
                                        let isMonthColumn = false;
                                        let shouldShow = false;
                                        
                                        for (let i = 0; i < monthNames.length; i++) {
                                            if (headerText.includes(monthNames[i])) {
                                                isMonthColumn = true;
                                                shouldShow = selectedMonthNames.includes(monthNames[i]);
                                                break;
                                            }
                                        }
                                        
                                        if (isMonthColumn) {
                                            td.style.display = shouldShow ? '' : 'none';
                                        }
                                    });
                                }
                            });
                        });
                    }
                });
            }
            
            // =======================
            // VENTE APPLY FILTERS
            // =======================
            const applyVenteFiltersBtn = document.getElementById('applyVenteFilters');
            if (applyVenteFiltersBtn) {
                applyVenteFiltersBtn.addEventListener('click', function() {
                    const selectedMonths = Array.from(document.querySelectorAll('.vente-month-checkbox:checked'))
                        .map(checkbox => parseInt(checkbox.value));
                    const productFilter = document.getElementById('venteProductSearch').value.toLowerCase().trim();
                    
                    if (selectedMonths.length === 0) {
                        alert('Please select at least one month');
                        return;
                    }
                    
                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                                      'July', 'August', 'September', 'October', 'November', 'December'];
                    const selectedMonthNames = selectedMonths.map(m => monthNames[m - 1]);
                    
                    // Filter tables
                    const venteTables = document.getElementById('venteTables');
                    if (venteTables) {
                        const tables = venteTables.querySelectorAll('table');
                        tables.forEach(table => {
                            // Filter month columns
                            const headerCells = table.querySelectorAll('thead th');
                            headerCells.forEach((th, index) => {
                                // Check if this header contains a month name
                                const headerText = th.textContent;
                                let isMonthColumn = false;
                                let shouldShow = false;
                                
                                for (let i = 0; i < monthNames.length; i++) {
                                    if (headerText.includes(monthNames[i])) {
                                        isMonthColumn = true;
                                        shouldShow = selectedMonthNames.includes(monthNames[i]);
                                        break;
                                    }
                                }
                                
                                if (isMonthColumn) {
                                    th.style.display = shouldShow ? '' : 'none';
                                }
                            });
                            
                            // Filter rows by product
                            const rows = table.querySelectorAll('tbody tr');
                            rows.forEach(row => {
                                const firstCell = row.querySelector('td');
                                const productName = firstCell ? firstCell.textContent.toLowerCase() : '';
                                
                                if (productFilter && !productName.includes(productFilter)) {
                                    row.style.display = 'none';
                                } else {
                                    row.style.display = '';
                                    // Show/hide month columns in data rows
                                    const cells = row.querySelectorAll('td');
                                    cells.forEach((td, index) => {
                                        const headerText = headerCells[index] ? headerCells[index].textContent : '';
                                        let isMonthColumn = false;
                                        let shouldShow = false;
                                        
                                        for (let i = 0; i < monthNames.length; i++) {
                                            if (headerText.includes(monthNames[i])) {
                                                isMonthColumn = true;
                                                shouldShow = selectedMonthNames.includes(monthNames[i]);
                                                break;
                                            }
                                        }
                                        
                                        if (isMonthColumn) {
                                            td.style.display = shouldShow ? '' : 'none';
                                        }
                                    });
                                }
                            });
                        });
                    }
                });
            }
        });
        
        // New function to create comparison tables with filters
        function createComparisonTablesWithFilters(data, years, container, selectedMonths, productFilter) {
            years.forEach(year => {
                const yearData = data.years[year] || {};
                const achatData = yearData.achat || {};
                const venteData = yearData.vente || {};
                
                // Create year table container
                const yearTableContainer = document.createElement('div');
                yearTableContainer.className = `month-table ${year === years[0] ? 'active' : ''}`;
                yearTableContainer.dataset.year = year;
                
                // Group all data by product-supplier combination from both achat and vente
                const productGroups = {};
                
                // Process achat data
                for (let month = 1; month <= 12; month++) {
                    const monthNum = month.toString().padStart(2, '0');
                    const monthData = achatData[monthNum] || { details: [] };
                    
                    monthData.details.forEach(item => {
                        const productKey = `${item.PRODUIT}|${item.FOURNISSEUR}`;
                        if (!productGroups[productKey]) {
                            productGroups[productKey] = {
                                name: item.PRODUIT,
                                supplier: item.FOURNISSEUR,
                                achatQuantities: Array(12).fill(0),
                                achatTotals: Array(12).fill(0),
                                venteQuantities: Array(12).fill(0),
                                venteTotals: Array(12).fill(0),
                                venteMarges: Array(12).fill(0),
                                venteMonthItems: Array(12).fill(null).map(() => [])
                            };
                        }
                        
                        const productData = productGroups[productKey];
                        productData.achatQuantities[month - 1] += (item.QTY || 0);
                        productData.achatTotals[month - 1] += (item.CHIFFRE || item.TOTAL || 0);
                    });
                }
                
                // Process vente data
                for (let month = 1; month <= 12; month++) {
                    const monthNum = month.toString().padStart(2, '0');
                    const monthData = venteData[monthNum] || { details: [] };
                    
                    monthData.details.forEach(item => {
                        const productKey = `${item.PRODUIT}|${item.FOURNISSEUR}`;
                        if (!productGroups[productKey]) {
                            productGroups[productKey] = {
                                name: item.PRODUIT,
                                supplier: item.FOURNISSEUR,
                                achatQuantities: Array(12).fill(0),
                                achatTotals: Array(12).fill(0),
                                venteQuantities: Array(12).fill(0),
                                venteTotals: Array(12).fill(0),
                                venteMarges: Array(12).fill(0),
                                venteMonthItems: Array(12).fill(null).map(() => [])
                            };
                        }
                        
                        const productData = productGroups[productKey];
                        productData.venteQuantities[month - 1] += (item.QTY || 0);
                        productData.venteTotals[month - 1] += (item.TOTAL || 0);
                        productData.venteMonthItems[month - 1].push({
                            TOTAL: item.TOTAL || 0,
                            MARGE: item.MARGE || 0
                        });
                    });
                }
                
                // Calculate correct margins for vente data
                Object.values(productGroups).forEach(productData => {
                    for (let month = 0; month < 12; month++) {
                        const monthItems = productData.venteMonthItems[month];
                        if (monthItems.length > 0) {
                            let totalConsomation = 0;
                            let totalTotal = 0;
                            
                            monthItems.forEach(item => {
                                totalTotal += item.TOTAL;
                                const consomation = item.MARGE > 0 ? item.TOTAL / (1 + item.MARGE) : item.TOTAL;
                                totalConsomation += consomation;
                            });
                            
                            productData.venteMarges[month] = totalConsomation > 0 
                                ? (totalTotal - totalConsomation) / totalConsomation 
                                : 0;
                        }
                    }
                    delete productData.venteMonthItems;
                });
                
                // Convert to array and create table with filters
                const products = Object.values(productGroups);
                if (products.length > 0) {
                    const tableContainer = createComparisonTable(products, year, selectedMonths, productFilter);
                    yearTableContainer.appendChild(tableContainer);
                    container.appendChild(yearTableContainer);
                }
            });
        }
    </script>
</body>
</html>