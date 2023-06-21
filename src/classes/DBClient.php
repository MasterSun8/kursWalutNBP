<?php

class DBClient
{

    // Class properties
    private $host;
    private $user;
    private $pass;
    private $db;
    private $conn;

    // Constructor method
    function __construct($host, $user, $pass, $db)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
    }

    // Connect to the database
    function connect()
    {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
    }

    // Close the database connection
    function close()
    {
        $this->conn->close();
    }

    // Insert today's exchange rates into the database
    function insertTodayRates($currencies)
    {
        // Connect to the database
        $this->connect();

        // Start building the SQL query to insert the exchange rates into the database
        $query = "INSERT INTO exchangeRates (Code, Bid, Ask) ";
        // Add the SELECT statement to select the exchange rate values from the $value array
        $query .= "SELECT ?, ?, ? ";
        // Add a subquery to check if the exchange rate already exists for the current date
        $query .= "FROM DUAL WHERE NOT(SELECT ? IN (SELECT `Code` FROM `exchangerates` WHERE `Date`=CURRENT_DATE))";

        $prepared = $this->conn->prepare($query);

        // Loop through each currency and insert it into the database if it doesn't already exist at today's date
        foreach ($currencies as &$value) {
            // Skip the currency if it's PLN
            if ($value[0] == 'PLN') {
                continue;
            }
            $prepared->bind_param("sdds", $value[0], $value[1], $value[2], $value[0]);
            $prepared->execute();
        }

        $prepared->close();
        $this->close();
    }

    function getDB($latest = false)
    {
        $this->connect();
        if (!$latest) {
            $query = "SELECT * FROM exchangeRates ORDER BY Date DESC;";
        } else {
            $query = "SELECT * FROM exchangeRates WHERE Date=(SELECT MAX(Date) from exchangeRates);";
        }

        $result = $this->conn->query($query);

        // Check if the query executed
        if (!$result) {
            return false;
        }

        $this->close();

        if ($latest) {
            // Initialize an empty array to store the currency data
            $currencies = array();

            // Add the base currency (PLN) to the array
            $currencies[0] = array('PLN', 1, 1);

            while ($row = $result->fetch_assoc()) {
                array_push($currencies, array($row['Code'], $row['Bid'], $row['Ask']));
            }
            return $currencies;
        } else {
            return $result;
        }
    }
    
    function resultAsTable($result){
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $key => $res) {
                echo "<th>$res</th>";
            }
            echo "</tr>";
        }
    }

    function drawDataFromDB()
    {
        $result = $this->getDB();
        if ($result) {
            $this->resultAsTable($result);
        } else {
            echo "0 results";
        }
    }

    function recordExchange($currFrom, $currTo, $valueFrom, $valueTo){
        $this->connect();

        if($valueFrom == 0 || $currFrom == $currTo){
            return false;
        }

        $query = "INSERT INTO `exchangehistory`(`currencyFrom`, `currencyTo`, `valueFrom`, `valueTo`)";
        $query .= "VALUES (?, ?, ?, ?);";
        
        $prepared = $this->conn->prepare($query);
        
        $prepared->bind_param("ssdd", $currFrom, $currTo, $valueFrom, $valueTo);
        $prepared->execute();
        
        $prepared->close();
    }

    function getHistory($limit){
        $this->connect();

        $limit = intval($limit);

        $query = "SELECT * FROM `exchangehistory` ORDER BY date DESC LIMIT $limit;";

        $result = $this->conn->query($query);

        // Check if the query executed
        if (!$result) {
            return false;
        }else{
            $this->resultAsTable($result);
        }

        $this->close();
    }
}
