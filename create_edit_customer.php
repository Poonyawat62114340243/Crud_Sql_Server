<?php
include("connection.php");

// Initialize variables for form fields
$customer = [
    'f_CustCode' => '',
    'f_CustName' => '',
    'f_CurAddress' => '',
    'f_TelNo' => '',
    'f_Idtax' => ''
];

$action = 'Create';
$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($id) {
    // Editing an existing record
    try {
        $db = connectSqlserver();
        $sql = "SELECT [f_CustCode], [f_CustName], [f_CurAddress], [f_TelNo], [f_Idtax]
                FROM [citypos001].[dbo].[Customer]
                WHERE [f_CustCode] = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_STR); // Use PDO::PARAM_STR for varchar
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        $action = 'Edit';
    } catch (PDOException $e) {
        echo "Query failed: " . htmlspecialchars($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form was submitted
    $f_CustCode = $_POST['f_CustCode'];
    $f_CustName = $_POST['f_CustName'];
    $f_CurAddress = $_POST['f_CurAddress'];
    $f_TelNo = $_POST['f_TelNo'];
    $f_Idtax = $_POST['f_Idtax'];

    try {
        $db = connectSqlserver();
        if ($action === 'Create') {
            $sql = "INSERT INTO [citypos001].[dbo].[Customer] ([f_CustCode], [f_CustName], [f_CurAddress], [f_TelNo], [f_Idtax])
                    VALUES (:f_CustCode, :f_CustName, :f_CurAddress, :f_TelNo, :f_Idtax)";
        } else {
            $sql = "UPDATE [citypos001].[dbo].[Customer]
                    SET [f_CustName] = :f_CustName, [f_CurAddress] = :f_CurAddress, [f_TelNo] = :f_TelNo, [f_Idtax] = :f_Idtax
                    WHERE [f_CustCode] = :f_CustCode";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':f_CustCode', $f_CustCode, PDO::PARAM_STR);
        $stmt->bindValue(':f_CustName', $f_CustName, PDO::PARAM_STR);
        $stmt->bindValue(':f_CurAddress', $f_CurAddress, PDO::PARAM_STR);
        $stmt->bindValue(':f_TelNo', $f_TelNo, PDO::PARAM_STR);
        $stmt->bindValue(':f_Idtax', $f_Idtax, PDO::PARAM_STR);
        $stmt->execute();
        
        header('Location: index.php'); 
        exit();
    } catch (PDOException $e) {
        echo "Query failed: " . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action; ?> Customer</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom Colors */
        :root {
            --color-primary: #1F316F;
            --color-secondary: #1A4870;
            --color-tertiary: #5B99C2;
            --color-quaternary: #F9DBBA;
        }

        body {
            background-color: var(--color-quaternary);
            color: #333;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 20px;
            max-width: 800px;
        }

        .form-group label {
            color: var(--color-primary);
        }

        .btn-custom {
            background-color: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
        }

        .btn-custom:hover {
            background-color: var(--color-tertiary);
            border-color: var(--color-tertiary);
        }

        .btn-custom:disabled {
            background-color: #ccc;
            border-color: #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo $action; ?> Customer</h1>
    <form method="post">
        <div class="form-group">
            <label for="f_CustCode">Customer Code</label>
            <input type="text" class="fosrm-control" id="f_CustCode" name="f_CustCode" value="<?php echo htmlspecialchars($customer['f_CustCode']); ?>" required <?php echo $action === 'Edit' ? 'readonly' : ''; ?>>
        </div>
        <div class="form-group">
            <label for="f_CustName">Customer Name</label>
            <input type="text" class="form-control" id="f_CustName" name="f_CustName" value="<?php echo htmlspecialchars($customer['f_CustName']); ?>" required>
        </div>
        <div class="form-group">
            <label for="f_CurAddress">Address</label>
            <input type="text" class="form-control" id="f_CurAddress" name="f_CurAddress" value="<?php echo htmlspecialchars($customer['f_CurAddress']); ?>" required>
        </div>
        <div class="form-group">
            <label for="f_TelNo">Telephone Number</label>
            <input type="text" class="form-control" id="f_TelNo" name="f_TelNo" value="<?php echo htmlspecialchars($customer['f_TelNo']); ?>" required>
        </div>
        <div class="form-group">
            <label for="f_Idtax">Tax ID</label>
            <input type="text" class="form-control" id="f_Idtax" name="f_Idtax" value="<?php echo htmlspecialchars($customer['f_Idtax']); ?>" required>
        </div>
        <button type="submit" class="btn btn-custom"><?php echo $action; ?> Customer</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.10/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
