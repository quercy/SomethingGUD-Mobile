<?php
/**
 * Created by PhpStorm.
 * User: reidsavage
 * Date: 5/3/15
 * Time: 6:09 PM
 */
class SG_Database {
    private $db;
//    private static $instance = null;
    function __construct() {
        try {
            # MS SQL Server and Sybase with PDO_DBLIB
            $host = 'localhost';
            $user = 'somethinggud';
            $dbname = 'somethinggud-mobile';
            $pass = 'ShtPamt26x4jZcz9';
            $this->db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        }
        catch(PDOException $e) {
            $this->db = null;
            echo $e->getMessage();
        }
    }

    public function test() {
        $q = $this->db->query("SHOW TABLES;");
//        $q->setFetchMode(PDO::FETCH_ASSOC);
        while($row = $q->fetch()) {
            var_dump($row);
        }

    }
    // @todo optimize by selecting only fields needed
    public function listAllProducts() {
        $q = $this->db->query("SELECT * FROM `products`;");
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
//    For singleton
//    public static function getInstance() {
//        if(SG_Database::$instance == null) {
//            SG_Database::$instance = new SG_Database();
//        }
//        return SG_Database::$instance;
//    }
}