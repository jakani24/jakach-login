<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jakach Login</title>
  <?php
	include "../assets/components.php";
	session_start();
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
            <!-- Form -->
            <form id="passwordForm">
              <!-- password Input -->
              <div class="mb-3">
                <input autofocus type="password" id="password" name="password" class="form-control" placeholder="Password" required>
              </div>
              <!-- Submit Button -->
		<div class="d-grid gap-2">
		  <!-- Login Button -->
		  <button type="submit" class="btn btn-primary btn-lg">Check</button>
		  <?php
			if($_SESSION["passkey_required"]==1){
				echo('<a class="btn btn-primary btn-lg" href="/login/passkey.php">Use passkey instead</a>');
			}
		?>
		<center><a href="#" onclick="reset_pw();">Forgott password</a></center>
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
                    Wrong password!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Password reset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    If you have a Telegram Id or an email address linked to your account, we have sent you a reset link.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<script>
	//pw reset:
	function reset_pw(){
		fetch("/api/login/send_reset_link.php");
		var resetModal = new bootstrap.Modal(document.getElementById('resetModal'));
		resetModal.show();
	}

    // Select the form
    const passwordForm = document.getElementById('passwordForm');

    // Add submit event listener
    passwordForm.addEventListener('submit', async (event) => {
      event.preventDefault(); // Prevent the default form submission behavior

      // Get the username input value
      const passwordInput = document.getElementById('password');
      const password = passwordInput.value;

      // Check if username is empty (just in case)
      if (!password) {
        alert('Please enter a password.');
        return;
      }
	try {
	    // Send POST request to the API
	    const response = await fetch('/api/login/check_pw.php', {
	        method: 'POST',
	        headers: {
	            'Content-Type': 'application/x-www-form-urlencoded', // Form-like data
	        },
	        body: new URLSearchParams({
	            password: password, // Send password as form data
	        }),
	    });

	    // Check if the request was successful
	    if (response.ok) {
	        // Await response.json() to parse the JSON
	        const data = await response.json();

	        if (data.status === 'success') {
	            // Redirect to /login/
	            window.location.href = '/login/';
	        } else {
	            // Show the error modal on failure
	            var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
	            errorModal.show();
	            // Optionally, display the error message
	            console.error("Error: ", data.message || "Unknown error");
	        }
	    } else {
	        // Handle HTTP errors (non-2xx status codes)
	        const errorData = await response.json();
	        alert(`Error: ${errorData.message || 'Failed to check password.'}`);
	    }
	} catch (error) {
	    // Handle network or unexpected errors
	    console.error('Error:', error);
	    alert('An error occurred. Please try again later.');
	}


    });
</script>
</body>
</html>

