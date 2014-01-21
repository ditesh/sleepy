<?php

class DB {

    public $users;
    public $webpages;

    public function __construct(array $config) {

        $dbname = $config["dbname"];
        $hostname = $config["hostname"];
        $port = $config["port"];
        $errpath = $config["error-log"];

        try {

            $conn = new MongoClient("mongodb://$hostname:$port");
            $db = $conn->selectDB($dbname);

            $this->users = $conn->selectDB($dbname)->users;
            $this->logs = $conn->selectDB($dbname)->logs;
            $this->hotels = $conn->selectDB($dbname)->hotels;
            $this->inventory = $conn->selectDB($dbname)->inventory;

        } catch (Exception $e) {

            error_log("MongoDB failed because of $e", 3, $errpath);
            throw $e;

        }
    }
}
