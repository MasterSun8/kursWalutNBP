<?php

class DBClient
{
    private $host;
    private $user;
    private $pass;
    private $db;
    private $conn;

    function __construct($host, $user, $pass, $db)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
    }

    function connect()
    {
        // Establish a connection to the database
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
    }

    function close()
    {
        // Close the database connection
        $this->conn->close();
    }

    function insertTodayRates($currencies)
    {
        $this->connect();

        // Prepare the query for inserting exchange rates
        $query = "INSERT INTO exchangeRates (Code, Bid, Ask) ";
        $query .= "SELECT ?, ?, ? ";
        $query .= "FROM DUAL WHERE NOT(SELECT ? IN (SELECT `Code` FROM `exchangerates` WHERE `Date`=CURRENT_DATE))";

        $prepared = $this->conn->prepare($query);

        if(!$prepared){
            return false;
        }
        // Iterate through the currencies array and insert each currency rate
        foreach ($currencies as &$value) {
            // Skip the currency if it's PLN
            if ($value[0] == 'PLN') {
                continue;
            }
            // Bind parameters and execute the prepared statement
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
            // Retrieve all exchange rates from the table in descending order
            $query = "SELECT * FROM exchangeRates ORDER BY Date DESC;";
        } else {
            // Retrieve the latest exchange rates from the table
            $query = "SELECT * FROM exchangeRates WHERE Date=(SELECT MAX(Date) from exchangeRates);";
        }

        $result = $this->conn->query($query);

        if (!$result) {
            return false;
        }

        $this->close();

        if ($latest) {
            $currencies = array();
            $currencies[0] = array('PLN', 1, 1);

            // Fetch the result and store exchange rates in an array
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
            // Display each row of the result as a table row
            echo "<tr>";
            foreach ($row as $key => $res) {
                echo "<th>$res</th>";
            }
            echo "</tr>";
        }
    }

    function drawDataFromDB()
    {
        // Retrieve exchange rates from the database and display as a table
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

        // Insert the exchange record into the history table
        $query = "INSERT INTO `exchangehistory`(`currencyFrom`, `currencyTo`, `valueFrom`, `valueTo`)";
        $query .= "VALUES (?, ?, ?, ?);";

        $prepared = $this->conn->prepare($query);

        if(!$prepared){
            return false;
        }
        
        $prepared->bind_param("ssdd", $currFrom, $currTo, $valueFrom, $valueTo);
        $prepared->execute();

        $prepared->close();
    }

    function getHistory($limit){
        $this->connect();

        $limit = intval($limit);

        // Retrieve exchange history from the database with a specified limit
        $query = "SELECT * FROM `exchangehistory` ORDER BY date DESC LIMIT $limit;";

        $result = $this->conn->query($query);

        if (!$result) {
            return false;
        } else {
            $this->resultAsTable($result);
        }

        $this->close();
    }
}

?>