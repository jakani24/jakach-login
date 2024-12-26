<?php
//with db:
header('Content-Type: application/json');



require_once 'WebAuthn.php';

// Assuming you've already established a database connection here
include "../../config/config.php";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD,$DB_DATABASE);
if ($conn->connect_error) {
	$success=0;
	die("Connection failed: " . $conn->connect_error);
}

try {
	session_start();
	if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit();
}

	
    // read get argument and post body
    $fn = filter_input(INPUT_GET, 'fn');
    $requireResidentKey = !!filter_input(INPUT_GET, 'requireResidentKey');
    $userVerification = filter_input(INPUT_GET, 'userVerification', FILTER_SANITIZE_SPECIAL_CHARS);

    $userId = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_SPECIAL_CHARS);
    $userName = filter_input(INPUT_GET, 'userName', FILTER_SANITIZE_SPECIAL_CHARS);
    $userDisplayName = filter_input(INPUT_GET, 'userDisplayName', FILTER_SANITIZE_SPECIAL_CHARS);

    $userId = preg_replace('/[^0-9a-f]/i', '', $userId);
    $userName = preg_replace('/[^0-9a-z]/i', '', $_SESSION["username"]);
    $userDisplayName = preg_replace('/[^0-9a-z öüäéèàÖÜÄÉÈÀÂÊÎÔÛâêîôû]/i', '', $userDisplayName);

    $post = trim(file_get_contents('php://input'));
    if ($post) {
        $post = json_decode($post, null, 512, JSON_THROW_ON_ERROR);
    }

    if ($fn !== 'getStoredDataHtml') {

        // Formats
	$formats = [];
	$formats[] = 'android-key';
	$formats[] = 'android-safetynet';
	$formats[] = 'apple';
	$formats[] = 'fido-u2f';
	$formats[] = 'none';
	$formats[] = 'packed';
	$formats[] = 'tpm';

	$rpId=$_SERVER['SERVER_NAME'];

	$typeUsb = true;
	$typeNfc = true;
	$typeBle = true;
	$typeInt = true;
	$typeHyb = true;

        // cross-platform: true, if type internal is not allowed
        //                 false, if only internal is allowed
        //                 null, if internal and cross-platform is allowed
        $crossPlatformAttachment = null;
        if (($typeUsb || $typeNfc || $typeBle || $typeHyb) && !$typeInt) {
            $crossPlatformAttachment = true;

        } else if (!$typeUsb && !$typeNfc && !$typeBle && !$typeHyb && $typeInt) {
            $crossPlatformAttachment = false;
        }


        // new Instance of the server library.
        // make sure that $rpId is the domain name.
        $WebAuthn = new lbuchs\WebAuthn\WebAuthn('WebAuthn Library', $rpId, $formats);

        // add root certificates to validate new registrations
            $WebAuthn->addRootCertificates('rootCertificates/solo.pem');
            $WebAuthn->addRootCertificates('rootCertificates/apple.pem');
            $WebAuthn->addRootCertificates('rootCertificates/yubico.pem');
            $WebAuthn->addRootCertificates('rootCertificates/hypersecu.pem');
            $WebAuthn->addRootCertificates('rootCertificates/globalSign.pem');
            $WebAuthn->addRootCertificates('rootCertificates/googleHardware.pem');
            $WebAuthn->addRootCertificates('rootCertificates/microsoftTpmCollection.pem');
            $WebAuthn->addRootCertificates('rootCertificates/mds');

    }

    // Handle different functions
    if ($fn === 'getCreateArgs') {
        $createArgs = $WebAuthn->getCreateArgs(\hex2bin($userId), $userName, $userDisplayName, 60*4, $requireResidentKey, $userVerification, $crossPlatformAttachment);

        header('Content-Type: application/json');
        print(json_encode($createArgs));

        // save challange to session. you have to deliver it to processGet later.
        $_SESSION['challenge'] = $WebAuthn->getChallenge();

    } else if ($fn === 'getGetArgs') {
        $ids = [];

        if ($requireResidentKey) {
            if (!isset($_SESSION['registrations']) || !is_array($_SESSION['registrations']) || count($_SESSION['registrations']) === 0) {
                throw new Exception('we do not have any registrations in session to check the registration');
            }

        } else {
            // load registrations from session stored there by processCreate.
            // normaly you have to load the credential Id's for a username
            // from the database.
            if (isset($_SESSION['registrations']) && is_array($_SESSION['registrations'])) {
                foreach ($_SESSION['registrations'] as $reg) {
                    if ($reg->userId === $userId) {
                        $ids[] = $reg->credentialId;
                    }
                }
            }

            if (count($ids) === 0) {
                throw new Exception('no registrations in session for userId ' . $userId);
            }
        }

        $getArgs = $WebAuthn->getGetArgs($ids, 60*4, $typeUsb, $typeNfc, $typeBle, $typeHyb, $typeInt, $userVerification);

        header('Content-Type: application/json');
        print(json_encode($getArgs));

        // save challange to session. you have to deliver it to processGet later.
        $_SESSION['challenge'] = $WebAuthn->getChallenge();
    } else if ($fn === 'processCreate') {
        // Process create
		$challenge = $_SESSION['challenge'];
        $clientDataJSON = base64_decode($post->clientDataJSON);
        $attestationObject = base64_decode($post->attestationObject);

        // Process create and store data in the database
        $data = $WebAuthn->processCreate($clientDataJSON, $attestationObject, $challenge, $userVerification === 'required', true, false);
	
		// add user infos
        $data->userId = $userId;
        $data->userName = $userName;
        $data->userDisplayName = $userDisplayName;

        // Store registration data in the database
	$stmt = $conn->prepare("UPDATE users set  credential_id = ?, public_key = ?, counter = ?, auth_method_enabled_passkey = 1, auth_method_required_passkey = 1 WHERE username = ?");
        $stmt->execute([ $data->credentialId, $data->credentialPublicKey, $data->signatureCounter,$userName]);

        $msg = 'registration success.';
        $return = new stdClass();
        $return->success = true;
        $return->msg = $msg;
        header('Content-Type: application/json');
        print(json_encode($return));
    } 

} catch (Throwable $ex) {
    $return = new stdClass();
    $return->success = false;
    $return->msg = $ex->getMessage();

    header('Content-Type: application/json');
    print(json_encode($return));
}
?>

