<?php

require 'db.php';

$shipment = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tracking_number = trim($_POST['tracking_number']);

    $stmt = $pdo->prepare("
        SELECT *
        FROM cargo_applications
        WHERE reference = ?
        OR hawb_number = ?
        LIMIT 1
    ");

    $stmt->execute([
        $tracking_number,
        $tracking_number
    ]);

    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shipment) {
        $message = "Shipment not found.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Airsell Cargo Tracking</title>

<style>

body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f4f6f9;
}

.header{
    background:#003366;
    color:white;
    text-align:center;
    padding:30px;
}

.container{
    width:90%;
    max-width:900px;
    margin:30px auto;
}

.card{
    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 2px 10px rgba(0,0,0,.1);
    margin-bottom:20px;
}

input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:4px;
}

button{
    background:#0066cc;
    color:white;
    padding:12px 20px;
    border:none;
    border-radius:4px;
    cursor:pointer;
}

button:hover{
    background:#004fa3;
}

.label{
    font-weight:bold;
    color:#003366;
}

.row{
    margin-bottom:12px;
}

.error{
    background:#f8d7da;
    color:#721c24;
    padding:15px;
    border-radius:5px;
}

.pending{
    color:#ff9800;
    font-weight:bold;
}

.approved,
.booked,
.received,
.arrived{
    color:#0066cc;
    font-weight:bold;
}

.delivered{
    color:green;
    font-weight:bold;
}

.cancelled{
    color:red;
    font-weight:bold;
}

.transit{
    color:#2196f3;
    font-weight:bold;
}

</style>

</head>

<body>

<div class="header">

<h1>🚚 Airsell Cargo Shipment Tracking</h1>

<p>Track your shipment using Reference or HAWB Number</p>

</div>

<div class="container">

<div class="card">

<form method="POST">

<input
type="text"
name="tracking_number"
placeholder="Enter Reference or HAWB Number"
required>

<button type="submit">

Track Shipment

</button>

</form>

</div>

<?php if($message): ?>

<div class="error">

<?php echo $message; ?>

</div>

<?php endif; ?>

<?php if($shipment): ?>

<?php
$statusClass = strtolower(
    str_replace(' ','',$shipment['status'])
);
?>

<div class="card">

<h2>Shipment Details</h2>

<div class="row">
<span class="label">Reference:</span>
<?php echo htmlspecialchars($shipment['reference']); ?>
</div>

<div class="row">
<span class="label">HAWB Number:</span>
<?php echo htmlspecialchars($shipment['hawb_number']); ?>
</div>

<div class="row">
<span class="label">Status:</span>

<span class="<?php echo $statusClass; ?>">

<?php echo htmlspecialchars($shipment['status']); ?>

</span>

</div>

<div class="row">
<span class="label">Origin:</span>
<?php echo htmlspecialchars($shipment['origin']); ?>
</div>

<div class="row">
<span class="label">Destination:</span>
<?php echo htmlspecialchars($shipment['destination']); ?>
</div>

<div class="row">
<span class="label">Shipment Date:</span>
<?php echo htmlspecialchars($shipment['shipment_date']); ?>
</div>

<div class="row">
<span class="label">Pieces:</span>
<?php echo htmlspecialchars($shipment['pieces']); ?>
</div>

<div class="row">
<span class="label">Weight:</span>
<?php echo htmlspecialchars($shipment['weight_kg']); ?> KG
</div>

<div class="row">
<span class="label">Last Updated:</span>
<?php echo htmlspecialchars($shipment['updated_at']); ?>
</div>

</div>

<?php endif; ?>

</div>

</body>
</html>