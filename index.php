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
            /* Enhanced Color Palette - Ultra Modern 2025 */
            --primary-color: #6366f1;      /* indigo-500 */
            --primary-hover: #4f46e5;      /* indigo-600 */
            --primary-light: #a5b4fc;      /* indigo-300 */
            --primary-dark: #312e81;       /* indigo-800 */

            /* Premium Corporate Colors - Glassmorphism Era */
            --button-primary: #0f172a;     /* slate-900 - Deep professional */
            --button-secondary: #1e293b;   /* slate-800 - Sophisticated */
            --button-accent: #06b6d4;      /* cyan-500 - Modern accent */
            --button-hover: #0c1425;       /* darker slate */
            --button-glow: rgba(6, 182, 212, 0.3); /* cyan glow */

            /* Ultra Modern Neutrals - Glassmorphism */
            --background-color: #000000;   /* Pure black - Ultra modern */
            --card-background: rgba(15, 23, 42, 0.95); /* slate-900 with transparency */
            --surface-color: #1e293b;      /* slate-800 */
            --text-primary: #ffffff;       /* Pure white */
            --text-secondary: #cbd5e1;     /* slate-300 */
            --text-muted: #94a3b8;         /* slate-400 */
            --border-color: rgba(51, 65, 85, 0.5); /* slate-700 with transparency */
            --border-light: rgba(71, 85, 105, 0.3); /* slate-600 with transparency */

            /* Status Colors - Enhanced */
            --success-color: #10b981;      /* emerald-500 */
            --error-color: #ef4444;        /* red-500 */
            --warning-color: #f59e0b;      /* amber-500 */
            --info-color: #06b6d4;         /* cyan-500 */

            /* Advanced Shadows - Glassmorphism */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.5);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.6), 0 2px 4px -2px rgba(0, 0, 0, 0.5);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.7), 0 4px 6px -4px rgba(0, 0, 0, 0.6);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.8), 0 8px 10px -6px rgba(0, 0, 0, 0.7);
            --shadow-glow: 0 0 30px rgba(6, 182, 212, 0.2);

            /* Modern Border Radius */
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.5rem;
            --radius-2xl: 2rem;

            /* Glassmorphism Properties */
            --glass-bg: rgba(15, 23, 42, 0.85);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
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
            padding: 1rem;
            position: relative;
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Ultra Modern Background - Glassmorphism Era */
        .analytics-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(6, 182, 212, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(16, 185, 129, 0.08) 0%, transparent 50%),
                linear-gradient(135deg, #000000 0%, #0f172a 25%, #000000 50%, #0f172a 75%, #000000 100%);
            background-size: 400% 400%;
            animation: gradientShift 20s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .analytics-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                linear-gradient(45deg, transparent 40%, rgba(12, 74, 110, 0.08) 50%, transparent 60%),
                linear-gradient(-45deg, transparent 40%, rgba(14, 165, 233, 0.05) 50%, transparent 60%);
        }

        /* Glassmorphism Login Container */
        /* Fixed small centered card */
        .login-wrapper {
            position: fixed;
            z-index: 50;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            max-width: 340px; /* slightly wider compact card */
            padding: 0.35rem;
            pointer-events: auto;
        }

        .login-container {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 0.6rem; /* compact card radius */
            padding: 1rem 1rem 1rem; /* balanced padding */
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.6), var(--glass-shadow);
            min-width: 260px;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            
            background-size: 300% 100%;
            animation: gradientFlow 6s ease infinite;
            border-radius: var(--radius-2xl) var(--radius-2xl) 0 0;
        }

        @keyframes gradientFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .login-container::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.05) 0%, transparent 70%);
            pointer-events: none;
            animation: subtlePulse 8s ease-in-out infinite;
        }

        @keyframes subtlePulse {
            0%, 100% { opacity: 0.05; }
            50% { opacity: 0.1; }
        }

        /* Modern Header Design - Glassmorphism */
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg,
                var(--button-accent),
                #8b5cf6,
                #10b981);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow:
                0 20px 40px rgba(6, 182, 212, 0.3),
                0 0 60px rgba(6, 182, 212, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            border: 3px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .brand-logo::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: linear-gradient(45deg,
                var(--button-accent),
                #8b5cf6,
                #10b981,
                #f59e0b);
            animation: logoRotate 8s linear infinite;
            z-index: -1;
            opacity: 0.6;
        }

        @keyframes logoRotate {
            to { transform: rotate(360deg); }
        }

        .brand-logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            z-index: 1;
            filter: brightness(1.2) drop-shadow(0 2px 8px rgba(255, 255, 255, 0.2));
        }

        .brand-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
            text-shadow: 0 2px 20px rgba(6, 182, 212, 0.3);
            background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-subtitle {
            font-size: 0.85rem;
            color: var(--button-accent);
            font-weight: 600;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            text-shadow: 0 0 20px rgba(6, 182, 212, 0.2);
        }

        .brand-tagline {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
            margin-bottom: 1rem;
            font-style: italic;
            opacity: 0.9;
        }

        .brand-description {
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.6;
            font-weight: 400;
            max-width: 340px;
            margin: 0 auto;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        /* Glassmorphism Form Design */
        .login-form {
            margin-bottom: 2.5rem;
        }

        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.875rem;
            letter-spacing: 0.01em;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .input-wrapper {
            position: relative;
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .input-wrapper:focus-within {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--button-accent);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: none;
            border-radius: 0.6rem;
            font-size: 0.95rem;
            font-weight: 400;
            background: transparent;
            color: var(--text-primary);
            outline: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-input::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .form-input:focus {
            transform: translateY(-1px);
            color: var(--text-primary);
        }

        .form-input.error {
            animation: inputShake 0.5s ease-in-out;
        }

        .form-input.success {
            box-shadow: inset 0 0 0 2px rgba(16, 185, 129, 0.3);
        }

        @keyframes inputShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .input-icon {
            position: absolute;
            left: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.25rem;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .input-wrapper:focus-within .input-icon {
            color: var(--button-accent);
            transform: translateY(-50%) scale(1.1);
        }

        .password-toggle {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: var(--radius);
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--button-accent);
            background: rgba(6, 182, 212, 0.1);
            transform: translateY(-50%) scale(1.05);
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

        /* General Error Message */
        .general-error {
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            text-align: center;
            font-weight: 500;
            border: 1px solid;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            animation: messageSlideIn 0.5s ease-out;
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .general-error.show {
            display: flex;
            animation: messageSlideIn 0.5s ease-out;
        }

        /* Ultra Modern Glassmorphism Button */
        .submit-btn {
            width: 100%;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.6rem 1rem;
            border-radius: 0.6rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.875rem;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.025em;
            box-shadow:
                0 6px 18px rgba(0, 0, 0, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
            text-transform: uppercase;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                transparent,
                rgba(6, 182, 212, 0.2),
                rgba(139, 92, 246, 0.2),
                transparent);
            transition: left 0.6s ease;
            z-index: 1;
        }

        .submit-btn span, .submit-btn .btn-loading {
            position: relative;
            z-index: 2;
        }

        .submit-btn:hover:not(:disabled) {
            background: rgba(15, 23, 42, 0.9);
            border-color: rgba(6, 182, 212, 0.3);
            transform: translateY(-3px) scale(1.02);
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 40px rgba(6, 182, 212, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .submit-btn:hover:not(:disabled)::before {
            left: 100%;
        }

        .submit-btn:active:not(:disabled) {
            transform: translateY(-1px) scale(1.01);
            transition-duration: 0.1s;
        }

        .submit-btn:focus:not(:disabled) {
            outline: none;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 0 0 3px rgba(6, 182, 212, 0.3);
        }

        .submit-btn:disabled {
            background: rgba(51, 65, 85, 0.5);
            border-color: rgba(71, 85, 105, 0.3);
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            color: var(--text-muted);
            backdrop-filter: blur(10px);
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

        /* Glassmorphism Messages */
        .success-message,
        .session-message {
            padding: 0.6rem 0.8rem; /* compact */
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.78rem; /* smaller text */
            text-align: center;
            font-weight: 500;
            border: 1px solid;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 0.4rem; /* tighter gap */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.18);
            animation: messageSlideIn 0.5s ease-out;
        }

        .success-message {
            background: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.22);
            color: #10b981;
        }

        .success-message.show {
            display: flex;
            animation: messageSlideIn 0.5s ease-out, successGlow 2s ease-in-out infinite alternate;
        }

        /* Smaller icon inside success message */
        .success-message i,
        .session-message i {
            font-size: 1rem;
            margin-right: 0.35rem;
            line-height: 1;
        }

        .session-message {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.3);
            color: #f59e0b;
        }

        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes successGlow {
            from { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), 0 0 20px rgba(16, 185, 129, 0.1); }
            to { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), 0 0 30px rgba(16, 185, 129, 0.2); }
        }

        /* Glassmorphism Footer (compact) */
        .login-footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.72rem;
            margin-top: 0.8rem; /* tightened spacing for compact card */
            padding-top: 0.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            font-weight: 500;
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.01);
            padding: 0.6rem 0.8rem;
            border-radius: 0 0 0.6rem 0.6rem;
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .login-footer p {
            margin: 0 0 0.35rem 0;
            opacity: 0.8;
            line-height: 1.2;
        }

        .login-footer p:last-child {
            margin: 0;
            font-size: 0.65rem;
            opacity: 0.6;
            font-style: italic;
        }

        /* Ultra Modern Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 0.5rem;
                background: #000000;
            }

            /* For mobile keep the card fixed but full-width friendly */
            .login-wrapper {
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                max-width: 95%;
                padding: 0.25rem;
            }

            .login-container {
                padding: 1.25rem 1rem 1rem;
                max-height: 92vh;
                border-radius: 0.75rem;
                backdrop-filter: blur(12px);
            }

            .brand-logo {
                width: 80px;
                height: 80px;
                margin-bottom: 1.5rem;
            }

            .brand-logo img {
                width: 40px;
                height: 40px;
            }

            .brand-title {
                font-size: 1.875rem;
            }

            .brand-subtitle {
                font-size: 1rem;
            }

            .brand-description {
                font-size: 0.8rem;
                padding: 0.75rem;
            }

            .login-header {
                margin-bottom: 2rem;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-input {
                padding: 1rem 1.25rem 1rem 3rem;
                font-size: 1rem;
            }

            .input-icon {
                left: 1.25rem;
                font-size: 1.1rem;
            }

            .password-toggle {
                right: 1.25rem;
                font-size: 1rem;
            }

            .submit-btn {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }

            .login-footer {
                margin-top: 2rem;
                padding: 1.25rem 1.5rem;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.25rem;
                background: #000000;
            }

            .login-container {
                padding: 1rem 0.75rem 0.9rem;
                border-radius: 0.5rem;
                backdrop-filter: blur(10px);
                max-height: 98vh;
            }

            .brand-logo {
                width: 70px;
                height: 70px;
                margin-bottom: 1rem;
            }

            .brand-logo img {
                width: 35px;
                height: 35px;
            }

            .brand-title {
                font-size: 1.5rem;
            }

            .brand-subtitle {
                font-size: 0.9rem;
            }

            .brand-description {
                font-size: 0.75rem;
                padding: 0.5rem;
            }

            .login-header {
                margin-bottom: 1.5rem;
            }

            .form-group {
                margin-bottom: 1.25rem;
            }

            .form-input {
                padding: 0.875rem 1rem 0.875rem 2.75rem;
                font-size: 0.95rem;
            }

            .input-icon {
                left: 1rem;
                font-size: 1rem;
            }

            .password-toggle {
                right: 1rem;
                font-size: 0.9rem;
            }

            .submit-btn {
                padding: 0.875rem 1.25rem;
                font-size: 0.95rem;
            }

            .login-footer {
                margin-top: 1.5rem;
                padding: 1rem 1.25rem;
                font-size: 0.7rem;
            }

            .success-message,
            .session-message {
                padding: 1rem 1.25rem;
                font-size: 0.85rem;
            }
        }

        /* Ultra small screens */
        @media (max-height: 600px) {
            .login-container {
                max-height: 98vh;
                padding: 1rem 0.75rem 0.75rem;
            }

            .brand-logo {
                width: 60px;
                height: 60px;
                margin-bottom: 0.75rem;
            }

            .brand-logo img {
                width: 30px;
                height: 30px;
            }

            .brand-title {
                font-size: 1.25rem;
                margin-bottom: 0.25rem;
            }

            .brand-subtitle {
                font-size: 0.8rem;
                margin-bottom: 0.25rem;
            }

            .brand-description {
                font-size: 0.65rem;
                padding: 0.5rem;
            }

            .login-header {
                margin-bottom: 1rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .login-footer {
                margin-top: 1rem;
                padding: 0.75rem 1rem;
                font-size: 0.65rem;
            }
        }

        /* Enhanced Animations */
        .login-container {
            animation: glassmorphismFadeIn 1s ease-out;
        }

        @keyframes glassmorphismFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
                filter: blur(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }

        /* Input content state */
        .input-wrapper.has-content {
            border-color: rgba(6, 182, 212, 0.2);
        }

        .input-wrapper.has-content .input-icon {
            color: var(--button-accent);
        }

        /* Enhanced loading animation */
        .btn-loading {
            display: none;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #ffffff;
            border-radius: 50%;
            animation: modernSpin 1s linear infinite;
            position: relative;
            z-index: 2;
        }

        @keyframes modernSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Modern ripple effect */
        .submit-btn {
            position: relative;
            overflow: hidden;
        }

        .submit-btn .ripple {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.1) 70%, transparent 100%);
            transform: scale(0);
            animation: modernRipple 0.6s ease-out;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes modernRipple {
            to {
                transform: scale(3);
                opacity: 0;
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
                <div class="brand-info">
<h1 class="brand-title" style="font-size:0;line-height:0;padding:0;margin:0;display:flex;justify-content:center;align-items:center;width:100%;">
    <svg class="animated-logo-svg" viewBox="0 0 600 120" width="100%" height="80" style="display:block;margin:0 auto;">
        <text 
            x="50%" y="80" 
            class="animated-logo-text"
            id="animatedLogoText"
            text-anchor="middle"
        >BNM WEB</text>
    </svg>
    <span style="position:absolute;left:-9999px;">BNM WEB</span>
</h1>
                    <p class="brand-subtitle">BNM Parapharm</p>
                    <p class="brand-tagline">Advanced Analytics & Smart Optimization</p>
            
             
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

            <!-- General error message -->
            <div class="error-message general-error" id="generalError">
                <span id="generalErrorText"></span>
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
                <p>&copy; 2025 BNM WEB - Enterprise Business Intelligence Platform</p>
                <!-- <p>Real-time Analytics • Automated Workflows • Smart Optimization • AI-Powered Insights</p> -->
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
        const generalError = document.getElementById('generalError');
        const generalErrorText = document.getElementById('generalErrorText');
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

        // Show/hide general error message
        function showGeneralError(message) {
            // Add icon if it doesn't exist
            if (!generalError.querySelector('.fa-exclamation-triangle')) {
                const icon = document.createElement('i');
                icon.className = 'fas fa-exclamation-triangle';
                generalError.insertBefore(icon, generalErrorText);
            }
            generalError.classList.add('show');
            generalErrorText.textContent = message;
        }

        function hideGeneralError() {
            generalError.classList.remove('show');
            // Remove icon when hiding
            const icon = generalError.querySelector('.fa-exclamation-triangle');
            if (icon) {
                icon.remove();
            }
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
            hideGeneralError();
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

            // Hide any existing messages
            successMessage.classList.remove('show');
            hideGeneralError();
            // Hide error messages
            hideError(usernameError);
            hideError(passwordError);

            // Send AJAX request
            const formData = new FormData(loginForm);

            fetch('db/login.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading state
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;

                if (data.success) {
                    // Show success message
                    successMessage.classList.add('show');

                    // Redirect after showing success message
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    // Show error message
                    if (data.message.includes('password') || data.message.includes('Password')) {
                        showError(passwordError, passwordErrorText, data.message);
                    } else if (data.message.includes('username') || data.message.includes('Username')) {
                        showError(usernameError, usernameErrorText, data.message);
                    } else {
                        // General error - show in password field for simplicity
                        showError(passwordError, passwordErrorText, data.message);
                    }

                    // Shake button on error
                    submitBtn.classList.add('shake');
                    setTimeout(() => submitBtn.classList.remove('shake'), 500);
                }
            })
            .catch(error => {
                // Hide loading state
                btnText.style.display = 'inline-block';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;

                // Show network/system error
                showGeneralError('Network error. Please try again.');

                // Shake button on error
                submitBtn.classList.add('shake');
                setTimeout(() => submitBtn.classList.remove('shake'), 500);

                console.error('Login error:', error);
            });
        });

        // Enhanced input field focus effects with floating labels
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
                // Add subtle glow effect
                this.parentElement.style.boxShadow = '0 0 20px rgba(6, 182, 212, 0.1)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
                this.parentElement.style.boxShadow = '';
            });

            // Add input event for dynamic validation feedback
            input.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.parentElement.classList.add('has-content');
                } else {
                    this.parentElement.classList.remove('has-content');
                }
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