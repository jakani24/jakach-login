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
		  <button style="align:right" type="button" class="btn btn-primary btn-block" onclick="checkRegistration()">Login with a passkey</button>
		</div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Error Modal -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="errorModalLabel">Error</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="errorModalMessage">
          <!-- Error message will go here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>


<script>
//js that handels our passkey login
function showErrorModal(message) {
        document.getElementById('errorModalMessage').textContent = message;
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
}
async function checkRegistration() {
            try {

                if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                    throw new Error('Browser not supported.');
                }

                // get check args
                let rep = await window.fetch('/api/login/check_passkey.php?fn=getGetArgs' + getGetParams(), {method:'GET',cache:'no-cache'});
                const getArgs = await rep.json();

                // error handling
                if (getArgs.success === false) {
                    throw new Error(getArgs.msg);
                }

                // replace binary base64 data with ArrayBuffer. a other way to do this
                // is the reviver function of JSON.parse()
                recursiveBase64StrToArrayBuffer(getArgs);

                // check credentials with hardware
                const cred = await navigator.credentials.get(getArgs);

                // create object for transmission to server
                const authenticatorAttestationResponse = {
                    id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
                    clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
                    authenticatorData: cred.response.authenticatorData ? arrayBufferToBase64(cred.response.authenticatorData) : null,
                    signature: cred.response.signature ? arrayBufferToBase64(cred.response.signature) : null,
                    userHandle: cred.response.userHandle ? arrayBufferToBase64(cred.response.userHandle) : null
                };

                // send to server
                rep = await window.fetch('/api/login/check_passkey.php?fn=processGet' + getGetParams(), {
                    method:'POST',
                    body: JSON.stringify(authenticatorAttestationResponse),
                    cache:'no-cache'
                });
                const authenticatorAttestationServerResponse = await rep.json();

                // check server response
                if (authenticatorAttestationServerResponse.success) {
                    reloadServerPreview();
                    //window.alert(authenticatorAttestationServerResponse.msg || 'login success');
		   window.location.href = "/login/";
					
                } else {
                    throw new Error(authenticatorAttestationServerResponse.msg);
                }

            } catch (err) {
                reloadServerPreview();
				if(err.message=="User does not exist"){
					//we will display a warning here later on
					showErrorModal("User does not exist!");
				}else{
					showErrorModal(err.message || 'unknown error occured');
				}
            }
        }


        /**
         * convert RFC 1342-like base64 strings to array buffer
         * @param {mixed} obj
         * @returns {undefined}
         */
        function recursiveBase64StrToArrayBuffer(obj) {
            let prefix = '=?BINARY?B?';
            let suffix = '?=';
            if (typeof obj === 'object') {
                for (let key in obj) {
                    if (typeof obj[key] === 'string') {
                        let str = obj[key];
                        if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
                            str = str.substring(prefix.length, str.length - suffix.length);

                            let binary_string = window.atob(str);
                            let len = binary_string.length;
                            let bytes = new Uint8Array(len);
                            for (let i = 0; i < len; i++)        {
                                bytes[i] = binary_string.charCodeAt(i);
                            }
                            obj[key] = bytes.buffer;
                        }
                    } else {
                        recursiveBase64StrToArrayBuffer(obj[key]);
                    }
                }
            }
        }

        /**
         * Convert a ArrayBuffer to Base64
         * @param {ArrayBuffer} buffer
         * @returns {String}
         */
        function arrayBufferToBase64(buffer) {
            let binary = '';
            let bytes = new Uint8Array(buffer);
            let len = bytes.byteLength;
            for (let i = 0; i < len; i++) {
                binary += String.fromCharCode( bytes[ i ] );
            }
            return window.btoa(binary);
        }
		
		function ascii_to_hex(str) {
			let hex = '';
			for (let i = 0; i < str.length; i++) {
				let ascii = str.charCodeAt(i).toString(16);
				hex += ('00' + ascii).slice(-2); // Ensure each hex value is 2 characters long
			}
			return hex;
		}
        /**
         * Get URL parameter
         * @returns {String}
         */
        function getGetParams() {
            let url = '';

            url += '&apple=1';
            url += '&yubico=1';
            url += '&solo=1'
            url += '&hypersecu=1';
            url += '&google=1';
            url += '&microsoft=1';
            url += '&mds=1';

            url += '&requireResidentKey=0';

            url += '&type_usb=1';
            url += '&type_nfc=1';
            url += '&type_ble=1';
            url += '&type_int=1';
            url += '&type_hybrid=1';

            url += '&fmt_android-key=1';
            url += '&fmt_android-safetynet=1';
            url += '&fmt_apple=1';
            url += '&fmt_fido-u2f=1';
            url += '&fmt_none=1';
            url += '&fmt_packed=1';
            url += '&fmt_tpm=1';

            url += '&rpId=auth.jakach.com';

            url += '&userId=' + encodeURIComponent(ascii_to_hex('<?php echo($_SESSION["username"]);?>'));
            url += '&userName=' + encodeURIComponent('<?php echo($_SESSION["username"]);?>');
            url += '&userDisplayName=' + encodeURIComponent('<?php echo($_SESSION["username"]);?>');

            url += '&userVerification=discouraged';

            return url;
        }

        function reloadServerPreview() {
        }

        function setAttestation(attestation) {
            let inputEls = document.getElementsByTagName('input');
            for (const inputEl of inputEls) {
                if (inputEl.id && inputEl.id.match(/^(fmt|cert)\_/)) {
                    inputEl.disabled = !attestation;
                }
                if (inputEl.id && inputEl.id.match(/^fmt\_/)) {
                    inputEl.checked = attestation ? inputEl.id !== 'fmt_none' : inputEl.id === 'fmt_none';
                }
                if (inputEl.id && inputEl.id.match(/^cert\_/)) {
                    inputEl.checked = attestation ? inputEl.id === 'cert_mds' : false;
                }
            }
        }

        /**
         * force https on load
         * @returns {undefined}
         */
        window.onload = function() {
            if (location.protocol !== 'https:' && location.host !== 'localhost') {
                location.href = location.href.replace('http', 'https');
            }
        }

</script>
</body>
</html>

