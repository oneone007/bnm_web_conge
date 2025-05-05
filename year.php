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
    <title>
    Annual Recap
</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <!-- <link rel="stylesheet" href="recap_achat.css"> -->

<style>

body {
    font-family: 'Inter', sans-serif;
}

  /* Resizable Columns */
  th.resizable {
    position: relative;
  }
  
  th.resizable .resizer {
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    cursor: col-resize;
    user-select: none;
    height: 100%;
  }
  .resizer:hover {
    background-color: rgba(0, 0, 0, 0.1);
}

.table-container {
    overflow-y: auto;
    overflow-x: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    width: 100%;
    display: flex;
    flex-direction: column;
  }
  
  .table-container table {
    width: 100%;
    table-layout: auto;
    border-collapse: collapse;
  }
  
  thead {
    position: sticky;
    top: 0;
    background-color: #f3f4f6;
    z-index: 10;
  }
  
  th,
  td {
    text-align: left;
    padding: 10px;
    border: 1px solid #ddd;
    white-space: normal; /* ALLOW MULTILINE */
    word-break: break-word; /* Wrap long words */
    line-height: 1.4;
  }
  
  tbody tr {
    height: auto; /* Allow row height to adjust */
  }
  
  .table-container.placement-table {
    flex: 0.5;
    width: 10px;
  }
.dark .table-container {
    border-color: #374151;
}

.dark .table-header {
    background-color: #374151;
    color: #f9fafb;
    /* White text in dark mode */
}


.dark .table-row:nth-child(odd) {
    background-color: #1f2937;
    color: #f9fafb;
    /* White text on dark background */
}

.dark .table-row:nth-child(even) {
    background-color: #474d53;
    color: #ececec;
}


.table-wrapper {
    display: flex;
    justify-content: space-between;
    /* Ensures tables are spaced apart */
    gap: 20px;
    /* Adds spacing between tables */
}

.paginatio-wrapper {
    display: flex;
    justify-content: center;
    /* Ensures tables are spaced apart */
    gap: 250px;
    /* Adds spacing between tables */
}

/* .download-wrapper {
    display: flex;

    gap: 550px;
} */

.title-wrapper {
    display: flex;

    /* Ensures tables are spaced apart */
    gap: 730px;
    /* Adds spacing between tables */
}






.sidebar {
    min-width: 200px;
    max-width: 250px;
    background-color: #f9fafb;
    border-right: 1px solid #e5e7eb;
    transition: transform 0.3s ease-in-out;
    position: fixed;
    height: 100vh;
    z-index: 40;
}

.sidebar-hidden {
    transform: translateX(-100%);
}

.content {
    margin-left: 250px;
    /* Adjust this value based on the sidebar width */
    transition: margin-left 0.3s ease-in-out;
    width: calc(100% - 250px);
    /* Adjust this value based on the sidebar width */
}

.content-full {
    margin-left: 0;
    width: 100%;
}

.table-header {
    background-color: #f3f4f6;
    text-align: left;
    color: #000;
    /* Default text color */
    position: sticky;
    top: 0;
}

.table-row {
    color: #000;
    /* Default black text */
}

.table-row:nth-child(odd) {
    background-color: #f9fafb;
}

/* Dark mode styles */
.dark .sidebar {
    background-color: #1f2937;
    border-right-color: #374151;
}



.dark body {
    background-color: #111827;
    color: #010911;
}


/* Dark Mode */
html.dark .sidebar {
    background-color: #1f2937;
    border-right-color: #374151;
}

html.dark body {
    background-color: #111827;
    color: white;
}

/* Dark Mode Toggle - Styled Checkbox */
/* Hide Default Checkbox */
/* Hide Default Checkbox */

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


.dark td {
    color: #000000 !important;
    /* Force black text in dark mode */
    background-color: #d1d5db;
    /* Light gray background for contrast */
}

.dark h2 {
    color: #000000 !important;
    /* Force black text in dark mode */
    background-color: #d1d5db;
    /* Light gray background for contrast */
}


.dark label {
    color: white !important;
}

/* Positioning the Dark Mode Toggle on Top Right */
#themeSwitcher {
    position: sticky;
    top: 0;
    right: 0;
    padding: 10px;
    z-index: 50;
}
.download-container {
display: flex;
justify-content: flex-end;
padding: 0 16px 12px 16px;
}
.download-wrapper {
display: flex;
flex-wrap: wrap;
justify-content: center;
gap: 50px; /* Reduced for responsiveness */
margin-top: 20px;
padding: 10px;
}

.download-wrapper button {
display: flex;
align-items: center;
gap: 10px;
background-color: white;
border: 1px solid #d1d5db;
color: #374151;
padding: 12px 24px;
border-radius: 8px;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
transition: all 0.3s ease-in-out;
}

.download-wrapper button:hover {
background-color: #f3f4f6;
transform: scale(1.05);
}

.download-wrapper button img {
width: 24px;
height: 24px;
}

/* Responsive Styles */
@media (max-width: 768px) {
.download-wrapper {
  flex-direction: column;
  align-items: center;
  gap: 20px;
}

.download-wrapper button {
  width: 90%; /* Full width for smaller screens */
  justify-content: center;
}
}

.search-container {
display: grid;
grid-template-columns: repeat(2, minmax(250px, 1fr)); /* 3 columns per row */
gap: 16px;
padding: 20px;
background: #f9fafb;
border-radius: 12px;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.search-container label {
display: block;
font-weight: 600;
color: #374151;
margin-bottom: 6px;
}

.search-container input {
width: 100%;
padding: 12px;
border: 1px solid #d1d5db;
border-radius: 8px;
font-size: 16px;
transition: all 0.3s ease-in-out;
background-color: white;
color: #111827;
box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.search-container input:focus {
outline: none;
border-color: #2563eb;
box-shadow: 0 0 8px rgba(37, 99, 235, 0.5);
}

/* Dark Mode */
.dark .search-container {
background: #1f2937;
box-shadow: none;
}

.dark .search-container label {
color: #e5e7eb;
}

.dark .search-container input {
background-color: #374151;
color: white;
border: 1px solid #4b5563;
box-shadow: none;
}

.dark .search-container input:focus {
border-color: #3b82f6;
box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
}

/* Responsive Design */
@media (max-width: 1024px) {
.search-container {
  grid-template-columns: repeat(2, minmax(250px, 1fr)); /* 2 per row on tablets */
}
}

@media (max-width: 768px) {
.search-container {
  grid-template-columns: 1fr; /* 1 per row on mobile */
}
}

.date-container {
display: flex;
flex-wrap: wrap;
gap: 16px;
align-items: center;
padding: 16px;
background: #f9fafb;
border-radius: 12px;
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
max-width: 600px; /* Adjust width as needed */
width: 100%; /* Ensures it doesn't exceed max-width */
margin: 0 auto; /* Centers the container */
}

@media (max-width: 768px) {
.date-container {
flex-direction: column;
gap: 12px;
align-items: flex-start;
max-width: 90%; /* Allows slight expansion on smaller screens */
}
}

.date-container label {
font-weight: 600;
color: #374151;
}

.date-container input {
padding: 10px 14px;
border: 1px solid #d1d5db;
border-radius: 8px;
font-size: 16px;
transition: all 0.3s ease-in-out;
background-color: white;
color: #111827;
box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.date-container input:focus {
outline: none;
border-color: #2563eb;
box-shadow: 0 0 8px rgba(37, 99, 235, 0.5);
}

/* Dark Mode */
.dark .date-container {
background: #1f2937;
box-shadow: none;
}

.dark .date-container label {
color: #e5e7eb;
}

.dark .date-container input {
background-color: #374151;
color: white;
border: 1px solid #4b5563;
}

.dark .date-container input:focus {
border-color: #3b82f6;
box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
}

/* Responsive */
@media (max-width: 768px) {
.date-container {
flex-direction: column;
gap: 12px;
align-items: flex-start;
}
}
/* Hide Default Checkbox */
.checkbox {
display: none;
}

/* Toggle Background */
.checkbox-label {
width: 60px;
height: 30px;
background: #f97316; /* Light Mode Orange */
display: flex;
align-items: center;
border-radius: 50px;
position: relative;
cursor: pointer;
padding: 5px;
transition: background 0.3s ease-in-out;
}

/* Ball */
.ball {
width: 24px;
height: 24px;
background: white;
position: absolute;
border-radius: 50%;
transition: transform 0.3s ease-in-out;
left: 5px;
}

/* Icons */
.icon {
font-size: 16px;
position: absolute;
top: 50%;
transform: translateY(-50%);
transition: opacity 0.3s ease-in-out;
}

/* Sun (Left) */
.sun {
left: 10px;
color: white;
}

/* Moon (Right) */
.moon {
right: 10px;
color: white;
opacity: 0; /* Hidden in Light Mode */
}

/* Dark Mode */
html.dark .checkbox-label {
background: #1f2937; /* Dark Mode Gray */
}

html.dark .ball {
transform: translateX(30px);
}

html.dark .sun {
opacity: 0; /* Hide Sun */
}

html.dark .moon {
opacity: 1; /* Show Moon */
}

/* Theme Switcher Position */
#themeSwitcher {
position: sticky;
top: 10px;
right: 10px;
padding: 10px;
z-index: 50;
}
  /* Resizable Columns */
  th.resizable {
    position: relative;
  }
  
  th.resizable .resizer {
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    cursor: col-resize;
    user-select: none;
    height: 100%;
  }
  
</style>
</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
<div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
  <div class="text-white text-lg font-semibold">Chargement des données...</div>
</div>

    <!-- Sidebar Toggle Button -->
 

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
  .hidden {
  display: none;
}

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


.table-container {
  height: auto !important;
  max-height: none !important;
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
.container {
  padding: 0;
  box-sizing: border-box;
  display: flex;
  justify-content: center;
  width: 100%;
  margin: 0 auto;
}
.dark .button .text {
  color: #000;  /* Black text */
}

.dark .button .text span {
  color: #000;  /* Ensures each span is also black */
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
    .autocomplete-suggestions {
        position: absolute;
        border: 1px solid #ccc;
        background-color: white;
        z-index: 999;
        max-height: 150px;
        overflow-y: auto;
        width: 100%;
        border-radius: 4px;
    }

    .autocomplete-suggestion {
        padding: 8px;
        cursor: pointer;
    }

    .autocomplete-suggestion:hover {
        background-color: #f0f0f0;
    }

    .search-container {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        position: relative;
    }

    .search-container > div {
        position: relative;
        flex: 1;
        min-width: 200px;
    }

    input[type="text"] {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
    }

    .chart-wrapper {
  position: relative;
  width: 100%;
  min-height: 256px;
}

.chart-controls {
  margin-bottom: 1.5rem;
}

.dark .chartjs-render-monitor {
  filter: brightness(0.8) contrast(1.2);
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .chart-wrapper {
    min-height: 300px;
  }
}




.loader {
  border-top-color: #3b82f6;
  border-bottom-color: #3b82f6;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.autocomplete-suggestions {
  position: absolute;
  z-index: 1000;
  background: white;
  border: 1px solid #ddd;
  max-height: 200px;
  overflow-y: auto;
  width: auto;
  min-width: 200px;
}

.autocomplete-suggestion {
  padding: 8px 12px;
  cursor: pointer;
}

.autocomplete-suggestion:hover {
  background-color: #f0f0f0;
}

.dark .autocomplete-suggestions {
  background: #374151;
  border-color: #4b5563;
}

.dark .autocomplete-suggestion {
  color: white;
}

.dark .autocomplete-suggestion:hover {
  background-color: #4b5563;
}
@media (prefers-color-scheme: dark) {
  .year-label {
    color: black !important;
  }
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
        Annual Recap 
            </h1>
        </div>
        <!-- Filters -->

<div class="dashboard-container">
<div class="search-controls bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-6">
  git fetch origin
  <!-- Rest of your existing search controls -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <div>
        <label for="recap_fournisseur" class="block text-sm font-medium dark:text-white">Fournisseur</label>
        <div class="relative">
            <input type="text" id="recap_fournisseur" placeholder="Search..." 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600">
            <div id="fournisseur_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
        </div>
    </div>
    
    <div>
        <label for="recap_product" class="block text-sm font-medium dark:text-white">Product</label>
        <div class="relative">
            <input type="text" id="recap_product" placeholder="Search..." 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600">
            <div id="product_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
        </div>
    </div>
    
    <div>
        <label for="recap_client" class="block text-sm font-medium dark:text-white">Client</label>
        <div class="relative">
            <input type="text" id="recap_client" placeholder="Search..." 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600">
            <div id="client_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
        </div>
    </div>
    
    <div>
        <label for="recap_zone" class="block text-sm font-medium dark:text-white">Zone</label>
        <div class="relative">
            <input type="text" id="recap_zone" placeholder="Search..." 
                   class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600">
            <div id="zone_suggestions" class="autocomplete-suggestions absolute z-10 w-full mt-1 hidden"></div>
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
  <!-- Search Controls -->


  <!-- Data Tables -->
<div class="table-wrapper mt-6">
  <!-- Year 2022 -->
  <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-6" data-year="2022">
        <h2 class="text-lg font-semibold p-4 dark:text-white text-center">Year 2022</h2>

    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse text-sm text-left dark:text-white">
        <thead>
          <tr class="table-header dark:bg-gray-700">
            <th onclick="sort2022Table('MONTH')" class="border px-4 py-2">Month</th>
            <th onclick="sort2022Table('TOTAL')" class="border px-4 py-2">Total</th>
            <th onclick="sort2022Table('MARGE')" class="border px-4 py-2">Marge (%)</th>
          </tr>
        </thead>
        <tbody id="table-body-2022" class="dark:bg-gray-800">
          <tr>
            <td colspan="3" class="text-center py-4">
              <div class="loader animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Year 2023 -->
  <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-6" data-year="2023">
        <h2 class="text-lg font-semibold p-4 dark:text-white text-center">Year 2023</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse text-sm text-left dark:text-white">
        <thead>
          <tr class="table-header dark:bg-gray-700">
            <th onclick="sort2023Table('MONTH')" class="border px-4 py-2">Month</th>
            <th onclick="sort2023Table('TOTAL')" class="border px-4 py-2">Total</th>
            <th onclick="sort2023Table('MARGE')" class="border px-4 py-2">Marge (%)</th>
          </tr>
        </thead>
        <tbody id="table-body-2023" class="dark:bg-gray-800">
          <tr>
            <td colspan="3" class="text-center py-4">
              <div class="loader animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Year 2024 -->
  <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-6" data-year="2024">
        <h2 class="text-lg font-semibold p-4 dark:text-white text-center">Year 2024</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse text-sm text-left dark:text-white">
        <thead>
          <tr class="table-header dark:bg-gray-700">
            <th onclick="sort2024Table('MONTH')" class="border px-4 py-2">Month</th>
            <th onclick="sort2024Table('TOTAL')" class="border px-4 py-2">Total</th>
            <th onclick="sort2024Table('MARGE')" class="border px-4 py-2">Marge (%)</th>
          </tr>
        </thead>
        <tbody id="table-body-2024" class="dark:bg-gray-800">
          <tr>
            <td colspan="3" class="text-center py-4">
              <div class="loader animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Year 2025 -->
  <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 mb-6" data-year="2025">
        <h2 class="text-lg font-semibold p-4 dark:text-white text-center">Year 2025</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full border-collapse text-sm text-left dark:text-white">
        <thead>
          <tr class="table-header dark:bg-gray-700">
            <th onclick="sort2025Table('MONTH')" class="border px-4 py-2">Month</th>
            <th onclick="sort2025Table('TOTAL')" class="border px-4 py-2">Total</th>
            <th onclick="sort2025Table('MARGE')" class="border px-4 py-2">Marge (%)</th>
          </tr>
        </thead>
        <tbody id="table-body-2025" class="dark:bg-gray-800">
          <tr>
            <td colspan="3" class="text-center py-4">
              <div class="loader animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
 </div>

  <!-- Charts Section -->


  <div class="chart-container mt-8">
  <div class="chart-controls bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-6">
    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
      <div>
        <label for="chart-type" class="block text-sm font-medium dark:text-white">Chart Type</label>
        <select id="chart-type" class="w-full p-2 border rounded dark:bg-gray-700 dark:border-gray-600">
        <option value="line">Line Chart</option>
          <option value="bar">Bar Chart</option>
          <option value="pie">Pie Chart</option>
          <option value="doughnut">Doughnut Chart</option>
          <option value="radar">Radar Chart</option>
        </select>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-6">
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md h-full">
      <h3 class="text-lg font-semibold mb-4 dark:text-white">Total Revenue</h3>
      <div class="chart-wrapper relative h-64 w-full">
        <canvas id="totalChart"></canvas>
      </div>
    </div>
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md h-full">
      <h3 class="text-lg font-semibold mb-4 dark:text-white">Margin Percentage</h3>
      <div class="chart-wrapper relative h-64 w-full">
        <canvas id="margeChart"></canvas>
      </div>
    </div>
    <div id="qtyChartContainer" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md h-full hidden">
      <h3 class="text-lg font-semibold mb-4 dark:text-white">Quantity</h3>
      <div class="chart-wrapper relative h-64 w-full">
        <canvas id="qtyChart"></canvas>
      </div>
    </div>
  </div>
</div>



</div>


<br>


<!-- Add this after your tables section -->
<!-- Add this after your tables section -->


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>


// Update the appState to include selected years
const appState = {
  selected: {
    fournisseur: null,
    product: null, // Added product
    client: null,
    zone: null,
    years: []
  },
  isLoading: false,
  cache: new Map(),
  debounceTimer: null
};

// DOM Elements
const elements = {
  applyBtn: document.getElementById('applyFilters'),
  resetBtn: document.getElementById('resetFilters'),
  inputs: {
    fournisseur: document.getElementById('recap_fournisseur'),
    product: document.getElementById('recap_product'), // Added product input
    client: document.getElementById('recap_client'),
    zone: document.getElementById('recap_zone')
  },
  suggestionBoxes: {
    fournisseur: document.getElementById('fournisseur_suggestions'),
    product: document.getElementById('product_suggestions'), // Added product suggestions
    client: document.getElementById('client_suggestions'),
    zone: document.getElementById('zone_suggestions')
  },
  yearCheckboxes: document.querySelectorAll('.year-checkbox')
};

// Constants
const API_ENDPOINTS = {
  fetchData: 'http://192.168.1.94:5000/fetchFournisseurDataByYear',
  listFournisseur: 'http://192.168.1.94:5000/listfournisseur',
  listProduct: 'http://192.168.1.94:5000/listproduct', // Added product endpoint
  listClient: 'http://192.168.1.94:5000/listclient',
  listZone: 'http://192.168.1.94:5000/listregion'
};
const monthNames = {
  '01': 'Janvier', '02': 'Février', '03': 'Mars', '04': 'Avril',
  '05': 'Mai', '06': 'Juin', '07': 'Juillet', '08': 'Août',
  '09': 'Septembre', '10': 'Octobre', '11': 'Novembre', '12': 'Décembre'
};

// Initialize the application
function init() {
  setupEventListeners();
  loadInitialData();
}

// Set up all event listeners
function setupEventListeners() {
  // Search input handlers with debouncing
  Object.entries(elements.inputs).forEach(([type, input]) => {
    input.addEventListener('input', debounce(() => handleSearchInput(type), 300));
    
    // Prevent hiding suggestions when clicking inside the input
    input.addEventListener('mousedown', (e) => {
      e.stopPropagation();
    });
  });

  // Year checkbox handlers
  elements.yearCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', handleYearSelection);
  });

  // Apply/reset buttons
  elements.applyBtn.addEventListener('click', fetchAndDisplayData);
  elements.resetBtn.addEventListener('click', resetFilters);

  // Enhanced click outside handler
  document.addEventListener('click', (e) => {
    let isSuggestion = false;
    let isInput = false;
    
    Object.values(elements.suggestionBoxes).forEach(box => {
      if (box.contains(e.target)) {
        isSuggestion = true;
      }
    });
    
    Object.values(elements.inputs).forEach(input => {
      if (input === e.target || input.contains(e.target)) {
        isInput = true;
      }
    });
    
    if (!isSuggestion && !isInput) {
      hideAllSuggestions();
    }
  });
}

function handleYearSelection() {
  const selectedYears = [];
  elements.yearCheckboxes.forEach(checkbox => {
    if (checkbox.checked) {
      selectedYears.push(parseInt(checkbox.value));
    }
  });
  
  if (selectedYears.length === 0) {
    elements.yearCheckboxes.forEach(checkbox => {
      checkbox.checked = true;
    });
    appState.selected.years = [2022, 2023, 2024, 2025];
  } else {
    appState.selected.years = selectedYears;
  }
}

function debounce(func, delay) {
  return function() {
    clearTimeout(appState.debounceTimer);
    appState.debounceTimer = setTimeout(() => func.apply(this, arguments), delay);
  };
}

function clearPreviousSearches(currentType) {
  Object.keys(elements.inputs).forEach(type => {
    if (type !== currentType) {
      elements.inputs[type].value = '';
      appState.selected[type] = null;
      hideSuggestions(type);
    }
  });
}

async function handleSearchInput(type) {
  const input = elements.inputs[type];
  const value = input.value.trim().toLowerCase();
  const suggestionBox = elements.suggestionBoxes[type];

  if (value && !appState.selected[type]) {
    clearPreviousSearches(type);
  }

  if (!value) {
    hideSuggestions(type);
    appState.selected[type] = null;
    return;
  }

  try {
    const endpoint = API_ENDPOINTS[`list${type.charAt(0).toUpperCase() + type.slice(1)}`];
    const response = await fetch(endpoint);
    const data = await response.json();
    
    const filtered = data.filter(item => 
      item.toLowerCase().includes(value)
    ).slice(0, 10);

    showSuggestions(type, filtered);
  } catch (error) {
    console.error(`Error fetching ${type} suggestions:`, error);
  }
}

function showSuggestions(type, items) {
  const box = elements.suggestionBoxes[type];
  box.innerHTML = '';

  if (items.length === 0) {
    box.innerHTML = '<div class="autocomplete-suggestion">No results found</div>';
  } else {
    items.forEach(item => {
      const div = document.createElement('div');
      div.className = 'autocomplete-suggestion';
      div.textContent = item;
      
      div.addEventListener('mousedown', (e) => {
        e.preventDefault();
        selectSuggestion(type, item);
      });
      
      box.appendChild(div);
    });
  }

  box.classList.remove('hidden');
}

function hideSuggestions(type) {
  elements.suggestionBoxes[type].classList.add('hidden');
}

function hideAllSuggestions() {
  Object.values(elements.suggestionBoxes).forEach(box => {
    box.classList.add('hidden');
  });
}

function selectSuggestion(type, value) {
  elements.inputs[type].value = value;
  appState.selected[type] = value;
  hideSuggestions(type);
  elements.inputs[type].focus();
}

function resetFilters() {
  Object.values(elements.inputs).forEach(input => {
    input.value = '';
  });
  
  elements.yearCheckboxes.forEach(checkbox => {
    checkbox.checked = true;
  });

  appState.selected = {
    fournisseur: null,
    product: null, // Reset product
    client: null,
    zone: null,
    years: [2022, 2023, 2024, 2025]
  };

  hideAllSuggestions();
  appState.cache.clear();
  fetchAndDisplayData();
}

async function fetchAndDisplayData() {
  if (appState.isLoading) return;

  appState.isLoading = true;
  document.querySelectorAll('.table-container').forEach(container => {
    container.style.display = 'none';
  });
  showLoadingOverlay();

  try {
    const cacheKey = JSON.stringify(appState.selected);

    if (appState.cache.has(cacheKey)) {
      updateUI(appState.cache.get(cacheKey));
      return;
    }

    const params = new URLSearchParams();
    if (appState.selected.fournisseur) params.append('fournisseur', appState.selected.fournisseur);
    if (appState.selected.product) params.append('product', appState.selected.product); // Added product
    if (appState.selected.client) params.append('client', appState.selected.client);
    if (appState.selected.zone) params.append('zone', appState.selected.zone);
    appState.selected.years.forEach(year => params.append('years', year));

    const response = await fetch(`${API_ENDPOINTS.fetchData}?${params}`);
    const data = await response.json();

    appState.cache.set(cacheKey, data);
    updateUI(data);
  } catch (error) {
    console.error("Error fetching data:", error);
    showAllErrors();
  } finally {
    appState.isLoading = false;
    hideLoadingOverlay();
  }
}
function updateUI(data) {
  document.querySelectorAll('.table-container').forEach(container => {
    container.style.display = 'none';
  });

  Object.keys(data).forEach(year => {
    const tableBody = document.getElementById(`table-body-${year}`);
    const tableContainer = document.querySelector(`.table-container[data-year="${year}"]`);
    
    if (tableBody && tableContainer) {
      tableContainer.style.display = 'block';
      updateYearTable(tableBody, data[year]);
    }
  });

  createChartsFromTables();
}

// Initialize the app
document.addEventListener('DOMContentLoaded', init);











// Modify the showAllLoaders and showAllErrors functions to only affect selected years
function showAllLoaders() {
  appState.selected.years.forEach(year => {
    const tableBody = document.getElementById(`table-body-${year}`);
    if (tableBody) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="3" class="text-center py-4">
            <div class="loader animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
          </td>
        </tr>
      `;
    }
  });
}

function showAllErrors() {
  appState.selected.years.forEach(year => {
    const tableBody = document.getElementById(`table-body-${year}`);
    if (tableBody) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="3" class="text-center py-4 text-red-500">
            Error loading data. Please try again.
          </td>
        </tr>
      `;
    }
  });
}


function updateYearTable(tableBody, yearData) {
    tableBody.innerHTML = '';
    const showQty = appState.selected.product !== null; // Check if product filter is applied

    if (!yearData || yearData.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="${showQty ? 4 : 3}" class="text-center py-4 text-gray-500">
                    No data available
                </td>
            </tr>
        `;
        return;
    }

    // Add TOTAL ANNUEL row at the top
    const totalRow = yearData.find(item => item.MONTH === null);
    if (totalRow) {
        const row = document.createElement('tr');
        row.className = 'border-b dark:border-gray-700';

        row.innerHTML = `
            <td colspan="${showQty ? 4 : 3}" class="text-center font-bold bg-gray-100 dark:bg-gray-700 py-2">
                TOTAL ANNUEL: ${formatNumber(totalRow.TOTAL)} (${totalRow.MARGE.toFixed(2)}%)
                ${showQty ? ` | QTY: ${formatNumber(totalRow.QTY)}` : ''}
            </td>
        `;
        row.classList.add('annual-total-row');
        tableBody.appendChild(row);
    }

    // Add the rest of the rows
    yearData.filter(item => item.MONTH !== null).forEach(item => {
        const row = document.createElement('tr');
        row.className = 'border-b dark:border-gray-700';

        const monthName = monthNames[item.MONTH] || item.MONTH;

        row.innerHTML = `
            <td class="border px-4 py-2">${monthName}</td>
            <td class="border px-4 py-2">${formatNumber(item.TOTAL)}</td>
            ${showQty ? `<td class="border px-4 py-2">${formatNumber(item.QTY)}</td>` : ''}
            <td class="border px-4 py-2">${item.MARGE.toFixed(2)}%</td>
        `;
        tableBody.appendChild(row);
    });

    // Update table headers to match column count
    const table = tableBody.closest('table');
    if (table) {
        const headerRow = table.querySelector('thead tr');
        if (headerRow) {
            headerRow.innerHTML = `
                <th class="px-4 py-2 cursor-pointer" onclick="sort${tableBody.id.split('-')[2]}Table('MONTH')">Mois</th>
                <th class="px-4 py-2 cursor-pointer" onclick="sort${tableBody.id.split('-')[2]}Table('TOTAL')">Total</th>
                ${showQty ? '<th class="px-4 py-2">Quantité</th>' : ''}
                <th class="px-4 py-2 cursor-pointer" onclick="sort${tableBody.id.split('-')[2]}Table('MARGE')">Marge</th>
            `;
        }
    }
}
function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num);
}

function createSortFunction(year) {
    return function(column) {
        const tableBody = document.getElementById(`table-body-${year}`);
        const rows = Array.from(tableBody.querySelectorAll('tr:not(.annual-total-row)'));
        const showQty = appState.selected.product !== null;
        
        rows.sort((a, b) => {
            let columnIndex;
            if (column === 'MONTH') columnIndex = 0;
            else if (column === 'TOTAL') columnIndex = 1;
            else if (column === 'QTY' && showQty) columnIndex = 2;
            else if (column === 'MARGE') columnIndex = showQty ? 3 : 2;
            
            const aValue = a.cells[columnIndex].textContent;
            const bValue = b.cells[columnIndex].textContent;
            
            if (column === 'MONTH') {
                const monthNumbers = Object.entries(monthNames).reduce((acc, [num, name]) => {
                    acc[name] = num;
                    return acc;
                }, {});
                return parseInt(monthNumbers[aValue]) - parseInt(monthNumbers[bValue]);
            } else {
                const aNum = parseFloat(aValue.replace(/[^\d,-]/g, '').replace(',', '.'));
                const bNum = parseFloat(bValue.replace(/[^\d,-]/g, '').replace(',', '.'));
                return aNum - bNum;
            }
        });
        
        const annualTotalRow = tableBody.querySelector('.annual-total-row');
        tableBody.innerHTML = '';
        rows.forEach(row => tableBody.appendChild(row));
        if (annualTotalRow) tableBody.appendChild(annualTotalRow);
    };
}

const sort2022Table = createSortFunction('2022');
const sort2023Table = createSortFunction('2023');
const sort2024Table = createSortFunction('2024');
const sort2025Table = createSortFunction('2025');

// Initial load
function showLoadingOverlay() {
  document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoadingOverlay() {
  document.getElementById('loadingOverlay').classList.add('hidden');
}




//CHART PART
// CHART PART
let totalChart = null;
let margeChart = null;
let qtyChart = null;

function createChartsFromTables() {
  const chartType = document.getElementById('chart-type').value;
  const showQty = appState.selected.product !== null;

  const allData = [];
  for (let year = 2022; year <= 2025; year++) {
    const tableBody = document.getElementById(`table-body-${year}`);
    if (tableBody) {
      const rows = tableBody.querySelectorAll('tr:not(.annual-total-row)');
      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        // Handle both cases (3 or 4 columns)
        if (cells.length === 3 || cells.length === 4) {
          const monthIndex = 0;
          const totalIndex = 1;
          const qtyIndex = 2;
          const margeIndex = showQty ? 3 : 2;
          
          const dataItem = {
            year: year.toString(),
            month: cells[monthIndex].textContent.trim(),
            total: parseFloat(cells[totalIndex].textContent.replace(/[^\d,.-]/g, '').replace(',', '.')),
            marge: parseFloat(cells[margeIndex].textContent.replace('%', ''))
          };
          
          if (showQty) {
            dataItem.qty = parseFloat(cells[qtyIndex].textContent.replace(/[^\d,.-]/g, '').replace(',', '.'));
          }
          
          allData.push(dataItem);
        }
      });
    }
  }

  updateTotalChart(allData, chartType);
  updateMargeChart(allData, chartType);
  
  // Only show QTY chart if product is selected
  if (showQty) {
    updateQtyChart(allData, chartType);
    document.getElementById('qtyChartContainer').classList.remove('hidden');
  } else {
    if (qtyChart) qtyChart.destroy();
    document.getElementById('qtyChartContainer').classList.add('hidden');
  }
}

function updateTotalChart(data, chartType) {
  const ctx = document.getElementById('totalChart');
  if (totalChart) totalChart.destroy();

  const labels = data.map(item => `${item.month} ${item.year}`);
  const totals = data.map(item => item.total);
  const backgroundColors = data.map(item => getChartColor(item.year, 0.7));
  const borderColors = data.map(item => getChartColor(item.year, 1));

  const dataset = {
    label: 'Total Revenue',
    data: totals,
    backgroundColor: backgroundColors,
    borderColor: borderColors,
    borderWidth: 1
  };

  totalChart = new Chart(ctx, {
    type: chartType,
    data: {
      labels: labels,
      datasets: chartType === 'pie' || chartType === 'doughnut' ? [dataset] : [dataset]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' },
        tooltip: {
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) label += ': ';
              return label + formatNumber(context.raw);
            }
          }
        }
      },
      scales: chartType === 'pie' || chartType === 'doughnut' ? {} : {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return formatNumber(value);
            }
          }
        }
      }
    }
  });
}

function updateMargeChart(data, chartType) {
  const ctx = document.getElementById('margeChart');
  if (margeChart) margeChart.destroy();

  const labels = data.map(item => `${item.month} ${item.year}`);
  const marges = data.map(item => item.marge);
  const backgroundColors = data.map(item => getChartColor(item.year, 0.7));
  const borderColors = data.map(item => getChartColor(item.year, 1));

  const dataset = {
    label: 'Margin %',
    data: marges,
    backgroundColor: backgroundColors,
    borderColor: borderColors,
    borderWidth: 1
  };

  margeChart = new Chart(ctx, {
    type: chartType,
    data: {
      labels: labels,
      datasets: chartType === 'pie' || chartType === 'doughnut' ? [dataset] : [dataset]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' },
        tooltip: {
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) label += ': ';
              return label + context.raw.toFixed(2) + '%';
            }
          }
        }
      },
      scales: chartType === 'pie' || chartType === 'doughnut' ? {} : {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return value + '%';
            }
          }
        }
      }
    }
  });
}

function updateQtyChart(data, chartType) {
  const ctx = document.getElementById('qtyChart');
  if (qtyChart) qtyChart.destroy();

  const labels = data.map(item => `${item.month} ${item.year}`);
  const quantities = data.map(item => item.qty);
  const backgroundColors = data.map(item => getChartColor(item.year, 0.7));
  const borderColors = data.map(item => getChartColor(item.year, 1));

  const dataset = {
    label: 'Quantity',
    data: quantities,
    backgroundColor: backgroundColors,
    borderColor: borderColors,
    borderWidth: 1
  };

  qtyChart = new Chart(ctx, {
    type: chartType,
    data: {
      labels: labels,
      datasets: [dataset]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `Quantity: ${formatNumber(context.raw)}`;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return formatNumber(value);
            }
          }
        }
      }
    }
  });
}

function getChartColor(year, opacity) {
  const colors = {
    '2022': `rgba(54, 162, 235, ${opacity})`,
    '2023': `rgba(255, 99, 132, ${opacity})`,
    '2024': `rgba(75, 192, 192, ${opacity})`,
    '2025': `rgba(153, 102, 255, ${opacity})`
  };
  return colors[year] || `rgba(201, 203, 207, ${opacity})`;
}

// Event listener for chart type change
document.getElementById('chart-type').addEventListener('change', createChartsFromTables);

function formatNumber(value) {
  return value.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
// Dark Mode Toggle
const themeToggle = document.getElementById('themeToggle');
const htmlElement = document.documentElement;

const savedDarkMode = localStorage.getItem('darkMode');
if (savedDarkMode === 'true') {
    htmlElement.classList.add('dark');
    themeToggle.checked = true;
}

themeToggle.addEventListener('change', () => {
    htmlElement.classList.toggle('dark');
    const isDarkMode = htmlElement.classList.contains('dark');
    localStorage.setItem('darkMode', isDarkMode);
});
</script>

</body>

</html>