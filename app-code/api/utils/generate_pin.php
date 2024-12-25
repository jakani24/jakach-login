<?php
function base32_decode($base32) {
    $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $base32 = strtoupper($base32);
    $buffer = 0;
    $bitsLeft = 0;
    $decoded = '';

    foreach (str_split($base32) as $char) {
        $value = strpos($base32Chars, $char);
        if ($value === false) {
            continue;
        }

        $buffer = ($buffer << 5) | $value;
        $bitsLeft += 5;

        if ($bitsLeft >= 8) {
            $bitsLeft -= 8;
            $decoded .= chr(($buffer >> $bitsLeft) & 0xFF);
        }
    }

    return $decoded;
}

function generateTOTP($secret) {
    // Convert the secret from Base32 to binary
    $secretBinary = base32_decode($secret);

    // Get the current time in seconds
    $time = floor(time() / 30); // 30-second time step
    
    // Convert the time to a 8-byte string
    $timeBytes = pack('N*', 0) . pack('N*', $time);  // Pack time as an 8-byte string

    // Hash the time with the secret key using HMAC-SHA1
    $hash = hash_hmac('sha1', $timeBytes, $secretBinary, true); 

    // Extract a 4-byte dynamic offset from the hash
    $offset = ord($hash[19]) & 0x0F; 

    // Calculate the 6-digit code by truncating the hash
    $code = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;  // Ensure non-negative
    
    // Modulo 10^6 to get the final 6-digit code
    $otp = $code % 1000000;
    
    // Pad with leading zeros if necessary
    return str_pad($otp, 6, '0', STR_PAD_LEFT);
}

?>
