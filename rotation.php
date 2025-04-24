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
// Restrict access for 'vente' and 'achat'
if (isset($_SESSION['username']) && in_array($_SESSION['username'], ['yasser'])) {
    header("Location: Acess_Denied");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNM Web</title>
    <script src="main.js" defer></script>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="rotation.css">
    <style>
        /* Responsive Chart Container */
@media (max-width: 1024px) {
    #chartContainer {
        height: 400px; /* Reduce height on medium screens */
    }
}

@media (max-width: 768px) {
    #chartContainer {
        height: 300px; /* Smaller height for tablets */
    }
}

@media (max-width: 480px) {
    #chartContainer {
        height: 250px; /* Compact height for mobile screens */
    }
}




/* Sidebar Hidden by Default */
.sidebar-hidden {
    transform: translateX(-100%);
}

/* Sidebar Appears Smoothly */
.sidebar {
    transition: transform 0.3s ease-in-out;
}

/* Sidebar Stays Open Until Mouse Leaves */
.sidebar:hover {
    transform: translateX(0);
}

    </style>


</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->
 

    <!-- Dark/Light Mode Toggle Button -->
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

   /* From Uiverse.io by Spacious74 */ 
   @keyframes fade-in {
  0% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}

@keyframes drop {
  0% {
    bottom: 0px;
    opacity: 1;
  }

  80% {
    opacity: 1;
  }

  100% {
    opacity: 1;
    bottom: -400px;
  }
}

.loader {
  text-align: center;
  font-size: 16px;
  display: flex;
  justify-content: center;
  width: -20%;
  position: relative;
  border: none;
  cursor: pointer;
  margin-left: 20%;
  background-color: transparent;
}

.loader-bg {
  border-radius: 12px;
  padding: 10px 15px;
  z-index: 2;
  width: 140px;
  color: #ace3f8;
  font-weight: bold;
  letter-spacing: 2px;
  text-transform: uppercase;
  background-color: #18798a;
  background-image: radial-gradient(circle 80px at 50% 150%, #63d44d, #18798a);
  margin-bottom: 10px;
}

.loader-bg:hover {
  text-shadow: 0px 0px 4px #63d44d;
}
.loader-bg:active {
  background-image: radial-gradient(circle 140px at 50% 150%, #63d44d, #18798a);
}
.loader-bg:hover + .drops .drop2,
.drop3 {
  animation: drop 1s cubic-bezier(1, 0.19, 0.66, 0.12) 0.2s infinite;
}
.loader-bg:active + .drops .drop1,
.drop2,
.drop3 {
  background-color: #63d44d;
}
.drops {
  -webkit-filter: url("#liquid");
  filter: url("#liquid");
  position: absolute;
  top: 35%;
  left: 0;
  bottom: 0;
  right: 0;
  z-index: 1;
  opacity: 0;
  animation: fade-in 0.1s linear 0.4s forwards;
}

.drop1,
.drop2,
.drop3 {
  width: 20px;
  height: 28px;
  border-radius: 50%;
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  margin: auto;
  background-color: #18798a;
}

.drop1 {
  width: 125px;
  height: 16px;
  bottom: 2px;
  border-radius: 0;
}

.drop3 {
  background-color: #63d44d;
}

.drop2,
.drop3 {
  animation: drop 2s cubic-bezier(1, 0.19, 0.66, 0.12) 1s infinite;
}


.btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn:hover {
        background-color: #45a049;
        transform: scale(1.05);
    }

    .btn i {
        font-size: 18px;
    }


    .products-table-container {
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
    max-height: 400px;
    overflow-y: auto;
    width: 50%;
    margin-top: 5px;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
}

.products-table th, .products-table td {
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.products-table tr:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}

.products-table tr.selected {
    background-color: #e0e0e0;
}

.table-pagination {
    display: flex;
    justify-content: space-between;
    padding: 8px 12px;
    background-color: #f9f9f9;
    border-top: 1px solid #ddd;
}

.table-pagination button {
    padding: 4px 8px;
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
}

.table-pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
fetch("side")
  .then(response => response.text())
  .then(html => {
    const container = document.getElementById("sidebar-container");
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = html;
    container.innerHTML = tempDiv.innerHTML;

    // After DOM injection, dynamically load sidebar script
    const script = document.createElement('script');
    script.src = 'sidebar.js'; // Move all logic into sidebar.js
    document.body.appendChild(script);
  })
  .catch(error => console.error("Error loading sidebar:", error));


</script>

    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">
        <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Rotation 
            </h1>
        </div>

        <!-- Filters -->


        <br>




      




        <!-- Date Inputs -->
<div class="date-container">
    <div class="flex items-center space-x-2">
        <label for="start-date">Begin Date:</label>
        <input type="date" id="start-date">
    </div>

    <div class="flex items-center space-x-2">
        <label for="end-date">End Date:</label>
        <input type="date" id="end-date">
    </div>

</div>




<div class="product-container">
    <input type="text" id="product-search" placeholder="Search product...">
    



</div>
<div class="products-table-container" id="products-table-container">
        <table class="products-table" id="products-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                </tr>
            </thead>
            <tbody id="products-table-body">
                <!-- Products will be loaded here -->
            </tbody>
        </table>
        <div class="table-pagination" id="table-pagination">
            <button id="prev-page">Previous</button>
            <span id="page-info">Page 1 of 1</span>
            <button id="next-page">Next</button>
        </div>
    </div>



<button id="downloadExcel_rotation" class="loader">
  <div class="loader-bg">
    <span>Download</span>
  </div>
  <div class="drops">
    <div class="drop1"></div>
    <div class="drop2"></div>
    <div class="drop3"></div>
  </div>
</button>
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" style="position: absolute; width: 0; height: 0;">
  <defs>
    <filter id="liquid">
      <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"></feGaussianBlur>
      <feColorMatrix
        in="blur"
        mode="matrix"
        values="1 0 0 0 0  
                0 1 0 0 0  
                0 0 1 0 0  
                0 0 0 18 -7"
        result="liquid">
      </feColorMatrix>
    </filter>
  </defs>
</svg>


       


 
<div class="flex gap-6">
    <!-- Left Side: Tables -->
    <div class="w-1/4">
        <!-- First Table: Smaller -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-4">
            <div class="flex justify-between items-center p-3">
                <h2 class="text-base font-semibold dark:text-black">HISTORIQUE</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th class="border px-3 py-2">QTY DISPO</th>
                            <th class="border px-3 py-2">DERNIER ACHAT</th>
                            <th class="border px-3 py-2">DATE</th>
                        </tr>
                    </thead>
                    <tbody id="historique-table" class="dark:bg-gray-800"></tbody>
                </table>
            </div>
        </div>
        <br>

        <!-- Second Table: Taller -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <div class="flex justify-between items-center p-3">
                <h2 class="text-base font-semibold dark:text-black">ROTATION PAR MOIS</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th class="border px-3 py-2">PERIOD</th>
                            <th class="border px-3 py-2">QTY_VENDU</th>
                            <th class="border px-3 py-2">QTY_ACHETE</th>
                        </tr>
                    </thead>
                    <tbody id="rotation-table" class="dark:bg-gray-800"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Side: Larger Canvas -->
 
    <div class="w-3/4 flex flex-col gap-6">
    <div class="flex gap-4">
        <button id="toggleChartBtn" class="btn" onclick="toggleChartType()">
            <i class="fas fa-chart-line"></i> Switch to Graph
        </button>
        <button id="fullscreenBtn" class="btn" onclick="toggleFullscreen()">
            <i class="fas fa-expand"></i> Full Screen
        </button>
    </div>
    <div id="chartContainer" class="canvas-container rounded-lg bg-white shadow-md dark:bg-gray-800 h-[500px] w-full flex justify-center items-center" style="display: none;">
    <canvas id="histogramChart" class="w-full h-full"></canvas>
</div>
<br><br>

<!-- Button Styles -->

</div>

<br><br>

<script>







document.addEventListener("DOMContentLoaded", () => {
    updateToggleButtonText();

    // Add event listeners for auto-updating the chart when input values change
    document.getElementById("product-search")?.addEventListener("input", fetchHistogramData);
    document.getElementById("start-date")?.addEventListener("change", function() {
        if (document.getElementById("end-date").value) {
            fetchHistogramData();
        }
    });
    document.getElementById("end-date")?.addEventListener("change", function() {
        if (document.getElementById("start-date").value) {
            fetchHistogramData();
        }
    });
});



function fetchHistogramData() {
    const productName = document.getElementById("product-search")?.value.trim();
    const startDate = document.getElementById("start-date")?.value;
    const endDate = document.getElementById("end-date")?.value;
    const chartContainer = document.getElementById("chartContainer");

    // Check if both startDate and endDate are provided
    if (!startDate || !endDate) {
        console.error("❌ Start date and end date are required.");
        chartContainer.style.display = "none"; // Hide the chart if dates are missing
        return;
    }

    const url = `http://192.168.1.94:5000/histogram?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&product=${encodeURIComponent(productName)}`;

    fetch(url)
        .then(response => response.ok ? response.json() : Promise.reject(`HTTP error! Status: ${response.status}`))
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                console.error("❌ No valid data received.");
                chartContainer.style.display = "none"; // Hide chart if no data
                return;
            }
            chartContainer.style.display = "flex"; // Show chart when data is available
            updateHistogramChart(data);
        })
        .catch(error => {
            console.error("❌ Error fetching histogram data:", error);
            chartContainer.style.display = "none"; // Hide chart on error
        });
}


function updateHistogramChart(data) {
    const labels = data.map(item => item.PERIOD);
    const qtyAchete = data.map(item => item.QTY_ACHETÉ);
    const qtyVendu = data.map(item => item.QTY_VENDU);

    const ctx = document.getElementById("histogramChart").getContext("2d");

    if (chartInstance) {
        chartInstance.destroy(); 
    }

    chartInstance = new Chart(ctx, {
        type: currentChartType, 
        data: {
            labels,
            datasets: [
                {
                    label: "Quantité Achetée",
                    data: qtyAchete,
                    backgroundColor: currentChartType === "bar" ? "rgba(54, 162, 235, 0.6)" : "transparent",
                    borderColor: "rgba(54, 162, 235, 1)",
                    borderWidth: 2,
                    fill: currentChartType === "bar" ? false : true,
                    tension: 0.3, 
                },
                {
                    label: "Quantité Vendue",
                    data: qtyVendu,
                    backgroundColor: currentChartType === "bar" ? "rgba(255, 99, 132, 0.6)" : "transparent",
                    borderColor: "rgba(255, 99, 132, 1)",
                    borderWidth: 2,
                    fill: currentChartType === "bar" ? false : true,
                    tension: 0.3, 
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: "top" } },
            scales: {
                x: { title: { display: true, text: "Period" } },
                y: { title: { display: true, text: "Quantity" }, beginAtZero: true }
            }
        }
    });
}

function toggleChartType() {
    currentChartType = currentChartType === "bar" ? "line" : "bar"; 
    updateToggleButtonText();
    fetchHistogramData();
}

function updateToggleButtonText() {
    const btn = document.getElementById("toggleChartBtn");
    btn.innerHTML = currentChartType === "bar"
        ? '<i class="fas fa-chart-line"></i> Switch to Graph'
        : '<i class="fas fa-chart-bar"></i> Switch to Histogram';
}

function toggleFullscreen() {
    const canvasContainer = document.querySelector(".canvas-container");
    if (!document.fullscreenElement) {
        canvasContainer.requestFullscreen().catch(err => console.error(`❌ Fullscreen error: ${err.message}`));
    } else {
        document.exitFullscreen();
    }
}



let chartInstance = null; 
let currentChartType = Math.random() < 0.5 ? "bar" : "line";
let allProducts = [];
let filteredProducts = [];
let currentPage = 1;
const rowsPerPage = 10;
let lastFetchTime = 0;
const CACHE_DURATION = 5 * 60 * 1000;

document.addEventListener("DOMContentLoaded", async function() {
    updateToggleButtonText();
    await fetchProducts();
    setupProductSearch();
    setupDateInputs();
    
    // Event listeners for chart updates
    document.getElementById("product-search")?.addEventListener("input", fetchHistogramData);
    document.getElementById("start-date")?.addEventListener("change", fetchHistogramData);
    document.getElementById("end-date")?.addEventListener("change", fetchHistogramData);
});

function setupDateInputs() {
    const productSearch = document.getElementById("product-search");
    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");
    
    // Set end date to today initially
    const today = new Date().toISOString().split("T")[0];
    endDate.value = today;
    
    // Enable dates only when product is selected
    productSearch.addEventListener("input", function() {
        if (this.value.trim()) {
            startDate.disabled = false;
            endDate.disabled = false;
        } else {
            startDate.disabled = true;
            endDate.disabled = true;
            startDate.value = "";
            endDate.value = today;
        }
    });
}
async function fetchProducts(forceRefresh = false) {
    const currentTime = Date.now();
    if (!forceRefresh && allProducts.length && (currentTime - lastFetchTime) < CACHE_DURATION) {
        console.log("✅ Using cached product data");
        return;
    }

    try {
        const response = await fetch("http://192.168.1.94:5000/fetch-rotation-product-data");
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        allProducts = await response.json();
        lastFetchTime = currentTime;
        filteredProducts = [...allProducts];
        renderTable();
    } catch (error) {
        console.error("❌ Error fetching products:", error);
    }
}

function setupProductSearch() {
    const productSearch = document.getElementById("product-search");
    const productsTableContainer = document.getElementById("products-table-container");
    
    productSearch.addEventListener("focus", function() {
        if (filteredProducts.length > 0) {
            productsTableContainer.style.display = "block";
        }
    });
    
    productSearch.addEventListener("input", debounce(function(e) {
        const searchValue = e.target.value.toLowerCase();
        
        if (!searchValue.trim()) {
            filteredProducts = [...allProducts];
        } else {
            filteredProducts = allProducts.filter(product => 
                product.NAME.toLowerCase().includes(searchValue)
            );
        }
        
        currentPage = 1;
        renderTable();
        productsTableContainer.style.display = filteredProducts.length > 0 ? "block" : "none";
    }, 300));
    
    // Close table when clicking outside
    document.addEventListener("click", function(e) {
        if (!productSearch.contains(e.target) && !productsTableContainer.contains(e.target)) {
            productsTableContainer.style.display = "none";
        }
    });
    
    // Pagination controls
    document.getElementById("prev-page").addEventListener("click", function() {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });
    
    document.getElementById("next-page").addEventListener("click", function() {
        const totalPages = Math.ceil(filteredProducts.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });
}

function renderTable() {
    const tableBody = document.getElementById("products-table-body");
    const paginationInfo = document.getElementById("page-info");
    const prevBtn = document.getElementById("prev-page");
    const nextBtn = document.getElementById("next-page");
    
    tableBody.innerHTML = "";
    
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = Math.min(startIndex + rowsPerPage, filteredProducts.length);
    const paginatedProducts = filteredProducts.slice(startIndex, endIndex);
    
    paginatedProducts.forEach((product, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${startIndex + index + 1}</td>
            <td>${product.NAME}</td>
        `;
        
        row.addEventListener("click", function() {
            document.getElementById("product-search").value = product.NAME;
            document.getElementById("products-table-container").style.display = "none";
            
            // Enable date inputs
            document.getElementById("start-date").disabled = false;
            document.getElementById("end-date").disabled = false;
            
            // Trigger all necessary functions
            fetchHistoriqueRotation();
            fetchRotationData();
            fetchHistogramData();
        });
        
        tableBody.appendChild(row);
    });
    
    const totalPages = Math.ceil(filteredProducts.length / rowsPerPage);
    paginationInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= totalPages;
}



function debounce(func, delay) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => func.apply(this, args), delay);
    };
}


async function fetchHistoriqueRotation() {
    const productName = document.getElementById("product-search").value.trim();

    console.log("Product Name:", `"${productName}"`); // ✅ Check if it's empty

    if (!productName) {
        console.error("❌ Missing product name, not sending request.");
        return; 
    }

    try {
        const url = `http://192.168.1.94:5000/fetchHistoriqueRotation?product=${encodeURIComponent(productName)}`;
        console.log("Requesting URL:", url); // ✅ Debugging

        const response = await fetch(url);
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("Response Data:", data); // ✅ Confirm data

        updateHistoriqueTable(data);
    } catch (error) {
        console.error("Error fetching data:", error);
        document.getElementById('historique-table').innerHTML = "<tr><td colspan='3' class='text-center text-red-500'>Failed to load data</td></tr>";
    }
}



function updateHistoriqueTable(data) {
    const tableBody = document.getElementById("historique-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    const row = data[0];  

    // Format the date properly in French
    const formattedDate = row.DATE 
        ? new Date(row.DATE).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) 
        : 'N/A';

    tableBody.innerHTML = `
        <tr class="dark:bg-gray-700">
            <td class="border px-3 py-2 dark:border-gray-600">${row.QTY_DISPO ?? 0}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${row.DERNIER_ACHAT ?? 0}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${formattedDate}</td>
        </tr>
    `;
}






// ✅ Attach event listeners to trigger fetching when filters change
["start-date", "end-date", "product-search"].forEach(id => {
    document.getElementById(id).addEventListener("change", fetchRotationData);
});

// Clear search input and date fields, then trigger function on click
document.getElementById("product-search").addEventListener("click", function () {
    this.value = ""; // Clear search input
    document.getElementById("start-date").value = ""; // Clear start date
    document.getElementById("end-date").value = ""; // Clear end date

    // Trigger change event for all to refresh results
    ["start-date", "end-date", "product-search"].forEach(id => {
        document.getElementById(id).dispatchEvent(new Event("change"));
    });
});


async function fetchRotationData() {
    const productInput = document.getElementById("product-search");
    const startDateInput = document.getElementById("start-date");
    const endDateInput = document.getElementById("end-date");

    const productName = productInput.value.trim();
    
    if (!productName) {
        console.warn("⚠️ Please select a product first.");
        return;
    }

    // Ensure startDate and endDate are selected after the product
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;

    if (!startDate || !endDate) {
        console.warn("⚠️ Please select both start and end dates.");
        return;
    }

    const url = `http://192.168.1.94:5000/rotationParMois?product=${encodeURIComponent(productName)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    console.log("🔗 Request URL:", url); // ✅ Debugging

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        const data = await response.json();
        console.log("📥 Response Data:", data); // ✅ Debugging

        updateRotationTable(data);
    } catch (error) {
        console.error("❌ Error fetching rotation data:", error);
        document.getElementById('rotation-table').innerHTML = "<tr><td colspan='3' class='text-center text-red-500'>Failed to load data</td></tr>";
    }
}

function updateRotationTable(data) {
    const tableBody = document.getElementById("rotation-table");
    tableBody.innerHTML = ""; // Clear previous data

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    let specialRows = "";
    let normalRows = "";

    data.forEach(row => {
        const rowHTML = `
            <tr class="dark:bg-gray-700 ${row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE" ? "font-bold" : ""}">
                <td class="border px-3 py-2 dark:border-gray-600">${row.PERIOD ?? 'N/A'}</td>
                <td class="border px-3 py-2 dark:border-gray-600">${row.QTY_VENDU ?? 0}</td>
                <td class="border px-3 py-2 dark:border-gray-600">${row.QTY_ACHETÉ ?? 0}</td>
            </tr>
        `;

        // Move "TOTAL" and "MOYENNE" rows to the top
        if (row.PERIOD === "TOTAL" || row.PERIOD === "MOYENNE") {
            specialRows += rowHTML;
        } else {
            normalRows += rowHTML;
        }
    });

    // Append special rows first, followed by normal rows
    tableBody.innerHTML = specialRows + normalRows;

    console.log("✅ Table updated successfully.");
}

// Set up event listeners for product and date inputs
document.addEventListener("DOMContentLoaded", () => {
    const productSelect = document.getElementById("product-select");
    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");

    // Initially disable date inputs
    startDate.disabled = true;
    endDate.disabled = true;

    // Set end date to today initially
    const today = new Date().toISOString().split("T")[0];
    endDate.value = today;

    function enableDateInputs() {
        if (productSelect.value) {
            startDate.disabled = false;
            endDate.disabled = false;
        } else {
            startDate.disabled = true;
            endDate.disabled = true;
            startDate.value = "";
            endDate.value = today;
        }
    }

    productSelect.addEventListener("change", () => {
        enableDateInputs();
        fetchRotationData(); // Fetch new data when product changes
    });

    startDate.addEventListener("change", () => {
        if (!endDate.value) {
            endDate.value = today;
        }
        fetchRotationData(); // Fetch new data when start date changes
    });

    endDate.addEventListener("change", () => {
        fetchRotationData(); // Fetch new data when end date changes
    });

    // Initial fetch if product is already selected
    if (productSelect.value) {
        enableDateInputs();
        fetchRotationData();
    }
});

document.getElementById("downloadExcel_rotation").addEventListener("click", async () => {
    const productName = document.getElementById("product-search").value.trim();
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!productName || !startDate || !endDate) {
        console.error("❌ Missing required fields. Not downloading file.");
        return;
    }

    const url = `http://192.168.1.94:5000/download-rotation-par-mois-excel?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&product=${encodeURIComponent(productName)}`;
    console.log("🔗 Download URL:", url); // ✅ Debugging

    // Create a hidden link and trigger download
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", ""); // Allow browser to determine filename
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});




// ✅ Attach event listeners to trigger fetching when filters change
["start-date", "end-date", "product-search"].forEach(id => {
    document.getElementById(id).addEventListener("change", fetchRotationData);
});




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


     

    
   
     
      
      

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
     





</body>

</html>
