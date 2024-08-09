<?php
include("connection.php");

// Get the search term from the URL
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$searchTermWildcard = '%' . $searchTerm . '%';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="report.csv"');

// Output buffering to handle large amounts of data
$output = fopen('php://output', 'w');

// Add BOM to indicate UTF-8 encoding
fwrite($output, "\xEF\xBB\xBF");

// Try to connect and fetch data
try {
    $db = connectSqlserver();

    // Retrieve records for the CSV export
    $sql = "SELECT [f_CustCode], [f_CustName], [f_CurAddress], [f_TelNo], [f_Idtax]
            FROM [citypos001].[dbo].[Customer]
            WHERE [f_CustCode] LIKE ? OR
                  [f_CustName] LIKE ? OR
                  [f_CurAddress] LIKE ? OR
                  [f_TelNo] LIKE ? OR
                  [f_Idtax] LIKE ?
            ORDER BY [f_CustCode]";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard]);

    // Add CSV header row
    fputcsv($output, ['f_CustCode', 'f_CustName', 'f_CurAddress', 'f_TelNo', 'f_Idtax']);

    // Add data rows
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Encode each cell to UTF-8 (if necessary)
        $row = array_map(function($value) {
            return mb_convert_encoding($value, 'UTF-8', mb_detect_encoding($value, 'UTF-8', true));
        }, $row);
        fputcsv($output, $row);
    }

    fclose($output);
    $db = null;
} catch (PDOException $e) {
    echo "Query failed: " . htmlspecialchars($e->getMessage());
}
?>
