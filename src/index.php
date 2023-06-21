<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Waluty NBP</title>
</head>

<body>
    <?php
    // Include necessary files
    require_once("config.php");
    require_once("classes/CurrencyRates.php");
    require_once("classes/APIParser.php");
    require_once("classes/DBClient.php");

    // Create a new instance of the DBClient class
    $dbClient = new DBClient(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // Get the latest currency rates from the API
    $result = getCurrencyRates();

    // If the API call fails, get the currency rates from the database
    if (!$result) {
        $currencies = $dbClient->getDB(true);
    } else {
        // If the API call succeeds, get all the currencies and their rates
        $currencies = getAllCurrencies($result);
    }

    // If there are currencies, insert them into the database
    if ($currencies && !isset($_POST['database'])) {
        $dbClient->insertTodayRates($currencies);
    }

    // Define global variables for currency conversion
    global $val;
    global $from;
    global $to;
    global $currFrom;
    global $currTo;

    // If the user has submitted a currency conversion form, update the global variables
    if (isset($_POST['from']) && isset($_POST['to'])) {
        if (isset($_POST['value'])) {
            $val = floatval($_POST['value']);
        }
        foreach ($currencies as &$value) {
            if ($value[0] == $_POST['from']) {
                $currFrom = $_POST['from'];
                $from = $value[1];
            }
            if ($value[0] == $_POST['to']) {
                $currTo = $_POST['to'];
                $to = $value[2];
            }
        }
    }

    // Check if the form has been filled with correct values
    $results = (isset($val) && isset($from) && isset($to));

    // If the form has not been submitted, set default values for the currency conversion
    if (!$results) {
        $val = 0;
        $from = 1;
        $to = 1;
        $currFrom = "PLN";
        $currTo = "PLN";
    }

    // If the exchange has been performed, convert the currency and record it in the database
    if (!isset($_POST['database']) && $currencies) {
        $exchange = exchangeCurrency($val, $from, $to);
        if ($results) {
            $dbClient->recordExchange($currFrom, $currTo, $val, $exchange);
        }
    ?>
        <form method="POST">
            <div class="result">
                <?php echo $val . " " . $currFrom . " to " . $currTo; ?>
            </div>
            <br>
            <div id="currencies">
                <div>
                    <input name="value" id="value" type="number" min="0" step="0.01" value="<?php echo $val; ?>">
                    <br>
                    <select name="from" id="from">
                        <?php
                        // Loop through the currencies and create an option for each one
                        foreach ($currencies as &$value) {
                            echo "<option value='$value[0]'";
                            if ($value[0] == $currFrom) {
                                echo " selected";
                            }
                            echo ">$value[0] &nbsp</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <input type="number" value="<?php echo $exchange ?>" readonly>
                    <br>
                    <select name="to" id="to">
                        <?php
                        // Loop through the currencies and create an option for each one
                        foreach ($currencies as &$value) {
                            echo "<option value='$value[0]' ";
                            if ($value[0] == $currTo) {
                                echo "selected";
                            }
                            echo ">$value[0] &nbsp</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <br>
            <div id="button">
                <div class="buttons">
                    <input type="submit" name="exchange" value="Exchange">
                </div>
                <div class="buttons">
                    <input type="submit" name="database" value="Show database">
                </div>
            </div>
        </form>
        <br>
        <table>
            <thead>
                <tr>
                    <th>From</th>
                    <th>To</th>
                    <th>Exchanged</th>
                    <th>Got</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get the exchange history from the database and return it as a table
                $dbClient->getHistory(7);
                ?>
            </tbody>
        </table>
    <?php
    }else{ // Show the database
    ?>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Bid</th>
                    <th>Ask</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Draw the table
                $dbClient->drawDataFromDB();
                ?>
            </tbody>
        </table>
        <br>
        <form method="post">
            <input type="submit" name="exchange" value="Exchange">
        </form>
    <?php
    }
    ?>

</body>

</html>