// login.php

session_start();

use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;

// Fetch stored credential information for the user
$storedCredentialId = $_SESSION['credential_id']; // Replace with DB fetch
$storedPublicKey = $_SESSION['public_key']; // Replace with DB fetch

$options = new PublicKeyCredentialRequestOptions(random_bytes(16)); // Challenge
$_SESSION['request_options'] = serialize($options);

header('Content-Type: application/json');
echo json_encode($options, JSON_UNESCAPED_SLASHES);
