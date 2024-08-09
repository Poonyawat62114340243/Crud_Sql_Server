<?php
include("connection.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $db = connectSqlserver();
        $sql = "DELETE FROM [citypos001].[dbo].[Customer] WHERE [f_CustCode] = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        echo "Query failed: " . htmlspecialchars($e->getMessage());
    }
}
?>
