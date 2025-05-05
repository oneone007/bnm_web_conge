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
Reacap Achat
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
        Reacap Achat 
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

</style>
        <br>
        
        <!-- Table -->
    <!-- Table -->
<div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800 p-6 text-center">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Total Recap</h2>

    <!-- Loading Animation -->
    <div id="loading-animation" class="flex justify-center items-center">
        <!-- <p class="text-lg font-medium text-gray-800 dark:text-white mb-4">Loading...</p> -->
        <div id="lottie-container" style="width: 250px; height: 250px;"></div>
    </div>

    <!-- Result Text (Initially Hidden) -->
    <p id="recap-text" class="text-lg font-medium text-gray-900 dark:text-white hidden">
        Total Chiffre: <span id="chiffre-value" class="font-bold text-indigo-600 dark:text-indigo-400"></span>
    </p>
</div>





     

        <!-- second table remise aauto  -->


        <!-- Pagination -->
        <div class="mt-4 flex justify-center space-x-2" id="pagination"></div>
        
        <div class="download-wrapper">

            <!-- <button id="download-recap-fournisseur-achat-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Fournisseur Download </span> -->
            </button>
            <button id="download-recap-fournisseur-achat-excel" class="button">
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
  </button>  <button id="download-recap-product-achat-excel" class="button">
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
             <!-- <button id="download-recap-product-achat-excel"
                class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
                <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
                <span>Product Download</span>
            </button> -->
        </div>

        <div class="search-container">
            <div>
                <label for="recap_fournisseur">Recap Fournisseur:</label>
                <input type="text" id="recap_fournisseur" placeholder="Search...">
            </div>
   
            <div>
                <label for="recap_product">Recap Product:</label>
                <input type="text" id="recap_product" placeholder="Search...">
            </div>
        
        </div>
        
     <br>
     <div class="table-wrapper">
        <!-- First Table -->
        <div class="table-container rounded-lg bg-white shadow-md dark:bg-gray-800">
            <h2 class="text-lg font-semibold p-4 dark:text-black">RECAP ACHAT FOURNISSEUR</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th data-column="FOURNISSEUR" onclick="sortrecapachatTable('FOURNISSEUR')" class="border px-4 py-2">Fournisseur</th>
                            <th data-column="CHIFFRE" onclick="sortrecapachatTable('CHIFFRE')" class="border px-4 py-2">CHIFFRE</th>
                        </tr>
                    </thead>
                    <tbody id="recap-frnsr-table-achat" class="dark:bg-gray-800">
                        <tr id="loading-row">
                            <td colspan="2" class="text-center p-4">
                                <div id="lottie-container-d" style="width: 290px; height: 200px; margin: auto;">
                                    <div class="loading-spinner"></div>
                                    <p>Loading...</p>
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
            <h2 class="text-lg font-semibold p-4 dark:text-white">RECAP ACHAT PRODUIT</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm text-left dark:text-white">
                    <thead>
                        <tr class="table-header dark:bg-gray-700">
                            <th data-column="PRODUIT" onclick="sortrecpproductTableachat('PRODUIT')" class="border px-4 py-2">Product</th>
                            <th data-column="QTY" onclick="sortrecpproductTableachat('QTY')" class="border px-4 py-2">QTY</th>
                            <th data-column="CHIFFRE" onclick="sortrecpproductTableachat('CHIFFRE')" class="border px-4 py-2">Chiffre</th>
                        </tr>
                    </thead>
                    <tbody id="recap-prdct-table" class="dark:bg-gray-800">
                        <tr id="loading-row">
                            <td colspan="3" class="text-center p-4">
                                <div id="lottie-d" style="width: 290px; height: 200px; margin: auto;">
                                    <div class="loading-spinner"></div>
                                    <p>Loading...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination for Second Table -->
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

        // Example functions for sorting (you can replace them with your sorting logic)
        function sortrecapachatTable(column) {
            console.log('Sorting recapachat table by ' + column);
            // Add your sorting logic here
        }

        function sortrecpproductTableachat(column) {
            console.log('Sorting recap product table by ' + column);
            // Add your sorting logic here
        }
    </script>

    
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
                document.getElementById("start-date").value = "";
                document.getElementById("end-date").value = "";
                document.getElementById("recap_fournisseur").value = "";
                document.getElementById("recap_product").value = "";
            };

            document.addEventListener("DOMContentLoaded", function () {
        const startDate = document.getElementById("start-date");
        const endDate = document.getElementById("end-date");
        const refreshBtn = document.getElementById("refresh-btn");

        

        // Set default value for end date to today
        const today = new Date().toISOString().split("T")[0];
        endDate.value = today;

        function triggerChangeEvent(inputElement) {
            inputElement.focus(); // Simulate user focusing on the field
            inputElement.value = inputElement.value; // Ensure the value is set correctly
            inputElement.dispatchEvent(new Event("input", { bubbles: true })); // Simulate user typing
            inputElement.dispatchEvent(new Event("change", { bubbles: true })); // Simulate user selection
        }

        // When start date is selected, set end date to today if not manually changed
        startDate.addEventListener("change", function () {
            if (!endDate.dataset.changed) {
                endDate.value = today;
                triggerChangeEvent(endDate); // Ensure all listeners detect the change
            }
        });

        // Mark end date as manually changed
        endDate.addEventListener("change", function () {
            endDate.dataset.changed = true;
        });

        // Refresh button action
        refreshBtn.addEventListener("click", function () {
            triggerChangeEvent(endDate); // Make sure refresh triggers the change
        });
    });
// Format number with thousand separators & two decimals


            function hideLoader() {
                const loaderRow = document.getElementById('loading-row');
                if (loaderRow) {
                    loaderRow.remove();
                }
            }

      

            
            // Attach event listeners to date inputs
            document.getElementById("start-date").addEventListener("change", fetchTotalRecapAchat);
            document.getElementById("end-date").addEventListener("change", fetchTotalRecapAchat);


            async function fetchTotalRecapAchat() {
  const startDate = document.getElementById("start-date").value;
  const endDate = document.getElementById("end-date").value;

  if (!startDate || !endDate) return; // Don't fetch until both dates are selected

  // Show loading animation, hide result text
  document.getElementById("loading-animation").classList.remove("hidden");
  document.getElementById("recap-text").classList.add("hidden");

  try {
    const response = await fetch(`http://192.168.1.94:5000/fetchTotalRecapAchat?start_date=${startDate}&end_date=${endDate}`);
    if (!response.ok) throw new Error("Network response was not ok");

    const data = await response.json();

    // If the server response contains 'chiffre', display the result
    if (data.chiffre) {
      const chiffre = formatNumber(data.chiffre);
      document.getElementById("chiffre-value").textContent = `${chiffre} DZD`;
    } else {
      throw new Error("Data structure is missing 'chiffre' field");
    }

    // Hide loading animation, show result text
    document.getElementById("loading-animation").classList.add("hidden");
    document.getElementById("recap-text").classList.remove("hidden");

  } catch (error) {
    console.error("Error fetching total recap achat data:", error);
    document.getElementById("recap-text").textContent = "Échec du chargement des données";
    document.getElementById("recap-text").classList.add("text-red-500");

    // Hide animation in case of error
    document.getElementById("loading-animation").classList.add("hidden");
    document.getElementById("recap-text").classList.remove("hidden");
  }
}

// Format number with thousand separators & two decimals
function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "0.00";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Attach event listeners to date inputs and trigger fetch on change
document.getElementById("start-date").addEventListener("change", fetchTotalRecapAchat);
document.getElementById("end-date").addEventListener("change", fetchTotalRecapAchat);




// Also trigger fetch when any other field changes




// Attach event listeners to relevant filter inputs


// Fetch data when filters are applied for recap achat
document.getElementById("recap_fournisseur").addEventListener("input", fetchFournisseurRecapAchat);
document.getElementById("recap_product").addEventListener("input", fetchFournisseurRecapAchat);
document.getElementById("start-date").addEventListener("input", fetchFournisseurRecapAchat);
document.getElementById("end-date").addEventListener("input", fetchFournisseurRecapAchat);

// Fetch data when filters are applied for recap achat

async function fetchFournisseurRecapAchat() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) return;

    // Construct URL with query parameters
    const url = `http://192.168.1.94:5000/fetchfourisseurRecapAchat?start_date=${startDate}&end_date=${endDate}&fournisseur=${fournisseur}&product=${product}`;

    try {
        showLoader(); // Show loading animation
        const response = await fetch(url); // Fetch data
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json(); // Parse the response as JSON
        console.log("Fetched Data:", data); // Debugging line to check the response
        updateFournisseurRecapAchatTable(data); // Update table with the fetched data
        hideLoader(); // Hide loading animation
    } catch (error) {
        console.error("Error fetching fournisseur recap achat data:", error);
        document.getElementById('recap-frnsr-table-achat').innerHTML =
            `<tr><td colspan="2" class="text-center text-red-500 p-4">Failed to load data</td></tr>`;
        hideLoader(); // Hide loading animation if error occurs
    }
}

// Show loader animation
function showLoader() {
    document.getElementById("recap-frnsr-table-achat").innerHTML = `
        <tr id="loading-row">
            <td colspan="2" class="text-center p-4">Loading...</td>
        </tr>
    `;
}

// Hide loader after fetching data
function hideLoader() {
    const loaderRow = document.getElementById("loading-row");
    if (loaderRow) loaderRow.remove();
}

// Format number with thousand separators & two decimals
function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}



// Update table with fetched data for recap achat
// Update table with fetched data for recap achat
// function updateFournisseurRecapAchatTable(data) {
//     const tableBody = document.getElementById("recap-frnsr-table-achat");
//     tableBody.innerHTML = "";

//     if (!data || data.length === 0) {
//         tableBody.innerHTML = `<tr><td colspan="2" class="text-center p-4">No data available</td></tr>`;
//         return;
//     }

//     // Find and separate the total row
//     const totalRow = data.find(row => row.FOURNISSEUR === "Total");
//     const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

//     // Add the "Total" row with sticky style
//     if (totalRow) {
//         tableBody.innerHTML += `
//             <tr class="bg-gray-200 font-bold sticky top-0 z-10">
//                 <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR}</td>
//                 <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
//             </tr>
//         `;
//     }

//     // Add the filtered data rows
//     filteredData.forEach(row => {
//         const tr = document.createElement("tr");
//         tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
//         tr.innerHTML = `
//             <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
//         `;

//         // Add click event to fill in the search input
//         tr.addEventListener("click", () => {
//             const searchInput = document.getElementById("recap_fournisseur");
//             if (row.FOURNISSEUR) {
//                 searchInput.value = row.FOURNISSEUR;
//                 searchInput.dispatchEvent(new Event("input")); // Trigger input event
//             }
//         });

//         tableBody.appendChild(tr);
//     });
// }

// Update table with fetched data for recap achat
function updateFournisseurRecapAchatTable(data) {
    const tableBody = document.getElementById("recap-frnsr-table-achat");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="2" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Find and separate the total row
    const totalRow = data.find(row => row.FOURNISSEUR === "Total");
    const filteredData = data.filter(row => row.FOURNISSEUR !== "Total");

    // 🔽 Sort by CHIFFRE descending
    filteredData.sort((a, b) => b.CHIFFRE - a.CHIFFRE);

    // Add the "Total" row with sticky style
    if (totalRow) {
        tableBody.innerHTML += `
            <tr class="bg-gray-200 font-bold sticky top-0 z-10">
                <td class="border px-4 py-2 dark:border-gray-600">${totalRow.FOURNISSEUR}</td>
                <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
            </tr>
        `;
    }

    // Add the filtered data rows
    filteredData.forEach(row => {
        const tr = document.createElement("tr");
        tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.FOURNISSEUR || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
        `;

        tr.addEventListener("click", () => {
            const searchInput = document.getElementById("recap_fournisseur");
            if (row.FOURNISSEUR) {
                searchInput.value = row.FOURNISSEUR;
                searchInput.dispatchEvent(new Event("input"));
            }
        });

        tableBody.appendChild(tr);
    });
}


// Event listeners to fetch data when inputs change


// Fetch data when filters are applied for product recap achat
document.getElementById("recap_fournisseur").addEventListener("input", fetchProductRecapAchat);
document.getElementById("recap_product").addEventListener("input", fetchProductRecapAchat);
document.getElementById("start-date").addEventListener("input", fetchProductRecapAchat);
document.getElementById("end-date").addEventListener("input", fetchProductRecapAchat);

// Fetch data when filters are applied for product recap achat
async function fetchProductRecapAchat() {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) return;

    // Construct URL with query parameters
    const url = `http://192.168.1.94:5000/fetchProductRecapAchat?start_date=${startDate}&end_date=${endDate}&fournisseur=${fournisseur}&product=${product}`;

    try {
        showLoader(); // Show loading animation
        const response = await fetch(url); // Fetch data
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json(); // Parse the response as JSON
        console.log("Fetched Data:", data); // Debugging line to check the response
        updateProductRecapAchatTable(data); // Update table with the fetched data
        hideLoader(); // Hide loading animation
    } catch (error) {
        console.error("Error fetching product recap achat data:", error);
        document.getElementById('recap-prdct-table').innerHTML =
            `<tr><td colspan="3" class="text-center text-red-500 p-4">Failed to load data</td></tr>`;
        hideLoader(); // Hide loading animation if error occurs
    }
}

// Show loader animation
function showLoader() {
    document.getElementById("recap-prdct-table").innerHTML = `
        <tr id="loading-row">
            <td colspan="3" class="text-center p-4">Loading...</td>
        </tr>
    `;
}

// Hide loader after fetching data
function hideLoader() {
    const loaderRow = document.getElementById("loading-row");
    if (loaderRow) loaderRow.remove();
}

// Format number with thousand separators & two decimals
function formatNumber(value) {
    if (value === null || value === undefined || isNaN(value)) return "";
    return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Update table with fetched data for product recap achat
// function updateProductRecapAchatTable(data) {
//     const tableBody = document.getElementById("recap-prdct-table");
//     tableBody.innerHTML = "";

//     if (!data || data.length === 0) {
//         tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No data available</td></tr>`;
//         return;
//     }

//     // Find and separate the total row
//     const totalRow = data.find(row => row.PRODUIT === "Total");
//     const filteredData = data.filter(row => row.PRODUIT !== "Total");

//     // Add the "Total" row with sticky style
//     if (totalRow) {
//         const totalRowElement = document.createElement("tr");
//         totalRowElement.className = "bg-gray-200 font-bold sticky top-0 z-10";
//         totalRowElement.innerHTML = `
//             <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUIT}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
//         `;
//         tableBody.appendChild(totalRowElement);
//     }

//     // Add the filtered data rows
//     filteredData.forEach(row => {
//         const tr = document.createElement("tr");
//         tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
//         tr.innerHTML = `
//             <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUIT || "N/A"}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
//             <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
//         `;

//         // Add click event to fill in the search input
//         tr.addEventListener("click", () => {
//             const searchInput = document.getElementById("recap_product");
//             if (row.PRODUIT) {
//                 searchInput.value = row.PRODUIT;
//                 searchInput.dispatchEvent(new Event("input")); // Trigger input event
//             }
//         });

//         tableBody.appendChild(tr);
//     });
// }
function updateProductRecapAchatTable(data) {
    const tableBody = document.getElementById("recap-prdct-table");
    tableBody.innerHTML = "";

    if (!data || data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">No data available</td></tr>`;
        return;
    }

    // Find and separate the total row
    const totalRow = data.find(row => row.PRODUIT === "Total");
    const filteredData = data.filter(row => row.PRODUIT !== "Total");

    // 🔽 Sort by CHIFFRE descending
    filteredData.sort((a, b) => b.CHIFFRE - a.CHIFFRE);

    // Add the "Total" row with sticky style
    if (totalRow) {
        const totalRowElement = document.createElement("tr");
        totalRowElement.className = "bg-gray-200 font-bold sticky top-0 z-10";
        totalRowElement.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${totalRow.PRODUIT}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(totalRow.CHIFFRE)}</td>
        `;
        tableBody.appendChild(totalRowElement);
    }

    // Add the filtered data rows
    filteredData.forEach(row => {
        const tr = document.createElement("tr");
        tr.className = "dark:bg-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-600";
        tr.innerHTML = `
            <td class="border px-4 py-2 dark:border-gray-600">${row.PRODUIT || "N/A"}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.QTY)}</td>
            <td class="border px-4 py-2 dark:border-gray-600">${formatNumber(row.CHIFFRE)}</td>
        `;

        tr.addEventListener("click", () => {
            const searchInput = document.getElementById("recap_product");
            if (row.PRODUIT) {
                searchInput.value = row.PRODUIT;
                searchInput.dispatchEvent(new Event("input"));
            }
        });

        tableBody.appendChild(tr);
    });
}

// Event listeners to fetch data when inputs change
document.getElementById("recap_fournisseur").addEventListener("input", fetchProductRecapAchat);
document.getElementById("recap_product").addEventListener("input", fetchProductRecapAchat);
document.getElementById("start-date").addEventListener("input", fetchProductRecapAchat);
document.getElementById("end-date").addEventListener("input", fetchProductRecapAchat);



document.getElementById("download-recap-product-achat-excel").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();
 

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    // Construct the URL with query parameters
    const url = new URL("http://192.168.1.94:5000/download-recap-product-achat-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
 

    try {
        // Trigger the download by navigating to the URL
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
});

// Fetch data when filters are applied
document.getElementById("download-recap-fournisseur-achat-excel").addEventListener("click", async function () {
    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const fournisseur = document.getElementById("recap_fournisseur").value.trim().toUpperCase();
    const product = document.getElementById("recap_product").value.trim().toUpperCase();
 

    // Ensure both start and end dates are provided
    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    // Construct the URL with query parameters
    const url = new URL("http://192.168.1.94:5000/download-recap-fournisseur-achat-excel");
    url.searchParams.append("start_date", startDate);
    url.searchParams.append("end_date", endDate);
    if (fournisseur) url.searchParams.append("fournisseur", fournisseur);
    if (product) url.searchParams.append("product", product);
 

    try {
        // Trigger the download by navigating to the URL
        window.location.href = url;
    } catch (error) {
        console.error("Error downloading Excel file:", error);
        alert("Failed to download the Excel file.");
    }
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



            document.querySelectorAll('.search-container input').forEach(input => {
    input.addEventListener('focus', function() {
        // Clear value
        this.value = '';

        // Trigger an 'input' event to simulate typing/deleting
        const event = new Event('input', { bubbles: true });
        this.dispatchEvent(event);
    });
});


        </script>

</body>

</html>