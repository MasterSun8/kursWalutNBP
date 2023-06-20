<?php
function parseAPICall($result)
{

    if (!$result) {
        return false;
    }

    // Encode the result as a pretty JSON string and echo it to the browser with a <pre> tag to preserve the formatting
    $json = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo '<pre>' . $json . '</pre>';

    // Since, the result didn't evalute to false, return the string.
    return $json;
}

function getAllCurrencies($result)
{
    if (!$result) {
        return false;
    }
    $result = $result['rates'];

    $currencies = array();

    $currencies[0] = array('PLN', 1, 1);

    foreach ($result as $key => &$value) {
        $currencies[$key + 1] = array($value['code'], $value['bid'], $value['ask']);
    }

    return $currencies;
}
