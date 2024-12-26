<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <!-- this is the first file accessed by the user. It sends a request to backend to check where it should send the user -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jakach Login</title>
  <?php
	include "../assets/components.php";
  ?>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-header text-center bg-primary text-white">
            <h4>Register</h4>
          </div>
          <div class="card-body">
            <form id="registerForm">
              <!-- Username -->
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" placeholder="Enter your username" required>
              </div>
              <!-- Email -->
              <div class="mb-3">
                <label for="email" class="form-label">Email Address (optional)</label>
                <input type="email" class="form-control" id="email" placeholder="Enter your email">
              </div>
	      <!-- Telegram -->
              <div class="mb-3">
                <label for="telegram" class="form-label">Telegram ID (optional)</label>
                <input type="text" class="form-control" id="telegram" placeholder="Enter your Telegram ID">
              </div>
              <!-- Password -->
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" placeholder="Enter your password" required>
              </div>
              <!-- Confirm Password -->
              <div class="mb-3">
                <label for="confirm-password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm-password" placeholder="Confirm your password" required>
              </div>
              <!-- Submit Button -->
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
              </div>
            </form>
          </div>
          <div class="card-footer text-center">
            <small>Already have an account? <a href="/?send_to=/account/">Login here</a></small>
          </div>
        </div>
      </div>
    </div>
  </div>
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="responseModalLabel">Message</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="responseMessage">
          <!-- Message content will be injected here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
<script>
// Grab the form element
    const registerForm = document.getElementById('registerForm');
    const responseModal = new bootstrap.Modal(document.getElementById('responseModal')); // Initialize the modal
    const responseMessage = document.getElementById('responseMessage'); // Modal body for messages

    // Listen for form submission
    registerForm.addEventListener('submit', async (event) => {
      event.preventDefault(); // Prevent the default form submission

      // Get form values
      const username = document.getElementById('username').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm-password').value;
      const telegram = document.getElementById('telegram').value.trim();

      // Validation
      if (username === '') {
        showModalMessage('Error', 'Username is required!');
        return;
      }

      if (password.length < 12) {
        showModalMessage('Error', 'Password must be at least 12 characters long!');
        return;
      }

      if (password !== confirmPassword) {
        showModalMessage('Error', 'Passwords do not match!');
        return;
      }

      // Prepare data to send
      const formData = {
        username: username,
        email: email,
        password: password,
	telegram: telegram
      };

      try {
        // Send data to the server using fetch
        const response = await fetch('/api/register/register_user.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json', // JSON format
          },
          body: JSON.stringify(formData), // Convert form data to JSON string
        });

        // Process the server's response
        if (response.ok) {
          const result = await response.json();
          if (result.success) {
            showModalMessage('Success', 'Registration successful!');
            // Redirect to a different page if needed after closing the modal
            setTimeout(() => {
              <?php
              	if(empty($_SESSION["end_url"]))
              		echo("window.location.href = '/?send_to=/account/';");
              	else
              		echo("window.location.href = '/?send_to=".$_SESSION["end_url"]."';");
              ?>
            }, 2000);
          } else {
            showModalMessage('Error', result.message || 'Registration failed!');
          }
        } else {
          showModalMessage('Error', 'An error occurred while registering. Please try again.');
        }
      } catch (error) {
        console.error('Error:', error);
        showModalMessage('Error', 'Unable to register. Please try again later.');
      }
    });

    // Function to display a modal message
    function showModalMessage(title, message) {
      document.getElementById('responseModalLabel').textContent = title; // Set the modal title
      responseMessage.textContent = message; // Set the modal body message
      responseModal.show(); // Show the modal
    }
</script>
</body>
</html>
