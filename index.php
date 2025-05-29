


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
        <style>
            
            body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', 'Arial', sans-serif;
        background-image: url('background.jpg');

    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-size: cover;
    overflow: hidden;
}


        .subheading {
            color: #666;
            margin-bottom: 20px;
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



       
<form class="form" id="loginForm" method="POST" action="db/login.php">

  <div class="form-header">
      <div class="logo-text">bnm parapharm</div>
      <img src="assets/tab.png" alt="Logo" class="logo">
  </div>

  <?php
  if (isset($_GET['session_expired'])) {
      echo "<p style='color: red; text-align: center;'>Your session has expired. Please log in again.</p>";
  }
  ?>

  <input class="input" type="text" id="username" placeholder="Username" name="username" autocomplete="off" required>
  <div class="error-message" id="usernameError"></div>

  <!-- <div id="lookAnimation"></div> -->
  <div class="password-container">
      <input class="input" type="password" id="password" placeholder="Password" name="password" required>
      <div id="eyeIcon"></div>
      <div class="error-message" id="passwordError"></div>
  </div>

  <div class="content__or-text">
      <span></span>
      <span>OR</span>
      <span></span>
  </div>

  <div class="signup-link">
      <p>Don't have an account? <a href="signup">Sign Up</a></p>
  </div>
  <br>

  <button type="submit" class="login-btn">Log In     </button>
  </form>



<style>
.signup-link {
    text-align: center;
    font-size: 14px;
    color: var(--font-color-sub);
}

.signup-link p {
    margin: 0;
}

.signup-link a {
    color: var(--main-color);
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s, text-decoration 0.3s;
}

.signup-link a:hover {
    color: var(--input-focus);
    text-decoration: underline;
}

.form-header {
    text-align: center;
    width: 100%;
}

.logo {
    max-width: 150px;
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

input:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px #4CAF50;
}
body { font-family: 'Poppins', sans-serif; }
.login-btn {
  background: linear-gradient(to right, #4CAF50, #81C784);
    border: none;
    border-radius: 25px;
    color: white;
    cursor: pointer;
    transition: background 0.3s ease;    box-shadow: 4px 4px var(--main-color);
width: 100px;
height: 50px;
    color: var(--font-color);
    border: 2px solid var(--main-color);
    padding: 5px 10px;
    font-size: 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s, box-shadow 0.3s;
}
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(to bottom, #ffffff 35%, #a6ce8e 50%, #ffffff 65%);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
.login-btn:hover {
  background: linear-gradient(to right, #388E3C, #66BB6A);    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.login-btn:active {
    box-shadow: 0px 0px var(--main-color);
    transform: translate(3px, 3px);}
            /* Password Container */

            
  .password-container {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}



        /* Look animation */
        #lookAnimation {
    width: 50px;
    height: 50px;
    margin: 0 auto -10px auto; /* Center horizontally and adjust bottom */
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
    .logo {
  display: inline-block;
  max-width: 100%; /* optional if you want it responsive */
}
 .error-message {
            color: red;
            font-size: 14px;
        }

        .form {
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);

  width: 350px;
  --input-focus: #2d8cf0;
  --font-color: #323232;
  --font-color-sub: #666;
  --bg-color: #fff;
  --main-color: #323232;
  padding: 20px;
  background: linear-gradient(145deg, #f7f7f7, #e1e1e1); /* Subtle light gradient for depth */
  display: flex;
      background: rgba(255, 255, 255, 0.9);

  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  gap: 20px;
  border-radius: 10px; /* Slightly more rounded corners */
  border: 1px solid var(--main-color);
  box-shadow: 4px 4px 20px rgba(0, 0, 0, 0.1); /* Softer shadow with more depth */
  height: 590px;
  max-width: 100%;
  backdrop-filter: blur(10px); /* Subtle blur effect for depth */
}

.title {
  color: var(--font-color);
  font-weight: 900;
  font-size: 20px;
  margin-bottom: -10px;
}

.title span {
  color: var(--font-color-sub);
  font-weight: 600;
  font-size: 17px;
}

.input {
  width: 320px;
  height: 40px;
  border-radius: 5px;
  border: 2px solid var(--main-color);
  background-color: var(--bg-color);
  box-shadow: 4px 4px var(--main-color);
  font-size: 15px;
  font-weight: 600;
  color: var(--font-color);
  padding: 5px 10px;
  outline: none;
}

.input::placeholder {
  color: var(--font-color-sub);
  opacity: 0.8;
}

.input:focus {
  border: 2px solid var(--input-focus);
}

.login-with {
  display: flex;
  gap: 20px;
}

.button-log {
  cursor: pointer;
  width: 40px;
  height: 40px;
  border-radius: 100%;
  border: 2px solid var(--main-color);
  background-color: var(--bg-color);
  box-shadow: 4px 4px var(--main-color);
  color: var(--font-color);
  font-size: 25px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.icon {
  width: 24px;
  height: 24px;
  fill: var(--main-color);
}




.content__or-text {
    display: flex;
    align-items: center;
    text-transform: uppercase;
    font-size: 13px;
    gap: 10px;
    width: 100%;
    height: 30px; /* Fixed height */
}

.content__or-text span:nth-child(2) {
    white-space: nowrap;
}

.content__or-text span:nth-child(1),
.content__or-text span:nth-child(3) {
    flex-grow: 1;
    height: 1px;
    background-color: #555; /* Line color */
}
/* Existing styles above remain unchanged */

/* Responsive Design */
@media (max-width: 480px) {
  .form {
    width: 90%;
    padding: 15px;
    height:290px;

  }

  .input {
    width: 100%;
  }

  .login-btn {
    width: 100%;
    font-size: 15px;
  }

  .login-with {
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
  }

  .button-log {
    width: 35px;
    height: 35px;
    font-size: 20px;
  }

  .logo-text {
    font-size: 30px;
    text-align: center;
  }

  .title {
    font-size: 18px;
    text-align: center;
  }

  .title span {
    font-size: 16px;
  }

  .signup-link {
    font-size: 13px;
  }

  .content__or-text {
    font-size: 12px;
    gap: 8px;
  }

  #eyeIcon {
    width: 25px;
    height: 25px;
  }

  #lookAnimation {
    width: 40px;
    height: 40px;
  }
}

@media (max-width: 768px) and (min-width: 481px) {
  .form {
    width: 80%;
    height: auto;
  }

  .input {
    width: 100%;
  }

  .login-btn {
    width: 100%;
  }

  .login-with {
    justify-content: center;
    gap: 15px;
  }

  .button-log {
    width: 38px;
    height: 38px;
  }

  .logo-text {
    font-size: 35px;
    text-align: center;
  }

  .title {
    text-align: center;
  }
}

</style>
  
<!-- Rocket animation container (Initially hidden) -->
<!-- Rocket animation container (Initially hidden) -->
<!-- <div id="rocketAnimation" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 500px; height: 500px;"> -->
    


    
<script>
// Select elements
const usernameField = document.getElementById("username");
const passwordField = document.getElementById("password");
const loginButton = document.querySelector(".login-btn");
const usernameError = document.getElementById("usernameError");
const passwordError = document.getElementById("passwordError");

let isCorrectLogin = false;
let originalTop = loginButton.offsetTop;
let originalLeft = loginButton.offsetLeft;

// Function to move the button randomly
const moveButtonRandomly = () => {
    if (!isCorrectLogin) {
        const top = Math.floor(Math.random() * (window.innerHeight - loginButton.offsetHeight));
        const left = Math.floor(Math.random() * (window.innerWidth - loginButton.offsetWidth));

        loginButton.style.position = "absolute";
        loginButton.style.top = `${top}px`;
        loginButton.style.left = `${left}px`;
    }
};

// Check credentials dynamically while typing
const checkCredentials = () => {
    const username = usernameField.value.trim();
    const password = passwordField.value.trim();

    if (username === "" || password === "") {
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "db/check_credentials.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = xhr.responseText.trim();

            if (response === "valid") {
                isCorrectLogin = true;
                loginButton.style.pointerEvents = "auto";
                loginButton.style.top = `${originalTop}px`;
                loginButton.style.left = `${originalLeft}px`;
                loginButton.style.backgroundColor = "green";
                usernameError.style.display = "none";
                passwordError.style.display = "none";
                usernameField.style.border = "2px solid green";
                passwordField.style.border = "2px solid green";
            } else {
                isCorrectLogin = false;
                loginButton.style.backgroundColor = "red";

                if (response.includes("Username")) {
                    usernameError.textContent = response;
                    usernameError.style.display = "block";
                    usernameField.style.border = "2px solid red";
                } else {
                    passwordError.textContent = response;
                    passwordError.style.display = "block";
                    passwordField.style.border = "2px solid red";
                }
            }
        }
    };

    xhr.send(`username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`);
};

// Attach hover event to move the button ONLY when credentials are wrong
loginButton.addEventListener("mouseover", () => {
    if (!isCorrectLogin) {
        moveButtonRandomly();
    }
});


// Move button away only on hover if credentials are incorrect
loginButton.addEventListener("mouseover", () => {
    if (!isCorrectLogin) {
        moveButtonRandomly();
    }
});

// Check credentials while typing
usernameField.addEventListener("input", checkCredentials);
passwordField.addEventListener("input", checkCredentials);



    // Load Lottie animations
    let eyeIcon = lottie.loadAnimation({
        container: document.getElementById('eyeIcon'),
        renderer: 'svg',
        loop: true,
        autoplay: true,
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
