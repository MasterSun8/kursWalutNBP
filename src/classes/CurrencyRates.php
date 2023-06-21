<?php

function getCurrencyRates($date = 'today', $endDate = '', $table = 'c')
{
    // Construct the API URL based on the provided parameters
    $url = "http://api.nbp.pl/api/exchangerates/tables/";
    $url .= "$table/$date/$endDate";

    // Initialize a cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL session and retrieve the response
    $response = curl_exec($ch);
    curl_close($ch);

    // Check if the response indicates an error (404 NotFound or 400 BadRequest)
    if (strpos($response, '404 NotFound') || strpos($response, '400 BadRequest')) {
        return false; // Return false if an error occurred
    }

    // Parse the JSON response into an associative array
    $result = json_decode($response, true);

    // Return the first element of the resulting array
    return $result[0];
}

function exchangeCurrency($val, $from, $to){
    // Perform currency conversion using the provided values
    return round($val * ($from / $to), 5);
}
