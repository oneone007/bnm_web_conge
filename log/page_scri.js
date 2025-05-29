// Function to validate the Sign-Up form
function validateSignupForm() {
    // Get the input values
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confipassword").value;

    // Error message element
    const errorMessage = document.getElementById("errorMessage");

    // Clear any previous error messages
    errorMessage.textContent = "";

    // Check if all fields are filled out
    if (username === "" || password === "" || confipassword === "") {
        errorMessage.textContent = "All fields are required!";
        triggerShakeEffect();
        return false; // Prevent form submission
    }

    // Check if passwords match
    if (password !== confipassword) {
        errorMessage.textContent = "Passwords do not match!";
        triggerShakeEffect();
        return false; // Prevent form submission
    }

    // If everything is fine, return true to allow form submission
    return true;
}

// Function to trigger the shake effect
function triggerShakeEffect() {
    document.querySelector('.login-box').classList.add('shake');
    setTimeout(() => {
        document.querySelector('.login-box').classList.remove('shake');
    }, 1000); // Shake effect duration is 1s
}