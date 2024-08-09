<?php
include("connection.php");

header('Content-Type: application/json');

try {
    $db = connectSqlserver();

    $sql = "SELECT TOP 10
                MbMaster.f_MbCode AS [รหัสลูกค้า],
                MbMaster.f_MbFirstName AS [ชื่อทั้งหมดของลูกค้า],
                MbMaster.f_TelNo AS [เบอร์โทร],
                MbMaster.f_MbType AS [ประเภทลูกค้า],
                SUM(POSTRNHD.F_TOTALNORMALV) AS [รวมยอดปกติ]
            FROM 
                MbMaster
            JOIN 
                POSTRNHD ON MbMaster.f_MbCode = POSTRNHD.F_CUSTCODE
            GROUP BY 
                MbMaster.f_MbCode,
                MbMaster.f_MbFirstName,
                MbMaster.f_TelNo,
                MbMaster.f_MbType
            ORDER BY 
                MbMaster.f_MbCode;";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
