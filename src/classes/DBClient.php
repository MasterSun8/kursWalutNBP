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
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
    }

    function close(){
        $this->conn->close;
    }

    function insertTodayRates($currencies){
        $this->connect();

        

        $this->close();
    }

    function drawDataFromDB()
    {
    }
}
