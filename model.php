<?php
/**
 * Created by PhpStorm.
 * User: reidsavage
 * Date: 5/3/15
 * Time: 6:09 PM
 */
// @todo optimize by selecting only fields needed
class SG_Model {
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
//            $this->db = null;
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

    public function listAllProducts() {
        $q = $this->db->query("SELECT * FROM `products`;");
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function login($user_email, $password) {
        $statement = $this->db->prepare("SELECT * FROM `users` WHERE `user_email` = ? AND `user_password` = ?");
        $data = [];
        if ($statement->execute([$user_email, $password])) {
            while ($row = $statement->fetch()) {
                $data[] = $row;
            }
        }
        if(sizeof($data) > 0) {
            return $this->getSessionKey($data[0]['user_id']);
        }
        else {
            return false;
        }
    }

    private function getSessionKey($user_id) {
        $q = $this->db->query("SELECT * FROM `sessions` WHERE `user_id` = $user_id;");
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result[0]['public_session_id'];
        }
        else {
            return $this->generateSessionKey($user_id);
        }
    }
    private function generateSessionKey($user_id) {
        $cart_id = $this->getUserCartID($user_id);
        $session_id = $this->makeKey();
        $sql = "INSERT INTO `sessions` (user_id,cart_id,public_session_id) VALUES ($user_id,$cart_id,'$session_id');";
        $q = $this->db->prepare($sql);
        $q->execute();
        return $session_id;
    }

    private function makeKey() {
        return md5(time());
    }

    private function getUserCartID($user_id) {
        $q = $this->db->query("SELECT * FROM `cart` WHERE `user_id` = $user_id;");
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result[0]['cart_id'];
        }
        else {
            return $this->generateCartForUser($user_id);
        }
    }

    private function generateCartForUser($user_id) {
        $sql = "INSERT INTO `cart` (user_id) VALUES ($user_id)";
        $q = $this->db->prepare($sql);
        $q->execute();
        return $this->db->lastInsertId();
    }
    public function authenticateKey($key) {
        $q = $this->db->query("SELECT * FROM `sessions` WHERE `public_session_id` = $key;");
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result[0]['public_session_id'];
        }
        else {
            return false;
        }
    }
    public function destroyKey($key) {
        $sql = "DELETE FROM `sessions` WHERE `public_session_id` = $key;";
        $q = $this->db->prepare($sql);
        return $q->execute ? true : false;
    }
}