// registration.php

session_start();

use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\AuthenticatorAttestationResponseValidator;

// Server configuration
$rpEntity = new PublicKeyCredentialRpEntity('Example App', 'example.com');

// Fetch or create user
$userId = bin2hex(random_bytes(16)); // Use a unique identifier per user
$_SESSION['user_id'] = $userId; // Save it for verification
$user = new PublicKeyCredentialUserEntity($userId, 'username', 'User Display Name');

// Generate options
$options = new PublicKeyCredentialCreationOptions(
    $rpEntity,
    $user,
    random_bytes(16), // Challenge
    [
        ['type' => 'public-key', 'alg' => -7], // Algorithms
    ],
    new AuthenticatorSelectionCriteria(),
    PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE
);

// Save options in session for later verification
$_SESSION['creation_options'] = serialize($options);

header('Content-Type: application/json');
echo json_encode($options, JSON_UNESCAPED_SLASHES);
