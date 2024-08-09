<?php
function connectSqlserver()
{
    $serverName = "DESKTOP-U676A76\SQL2019";
    $database = "citypos001";
    $username = "sa";
    $password = "admin123";

    try {
        // Create a new PDO instance
        $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $username, $password);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // Return error if connection fails
        return "Connection failed: " . $e->getMessage();
    }
}
?>
