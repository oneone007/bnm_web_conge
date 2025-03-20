<?php
session_start();

// Set session timeout to 1 hour (3600 seconds)
$inactive_time = 3600;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM"); // Redirect to login if not logged in
    exit();
}

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    // Calculate session lifetime
    $session_lifetime = time() - $_SESSION['last_activity'];

    if ($session_lifetime > $inactive_time) {
        session_unset(); // Unset session variables
        session_destroy(); // Destroy the session
        header("Location: BNM?session_expired=1"); // Redirect to login page with message
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM</title>
    <script src="main.js" defer></script>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="recapvente.css">


</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->
 

    <!-- Dark/Light Mode Toggle Button -->
<!-- Dark/Light Mode Toggle Button -->

  <!-- Dark Mode Toggle (Top Right) -->
<!-- From Uiverse.io by Galahhad --> 

  <!-- Dark Mode Toggle (Top Right) -->
<!-- From Uiverse.io by Galahhad --> 
<div class="theme-switch-wrapper">
  <label class="theme-switch">
    <input type="checkbox" class="theme-switch__checkbox" id="themeToggle">
    <div class="theme-switch__container">
      <div class="theme-switch__clouds"></div>
      <div class="theme-switch__stars-container">
        <!-- Stars SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 144 55" fill="none">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M135.831 3.00688C135.055 3.85027 134.111 4.29946 133 4.35447C134.111 4.40947 135.055 4.85867 135.831 5.71123C136.607 6.55462 136.996 7.56303 136.996 8.72727C136.996 7.95722 137.172 7.25134 137.525 6.59129C137.886 5.93124 138.372 5.39954 138.98 5.00535C139.598 4.60199 140.268 4.39114 141 4.35447C139.88 4.2903 138.936 3.85027 138.16 3.00688C137.384 2.16348 136.996 1.16425 136.996 0C136.996 1.16425 136.607 2.16348 135.831 3.00688ZM31 23.3545C32.1114 23.2995 33.0551 22.8503 33.8313 22.0069C34.6075 21.1635 34.9956 20.1642 34.9956 19C34.9956 20.1642 35.3837 21.1635 36.1599 22.0069C36.9361 22.8503 37.8798 23.2903 39 23.3545C38.2679 23.3911 37.5976 23.602 36.9802 24.0053C36.3716 24.3995 35.8864 24.9312 35.5248 25.5913C35.172 26.2513 34.9956 26.9572 34.9956 27.7273C34.9956 26.563 34.6075 25.5546 33.8313 24.7112C33.0551 23.8587 32.1114 23.4095 31 23.3545Z" fill="currentColor"></path>
        </svg>
      </div>
      <div class="theme-switch__circle-container">
        <div class="theme-switch__sun-moon-container">
          <div class="theme-switch__moon">
            <div class="theme-switch__spot"></div>
            <div class="theme-switch__spot"></div>
            <div class="theme-switch__spot"></div>
          </div>
        </div>
      </div>
    </div>
  </label>
</div>

<!-- CSS to position top-right -->
<style>
.theme-switch-wrapper {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
}

/* Optional: Add cursor pointer */
.theme-switch {
  cursor: pointer;
}
</style>



<style>



.theme-switch {
    position: sticky;
    top: 10px;
    left: 10px;
    padding: 10px;
    z-index: 50;
  --toggle-size: 20px; /* Reduced from 30px */
  
  --container-width: 3.75em;   /* Reduced from 5.625em */
  --container-height: 1.7em;   /* Reduced from 2.5em */
  --container-radius: 4em;     /* Scaled down proportionally */
  
  --container-light-bg: #3D7EAE;
  --container-night-bg: #1D1F2C;
  
  --circle-container-diameter: 2.3em;  /* Reduced from 3.375em */
  --sun-moon-diameter: 1.5em;          /* Reduced from 2.125em */
  
  --sun-bg: #ECCA2F;
  --moon-bg: #C4C9D1;
  --spot-color: #959DB1;
  
  --circle-container-offset: calc((var(--circle-container-diameter) - var(--container-height)) / 2 * -1);
  
  --stars-color: #fff;
  --clouds-color: #F3FDFF;
  --back-clouds-color: #AACADF;
  
  --transition: .5s cubic-bezier(0, -0.02, 0.4, 1.25);
  --circle-transition: .3s cubic-bezier(0, -0.02, 0.35, 1.17);
  
}

.theme-switch, .theme-switch *, .theme-switch *::before, .theme-switch *::after {
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-size: var(--toggle-size);
}

.theme-switch__container {
  width: var(--container-width);
  height: var(--container-height);
  background-color: var(--container-light-bg);
  border-radius: var(--container-radius);
  overflow: hidden;
  cursor: pointer;
  -webkit-box-shadow: 0em -0.062em 0.062em rgba(0, 0, 0, 0.25), 0em 0.062em 0.125em rgba(255, 255, 255, 0.94);
  box-shadow: 0em -0.062em 0.062em rgba(0, 0, 0, 0.25), 0em 0.062em 0.125em rgba(255, 255, 255, 0.94);
  -webkit-transition: var(--transition);
  -o-transition: var(--transition);
  transition: var(--transition);
  position: relative;
}

.theme-switch__container::before {
  content: "";
  position: absolute;
  z-index: 1;
  inset: 0;
  -webkit-box-shadow: 0em 0.05em 0.187em rgba(0, 0, 0, 0.25) inset, 0em 0.05em 0.187em rgba(0, 0, 0, 0.25) inset;
  box-shadow: 0em 0.05em 0.187em rgba(0, 0, 0, 0.25) inset, 0em 0.05em 0.187em rgba(0, 0, 0, 0.25) inset;
  border-radius: var(--container-radius)
}

.theme-switch__checkbox {
  display: none;
}

.theme-switch__circle-container {
  width: var(--circle-container-diameter);
  height: var(--circle-container-diameter);
  background-color: rgba(255, 255, 255, 0.1);
  position: absolute;
  left: var(--circle-container-offset);
  top: var(--circle-container-offset);
  border-radius: var(--container-radius);
  -webkit-box-shadow: inset 0 0 0 3.375em rgba(255, 255, 255, 0.1), inset 0 0 0 3.375em rgba(255, 255, 255, 0.1), 0 0 0 0.625em rgba(255, 255, 255, 0.1), 0 0 0 1.25em rgba(255, 255, 255, 0.1);
  box-shadow: inset 0 0 0 3.375em rgba(255, 255, 255, 0.1), inset 0 0 0 3.375em rgba(255, 255, 255, 0.1), 0 0 0 0.625em rgba(255, 255, 255, 0.1), 0 0 0 1.25em rgba(255, 255, 255, 0.1);
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-transition: var(--circle-transition);
  -o-transition: var(--circle-transition);
  transition: var(--circle-transition);
  pointer-events: none;
}

.theme-switch__sun-moon-container {
  pointer-events: auto;
  position: relative;
  z-index: 2;
  width: var(--sun-moon-diameter);
  height: var(--sun-moon-diameter);
  margin: auto;
  border-radius: var(--container-radius);
  background-color: var(--sun-bg);
  -webkit-box-shadow: 0.062em 0.062em 0.062em 0em rgba(254, 255, 239, 0.61) inset, 0em -0.062em 0.062em 0em #a1872a inset;
  box-shadow: 0.062em 0.062em 0.062em 0em rgba(254, 255, 239, 0.61) inset, 0em -0.062em 0.062em 0em #a1872a inset;
  -webkit-filter: drop-shadow(0.062em 0.125em 0.125em rgba(0, 0, 0, 0.25)) drop-shadow(0em 0.062em 0.125em rgba(0, 0, 0, 0.25));
  filter: drop-shadow(0.062em 0.125em 0.125em rgba(0, 0, 0, 0.25)) drop-shadow(0em 0.062em 0.125em rgba(0, 0, 0, 0.25));
  overflow: hidden;
  -webkit-transition: var(--transition);
  -o-transition: var(--transition);
  transition: var(--transition);
}

.theme-switch__moon {
  -webkit-transform: translateX(100%);
  -ms-transform: translateX(100%);
  transform: translateX(100%);
  width: 100%;
  height: 100%;
  background-color: var(--moon-bg);
  border-radius: inherit;
  -webkit-box-shadow: 0.062em 0.062em 0.062em 0em rgba(254, 255, 239, 0.61) inset, 0em -0.062em 0.062em 0em #969696 inset;
  box-shadow: 0.062em 0.062em 0.062em 0em rgba(254, 255, 239, 0.61) inset, 0em -0.062em 0.062em 0em #969696 inset;
  -webkit-transition: var(--transition);
  -o-transition: var(--transition);
  transition: var(--transition);
  position: relative;
}

.theme-switch__spot {
  position: absolute;
  top: 0.75em;
  left: 0.312em;
  width: 0.75em;
  height: 0.75em;
  border-radius: var(--container-radius);
  background-color: var(--spot-color);
  -webkit-box-shadow: 0em 0.0312em 0.062em rgba(0, 0, 0, 0.25) inset;
  box-shadow: 0em 0.0312em 0.062em rgba(0, 0, 0, 0.25) inset;
}

.theme-switch__spot:nth-of-type(2) {
  width: 0.375em;
  height: 0.375em;
  top: 0.937em;
  left: 1.375em;
}

.theme-switch__spot:nth-last-of-type(3) {
  width: 0.25em;
  height: 0.25em;
  top: 0.312em;
  left: 0.812em;
}

.theme-switch__clouds {
  width: 1.25em;
  height: 1.25em;
  background-color: var(--clouds-color);
  border-radius: var(--container-radius);
  position: absolute;
  bottom: -0.625em;
  left: 0.312em;
  -webkit-box-shadow: 0.937em 0.312em var(--clouds-color), -0.312em -0.312em var(--back-clouds-color), 1.437em 0.375em var(--clouds-color), 0.5em -0.125em var(--back-clouds-color), 2.187em 0 var(--clouds-color), 1.25em -0.062em var(--back-clouds-color), 2.937em 0.312em var(--clouds-color), 2em -0.312em var(--back-clouds-color), 3.625em -0.062em var(--clouds-color), 2.625em 0em var(--back-clouds-color), 4.5em -0.312em var(--clouds-color), 3.375em -0.437em var(--back-clouds-color), 4.625em -1.75em 0 0.437em var(--clouds-color), 4em -0.625em var(--back-clouds-color), 4.125em -2.125em 0 0.437em var(--back-clouds-color);
  box-shadow: 0.937em 0.312em var(--clouds-color), -0.312em -0.312em var(--back-clouds-color), 1.437em 0.375em var(--clouds-color), 0.5em -0.125em var(--back-clouds-color), 2.187em 0 var(--clouds-color), 1.25em -0.062em var(--back-clouds-color), 2.937em 0.312em var(--clouds-color), 2em -0.312em var(--back-clouds-color), 3.625em -0.062em var(--clouds-color), 2.625em 0em var(--back-clouds-color), 4.5em -0.312em var(--clouds-color), 3.375em -0.437em var(--back-clouds-color), 4.625em -1.75em 0 0.437em var(--clouds-color), 4em -0.625em var(--back-clouds-color), 4.125em -2.125em 0 0.437em var(--back-clouds-color);
  -webkit-transition: 0.5s cubic-bezier(0, -0.02, 0.4, 1.25);
  -o-transition: 0.5s cubic-bezier(0, -0.02, 0.4, 1.25);
  transition: 0.5s cubic-bezier(0, -0.02, 0.4, 1.25);
}

.theme-switch__stars-container {
  position: absolute;
  color: var(--stars-color);
  top: -100%;
  left: 0.312em;
  width: 2.75em;
  height: auto;
  -webkit-transition: var(--transition);
  -o-transition: var(--transition);
  transition: var(--transition);
}

/* actions */

.theme-switch__checkbox:checked + .theme-switch__container {
  background-color: var(--container-night-bg);
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__circle-container {
  left: calc(100% - var(--circle-container-offset) - var(--circle-container-diameter));
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__circle-container:hover {
  left: calc(100% - var(--circle-container-offset) - var(--circle-container-diameter) - 0.187em)
}

.theme-switch__circle-container:hover {
  left: calc(var(--circle-container-offset) + 0.187em);
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__moon {
  -webkit-transform: translate(0);
  -ms-transform: translate(0);
  transform: translate(0);
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__clouds {
  bottom: -4.062em;
}

.theme-switch__checkbox:checked + .theme-switch__container .theme-switch__stars-container {
  top: 50%;
  -webkit-transform: translateY(-50%);
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
}
</style>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script>
        lottie.loadAnimation({
            container: document.getElementById("lottieContainer"),
            renderer: "svg",
            loop: true,
            autoplay: true,
            path: "json_files/r.json" // Replace with actual path to your .rjson file
        });
    </script>

<!-- Sidebar -->
<div id="sidebar-container"></div>

<script>
    // Fetch sidebar content dynamically
 fetch("side")
    .then(response => response.text())
    .then(html => {
        let container = document.getElementById("sidebar-container");
        let tempDiv = document.createElement("div");
        tempDiv.innerHTML = html;

        // Insert sidebar content into the page
        container.innerHTML = tempDiv.innerHTML;

        // Reattach event listeners for submenu toggles (Products, Recaps)
        const productsToggle = document.getElementById("products-toggle");
        if (productsToggle) {
            productsToggle.addEventListener("click", function () {
                let submenu = document.getElementById("products-submenu");
                submenu.classList.toggle("hidden");
            });
        }

        const recapsToggle = document.getElementById("recaps-toggle");
        if (recapsToggle) {
            recapsToggle.addEventListener("click", function () {
                let submenu = document.getElementById("recaps-submenu");
                submenu.classList.toggle("hidden");
            });
        }

        // Initialize Lottie animation after sidebar is inserted
        const ramAnimation = document.getElementById('ram-animation');
        if (ramAnimation) {
            lottie.loadAnimation({
                container: ramAnimation,
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
        }

        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.content');

        if (sidebarToggle && sidebar && content) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('sidebar-hidden');
                content.classList.toggle('content-full');

                // Adjust button position when sidebar is hidden or shown
                if (sidebar.classList.contains('sidebar-hidden')) {
                    sidebarToggle.style.left = '10px';  // Sidebar hidden
                } else {
                    sidebarToggle.style.left = '260px'; // Sidebar visible
                }
            });
        } else {
            console.error("Sidebar or Toggle Button not found!");
        }

        // Auto-hide sidebar when not hovered
        document.addEventListener('mousemove', (event) => {
            if (event.clientX < 50) {  // Mouse near the left edge (50px)
                sidebar.classList.remove('sidebar-hidden');
                content.classList.remove('content-full');
            }
        });

        // Hide sidebar when the mouse leaves it
        sidebar.addEventListener('mouseleave', () => {
            sidebar.classList.add('sidebar-hidden');
            content.classList.add('content-full');
        });

    })
    .catch(error => console.error("Error loading sidebar:", error));

</script>




    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">


        <!-- Filters -->


        <br>



        <!-- Search Fields -->
<!-- Search Fields -->
<!-- Search Fields -->



        <br>
        <!-- Date Inputs -->
        <div class="date-container flex space-x-4 items-center">
    <div class="flex items-center space-x-2">
        <label for="start-date">Begin Date:</label>
        <input type="date" id="start-date" class="border rounded px-2 py-1">
    </div>

    <div class="flex items-center space-x-2">
        <label for="end-date">End Date:</label>
        <input type="date" id="end-date" class="border rounded px-2 py-1">
    </div>

    <!-- Refresh Button with Icon -->
    <button id="refresh-btn" class="p-3 bg-white text-blue-500 rounded-full shadow-lg hover:shadow-xl border border-blue-500 transition duration-200 flex items-center justify-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
    </svg>
</button>

</div>


        <br>

        <!-- <button id="downloadExcel_totalrecap"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Total Recap Download</span>
        </button> -->

        <div class="container">
  <button id="downloadExcel_totalrecap" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
</div>


<style>
.container {
  padding: 0;
  box-sizing: border-box;
  display: flex;
  justify-content: center;
  width: 100%;
  margin: 0 auto;
}


.container * {
  border: none;
  outline: none;
}

.button {
  display: flex;
  align-items: center;
  position: relative;
  justify-content: end;
  cursor: pointer;
  width: 200px;
  height: 65px;
  border-radius: 13px;
  font-size: 18px;
  font-weight: 500;
  background-color: #f9fbf9;
  border: 3px solid #b7b8b7;
  -webkit-box-shadow: 0px 10px 24px 0px rgba(214, 215, 214, 1);
  -moz-box-shadow: 0px 10px 24px 0px rgba(214, 215, 214, 1);
  box-shadow: 0px 10px 24px 0px rgba(214, 215, 214, 1);
  overflow: hidden;
  padding: 0px 13px;
  border-top-color: #d3d3d3;
  border-bottom-color: #7e7f7e;
  transition: all 0.3s ease;
}

.button .icon {
  aspect-ratio: 1/1;
  width: 25px;
  z-index: 10;
  transition: 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  position: absolute;
  top: 50%;
  transform: translate(-50%, -50%);
  left: 30px;
}

.button .text {
  z-index: 10;
  font-weight: 600;
  display: flex;
  align-items: center;
}
.button .text .tab {
  margin: 0px 2px;
}
.button .text span {
  transition: 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.button::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 190px;
  height: 52px;
  background-color: #fff;
  border-radius: 50px;
  -webkit-box-shadow: inset 0px 10px 50px -40px rgba(66, 68, 90, 1);
  -moz-box-shadow: inset 0px 10px 50px -40px rgba(66, 68, 90, 1);
  box-shadow: inset 0px 10px 50px -40px rgba(66, 68, 90, 1);
  transition: all 0.3s ease;
}

.button:hover::before {
  width: 200px;
  height: 64px;
  border-radius: 13px;
  -webkit-box-shadow: inset 0px -10px 50px -40px rgba(66, 68, 90, 1);
  -moz-box-shadow: inset 0px -10px 50px -40px rgba(66, 68, 90, 1);
  box-shadow: inset 0px -10px 50px -40px rgba(66, 68, 90, 1);
}

.button:hover .text span {
  transform: translateY(80px);
  opacity: 0;
}

.button:hover .icon {
  width: 35px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(45deg);
}

.button:active {
  transform: translateY(5px);
}
.download-wrapper {
    display: flex;
  justify-content: center;
  gap: 50px; /* Adjust the gap between the two sections */
  margin-bottom: 20px; /* Adds space between buttons and tables */

}

</style>

        <br>
        
        <!-- Table -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="overflow-x-auto">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2"> Total Recap</h2>

                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">Date</th>
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">CHIFFRE</th>
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">QTY</th>
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">MARGE</th>
                            <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">POURCENTAGE</th>
                        </tr>
                    </thead>
                    <tbody id="recap-table" class="dark:bg-gray-800">
                        <tr id="loading-row">
                            <td colspan="5" class="text-center p-4">
                                <div id="lottie-container" style="width: 290px; height: 200px; margin: auto;"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>






     

        <!-- second table remise aauto  -->


        <!-- Pagination -->
        <div class="mt-4 flex justify-center space-x-2" id="pagination"></div>
        
        <div class="download-wrapper">

            <!-- <button id="download-fournisseur"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Fournisseur Download </span>
            </button> -->
            <button id="download-fournisseur" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>  <button id="download-product-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
             <!-- <button id="download-product-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Product Download</span>
            </button> -->
        </div>

   
     
        <div class="table-wrapper">
            <!-- First Table -->
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">RECAP PAR FOURNISSEUR</h2>
                    <div>
                        <input type="text" id="recap_fournisseur" placeholder="Search..." class="p-2 border border-gray-300 rounded">
                    </div>
                </div>
        
                <div class="overflow-x-auto">


                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="FOURNISSEUR" onclick="sortrecapTable('FOURNISSEUR')"
                                    class="border px-4 py-2">Fournisseur</th>
                                <th data-column="Total" onclick="sortrecapTable('Total')" class="border px-4 py-2">Total
                                </th>
                                <th data-column="QTy" onclick="sortrecapTable('QTy')" class="border px-4 py-2">
                                    QTy</th>
                                <th data-column="Marge" onclick="sortrecapTable('Marge')" class="border px-4 py-2">
                                    Marge</th>
                               
                            </tr>
                        </thead>
                        <tbody id="recap-frnsr-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="5" class="text-center p-4">
                                    <div id="lottie-container-d" style="width: 290px; height: 200px; margin: auto;">
                                    </div>
                                </td>
                            </tr>


                        </tbody>
                    </table>


                </div>
                <!-- Pagination for First Table -->
            </div>


            <!-- Second Table -->

            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                   
                    <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                        <h2 class="text-lg font-semibold dark:text-black">RECAP PAR PRODUIT</h2>
                        <div>
                            <input type="text" id="recap_product" placeholder="Search..." class="p-2 border border-gray-300 rounded">
                        </div>
                    </div>
                <div class="overflow-x-auto">


                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="PRODUCT" onclick="sortrecpproductTable('PRODUCT')"
                                    class="border px-4 py-2">
                                    Product</th>
                                <th data-column="Total" onclick="sortrecpproductTable('Total')"
                                    class="border px-4 py-2">Total
                                </th>
                                <th data-column="QTY" onclick="sortrecpproductTable('QTY')" class="border px-4 py-2">QTY
                                </th>
                                <th data-column="Marge" onclick="sortrecpproductTable('Marge')"
                                    class="border px-4 py-2">Marge
                                </th>
                            </tr>
                        </thead>
                        <tbody id="recap-prdct-table" class="dark:bg-gray-800"></tbody>
                        <tr id="loading-row">
                            <td colspan="5" class="text-center p-4">
                                <div id="lottie-d" style="width: 290px; height: 200px; margin: auto;"></div>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- Pagination for Second Table -->
            </div>
        </div>
      
        <div class="download-wrapper">

            <!-- <button id="download-zone-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Zone Download </span> -->


                <button id="download-zone-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>  <button id="download-client-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
                
            <!-- </button> <button id="download-client-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Client Download</span>
            </button> -->
        </div>

        <div class="table-wrapper">
            <!-- First Table: RECAP PAR ZONE -->
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
              

                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">RECAP PAR ZONE</h2>
                    <img id="generate-chart-zone"
                    src="assets/chrt.png"
                    alt="chart Icon"
                    class="w-6 h-6 cursor-pointer transform hover:scale-105 transition-all duration-300 ease-in-out"
                    >
                    <div>
                     
                        <input type="text" id="recap_zone" placeholder="Search..." class="p-2 border border-gray-300 rounded">
                    </div>
                </div>
            
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="ZONE" onclick="sortrecapzone('ZONE')" class="border px-4 py-2">NAME</th>
                                <th data-column="Total" onclick="sortrecapzone('Total')" class="border px-4 py-2">Total</th>
                                <th data-column="QTy" onclick="sortrecapzone('QTy')" class="border px-4 py-2">QTy</th>
                                <th data-column="Marge" onclick="sortrecapzone('Marge')" class="border px-4 py-2">Marge</th>
                            </tr>
                        </thead>
                        <tbody id="recap-zone-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="4" class="text-center p-4">
                                    <div id="zone" style="width: 290px; height: 200px; margin: auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            

            <!-- Second Table: RECAP CLIENT -->
            <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">RECAP CLIENT</h2>
                    <div>
                        <input type="text" id="recap_client" placeholder="Search..." class="p-2 border border-gray-300 rounded">
                    </div>
                </div>  
                              <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="CLIENT" onclick="sortrecpclienttTable('CLIENT')"
                                    class="border px-4 py-2">NAME</th>
                                <th data-column="TOTAL" onclick="sortrecpclienttTable('TOTAL')"
                                    class="border px-4 py-2">Total</th>
                                <th data-column="QTY" onclick="sortrecpclienttTable('QTY')" class="border px-4 py-2">QTy
                                </th>
                                <th data-column="MARGE" onclick="sortrecpclienttTable('MARGE')"
                                    class="border px-4 py-2">Marge</th>
                            </tr>
                        </thead>
                        <tbody id="recap-client-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="4" class="text-center p-4">
                                    <div id="client" style="width: 290px; height: 200px; margin: auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
     
        <div class="download-wrapper">

            <!-- <button id="download-Opérateur-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Opérateur Download </span>
             -->

                <button id="download-Opérateur-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>  <button id="download-BCCB-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>

                
            <!-- </button> <button id="download-BCCB-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>BCCB Download</span>
            </button> -->
        </div>
    
        <div class="table-wrapper flex flex-wrap gap-6">
            <!-- First Table: RECAP PAR OPÉRATEUR -->
            <div class="table-container flex-1 min-w-[400px] rounded-lg bg-white shadow-md dark:bg-gray-800">
            
                

                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">RECAP PAR OPÉRATEUR</h2>
                    <img id="generate-chart-operateur"
                    src="assets/chrt.png"
                    alt="chart Icon"
                    class="w-6 h-6 cursor-pointer transform hover:scale-105 transition-all duration-300 ease-in-out"
                    >
                    <div>
                     
                        <input type="text" id="recap_operateur" placeholder="Search..." class="p-2 border border-gray-300 rounded">
                    </div>
                </div>

            
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="OPERATEUR" onclick="sortRecapOperator('OPERATEUR')" class="border px-4 py-2">Opérateur</th>
                                <th data-column="TOTAL" onclick="sortRecapOperator('TOTAL')" class="border px-4 py-2">Total</th>
                                <th data-column="QTY" onclick="sortRecapOperator('QTY')" class="border px-4 py-2">QTy</th>
                                <th data-column="MARGE" onclick="sortRecapOperator('MARGE')" class="border px-4 py-2">Marge</th>
                            </tr>
                        </thead>
                        <tbody id="recap-operator-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="4" class="text-center p-4">
                                    <div id="operator" style="width: 290px; height: 200px; margin: auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            

            <!-- Second Table: RECAP PAR BCCB -->
            <div class="table-container flex-1 min-w-[400px] rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="flex justify-between items-center p-4"> <!-- Flex container for title and search -->
                    <h2 class="text-lg font-semibold dark:text-black">BCCB CLIENT</h2>
                    <div>
                        <input type="text" id="recap_bccbclient" placeholder="Search..." class="p-2 border border-gray-300 rounded">
                    </div>
                </div>  
                <div class="overflow-x-auto">
              
  <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                        <thead>
                            <tr class="table-header dark:bg-gray-700">
                                <th data-column="DOCUMENTNO" onclick="sortRecapBccb('DOCUMENTNO')"
                                    class="border px-4 py-2">Document No</th>
                                <th data-column="DATEORDERED" onclick="sortRecapBccb('DATEORDERED')"
                                    class="border px-4 py-2">Date Order</th>
                                <th data-column="GRANDTOTAL" onclick="sortRecapBccb('GRANDTOTAL')"
                                    class="border px-4 py-2">Grand Total</th>
                                <th data-column="MARGE" onclick="sortRecapBccb('MARGE')"
                                    class="border px-4 py-2">Marge (%)</th>
                            </tr>
                        </thead>
                        <tbody id="recap-bccb-table" class="dark:bg-gray-800">
                            <tr id="loading-row">
                                <td colspan="4" class="text-center p-4">
                                    <div id="bccb" style="width: 290px; height: 200px; margin: auto;"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

<br>
<!-- <button id="download-bccb-product-excel"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>BCCB Product Recap Download</span>
        </button> -->
        <div class="container">
  <button id="download-bccb-product-excel" class="button">
    <img src="assets/excel.png" alt="Excel Icon" class="icon" style="width: 24px; height: 24px;" />
    <p class="text">
      <span style="transition-duration: 100ms">D</span>
      <span style="transition-duration: 150ms">o</span>
      <span style="transition-duration: 200ms">w</span>
      <span style="transition-duration: 250ms">n</span>
      <span style="transition-duration: 350ms">l</span>
      <span style="transition-duration: 400ms">o</span>
      <span style="transition-duration: 450ms">a</span>
      <span style="transition-duration: 500ms">d</span>
      <span class="tab"></span>
      <span style="transition-duration: 550ms">E</span>
      <span style="transition-duration: 600ms">x</span>
      <span style="transition-duration: 650ms">c</span>
      <span style="transition-duration: 700ms">e</span>
      <span style="transition-duration: 750ms">l</span>
    </p>
  </button>
</div>
        <div id="bccb-product-container" class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800" style="display: none;">
    <div class="overflow-x-auto">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">BCCB Product Recap</h2>
        <table class="min-w-full border-collapse text-sm text-left dark:text-white">
            <thead>
                <tr class="table-header dark:bg-gray-700">
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">PRODUCT</th>
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">QTY</th>
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">REMISE</th>
                    <th class="border border-gray-300 px-4 py-2 dark:border-gray-600">MARGE</th>
                </tr>
            </thead>
            <tbody id="recap-bccb-product-table" class="dark:bg-gray-800">
                <tr id="loading-row">
                    <td colspan="4" class="text-center p-4">
                        <div id="lottie-container" style="width: 290px; height: 200px; margin: auto;"></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>




        
        <!-- Chart container -->
        <div style="width: 80%; margin: auto;">
            <canvas id="allcharts" style="width: 100%; height: 400px;"></canvas>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
     


        <br><br><br> <br>
        <script>



            // Define an array of element IDs and their corresponding JSON file paths
            const lottieElements = [
                { id: "lottie-container", path: "json_files/date.json" },
                { id: "lottie-container-d", path: "json_files/l.json" },
                { id: "lottie-d", path: "json_files/l.json" },
                { id: "bccb", path: "json_files/l.json" },
                { id: "operator", path: "json_files/l.json" },
                { id: "zone", path: "json_files/l.json" },
                { id: "client", path: "json_files/l.json" }
            ];

            // Loop through each element and initialize Lottie animation
            lottieElements.forEach(({ id, path }) => {
                const container = document.getElementById(id);
                if (container) {
                    lottie.loadAnimation({
                        container: container,
                        renderer: "svg",
                        loop: true,
                        autoplay: true,
                        path: path
                    });
                }
            });

            // Ensure dates clear on refresh
            window.onload = () => {
    // Clear all inputs except date fields on page load
    document.getElementById("recap_fournisseur").value = "";
    document.getElementById("recap_product").value = "";
    document.getElementById("recap_zone").value = "";
    document.getElementById("recap_client").value = "";
    document.getElementById("recap_operateur").value = "";
    document.getElementById("recap_bccbclient").value = "";

    const startDateInput = document.getElementById("start-date");
    const endDateInput = document.getElementById("end-date");

    // Get today's date in YYYY-MM-DD format
    const today = new Date().toISOString().split("T")[0];

    function updateEndDate() {
        if (!endDateInput.value || new Date(endDateInput.value) < new Date(startDateInput.value)) {
            endDateInput.value = today;
        }

        // Trigger events
        endDateInput.dispatchEvent(new Event("input", { bubbles: true }));
        endDateInput.dispatchEvent(new Event("change", { bubbles: true }));
    }

    // Set end date when start date is selected
    startDateInput.addEventListener("change", updateEndDate);

    // Refresh button: clear other fields but keep date fields
    document.getElementById("refresh-btn").addEventListener("click", () => {
        // Clear non-date fields
        document.getElementById("recap_fournisseur").value = "";
        document.getElementById("recap_product").value = "";
        document.getElementById("recap_zone").value = "";
        document.getElementById("recap_client").value = "";
        document.getElementById("recap_operateur").value = "";
        document.getElementById("recap_bccbclient").value = "";

        // Trigger update events for date fields
        startDateInput.dispatchEvent(new Event("input", { bubbles: true }));
        startDateInput.dispatchEvent(new Event("change", { bubbles: true }));
        endDateInput.dispatchEvent(new Event("input", { bubbles: true }));
        endDateInput.dispatchEvent(new Event("change", { bubbles: true }));

        // Refresh data based on existing date values
        fetchData(startDateInput.value, endDateInput.value, "", "", "", "", "", ""); 
    });
};



            // Fetch data when both dates are selected
            async function fetchTotalRecap() {
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;

                if (!startDate || !endDate) return; // Don't fetch until both dates are selected

                try {
                    
                    const response = await fetch(`http://192.168.1.94:5000/fetchTotalrecapData?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`);

                    if (!response.ok) throw new Error("Network response was not ok");

                    const data = await response.json();
                    updateTotalRecapTable(data, startDate, endDate);
                    hideLoader();
                } catch (error) {
                    console.error("Error fetching total recap data:", error);
                    document.getElementById('loading-row').innerHTML = "<td colspan='5' class='text-center text-red-500'>Failed to load data</td>";
                    hideLoader();
                }
            }

            function hideLoader() {
                const loaderRow = document.getElementById('loading-row');
                if (loaderRow) {
                    loaderRow.remove();
                }
            }

            // Format number with thousand separators & two decimals
            function formatNumbert(value) {
                if (value === null || value === undefined || isNaN(value)) return "";
                return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Format percentage
            function formatPercentage(value) {
                if (value === null || value === undefined || isNaN(value)) return "";
                return (parseFloat(value) * 100).toFixed(2) + "%";
            }

            // Update table with fetched data
            function updateTotalRecapTable(data, startDate, endDate) {
                const tableBody = document.getElementById("recap-table");
                tableBody.innerHTML = "";

                if (!data || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4">No data available</td></tr>`;
                    return;
                }

                const row = data[0]; // Since it's only one row
                tableBody.innerHTML = `
<tr class="dark:bg-gray-700">
    <td class="border px-4 py-2 dark:border-gray-600">From ${startDate} to ${endDate}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumbert(row.CHIFFRE)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumbert(row.QTY)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatNumbert(row.MARGE)}</td>
    <td class="border px-4 py-2 dark:border-gray-600">${formatPercentage(row.POURCENTAGE)}</td>
</tr>
`;
            }

            // Attach event listeners to date inputs
            document.getElementById("start-date").addEventListener("change", fetchTotalRecap);
            document.getElementById("end-date").addEventListener("change", fetchTotalRecap);



            document.getElementById("downloadExcel_totalrecap").addEventListener("click", function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
        alert("Please select both start and end dates before downloading.");
        return;
    }

    const downloadUrl = `http://192.168.1.94:5000/download-totalrecap-excel?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000000`;
    window.location.href = downloadUrl;  // Triggers file download
});




            // Debounce function to limit requests
            function debounce(func, delay) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func(...args), delay);
                };
            }

            // Fetch data when filters are applied
            async function fetchFournisseurRecap() {
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
                const product = document.getElementById("recap_product").value.trim().toUpperCase();
                const client = document.getElementById("recap_client").value.trim().toUpperCase();
                const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
                const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
                const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

                if (!startDate || !endDate) return;

                const url = new URL("http://192.168.1.94:5000/fetchFournisseurData");
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000"); 
                if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
                if (product) url.searchParams.append("product", product);
                if (client) url.searchParams.append("client", client);
                if (operateur) url.searchParams.append("operateur", operateur);
                if (bccb) url.searchParams.append("bccb", bccb);
                if (zone) url.searchParams.append("zone", zone);

                try {
                    showLoader();
                    const response = await fetch(url);
                    if (!response.ok) throw new Error("Network response was not ok");

                    const data = await response.json();
                    console.log("Fetched Data:", data);  // Debugging line to check if response contains data
                    updateFournisseurTable(data);
                    hideLoader();
                } catch (error) {
                    console.error("Error fetching fournisseur data:", error);
                    document.getElementById('recap-frnsr-table').innerHTML =
                        `<tr><td colspan="5" class="text-center text-red-500 p-4">Failed to load data</td></tr>`;
                    hideLoader();
                }
            }


            // Show loader animation
            function showLoader() {
                document.getElementById("recap-frnsr-table").innerHTML = `
        <tr id="loading-row">
            <td colspan="5" class="text-center p-4">Loading...</td>
        </tr>
    `;
            }

            // Hide loader after fetching data
            function hideLoader() {
                const loaderRow = document.getElementById("loading-row");
                if (loaderRow) loaderRow.remove();
            }

            // Format number with thousand separators & two decimals
            function formatNumberf(value) {
                if (value === null || value === undefined || isNaN(value)) return "";
                return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }


            document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("recap-frnsr-table");
    const fournisseurInput = document.getElementById("recap_fournisseur");

    tableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedFournisseurs = [...document.querySelectorAll(".selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        fournisseurInput.value = selectedFournisseurs.join(", ");

        // Manually trigger the input event to simulate user search
        fournisseurInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchFournisseurRecap();
    });
});




            // Update table with fetched data
            function updateFournisseurTable(data) {
                const tableBody = document.getElementById("recap-frnsr-table");
                tableBody.innerHTML = "";

                if (!data || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4">No data available</td></tr>`;
                    return;
                }

                // Find and separate the total row
                const totalRow = data.find(row => row.FOURNISSEUR === "Total");
                const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

                if (totalRow) {
                    tableBody.innerHTML += `
            <tr class="bg-gray-200 font-bold">
                <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(totalRow.TOTAL)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(totalRow.QTY)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(totalRow.MARGE)}%</td>
            </tr>
        `;
                }

                filteredData.forEach(row => {
                    tableBody.innerHTML += `
            <tr class="dark:bg-gray-700">
                <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.TOTAL)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.QTY)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.MARGE)}%</td>
            </tr>
        `;
                });
            }



            document.getElementById("download-fournisseur").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();
    const client = document.getElementById("recap_client").value.trim().toUpperCase();
    const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
    const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
    const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL("http://192.168.1.94:5000/download-fournisseur-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
});

            // Attach event listeners
            // Attach event listeners for all filters
            document.getElementById("start-date").addEventListener("change", fetchFournisseurRecap);
            document.getElementById("end-date").addEventListener("change", fetchFournisseurRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchFournisseurRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchFournisseurRecap, 500));

            // Debounce function to limit requests
            function debounce(func, delay) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func(...args), delay);
                };
            }

            // Fetch data when filters are applied
            async function fetchProductRecap() {
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
                const product = document.getElementById("recap_product").value.trim().toUpperCase();
                const client = document.getElementById("recap_client").value.trim().toUpperCase();
                const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
                const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
                const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

                if (!startDate || !endDate) return;

                const url = new URL("http://192.168.1.94:5000/fetchProductData");
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000"); 
                if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
                if (product) url.searchParams.append("product", product);
                if (client) url.searchParams.append("client", client);
                if (operateur) url.searchParams.append("operateur", operateur);
                if (bccb) url.searchParams.append("bccb", bccb);
                if (zone) url.searchParams.append("zone", zone);

                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error("Network response was not ok");

                    const data = await response.json();
                    console.log("Received Data:", data);  // 🚀 Debugging line
                    updateProductTable(data);
                } catch (error) {
                    console.error("Error fetching product data:", error);
                }
            }


            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-prdct-table");
    const productInput = document.getElementById("recap_product");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-prdct-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchProductRecap();
    });
});
    // Format number with thousand separators & two decimals
    function formatNumberp(value) {
                if (value === null || value === undefined || isNaN(value)) return "";
                return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            // Update table with fetched data
            function updateProductTable(data) {
                const tableBody = document.getElementById("recap-prdct-table");
                tableBody.innerHTML = ""; // Clear table before inserting new rows

                if (!data || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
                    return;
                }

                const fragment = document.createDocumentFragment();

                // Find and extract the "Total" row
                const totalRow = data.find(row => row.PRODUIT === "Total");
                const filteredData = data.filter(row => row.PRODUIT !== "Total");

                // Create and append the "Total" row first
                if (totalRow) {
                    const totalTr = document.createElement("tr");
                    totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
                    totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUIT}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
                    fragment.appendChild(totalTr);
                }

                // Append remaining rows
                filteredData.forEach(row => {
                    const tr = document.createElement("tr");
                    tr.classList.add("dark:bg-gray-700");
                    tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUIT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
                    fragment.appendChild(tr);
                });

                tableBody.appendChild(fragment); // Append rows efficiently
            }


            document.getElementById("download-product-excel").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();
    const client = document.getElementById("recap_client").value.trim().toUpperCase();
    const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
    const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
    const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL("http://192.168.1.94:5000/download-product-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
});

            // Format numbers with commas (thousands separator)
            function formatNumber(value) {
                return new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
            }

            // Attach event listeners to all search fields
            document.getElementById("start-date").addEventListener("change", fetchProductRecap);
            document.getElementById("end-date").addEventListener("change", fetchProductRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchProductRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchProductRecap, 500));





            function debounce(func, delay) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func(...args), delay);
                };
            }


            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-zone-table");
    const productInput = document.getElementById("recap_zone");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-zone-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        updateZoneTable();
    });
});


            // Fetch data when filters are applied
            async function fetchZoneRecap() {
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
                const product = document.getElementById("recap_product").value.trim().toUpperCase();
                const client = document.getElementById("recap_client").value.trim().toUpperCase();
                const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
                const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
                const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

                if (!startDate || !endDate) return;

                const url = new URL("http://192.168.1.94:5000/fetchZoneRecap");
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000"); 
                if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
                if (product) url.searchParams.append("product", product);
                if (client) url.searchParams.append("client", client);
                if (operateur) url.searchParams.append("operateur", operateur);
                if (bccb) url.searchParams.append("bccb", bccb);
                if (zone) url.searchParams.append("zone", zone);

                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error("Network response was not ok");

                    const data = await response.json();
                    console.log("Received Data:", data); // Debugging log
                    updateZoneTable(data);
                    return data;  // ✅ Fix: Ensure function returns data
                } catch (error) {
                    console.error("Error fetching zone recap data:", error);
                }
            }

            // Update table with fetched data
            function updateZoneTable(data) {
                const tableBody = document.getElementById("recap-zone-table");
                tableBody.innerHTML = ""; // Clear table before inserting new rows

                if (!data || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
                    return;
                }

                const fragment = document.createDocumentFragment();

                // Find and extract the "Total" row
                const totalRow = data.find(row => row.ZONE === "Total");
                const filteredData = data.filter(row => row.ZONE !== "Total");

                // Create and append the "Total" row first
                if (totalRow) {
                    const totalTr = document.createElement("tr");
                    totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
                    totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.ZONE}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
                    fragment.appendChild(totalTr);
                }

                // Append remaining rows
                filteredData.forEach(row => {
                    const tr = document.createElement("tr");
                    tr.classList.add("dark:bg-gray-700");
                    tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.ZONE || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
                    fragment.appendChild(tr);
                });

                tableBody.appendChild(fragment); // Append rows efficiently
            }

            // Format numbers with commas (thousands separator)
            function formatNumber(value) {
                return new Intl.NumberFormat("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
            }

            // Attach event listeners to all search fields
            document.getElementById("start-date").addEventListener("change", fetchZoneRecap);
            document.getElementById("end-date").addEventListener("change", fetchZoneRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchZoneRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchZoneRecap, 500));

// Download Zone Recap as Excel
document.getElementById("download-zone-excel").addEventListener("click", function () {
    downloadExcel("download-zone-excel");
});

// Download Client Recap as Excel
document.getElementById("download-client-excel").addEventListener("click", function () {
    downloadExcel("download-client-excel");
});
// Download Operator Recap as Excel
document.getElementById("download-Opérateur-excel").addEventListener("click", function () {
    downloadExcel("download-operator-excel");
});

// Download BCCB Recap as Excel
document.getElementById("download-BCCB-excel").addEventListener("click", function () {
    downloadExcel("download-bccb-excel");
});
document.getElementById("download-bccb-product-excel").addEventListener("click", function () {
    downloadExcel("download-bccb-product-excel");
});
function downloadExcel(endpoint) {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();
    const client = document.getElementById("recap_client").value.trim().toUpperCase();
    const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
    const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
    const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL(`http://192.168.1.94:5000/${endpoint}`);
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    window.location.href = url;
}


            // Fetch data for Recap by Client
            async function fetchClientRecap() {
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
                const product = document.getElementById("recap_product").value.trim().toUpperCase();
                const client = document.getElementById("recap_client").value.trim().toUpperCase();
                const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
                const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
                const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

                if (!startDate || !endDate) return;

                const url = new URL("http://192.168.1.94:5000/fetchClientRecap");
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000000"); 
                if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
                if (product) url.searchParams.append("product", product);
                if (client) url.searchParams.append("client", client);
                if (operateur) url.searchParams.append("operateur", operateur);
                if (bccb) url.searchParams.append("bccb", bccb);
                if (zone) url.searchParams.append("zone", zone);

                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error("Network response was not ok");

                    const data = await response.json();
                    console.log("Received Client Recap Data:", data); // Debugging log
                    updateClientTable(data);
                } catch (error) {
                    console.error("Error fetching client recap data:", error);
                }
            }


            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-client-table");
    const productInput = document.getElementById("recap_client");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-client-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchClientRecap();
    });
});

            // Update table with fetched data
            function updateClientTable(data) {
                const tableBody = document.getElementById("recap-client-table");
                tableBody.innerHTML = ""; // Clear table before inserting new rows

                if (!data || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
                    return;
                }

                const fragment = document.createDocumentFragment();

                // Find and extract the "Total" row
                const totalRow = data.find(row => row.CLIENT === "Total");
                const filteredData = data.filter(row => row.CLIENT !== "Total");

                // Create and append the "Total" row first
                if (totalRow) {
                    const totalTr = document.createElement("tr");
                    totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
                    totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.CLIENT}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
                    fragment.appendChild(totalTr);
                }

                // Append remaining rows
                filteredData.forEach(row => {
                    const tr = document.createElement("tr");
                    tr.classList.add("dark:bg-gray-700");
                    tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.CLIENT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
                    fragment.appendChild(tr);
                });

                tableBody.appendChild(fragment); // Append rows efficiently
            }

            // Attach event listeners to fetch data when filters change
            document.getElementById("start-date").addEventListener("change", fetchClientRecap);
            document.getElementById("end-date").addEventListener("change", fetchClientRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchClientRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchClientRecap, 500));

// Button click triggers the fetching and chart creation
// Fetch the operator recap data normally, without waiting for the button click
async function fetchOperatorRecap() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();
    const client = document.getElementById("recap_client").value.trim().toUpperCase();
    const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
    const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
    const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchOperatorRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000000"); 
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received Operator Recap Data:", data); // Debugging log
        updateOperatorTable(data);  // Always update table with fetched data
        return data; // Return fetched data
    } catch (error) {
        console.error("Error fetching operator recap data:", error);
        return null; // In case of error, return null
    }
}



document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-operator-table");
    const productInput = document.getElementById("recap_operateur");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-operator-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        productInput.value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        productInput.dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchOperatorRecap();
    });
});
// Button click triggers the chart creation (after data is fetched)

// Update table with fetched data
function updateOperatorTable(data) {
    const tableBody = document.getElementById("recap-operator-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    // Find and extract the "Total" row
    const totalRow = data.find(row => row.OPERATEUR === "Total");
    const filteredData = data.filter(row => row.OPERATEUR !== "Total");

    // Create and append the "Total" row first
    if (totalRow) {
        const totalTr = document.createElement("tr");
        totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
        totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.OPERATEUR}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(totalRow.MARGE * 100)}%</td>
        `;
        fragment.appendChild(totalTr);
    }

    // Append remaining rows
    filteredData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.OPERATEUR || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.TOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberp(row.MARGE * 100)}%</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment); // Append rows efficiently
}

// Update chart with fetched data (only when clicking the button)
let allcharts = null; // Global chart variable

// Function to fetch and update the Operator chart
document.getElementById("generate-chart-operateur").addEventListener("click", async function () {
    const data = await fetchOperatorRecap();
    console.log("Fetched operateur Data chart without total:", data); // Debugging log

    if (data && data.length > 0) {
        // Filter out the "Total" row before passing the data to the chart
        const dataForChart = data.filter(row => row.OPERATEUR !== "Total");
        
        // Update the chart with the filtered data
        updateChart(dataForChart, "OPERATEUR");
    } else {
        console.warn("No data received for ZONE.");
    }
});

// Function to fetch and update the Zone chart
document.getElementById("generate-chart-zone").addEventListener("click", async function () {
    const data = await fetchZoneRecap();
    console.log("Fetched Zone Data chart without total:", data); // Debugging log

    if (data && data.length > 0) {
        // Filter out the "Total" row before passing the data to the chart
        const dataForChart = data.filter(row => row.ZONE !== "Total");
        
        // Update the chart with the filtered data
        updateChart(dataForChart, "ZONE");
    } else {
        console.warn("No data received for ZONE.");
    }
});



// Generic function to update the chart based on dataset type (Operator or Zone)
function updateChart(data, type) {
    if (!data || data.length === 0) {
        console.warn(`No data available for the ${type} chart.`);
        return;
    }

    // Extract "Total" row if available
    const totalRow = data.find(row => row[type] === "Total");
    const filteredData = data.filter(row => row[type] !== "Total");

    // Prepare labels and values
    const labels = filteredData.map(row => row[type]);
    const totalValues = filteredData.map(row => row.TOTAL);
    const qtyValues = filteredData.map(row => row.QTY);
    const margeValues = filteredData.map(row => row.MARGE * 100);

    // Include the "Total" row in the chart
    if (totalRow) {
        labels.unshift(totalRow[type]);
        totalValues.unshift(totalRow.TOTAL);
        qtyValues.unshift(totalRow.QTY);
        margeValues.unshift(totalRow.MARGE * 100);
    }

    console.log(`Chart Labels for ${type}:`, labels);
    console.log("Total Values:", totalValues);
    console.log("Qty Values:", qtyValues);
    console.log("Marge Values:", margeValues);

    const canvas = document.getElementById("allcharts");
    if (!canvas) {
        console.error("Canvas element not found!");
        return;
    }

    const ctx = canvas.getContext("2d");

    // Destroy previous chart before creating a new one
    if (allcharts instanceof Chart) {
        console.log("Destroying old chart...");
        allcharts.destroy();
    }

    // Render the new chart
    setTimeout(() => {
        allcharts = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Total",
                        data: totalValues,
                        backgroundColor: "rgba(54, 162, 235, 0.6)",
                    },
                    {
                        label: "QTy",
                        data: qtyValues,
                        backgroundColor: "rgba(255, 99, 132, 0.6)",
                    },
                    {
                        label: "Marge (%)",
                        data: margeValues,
                        backgroundColor: "rgba(75, 192, 192, 0.6)",
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });

        console.log(`${type} chart successfully created!`);
    }, 100);
}

// Helper function to format numbers
function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

            // Attach event listeners to fetch data when filters change
            document.getElementById("start-date").addEventListener("change", fetchOperatorRecap);
            document.getElementById("end-date").addEventListener("change", fetchOperatorRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchOperatorRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchOperatorRecap, 500));




        
  // Fetch data for Recap by BCCB
  async function fetchBccbRecap() {
                const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
                const product = document.getElementById("recap_product").value.trim().toUpperCase();
                const client = document.getElementById("recap_client").value.trim().toUpperCase();
                const operateur = document.getElementById("recap_operateur").value.trim().toUpperCase();
                const bccb = document.getElementById("recap_bccbclient").value.trim().toUpperCase();
                const zone = document.getElementById("recap_zone").value.trim().toUpperCase();

                if (!startDate || !endDate) return;

                const url = new URL("http://192.168.1.94:5000/fetchBCCBRecap"); 
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                
                if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
                if (product) url.searchParams.append("product", product);
                if (client) url.searchParams.append("client", client);
                if (operateur) url.searchParams.append("operateur", operateur);
                if (bccb) url.searchParams.append("bccb", bccb);
                if (zone) url.searchParams.append("zone", zone);

                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error("Network response was not ok");

                    const data = await response.json();
                    console.log("Received BCCB Recap Data:", data); // Debugging log
                    updateBccbTable(data);
                } catch (error) {
                    console.error("Error fetching BCCB recap data:", error);
                }
            }


            function formatNumberb(value) {
    return parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

 // Update table with fetched data
            function updateBccbTable(data) {
                const tableBody = document.getElementById("recap-bccb-table");
                tableBody.innerHTML = ""; // Clear table before inserting new rows

                if (!data || data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
                    return;
                }

                const fragment = document.createDocumentFragment();

                // Find and separate the "Total" row
                const totalRow = data.find(row => row.DOCUMENTNO === "Total");
                const filteredData = data.filter(row => row.DOCUMENTNO !== "Total");

                // Append regular rows first
                filteredData.forEach(row => {
                    const tr = document.createElement("tr");
                    tr.classList.add("dark:bg-gray-700");
                    tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.DOCUMENTNO || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.DATEORDERED || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumberb(row.GRANDTOTAL)}</td>

            <td class="border px-4 py-2 dark:border-gray-600">${row.MARGE !== null ? row.MARGE.toFixed(2) + '%' : "N/A"}</td>
        `;
                    fragment.appendChild(tr);
                });

                // Append total row at the bottom
                if (totalRow) {
                    const totalTr = document.createElement("tr");
                    totalTr.classList.add("bg-gray-200", "font-bold", "dark:bg-gray-700");
                    totalTr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600 text-right" colspan="2">Total:</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.GRANDTOTAL)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.MARGE !== null ? totalRow.MARGE.toFixed(2) + '%' : "N/A"}</td>
        `;
                    fragment.appendChild(totalTr);
                }

                // Append everything to the table
                tableBody.appendChild(fragment);
            }




            

            document.getElementById("recap_bccbclient").addEventListener("input", debounce(() => {
    const bccbInput = document.getElementById("recap_bccbclient").value.trim();
    fetchBccbRecap();
    
    if (!bccbInput) {
        // Hide the product table if BCCB is cleared
        document.getElementById("bccb-product-container").style.display = "none";
    }
}, 500));


            document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-bccb-table");
    const productInput = document.getElementById("recap_bccbclient");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get selected BCCB (assuming only one should be selected)
        let selectedBccb = row.cells[0].textContent.trim();

        // Update input field
        productInput.value = selectedBccb;

        // Manually trigger input event
        productInput.dispatchEvent(new Event("input"));

        // Fetch BCCB Recap
        fetchBccbRecap();

        // Fetch BCCB Product (Fix: Use selectedBccb)
        fetchBccbProduct(selectedBccb);
    });
});

          
            // Attach event listeners to fetch data when filters change
            document.getElementById("start-date").addEventListener("change", fetchBccbRecap);
            document.getElementById("end-date").addEventListener("change", fetchBccbRecap);
            document.getElementById("recap_fournisseur").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_product").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_client").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_operateur").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_bccbclient").addEventListener("input", debounce(fetchBccbRecap, 500));
            document.getElementById("recap_zone").addEventListener("input", debounce(fetchBccbRecap, 500));

 async function fetchBccbProduct(bccb) {
    if (!bccb) return;

    const tableContainer = document.getElementById("bccb-product-container");
    tableContainer.style.display = "none"; // Hide table before fetching

    const url = new URL("http://192.168.1.94:5000/fetchBCCBProduct");
    url.searchParams.append("bccb", bccb);
    url.searchParams.append("ad_org_id", "1000000"); 

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Received BCCB Product Data:", data); // Debugging log

        updateBccbProductTable(data);

        // Show table only if data exists
        if (data.length > 0) {
            tableContainer.style.display = "block";
        }
    } catch (error) {
        console.error("Error fetching BCCB product data:", error);
    }
}


function updateBccbProductTable(data) {
    const tableBody = document.getElementById("recap-bccb-product-table");
    tableBody.innerHTML = ""; // Clear previous content

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No product data available</td></tr>`;
        return;
    }

    const fragment = document.createDocumentFragment();

    data.forEach(row => {
        // Convert REMISE to a whole number percentage, default to 0%
        const remiseFormatted = row.REMISE ? Math.round(row.REMISE * 100) + "%" : "0%";

        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUCT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.QTY || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${remiseFormatted}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.MARGE || "N/A"}</td>
        `;
        fragment.appendChild(tr);
    });

    tableBody.appendChild(fragment);
}




            // Dark Mode Toggle Functionality
            const themeToggle = document.getElementById('themeToggle');
            const htmlElement = document.documentElement;

            // Load Dark Mode Preference from Local Storage
            const savedDarkMode = localStorage.getItem('darkMode');
            if (savedDarkMode === 'true') {
                htmlElement.classList.add('dark');
                themeToggle.checked = true;
            }

            // Toggle Dark Mode on Click
            themeToggle.addEventListener('change', () => {
                htmlElement.classList.toggle('dark');
                const isDarkMode = htmlElement.classList.contains('dark');
                localStorage.setItem('darkMode', isDarkMode);
            });

        </script>

</body>

</html>