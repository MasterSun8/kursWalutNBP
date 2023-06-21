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
    require_once("config.php");
    require_once("classes/CurrencyRates.php");
    require_once("classes/APIParser.php");
    require_once("classes/DBClient.php");

    $dbClient = new DBClient(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $result = getCurrencyRates();
    if (!$result) {
        $currencies = $dbClient->getDB(true);
    } else {
        $currencies = getAllCurrencies($result);
    }

    if ($currencies && !isset($_POST['database'])) {
        $dbClient->insertTodayRates($currencies);
    }

    global $val;
    global $from;
    global $to;
    global $currFrom;
    global $currTo;
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

    $results = (isset($val) && isset($from) && isset($to));

    if (!$results) {
        $val = 0;
        $from = 1;
        $to = 1;
        $currFrom = "PLN";
        $currTo = "PLN";
    }

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
                $dbClient->getHistory(7);
                ?>
            </tbody>
        </table>
    <?php
    } else {
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