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
if (isset($_SESSION['username']) && in_array($_SESSION['username'], ['vente', 'achat'])) {
    header("Location: Acess_Denied");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <link rel="stylesheet" href="journal.css">


</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->
 <!-- Include SweetAlert2 Library (Add this to your HTML head if not already included) -->
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        title: "You are in Facturation Server!",
        html: '<div id="lte-alert-icon" style="width:150px; height:150px; margin:0 auto;"></div>',
        
        confirmButtonText: "OK",
        allowOutsideClick: false,
        didOpen: () => {
            // Load Lottie Animation
            lottie.loadAnimation({
                container: document.getElementById("lte-alert-icon"),
                renderer: "svg",
                loop: true,
                autoplay: true,
                path: "json_files/alrt.json" // Make sure this file is accessible
            });
        }
    });
});
</script>


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
fetch("side")
  .then(response => response.text())
  .then(html => {
    const container = document.getElementById("sidebar-container");
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = html;
    container.innerHTML = tempDiv.innerHTML;

    // After DOM injection, dynamically load sidebar script
    const script = document.createElement('script');
    script.src = 'sid.js'; // Move all logic into sid.js
    document.body.appendChild(script);
  })
  .catch(error => console.error("Error loading sidebar:", error));


</script>
    <!-- Main Content -->
    <div id="content" class="content flex-grow p-4">


    <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Journal de Vente Fact 
            </h1>
        </div>

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

<div class="search-container">
            <div>
                <label for="client_journal">Search Client:</label>
                <input type="text" id="client_journal" placeholder="Search for client ...">
            </div>
   
           
        
        </div>
        <br>

        <!-- <button id="downloadExcel_journal"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Journal de vente Download</span>
        </button> -->

<!-- From Uiverse.io by Rodrypaladin --> 
<!-- <button id="downloadExcel_journal"
 class="button">
  <span class="button__span">Journal de vente Download</span>
  
</button>
<STYle>
   .button {
  font-size: 1.4em; /* Slightly smaller text */
  padding: 8px 16px;
  background-color: transparent;
  transition: all 0.2s ease-out;
  border: none;
}

.button__span {
  color: #131313;
  transition: all 0.2s ease-out;
  letter-spacing: 0.1em;
  text-shadow: 1px -1px 0 #767676, 
      -1px 2px 1px #737272, 
      -2px 3px 1px #767474, 
      -3px 4px 1px #787777, 
      -4px 5px 1px #7b7a7a, 
      -5px 6px 1px #7f7d7d, 
      -6px 7px 1px #828181, 
      -7px 8px 1px #868585, 
      -8px 9px 1px #8b8a89, 
      -9px 10px 1px #8f8e8d;
}

.button__span:hover {
  text-shadow: -1px -1px 0 #767676, 
      1px 2px 1px #737272, 
      2px 3px 1px #767474, 
      3px 4px 1px #787777, 
      4px 5px 1px #7b7a7a, 
      5px 6px 1px #7f7d7d, 
      6px 7px 1px #828181, 
      7px 8px 1px #868585, 
      8px 9px 1px #8b8a89, 
      9px 10px 1px #8f8e8d;
}

.button:active .button__span {
  text-shadow: none;
}

</STYle> -->


<div >
  <button class="Btn center-btn" id="downloadExcel_journal">
    <div class="svgWrapper">
      <img src="assets/excel.png" alt="Excel Icon" class="excelIcon" />
      <div class="text">&nbsp;Download</div>
      </div>
  </button>
</div>



<style>
    .center-btn {
  display: block;
  margin: 0 auto;
}

  .Btn {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: 50px;
    height: 50px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: 0.3s;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
    background-color: #217346; /* Excel green */
  }

  .svgWrapper {
    width: 100%;
    transition: 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .excelIcon {
    width: 24px;
    height: 24px;
  }

  .text {
    position: absolute;
    right: 0;
    width: 0;
    opacity: 0;
    color: white;
    font-size: 1.1em;
    font-weight: 600;
    transition: 0.3s;
    white-space: nowrap;
  }

  .Btn:hover {
    width: 140px;
    border-radius: 40px;
  }

  .Btn:hover .svgWrapper {
    width: 30%;
    padding-left: 20px;
  }

  .Btn:hover .text {
    opacity: 1;
    width: 70%;
    padding-right: 10px;
  }

  .Btn:active {
    transform: translate(2px, 2px);
  }
</style>



        <br>
        
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Total journal de vente</h2>
    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">

            <th data-column="totalTotalHT" onclick="sortJournalVenteTable('TotalHT')" class="border px-4 py-2">Total HT</th>
            <th data-column="totalTotalTVA" onclick="sortJournalVenteTable('TotalTVA')" class="border px-4 py-2">Total TVA</th>
            <th data-column="totalTotalDT" onclick="sortJournalVenteTable('TotalDT')" class="border px-4 py-2">Total DT</th>
            <th data-column="totalTotalTTC" onclick="sortJournalVenteTable('TotalTTC')" class="border px-4 py-2">Total TTC</th>
            <th data-column="totalNETAPAYER" onclick="sortJournalVenteTable('NETAPAYER')" class="border px-4 py-2">Net à Payer</th>


        </tr>
    </thead>
    <tbody id="totaljournal-vente-table" class="dark:bg-gray-800">

  
    </tbody>
</table>


</div>

<br> <br>


        <!-- Table -->
    <!-- Table -->
<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Journal de vente</h2>
    <table class="min-w-full border-collapse text-sm text-left dark:text-white">
    <thead>
        <tr class="table-header dark:bg-gray-700">
        <th data-column="DocumentNo" onclick="sortJournalVenteTable('DocumentNo')" class="border px-4 py-2">
    Document No <span id="sort-icon-DocumentNo"></span>
</th>
        <th data-column="DateInvoiced" onclick="sortJournalVenteTable('DateInvoiced')" class="border px-4 py-2">Date Invoiced</th>

            <th data-column="Client" onclick="sortJournalVenteTable('Client')" class="border px-4 py-2">Client</th>
            <th data-column="TotalHT" onclick="sortJournalVenteTable('TotalHT')" class="border px-4 py-2">Total HT</th>
            <th data-column="TotalTVA" onclick="sortJournalVenteTable('TotalTVA')" class="border px-4 py-2">Total TVA</th>
            <th data-column="TotalDT" onclick="sortJournalVenteTable('TotalDT')" class="border px-4 py-2">Total DT</th>
            <th data-column="TotalTTC" onclick="sortJournalVenteTable('TotalTTC')" class="border px-4 py-2">Total TTC</th>
            <th data-column="NETAPAYER" onclick="sortJournalVenteTable('NETAPAYER')" class="border px-4 py-2">Net à Payer</th>

            <th data-column="Region" onclick="sortJournalVenteTable('Region')" class="border px-4 py-2">Region</th>
            <th data-column="Entreprise" onclick="sortJournalVenteTable('Entreprise')" class="border px-4 py-2">Entreprise</th>

        </tr>
    </thead>
    <tbody id="journal-vente-table" class="dark:bg-gray-800">
         <tr id="loading-row">
            <td colspan="10" class="text-center p-4">
                <div id="lottie-container-d" style="width: 290px; height: 200px; margin: auto;"></div>
            </td>
        </tr> 
  
    </tbody>
</table>


</div>


<div class="flex justify-center items-center mt-4 text-sm text-gray-700 dark:text-white">
    <div id="pagination-info" class="mr-4">Page 1</div>
    <div class="space-x-2">
        <button onclick="goToFirstPage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">First</button>
        <button onclick="goToPreviousPage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">Previous</button>
        <button onclick="goToNextPage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">Next</button>
        <button onclick="goToLastPage()" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300">Last</button>
    </div>
</div>


     

        <!-- second table remise aauto  -->


        <!-- Pagination -->
         
    
     <br>


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
            document.addEventListener("DOMContentLoaded", function () {
    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");
    const clientInput = document.getElementById("client_journal");
    const refreshBtn = document.getElementById("refresh-btn");

    // Set default value for end date to today
    const today = new Date().toISOString().split("T")[0];
    endDate.value = today;

    function triggerChangeEvent(inputElement) {
        inputElement.focus();
        inputElement.value = inputElement.value; // Ensure the value remains the same
        inputElement.dispatchEvent(new Event("input", { bubbles: true })); // Simulate typing
        inputElement.dispatchEvent(new Event("change", { bubbles: true })); // Simulate selection
    }

    // Ensure start date selection triggers end date update
    startDate.addEventListener("change", function () {
        if (!endDate.dataset.changed) {
            endDate.value = today;
            triggerChangeEvent(endDate);
        }
    });

    // Mark end date as manually changed
    endDate.addEventListener("change", function () {
        endDate.dataset.changed = true;
    });

    // Refresh button action
    refreshBtn.addEventListener("click", function () {
        triggerChangeEvent(startDate);
        triggerChangeEvent(endDate);
        triggerChangeEvent(clientInput);
    });
});

    // Show loader animation
function showJournalVenteLoader() {
    document.getElementById("journal-vente-table").innerHTML = `
        <tr id="loading-row">
            <td colspan="10" class="text-center p-4">Loading...</td>
        </tr>
    `;
}

// Hide loader after fetching data
function hideJournalVenteLoader() {
    const loaderRow = document.getElementById("loading-row");
    if (loaderRow) loaderRow.remove();
}

// Format number with thousand separators & two decimals
function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function formatDate(dateString) {
    if (!dateString) return ''; // Return an empty string if no date provided

    const date = new Date(dateString);
    
    // Format the date as 'Wed, 26 Mar 2025'
    const options = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-GB', options); // 'en-GB' for British date format
}



 // Function to fetch and display total journal data
async function fetchAndDisplayTotalJournal() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    
    try {
        // Fetch data from the API endpoint
        const response = await fetch(`http://127.0.0.1:5000/totalJournal?start_date=${startDate}&end_date=${endDate}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        // Format numbers with thousands separators and 2 decimal places
        const formatNumber = (num) => {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        };
        
        // Create the table row with the data
        const tableBody = document.getElementById('totaljournal-vente-table');
        tableBody.innerHTML = `
            <tr>
                <td class="border px-4 py-2">${formatNumber(data.TotalHT)}</td>
                <td class="border px-4 py-2">${formatNumber(data.TotalTVA)}</td>
                <td class="border px-4 py-2">${formatNumber(data.TotalDT)}</td>
                <td class="border px-4 py-2">${formatNumber(data.TotalTTC)}</td>
                <td class="border px-4 py-2">${formatNumber(data.NETAPAYER)}</td>
            </tr>
        `;
        
    } catch (error) {
        console.error('Error fetching total journal data:', error);
        // Display error message in the table
        document.getElementById('totaljournal-vente-table').innerHTML = `
            <tr>
                <td colspan="5" class="border px-4 py-2 text-red-500">Error loading data: ${error.message}</td>
            </tr>
        `;
    }
}

// Call the function when the page loads or when date inputs change
document.addEventListener('DOMContentLoaded', fetchAndDisplayTotalJournal);

// If you have date inputs that should trigger a refresh when changed:
document.getElementById("start-date")?.addEventListener('change', fetchAndDisplayTotalJournal);
document.getElementById("end-date")?.addEventListener('change', fetchAndDisplayTotalJournal);



// Optional: Auto-fetch on page load or hook it to a filter button




let journalData = [];
let currentPage = 1;
const rowsPerPage = 8;
let sortColumn = null;
let sortDirection = 'asc'; // or 'desc'
// Sorting function (as referenced in your table headers)
function sortJournalVenteTable(column) {
    if (sortColumn === column) {
        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn = column;
        sortDirection = 'asc';
    }

    journalData.sort((a, b) => {
        let valA = a[column];
        let valB = b[column];

        if (column === 'DateInvoiced') {
            valA = new Date(valA);
            valB = new Date(valB);
        }

        if (typeof valA === 'number' && typeof valB === 'number') {
            return sortDirection === 'asc' ? valA - valB : valB - valA;
        }

        valA = (valA || '').toString().toUpperCase();
        valB = (valB || '').toString().toUpperCase();

        if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
        if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
        return 0;
    });

    updateSortIcons(); // Call to update icons
    currentPage = 1;
    renderPage();
}


// Fetch data when filters are applied for journal vente
async function fetchJournalVente() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const client = document.getElementById("client_journal").value.trim().toUpperCase();

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) return;

    // Construct URL with query parameters
    const url = `http://192.168.1.94:5000/journalVente?start_date=${startDate}&end_date=${endDate}&client=${client}`;

    try {
        showJournalVenteLoader(); // Show loading animation
        const response = await fetch(url); // Fetch data
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json(); // Parse the response as JSON
        console.log("Fetched Data:", data); // Debugging line to check the response
        updateJournalVenteTable(data); // Update table with the fetched data
        hideJournalVenteLoader(); // Hide loading animation
    } catch (error) {
        console.error("Error fetching journal vente data:", error);
        document.getElementById('journal-vente-table').innerHTML =
            `<tr><td colspan="10" class="text-center text-red-500 p-4">Failed to load data</td></tr>`;
        hideJournalVenteLoader(); // Hide loading animation if error occurs
    }
}


function updateJournalVenteTable(data) {
    journalData = data || [];
    currentPage = 1;
    renderPage();
}

function renderPage() {
    const tableBody = document.getElementById("journal-vente-table");
    tableBody.innerHTML = "";

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = journalData.slice(start, end);

    if (pageData.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="10" class="text-center p-4">No data available</td></tr>`;
    }

    pageData.forEach(row => {
        const tr = document.createElement("tr");
        tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.DocumentNo}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatDate(row.DateInvoiced)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.Client}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalHT)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalTVA)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalDT)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.TotalTTC)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.NETAPAYER)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.Region ? row.Region.replace(/</g, "&lt;").replace(/>/g, "&gt;") : "Aucune"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${row.Entreprise || "N/A"}</td>
        `;
        tr.addEventListener("click", () => {
            const searchInput = document.getElementById("client_journal");
            if (row.Client) {
                searchInput.value = row.Client;
                searchInput.dispatchEvent(new Event("input"));
            }
        });
        tableBody.appendChild(tr);
    });

    updatePaginationInfo();
}
function updateSortIcons() {
    const headers = document.querySelectorAll("th[data-column]");
    headers.forEach(th => {
        const col = th.getAttribute("data-column");
        const icon = th.querySelector("span");
        if (!icon) return;
        if (col === sortColumn) {
            icon.innerHTML = sortDirection === 'asc' ? '▲' : '▼';
        } else {
            icon.innerHTML = '';
        }
    });
}

function updatePaginationInfo() {
    const totalPages = Math.ceil(journalData.length / rowsPerPage) || 1;
    document.getElementById("pagination-info").textContent = `Page ${currentPage} of ${totalPages}`;
}

function goToFirstPage() {
    currentPage = 1;
    renderPage();
}

function goToPreviousPage() {
    if (currentPage > 1) {
        currentPage--;
        renderPage();
    }
}

function goToNextPage() {
    const totalPages = Math.ceil(journalData.length / rowsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderPage();
    }
}

function goToLastPage() {
    currentPage = Math.ceil(journalData.length / rowsPerPage);
    renderPage();
}



// Attach event listeners
document.getElementById("start-date").addEventListener("change", fetchJournalVente);
document.getElementById("end-date").addEventListener("change", fetchJournalVente);
document.getElementById("client_journal").addEventListener("input", fetchJournalVente);

// Clear client_journal and trigger function on click
document.getElementById("client_journal").addEventListener("click", function () {
    this.value = ""; // Clear the input
    this.dispatchEvent(new Event("input")); // Trigger input event
});


document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("downloadExcel_journal").addEventListener("click", downloadJournalExcel);
});

function downloadJournalExcel() {
    const clientName = document.getElementById("client_journal").value.trim();
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
        console.error("❌ Missing required fields. Not downloading file.");
        return;
    }

    const url = `http://192.168.1.94:5000/download-journal-vente-excel?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&client=${encodeURIComponent(clientName || "")}`;
    console.log("🔗 Download URL:", url); // ✅ Debugging

    // Create a hidden link and trigger download
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "journal_vente.xlsx"); // Ensure filename is set
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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