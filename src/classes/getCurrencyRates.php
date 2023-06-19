<?php

function getCurrencyRates($date='today', $endDate='', $table='c'){
    // Function to retrieve currency exchange rates from the NBP API
    // $table: the table of currency exchange rates to retrieve (e.g. A for A/W)
    // $date: the date of the exchange rates to retrieve (in format YYYY-MM-DD)
    // $endDate: the end date of the range of exchange rates to retrieve (in format YYYY-MM-DD)

    // Construct the API URL with the provided parameters
    $url = "http://api.nbp.pl/api/exchangerates/tables/";
    $url .= "$table/$date/$endDate";

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options to retrieve the data from the API
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL session and retrieve the response
    $response = curl_exec($ch);

    // Close the cURL session
    curl_close($ch);

    // Check if the response is an error message (404 Not Found or 400 Bad Request)
    if(str_starts_with($response, '404 NotFound') || str_starts_with($response, '400 BadRequest')){
        return false;
    }

    // Decode the 
    $result = json_decode($response, true);
    
    return $result[0];
}