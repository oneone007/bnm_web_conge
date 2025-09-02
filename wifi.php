<?php
session_start();
// Only allow access if username is 'admin' and role is 'Developer'
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Developer') {
    header('Location: 403');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>WiFi Password Manager</title>
  <script src="theme.js"></script>
  <script src="api_config.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --light-bg: #f5f7fa;
      --dark-bg: #111827;
      --light-card: #ffffff;
      --dark-card: #1f2937;
      --light-text: #333;
      --dark-text: #e5e7eb;
      --primary: #007bff;
      --success: #28a745;
      --danger: #dc3545;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 40px;
      background: var(--light-bg);
      color: var(--light-text);
      transition: background 0.3s, color 0.3s;
    }

    body.dark {
      background: var(--dark-bg);
      color: var(--dark-text);
    }

    h2 {
      margin-bottom: 20px;
    }

    #message {
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 5px;
      display: none;
    }

    #message.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    #message.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    #addForm {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      background: var(--light-card);
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      margin-bottom: 20px;
      transition: background 0.3s;
    }

    body.dark #addForm {
      background: var(--dark-card);
    }

    #addForm input {
      flex: 1;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    #addForm button {
      padding: 10px 20px;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background 0.3s;
    }

    #addForm button:hover {
      background: #0056b3;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--light-card);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
      border-radius: 8px;
      overflow: hidden;
      transition: background 0.3s;
    }

    body.dark table {
      background: var(--dark-card);
    }

    th, td {
      padding: 14px;
      border-bottom: 1px solid #eee;
      text-align: center;
    }

    th {
      background-color: var(--primary);
      color: white;
    }

    input[type="text"],
    input[type="password"] {
      width: 90%;
      padding: 6px 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }

    .actions button {
      margin: 0 5px;
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .actions .save {
      background-color: var(--success);
      color: white;
    }

    .actions .save:hover {
      background-color: #218838;
    }

    .actions .delete {
      background-color: var(--danger);
      color: white;
    }

    .actions .delete:hover {
      background-color: #c82333;
    }

    .toggle-btn {
      margin-left: 5px;
      cursor: pointer;
      color: var(--primary);
      border: none;
      background: none;
      font-size: 14px;
    }

    .dark-mode-toggle {
      margin-bottom: 20px;
      background: none;
      border: 2px solid var(--primary);
      color: var(--primary);
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.3s;
    }

    .dark-mode-toggle:hover {
      background: var(--primary);
      color: white;
    }

    body.dark input[type="text"],
body.dark input[type="password"] {
  background-color: #1f2937;
  color: #e5e7eb;
  border: 1px solid #374151;
}

body.dark table td {
  background-color: #1f2937;
  color: #e5e7eb;
  border-color: #374151;
}
body.dark input[type="text"]:focus,
body.dark input[type="password"]:focus {
  outline: none;
  border-color: #3b82f6; /* blue-500 */
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

  </style>
</head>
<body>

<h2>WiFi Password Manager</h2>
<!-- <button class="dark-mode-toggle" onclick="toggleDarkMode()">🌙 Toggle Dark Mode</button> -->
<div id="message"></div>

<form id="addForm">
  <input type="text" id="name" placeholder="Network Name" required />
  <input type="text" id="password" placeholder="WiFi Password" required />
  <input type="text" id="modempasswd" placeholder="Modem Password" required />
  <input type="text" id="ip" placeholder="IP Address" required />
  <button type="submit">Add WiFi</button>
</form>

<table id="wifiTable">
  <thead>
    <tr>
      <th>Network Name</th>
      <th>Password</th>
      <th>Modem Password</th>
      <th>IP Address</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<script>
const apiBase = window.API_CONFIG.getBaseUrl();

function showMessage(message, type = "success") {
  const msgDiv = document.getElementById("message");
  msgDiv.className = type;
  msgDiv.textContent = message;
  msgDiv.style.display = "block";
  setTimeout(() => msgDiv.style.display = "none", 3000);
}

function createPasswordField(value, id, field) {
  const wrapper = document.createElement("div");
  wrapper.style.display = "flex";
  wrapper.style.alignItems = "center";
  wrapper.style.justifyContent = "center";
  wrapper.style.gap = "6px";

  const input = document.createElement("input");
  input.type = "password";
  input.value = value;
  input.dataset.id = id;
  input.dataset.field = field;

  const btn = document.createElement("button");
  btn.type = "button";
  btn.className = "toggle-btn";
  btn.innerHTML = `<i class="fas fa-lock"></i>`;

  btn.onclick = () => {
    const icon = btn.querySelector("i");
    if (input.type === "password") {
      input.type = "text";
      icon.className = "fas fa-lock-open";
    } else {
      input.type = "password";
      icon.className = "fas fa-lock";
    }
  };

  wrapper.appendChild(input);
  wrapper.appendChild(btn);

  return wrapper;
}


function loadWiFi() {
  fetch(apiBase + "/list")
    .then((res) => res.json())
    .then((data) => {
      const tbody = document.querySelector("#wifiTable tbody");
      tbody.innerHTML = "";
      data.forEach((item) => {
        const row = document.createElement("tr");

        const nameCell = document.createElement("td");
        nameCell.innerHTML = `<input type="text" value="${item.name}" data-id="${item.id}" data-field="name" />`;

        const passCell = document.createElement("td");
        passCell.appendChild(createPasswordField(item.password, item.id, "password"));

        const modemCell = document.createElement("td");
        modemCell.appendChild(createPasswordField(item.modempasswd, item.id, "modempasswd"));

        const ipCell = document.createElement("td");
        ipCell.innerHTML = `<input type="text" value="${item.ip}" data-id="${item.id}" data-field="ip" />`;

        const actionCell = document.createElement("td");
        actionCell.className = "actions";
        actionCell.innerHTML = `
          <button class="save" onclick="saveWiFi(${item.id})">Save</button>
          <button class="delete" onclick="deleteWiFi(${item.id})">Delete</button>
        `;

        row.appendChild(nameCell);
        row.appendChild(passCell);
        row.appendChild(modemCell);
        row.appendChild(ipCell);
        row.appendChild(actionCell);

        tbody.appendChild(row);
      });
    })
    .catch(() => showMessage("Failed to load WiFi list", "error"));
}

function saveWiFi(id) {
  const inputs = document.querySelectorAll(`input[data-id='${id}']`);
  const data = {};
  inputs.forEach((input) => {
    data[input.getAttribute("data-field")] = input.value;
  });

  fetch(`${apiBase}/update/${id}`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  })
    .then((res) => res.json())
    .then((response) => {
      if (response.success) {
        showMessage("WiFi updated successfully");
        loadWiFi();
      } else {
        showMessage("Failed to update WiFi", "error");
      }
    })
    .catch(() => showMessage("Error during update", "error"));
}

function deleteWiFi(id) {
  if (!confirm("Are you sure you want to delete this entry?")) return;

  fetch(`${apiBase}/delete/${id}`, {
    method: "POST",
  })
    .then((res) => res.json())
    .then((response) => {
      if (response.success) {
        showMessage("WiFi deleted successfully");
        loadWiFi();
      } else {
        showMessage("Failed to delete WiFi", "error");
      }
    })
    .catch(() => showMessage("Error during delete", "error"));
}

document.getElementById("addForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const data = {
    name: document.getElementById("name").value,
    password: document.getElementById("password").value,
    modempasswd: document.getElementById("modempasswd").value,
    ip: document.getElementById("ip").value,
  };

  fetch(apiBase + "/add", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  })
    .then((res) => res.json())
    .then((response) => {
      if (response.success) {
        showMessage("WiFi added successfully");
        loadWiFi();
        this.reset();
      } else {
        showMessage("Failed to add WiFi", "error");
      }
    })
    .catch(() => showMessage("Error during add", "error"));
});


// Remove old toggleDarkMode. Integrate with theme.js dark mode system
// Listen for theme changes and update body class accordingly
document.addEventListener('DOMContentLoaded', function() {
  function syncTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
      document.body.classList.add('dark');
    } else {
      document.body.classList.remove('dark');
    }
  }
  syncTheme();
  window.addEventListener('storage', function(e) {
    if (e.key === 'theme') syncTheme();
  });
  window.addEventListener('themeChanged', function(e) {
    syncTheme();
  });
});

// Initial Load
loadWiFi();
</script>

</body>
</html>
