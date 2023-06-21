<?php
function parseAPICall($result)
{
    // Check if the result is empty or null
    if (!$result) {
        return false; // Return false if the result is invalid
    }

    // Encode the result as a pretty JSON string
    $json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // Output the JSON string with preserved formatting using <pre> tag
    echo '<pre>' . $json . '</pre>';
}

// Function to extract all currencies from the API call result and return an array of currency data
function getAllCurrencies($result)
{
    // Check if the result is empty or null
    if (!$result) {
        return false; // Return false if the result is invalid
    }

    // Extract the rates from the result
    $result = $result['rates'];

    // Initialize an empty array to store the currency data
    $currencies = array();

    // Add the base currency (PLN) to the array
    $currencies[0] = array('PLN', 1, 1);

    // Loop through the rates and add each currency to the array
    foreach ($result as $key => &$value) {
        $currencies[$key + 1] = array($value['code'], $value['bid'], $value['ask']);
    }

    // Return the array of currency data
    return $currencies;
}
