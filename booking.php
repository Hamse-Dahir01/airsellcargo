<?php
require 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $reference = "ASC-" . str_pad(rand(1,999999), 6, "0", STR_PAD_LEFT);

    $stmt = $pdo->prepare("
        INSERT INTO cargo_applications (
            reference,
            sender_name,
            sender_phone,
            sender_email,
            sender_address,
            receiver_name,
            receiver_phone,
            receiver_email,
            receiver_address,
            origin,
            destination,
            shipment_date,
            pieces,
            weight_kg,
            dimensions,
            cargo_description,
            payment_terms,
            special_instructions,
            consent_given
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $reference,
        $_POST['sender_name'],
        $_POST['sender_phone'],
        $_POST['sender_email'],
        $_POST['sender_address'],
        $_POST['receiver_name'],
        $_POST['receiver_phone'],
        $_POST['receiver_email'],
        $_POST['receiver_address'],
        $_POST['origin'],
        $_POST['destination'],
        $_POST['shipment_date'],
        $_POST['pieces'],
        $_POST['weight_kg'],
        $_POST['dimensions'],
        $_POST['cargo_description'],
        $_POST['payment_terms'],
        $_POST['special_instructions'],
        1
    ]);

    $id = $pdo->lastInsertId();

    $hawb = "ASC-HAWB" . str_pad($id, 6, "0", STR_PAD_LEFT);

    $update = $pdo->prepare("
        UPDATE cargo_applications
        SET hawb_number = ?
        WHERE id = ?
    ");

    $update->execute([$hawb, $id]);

    $message = "Booking Submitted Successfully.<br>
                Reference: $reference<br>
                HAWB: $hawb";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Airsell Cargo Booking</title>
    <style>
        body{
            font-family:Arial;
            max-width:900px;
            margin:auto;
            padding:20px;
        }

        input, textarea, select{
            width:100%;
            padding:10px;
            margin-bottom:10px;
        }

        button{
            background:#0066cc;
            color:#fff;
            padding:12px 20px;
            border:none;
            cursor:pointer;
        }

        .success{
            background:#d4edda;
            color:#155724;
            padding:15px;
            margin-bottom:20px;
        }
    </style>
</head>
<body>

<h2>Airsell Cargo Booking Form</h2>

<?php if($message): ?>
<div class="success">
    <?= $message ?>
</div>
<?php endif; ?>

<form method="post">

<h3>Sender Information</h3>

<input type="text" name="sender_name" placeholder="Sender Name" required>

<input type="text" name="sender_phone" placeholder="Sender Phone" required>

<input type="email" name="sender_email" placeholder="Sender Email">

<input type="text" name="sender_address" placeholder="Sender Address" required>

<h3>Receiver Information</h3>

<input type="text" name="receiver_name" placeholder="Receiver Name" required>

<input type="text" name="receiver_phone" placeholder="Receiver Phone" required>

<input type="email" name="receiver_email" placeholder="Receiver Email">

<input type="text" name="receiver_address" placeholder="Receiver Address" required>

<h3>Shipment Information</h3>

<input type="text" name="origin" placeholder="Origin" required>

<input type="text" name="destination" placeholder="Destination" required>

<input type="date" name="shipment_date" required>

<input type="number" name="pieces" min="1" required>

<input type="number" step="0.01" name="weight_kg" required>

<input type="text" name="dimensions" placeholder="Dimensions (L x W x H)">

<textarea name="cargo_description" placeholder="Cargo Description" required></textarea>

<select name="payment_terms" required>
    <option value="">Select Payment Terms</option>
    <option value="Prepaid">Prepaid</option>
    <option value="Collect">Collect</option>
</select>

<textarea name="special_instructions"
    placeholder="Special Instructions"></textarea>

<label>
    <input type="checkbox" required>
    I consent to the processing of my shipment information.
</label>

<br><br>

<button type="submit">
    Submit Booking
</button>

</form>

</body>
</html>