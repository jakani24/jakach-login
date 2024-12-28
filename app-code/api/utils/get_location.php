<?php

function get_location_from_ip($ip) {
    // Use ip-api.com to fetch geolocation data
    $url = "http://ip-api.com/json/$ip";

    // Initialize curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute curl and decode the JSON response
    $response = curl_exec($ch);
    curl_close($ch);

    // Convert JSON response to PHP array
    $data = json_decode($response, true);

    // Check for a successful response
    if ($data && $data['status'] === 'success') {
        return $data; // Return the geolocation data
    }

    return null; // Return null if API call fails
}

?>
