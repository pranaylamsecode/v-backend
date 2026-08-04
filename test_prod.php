<?php

function apiRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://dump.lambodragroup.in/api' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['status' => $status, 'body' => $response];
}

// Try to login as an existing employee or get 401
$login = apiRequest('/employee/login', 'POST', [
    'email' => 'pranay.l@veerit.com', // typical test email, if it fails we can't do much
    'password' => 'password' // guess
]);

echo "Login Response:\n";
print_r($login);

// Just try an endpoint without auth to see if we get a 500 or 401
$res = apiRequest('/employee/overview-stats');
echo "\nOverview Stats Response (No Auth):\n";
print_r($res);
