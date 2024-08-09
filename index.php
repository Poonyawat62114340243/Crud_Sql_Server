<?php
include("connection.php");

// Define the number of records per page
$recordsPerPage = 10;

// Get the current page number from the URL or set to 1 if not provided
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// Get the search term from the URL or set to empty string if not provided
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

try {
    $db = connectSqlserver();
    
    // Retrieve total record count with search filter
    $totalSql = "SELECT COUNT(*) AS total
                 FROM [citypos001].[dbo].[Customer]
                 WHERE [f_CustCode] LIKE ? OR
                       [f_CustName] LIKE ? OR
                       [f_CurAddress] LIKE ? OR
                       [f_TelNo] LIKE ? OR
                       [f_Idtax] LIKE ?";
    $totalStmt = $db->prepare($totalSql);
    $searchTermWildcard = '%' . $searchTerm . '%'; // Prepare search term for SQL query
    $totalStmt->execute([$searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard, $searchTermWildcard]);
    $totalRecords = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $recordsPerPage);
    
    // Retrieve records for the current page with search filter
    $sql = "SELECT [f_CustCode], [f_CustName], [f_CurAddress], [f_TelNo], [f_Idtax]
            FROM [citypos001].[dbo].[Customer]
            WHERE [f_CustCode] LIKE ? OR
                  [f_CustName] LIKE ? OR
                  [f_CurAddress] LIKE ? OR
                  [f_TelNo] LIKE ? OR
                  [f_Idtax] LIKE ?
            ORDER BY [f_CustCode]
            OFFSET ? ROWS
            FETCH NEXT ? ROWS ONLY";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, $searchTermWildcard, PDO::PARAM_STR);
    $stmt->bindValue(2, $searchTermWildcard, PDO::PARAM_STR);
    $stmt->bindValue(3, $searchTermWildcard, PDO::PARAM_STR);
    $stmt->bindValue(4, $searchTermWildcard, PDO::PARAM_STR);
    $stmt->bindValue(5, $searchTermWildcard, PDO::PARAM_STR);
    $stmt->bindValue(6, $offset, PDO::PARAM_INT);
    $stmt->bindValue(7, $recordsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Light Mode Styles */
        :root {
            --color-primary: #1F316F;
            --color-secondary: #1A4870;
            --color-tertiary: #5B99C2;
            --color-quaternary: #F9DBBA;
            --background-color: #F9DBBA;
            --text-color: #333;
            --table-background: #fff;
            --table-header-background: #1F316F;
            --table-row-hover-background: #5B99C2;
            --pagination-link-color: #1F316F;
            --pagination-link-hover-background: #5B99C2;
            --pagination-active-background: #1A4870;
            --footer-background: #1F316F;
            --btn-bg: #007bff;
            --btn-text: #fff;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
            padding-bottom: 80px; /* Adjusted padding for footer spacing */
        }

        h1 {
            color: var(--color-primary);
        }

        .table {
            background-color: var(--table-background);
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table thead {
            background-color: var(--table-header-background);
            color: #fff;
        }

        .table th, .table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .table tbody tr:hover {
            background-color: var(--table-row-hover-background);
            color: #fff;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .pagination .page-link {
            color: var(--pagination-link-color);
            border: 1px solid var(--pagination-link-color);
        }

        .pagination .page-link:hover {
            background-color: var(--pagination-link-hover-background);
            border-color: var(--pagination-link-hover-background);
            color: #fff;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--pagination-active-background);
            border-color: var(--pagination-active-background);
            color: #fff;
        }

        /* Additional padding to the container to prevent overlap with the footer */
        .container {
            padding-bottom: 80px; /* Adjusted padding for footer spacing */
        }

        footer {
            background-color: var(--footer-background) !important;
            color: #fff !important;
            text-align: center;
            padding: 10px;
            width: 100%;
            bottom: 0;
            left: 0;
        }       

        /* Ensure pagination buttons are displayed properly */
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .btn-primary {
            background-color: var(--btn-bg);
            color: var(--btn-text);
            border-color: var(--btn-bg);
        }

        .btn-primary:hover {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
            color: #fff;
        }

        .btn-custom {
            margin: 0 5px;
        }

        /* Media Queries for Responsive Design */

        /* Mobile Devices (less than 576px) */
        @media (max-width: 575.98px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .table th, .table td {
                font-size: 0.875rem;
                padding: 8px;
            }

            .pagination {
                flex-direction: column;
            }
        }

        /* Tablets (between 576px and 768px) */
        @media (min-width: 576px) and (max-width: 767.98px) {
            .container {
                padding: 15px;
            }

            h1 {
                font-size: 1.75rem;
            }

            .table th, .table td {
                font-size: 1rem;
                padding: 10px;
            }
        }

        /* Small Desktops (between 768px and 992px) */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 2rem;
            }

            .table th, .table td {
                font-size: 1rem;
                padding: 12px;
            }
        }

        /* Large Desktops (992px and above) */
        @media (min-width: 992px) {
            .container {
                padding: 25px;
            }

            h1 {
                font-size: 2.5rem;
            }

            .table th, .table td {
                font-size: 1.125rem;
                padding: 14px;
            }
        }

        /* Dark Mode Styles */
        body.dark-mode {
            --background-color: #333;
            --text-color: #f9f9f9;
            --table-background: #444;
            --table-header-background: #555;
            --table-row-hover-background: #666;
            --pagination-link-color: #f9f9f9;
            --pagination-link-hover-background: #777;
            --pagination-active-background: #888;
            --footer-background: #555;
            --btn-bg: #007bff;
            --btn-text: #fff;
        }

        /* Modal Styles */
        .modal-content {
            transition: transform 0.3s ease-in-out;
        }

        .modal-content.slide-in {
            transform: translateX(0);
        }

        .modal-content.slide-out {
            transform: translateX(100%);
        }

        .btn-custom.disabled {
            opacity: 0.6; /* Make it look disabled */
            pointer-events: none; /* Disable clicking */
            cursor: not-allowed; /* Change cursor to indicate it's not clickable */
        }

        .btn-custom {
            margin: 0 5px;
            border: 1px solid var(--color-primary);
        }

        .btn-custom:not(.disabled):hover {
            background-color: var(--pagination-link-hover-background);
            border-color: var(--pagination-link-hover-background);
            color: #fff;
        }

        .btn-custom.active {
            background-color: var(--pagination-active-background);
            border-color: var(--pagination-active-background);
            color: #fff;
        }

        /* Default Modal Size */
        .modal-dialog {
            max-width: 90%;
            margin: 1.75rem auto;
        }

        /* Responsive Design Adjustments */
        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 85%;
            }
        }

        @media (min-width: 768px) {
            .modal-dialog {
                max-width: 80%;
            }
        }

        @media (min-width: 992px) {
            .modal-dialog {
                max-width: 70%;
            }
        }

        @media (min-width: 1200px) {
            .modal-dialog {
                max-width: 60%;
            }
        }

    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4 text-primary">Customer List</h1>
    <div class="mb-3">
        <a href="create_edit_customer.php" class="btn btn-primary">Add New Customer</a>
        <button id="theme-toggle" class="btn btn-dark">Toggle Dark Mode</button>
        <!-- Button to trigger the modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reportModal">Show Report</button>
    </div>
    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </div>
    </form>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>รหัสลูกค้า</th>
                <th>ชื่อทั้งหมดของลูกค้า</th>
                <th>ที่อยู่</th>
                <th>เบอร์โทร</th>
                <th>เลขที่ภาษี</th>
                <th>OPTION</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['f_CustCode']); ?></td>
                <td><?php echo htmlspecialchars($row['f_CustName']); ?></td>
                <td><?php echo htmlspecialchars($row['f_CurAddress']); ?></td>
                <td><?php echo htmlspecialchars($row['f_TelNo']); ?></td>
                <td><?php echo htmlspecialchars($row['f_Idtax']); ?></td>
                <td>
                    <a href="create_edit_customer.php?id=<?php echo htmlspecialchars($row['f_CustCode']); ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete_customer.php?id=<?php echo htmlspecialchars($row['f_CustCode']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <nav aria-label="Page navigation">
        <div class="d-flex justify-content-between">
            <!-- Previous Page Button -->
            <a class="btn btn-custom <?php echo $page <= 1 ? 'disabled' : ''; ?>" 
            href="<?php echo $page > 1 ? '?page=' . ($page - 1) . '&search=' . urlencode($searchTerm) : '#'; ?>" 
            aria-disabled="<?php echo $page <= 1 ? 'true' : 'false'; ?>">
            Previous
            </a>
            
            <!-- Next Page Button -->
            <a class="btn btn-custom <?php echo $page >= $totalPages ? 'disabled' : ''; ?>" 
            href="<?php echo $page < $totalPages ? '?page=' . ($page + 1) . '&search=' . urlencode($searchTerm) : '#'; ?>" 
            aria-disabled="<?php echo $page >= $totalPages ? 'true' : 'false'; ?>">
            Next
            </a>
        </div>
    </nav>
</div>

<!-- Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reportContent">
                <!-- Report content will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printReport">Print</button>
                <button type="button" class="btn btn-primary" id="exportPdf">Export PDF</button>
                <button type="button" class="btn btn-primary" id="exportCsv">Export CSV</button>
            </div>
        </div>
    </div>
</div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Poonyawat Mandee. All rights reserved.</p>
    </footer>   

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.10/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('dataModal');
    const dataContentDiv = document.getElementById('dataContent');

    $(modal).on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');

        // Slide-in animation
        $('.modal-content').removeClass('slide-out').addClass('slide-in');

        // Fetch data and populate the modal
        fetch(`fetch_data.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                let html = `<p><strong>Customer Code:</strong> ${data.f_CustCode}</p>
                            <p><strong>Customer Name:</strong> ${data.f_CustName}</p>
                            <p><strong>Address:</strong> ${data.f_CurAddress}</p>
                            <p><strong>Phone:</strong> ${data.f_TelNo}</p>
                            <p><strong>Tax ID:</strong> ${data.f_Idtax}</p>`;
                dataContentDiv.innerHTML = html;
            })
            .catch(error => {
                dataContentDiv.innerHTML = '<p>Error loading data.</p>';
                console.error('Error:', error);
            });
    });

    $(modal).on('hide.bs.modal', function() {
        // Slide-out animation
        $('.modal-content').removeClass('slide-in').addClass('slide-out');
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const reportModal = document.getElementById('reportModal');
    const reportContentDiv = document.getElementById('reportContent');

    // Show report button
    document.querySelector('[data-target="#reportModal"]').addEventListener('click', function() {
        fetch('fetch_report.php')
            .then(response => response.json())
            .then(data => {
                console.log('Report Data:', data); // Debugging line

                if (Array.isArray(data)) {
                    let html = '<table class="table table-striped table-bordered">';
                    html += '<thead><tr>';
                    html += '<th>รหัสลูกค้า</th><th>ชื่อทั้งหมดของลูกค้า</th><th>เบอร์โทร</th><th>ประเภทลูกค้า</th>';
                    html += '<th>รวมยอดปกติ</th>';
                    html += '</tr></thead><tbody>';

                    data.forEach(row => {
                        html += '<tr>';
                        html += `<td>${row['รหัสลูกค้า']}</td>`;
                        html += `<td>${row['ชื่อทั้งหมดของลูกค้า']}</td>`;
                        html += `<td>${row['เบอร์โทร']}</td>`;
                        html += `<td>${row['ประเภทลูกค้า']}</td>`;
                        html += `<td>${row['รวมยอดปกติ']}</td>`;
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                    reportContentDiv.innerHTML = html;
                } else {
                    reportContentDiv.innerHTML = '<p>No data available.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching report:', error);
                reportContentDiv.innerHTML = '<p>Error loading report data.</p>';
            });
    });

    // Print functionality
    document.getElementById('printReport').addEventListener('click', function() {
        const reportContent = document.getElementById('reportContent').innerHTML;
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = reportContent;
        window.print();
        document.body.innerHTML = originalContent;
    });

    // Export PDF functionality
    document.getElementById('exportPdf').addEventListener('click', function() {
        const reportContent = document.getElementById('reportContent').innerHTML;
        fetch('generate_pdf.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `content=${encodeURIComponent(reportContent)}`
        })
        .then(response => response.blob())
        .then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'report.pdf';
            a.click();
            URL.revokeObjectURL(url);
        });
    });

    // Export CSV functionality
    document.getElementById('exportCsv').addEventListener('click', function() {
        const searchTerm = new URLSearchParams(window.location.search).get('search') || '';
        const url = `export_csv.php?search=${encodeURIComponent(searchTerm)}`;
        window.location.href = url;
    });

    // Dark mode toggle
    const themeToggleButton = document.getElementById('theme-toggle');
    themeToggleButton.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
    });
});
</script>

</body>
</html>

<?php
    $db = null;
} catch (PDOException $e) {
    echo "Query failed: " . htmlspecialchars($e->getMessage());
}
?>
