<?php
require "DataBaseConfig.php";

class DataBase
{
    public $connect;
    public $data;
    private $sql;
    protected $servername;
    protected $username;
    protected $password;
    protected $databasename;

    public function __construct()
    {
        $this->connect = null;
        $this->data = null;
        $this->sql = null;
        $dbc = new DataBaseConfig();
        $this->servername = $dbc->servername;
        $this->username = $dbc->username;
        $this->password = $dbc->password;
        $this->databasename = $dbc->databasename;
    }

    function dbConnect()
    {
        $this->connect = mysqli_connect($this->servername, $this->username, $this->password, $this->databasename);
        return $this->connect;
    }

    function prepareData($data)
    {
        return mysqli_real_escape_string($this->connect, stripslashes(htmlspecialchars($data)));
    }

    function logIn($table, $username, $password)
    {
        $username = $this->prepareData($username);
        $password = $this->prepareData($password);
        $this->sql = "select * from " . $table . " where username = '" . $username . "'";
        $result = mysqli_query($this->connect, $this->sql);
        $row = mysqli_fetch_assoc($result);
        if (mysqli_num_rows($result) != 0) {
            $dbusername = $row['username'];
            $dbpassword = $row['password'];
            if ($dbusername == $username && password_verify($password, $dbpassword)) {
                $login = true;
            } else $login = false;
        } else $login = false;

        return $login;
    }

    function signUp($table, $username, $email, $password)
    {
        $username = $this->prepareData($username);
        $email = $this->prepareData($email);
        $password = $this->prepareData($password);
        $password = password_hash($password, PASSWORD_DEFAULT);
        $this->sql =
            "INSERT INTO " . $table . " (username, email, password) VALUES ('" . $username . "','" . $email . "','" . $password . "')";
        if (mysqli_query($this->connect, $this->sql)) {
            return true;
        } else return false;
    }

    function resetPassword($table, $email, $password) 
    {
        // Sanitize inputs
        $email = $this->prepareData($email);
        $password = $this->prepareData($password);
    
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        // Build the SQL query
        $this->sql = "UPDATE " . $table . " SET password = '" . $hashedPassword . "' WHERE email = '" . $email . "'";
    
        // Execute the query
        if (mysqli_query($this->connect, $this->sql)) {
            // Check if any rows were affected
            if (mysqli_affected_rows($this->connect) > 0) {
                return true; // Password reset successful
            } else {
                return false; // No user found with that email
            }
        } else {
            return false; // Query execution failed
        }
    }
}
?>
