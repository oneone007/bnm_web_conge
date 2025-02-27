

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
        <style>
            
        /* General Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .login-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 350px;
        }
        .login-btn {
    position: absolute;
   
    transform: translate(-50%, -50%);
}


        .subheading {
            color: #666;
            margin-bottom: 20px;
        }
    
        .textbox i { 
            position: absolute; 
            left: -10px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #888; 
        } 
        .textbox {
    position: relative;
    margin-bottom: 20px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center; /* Center everything */
}

.textbox input {
    width: 100%; /* Ensure both fields have the same width */
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    text-align: left; /* Keep text alignment normal */
    padding: 10px 10px 10px 40px; /* Add padding to the left for the icon */ 

}


        /* Password Container */
  .password-container {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}



        /* Look animation */
        #lookAnimation {
            width: 60px;
            height: 60px;
            margin-bottom: -10px; /* Moves it closer to password field */
            display: none;
        }

        /* Eye icon inside password field */
        #eyeIcon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            cursor: pointer;
        }

        .error-message {
            color: red;
            font-size: 14px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        #ramadan-animation {
    width: 200px; /* Increase size */
    height: 200px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto; /* Center horizontally */
}
#rmdn-animation {
    position: fixed;  /* Stay fixed at the top */
    top: 0;
    left: 50%;  /* Center horizontally */
    transform: translateX(-50%); /* Shift back by half width */
    width: 300vw;  /* Full width */
    height: 150px; /* Adjust as needed */
    display: flex;
    justify-content: center;
    align-items: center;
    pointer-events: none; /* Prevent interaction */
    background: transparent;
    z-index: 1000; /* Keep it on top */
}
#ram-animation {
    position: fixed;  /* Stay fixed at the top */
    top: 0;
    left: 50%;  /* Center horizontally */
    transform: translateX(-50%); /* Shift back by half width */
    width: 300vw;  /* Full width */
    height: 150px; /* Adjust as needed */
    display: flex;
    justify-content: center;
    align-items: center;
    pointer-events: none; /* Prevent interaction */
    background: transparent;
    z-index: 1000; /* Keep it on top */
}

#background-frame {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1; /* Send to background */
    border: none;
}


#content {
    position: relative;
    z-index: 1; /* Keep content above background */
    text-align: center;
    color: white;
    font-size: 24px;
    padding: 50px;
}


.login-btn {
    background-color: #007bff; /* Primary blue */
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s, box-shadow 0.3s;
}

.login-btn:hover {
    background-color: #0056b3; /* Darker blue on hover */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.login-btn:active {
    background-color: #004494;
}

@font-face {
    font-family: 'GrrrExtraLight';
    src: url('Grrr-ExtraLight.ttf') format('truetype'); /* Adjust path if needed */
    font-weight: 200; /* Extra Light */
    font-style: normal;
}

.logo-text {
    font-family: 'GrrrExtraLight', sans-serif;
    font-size: 40px;
    font-weight: 200; /* Extra Light */
    color: #C2A159; /* Gold-like color */
    text-transform: lowercase;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    letter-spacing: 1px;
}

body {
    margin: auto;
    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
    overflow: auto;
    background: linear-gradient(315deg, #f8f8f8 3%, #e0e0e0 38%, #cfcfcf 68%, #ffffff 98%);
    animation: gradient 15s ease infinite;
    background-size: 400% 400%;
    background-attachment: fixed;
    color: #333; /* Dark grey for text */
}

@keyframes gradient {
    0% {
        background-position: 0% 0%;
    }
    50% {
        background-position: 100% 100%;
    }
    100% {
        background-position: 0% 0%;
    }
}

.wave {
    background: rgba(0, 0, 0, 0.05); /* Soft grey overlay */
    border-radius: 1000% 1000% 0 0;
    position: fixed;
    width: 200%;
    height: 12em;
    animation: wave 10s -3s linear infinite;
    transform: translate3d(0, 0, 0);
    opacity: 0.5;
    bottom: 0;
    left: 0;
    z-index: -1;
}

.wave:nth-of-type(2) {
    bottom: -1.25em;
    animation: wave 18s linear reverse infinite;
    opacity: 0.4;
}

.wave:nth-of-type(3) {
    bottom: -2.5em;
    animation: wave 20s -1s reverse infinite;
    opacity: 0.3;
}

@keyframes wave {
    2% {
        transform: translateX(1);
    }
    25% {
        transform: translateX(-25%);
    }
    50% {
        transform: translateX(-50%);
    }
    75% {
        transform: translateX(-25%);
    }
    100% {
        transform: translateX(1);
    }
}


    </style>
</head>
<body>
    <div>
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
     </div>
    <div>
        <div id="rmdn-animation"></div>
        <div id="ram-animation"></div>
    </div>
  

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
<script>
var animation = lottie.loadAnimation({
    container: document.getElementById('rmdn-animation'), // Corrected ID
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: 'json_files/rmdn.json',
    rendererSettings: {
        clearCanvas: true,
        preserveAspectRatio: 'xMidYMid meet',
        progressiveLoad: true,
        hideOnTransparent: true
    }
});

var animation = lottie.loadAnimation({
    container: document.getElementById('ram-animation'), // Corrected ID
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: 'json_files/ram.json',
    rendererSettings: {
        clearCanvas: true,
        preserveAspectRatio: 'xMidYMid meet',
        progressiveLoad: true,
        hideOnTransparent: true
    }
});

</script>

<div class="login-container">


        <div class="login-box">
            <!-- <img src="bnm.png" alt="Logo" class="logo"> -->

<div id="ramadan-animation"></div>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
<script>
var animation = lottie.loadAnimation({
    container: document.getElementById('ramadan-animation'),
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: 'json_files/ramadan.json',
    rendererSettings: {
        clearCanvas: true,
        preserveAspectRatio: 'xMidYMid meet',
        progressiveLoad: true,
        hideOnTransparent: true
    }
});

// Slow down the animation (default speed is 1)
animation.setSpeed(0.6); // Adjust to a slower speed (0.6x)

// Change the color to #b5e0f1
animation.addEventListener('DOMLoaded', function () {
    let elements = document.querySelectorAll('#ramadan-animation svg *');
    elements.forEach(el => el.style.fill = "#ffffff");
});

</script>       
<div class="logo-text">bnm parapharm</div>
<?php
if (isset($_GET['session_expired'])) {
    echo "<p style='color: red; text-align: center;'>Your session has expired. Please log in again.</p>";
}
?>


     <p class="subheading">Please enter your credentials to log in.</p>

            <form id="loginForm">
                <div class="textbox">
                    <i class="fas fa-user"></i> <!-- Username icon --> 

                    <input type="text" id="username" placeholder="Username" name="username" autocomplete="off" required>
                    <div class="error-message" id="usernameError"></div>
                </div>

                <div class="textbox password-container">
                       <!-- Look animation (Above Password) -->
                       <div id="lookAnimation"></div>
                    <!-- Password input -->
                    <div class="password-container">
                        <i class="fas fa-lock"></i> <!-- Password icon --> 

                    <input type="password" id="password" placeholder="Password" name="password" required>
                    <div id="eyeIcon"></div> <!-- Eye icon inside input field -->
                    <div class="error-message" id="passwordError"></div>
                </div>
                </div>
                <div class="signup-link">
                    <p>Don't have an account? <a href="signup">Sign Up</a></p>
                    <br>
                </div>

                <div class="error-message" id="errorMessage"></div>
                <button type="submit" class="login-btn">Login</button>
            </form>
          
        </div>
    </div>
<!-- Rocket animation container (Initially hidden) -->
<!-- Rocket animation container (Initially hidden) -->
<!-- <div id="rocketAnimation" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 500px; height: 500px;"> -->
    
</div>


    
<script>
// Select elements
const loginButton = document.querySelector(".login-btn");
const usernameField = document.getElementById("username");
const passwordField = document.getElementById("password");
const usernameError = document.getElementById("usernameError");
const passwordError = document.getElementById("passwordError");
const loginForm = document.getElementById("loginForm");

// Store original button position
const originalTop = loginButton.offsetTop;
const originalLeft = loginButton.offsetLeft;
let isCorrectLogin = false; // Track login correctness

// Function to move the login button randomly
const moveButtonRandomly = () => {
    if (!isCorrectLogin) {
        const top = Math.floor(Math.random() * (window.innerHeight - loginButton.offsetHeight));
        const left = Math.floor(Math.random() * (window.innerWidth - loginButton.offsetWidth));

        loginButton.style.position = "absolute";
        loginButton.style.top = `${top}px`;
        loginButton.style.left = `${left}px`;
    }
};

// Function to check credentials
const checkCredentials = () => {
    const username = usernameField.value.trim();
    const password = passwordField.value.trim();

    fetch("check_credentials.php", {
        method: "POST",
        body: new URLSearchParams({ username, password }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            isCorrectLogin = true; // Stop moving
            loginButton.style.pointerEvents = "auto";
            loginButton.style.top = `${originalTop}px`;
            loginButton.style.left = `${originalLeft}px`;
            loginButton.style.backgroundColor = "green"; // Turn green
            usernameError.style.display = "none";
            passwordError.style.display = "none";
            usernameField.style.border = "2px solid green";
            passwordField.style.border = "2px solid green";
        } else {
            isCorrectLogin = false; // Keep moving on hover
            loginButton.style.backgroundColor = "red"; // Turn red

            if (data.error.includes("Username")) {
                usernameError.textContent = data.error;
                usernameError.style.display = "block";
                usernameField.style.border = "2px solid red";
            } else {
                passwordError.textContent = data.error;
                passwordError.style.display = "block";
                passwordField.style.border = "2px solid red";
            }
        }
    })
    .catch(error => console.error("Error:", error));
};

// Move button away only on hover if credentials are incorrect
loginButton.addEventListener("mouseover", () => {
    if (!isCorrectLogin) {
        moveButtonRandomly();
    }
});

// Prevent form submission and check login
loginForm.addEventListener("submit", function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    fetch("login.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            isCorrectLogin = false; // Keep moving on hover if wrong
            moveButtonRandomly();

            if (data.error.includes("Username")) {
                usernameError.textContent = data.error;
                usernameError.style.display = "block";
                usernameField.style.border = "2px solid red";
            } else {
                passwordError.textContent = data.error;
                passwordError.style.display = "block";
                passwordField.style.border = "2px solid red";
            }
        }
    })
    .catch(error => console.error("Error:", error));
});

// Check credentials while typing (but don't move button)
usernameField.addEventListener("input", checkCredentials);
passwordField.addEventListener("input", checkCredentials);


    // Load Lottie animations
    let eyeIcon = lottie.loadAnimation({
        container: document.getElementById('eyeIcon'),
        renderer: 'svg',
        loop: false,
        autoplay: false,
        path: 'json_files/eye.json' // Replace with correct path
    });
    
    let lookAnimation = lottie.loadAnimation({
        container: document.getElementById('lookAnimation'),
        renderer: 'svg',
        loop: true,
        autoplay: false,
        path: 'json_files/look.json' // Replace with correct path
    });
    
    document.getElementById('eyeIcon').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const lookDiv = document.getElementById('lookAnimation');
    
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            eyeIcon.goToAndPlay(15, true); // Play open-eye frame
            lookDiv.style.display = 'block'; // Show look animation
            lookAnimation.goToAndPlay(0, true);
        } else {
            passwordField.type = 'password';
            eyeIcon.goToAndPlay(0, true); // Play closed-eye frame
            lookDiv.style.display = 'none'; // Hide look animation
        }
    });
    </script>

</body>
</html>
