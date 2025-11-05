<?php
session_start();

// Include navigation helper
require_once __DIR__ . '/navigation_helper.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Store username and role in variables
$username = $_SESSION['username'] ?? 'Guest';
$Role = $_SESSION['Role'] ?? 'Uknown'; // Default role as 'user'

// Define allowed pages for specific roles (add more as needed)
// Use shared loader from navigation_helper.php
$role_allowed_pages = load_permissions();


function is_page_allowed($page, $role, $role_allowed_pages) {
    if (($role_allowed_pages[$role] ?? null) === 'all') {
        return true;
    }
    $allowed = $role_allowed_pages[$role] ?? [];
    return in_array($page, $allowed);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        body.dark-mode {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 100%);
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            color: #1e293b;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            max-height: 100vh;
            width: 280px;
            height: 100vh;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-radius: 0 20px 20px 0;
            display: flex;
            flex-direction: column;
        }

        .dark .sidebar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            color: #f8fafc;
            border-right-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        /* Brand Header Styles */
        .brand-header {
            padding: 0.3rem 1rem 0.75rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            margin: 0;
            line-height: 1;
        }

        .brand-text {
            font-family: 'Poppins', sans-serif;
            text-transform: lowercase;
            font-weight: 500;
            font-size: 18px;
            color: #c9a35c;
            letter-spacing: 1px;
            margin: 0;
            padding: 0;
            line-height: 1;
            text-shadow: 0 2px 4px rgba(201, 163, 92, 0.3);
        }

        .dark .brand-header {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.3) 0%, rgba(15, 23, 42, 0.1) 100%);
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }

        .dark .brand-text {
            color: #d4af37;
            text-shadow: 0 2px 4px rgba(212, 175, 55, 0.4);
        }

        .sidebar-nav {
            flex: 1;
            padding: 0.5rem 0;
            overflow-y: auto;
        }

        .sidebar-nav ul {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .sidebar-nav button {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 0.7rem;
            width: 90%;
            border: none;
            background: transparent;
            color: #475569;
            text-align: left;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 500;
            border-radius: 12px;
            margin: 0.2rem 0.4rem;
            box-sizing: border-box;
        }

        .sidebar-nav button:hover {
            background: rgba(0, 0, 0, 0.05);
            color: #1e293b;
        }

        .sidebar-nav button.active {
            background: rgba(0, 0, 0, 0.1);
            color: #1e293b;
            font-weight: 600;
        }
        

        .dark .sidebar-nav button {
            color: #cbd5e1;
        }

        .dark .sidebar-nav button:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #f8fafc;
        }

        .dark .sidebar-nav button.active {
            background: rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            font-weight: 600;
        }

        .sidebar-nav .icon {
            color: #64748b;
            font-size: 0.7rem;
        }

        .dark .sidebar-nav .icon {
            color: #94a3b8;
        }

        .sidebar hr {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            margin: 1.5rem 1rem;
        }

        .dark .sidebar hr {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        }

        .logo-section {
            background: linear-gradient(135deg, #f4e4bc 0%, #c9a35c 100%);
            padding: 0.75rem 0.6rem;
            text-align: center;
            border-bottom: 1px solid rgba(201, 163, 92, 0.3);
            position: relative;
            overflow: hidden;
        }

        .logo-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }

        .logo-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
        }

        .dark .logo-section {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        }

        .user-profile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.2rem;
        }

        .user-avatar {
            font-size: 1.4rem;
            color: #fbbf24;
            filter: drop-shadow(0 2px 4px rgba(251, 191, 36, 0.3));
        }

        .dark .user-avatar {
            color: #fbbf24;
            filter: drop-shadow(0 2px 4px rgba(251, 191, 36, 0.4));
        }

        .user-info {
            text-align: left;
            flex: 1;
        }

        .user-name {
            font-weight: 700;
            font-size: 0.7rem;
            color: #000000;
            margin-bottom: 0.2rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .dark .user-name {
            color: #f8fafc;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .user-role {
            font-size: 0.5rem;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .dark .user-role {
            color: rgba(248, 250, 252, 0.8);
        }

        .highlight {
            color: #10b981;
            font-weight: 700;
        }

        .submenu {
            display: none;
            margin-left: 1rem;
            border-left: 2px solid #7fb206;
            padding-left: 1rem;
        }

        .submenu.show {
            display: block;
        }

        .mode-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.4rem 0.6rem;
            margin: 0.3rem 0.6rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .dark .mode-toggle {
            background: rgba(15, 23, 42, 0.3);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .mode-toggle .mode-label {
            display: flex;
            align-items: center;
            font-size: 0.6rem;
            font-weight: 600;
            color: #333;
            gap: 0.3rem;
        }

        .dark .mode-toggle .mode-label {
            color: #f1f5f9;
        }

        .mode-toggle input[type="radio"] + label i {
            font-size: 0.55rem;
        }

                .mode-toggle input[type="radio"] + label i {
            font-size: 0.55rem;
        }

        .mode-toggle .mode-label i {
            color: #c9a35c;
            font-size: 0.7rem;
            filter: drop-shadow(0 1px 2px rgba(201, 163, 92, 0.3));
        }

        .mode-toggle .mode-options {
            display: flex;
            gap: 0;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 2px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dark .mode-toggle .mode-options {
            background: rgba(15, 23, 42, 0.3);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .mode-toggle input[type="radio"] {
            display: none;
        }

        .mode-toggle input[type="radio"] + label {
            padding: 0.4rem 0.6rem;
            border-radius: 8px;
            font-size: 0.6rem;
            font-weight: 600;
            color: #64748b;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            flex: 1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .mode-toggle input[type="radio"] + label:hover {
            background: rgba(201, 163, 92, 0.1);
            color: #c9a35c;
        }

        .dark .mode-toggle input[type="radio"] + label {
            color: #94a3b8;
        }

        .dark .mode-toggle input[type="radio"] + label:hover {
            background: rgba(212, 175, 55, 0.1);
            color: #d4af37;
        }

        .mode-toggle input[type="radio"]:checked + label {
            background: linear-gradient(135deg, #c9a35c 0%, #d4af37 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(201, 163, 92, 0.3);
            transform: scale(1.02);
        }

        .dark .mode-toggle input[type="radio"]:checked + label {
            background: linear-gradient(135deg, #d4af37 0%, #c9a35c 100%);
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.4);
        }        .mode-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            background: #e9ecef;
            border-radius: 24px;
            cursor: pointer;
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .mode-switch:hover {
            border-color: #adb5bd;
        }

        .dark .mode-switch {
            background: #475569;
            border-color: #64748b;
        }

        .dark .mode-switch:hover {
            border-color: #94a3b8;
        }

        .mode-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 2px;
            left: 2px;
            right: 2px;
            bottom: 2px;
            background: #ffffff;
            border-radius: 50px;
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 12px;
            width: 12px;
            left: 4px;
            bottom: 4px;
            background: #6c757d;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        input:checked + .slider {
            background: #7fb206;
            border-color: #7fb206;
        }

        input:checked + .slider:before {
            transform: translateX(16px);
            background: #ffffff;
        }

        .dark input:checked + .slider {
            background: #a8d633;
            border-color: #a8d633;
        }

        /* Logout Panel Styles */
        .logout-panel {
            position: fixed;
            top: 0;
            right: -350px;
            width: 350px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 20px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .logout-panel.open {
            right: 0;
        }

        .logout-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .logout-panel-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .panel-close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .panel-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .logout-panel-content {
            flex: 1;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .logout-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .logout-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }

        .logout-option.primary {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
        }

        .logout-option.primary:hover {
            background: linear-gradient(135deg, #c82333 0%, #a02622 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .logout-option.secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .logout-option.secondary:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .logout-option i {
            font-size: 1.25rem;
        }

        .logout-stats {
            border-top: 1px solid #e5e7eb;
            padding-top: 1.5rem;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .stat-item:last-child {
            margin-bottom: 0;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .stat-value {
            color: #333;
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* Dark mode styles for logout panel */
        .dark .logout-panel {
            background: #1e293b;
            color: white;
        }

        .dark .logout-panel-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }

        .dark .logout-option.secondary {
            background: #334155;
            color: #cbd5e1;
            border-color: #475569;
        }

        .dark .logout-option.secondary:hover {
            background: #475569;
            border-color: #64748b;
        }

        .dark .logout-stats {
            border-top-color: #334155;
        }

        .dark .stat-label {
            color: #94a3b8;
        }

        .dark .stat-value {
            color: #f1f5f9;
        }

        /* Power Button Styles */
        .power-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            font-size: 0.7rem;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        /* Dark mode styles for power button */
        .dark .power-btn {
            background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
            box-shadow: 0 4px 16px rgba(248, 113, 113, 0.4);
        }

        .dark .power-btn:hover {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.5);
        }

        #sidebarToggle {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: fixed;
            top: 24px;
            left: 24px;
            z-index: 1010;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .dark #sidebarToggle {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #f8fafc;
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }

        .sidebar-visible #sidebarToggle {
            left: 300px;
        }

        .sidebar-nav button.disabled {
            opacity: 0.4;
            pointer-events: none;
            cursor: not-allowed;
        }

        /* Bottom Action Buttons */
        .sidebar-bottom-actions {
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: auto;
            align-items: center;
            justify-items: center;
        }

        .dark .sidebar-bottom-actions {
            border-top-color: rgba(255, 255, 255, 0.05);
        }

        .sidebar-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sidebar-action-btn:hover {
            transform: translateY(-3px) scale(1.05);
        }

        .sidebar-action-btn:active {
            transform: translateY(-1px) scale(0.95);
        }

        .sidebar-action-btn span {
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
        }

        .dark .sidebar-action-btn span {
            color: #cbd5e1;
        }

        .sidebar-action-btn i {
            font-size: 2.5rem;
            color: #475569;
        }

        .dark .sidebar-action-btn i {
            color: #cbd5e1;
        }

        .sidebar-action-btn:hover i,
        .sidebar-action-btn:hover span {
            color: #c9a35c;
        }

        .dark .sidebar-action-btn:hover i,
        .dark .sidebar-action-btn:hover span {
            color: #d4af37;
        }

        /* Mini Eva Robot Animation */
        .loader-mini {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modelViewPort-mini {
            perspective: 1000px;
            width: 7rem;
            aspect-ratio: 1;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #000;
            overflow: hidden;
        }

        .eva-mini {
            --EVA-ROTATION-DURATION: 4s;
            transform-style: preserve-3d;
            animation: rotateRight var(--EVA-ROTATION-DURATION) linear infinite alternate;
        }

        .head-mini {
            position: relative;
            width: 1.65rem;
            height: 1.125rem;
            border-radius: 48% 53% 45% 55% / 79% 79% 20% 22%;
            background: linear-gradient(to right, white 45%, gray);
        }

        .eyeChamber-mini {
            width: 1.2rem;
            height: 0.75rem;
            position: relative;
            left: 50%;
            top: 55%;
            border-radius: 45% 53% 45% 48% / 62% 59% 35% 34%;
            background-color: #0c203c;
            box-shadow: 0px 0px 1.5px 1.5px white, inset 0px 0px 0px 1.5px black;
            transform: translate(-50%, -50%);
            animation: moveRight var(--EVA-ROTATION-DURATION) linear infinite alternate;
        }

        .eye-mini {
            width: 0.34rem;
            height: 0.45rem;
            position: absolute;
            border-radius: 50%;
        }

        .eye-mini:first-child {
            left: 5px;
            top: 50%;
            background: repeating-linear-gradient(65deg, #9bdaeb 0px, #9bdaeb 0.75px, white 1.5px);
            box-shadow: inset 0px 0px 3.5px #04b8d5, 0px 0px 11px 0.75px #0bdaeb;
            transform: translate(0, -50%) rotate(-65deg);
        }

        .eye-mini:nth-child(2) {
            right: 5px;
            top: 50%;
            background: repeating-linear-gradient(-65deg, #9bdaeb 0px, #9bdaeb 0.75px, white 1.5px);
            box-shadow: inset 0px 0px 3.5px #04b8d5, 0px 0px 11px 0.75px #0bdaeb;
            transform: translate(0, -50%) rotate(65deg);
        }

        .body-mini {
            width: 1.65rem;
            height: 2.25rem;
            position: relative;
            margin-block-start: 0.19rem;
            border-radius: 47% 53% 45% 55% / 12% 9% 90% 88%;
            background: linear-gradient(to right, white 35%, gray);
        }

        .hand-mini {
            position: absolute;
            left: -0.45rem;
            top: 0.225rem;
            width: 0.6rem;
            height: 1.65rem;
            border-radius: 40%;
            background: linear-gradient(to left, white 15%, gray);
            box-shadow: 1.5px 0px 3.5px rgba(0, 0, 0, 0.18);
            transform: rotateY(55deg) rotateZ(10deg);
        }

        .hand-mini:first-child {
            animation: compensateRotation var(--EVA-ROTATION-DURATION) linear infinite alternate;
        }

        .hand-mini:nth-child(2) {
            left: 82%;
            background: linear-gradient(to right, white 15%, gray);
            transform: rotateY(55deg) rotateZ(-10deg);
            animation: compensateRotationRight var(--EVA-ROTATION-DURATION) linear infinite alternate;
        }

        .scannerThing-mini {
            width: 0;
            height: 0;
            position: absolute;
            left: 60%;
            top: 10%;
            border-top: 45px solid #9bdaeb;
            border-left: 60px solid transparent;
            border-right: 60px solid transparent;
            transform-origin: top left;
            mask: linear-gradient(to right, white, transparent 35%);
            animation: glow 2s cubic-bezier(0.86, 0, 0.07, 1) infinite;
        }

        .scannerOrigin-mini {
            position: absolute;
            width: 6px;
            aspect-ratio: 1;
            border-radius: 50%;
            left: 60%;
            top: 10%;
            background: #9bdaeb;
            box-shadow: inset 0px 0px 3.5px rgba(0, 0, 0, 0.5);
            animation: moveRight var(--EVA-ROTATION-DURATION) linear infinite;
        }

        @keyframes rotateRight {
            from { transform: rotateY(0deg); }
            to { transform: rotateY(25deg); }
        }

        @keyframes moveRight {
            from { transform: translate(-50%, -50%); }
            to { transform: translate(-40%, -50%); }
        }

        @keyframes compensateRotation {
            from { transform: rotateY(55deg) rotateZ(10deg); }
            to { transform: rotatey(30deg) rotateZ(10deg); }
        }

        @keyframes compensateRotationRight {
            from { transform: rotateY(55deg) rotateZ(-10deg); }
            to { transform: rotateY(70deg) rotateZ(-10deg); }
        }

        @keyframes glow {
            from { opacity: 0; }
            20% { opacity: 1; }
            45% { transform: rotate(-25deg); }
            75% { transform: rotate(5deg); }
            100% { opacity: 0; }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1000;
                width: 250px;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            #sidebarToggle.open {
                left: 280px;
            }

            #sidebarToggle {
                top: 20px;
                left: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <!-- BNM Parapharm Header -->
        <div class="brand-header">
            <h1 class="brand-text">bnm parapharm</h1>
        </div>

        <!-- Logo and User Info with Power Button -->
        <div class="logo-section">
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($Role); ?></div>
                </div>
                
                <!-- Power Button for Logout -->
                <button onclick="window.location.href='db/logout.php'" class="power-btn" id="powerBtn" title="Logout">
                    <i class="fas fa-power-off"></i>
                </button>
            </div>
        </div>
        <!-- Sidebar Mode Toggle -->
        <div class="mode-toggle">
            <span class="mode-label">
                <i class="fas fa-compass"></i> Mode
            </span>
            <div class="mode-options">
                <input type="radio" id="sidebarModeManual" name="sidebarMode" value="manual" checked>
                <label for="sidebarModeManual" title="Manual Mode">
                    Manual
                </label>
                <input type="radio" id="sidebarModeAuto" name="sidebarMode" value="auto">
                <label for="sidebarModeAuto" title="Auto Mode">
                    Auto
                </label>
            </div>
        </div>
        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <?php echo renderNavigationMenu($Role, $role_allowed_pages); ?>
        </nav>

        <!-- Bottom Action Icons -->
        <div class="sidebar-bottom-actions">
            <!-- Bot SVG -->
            <div class="sidebar-action-btn" onclick="toggleChatbot()" title="BNM Support Ticket">
                <div class="loader-mini">
                    <div class="modelViewPort-mini">
                        <div class="eva-mini">
                            <div class="head-mini">
                                <div class="eyeChamber-mini">
                                    <div class="eye-mini"></div>
                                    <div class="eye-mini"></div>
                                </div>
                            </div>
                            <div class="body-mini">
                                <div class="hand-mini"></div>
                                <div class="hand-mini"></div>
                                <div class="scannerThing-mini"></div>
                                <div class="scannerOrigin-mini"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <span>Bot</span>
            </div>

            <!-- Search Icon -->
            <?php if (in_array(strval($_SESSION['Role'] ?? ''), ['Developer', 'Admin','Sup Achat', 'Sup Vente'])): ?>
            <div class="sidebar-action-btn" onclick="if(typeof window.openArticlesInline === 'function') { window.openArticlesInline(); } else { console.error('openArticlesInline not found'); }" title="Rechercher Article">
                <i class="fas fa-search"></i>
                <span>Articles</span>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        // Navigation function
        function navigateTo(page) {
            // Map page names to actual file names
            const pageMap = {
                'inv': 'inventory/inv.php',
                'inv_admin': 'inventory/inv_admin.php',
                'editnavbar': 'sidebar/editnavbar.php',
                'mail_dashboard': 'mail/dashboard.php',
                'mail_templates': 'mail/templates.php',
                'mail_contacts': 'mail/contacts.php',
                'send_mail': 'mail/send.php',
                'mail_logs': 'mail/logs.php',
                'mail_settings': 'mail/settings.php'
            };

            const targetPage = pageMap[page] || page;
            window.location.href = targetPage;
        }

        // Logout Panel Functions
        function toggleLogoutPanel() {
            const panel = document.getElementById('logoutPanel');
            if (panel) {
                panel.classList.toggle('open');
                if (panel.classList.contains('open')) {
                    updateSessionTime();
                }
            }
        }

        function logout() {
            // Show loading state on the logout button
            const logoutBtn = document.querySelector('.logout-option.primary');
            const icon = logoutBtn.querySelector('i');
            const span = logoutBtn.querySelector('span');

            logoutBtn.disabled = true;
            icon.className = 'fas fa-spinner fa-spin';
            span.textContent = 'Signing Out...';

            // Add a small delay for better UX
            setTimeout(() => {
                window.location.href = 'db/logout.php';
            }, 800);
        }

        function updateSessionTime() {
            // Calculate session time (this is a simple example - you might want to track actual session start time)
            const now = new Date();
            const sessionStart = new Date(now.getTime() - (30 * 60 * 1000)); // Assume 30 minutes session for demo
            const diff = now - sessionStart;

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

            const sessionTimeElement = document.getElementById('sessionTime');
            if (sessionTimeElement) {
                sessionTimeElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
            }
        }

        // Toggle submenu function
        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const button = submenu.previousElementSibling;
            const chevron = button.querySelector(".chevron");

            submenu.classList.toggle("show");
            chevron.classList.toggle("rotate");
        }

        // Enhanced mobile sidebar toggle with smooth animations
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        function toggleSidebar() {
            const isOpen = sidebar.classList.contains('open');
            const screenWidth = window.innerWidth;

            if (screenWidth <= 768) {
                // Mobile/tablet behavior
                if (isOpen) {
                    sidebar.classList.remove('open');
                    sidebarToggle.classList.remove('open');
                    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    document.body.classList.remove('sidebar-visible');
                    sidebarToggle.style.left = '20px';
                } else {
                    sidebar.classList.add('open');
                    sidebarToggle.classList.add('open');
                    sidebarToggle.innerHTML = '<i class="fas fa-times"></i>';
                    document.body.classList.add('sidebar-visible');
                    sidebarToggle.style.left = '280px';
                }
            } else {
                // Desktop manual mode behavior
                if (sidebar.style.transform === 'translateX(-100%)' || sidebar.style.transform === '') {
                    // Show sidebar
                    sidebar.style.transform = 'translateX(0)';
                    sidebarToggle.innerHTML = '<i class="fas fa-times"></i>';
                    document.body.classList.add('sidebar-visible');
                } else {
                    // Hide sidebar
                    sidebar.style.transform = 'translateX(-100%)';
                    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    document.body.classList.remove('sidebar-visible');
                }
            }
        }

        sidebarToggle.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !sidebarToggle.contains(event.target) &&
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                sidebarToggle.classList.remove('open');
                sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
                document.body.classList.remove('sidebar-visible');
                sidebarToggle.style.left = '20px';
            }
        });

        // Handle window resize for responsive behavior
        window.addEventListener('resize', function() {
            const screenWidth = window.innerWidth;

            // Reset sidebar state on resize if needed
            if (screenWidth > 768) {
                // Desktop mode
                sidebar.classList.remove('open');
                sidebarToggle.classList.remove('open');
                sidebarToggle.style.left = ''; // Reset to CSS default
                if (document.body.classList.contains('sidebar-visible')) {
                    sidebar.style.transform = 'translateX(0)';
                    sidebarToggle.innerHTML = '<i class="fas fa-times"></i>';
                } else {
                    sidebar.style.transform = 'translateX(-100%)';
                    sidebarToggle.innerHTML = '<i class="fas fa-bars"></i>';
                }
            } else {
                // Mobile/tablet mode
                sidebar.style.transform = '';
                if (sidebar.classList.contains('open')) {
                    sidebarToggle.style.left = '280px';
                } else {
                    sidebarToggle.style.left = '20px';
                }
            }
        });

        // Make current page active in sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop().split('.')[0];
            const buttons = document.querySelectorAll('nav button');

            buttons.forEach(button => {
                if (button.getAttribute('onclick')?.includes(currentPage)) {
                    button.classList.add('active');

                    // Open parent submenu if this is a submenu item
                    const submenuItem = button.closest('.submenu');
                    if (submenuItem) {
                        const parentButton = submenuItem.previousElementSibling;
                        submenuItem.classList.add('show');
                        parentButton.querySelector('.chevron').classList.add('rotate');
                    }
                }
            });
        });

        // Sidebar Mode Toggle Logic
        const sidebarModeManual = document.getElementById('sidebarModeManual');
        const sidebarModeAuto = document.getElementById('sidebarModeAuto');

        function setSidebarMode(mode) {
            if (mode === 'auto') {
                sidebar.classList.add('sidebar-auto-hide');
                sidebarToggle.style.display = 'none';
                sidebar.classList.remove('open');
                sidebar.style.transform = 'translateX(-100%)';
                document.body.classList.remove('sidebar-visible');
                document.addEventListener('mousemove', handleSidebarReveal);
                // Add mouseleave event to sidebar to hide it when mouse leaves
                sidebar.addEventListener('mouseleave', handleSidebarAutoHide);
            } else {
                sidebar.classList.remove('sidebar-auto-hide');
                sidebarToggle.style.display = '';
                sidebar.style.transform = 'translateX(0)'; // Show sidebar by default in manual mode
                sidebarToggle.innerHTML = '<i class="fas fa-times"></i>'; // Show close icon
                document.body.classList.add('sidebar-visible'); // Sidebar is visible by default in manual mode
                document.removeEventListener('mousemove', handleSidebarReveal);
                sidebar.removeEventListener('mouseleave', handleSidebarAutoHide);
            }
        }

        function handleSidebarReveal(e) {
            if (e.clientX < 30) {
                sidebar.style.transform = 'translateX(0)';
                document.body.classList.add('sidebar-visible');
            }
        }

        function handleSidebarAutoHide(e) {
            // Only hide if in auto mode and mouse is not over sidebar
            if (sidebar.classList.contains('sidebar-auto-hide')) {
                sidebar.style.transform = 'translateX(-100%)';
                document.body.classList.remove('sidebar-visible');
            }
        }

        sidebarModeManual.addEventListener('change', function() {
            if (this.checked) setSidebarMode('manual');
        });
        sidebarModeAuto.addEventListener('change', function() {
            if (this.checked) setSidebarMode('auto');
        });

        // Set initial mode
        if (sidebarModeAuto.checked) {
            setSidebarMode('auto');
        } else {
            setSidebarMode('manual');
        }

        // Function to toggle chatbot
        function toggleChatbot() {
            if (typeof parent.toggleChatbot === 'function') {
                parent.toggleChatbot();
            } else {
                // Fallback: Try to access chatbot window directly
                const chatbotWindow = parent.document.getElementById('chatbotWindow');
                if (chatbotWindow) {
                    if (chatbotWindow.classList.contains('open')) {
                        chatbotWindow.classList.remove('open');
                    } else {
                        chatbotWindow.classList.add('open');
                    }
                }
            }
        }
    </script>
</body>
</html>