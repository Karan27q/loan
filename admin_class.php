<?php
session_start();
ini_set('display_errors', 1);

class Action {
    private $db;

    public function __construct() {
        ob_start();
        include 'db_connect.php';
        $this->db = $conn;
    }

    function __destruct() {
        $this->db->close();
        ob_end_flush();
    }

    // General function for executing queries with prepared statements
    private function executeQuery($query, $types, $params) {
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            error_log("SQL Prepare Error: " . $this->db->error);
            return false;
        }

        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            error_log("SQL Execute Error: " . $stmt->error);
            return false;
        }

        return $stmt;
    }

    // Login function
    function login(){
        extract($_POST);
        $qry = $this->db->query("SELECT * FROM users where username = '".$username."' and password = '".$password."' ");
        if($qry->num_rows > 0){
            foreach ($qry->fetch_array() as $key => $value) {
                if($key != 'passwors' && !is_numeric($key))
                    $_SESSION['login_'.$key] = $value;
            }
            return 1;
        }else{
            return 3;
        }
    }

    function logout() {
        session_destroy();
        header("location:login.php");
    }

    // Save user
    function save_user() {
        extract($_POST);

        if (empty($name) || empty($username) || empty($password) || empty($type)) {
            return 0; // Missing required fields
        }

        if (empty($id)) {
            $query = "INSERT INTO users (name, username, password, type) VALUES (?, ?, ?, ?)";
            $result = $this->executeQuery($query, 'ssss', [$name, $username, $password, $type]);
        } else {
            $query = "UPDATE users SET name = ?, username = ?, password = ?, type = ? WHERE id = ?";
            $result = $this->executeQuery($query, 'ssssi', [$name, $username, $password, $type, $id]);
        }

        return $result ? (empty($id) ? 1 : 2) : 0;
    }

    // Save loan
    function save_loan() {
        extract($_POST);

        if (empty($borrower_id) || empty($loan_type_id) || empty($plan_id) || empty($amount) || empty($daily_amount)) {
            return 0; // Missing required fields
        }

        $data = " borrower_id = ?, loan_type_id = ?, plan_id = ?, amount = ?, daily_amount = ?, purpose = ?";
        $params = [$borrower_id, $loan_type_id, $plan_id, $amount, $daily_amount, $purpose];
        $types = "iiidds";

        if (isset($status)) {
            $data .= ", status = ?";
            $params[] = $status;
            $types .= "i";
        }

        if (empty($id)) {
            $query = "INSERT INTO loan_list SET $data";
            $result = $this->executeQuery($query, $types, $params);
        } else {
            $query = "UPDATE loan_list SET $data WHERE id = ?";
            $params[] = $id;
            $types .= "i";
            $result = $this->executeQuery($query, $types, $params);
        }

        return $result ? (empty($id) ? 1 : 2) : 0;
    }

    // Delete loan
    function delete_loan() {
        extract($_POST);
        $query = "DELETE FROM loan_list WHERE id = ?";
        $result = $this->executeQuery($query, 'i', [$id]);

        return $result ? 1 : 0;
    }

    // Save payment
    function save_payment() {
        extract($_POST);

        if (empty($loan_id) || empty($payee) || empty($amount) || empty($collect_by)) {
            return 0; // Missing required fields
        }

        $query = "INSERT INTO payments (loan_id, payee, amount, penalty_amount, overdue, collect_by) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $result = $this->executeQuery($query, 'isddds', [$loan_id, $payee, $amount, $penalty_amount, $overdue, $collect_by]);

        return $result ? 1 : 0;
    }

    // Delete payment
    function delete_payment() {
        extract($_POST);
        $query = "DELETE FROM payments WHERE id = ?";
        $result = $this->executeQuery($query, 'i', [$id]);

        return $result ? 1 : 0;
    }

    // Save loan plan
    function save_plan() {
        extract($_POST);

        if (empty($days) || empty($interest_percentage) || empty($penalty_rate)) {
            return 0; // Missing required fields
        }

        $query = empty($id) 
            ? "INSERT INTO loan_plan (days, interest_percentage, penalty_rate) VALUES (?, ?, ?)" 
            : "UPDATE loan_plan SET days = ?, interest_percentage = ?, penalty_rate = ? WHERE id = ?";
        
        $params = empty($id) ? [$days, $interest_percentage, $penalty_rate] : [$days, $interest_percentage, $penalty_rate, $id];
        $types = empty($id) ? 'ddd' : 'dddi';

        $result = $this->executeQuery($query, $types, $params);

        return $result ? (empty($id) ? 1 : 2) : 0;
    }

    // Delete loan plan
    function delete_plan() {
        extract($_POST);
        $query = "DELETE FROM loan_plan WHERE id = ?";
        $result = $this->executeQuery($query, 'i', [$id]);

        return $result ? 1 : 0;
    }

    // Add more save/delete functions following the same pattern as above
}

?>
