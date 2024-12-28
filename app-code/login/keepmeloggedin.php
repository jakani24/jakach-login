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
              <!-- Submit Button -->
		<div class="d-grid gap-2">
		  <!-- Login Button -->
		  <a class="btn btn-primary btn-lg" onclick="send_request('true');">Remember me</a>
		  <a class="btn btn-primary btn-lg" onclick="send_request('false');">Do not remember me</a>
		</div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>


<script>
    // Select the form
    async function send_request(keepmeloggedin){
   	const response = await fetch('/api/login/keepmeloggedin.php', {
	        method: 'POST',
	        headers: {
	            'Content-Type': 'application/x-www-form-urlencoded', // Form-like data
	        },
	        body: new URLSearchParams({
	            keepmeloggedin: keepmeloggedin, // Send password as form data
	        }),
	    });
	    
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
	    }
    
    }
</script>
</body>
</html>

