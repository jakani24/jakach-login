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
		  <a href="/login/" class="btn btn-primary btn-lg" id="continueLink">Continue as <?php echo($_SESSION["username"]); ?></a>
		  <a class="btn btn-outline-primary btn-lg" href="/?donotsend&send_to=<?php echo($_SESSION["end_url"]); ?>">Use another account</a>
		</div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
// Listen for the 'Enter' key press on the document
document.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        // If Enter is pressed, click the link with id 'continueLink'
        document.getElementById("continueLink").click();
    }
});
</script>
</body>
</html>

