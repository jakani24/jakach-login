<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("LOCATION:/?send_to=/account/");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Page</title>
  <!-- Bootstrap CSS -->
  <?php  
    include "../assets/components.php";  
  ?>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> <!-- Google Material Icons -->
</head>
<body>
  <!-- Main Container -->
  <div class="container my-5">
    <div class="row">
      <!-- Profile Sidebar -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <h3 id="user-name" class="card-title">Loading...</h3>
            <p id="user-email" class="text-muted">Loading...</p>
          </div>
        </div>
      </div>

      <!-- Account Settings -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <!-- Tabs for General and Password sections -->
            <ul class="nav nav-tabs" id="accountTab" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true"><span class="material-icons">person</span></a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="password-tab" data-bs-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false"><span class="material-icons">lock</span></a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="2fa-tab" data-bs-toggle="tab" href="#2fa" role="tab" aria-controls="2fa" aria-selected="false"><span class="material-icons">security</span></a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="passkey-tab" data-bs-toggle="tab" href="#passkey" role="tab" aria-controls="passkey" aria-selected="false"><span class="material-icons">fingerprint</span></a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="message-tab" data-bs-toggle="tab" href="#message" role="tab" aria-controls="message" aria-selected="false"><span class="material-icons">message</span></a>
              </li>
              <?php
              	if($_SESSION["permissions"][0]==="1"){
              		echo('<li class="nav-item" role="presentation">
                <a class="nav-link" href="/account/manage_users.php" role="tab" aria-controls="message" aria-selected="false"><span class="material-icons">people</span></a>
              </li>');
              	}
              ?>
              <li class="nav-item" role="presentation">
                <a class="nav-link"  href="/login/logout.php" role="tab" aria-selected="false"><span class="material-icons">logout</span></a>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <!-- Tab Content -->
            <div class="tab-content" id="accountTabContent">
              <!-- General Account Details -->
              <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <form id="account-form">
                  <!-- Name -->
                  <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" placeholder="Loading...">
                  </div>

                  <!-- Email -->
                  <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" placeholder="Set email">
                  </div>

                  <!-- Telegram ID -->
                  <div class="mb-3">
                    <label for="telegram" class="form-label">Telegram ID</label>
                    <input type="text" class="form-control" id="telegram" placeholder="Set telegram id">
                  </div>
                  
                  
                  <div class="mb-3">
                    <label for="user_token" class="form-label">User token</label>
                    <input type="text" class="form-control" id="user_token" disabled>
                  </div>
                  
                  <div class="mb-3">
                    <label for="last_login" class="form-label">Last login</label>
                    <input type="text" class="form-control" id="last_login" disabled>
                  </div>

                  <!-- Save Changes Button -->
                  <button type="button" id="save-button" class="btn btn-success">Save Changes</button>
                  <a class="btn btn-danger" onclick="delete_all_logmein();">Delete all &quot;remember me&quot; sessions</a>
                </form>
              </div>

              <!-- Password Change Section -->
              <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                <form id="password-form">
                  <!-- Old Password -->
                  <div class="mb-3">
                    <label for="old-password" class="form-label">Old Password</label>
                    <input type="password" class="form-control" id="old-password" placeholder="Enter old password" required>
                  </div>

                  <!-- New Password -->
                  <div class="mb-3">
                    <label for="new-password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new-password" placeholder="Enter new password" required>
                  </div>

                  <!-- Confirm New Password -->
                  <div class="mb-3">
                    <label for="confirm-password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm-password" placeholder="Confirm new password" required>
                  </div>

                  <!-- Change Password Button -->
                  <button type="button" id="change-password-button" class="btn btn-warning">Change Password</button>
                </form>
              </div>
              <div class="tab-pane fade" id="2fa" role="tabpanel" aria-labelledby="2fa-tab">
                <p>If you enable this you will need to enter a pin before beeing able to log in.</p>
                <div class="form-check form-switch">
		  <input class="form-check-input" type="checkbox" id="twofa-switch">
		  <label class="form-check-label" for="twofa-switch">Enable 2FA</label>
		</div>

              </div>
              <div class="tab-pane fade" id="passkey" role="tabpanel" aria-labelledby="passkey-tab">
                <p>Using a passkey you can login with e.g. your fingerprint. If this is enabled you can still login using your password but using a passkey is faster.</p>
		<br>
		<button id="create-passkey" class="btn btn-primary" onclick="createRegistration()">Register passkey</button>
              </div>
              <div class="tab-pane fade" id="message" role="tabpanel" aria-labelledby="message-tab">
                <p>You can get a message via telegram whenever somebody logs in to your account</p>
		<div class="form-check form-switch">
		  <input class="form-check-input" type="checkbox" id="message-switch">
		  <label class="form-check-label" for="message-switch">Enable login messages</label>
		</div>
              </div>
            </div>
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

  <!-- Success Modal -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="successModalLabel">Success</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="successModalMessage">
          <!-- Success message will go here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Fetch user data from the backend API
      fetch('/api/account/get_user_data.php') // Replace with the actual API endpoint
        .then(response => {
          if (!response.ok) {
            throw new Error('Failed to fetch user data');
          }
          return response.json();
        })
        .then(user => {
          // Populate the fields with user data
          document.getElementById('user-name').textContent = user.name;
          document.getElementById('user-email').textContent = user.email;

          // Fill input fields
          document.getElementById('name').value = user.name;
          document.getElementById('user_token').value = user.user_token;
          document.getElementById('email').value = user.email;
          document.getElementById('telegram').value = user.telegram_id;
          document.getElementById('twofa-switch').checked = user.twofa_enabled;
          document.getElementById('message-switch').checked = user.login_message;
          document.getElementById('last_login').value = user.last_login;
          
        })
        .catch(error => {
          console.error('Error:', error);
          showErrorModal('Failed to load user data. Please try again later.');
        });

      // Handle Save Changes button click
      const saveButton = document.getElementById('save-button');
      saveButton.addEventListener('click', () => {
        const updatedUser = {
          name: document.getElementById('name').value,
          email: document.getElementById('email').value,
          telegram_id: document.getElementById('telegram').value
        };

        // Send updated data to the backend
        fetch('/api/account/update_user_data.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(updatedUser)
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Failed to save user data');
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            document.getElementById('user-name').textContent = updatedUser.name;
            document.getElementById('user-email').textContent = updatedUser.email;
          } else {
            showErrorModal('Failed to update user data. ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showErrorModal('Failed to save user data. Please try again later.');
        });
      });

      // Handle Change Password button click
      const changePasswordButton = document.getElementById('change-password-button');
      changePasswordButton.addEventListener('click', () => {
        const oldPassword = document.getElementById('old-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        // Validate password fields
        if (newPassword !== confirmPassword) {
          showErrorModal('New password and confirm password do not match.');
          return;
        }

        const passwordData = {
          old_password: oldPassword,
          new_password: newPassword
        };

        // Send password change request to the backend
        fetch('/api/account/update_pw.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(passwordData)
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Failed to change password');
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            delete_all_logmein();
            showSuccessModal('Password updated.');
          } else {
            showErrorModal('Failed to update password. ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showErrorModal('Failed to change password. Please try again later.');
        });
      });

      
      
      ////////////////////////////////////////////
      const switchElement = document.getElementById('twofa-switch');

    // Add an event listener for when the switch is changed
    switchElement.addEventListener('change', async function () {
      // Get the current state of the switch
      const isEnabled = switchElement.checked;

      try {
        // Send the state to the backend using a POST request
        const response = await fetch('/api/account/update_2fa.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            enable_2fa: isEnabled, // Send the new state of 2FA
          }),
        });

        // Check if the response is successful
        const result = await response.json();
        if (response.ok) {
          // Handle success
          showSuccessModal(result.message || (isEnabled ? '2FA enabled successfully.' : '2FA disabled successfully.'));
        } else {
          // Handle error
          showErrorModal('Error: ' + (result.message || 'An error occurred while updating 2FA.'));
        }
      } catch (error) {
        console.error('Error:', error);
        showErrorModal('Failed to send request. Please try again later.');
      }
    });
    
    const switchElement2 = document.getElementById('message-switch');

    // Add an event listener for when the switch is changed
    switchElement2.addEventListener('change', async function () {
      // Get the current state of the switch
      const isEnabled = switchElement2.checked;
	if(document.getElementById('telegram').value.length!=0){
	      try {
		// Send the state to the backend using a POST request
		const response = await fetch('/api/account/update_message.php', {
		  method: 'POST',
		  headers: {
		    'Content-Type': 'application/json',
		  },
		  body: JSON.stringify({
		    enable_message: isEnabled, // Send the new state of 2FA
		  }),
		});

		// Check if the response is successful
		const result = await response.json();
		if (response.ok) {
		  // Handle success
		  showSuccessModal(result.message || (isEnabled ? 'Login messages enabled successfully.' : 'Login messages disabled successfully.'));
		} else {
		  // Handle error
		  showErrorModal('Error: ' + (result.message || 'An error occurred while updating login messages.'));
		}
	      } catch (error) {
		console.error('Error:', error);
		showErrorModal('Failed to send request. Please try again later.');
	      }
	  }else{
	  	showErrorModal("Please configure your Telegram ID first.");
	  }
    });
    });
    
    // Function to show error modal
      function showErrorModal(message) {
        document.getElementById('errorModalMessage').textContent = message;
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
      }
      function showSuccessModal(message) {
        document.getElementById('successModalMessage').textContent = message;
        const errorModal = new bootstrap.Modal(document.getElementById('successModal'));
        errorModal.show();
      }
    //webauthn js
    
    async function createRegistration() {
            try {

                // check browser support
                if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                    throw new Error('Browser not supported.');
                }

                // get create args
                let rep = await window.fetch('/api/account/update_passkey.php?fn=getCreateArgs' + getGetParams(), {method:'GET', cache:'no-cache'});
                //let rep = await window.fetch('/test/server.php?fn=getCreateArgs' + getGetParams(), {method:'GET', cache:'no-cache'});
                const createArgs = await rep.json();

                // error handling
                if (createArgs.success === false) {
                    throw new Error(createArgs.msg || 'unknown error occured');
                }

                // replace binary base64 data with ArrayBuffer. a other way to do this
                // is the reviver function of JSON.parse()
                recursiveBase64StrToArrayBuffer(createArgs);

                // create credentials
                const cred = await navigator.credentials.create(createArgs);

                // create object
                const authenticatorAttestationResponse = {
                    transports: cred.response.getTransports  ? cred.response.getTransports() : null,
                    clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
                    attestationObject: cred.response.attestationObject ? arrayBufferToBase64(cred.response.attestationObject) : null
                };

                // check auth on server side
                rep = await window.fetch('/api/account/update_passkey.php?fn=processCreate' + getGetParams(), {
                //rep = await window.fetch('/test/server.php?fn=processCreate' + getGetParams(), {
                    method  : 'POST',
                    body    : JSON.stringify(authenticatorAttestationResponse),
                    cache   : 'no-cache'
                });
                const authenticatorAttestationServerResponse = await rep.json();

                // prompt server response
                if (authenticatorAttestationServerResponse.success) {
                    reloadServerPreview();
                    showSuccessModal(authenticatorAttestationServerResponse.msg || 'Registrated passkey successfully');
                } else {
                    throw new Error(authenticatorAttestationServerResponse.msg);
                }

            } catch (err) {
                reloadServerPreview();
                showErrorModal(err.message || 'unknown error occured');
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

            url += '&rpId=auth.jakach.ch';
			
            url += '&userId=' + encodeURIComponent(ascii_to_hex('<?php echo($_SESSION["username"]);?>'));
            url += '&userName=' + encodeURIComponent('<?php echo($_SESSION["username"]);?>');
            url += '&userDisplayName=' + encodeURIComponent('<?php echo($_SESSION["username"]);?>');

            url += '&userVerification=discouraged';

            return url;
        }

        function reloadServerPreview() {
            //let iframe = document.getElementById('serverPreview');
            //iframe.src = iframe.src;
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
	function delete_all_logmein(){
		fetch("/api/login/delete_keepmeloggedin.php");
	}
  </script>

</body>
</html>

