<?php

require_once __DIR__ . '/../tcpdf/tcpdf.php';

include __DIR__ . '/../db.php';

$id = intval($_GET['id']);
$filter = $_GET['filter'] ?? 'all';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

// Get Karigar Info
$karigar = $conn->query("SELECT name FROM karigars WHERE id = $id")->fetch_assoc();
$karigar_name = $karigar['name'] ?? 'Unknown';

// Filter Logic
$where = "karigar_id = $id";
if ($filter === 'week') {
  $where .= " AND YEARWEEK(date) = YEARWEEK(CURDATE())";
} elseif ($filter === 'month') {
  $where .= " AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())";
} elseif ($filter === 'custom' && $from && $to) {
  $where .= " AND date BETWEEN '$from' AND '$to'";
}

// Fetch Entries
$result = $conn->query("SELECT we.*, c.name AS category_name 
                        FROM work_entries we 
                        JOIN categories c ON we.category = c.id 
                        WHERE $where ORDER BY we.date DESC");


// PDF Setup
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('KariTrack');
$pdf->SetTitle("Work Report - $karigar_name");
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

// Add Logo
$logo_path = __DIR__ . '/assets/logo.png';
if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 10, 10, 30);
}
$pdf->Ln(15);

// Header
$date_str = date("d M Y");
$html = "
  <h2 style='text-align:center;'>KariTrack - Karigar Report</h2>
  <p><strong>Karigar:</strong> $karigar_name</p>
  <p><strong>Date:</strong> $date_str</p>
  <p><strong>Filter:</strong> " . ucfirst($filter);
if ($filter === 'custom') {
    $html .= " ($from to $to)";
}
$html .= "</p><br>";

// Table
$html .= "
<table border=10% cellpadding='5'>
  <thead>
    <tr style='background-color:#f2f2f2;'>
      <th>#</th>
      <th><b>Category</b></th>
      <th><b>Quantity</b></th>
      <th><b>Price/Item</b></th>
      <th><b>Total</b></th>
      <th><b>Date</b></th>
    </tr>
  </thead>
  <tbody>
";

$index = 1;
$total_amount = 0;
while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
      <td>{$index}</td>
      <td>{$row['category_name']}</td>
      <td>{$row['quantity']}</td>
      <td>₹{$row['price_per_item']}</td>
      <td>₹{$row['total']}</td>
      <td>{$row['date']}</td>
    </tr>";
    $total_amount += $row['total'];
    $index++;
}

$html .= "</tbody></table>";

$html .= "<br><h4><strong>Total Amount Earned: ₹" . number_format($total_amount, 2) . "</strong></h4>";



// Output
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("Karigar_{$karigar_name}_Report.pdf", 'D');
?>
