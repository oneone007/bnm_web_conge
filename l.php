<?php
session_start(); // Start the session to access the logged-in user ID

$host = 'localhost'; // Change if needed
$user = 'root'; // Change if needed
$pass = ''; // Change if needed
$dbname = 'bnm'; // Your database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the rating submission if it's sent via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating'])) {
    $rating = intval($_POST['rating']);

    // Ensure that the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session

        // Update the rating for the logged-in user
        $sql = "UPDATE users SET rating = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $rating, $user_id);

        if ($stmt->execute()) {
            echo "Rating updated successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "You must be logged in to submit a rating.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Star Rating</title>
    <style>
        .stars {
            font-size: 30px;
            cursor: pointer;
            color: gray;
        }
        .stars:hover,
        .stars.selected {
            color: gold;
        }
    </style>

    <h3>Rate us:</h3>
    <span class="stars" data-value="1">★</span>
    <span class="stars" data-value="2">★</span>
    <span class="stars" data-value="3">★</span>
    <span class="stars" data-value="4">★</span>
    <span class="stars" data-value="5">★</span>

    <p id="result"></p>

    <script>
        document.querySelectorAll('.stars').forEach(star => {
            star.addEventListener('click', function() {
                let rating = this.getAttribute('data-value');

                // Remove previous selections
                document.querySelectorAll('.stars').forEach(s => s.classList.remove('selected'));
                
                // Highlight selected stars
                for (let i = 1; i <= rating; i++) {
                    document.querySelector('.stars[data-value="' + i + '"]').classList.add('selected');
                }

                // Send rating to backend (same page)
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'rating=' + rating
                })
                .then(response => response.text()) // Expect a plain text response
                .then(data => {
                    // Display the response in the result paragraph
                    document.getElementById('result').innerText = data;
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>

</body>
</html>
