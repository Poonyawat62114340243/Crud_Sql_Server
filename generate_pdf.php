<?php
require('fpdf.php');

class PDF extends FPDF
{
    function WriteHTML($html)
    {
        // Simple HTML parser
        $this->Write(5, $html);
    }
}

// Retrieve the content from the POST request
$content = isset($_POST['content']) ? $_POST['content'] : '';

if ($content) {
    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $pdf->WriteHTML($content);
    $pdf->Output('D', 'report.pdf'); // 'D' forces download
} else {
    echo 'No content provided.';
}
?>

<script>
    exportPdfButton.addEventListener('click', function() {
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

</script>
