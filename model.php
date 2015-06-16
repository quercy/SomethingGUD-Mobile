<?php

class SG_Model {
    private $db;
    function __construct() {
        try {
            $host = $dbname = $user = $pass = "";
            include_once("db_config.php");
            $this->db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        }
        catch(PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Returns an array of products and their tags.
     * @return array
     */
    public function listAllProducts() {
        $q = $this->db->query("SELECT * FROM `products`"); // @todo optimize by selecting only fields needed
        $results = $q->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as &$result) {
            $tags = $this->getTagsForProduct($result['product_id']);
            $result['tags'] = is_null($tags) ? array() : $tags;
        }
        return $results;
    }

    /**
     * @param $id
     * @return array
     */
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

    /**
     * @param $id
     * @return mixed
     */
    public function getProduct($id) {
        $q = $this->db->query("SELECT * FROM `products` WHERE `product_id` = $id;"); // @todo optimize by selecting only fields needed
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        return array_pop($result);
    }

    /**
     * @param $password
     * @return string
     */
    private function encodePassword($password) {
        return md5($password); // !this is a proof of concept - _not secure_
    }

    /**
     * Authenticates a user. Returns the session key if successful, otherwise false.
     * @param $user_email
     * @param $password
     * @return bool|string
     */
    public function login($user_email, $password) {
        $statement = $this->db->prepare("SELECT * FROM `users` WHERE `user_email` = ? AND `user_password` = ?"); // @todo optimize by selecting only fields needed
        $data = [];
        $password = $this->encodePassword($password);
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

    /**
     * Gets (or creates) a session key for a user.
     * @param $user_id
     * @return string
     */
    private function getSessionKey($user_id) {
        $q = $this->db->query("SELECT * FROM `sessions` WHERE `user_id` = $user_id;"); // @todo optimize by selecting only fields needed
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result[0]['public_session_id'];
        }
        else {
            return $this->generateSessionKey($user_id);
        }
    }

    /**
     * Returns the user's session id or creates it if it doesn't exist.
     * @param $user_id
     * @return string
     */
    private function generateSessionKey($user_id) {
        $cart_id = $this->getUserCartID($user_id);
        $session_id = $this->makeKey();
        $sql = "INSERT INTO `sessions` (user_id,cart_id,public_session_id) VALUES ($user_id,$cart_id,'$session_id');";
        $q = $this->db->prepare($sql);
        $q->execute();
        return $session_id;
    }

    /**
     * @return string
     */
    private function makeKey() {
        return md5(time());
    }

    /**
     * Returns the user's cart ID or creates the cart it if it doesn't exist.
     * @param $user_id
     * @return string
     */
    private function getUserCartID($user_id) {
        $q = $this->db->query("SELECT * FROM `cart` WHERE `user_id` = $user_id;"); // @todo optimize by selecting only fields needed
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result[0]['cart_id'];
        }
        else {
            return $this->generateCartForUser($user_id);
        }
    }

    /**
     * @param $user_id
     * @return string
     */
    private function generateCartForUser($user_id) {
        $sql = "INSERT INTO `cart` (user_id) VALUES ($user_id)";
        $q = $this->db->prepare($sql);
        $q->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Validates a key; returns null if not valid
     * @param $key
     * @return null|string
     */
    public function authenticateKey($key) {
        $q = $this->db->query("SELECT * FROM `sessions` WHERE `public_session_id` = '$key';"); // @todo optimize by selecting only fields needed
        $q->execute();
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result[0]['public_session_id'];
        }
        else {
            return null;
        }
    }

    /**
     * Destroys a session.
     * @param $key
     * @return bool
     */
    public function destroyKey($key) {
        $sql = "DELETE FROM `sessions` WHERE `public_session_id` = $key;";
        $q = $this->db->prepare($sql);
        return $q->execute ? true : false;
    }

    /**
     * @return array|null
     */
    public function getCategories() {
        $q = $this->db->query("SELECT * FROM `categories`;"); // @todo optimize by selecting only fields needed
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result;
        }
        else {
            return null;
        }
    }

    /**
     * Returns the user id for the specified key or null.
     * @param $key
     * @return mixed|null
     */
    private function getUserIDByKey($key) {
        $q = $this->db->query("SELECT * FROM `sessions`
        INNER JOIN `users` ON `sessions`.`user_id` = `users`.`user_id`
        WHERE `public_session_id` = '$key';"); // @todo optimize by selecting only fields needed
        $q->execute();
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return array_pop($result['user_id']);
        }
        else {
            return null;
        }
    }

    /**
     * Returns all user info for the specified key.
     * @param $key
     * @return mixed|null
     */
    public function getUserInfo($key) {
        $q = $this->db->query("SELECT `users`.`user_id`, `user_email`, `address_id`, `first_name`, `last_name`, `address_line_1`, `address_line_2`, `city`, `state`, `zip` FROM `users`
        INNER JOIN `sessions` ON `users`.`user_id` = `sessions`.`user_id`
        INNER JOIN `addresses` ON `addresses`.`user_id` = `sessions`.`user_id`
        WHERE `public_session_id` = '$key';");
        $q->execute();
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return array_pop($result);
        }
        else {
            return null;
        }
    }

    /**
     * @param $key
     * @return mixed|null
     */
    private function getSessionData($key) {
        $q = $this->db->query("SELECT * FROM `sessions` WHERE `public_session_id` = '$key';"); // @todo optimize by selecting only fields needed
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return array_pop($result);
        }
        else {
            return null;
        }
    }

    /**
     * Normalizes the passed cart data, empties the existing cart, and inserts the new data
     * @param $key
     * @param $product_data
     * @return bool
     */
    public function updateCart($key, $product_data) {
        $session_data = $this->getSessionData($key);
        // doing this extremely inefficient loop to search for duplicate products the client may have posted and to
        // combine the quantities
        for($i = 0; $i < sizeof($product_data); $i++) { // @todo optimize (o(n^2)...)
            for($f = 0; $f < sizeof($product_data); $f++) {
                if($i != $f && $product_data[$i]['product_id'] == $product_data[$f]['product_id']) {
                    $product_data[$i]['quantity'] = strval(
                        intval($product_data[$i]['quantity'])
                        + intval($product_data[$f]['quantity']));
                    unset($product_data[$f]);
                }
            }
        }

        if($session_data) {
            $cart_id = $session_data['cart_id'];
            // empty the cart
            $sql = "DELETE FROM `cart_products` WHERE `cart_id` = $cart_id;";
            $q = $this->db->prepare($sql);
            $q->execute();
            // insert the new products
            if(sizeof($product_data) > 0) {
                $stmt = $this->db->prepare("INSERT INTO `cart_products` (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)");
                $stmt->bindParam(':cart_id', $cart_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':quantity', $quantity);
                foreach ($product_data as $product_datum) {
                    $cart_id = $session_data['cart_id'];
                    $product_id = $product_datum['product_id'];
                    $quantity = $product_datum['quantity'];
                    $stmt->execute();
                }
            }
            return true; // success
        }
        else {
            return false; // didn't authenticate
        }
    }

    /**
     * Returns all of the items for the user with the specified key
     * @param $key
     * @return array|null
     */
    public function getCart($key) {
        $session_data = $this->getSessionData($key);
        $cart_id = $session_data['cart_id'];
        $q = $this->db->query("SELECT * FROM `cart_products` WHERE `cart_id` = $cart_id;"); // @todo optimize by selecting only fields needed
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return $result;
        }
        else {
            return null;
        }
    }

    /**
     * @param $email
     * @return bool
     */
    private function emailExists($email) {
        $q = $this->db->query("SELECT * FROM `users` WHERE `user_email` = '$email';"); // @todo optimize by selecting only fields needed
        $result = $q->fetchAll(PDO::FETCH_ASSOC);
        if(sizeof($result) > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @param $data
     * @return string
     */
    public function addUser($data) {
        $user_email = $data['user_email'];
        if($this->emailExists($user_email)) {
            return 'email exists';
        }
        $password = $data['password'];
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $zip = $data['zip'];
        $password = $this->encodePassword($password);
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