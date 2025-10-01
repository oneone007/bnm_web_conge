<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM Analyse - Analytics Dashboard</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
    <!-- Animated Logo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@800&display=swap" rel="stylesheet">
    <style>
        /* Modern Design System */
        :root {
            /* Fresh Color Palette */
            --primary-color: #6366f1;      /* indigo-500 */
            --primary-hover: #4f46e5;      /* indigo-600 */
            --primary-light: #a5b4fc;      /* indigo-300 */
            --primary-dark: #312e81;       /* indigo-800 */
            
            /* Enhanced Button Colors - Professional Corporate */
            --button-primary: #1e40af;     /* blue-700 - Professional navy */
            --button-secondary: #374151;   /* gray-700 - Sophisticated gray */
            --button-accent: #3b82f6;      /* blue-500 - Bright accent */
            --button-hover: #1d4ed8;       /* blue-800 - Deeper navy */
            --button-hover-secondary: #4b5563; /* gray-600 - Lighter gray */
            --button-glow: rgba(30, 64, 175, 0.3); /* navy with opacity */
            
            /* Accent Colors */
            --accent-color: #f59e0b;        /* amber-500 */
            --accent-hover: #d97706;        /* amber-600 */
            --accent-light: #fbbf24;       /* amber-400 */
            
            /* Modern Neutrals */
            --background-color: #0f172a;   /* slate-900 - Dark background */
            --card-background: #1e293b;    /* slate-800 - Card background */
            --surface-color: #334155;      /* slate-700 - Surface */
            --text-primary: #f8fafc;       /* slate-50 - Light text */
            --text-secondary: #cbd5e1;     /* slate-300 - Secondary text */
            --text-muted: #94a3b8;         /* slate-400 - Muted text */
            --border-color: #475569;       /* slate-600 - Borders */
            --border-light: #334155;       /* slate-700 - Light borders */
            
            /* Status Colors */
            --success-color: #10b981;      /* emerald-500 */
            --error-color: #ef4444;        /* red-500 */
            --warning-color: #f59e0b;      /* amber-500 */
            
            /* Modern Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.3);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.4), 0 2px 4px -2px rgb(0 0 0 / 0.3);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.5), 0 4px 6px -4px rgb(0 0 0 / 0.4);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.6), 0 8px 10px -6px rgb(0 0 0 / 0.5);
            
            /* Modern Radius */
            --radius: 0.75rem;
            --radius-md: 1rem;
            --radius-lg: 1.5rem;
            --radius-xl: 2rem;
        }

        /* Animated Logo */
        .animated-logo-svg {
            width: 100%;
            max-width: 350px;
            height: 80px;
            display: block;
        }
        .animated-logo-text {
            fill: none;
            stroke: var(--primary-light);
            stroke-width: 3px;
            font-size: 58px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-weight: 600;
            stroke-dasharray: 700;
            stroke-dashoffset: 700;
            animation: text-draw 2s forwards 1;
            animation-delay: 0.5s;
        }
        @keyframes text-draw {
            80% {
                fill: transparent;
                stroke-dashoffset: 0;
                stroke-width: 3px;
            }
            100% {
                fill: var(--primary-light);
                stroke-dashoffset: 0;
                stroke-width: 0;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--background-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Modern Dark Background */
        .analytics-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background: 
                radial-gradient(circle at 25% 25%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(245, 158, 11, 0.1) 0%, transparent 50%),
                linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }
        
        .analytics-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                linear-gradient(45deg, transparent 40%, rgba(99, 102, 241, 0.05) 50%, transparent 60%),
                linear-gradient(-45deg, transparent 40%, rgba(245, 158, 11, 0.03) 50%, transparent 60%);
        }

        /* Modern Card Container */
        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
        }

        .login-container {
            background: var(--card-background);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 3rem 2.5rem 2.5rem;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(20px);
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--primary-color));
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        .login-container::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Modern Header Design */
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            border: 3px solid var(--border-color);
        }

        .brand-logo::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color), var(--primary-light));
            animation: rotate 4s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            to { transform: rotate(360deg); }
        }

        .brand-logo img {
            width: 38px;
            height: 38px;
            object-fit: contain;
            z-index: 1;
            filter: brightness(1.2);
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 1.25rem;
            color: var(--accent-color);
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .brand-description {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
            font-weight: 400;
            max-width: 280px;
            margin: 0 auto;
        }

        /* Modern Form Design */
        .login-form {
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.75rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: 0.01em;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem 1rem 3rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 400;
            transition: all 0.3s ease;
            background: var(--surface-color);
            color: var(--text-primary);
            outline: none;
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background: var(--card-background);
            transform: translateY(-1px);
        }

        .form-input.error {
            border-color: var(--error-color);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .form-input.success {
            border-color: var(--success-color);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }

        .input-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.125rem;
            transition: all 0.3s ease;
        }

        .form-input:focus ~ .input-icon {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
        }

        .password-toggle {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: var(--radius);
        }

        .password-toggle:hover {
            color: var(--primary-color);
            background: rgba(99, 102, 241, 0.1);
        }

        /* Error Messages */
        .error-message {
            color: var(--error-color);
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        /* Professional Corporate Button */
        .submit-btn {
            width: 100%;
            background: var(--button-primary);
            color: #ffffff;
            border: 2px solid var(--button-primary);
            padding: 0.875rem 1.5rem;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.025em;
            box-shadow: var(--shadow-lg);
            text-transform: uppercase;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            backdrop-filter: blur(10px);
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
            z-index: 1;
        }

        .submit-btn span, .submit-btn .btn-loading {
            position: relative;
            z-index: 2;
        }

        .submit-btn:hover:not(:disabled) {
            background: var(--button-hover);
            border-color: var(--button-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl), 0 0 20px var(--button-glow);
            color: #ffffff;
        }

        .submit-btn:hover:not(:disabled)::before {
            left: 100%;
        }

        .submit-btn:active:not(:disabled) {
            transform: translateY(0px);
            transition-duration: 0.1s;
        }

        .submit-btn:focus:not(:disabled) {
            outline: none;
            box-shadow: var(--shadow-lg), 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        .submit-btn:disabled {
            background: var(--surface-color);
            border-color: var(--border-color);
            cursor: not-allowed;
            transform: none;
            box-shadow: var(--shadow-sm);
            color: var(--text-muted);
        }

        /* Professional Loading Animation */
        .btn-loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            position: relative;
            z-index: 2;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Ripple Effect */
        .submit-btn {
            position: relative;
        }

        /* Subtle Ripple Effect */
        .submit-btn .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.4s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }

        /* Professional Messages */
        .success-message,
        .session-message {
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: center;
            font-weight: 500;
            border: 1px solid;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .success-message {
            background: rgba(5, 150, 105, 0.05);
            border-color: var(--success-color);
            color: var(--success-color);
            display: none;
        }

        .success-message.show {
            display: flex;
            animation: slideDown 0.3s ease;
        }

        .session-message {
            background: rgba(217, 119, 6, 0.05);
            border-color: var(--warning-color);
            color: var(--warning-color);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Professional Footer */
        .login-footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.75rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-light);
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 0.5rem;
            }

            .login-wrapper {
                max-width: 95%;
            }

            .login-container {
                padding: 1.2rem;
                max-height: 95vh;
            }

            .brand-title {
                font-size: 1.75rem;
            }

            .brand-subtitle {
                font-size: 0.9rem;
            }

            .brand-description {
                font-size: 0.75rem;
            }

            .analytics-background svg {
                display: none;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.25rem;
            }

            .login-container {
                padding: 0.7rem;
                border-radius: 1.2rem;
                max-height: 98vh;
            }

            .brand-logo {
                width: 44px;
                height: 44px;
                margin-bottom: 0.7rem;
            }

            .brand-logo img {
                width: 24px;
                height: 24px;
            }

            .brand-title {
                font-size: 1.25rem;
            }

            .brand-subtitle {
                font-size: 0.8rem;
            }

            .brand-description {
                font-size: 0.68rem;
            }

            .login-header {
                margin-bottom: 1.5rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-input {
                padding: 0.75rem 0.875rem;
                padding-left: 2.5rem;
                font-size: 0.9rem;
            }

            .input-icon {
                left: 0.75rem;
                font-size: 0.9rem;
            }

            .password-toggle {
                right: 0.75rem;
                font-size: 0.85rem;
            }

            .submit-btn {
                padding: 0.875rem;
                font-size: 0.95rem;
            }

            .login-footer {
                font-size: 0.7rem;
                margin-top: 1rem;
                padding-top: 1rem;
            }
        }

        /* Ultra small screens */
        @media (max-height: 600px) {
            .login-container {
                max-height: 98vh;
                padding: 0.7rem;
            }

            .brand-logo {
                width: 32px;
                height: 32px;
                margin-bottom: 0.4rem;
            }

            .brand-logo img {
                width: 18px;
                height: 18px;
            }

            .brand-title {
                font-size: 1rem;
                margin-bottom: 0.18rem;
            }

            .brand-subtitle {
                font-size: 0.7rem;
                margin-bottom: 0.18rem;
            }

            .brand-description {
                font-size: 0.55rem;
            }

            .login-header {
                margin-bottom: 0.7rem;
            }

            .form-group {
                margin-bottom: 0.6rem;
            }

            .login-footer {
                margin-top: 0.5rem;
                padding-top: 0.5rem;
                font-size: 0.55rem;
            }
        }

        /* Smooth animations */
        .login-container {
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Error shake animation */
        .shake {
            animation: shake 0.6s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
            20%, 40%, 60%, 80% { transform: translateX(8px); }
        }

        /* Enhanced Data Visualization Animations */
        @keyframes float-bar {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-10px) rotate(2deg);
            }
        }

        @keyframes float-line {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-15px) scale(1.05);
            }
        }

        @keyframes float-pie {
            0% {
                transform: rotate(0deg) scale(1);
            }
            50% {
                transform: rotate(180deg) scale(1.1);
            }
            100% {
                transform: rotate(360deg) scale(1);
            }
        }

        @keyframes pulse-opacity {
            0%, 100% {
                opacity: 0.15;
            }
            50% {
                opacity: 0.3;
            }
        }

        @keyframes dash {
            0% {
                stroke-dashoffset: 1000;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }

        /* New visualization styles */
        .viz-container {
            position: absolute;
            opacity: 0.12;
            z-index: 0;
            transition: all 0.5s ease;
        }

        .viz-container:hover {
            opacity: 0.2;
            transform: scale(1.05);
        }

        .viz-line-path {
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: dash 8s linear infinite;
        }
    </style>
</head>
<body>
    <!-- Enhanced Data Visualization Background -->
    <div class="analytics-background">
        <!-- Interactive Network Graph -->
        <svg class="viz-container" width="300" height="300" style="top:5%;left:5%;animation: float-line 12s ease-in-out infinite alternate;">
            <defs>
                <linearGradient id="networkGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#3b82f6" />
                    <stop offset="100%" stop-color="#1e40af" />
                </linearGradient>
            </defs>
            <!-- Connections -->
            <line x1="50" y1="50" x2="250" y2="80" stroke="url(#networkGradient)" stroke-width="1.5" opacity="0.6">
                <animate attributeName="opacity" values="0.6;0.2;0.6" dur="4s" repeatCount="indefinite"/>
            </line>
            <line x1="150" y1="30" x2="200" y2="250" stroke="url(#networkGradient)" stroke-width="1.5" opacity="0.5">
                <animate attributeName="opacity" values="0.5;0.1;0.5" dur="5s" repeatCount="indefinite"/>
            </line>
            <line x1="80" y1="200" x2="220" y2="150" stroke="url(#networkGradient)" stroke-width="1.5" opacity="0.4">
                <animate attributeName="opacity" values="0.4;0.8;0.4" dur="6s" repeatCount="indefinite"/>
            </line>
            <!-- Nodes -->
            <circle cx="50" cy="50" r="8" fill="#3b82f6" opacity="0.8">
                <animate attributeName="r" values="8;5;8" dur="3s" repeatCount="indefinite"/>
            </circle>
            <circle cx="250" cy="80" r="6" fill="#06b6d4" opacity="0.7">
                <animate attributeName="r" values="6;3;6" dur="3.5s" repeatCount="indefinite"/>
            </circle>
            <circle cx="150" cy="30" r="7" fill="#1e40af" opacity="0.6">
                <animate attributeName="r" values="7;4;7" dur="4s" repeatCount="indefinite"/>
            </circle>
            <circle cx="200" cy="250" r="9" fill="#3b82f6" opacity="0.9">
                <animate attributeName="r" values="9;6;9" dur="3.2s" repeatCount="indefinite"/>
            </circle>
            <circle cx="80" cy="200" r="5" fill="#06b6d4" opacity="0.7">
                <animate attributeName="r" values="5;2;5" dur="3.8s" repeatCount="indefinite"/>
            </circle>
            <circle cx="220" cy="150" r="7" fill="#1e40af" opacity="0.6">
                <animate attributeName="r" values="7;4;7" dur="4.2s" repeatCount="indefinite"/>
            </circle>
        </svg>

        <!-- Animated Line Chart with Gradient -->
        <svg class="viz-container" width="400" height="150" style="top:15%;right:5%;animation: float-line 10s ease-in-out infinite alternate;">
            <defs>
                <linearGradient id="lineGradient" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="0" y2="100%">
                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.8"/>
                    <stop offset="100%" stop-color="#06b6d4" stop-opacity="0.2"/>
                </linearGradient>
                <linearGradient id="lineStroke" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#1e40af" />
                    <stop offset="50%" stop-color="#3b82f6" />
                    <stop offset="100%" stop-color="#06b6d4" />
                </linearGradient>
            </defs>
            <path class="viz-line-path" d="M10,100 C50,50 100,120 150,70 S250,130 300,80 S350,110 390,60" 
                  fill="none" stroke="url(#lineStroke)" stroke-width="3" stroke-linecap="round"/>
            <path d="M10,100 C50,50 100,120 150,70 S250,130 300,80 S350,110 390,60 L390,150 L10,150 Z" 
                  fill="url(#lineGradient)" opacity="0.6"/>
        </svg>

        <!-- 3D Bar Chart Visualization -->
        <svg class="viz-container" width="250" height="120" style="bottom:15%;left:10%;animation: float-bar 8s ease-in-out infinite alternate;">
            <defs>
                <linearGradient id="barGradient1" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#3b82f6" />
                    <stop offset="100%" stop-color="#1e40af" />
                </linearGradient>
                <linearGradient id="barGradient2" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#06b6d4" />
                    <stop offset="100%" stop-color="#0c8599" />
                </linearGradient>
            </defs>
            <!-- 3D Bars with perspective -->
            <g transform="skewX(-20)">
                <rect x="30" y="40" width="25" height="60" fill="url(#barGradient1)" rx="2">
                    <animate attributeName="height" values="60;40;80;60" dur="5s" repeatCount="indefinite"/>
                    <animate attributeName="y" values="40;60;20;40" dur="5s" repeatCount="indefinite"/>
                </rect>
                <rect x="70" y="60" width="25" height="40" fill="url(#barGradient2)" rx="2">
                    <animate attributeName="height" values="40;60;30;40" dur="4s" repeatCount="indefinite"/>
                    <animate attributeName="y" values="60;40;70;60" dur="4s" repeatCount="indefinite"/>
                </rect>
                <rect x="110" y="30" width="25" height="70" fill="url(#barGradient1)" rx="2">
                    <animate attributeName="height" values="70;50;90;70" dur="6s" repeatCount="indefinite"/>
                    <animate attributeName="y" values="30;50;10;30" dur="6s" repeatCount="indefinite"/>
                </rect>
                <rect x="150" y="50" width="25" height="50" fill="url(#barGradient2)" rx="2">
                    <animate attributeName="height" values="50;70;40;50" dur="5.5s" repeatCount="indefinite"/>
                    <animate attributeName="y" values="50;30;60;50" dur="5.5s" repeatCount="indefinite"/>
                </rect>
            </g>
        </svg>

        <!-- Radial Progress Chart -->
        <svg class="viz-container" width="180" height="180" viewBox="0 0 36 36" style="bottom:5%;right:10%;animation: float-pie 15s ease-in-out infinite alternate;">
            <defs>
                <linearGradient id="radialGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#3b82f6" />
                    <stop offset="100%" stop-color="#06b6d4" />
                </linearGradient>
            </defs>
            <path d="M18 2.0845
                    a 15.9155 15.9155 0 0 1 0 31.831
                    a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none"
                stroke="#e2e8f0"
                stroke-width="2"
                stroke-opacity="0.3"/>
            <path d="M18 2.0845
                    a 15.9155 15.9155 0 0 1 0 31.831
                    a 15.9155 15.9155 0 0 1 0 -31.831"
                fill="none"
                stroke="url(#radialGradient)"
                stroke-width="2"
                stroke-linecap="round"
                stroke-dasharray="75, 100"
                transform="rotate(-90 18 18)">
                <animateTransform attributeName="transform" type="rotate" from="-90 18 18" to="270 18 18" dur="10s" repeatCount="indefinite"/>
            </path>
            <circle cx="18" cy="18" r="6" fill="url(#radialGradient)" opacity="0.8">
                <animate attributeName="r" values="6;4;6" dur="3s" repeatCount="indefinite"/>
            </circle>
        </svg>

        <!-- Scatter Plot Visualization -->
        <svg class="viz-container" width="200" height="200" style="top:60%;left:60%;animation: pulse-opacity 7s ease-in-out infinite alternate;">
            <defs>
                <radialGradient id="scatterGradient1" cx="30%" cy="30%" r="50%">
                    <stop offset="0%" stop-color="#3b82f6" />
                    <stop offset="100%" stop-color="#1e40af" stop-opacity="0.7" />
                </radialGradient>
                <radialGradient id="scatterGradient2" cx="30%" cy="30%" r="50%">
                    <stop offset="0%" stop-color="#06b6d4" />
                    <stop offset="100%" stop-color="#0c8599" stop-opacity="0.7" />
                </radialGradient>
            </defs>
            <!-- Data points -->
            <circle cx="30" cy="50" r="5" fill="url(#scatterGradient1)">
                <animate attributeName="cy" values="50;40;60;50" dur="5s" repeatCount="indefinite"/>
            </circle>
            <circle cx="70" cy="80" r="7" fill="url(#scatterGradient2)">
                <animate attributeName="cy" values="80;70;90;80" dur="4s" repeatCount="indefinite"/>
            </circle>
            <circle cx="110" cy="30" r="6" fill="url(#scatterGradient1)">
                <animate attributeName="cy" values="30;20;40;30" dur="6s" repeatCount="indefinite"/>
            </circle>
            <circle cx="150" cy="60" r="4" fill="url(#scatterGradient2)">
                <animate attributeName="cy" values="60;50;70;60" dur="5.5s" repeatCount="indefinite"/>
            </circle>
            <circle cx="50" cy="120" r="5" fill="url(#scatterGradient1)">
                <animate attributeName="cy" values="120;110;130;120" dur="4.5s" repeatCount="indefinite"/>
            </circle>
            <circle cx="90" cy="150" r="6" fill="url(#scatterGradient2)">
                <animate attributeName="cy" values="150;140;160;150" dur="5s" repeatCount="indefinite"/>
            </circle>
            <circle cx="130" cy="100" r="5" fill="url(#scatterGradient1)">
                <animate attributeName="cy" values="100;90;110;100" dur="6.5s" repeatCount="indefinite"/>
            </circle>
            <circle cx="170" cy="130" r="7" fill="url(#scatterGradient2)">
                <animate attributeName="cy" values="130;120;140;130" dur="4s" repeatCount="indefinite"/>
            </circle>
        </svg>

        <!-- Floating Data Points -->
        <svg width="12" height="12" style="position:absolute;top:20%;left:60%;z-index:0;opacity:0.22;">
            <circle cx="6" cy="6" r="6" fill="#3b82f6">
                <animate attributeName="r" values="6;2;6" dur="2s" repeatCount="indefinite"/>
                <animate attributeName="cy" values="6;3;6" dur="3s" repeatCount="indefinite"/>
            </circle>
        </svg>
        <svg width="10" height="10" style="position:absolute;top:80%;left:80%;z-index:0;opacity:0.18;">
            <circle cx="5" cy="5" r="5" fill="#06b6d4">
                <animate attributeName="r" values="5;1;5" dur="2.5s" repeatCount="indefinite"/>
                <animate attributeName="cx" values="5;8;5" dur="4s" repeatCount="indefinite"/>
            </circle>
        </svg>
        <svg width="14" height="14" style="position:absolute;top:70%;left:20%;z-index:0;opacity:0.18;">
            <circle cx="7" cy="7" r="7" fill="#1e40af">
                <animate attributeName="r" values="7;3;7" dur="2.2s" repeatCount="indefinite"/>
                <animate attributeName="cy" values="7;10;7" dur="3.5s" repeatCount="indefinite"/>
            </circle>
        </svg>
    </div>

    <!-- Centered Login -->
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
            <!-- Removed duplicate animated SVG logo -->
            <div class="brand-logo">
                <img src="assets/tab.png" alt="BNM Logo">
            </div>
                <div class="brand-info">
<h1 class="brand-title" style="font-size:0;line-height:0;padding:0;margin:0;display:flex;justify-content:center;align-items:center;width:100%;">
    <svg class="animated-logo-svg" viewBox="0 0 600 120" width="100%" height="80" style="display:block;margin:0 auto;">
        <text 
            x="50%" y="80" 
            class="animated-logo-text"
            id="animatedLogoText"
            text-anchor="middle"
        >BNM Web</text>
    </svg>
    <span style="position:absolute;left:-9999px;">BNM Web</span>
</h1>
                    <p class="brand-subtitle">BNM Parapharm</p>
                </div>
            </div>
            <!-- Session expired message -->
            <?php if (isset($_GET['session_expired'])): ?>
            <div class="session-message">
                <i class="fas fa-exclamation-triangle"></i>
                Your session has expired. Please sign in again.
            </div>
            <?php endif; ?>

            <!-- Session terminated by admin message -->
            <?php if (isset($_GET['message']) && $_GET['message'] === 'session_terminated'): ?>
            <div class="session-message">
                <i class="fas fa-shield-alt"></i>
                Your session has been terminated . Please sign in again.
            </div>
            <?php endif; ?>

            <!-- Success message -->
            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i>
                Authentication successful. Redirecting...
            </div>

            <!-- Login Form -->
            <form class="login-form" id="loginForm" method="POST" action="db/login.php">
                <!-- Username Field -->
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            placeholder="Enter your username"
                            autocomplete="username"
                            required
                        >
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    <div class="error-message" id="usernameError">
                        <span id="usernameErrorText"></span>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                        <i class="fas fa-lock input-icon"></i>
                        <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                    </div>
                    <div class="error-message" id="passwordError">
                        <span id="passwordErrorText"></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn" id="submitBtn">
                    <span id="btnText">Sign In</span>
                    <div class="btn-loading" id="btnLoading"></div>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>&copy; 2025 BNM Web - Business Intelligence Platform. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        // DOM Elements
        const usernameField = document.getElementById('username');
        const passwordField = document.getElementById('password');
        const usernameError = document.getElementById('usernameError');
        const passwordError = document.getElementById('passwordError');
        const usernameErrorText = document.getElementById('usernameErrorText');
        const passwordErrorText = document.getElementById('passwordErrorText');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnLoading = document.getElementById('btnLoading');
        const passwordToggle = document.getElementById('passwordToggle');
        const successMessage = document.getElementById('successMessage');
        const loginForm = document.getElementById('loginForm');

        // State
        let isCheckingCredentials = false;

        // Password visibility toggle
        passwordToggle.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        });

        // Show/hide error messages
        function showError(element, textElement, message) {
            element.classList.add('show');
            textElement.textContent = message;
            element.parentElement.querySelector('.form-input').classList.add('error');
            element.parentElement.querySelector('.form-input').classList.remove('success');
        }

        function hideError(element) {
            element.classList.remove('show');
            element.parentElement.querySelector('.form-input').classList.remove('error');
        }

        function showSuccess(inputElement) {
            inputElement.classList.add('success');
            inputElement.classList.remove('error');
        }

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Basic client-side validation
        function validateFields() {
            const username = usernameField.value.trim();
            const password = passwordField.value.trim();

            // Reset states
            hideError(usernameError);
            hideError(passwordError);
            successMessage.classList.remove('show');

            let isValid = true;

            // Basic validation - just check if fields are not empty
            if (!username) {
                showError(usernameError, usernameErrorText, 'Username is required');
                isValid = false;
            }

            if (!password) {
                showError(passwordError, passwordErrorText, 'Password is required');
                isValid = false;
            }

            // Enable/disable submit button based on basic validation
            if (username && password) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }

            return isValid;
        }

        // Debounced field validation (client-side only)
        const debouncedValidation = debounce(validateFields, 300);

        // Event listeners for basic validation
        usernameField.addEventListener('input', debouncedValidation);
        passwordField.addEventListener('input', debouncedValidation);

        // Form submission
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Always prevent default to handle validation first

            // Perform basic validation
            if (!validateFields()) {
                submitBtn.classList.add('shake');
                setTimeout(() => submitBtn.classList.remove('shake'), 500);
                return;
            }

            // Add ripple effect
            const ripple = document.createElement('div');
            ripple.className = 'ripple';
            const rect = submitBtn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            submitBtn.appendChild(ripple);

            setTimeout(() => ripple.remove(), 400);

            // Show loading state
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            submitBtn.disabled = true;

            // Submit the form normally (let the server handle authentication)
            setTimeout(() => {
                loginForm.submit();
            }, 200); // Small delay to show loading animation and ripple
        });

        // Input field focus effects
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });

        // Initial state
        submitBtn.disabled = true;

        // Auto-focus username field
        window.addEventListener('load', function() {
            usernameField.focus();
        });
    </script>

</body>
</html>