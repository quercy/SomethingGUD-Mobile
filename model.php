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
        $q = $this->db->query("SELECT * FROM `products`");
        $results = $q->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as &$result) {
            $result['tags'] = $this->getTagsForProduct($result['product_id']);
        }
        return $results;
    }

    private function getTagsForProduct($id) {
        $return = [];
        $q = $this->db->query("SELECT `tag_text` FROM `tags`
        INNER JOIN `product_tags` ON `tags`.`tag_id` = `product_tags`.`tag_id`
        WHERE `product_tags`.`product_id` = $id;");
        $results = $q->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $result) {
            $return[] = $result['tag_text'];
        }
        return $return;
    }

    public function getProduct($id) {
        $q = $this->db->query("SELECT * FROM `products` WHERE `product_id` = $id;");
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        return array_pop($result);
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
        $q = $this->db->query("SELECT * FROM `sessions` WHERE `public_session_id` = '$key';");
        $q->execute();
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

    public function getCategories() {
        $q = $this->db->query("SELECT * FROM `categories`;");
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result;
        }
        else {
            return null;
        }
    }

    private function getUserIDByKey($key) {
        $q = $this->db->query("SELECT * FROM `sessions`
        INNER JOIN `users` ON `sessions`.`user_id` = `users`.`user_id`
        WHERE `public_session_id` = '$key';");
        $q->execute();
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return array_pop($result['user_id']);
        }
        else {
            return false;
        }
    }

    private function getSessionData($key) {
        $q = $this->db->query("SELECT * FROM `sessions` WHERE `public_session_id` = '$key';");
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return array_pop($result);
        }
        else {
            return null;
        }
    }

    public function updateCart($key, $product_data) {
        $session_data = $this->getSessionData($key);
        if($session_data) {
            $cart_id = $session_data['cart_id'];
            $sql = "DELETE FROM `cart_products` WHERE `cart_id` = $cart_id;";
            $q = $this->db->prepare($sql);
            $q->execute();
//            $sql2 = "INSERT INTO `cart_products` (cart_id, product_id) VALUES ();";
//            $q2 = $this->db->prepare($sql2);
//            $q2->execute();

            $stmt = $this->db->prepare("INSERT INTO `cart_products` (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)");
            $stmt->bindParam(':cart_id', $cart_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':quantity', $quantity);
            foreach($product_data as $product_datum) {
//                var_dump($product_datum);
                $cart_id = $session_data['cart_id'];
                $product_id = $product_datum['product_id'];
                $quantity = $product_datum['quantity'];
//                echo($stmt);
                $stmt->execute();
            }
            return true;
        }
        else {
            return false;
        }
    }

    public function getCart($key) {
        $session_data = $this->getSessionData($key);
//        var_dump($session_data);
        $cart_id = $session_data['cart_id'];
        $q = $this->db->query("SELECT * FROM `cart_products` WHERE `cart_id` = $cart_id;");
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result;
        }
        else {
            return null;
        }
    }

    public function addUser($data) {
        $user_email = $data['user_email'];
        $password = $data['password'];
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $zip = $data['zip'];
        $password = md5($password);
        $sql = "INSERT INTO `users` (user_email, user_password) VALUES ('$user_email','$password');";
        $q = $this->db->prepare($sql);
        $q->execute();
        $user_id = $this->db->lastInsertId();
        $sql2 = "INSERT INTO `addresses` (user_id, first_name, last_name, zip) VALUES ($user_id,'$first_name', '$last_name', '$zip');";
        $q2 = $this->db->prepare($sql2);
        $q2->execute();
        return $this->generateSessionKey($user_id);
    }
}