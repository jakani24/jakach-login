<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <?php
    include "../assets/components.php";
    ?>

</head>

<body>
    <div class="container mt-5">
        <h2>Password Reset</h2>

        <?php
        // Check if the 'token' GET parameter is present
        if (!isset($_GET['token'])) {
            echo '<div class="alert alert-danger" role="alert">Invalid or missing token!</div>';
            exit;
        }
        $token = $_GET['token'];
        ?>

        <form id="resetForm">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <!-- New Password -->
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
		<br>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>

        <div id="responseMessage" class="mt-3"></div>
    </div>

    
    <script>
        document.getElementById("resetForm").addEventListener("submit", async function(event) {
            event.preventDefault(); // Prevent the form from submitting the traditional way

            // Get form data
            const formData = new FormData(this);

            // Send data to the backend using fetch API
            try {
                const response = await fetch('/api/login/reset_pw.php', {
                    method: 'POST',
                    body: formData
                });

                // Handle response
                const result = await response.json();

                const responseMessage = document.getElementById("responseMessage");
                if (response.ok && result.status === "success") {
                    responseMessage.innerHTML = `<div class="alert alert-success" role="alert">${result.message}</div>`;
                } else {
                    responseMessage.innerHTML = `<div class="alert alert-danger" role="alert">${result.message}</div>`;
                }
            } catch (error) {
                // Handle any network errors
                const responseMessage = document.getElementById("responseMessage");
                responseMessage.innerHTML = `<div class="alert alert-danger" role="alert">There was an error processing your request. Please try again later.</div>`;
            }
        });
    </script>
</body>

</html>

