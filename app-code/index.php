<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <!-- here the user provides his username. we will then send him around yey :) -->

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jakach Login</title>
  <?php
	include "assets/components.php";
	session_start();
	$_SESSION["end_url"]=$_GET["send_to"];
	if (isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] === true) {
	    header("LOCATION:/login/");
	    exit();
	}
  ?>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-4">
        <!-- Card for the form -->
        <div class="card shadow">
          <div class="card-body">
            <h4 class="card-title text-center mb-4">Jakach Login</h4>
		<?php
                        if(isset($_GET["user_not_found"])){
                                echo("<div class='alert alert-danger'>Account not found!</div>");
                        }
                ?>
            <!-- Form -->
            <form id="usernameForm">
              <!-- Username Input -->
              <div class="mb-3">
                <input type="text" autofocus id="username" name="username" class="form-control" placeholder="Benutzername" required>
              </div>
              <!-- Submit Button -->
		<div class="d-grid gap-2">
		  <!-- Login Button -->
		  <button type="submit" class="btn btn-primary btn-lg">Einloggen</button>
		  <!-- Register Button -->
		  <a class="btn btn-outline-primary btn-lg" href="/register/">Account Erstellen</a>
		</div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Account not found!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<script>
    // Select the form
    const usernameForm = document.getElementById('usernameForm');

    // Add submit event listener
    usernameForm.addEventListener('submit', async (event) => {
      event.preventDefault(); // Prevent the default form submission behavior

      // Get the username input value
      const usernameInput = document.getElementById('username');
      const username = usernameInput.value;

      // Check if username is empty (just in case)
      if (!username) {
        alert('Please enter a username.');
        return;
      }

      try {
        // Send POST request to the API
	const response = await fetch('/api/login/set_username.php', {
	    method: 'POST',
	    headers: {
	        'Content-Type': 'application/x-www-form-urlencoded', // Form-like data
	    },
	    body: new URLSearchParams({
	        username: username, // Send username as form data
	    }),
	});
        // Check if the request was successful
        if (response.ok) {
          // Redirect to /login/ upon success
          window.location.href = '/login/';
        } else {
          // Handle errors
          const errorData = await response.json();
          alert(`Error: ${errorData.message || 'Failed to set username.'}`);
        }
      } catch (error) {
        // Handle network errors
        console.error('Error:', error);
        alert('An error occurred. Please try again later.');
      }
    });
</script>
<?php
        // Check if the GET parameter "user_not_found" is set
        if (isset($_GET["user_not_found"])) {
            echo "
            <script>
                // Use JavaScript to trigger the modal to open
                var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            </script>
            ";
        }
?>
</body>
</html>

