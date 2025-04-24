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
    <title>BNM Web</title>
    <link rel="icon" href="assets/tab.png" sizes="128x128" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- <link rel="stylesheet" href="journal.css"> -->
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
.dark .table-container {
    border-color: #374151;
}

.dark .table-header {
    background-color: #374151;
    color: #000000;
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
    width: 40%;
    display: grid;
    grid-template-columns: repeat(1, minmax(150px, 1fr));
    gap: 16px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: relative; /* Make it relative for absolute children */
  }
  
  
  #suggestions {
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 5px;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;               /* Match input width */
    z-index: 1000;
    color: #333;
    
    line-height: 30px;          /* Adjust based on your font-size */
    max-height: calc(30px * 5); /* 5 visible rows */
    overflow-y: auto;           /* Scroll if more */
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

#lottieContainer {

z-index: -1; /* Push it behind other content */
pointer-events: none; /* Prevent interaction */
}


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
  


</style>

</head>

<body class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar Toggle Button -->
 <!-- Include SweetAlert2 Library (Add this to your HTML head if not already included) -->



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






<!-- Sidebar -->

    <!-- Main Content -->

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


        <!-- Filters -->
   
        
        <div id="content" class="content flex-grow p-4">

        <div class="flex justify-center items-center mb-6">
        <h1 class="text-5xl font-bold dark:text-white text-center  ">
        Etat Fournisseur 
            </h1>
        </div>
        <br>



        <!-- Search Fields -->
<!-- Search Fields -->
<!-- Search Fields -->


 



<div class="search-container">
  <label for="etat_fournisseur">Search :</label>
  <input type="text" id="etat_fournisseur" placeholder="Search fournisseur...">
  <div id="suggestions"></div>
</div>

    

        <!-- <button id="downloadExcel_journal"
            class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700">
            <img src="assets/excel.png" alt="Excel Icon" class="w-6 h-6">
            <span>Journal de vente Download</span>
        </button> -->

<!-- From Uiverse.io by Rodrypaladin --> 
<!-- <button id="downloadExcel_journal"
 class="button">
  <span class="button__span">Journal de vente Download</span>
  
</button> -->
<div class="container">
  <button id="dette-excel" class="button">
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
        <div id="supplier-table-container">
    <h2>TOTAL FOURNISSEUR</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>TOTAL ECHU</th>
                    <th>TOTAL DETTE</th>
                    <th>TOTAL STOCK</th>
                </tr>
            </thead>
            <tbody id="dette-table">
                <!-- Dynamic Data Will Be Centered -->
            </tbody>
        </table>
    </div>
</div>

<style>
 /* Normal table styles */
#supplier-table-container table {
    width: 100%;
    border-collapse: collapse;
}

#supplier-table-container th,
#supplier-table-container td {
    padding: 10px;
    text-align: center;
    border: 1px solid #ccc;
}

/* Dark mode styles */
.dark #supplier-table-container {
    color: #000; /* Make text black */
}

.dark #supplier-table-container h2,
.dark #supplier-table-container th,
.dark #supplier-table-container td {
    color: #000; /* Ensure all text is black */
}

.dark #supplier-table-container table {
    background-color: #d3d3d3; /* Light grey background for the table */
}

</style>



    <br>
        
        <!-- Table -->
        <div id="etat-fournisseur-container" class="p-4 bg-white rounded-xl shadow-lg">
  <h2 class="text-2xl font-semibold mb-4">ETAT FOURNISSEUR</h2>

  <!-- Refresh Button -->
  <div class="flex justify-end mb-3">
    <button id="refresh-btn" 
            class="p-2 bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 transition duration-200 flex items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" 
              d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5
                 m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
      </svg>
      <span class="ml-2">Refresh</span>
    </button>
  </div>

  <!-- Table -->
  <div class="etat-table-wrapper overflow-x-auto rounded-lg border border-gray-300">
    <table id="etat-fournisseur-table" class="table-auto w-full border-collapse">
      <thead class="bg-gray-100">
        <tr>
          <th data-column="FOURNISSEUR" onclick="sortetatTable('FOURNISSEUR')" class="p-3 cursor-pointer">Fournisseur</th>
          <th data-column="TOTAL ECHU" onclick="sortetatTable('TOTAL ECHU')" class="p-3 cursor-pointer">Total Échu</th>
          <th data-column="TOTAL DETTE" onclick="sortetatTable('TOTAL DETTE')" class="p-3 cursor-pointer">Total Dette</th>
          <th data-column="TOTAL STOCK" onclick="sortetatTable('TOTAL STOCK')" class="p-3 cursor-pointer">Total Stock</th>
        </tr>
      </thead>
      <tbody id="etat-fournisseur-body">
        <!-- Data Loads Here -->
      </tbody>
    </table>
  </div>
</div>




<style>

.search-container {
    width: 40%;
    display: grid;
    grid-template-columns: repeat(1, minmax(150px, 1fr));
    gap: 16px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: relative; /* Make it relative for absolute children */
  }
  
  
  #suggestions {
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 5px;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;               /* Match input width */
    z-index: 1000;
    color: #333;
    
    line-height: 30px;          /* Adjust based on your font-size */
    max-height: calc(30px * 5); /* 5 visible rows */
    overflow-y: auto;           /* Scroll if more */
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

/* Dark mode */
.dark #suggestions {
  background-color: #222;
  color: #fff;
  border-color: #555;
}

.suggestion-item {
  padding: 10px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.suggestion-item:hover {
  background-color: #f0f0f0;
}

    #etat-fournisseur-body {
  display: block;
  max-height: 420px; /* Adjust height for around 10 rows */
  overflow-y: auto;
}

#etat-fournisseur-body tr {
  display: table;
  width: 100%;
  table-layout: fixed;
}

thead, tbody tr {
  display: table;
  width: 100%;
  table-layout: fixed;
}

/* Dark mode adjustments */
.dark .etat-table-wrapper {
  background-color: #000;
  color: black;
}

.dark .etat-table-wrapper th,
.dark .etat-table-wrapper td {
  background-color: #111;
  color: black;
  border-color: #333;
}

.dark .etat-table-wrapper th:hover {
  background-color: #222;
}

      #supplier-table-container h2 {
    font-size: 18px;
    color: #333;
    margin-bottom: 19px;
    font-weight: bold; /* <-- Makes it bold */
}



#supplier-table-container {
    width: 600px;
    height: 170px;
    background-color: #f3f4f6;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 10px;
    margin: auto;
    text-align: center;
}



#supplier-table-container .table-wrapper {
    overflow-x: auto;
}

#supplier-table-container table {
    width: 100%;
    border-collapse: collapse;
}

#supplier-table-container th,
#supplier-table-container td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
}

#supplier-table-container thead {
    background-color: #f0f0f0;
}

#supplier-table-container tbody {
    background-color: #fafafa;
}



#etat-fournisseur-container {
      width: 90%;
      background-color: #fff;
      padding: 20px;
      margin: auto;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      text-align: center;
      background-color: #f3f4f6;

    }

    #etat-fournisseur-container h2 {
      font-size: 20px;
      font-weight: bold;
      color: #333;
      margin-bottom: 20px;
    }

    .etat-table-wrapper {
      max-height: 440px;  /* Roughly fits 10 rows (44px each) */
      overflow-y: auto;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    #etat-fournisseur-table {
      width: 100%;
      border-collapse: collapse;
      text-align: center;
    }

    #etat-fournisseur-table th, #etat-fournisseur-table td {
      border: 1px solid #ccc;
      padding: 12px;
      font-size: 14px;
      background-color: #fff;
    }

    #etat-fournisseur-table th {
      background-color: #f0f0f0;
      cursor: pointer;
      position: sticky;
      top: 0;
      z-index: 2;
      transition: background-color 0.3s;
    }

    #etat-fournisseur-table th:hover {
      background-color: #ddd;
    }

    #etat-fournisseur-table tbody tr:hover {
      background-color: #f9f9f9;
    }

    
.loading-cell {
    text-align: center;
    padding: 20px;
}

#lottie-container-d {
    width: 290px;
    height: 200px;
    margin: auto;
}


/* From Uiverse.io by Pradeepsaranbishnoi */ 
/* Compact Loading Animation */
.containerlo {
  width: 100px;
  height: 8px;
  border: 1px solid #b2b2b2;
  border-radius: 5px;
  margin: 0 auto;
  padding: 1px;
  overflow: hidden;
  font-size: 0;
}

.box {
  width: 6px;
  height: 50%;
  background: linear-gradient(to bottom, #2838c7 0%,#5979ef 17%,#869ef3 32%,#869ef3 45%,#5979ef 59%,#2838c7 100%);
  display: inline-block;
  margin-right: 1px;
  animation: loader 1.5s infinite linear;
}

.logobnm {
  width: 120px;
  margin: auto;
  text-align: center;
}

.logobnm p {
  margin: 0;
  padding: 0;
}

.top {
  font-size: 10px;
  font-weight: 300;
  line-height: 10px;
}

.top:after {
  content: "\00a9";
  font-size: 8px;
  position: relative;
  top: -3px;
  margin-left: 2px;
}

.mid {
  font-size: 20px;
  font-weight: 700;
  line-height: 18px;
}

.mid span {
  font-size: 10px;
  vertical-align: top;
  color: #FF6821;
  margin-top: -5px;
}

.logobnm .bottom {
  font-size: 14px;
  font-weight: 300;
  line-height: 14px;
  margin-left: 2px;
}

@keyframes loader {
  0% {
    transform: translate(-20px);
  }
  100% {
    transform: translate(100px);
  }
}


</style>






     

        <!-- second table remise aauto  -->


        <!-- Pagination -->
         
    
     <br>


        <br><br><br> <br>
        <script>
            window.onload = () => {
    fetchFournisseurDette();  // If you have this function
    loadFournisseurData();    // Your main data load function
};
async function fetchFournisseurDette() {
  const tableBody = document.getElementById("dette-table");
tableBody.innerHTML = `
<tr>
  <td>
    <div class="window">
      <div class="logobnm">
        <p class="top">BNM</p>
        <p class="mid">BnmWeb</p>
        <p class="bottom">Loading...</p>
        <div class="containerlo">
          <div class="box"></div>
          <div class="box"></div>
          <div class="box"></div>
        </div>
      </div>
    </div>
  </td>
</tr>`;

    try {
        const response = await fetch("http://127.0.0.1:5000/etat_fournisseur");
        if (!response.ok) throw new Error("Network response was not ok");

        const data = await response.json();
        console.log("✅ Data received:", data);

        // Clear loading
        tableBody.innerHTML = "";

        // Check if data contains values
        if (!data || Object.keys(data).length === 0) {
            tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4">Aucune donnée disponible</td></tr>`;
            return;
        }

        // Build the row dynamically
        const tr = document.createElement("tr");
        tr.classList.add("dark:bg-gray-700");
        tr.innerHTML = `
            <td class="border px-3 py-2 dark:border-gray-600">${data["TOTAL ECHU"].toLocaleString('fr-FR')}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${data["TOTAL DETTE"].toLocaleString('fr-FR')}</td>
            <td class="border px-3 py-2 dark:border-gray-600">${data["TOTAL STOCK"].toLocaleString('fr-FR')}</td>
        `;
        tableBody.appendChild(tr);

    } catch (error) {
        console.error("❌ Error fetching data:", error);
        tableBody.innerHTML = `<tr><td colspan="3" class="text-center p-4 text-red-500">Erreur de chargement</td></tr>`;
    }
}




let currentSortColumn = '';
let currentSortOrder = 'desc';
let fournisseurData = [];
async function loadFournisseurData(sortColumn = '', sortOrder = 'desc') {
  try {
    console.log('Fetching data from server...'); // ✅ Check in browser console

    const tableBody = document.getElementById("etat-fournisseur-body");

    // Show loading animation while waiting for data
    tableBody.innerHTML = `
    <tr>
      <td colspan="4" style="text-align: center;">
        <div class="window">
          <div class="logobnm">
            <p class="top">BNM</p>
            <p class="mid">BnmWeb</p>
            <p class="bottom">Loading...</p>
            <div class="containerlo">
              <div class="box"></div>
              <div class="box"></div>
              <div class="box"></div>
            </div>
          </div>
        </div>
      </td>
    </tr>`;

    // Fetch data from server
    const url = `http://127.0.0.1:5000/fetchFournisseurDette`;
    const response = await fetch(url);
    const data = await response.json();

    if (data.error) {
      console.error(data.error);
      tableBody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: red;">Error loading data</td></tr>`;
      return;
    }

    fournisseurData = data; // ✅ Store fresh data
    console.log('Data fetched successfully:', fournisseurData); // ✅ Data preview

    // Render table with fetched data
    renderFournisseurTable(sortColumn, sortOrder);
  } catch (error) {
    console.error('Error fetching data:', error);
    document.getElementById("etat-fournisseur-body").innerHTML =
      `<tr><td colspan="4" style="text-align: center; color: red;">Error fetching data</td></tr>`;
  }
}

function renderFournisseurTable(sortColumn = '', sortOrder = 'desc', filterText = '') {
  let dataToRender = [...fournisseurData];

  // Filter Logic
  if (filterText) {
    dataToRender = dataToRender.filter(f =>
      f["FOURNISSEUR"].toLowerCase().includes(filterText.toLowerCase())
    );
  }

  // Sorting Logic
  if (sortColumn) {
    dataToRender.sort((a, b) => {
      const valA = isNaN(parseFloat(a[sortColumn])) ? (a[sortColumn] || '').toLowerCase() : parseFloat(a[sortColumn]);
      const valB = isNaN(parseFloat(b[sortColumn])) ? (b[sortColumn] || '').toLowerCase() : parseFloat(b[sortColumn]);

      if (valA < valB) return sortOrder === 'asc' ? -1 : 1;
      if (valA > valB) return sortOrder === 'asc' ? 1 : -1;
      return 0;
    });
  }

  // Render table body
  const tableBody = document.getElementById('etat-fournisseur-body');
  tableBody.innerHTML = ''; // Clear previous content

  if (dataToRender.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="4" style="text-align: center;">No data found</td></tr>`;
    return;
  }

  dataToRender.forEach(row => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="clickable">${row["FOURNISSEUR"].toLocaleString('fr-FR')}</td>
      <td>${row["TOTAL ECHU"].toLocaleString('fr-FR')}</td>
      <td>${row["TOTAL DETTE"].toLocaleString('fr-FR')}</td>
      <td>${row["TOTAL STOCK"].toLocaleString('fr-FR')}</td>
    `;
    tableBody.appendChild(tr);
  });

  // Make Fournisseur column clickable for auto-filling the search
  document.querySelectorAll('.clickable').forEach(td => {
    td.addEventListener('click', () => {
      document.getElementById('etat_fournisseur').value = td.textContent;
      triggerSearch(); // Auto trigger the search
    });
  });
}

function sortetatTable(column) {
  // Toggle sort order if the same column is clicked again
  if (currentSortColumn === column) {
    currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
  } else {
    currentSortColumn = column;
    currentSortOrder = 'desc';
  }

  // Update arrows on headers
  document.querySelectorAll('th').forEach(th => {
    const content = th.innerText.replace(/ ↑| ↓/g, '');
    th.innerText = content;
  });

  const currentHeader = document.querySelector(`th[data-column="${column}"]`);
  if (currentHeader) {
    const arrow = currentSortOrder === 'asc' ? ' ↑' : ' ↓';
    currentHeader.innerText += arrow;
  }

  // Re-render table with current search filter
  const searchValue = document.getElementById('etat_fournisseur').value.trim();
  renderFournisseurTable(currentSortColumn, currentSortOrder, searchValue);
}

// async function loadFournisseurData(sortColumn = '', sortOrder = 'desc') {
//   try {
//     console.log('Fetching data from server...'); // ✅ Check this in the browser console
//     const url = `http://127.0.0.1:5000/fetchFournisseurDette`;

//     const response = await fetch(url);
//     const data = await response.json();

//     if (data.error) {
//       console.error(data.error);
//       return;
//     }

//     fournisseurData = data; // ✅ Store fresh data
//     console.log('Data fetched successfully:', fournisseurData); // ✅ Data preview
//     renderFournisseurTable(sortColumn, sortOrder);
//   } catch (error) {
//     console.error('Error fetching data:', error);
//   }
// }


// function renderFournisseurTable(sortColumn = '', sortOrder = 'desc', filterText = '') {
//   let dataToRender = [...fournisseurData];

//   // Filter Logic (if there's a filter text)
//   if (filterText) {
//     dataToRender = dataToRender.filter(f =>
//       f["FOURNISSEUR"].toLowerCase().includes(filterText.toLowerCase())
//     );
//   }

//   // Sorting Logic
//   if (sortColumn) {
//     dataToRender.sort((a, b) => {
//       const valA = isNaN(parseFloat(a[sortColumn])) ? (a[sortColumn] || '').toLowerCase() : parseFloat(a[sortColumn]);
//       const valB = isNaN(parseFloat(b[sortColumn])) ? (b[sortColumn] || '').toLowerCase() : parseFloat(b[sortColumn]);

//       if (valA < valB) return sortOrder === 'asc' ? -1 : 1;
//       if (valA > valB) return sortOrder === 'asc' ? 1 : -1;
//       return 0;
//     });
//   }

//   // Render table body
//   const tableBody = document.getElementById('etat-fournisseur-body');
//   tableBody.innerHTML = '';

  

//   dataToRender.forEach(row => {
//     const tr = document.createElement('tr');
//     tr.innerHTML = `
//       <td class="clickable">${row["FOURNISSEUR"]}</td>
//       <td>${row["TOTAL ECHU"]}</td>
//       <td>${row["TOTAL DETTE"]}</td>
//       <td>${row["TOTAL STOCK"]}</td>
//     `;
//     tableBody.appendChild(tr);
//   });

//   // Make Fournisseur column clickable for auto-filling the search
//   document.querySelectorAll('.clickable').forEach(td => {
//     td.addEventListener('click', () => {
//       document.getElementById('etat_fournisseur').value = td.textContent;
//       triggerSearch(); // Auto trigger the search
//     });
//   });
// }

// function sortetatTable(column) {
//   // Toggle sort order if the same column is clicked again
//   if (currentSortColumn === column) {
//     currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
//   } else {
//     currentSortColumn = column;
//     currentSortOrder = 'desc';
//   }

//   // Update arrows on headers
//   document.querySelectorAll('th').forEach(th => {
//     const content = th.innerText.replace(/ ↑| ↓/g, '');
//     th.innerText = content;
//   });

//   const currentHeader = document.querySelector(`th[data-column="${column}"]`);
//   if (currentHeader) {
//     const arrow = currentSortOrder === 'asc' ? ' ↑' : ' ↓';
//     currentHeader.innerText += arrow;
//   }

//   // Re-render table with current search filter
//   const searchValue = document.getElementById('etat_fournisseur').value.trim();
//   renderFournisseurTable(currentSortColumn, currentSortOrder, searchValue);
// }

// Autocomplete suggestion dropdown
function showSuggestions(filteredList) {
  const suggestionBox = document.getElementById('suggestions');
  suggestionBox.innerHTML = '';
  filteredList.forEach(f => {
    const div = document.createElement('div');
    div.classList.add('suggestion-item');
    div.textContent = f["FOURNISSEUR"];
    div.addEventListener('click', () => {
      document.getElementById('etat_fournisseur').value = f["FOURNISSEUR"];
      suggestionBox.innerHTML = '';
      triggerSearch(); // Auto trigger search when suggestion clicked
    });
    suggestionBox.appendChild(div);
  });
}

function triggerSearch() {
  const searchValue = document.getElementById('etat_fournisseur').value.trim();
  renderFournisseurTable(currentSortColumn, currentSortOrder, searchValue);
}

// Event listener for live search/autocomplete
document.getElementById('etat_fournisseur').addEventListener('input', function () {
  const searchValue = this.value.trim().toLowerCase();
  if (searchValue === '') {
    document.getElementById('suggestions').innerHTML = '';
    renderFournisseurTable(currentSortColumn, currentSortOrder);
    return;
  }

  // Suggestion logic
  const filtered = fournisseurData.filter(f =>
    f["FOURNISSEUR"].toLowerCase().includes(searchValue)
  );

  showSuggestions(filtered);
  renderFournisseurTable(currentSortColumn, currentSortOrder, searchValue);
});

// Initial Load
loadFournisseurData();

// Initial load
document.getElementById('refresh-btn').addEventListener('click', () => {
    const refreshBtn = document.getElementById('refresh-btn');
    refreshBtn.innerHTML = '⏳ Loading...';

    const searchValue = document.getElementById('etat_fournisseur').value.trim().toLowerCase();
    loadFournisseurData().then(() => {
        if (searchValue) {
            const filtered = fournisseurData.filter(f =>
                f["FOURNISSEUR"].toLowerCase().includes(searchValue)
            );
            renderFilteredTable(filtered);
        } else {
            renderFournisseurTable(currentSortColumn, currentSortOrder);
        }

        refreshBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 0 0 12.755 4.652M4.5 15.75H9m-4.5 0v4.5m15-7.5a7.5 7.5 0 0 0-12.755-4.652M19.5 8.25H15m4.5 0V3.75" />
            </svg>
        `;
    });
});


function renderFilteredTable(filteredData) {
    const tableBody = document.getElementById('etat-fournisseur-body');
    tableBody.innerHTML = '';

    filteredData.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="clickable">${row["FOURNISSEUR"]}</td>
            <td>${row["TOTAL ECHU"]}</td>
            <td>${row["TOTAL DETTE"]}</td>
            <td>${row["TOTAL STOCK"]}</td>
        `;
        tableBody.appendChild(tr);
    });

    // Reattach click events
    document.querySelectorAll('.clickable').forEach(td => {
        td.addEventListener('click', () => {
            document.getElementById('etat_fournisseur').value = td.textContent;
            triggerSearch();
        });
    });
}


// Initial load on page load



  // Auto load on page load


// Initial load
document.getElementById("dette-excel").addEventListener("click", function () {
    exportToExcel();
});

function exportToExcel() {
    // Ensure the SheetJS library is loaded
    if (typeof XLSX === "undefined") {
        console.error("SheetJS library (XLSX) is required.");
        return;
    }

    // Select table data
    const table = document.getElementById("etat-fournisseur-body");
    if (!table || table.rows.length === 0) {
        alert("Aucune donnée à exporter.");
        return;
    }

    let data = [];
    let headers = ["FOURNISSEUR", "TOTAL ECHU", "TOTAL DETTE", "TOTAL STOCK"];

    // Extract table rows data
    for (let row of table.rows) {
        let rowData = [];
        for (let cell of row.cells) {
            rowData.push(cell.innerText);
        }
        data.push(rowData);
    }

    // Create a new Excel worksheet
    let ws = XLSX.utils.aoa_to_sheet([headers, ...data]);

    // Apply styling to headers (Background and Font)
    let range = XLSX.utils.decode_range(ws["!ref"]);
    for (let C = range.s.c; C <= range.e.c; ++C) {
        let headerCell = XLSX.utils.encode_cell({ r: 0, c: C });
        ws[headerCell].s = {
            fill: { fgColor: { rgb: "4F81BD" } }, // Blue header
            font: { bold: true, color: { rgb: "FFFFFF" } }, // White text
        };
    }

    // Apply alternate row coloring
    for (let R = 1; R <= data.length; R++) {
        for (let C = 0; C < headers.length; C++) {
            let cellRef = XLSX.utils.encode_cell({ r: R, c: C });
            ws[cellRef].s = {
                fill: { fgColor: { rgb: R % 2 === 0 ? "EAEAEA" : "FFFFFF" } }, // Alternating row colors
            };
        }
    }

    // Create the Excel workbook
    let wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Fournisseur Dette");

    // Generate filename
    let today = new Date().toISOString().split("T")[0]; // YYYY-MM-DD
    let filename = `Fournisseur_Dette_${today}.xlsx`;

    // Trigger download
    XLSX.writeFile(wb, filename);
}






// // Format number with thousand separators & two decimals
// function formatNumber(value) {
//     if (value === null || value === undefined || isNaN(value)) return "0.00";
//     return parseFloat(value).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
// }





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