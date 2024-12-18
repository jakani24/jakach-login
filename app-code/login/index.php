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
<div class="d-flex flex-column justify-content-center align-items-center" style="height: 100vh;">
    <!-- Spinner -->
    <div class="spinner-border text-primary mb-3" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    <!-- Redirecting Text -->
    <p class="text-center fs-4">Redirecting...</p>
</div>
<script>
	const redirect_api="/api/login/redirect.php";


	async function redirect() {
      	 try {
        	const response = await fetch(redirect_api);

        	// Check if the response is OK
        	if (!response.ok) {
        	  throw new Error(`HTTP error! status: ${response.status}`);
        	}

        	// Parse the JSON response
        	const data = await response.json();

        	// Extract the 'redirect' property from the response
        	const redirectUrl = data.redirect;

        	// Check if the redirect URL exists
        	if (redirectUrl) {
	          // Redirect the user to the URL
	          window.location.href = redirectUrl;
	        }
	      } catch (error) {
        	// Handle errors (e.g., network issues or API errors)
        	console.error("Error fetching data:", error);
     	     }
    	}

    // Call the function on page load
    redirect();
</script>
</body>
</html>
