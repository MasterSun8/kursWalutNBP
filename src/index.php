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
    require_once("src/config.php");
    require_once("classes/getCurrencyRates.php");
    require_once("classes/APIParser.php");
    require_once("classes/DBClient.php");

    $dbClient = new DBClient(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $result;
    $currencies;

    $result = getCurrencyRates();
    $currencies = getAllCurrencies($result);

    $val;
    $from;
    $to;
    if (isset($_POST['from']) && isset($_POST['to'])) {
        if (isset($_POST['value'])) {
            $val = floatval($_POST['value']);
        }
        foreach ($currencies as &$value) {
            if ($value[0] == $_POST['from']) {
                $from = $value[1];
            }
            if ($value[0] == $_POST['to']) {
                $to = $value[2];
            }
        }
    }

    $results = (isset($val) && isset($from) && isset($to));
    if (!$results) {
        $val = 0;
        $from = 1;
        $to = 1;
    }
    ?>

    <form method="POST">
        <div class="result">
            <?php
            if ($results) {
                echo $val . " " . $_POST['from'] . " to " . $_POST['to'];
            }
            ?>
        </div>
        <br>
        <div id="currencies">
            <div>
                <input name="value" id="value" type="number" min="0" step="0.01" value="<?php echo $val; ?>">
                <br>
                <select name="from" id="from">
                    <?php
                    if ($results) {
                        foreach ($currencies as &$value) {
                            echo "<option value='$value[0]'";
                            if ($value[1] == $from) {
                                echo " selected";
                            }
                            echo ">$value[0] &nbsp</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div>
                <input type="number" value="<?php if ($results) {
                                                echo round($val * ($from / $to), 5);
                                            } ?>" readonly>
                <br>
                <select name="to" id="to">
                    <?php
                    if ($results) {
                        foreach ($currencies as &$value) {
                            echo "<option value='$value[0]' ";
                            if ($value[2] == $to) {
                                echo "selected";
                            }
                            echo ">$value[0] &nbsp</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <br>
        <input type="submit" value="Exchange">
    </form>


</body>

</html>