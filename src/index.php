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
require_once("classes/getCurrencyRates.php");
require_once("classes/APIParser.php");
require_once("classes/DBParser.php");

$result;
$currencies;
$result = getCurrencyRates();
//parseAPICall($result);
$currencies = getAllCurrencies($result);
drawDataFromDB();

?>

<form method="get">
    <div class="result">
    <?php
        echo intval($_GET['value'])." ".$_GET['from']." to ".$_GET['to'];
    ?>
    </div>
    <br>
    <div id="currencies">
        <div>
            <input name="value" id="value" type="number" min="0" step="0.01" value="<?php echo intval($_GET['value']); ?>">
            <br>
            <select name="from" id="from">
            <option value="PLN">PLN &nbsp</option>
            <?php 
                if('PLN'==$_GET['from']){
                    $from = 1;
                }
                if('PLN'==$_GET['to']){
                    $to = 1;
                }
                foreach ($currencies as &$value) {
                    echo "<option value='$value[0]'";
                    if($value[0]==$_GET['from']){
                        $from = $value[1];
                        echo " selected";
                    }
                    if($value[0]==$_GET['to']){
                        $to = $value[1];
                    }
                    echo ">$value[0] &nbsp</option>";
                }
            ?>
            </select>
        </div>
    
        <div>
            <div class="result">
                <?php
                    echo round(intval($_GET['value']) * ($from/$to), 5);
                ?>
            </div>
            <br>
            <select name="to" id="to">
            <option value="PLN">PLN &nbsp</option>
            <?php foreach ($currencies as &$value) {echo "<option value='$value[0]'>$value[0] &nbsp</option>";}?>
            </select>
        </div>
    </div>
    <br>
    <input type="submit" value="Exchange">
</form>

</body>
</html>