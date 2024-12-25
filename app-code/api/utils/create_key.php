<?php
function generateBase32Secret($length = 16) {
    // Generate a random binary string (length in bytes)
    $randomBytes = random_bytes($length); // Length in bytes (16 bytes = 128 bits)

    // Encode the binary string to Base32
    $base32Secret = base32_encode($randomBytes);
    
    return $base32Secret;
}

// Function to encode to Base32
function base32_encode($data) {
    $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
    $encoded = '';
    $buffer = 0;
    $bitsLeft = 0;

    foreach (str_split($data) as $char) {
        $buffer = ($buffer << 8) | ord($char);
        $bitsLeft += 8;

        while ($bitsLeft >= 5) {
            $bitsLeft -= 5;
            $encoded .= $base32Chars[($buffer >> $bitsLeft) & 31];
            $buffer &= (1 << $bitsLeft) - 1;
        }
    }

    if ($bitsLeft > 0) {
        $encoded .= $base32Chars[($buffer << (5 - $bitsLeft)) & 31];
    }

    return $encoded;
}

?>
