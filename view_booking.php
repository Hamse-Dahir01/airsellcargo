<?php

require 'db.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    die("Invalid shipment ID.");
}

/*
|--------------------------------------------------------------------------
| Update Status
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = $_POST['status'];

    $update = $pdo->prepare("
        UPDATE cargo_applications
        SET status = ?
        WHERE id = ?
    ");

    $update->execute([
        $status,
        $id
    ]);

    header("Location: view_booking.php?id=".$id."&success=1");
    exit;
}

/*
|--------------------------------------------------------------------------
| Load Shipment
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM cargo_applications
    WHERE id = ?
");

$stmt->execute([$id]);

$shipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shipment) {
    die("Shipment not found.");
}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Shipment Details</title>

<style>

body{
    font-family:Arial,sans-serif;
    background:#f4f6f9;
    margin:0;
}

.header{
    background:#003366;
    color:white;
    padding:20px;
}

.container{
    width:90%;
    margin:30px auto;
}

.card{
    background:white;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 10px rgba(0,0,0,.1);
    margin-bottom:20px;
}

h2{
    color:#003366;
    margin-bottom:15px;
}

.row{
    margin-bottom:10px;
}

.label{
    font-weight:bold;
    color:#333;
}

.success{
    background:#d4edda;
    color:#155724;
    padding:15px;
    border-radius:6px;
    margin-bottom:15px;
}

select{
    padding:10px;
    width:250px;
}

button{
    padding:10px 20px;
    border:none;
    background:#0066cc;
    color:white;
    cursor:pointer;
}

button:hover{
    background:#004fa3;
}

.back-btn{
    text-decoration:none;
    background:#555;
    color:white;
    padding:10px 15px;
    border-radius:5px;
}

</style>

</head>

<body>

<div class="header">

<h1>Shipment Details</h1>

</div>

<div class="container">

<?php if(isset($_GET['success'])): ?>

<div class="success">

Status updated successfully.

</div>

<?php endif; ?>

<div class="card">

<h2>Shipment Information</h2>

<div class="row">
<span class="label">Reference:</span>
<?php echo htmlspecialchars($shipment['reference']); ?>
</div>

<div class="row">
<span class="label">HAWB:</span>
<?php echo htmlspecialchars($shipment['hawb_number']); ?>
</div>

<div class="row">
<span class="label">Status:</span>
<?php echo htmlspecialchars($shipment['status']); ?>
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
<span class="label">Dimensions:</span>
<?php echo htmlspecialchars($shipment['dimensions']); ?>
</div>

<div class="row">
<span class="label">Cargo Description:</span>
<?php echo htmlspecialchars($shipment['cargo_description']); ?>
</div>

<div class="row">
<span class="label">Payment Terms:</span>
<?php echo htmlspecialchars($shipment['payment_terms']); ?>
</div>

</div>

<div class="card">

<h2>Sender Information</h2>

<div class="row">
<span class="label">Name:</span>
<?php echo htmlspecialchars($shipment['sender_name']); ?>
</div>

<div class="row">
<span class="label">Phone:</span>
<?php echo htmlspecialchars($shipment['sender_phone']); ?>
</div>

<div class="row">
<span class="label">Email:</span>
<?php echo htmlspecialchars($shipment['sender_email']); ?>
</div>

<div class="row">
<span class="label">Address:</span>
<?php echo htmlspecialchars($shipment['sender_address']); ?>
</div>

</div>

<div class="card">

<h2>Receiver Information</h2>

<div class="row">
<span class="label">Name:</span>
<?php echo htmlspecialchars($shipment['receiver_name']); ?>
</div>

<div class="row">
<span class="label">Phone:</span>
<?php echo htmlspecialchars($shipment['receiver_phone']); ?>
</div>

<div class="row">
<span class="label">Email:</span>
<?php echo htmlspecialchars($shipment['receiver_email']); ?>
</div>

<div class="row">
<span class="label">Address:</span>
<?php echo htmlspecialchars($shipment['receiver_address']); ?>
</div>

</div>

<div class="card">

<h2>Update Shipment Status</h2>

<form method="POST">

<select name="status">

<option value="Pending" <?php if($shipment['status']=='Pending') echo 'selected'; ?>>Pending</option>

<option value="Approved" <?php if($shipment['status']=='Approved') echo 'selected'; ?>>Approved</option>

<option value="Booked" <?php if($shipment['status']=='Booked') echo 'selected'; ?>>Booked</option>

<option value="Received" <?php if($shipment['status']=='Received') echo 'selected'; ?>>Received</option>

<option value="In Transit" <?php if($shipment['status']=='In Transit') echo 'selected'; ?>>In Transit</option>

<option value="Arrived" <?php if($shipment['status']=='Arrived') echo 'selected'; ?>>Arrived</option>

<option value="Delivered" <?php if($shipment['status']=='Delivered') echo 'selected'; ?>>Delivered</option>

<option value="Cancelled" <?php if($shipment['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>

</select>

<br><br>

<button type="submit">

Save Status

</button>

</form>

</div>

<a class="back-btn" href="admin.php">

← Back to Dashboard

</a>

</div>

</body>
</html>