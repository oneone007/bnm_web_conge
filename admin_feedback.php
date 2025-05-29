<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: BNM");
    exit();
}

// Define allowed admin usernames
$allowed_admins = ['admin'];

// Restrict access
if (!in_array($_SESSION['username'], $allowed_admins)) {
    header("Location: Acess_Denied");
    exit();
}

// Handle feedback deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $feedbackId = $_POST['delete_id'];
    $json_file = 'feedback.json';
    
    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file);
        $feedbacks = json_decode($json_data, true) ?? [];
        
        // Find and remove the feedback with matching timestamp (using timestamp as ID)
        $updatedFeedbacks = array_filter($feedbacks, function($item) use ($feedbackId) {
            return $item['timestamp'] !== $feedbackId;
        });
        
        // Save back to file
        file_put_contents($json_file, json_encode(array_values($updatedFeedbacks), JSON_PRETTY_PRINT));
        
        // Redirect to refresh the page
        exit();
    }
}

// Load feedback from JSON
$json_feedbacks = [];
$json_file = 'feedback.json';
if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $json_feedbacks = json_decode($json_data, true) ?? [];
    
    // Sort by timestamp (newest first)
    usort($json_feedbacks, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - User Feedback</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      padding-top: 50px;
    }
    .container {
      max-width: 1200px;
    }
    h2 {
      margin-bottom: 20px;
    }
    table {
      background-color: white;
    }
    .badge-json {
      background-color: #198754;
    }
    .action-buttons {
      white-space: nowrap;
    }
  </style>
</head>
<body>

<div class="container">
  <h2 class="text-center">User Feedback (JSON)</h2>
  <table class="table table-bordered table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Username</th>
        <th>Type</th>
        <th>Content</th>
        <th>Rating</th>
        <th>Page</th>
        <th>Date Submitted</th>
        <th>Source</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($json_feedbacks)): ?>
        <?php foreach ($json_feedbacks as $index => $feedback): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($feedback['username']) ?></td>
            <td><strong><?= ucfirst(htmlspecialchars($feedback['type'])) ?></strong></td>
            <td><?= nl2br(htmlspecialchars($feedback['content'])) ?></td>
            <td><?= $feedback['rating'] ? htmlspecialchars($feedback['rating']) . ' ★' : '-' ?></td>
            <td><?= htmlspecialchars($feedback['page'] ?? '-') ?></td>
            <td><?= date('d M Y, H:i', strtotime($feedback['timestamp'])) ?></td>
            <td>
              <span class="badge bg-success">JSON</span>
            </td>
            <td class="action-buttons">
              <form method="post" style="display: inline;">
                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($feedback['timestamp']) ?>">
                <button type="submit" class="btn btn-sm btn-danger" 
                        onclick="return confirm('Are you sure you want to delete this feedback?');">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="9" class="text-center">No feedback found in JSON file.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>