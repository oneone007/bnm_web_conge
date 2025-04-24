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
    <!-- <link rel="stylesheet" href="recapvente.css"> -->

<style>

body {
    font-family: 'Inter', sans-serif;
}


.table-container {
    max-height: 400px;
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
grid-template-columns: repeat(3, minmax(250px, 1fr)); /* 3 columns per row */
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





.selected-row {
background-color: #d1d5db !important; /* Light gray */
font-weight: bold;
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
        Reacap Vente 
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

th {
    position: relative;
}

.resizer {
    background: transparent;
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    height: 100%;
    cursor: col-resize;
    z-index: 1;
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
                <div id="pagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="firstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="prevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="pageIndicator"></span>
    <button id="nextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="lastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
</div>            </div>


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
                <div id="productPagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="firstProductPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="prevProductPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="productPageIndicator"></span>
    <button id="nextProductPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="lastProductPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
</div>
           </div>
        </div>
        <script>
function makeTableColumnsResizable(table) {
    const cols = table.querySelectorAll("th");
    const tableContainer = table.parentElement;

    cols.forEach((col) => {
        // Create a resizer handle
        const resizer = document.createElement("div");
        resizer.classList.add("resizer");
        col.style.position = "relative";
        resizer.style.width = "5px";
        resizer.style.height = "100%";
        resizer.style.position = "absolute";
        resizer.style.top = "0";
        resizer.style.right = "0";
        resizer.style.cursor = "col-resize";
        resizer.style.userSelect = "none";
        resizer.style.zIndex = "10";

        col.appendChild(resizer);

        let x = 0;
        let w = 0;

        resizer.addEventListener("mousedown", (e) => {
            x = e.clientX;
            w = col.offsetWidth;

            document.addEventListener("mousemove", mouseMoveHandler);
            document.addEventListener("mouseup", mouseUpHandler);
        });

        const mouseMoveHandler = (e) => {
            const dx = e.clientX - x;
            col.style.width = `${w + dx}px`;
        };

        const mouseUpHandler = () => {
            document.removeEventListener("mousemove", mouseMoveHandler);
            document.removeEventListener("mouseup", mouseUpHandler);
        };
    });
}

// Wait for the DOM to load before applying resizable
document.addEventListener("DOMContentLoaded", () => {
    const tables = document.querySelectorAll(".table-container table");
tables.forEach((table) => makeTableColumnsResizable(table));
});
</script>

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
                <div id="zonePagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="firstzonePage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="prevzonePage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="zonePageIndicator"></span>
    <button id="nextzonePage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="lastzonePage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
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
                <div id="clpagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="clfirstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="clprevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="clpageIndicator"></span>
    <button id="clnextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="cllastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
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
                <div id="oppagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="opfirstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="opprevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="oppageIndicator"></span>
    <button id="opnextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="oplastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
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
                <div id="bpagination" class="mt-4 flex justify-center items-center gap-4 text-sm dark:text-white">
    <button id="bfirstPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">First</button>
    <button id="bprevPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Previous</button>
    <span id="bpageIndicator"></span>
    <button id="bnextPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Next</button>
    <button id="blastPage" class="px-3 py-1 border rounded dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Last</button>
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
                    
                    const response = await fetch(`http://192.168.1.94:5000/fetchTotalrecapData?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000012`);

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

    const downloadUrl = `http://192.168.1.94:5000/download-totalrecap-excel?start_date=${startDate}&end_date=${endDate}&ad_org_id=1000012`;
    window.location.href = downloadUrl;  // Triggers file download
});


let currentPage = 1;
const rowsPerPage = 10;
let fullData = [];


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
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

                if (!startDate || !endDate) return;

                const url = new URL("http://192.168.1.94:5000/fetchFournisseurData");
                url.searchParams.append("start_date", startDate);
                url.searchParams.append("end_date", endDate);
                url.searchParams.append("ad_org_id", "1000012"); 
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




   
function updateFournisseurTable(data) {
    const tableBody = document.getElementById("recap-frnsr-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Save data globally for pagination
    fullData = data;

    // Separate total row
    const totalRow = data.find(row => row.FOURNISSEUR === "Total");
    const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    currentPage = Math.min(currentPage, totalPages);

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const pageData = filteredData.slice(start, end);

    // Append total row first
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

    // Then add paginated rows
    pageData.forEach(row => {
        tableBody.innerHTML += `
            <tr class="dark:bg-gray-700">
                <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.TOTAL)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.QTY)}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumberf(row.MARGE)}%</td>
            </tr>
        `;
    });

    // Update pagination text
    document.getElementById("pageIndicator").textContent = `Page ${currentPage} of ${totalPages}`;
}


document.getElementById("firstPage").addEventListener("click", () => {
    currentPage = 1;
    updateFournisseurTable(fullData);
});

document.getElementById("prevPage").addEventListener("click", () => {
    if (currentPage > 1) currentPage--;
    updateFournisseurTable(fullData);
});

document.getElementById("nextPage").addEventListener("click", () => {
    const totalPages = Math.ceil((fullData.filter(r => r.FOURNISSEUR !== "Total").length) / rowsPerPage);
    if (currentPage < totalPages) currentPage++;
    updateFournisseurTable(fullData);
});

document.getElementById("lastPage").addEventListener("click", () => {
    currentPage = Math.ceil((fullData.filter(r => r.FOURNISSEUR !== "Total").length) / rowsPerPage);
    updateFournisseurTable(fullData);
});


document.getElementById("download-fournisseur").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL("http://192.168.1.94:5000/download-fournisseur-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012"); // Added ad_org_id parameter

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

            let currentProductPage = 1;
const productRowsPerPage = 10;
let fullProductData = [];

document.addEventListener("DOMContentLoaded", () => {
    const productTableBody = document.getElementById("recap-prdct-table");

    productTableBody.addEventListener("click", function (event) {
        let row = event.target.closest("tr");
        if (!row || row.id === "loading-row") return;

        // Toggle selection
        row.classList.toggle("selected-row");

        // Get all selected rows
        let selectedProducts = [...document.querySelectorAll("#recap-prdct-table .selected-row")]
            .map(row => row.cells[0].textContent.trim());

        // Update the input field (simulate manual typing)
        document.getElementById("recap_product").value = selectedProducts.join(", ");

        // Manually trigger the input event to simulate user search
        document.getElementById("recap_product").dispatchEvent(new Event("input"));

        // Auto-trigger full search (same as manual input)
        fetchProductRecap();
    });
});

// Fetch data for product table
async function fetchProductRecap() {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchProductData");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012"); 
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

// Update product table with pagination
function updateProductTable(data) {
    const tableBody = document.getElementById("recap-prdct-table");
    tableBody.innerHTML = ""; // Clear table before inserting new rows

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="4" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Save data globally for pagination
    fullProductData = data;

    // Separate the "Total" row
    const totalRow = data.find(row => row.PRODUIT === "Total");
    const filteredData = data.filter(row => row.PRODUIT !== "Total");

    const totalPages = Math.ceil(filteredData.length / productRowsPerPage);
    currentProductPage = Math.min(currentProductPage, totalPages);

    const start = (currentProductPage - 1) * productRowsPerPage;
    const end = start + productRowsPerPage;
    const pageData = filteredData.slice(start, end);

    const fragment = document.createDocumentFragment();

    // Add total row first
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

    // Add paginated rows
    pageData.forEach(row => {
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

    // Update pagination text
    document.getElementById("productPageIndicator").textContent = `Page ${currentProductPage} of ${totalPages}`;
}

// Pagination controls
document.getElementById("firstProductPage").addEventListener("click", () => {
    currentProductPage = 1;
    updateProductTable(fullProductData);
});

document.getElementById("prevProductPage").addEventListener("click", () => {
    if (currentProductPage > 1) currentProductPage--;
    updateProductTable(fullProductData);
});

document.getElementById("nextProductPage").addEventListener("click", () => {
    const totalPages = Math.ceil(fullProductData.filter(r => r.PRODUIT !== "Total").length / productRowsPerPage);
    if (currentProductPage < totalPages) currentProductPage++;
    updateProductTable(fullProductData);
});

document.getElementById("lastProductPage").addEventListener("click", () => {
    currentProductPage = Math.ceil(fullProductData.filter(r => r.PRODUIT !== "Total").length / productRowsPerPage);
    updateProductTable(fullProductData);
});

// Format number for product table with thousand separators & two decimals
function formatNumberp(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}



document.getElementById("download-product-excel").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();
    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL("http://192.168.1.94:5000/download-product-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012"); // Added ad_org_id parameter

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

// Pagination state for zone
let currentZonePage = 1;
let totalZonePages = 1;
const itemsPerZonePage = 10; // Number of items to display per page

// Fetch data when filters are applied for zone recap
async function fetchZoneRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchZoneRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012");
    url.searchParams.append("page", page);  // Add the page parameter for pagination
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
        return data; // Ensure function returns data
    } catch (error) {
        console.error("Error fetching zone recap data:", error);
    }
}

// Update table with fetched zone data
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

    // Append remaining rows (apply pagination by slicing the data)
    const paginatedData = filteredData.slice((currentZonePage - 1) * itemsPerZonePage, currentZonePage * itemsPerZonePage);
    paginatedData.forEach(row => {
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

    // Update pagination
    updatePagination(filteredData.length);
}

// Update pagination controls for zone
function updatePagination(totalItems) {
    totalZonePages = Math.ceil(totalItems / itemsPerZonePage);

    const zonePageIndicator = document.getElementById("zonePageIndicator");
    zonePageIndicator.textContent = `Page ${currentZonePage} of ${totalZonePages}`;

    document.getElementById("firstzonePage").disabled = currentZonePage === 1;
    document.getElementById("prevzonePage").disabled = currentZonePage === 1;
    document.getElementById("nextzonePage").disabled = currentZonePage === totalZonePages;
    document.getElementById("lastzonePage").disabled = currentZonePage === totalZonePages;
}

// Handle pagination button clicks for zone
document.getElementById("firstzonePage").addEventListener("click", () => changeZonePage(1));
document.getElementById("prevzonePage").addEventListener("click", () => changeZonePage(currentZonePage - 1));
document.getElementById("nextzonePage").addEventListener("click", () => changeZonePage(currentZonePage + 1));
document.getElementById("lastzonePage").addEventListener("click", () => changeZonePage(totalZonePages));

// Change page for zone
function changeZonePage(page) {
    if (page < 1 || page > totalZonePages) return;
    currentZonePage = page;
    fetchZoneRecap(currentZonePage); // Fetch data for the new page
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
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    const url = new URL(`http://192.168.1.94:5000/${endpoint}`);
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012"); // Added ad_org_id parameter

    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
    if (client) url.searchParams.append("client", client);
    if (operateur) url.searchParams.append("operateur", operateur);
    if (bccb) url.searchParams.append("bccb", bccb);
    if (zone) url.searchParams.append("zone", zone);

    window.location.href = url;
}


let currentClientPage = 1;
let totalClientPages = 1;
const itemsPerClientPage = 10; // Number of items to display per page


async function fetchClientRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchClientRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012");
    url.searchParams.append("page", page);  // Add the page parameter for pagination
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
        return data; // Ensure function returns data
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

    // Append remaining rows (apply pagination by slicing the data)
    const paginatedData = filteredData.slice((currentClientPage - 1) * itemsPerClientPage, currentClientPage * itemsPerClientPage);
    paginatedData.forEach(row => {
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

    // Update pagination
    updateClientPagination(filteredData.length);
}
function updateClientPagination(totalItems) {
    totalClientPages = Math.ceil(totalItems / itemsPerClientPage);

    const clientPageIndicator = document.getElementById("clpageIndicator");
    clientPageIndicator.textContent = `Page ${currentClientPage} of ${totalClientPages}`;

    document.getElementById("clfirstPage").disabled = currentClientPage === 1;
    document.getElementById("clprevPage").disabled = currentClientPage === 1;
    document.getElementById("clnextPage").disabled = currentClientPage === totalClientPages;
    document.getElementById("cllastPage").disabled = currentClientPage === totalClientPages;
}
document.getElementById("clfirstPage").addEventListener("click", () => {
    currentClientPage = 1;
    fetchClientRecap(currentClientPage);
});

document.getElementById("clprevPage").addEventListener("click", () => {
    if (currentClientPage > 1) {
        currentClientPage--;
        fetchClientRecap(currentClientPage);
    }
});

document.getElementById("clnextPage").addEventListener("click", () => {
    if (currentClientPage < totalClientPages) {
        currentClientPage++;
        fetchClientRecap(currentClientPage);
    }
});

document.getElementById("cllastPage").addEventListener("click", () => {
    currentClientPage = totalClientPages;
    fetchClientRecap(currentClientPage);
});


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

let currentOperatorPage = 1;
let totalOperatorPages = 1;
const itemsPerOperatorPage = 10; // Number of items per page for operator recap




async function fetchOperatorRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchOperatorRecap");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("ad_org_id", "1000012");
    url.searchParams.append("page", page);  // Add page parameter for pagination
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
        updateOperatorTable(data);  // Update table with fetched data
        return data; // Return fetched data
    } catch (error) {
        console.error("Error fetching operator recap data:", error);
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

    // Apply pagination by slicing the data
    const paginatedData = filteredData.slice((currentOperatorPage - 1) * itemsPerOperatorPage, currentOperatorPage * itemsPerOperatorPage);
    paginatedData.forEach(row => {
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

    // Update pagination
    updatePaginationop(filteredData.length);
}
function updatePaginationop(totalItems) {
    totalOperatorPages = Math.ceil(totalItems / itemsPerOperatorPage);

    const pageIndicator = document.getElementById("oppageIndicator");
    pageIndicator.textContent = `Page ${currentOperatorPage} of ${totalOperatorPages}`;

    document.getElementById("opfirstPage").disabled = currentOperatorPage === 1;
    document.getElementById("opprevPage").disabled = currentOperatorPage === 1;
    document.getElementById("opnextPage").disabled = currentOperatorPage === totalOperatorPages;
    document.getElementById("oplastPage").disabled = currentOperatorPage === totalOperatorPages;
}

document.getElementById("opfirstPage").addEventListener("click", () => changeOperatorPage(1));
document.getElementById("opprevPage").addEventListener("click", () => changeOperatorPage(currentOperatorPage - 1));
document.getElementById("opnextPage").addEventListener("click", () => changeOperatorPage(currentOperatorPage + 1));
document.getElementById("oplastPage").addEventListener("click", () => changeOperatorPage(totalOperatorPages));

function changeOperatorPage(page) {
    if (page < 1 || page > totalOperatorPages) return;
    currentOperatorPage = page;
    fetchOperatorRecap(currentOperatorPage); // Fetch data for the new page
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



let currentBccbPage = 1;
let totalBccbPages = 1;
const itemsPerBccbPage = 10; // Adjust this to the number of items per page

        
  // Fetch data for Recap by BCCB
  async function fetchBccbRecap(page = 1) {
    const startDate = document.getElementById("start-date").value;
                const endDate = document.getElementById("end-date").value;
                const fournisseur = document.getElementById("recap_fournisseur").value.trim();
                const product = document.getElementById("recap_product").value.trim();
                const client = document.getElementById("recap_client").value.trim();
                const operateur = document.getElementById("recap_operateur").value.trim();
                const bccb = document.getElementById("recap_bccbclient").value.trim();
                const zone = document.getElementById("recap_zone").value.trim();

    if (!startDate || !endDate) return;

    const url = new URL("http://192.168.1.94:5000/fetchBCCBRecapfact"); 
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    url.searchParams.append("page", page);  // Add page parameter for pagination
    
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
        return data; // Return data for pagination
    } catch (error) {
        console.error("Error fetching BCCB recap data:", error);
    }
}
function formatNumberb(value) {
    return parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateString) {
    if (!dateString) return ''; // Return an empty string if no date provided

    const date = new Date(dateString);
    
    // Format the date as 'Wed, 26 Mar 2025'
    const options = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-GB', options); // 'en-GB' for British date format
}
// Debounce function to limit API calls on input change
function debounce(fn, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
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
    const totalRow = data.find(row => row.DOCUMENTNO === "Total");
    const filteredData = data.filter(row => row.DOCUMENTNO !== "Total");

    const paginatedData = filteredData.slice((currentBccbPage - 1) * itemsPerBccbPage, currentBccbPage * itemsPerBccbPage);

    paginatedData.forEach(row => {
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.DOCUMENTNO || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatDate(row.DATEORDERED)}</td>
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

    // Update pagination
    updatePaginationbccb(filteredData.length);
}
function updatePaginationbccb(totalItems) {
    totalBccbPages = Math.ceil(totalItems / itemsPerBccbPage);

    const pageIndicator = document.getElementById("bpageIndicator");
    pageIndicator.textContent = `Page ${currentBccbPage} of ${totalBccbPages}`;

    document.getElementById("bfirstPage").disabled = currentBccbPage === 1;
    document.getElementById("bprevPage").disabled = currentBccbPage === 1;
    document.getElementById("bnextPage").disabled = currentBccbPage === totalBccbPages;
    document.getElementById("blastPage").disabled = currentBccbPage === totalBccbPages;
}


document.getElementById("bfirstPage").addEventListener("click", () => changeBccbPage(1));
document.getElementById("bprevPage").addEventListener("click", () => changeBccbPage(currentBccbPage - 1));
document.getElementById("bnextPage").addEventListener("click", () => changeBccbPage(currentBccbPage + 1));
document.getElementById("blastPage").addEventListener("click", () => changeBccbPage(totalBccbPages));

function changeBccbPage(page) {
    if (page < 1 || page > totalBccbPages) return;
    currentBccbPage = page;
    fetchBccbRecap(currentBccbPage); // Fetch data for the new page
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

    const url = new URL("http://192.168.1.94:5000/fetchBCCBProductfact");
    url.searchParams.append("bccb", bccb);
    url.searchParams.append("ad_org_id", "1000012"); 

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