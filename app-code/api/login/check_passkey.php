<?php
header('Content-Type: application/json');
require_once 'WebAuthn.php';
include "../../config/config.php";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD,$DB_DATABASE);
if ($conn->connect_error) {
	$success=0;
	die("Connection failed: " . $conn->connect_error);
}

try {
    session_start();

    // read get argument and post body
    $fn = filter_input(INPUT_GET, 'fn');
    $requireResidentKey = !!filter_input(INPUT_GET, 'requireResidentKey');
    $userVerification = filter_input(INPUT_GET, 'userVerification', FILTER_SANITIZE_SPECIAL_CHARS);

    $userId = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_SPECIAL_CHARS);
    $userName = filter_input(INPUT_GET, 'userName', FILTER_SANITIZE_SPECIAL_CHARS);
    $userDisplayName = filter_input(INPUT_GET, 'userDisplayName', FILTER_SANITIZE_SPECIAL_CHARS);

    $userId = preg_replace('/[^0-9a-f]/i', '', $userId);
    $userName = preg_replace('/[^0-9a-z]/i', '', $userName);
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
        // Get create arguments
        $createArgs = $WebAuthn->getCreateArgs(\hex2bin($userId), $userName, $userDisplayName, 60*4, $requireResidentKey, $userVerification);
        header('Content-Type: application/json');
        print(json_encode($createArgs));

        // Save challenge to session or somewhere else if needed
    } else if ($fn === 'getGetArgs') {
        $ids = [];

		//get registrations form user table
		//put credential id into session where userid = $userId
		
		$stmt = $conn->prepare("SELECT credential_id FROM users WHERE username = ?");
		$stmt->bind_param("s", $userName);
        $stmt->execute();
		$registration = $stmt->get_result();
		$row = $registration->fetch_assoc();
		
        
        if ($registration->num_rows <= 0) {
            throw new Exception('User does not exist');
        }
		
		$_SESSION["registrations"]["credentialId"]=$row["credential_id"];
		
		$ids[]=$row["credential_id"];
        $_SESSION["registrations"]["userId"]=$userId;

        $getArgs = $WebAuthn->getGetArgs($ids, 60*4, $typeUsb, $typeNfc, $typeBle, $typeHyb, $typeInt, $userVerification);

        header('Content-Type: application/json');
        print(json_encode($getArgs));

        // save challange to session. you have to deliver it to processGet later.
        $_SESSION['challenge'] = $WebAuthn->getChallenge();

    }else if ($fn === 'processGet') {
        // Process get
        // Retrieve registration data from the database based on credential ID
        $id = base64_decode($post->id);
        $stmt = $conn->prepare("SELECT * FROM users WHERE credential_id = ?");
        $stmt->bind_param("s", $_SESSION["registrations"]["credentialId"]);
        $stmt->execute();
		$registration = $stmt->get_result();
		$row = $registration->fetch_assoc();
        
        if (!$registration) {
            throw new Exception('Public Key for credential ID not found!');
        }

        $clientDataJSON = base64_decode($post->clientDataJSON);
        $authenticatorData = base64_decode($post->authenticatorData);
        $signature = base64_decode($post->signature);
        $userHandle = base64_decode($post->userHandle);
        $challenge = $_SESSION['challenge'] ?? '';
        $credentialPublicKey = $row['public_key'];

        // Process the get request
        $WebAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge, null, $userVerification === 'required');

        // Authentication success
		//set sessionso user is authenticated
		$_SESSION["mfa_authenticated"]=1;
		$_SESSION["pw_authenticated"]=1;
		$_SESSION["passkey_authenticated"]=1;
		$return = new stdClass();
		$return->success = true;
		
		
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
