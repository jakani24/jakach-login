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
            <form id="twofaForm">
              <!-- password Input -->
              <div class="mb-3">
                <input autofocus type="text" id="twofa" name="twofa" class="form-control" placeholder="2FA pin" required>
              </div>
              <!-- Submit Button -->
		<div class="d-grid gap-2">
		  <!-- Login Button -->
		  <button type="submit" class="btn btn-primary btn-lg">Check</button>
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
                    Wrong 2fa pin!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<script>
    // Select the form
    const passwordForm = document.getElementById('twofaForm');

    // Add submit event listener
    passwordForm.addEventListener('submit', async (event) => {
      event.preventDefault(); // Prevent the default form submission behavior

      // Get the username input value
      const passwordInput = document.getElementById('twofa');
      const password = passwordInput.value;

      // Check if username is empty (just in case)
      if (!password) {
        alert('Please enter a 2fa pin.');
        return;
      }
	try {
	    // Send POST request to the API
	    const response = await fetch('/api/login/check_mfa.php', {
	        method: 'POST',
	        headers: {
	            'Content-Type': 'application/x-www-form-urlencoded', // Form-like data
	        },
	        body: new URLSearchParams({
	            twofa_pin: password, // Send password as form data
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
	        alert(`Error: ${errorData.message || 'Failed to check 2fa pin.'}`);
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

