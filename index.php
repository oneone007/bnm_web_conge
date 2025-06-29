


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM Parapharm - Login</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
    <style>
        :root {
            /* Brand Colors - Based on your logo gold */
            --primary-color: #C2A159;
            --primary-hover: #B8975A;
            --primary-light: #D4B76B;
            --primary-dark: #A08B4D;
            
            /* Complementary Colors */
            --secondary-color: #2C3E50;
            --secondary-light: #34495E;
            --accent-color: #E8F4FD;
            --accent-dark: #D1E9F8;
            
            /* Functional Colors */
            --success-color: #27AE60;
            --success-light: #2ECC71;
            --error-color: #E74C3C;
            --error-light: #EC7063;
            --warning-color: #F39C12;
            --warning-light: #F8C471;
            
            /* Neutral Colors */
            --background-color: #F8F9FA;
            --background-gradient-start: #FDFBF7;
            --background-gradient-end: #F5F1EA;
            --card-background: #FFFFFF;
            --text-primary: #2C3E50;
            --text-secondary: #7F8C8D;
            --text-light: #95A5A6;
            --border-color: #E8E8E8;
            --border-light: #F4F4F4;
            
            /* Shadows */
            --shadow-sm: 0 1px 3px rgba(194, 161, 89, 0.1);
            --shadow-md: 0 4px 6px rgba(194, 161, 89, 0.15), 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px rgba(194, 161, 89, 0.2), 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px rgba(194, 161, 89, 0.25), 0 8px 10px rgba(0, 0, 0, 0.1);
            
            /* Border Radius */
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--background-gradient-start) 0%, var(--background-gradient-end) 50%, #F0EDE5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background elements */
        .bg-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .floating-shape {
            position: absolute;
            background: linear-gradient(45deg, rgba(194, 161, 89, 0.1), rgba(194, 161, 89, 0.05));
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            backdrop-filter: blur(1px);
        }

        .floating-shape:nth-child(1) {
            width: 120px;
            height: 120px;
            top: 10%;
            left: 15%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 80px;
            height: 80px;
            top: 25%;
            right: 20%;
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            width: 150px;
            height: 150px;
            bottom: 15%;
            left: 8%;
            animation-delay: 4s;
        }

        .floating-shape:nth-child(4) {
            width: 60px;
            height: 60px;
            bottom: 35%;
            right: 25%;
            animation-delay: 1s;
        }

        .floating-shape:nth-child(5) {
            width: 100px;
            height: 100px;
            top: 50%;
            left: 5%;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg) scale(1);
            }
            33% {
                transform: translateY(-15px) rotate(120deg) scale(1.05);
            }
            66% {
                transform: translateY(-25px) rotate(240deg) scale(0.95);
            }
        }

        /* Login container */
        .login-container {
            background: var(--card-background);
            border-radius: var(--radius-xl);
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(194, 161, 89, 0.1);
            transition: all 0.3s ease;
        }

        .login-container:hover {
            box-shadow: 0 25px 50px rgba(194, 161, 89, 0.3), 0 12px 18px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        /* Header */
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.2rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(194, 161, 89, 0.05), rgba(194, 161, 89, 0.02));
            border-radius: var(--radius-lg);
            border: 1px solid rgba(194, 161, 89, 0.1);
        }

        .logo {
            width: 70px;
            height: 70px;
            border-radius: var(--radius);
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(194, 161, 89, 0.2));
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .brand-name {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: -0.025em;
            text-shadow: 0 2px 4px rgba(194, 161, 89, 0.1);
        }

        .brand-subtitle {
            color: var(--secondary-color);
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 0.25rem;
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 400;
        }

        /* Form */
        .login-form {
            space-y: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            padding-left: 3.5rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--card-background);
            color: var(--text-primary);
            font-weight: 500;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(194, 161, 89, 0.15);
            background: var(--accent-color);
        }

        .form-input.error {
            border-color: var(--error-color);
            box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.15);
            background: rgba(231, 76, 60, 0.02);
        }

        .form-input.success {
            border-color: var(--success-color);
            box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.15);
            background: rgba(39, 174, 96, 0.02);
        }

        .input-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }

        .form-input:focus ~ .input-icon {
            color: var(--primary-color);
        }

        .password-toggle {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: var(--radius);
        }

        .password-toggle:hover {
            color: var(--primary-color);
            background: rgba(194, 161, 89, 0.1);
        }

        /* Error messages */
        .error-message {
            color: var(--error-color);
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.75rem;
            display: none;
            padding: 0.5rem 0.75rem;
            background: rgba(231, 76, 60, 0.05);
            border-radius: var(--radius);
            border-left: 3px solid var(--error-color);
        }

        .error-message.show {
            display: block;
            animation: slideDown 0.3s ease;
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

        /* Remember me section */
        .remember-me {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 2rem 0;
            padding: 1rem;
            background: var(--background-color);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .checkbox {
            width: 1.2rem;
            height: 1.2rem;
            accent-color: var(--primary-color);
            cursor: pointer;
        }

        .checkbox-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            cursor: pointer;
            font-weight: 500;
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.25rem 0;
        }

        .forgot-password:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            padding: 1.1rem;
            border-radius: var(--radius-lg);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-md);
        }

        .submit-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            background: var(--text-light);
            cursor: not-allowed;
            transform: none;
            box-shadow: var(--shadow-sm);
        }

        .btn-loading {
            display: none;
            width: 22px;
            height: 22px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-color), transparent);
        }

        .divider-text {
            padding: 0 1.5rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
            background: var(--card-background);
        }

        /* Sign up link */
        .signup-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-light);
        }

        .signup-link p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .signup-link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        /* Success message */
        .success-message {
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.1), rgba(39, 174, 96, 0.05));
            border: 1px solid rgba(39, 174, 96, 0.2);
            color: var(--success-color);
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            display: none;
            border-left: 4px solid var(--success-color);
        }

        .success-message.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        /* Session expired message */
        .session-message {
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.1), rgba(243, 156, 18, 0.05));
            border: 1px solid rgba(243, 156, 18, 0.2);
            color: var(--warning-color);
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
            border-left: 4px solid var(--warning-color);
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                padding: 2rem 1.5rem;
                margin: 0.5rem;
                max-width: 100%;
            }

            .brand-name {
                font-size: 1.5rem;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .form-input {
                padding: 0.875rem 1rem;
                padding-left: 3rem;
                font-size: 0.95rem;
            }

            .input-icon {
                left: 1rem;
                font-size: 1.1rem;
            }

            .password-toggle {
                right: 1rem;
                font-size: 1.1rem;
            }

            .submit-btn {
                padding: 1rem;
                font-size: 1rem;
            }
        }

        /* Animation for form appearance */
        .login-container {
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Shake animation for errors */
        .shake {
            animation: shake 0.6s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
            20%, 40%, 60%, 80% { transform: translateX(8px); }
        }

        /* Focus ring improvements */
        .form-input:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Better button states */
        .submit-btn:focus-visible {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <!-- Animated background elements -->
    <div class="bg-elements">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="logo-container">
                <img src="assets/tab.png" alt="BNM Logo" class="logo">
                <div>
                    <div class="brand-name">BNM</div>
                    <div class="brand-subtitle">Parapharm</div>
                </div>
            </div>
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Please sign in to your account</p>
        </div>

        <!-- Session expired message -->
        <?php if (isset($_GET['session_expired'])): ?>
        <div class="session-message">
            <i class="fas fa-exclamation-triangle"></i>
            Your session has expired. Please log in again.
        </div>
        <?php endif; ?>

        <!-- Success message -->
        <div class="success-message" id="successMessage">
            <i class="fas fa-check-circle"></i>
            Credentials verified successfully!
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
                    <i class="fas fa-exclamation-circle"></i>
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
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="passwordErrorText"></span>
                </div>
            </div>



            <!-- Submit Button -->
            <button type="submit" class="submit-btn" id="submitBtn">
                <span id="btnText">Sign In</span>
                <div class="btn-loading" id="btnLoading"></div>
            </button>
        </form>

        <!-- Divider -->
        <div class="divider">
            <div class="divider-line"></div>
            <span class="divider-text">OR</span>
            <div class="divider-line"></div>
        </div>

        <!-- Sign Up Link -->
        <div class="signup-link">
            <p>Don't have an account? <a href="signup">Create an account</a></p>
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
        let isValidCredentials = false;
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

        // Check credentials with server
        function checkCredentials() {
            const username = usernameField.value.trim();
            const password = passwordField.value.trim();

            // Reset states
            hideError(usernameError);
            hideError(passwordError);
            successMessage.classList.remove('show');

            if (!username || !password) {
                isValidCredentials = false;
                return;
            }

            if (isCheckingCredentials) return;
            isCheckingCredentials = true;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'db/check_credentials.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    isCheckingCredentials = false;

                    if (xhr.status === 200) {
                        const response = xhr.responseText.trim();

                        if (response === 'valid') {
                            isValidCredentials = true;
                            showSuccess(usernameField);
                            showSuccess(passwordField);
                            successMessage.classList.add('show');
                            submitBtn.disabled = false;
                        } else {
                            isValidCredentials = false;
                            submitBtn.disabled = true;

                            if (response.toLowerCase().includes('username')) {
                                showError(usernameError, usernameErrorText, response);
                                usernameField.classList.add('shake');
                                setTimeout(() => usernameField.classList.remove('shake'), 500);
                            } else {
                                showError(passwordError, passwordErrorText, response);
                                passwordField.classList.add('shake');
                                setTimeout(() => passwordField.classList.remove('shake'), 500);
                            }
                        }
                    } else {
                        isValidCredentials = false;
                        submitBtn.disabled = true;
                        showError(passwordError, passwordErrorText, 'Connection error. Please try again.');
                    }
                }
            };

            xhr.send(`username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`);
        }

        // Debounced credential check
        const debouncedCheck = debounce(checkCredentials, 500);

        // Event listeners
        usernameField.addEventListener('input', debouncedCheck);
        passwordField.addEventListener('input', debouncedCheck);

        // Form submission
        loginForm.addEventListener('submit', function(e) {
            if (!isValidCredentials) {
                e.preventDefault();
                submitBtn.classList.add('shake');
                setTimeout(() => submitBtn.classList.remove('shake'), 500);
                return;
            }

            // Show loading state
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-block';
            submitBtn.disabled = true;
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
