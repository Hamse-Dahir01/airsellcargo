<?php

session_start();

if (!isset($_SESSION['admin_id'])) {

    header("Location: login.php");
    exit;
}
require 'db.php';

$search = trim($_GET['search'] ?? '');

/*
|--------------------------------------------------------------------------
| Search Shipments
|--------------------------------------------------------------------------
*/
if (!empty($search)) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM cargo_applications
        WHERE reference LIKE ?
           OR hawb_number LIKE ?
           OR sender_name LIKE ?
           OR receiver_name LIKE ?
        ORDER BY id DESC
    ");

    $stmt->execute([
        "%$search%",
        "%$search%",
        "%$search%",
        "%$search%"
    ]);

} else {

    $stmt = $pdo->query("
        SELECT *
        FROM cargo_applications
        ORDER BY id DESC
    ");
}

$shipments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/
$total_shipments = $pdo->query("
    SELECT COUNT(*) FROM cargo_applications
")->fetchColumn();

$pending = $pdo->query("
    SELECT COUNT(*) FROM cargo_applications
    WHERE status='Pending'
")->fetchColumn();

$transit = $pdo->query("
    SELECT COUNT(*) FROM cargo_applications
    WHERE status='In Transit'
")->fetchColumn();

$delivered = $pdo->query("
    SELECT COUNT(*) FROM cargo_applications
    WHERE status='Delivered'
")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Airsell Cargo Admin Dashboard</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial, sans-serif;
    background:#f4f6f9;
}

.header{
    background:#003366;
    color:#fff;
    padding:20px 30px;
}

.header h1{
    font-size:28px;
}

.container{
    width:95%;
    margin:20px auto;
}

.stats{
    display:flex;
    gap:20px;
    flex-wrap:wrap;
    margin-bottom:20px;
}

.card{
    background:#fff;
    flex:1;
    min-width:220px;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}

.card h3{
    color:#666;
    margin-bottom:10px;
}

.card h1{
    color:#003366;
}

.search-box{
    background:#fff;
    padding:20px;
    border-radius:8px;
    margin-bottom:20px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}

.search-box input{
    width:300px;
    padding:10px;
}

.search-box button{
    padding:10px 15px;
    background:#0066cc;
    color:white;
    border:none;
    cursor:pointer;
}

.search-box button:hover{
    background:#0055aa;
}

.table-container{
    background:white;
    padding:20px;
    border-radius:8px;
    overflow-x:auto;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#003366;
    color:white;
}

th,td{
    padding:12px;
    border:1px solid #ddd;
    text-align:left;
}

tr:nth-child(even){
    background:#f9f9f9;
}

.status{
    font-weight:bold;
}

.pending{
    color:#ff9800;
}

.transit{
    color:#2196f3;
}

.delivered{
    color:#4caf50;
}

.approved,
.booked,
.received,
.arrived{
    color:#0066cc;
}

.cancelled{
    color:red;
}

.action-btn{
    background:#0066cc;
    color:white;
    text-decoration:none;
    padding:8px 12px;
    border-radius:4px;
}

.action-btn:hover{
    background:#004fa3;
}

</style>

</head>

<body>

<div class="header">
    <h1>🚚 Airsell Cargo Administration Dashboard</h1>
</div>

<div class="container">

    <div class="stats">

        <div class="card">
            <h3>Total Shipments</h3>
            <h1><?php echo $total_shipments; ?></h1>
        </div>

        <div class="card">
            <h3>Pending</h3>
            <h1><?php echo $pending; ?></h1>
        </div>

        <div class="card">
            <h3>In Transit</h3>
            <h1><?php echo $transit; ?></h1>
        </div>

        <div class="card">
            <h3>Delivered</h3>
            <h1><?php echo $delivered; ?></h1>
        </div>

    </div>

    <div class="search-box">

        <form method="GET">

            <input
                type="text"
                name="search"
                placeholder="Search Reference, HAWB, Sender or Receiver"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">
                Search
            </button>

        </form>

    </div>

    <div class="table-container">

        <table>

            <tr>
                <th>ID</th>
                <th>Reference</th>
                <th>HAWB</th>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Destination</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>

            <?php foreach($shipments as $row): ?>

            <?php

            $statusClass = strtolower(
                str_replace(' ', '', $row['status'])
            );

            ?>

            <tr>

                <td><?php echo $row['id']; ?></td>

                <td><?php echo htmlspecialchars($row['reference']); ?></td>

                <td><?php echo htmlspecialchars($row['hawb_number']); ?></td>

                <td><?php echo htmlspecialchars($row['sender_name']); ?></td>

                <td><?php echo htmlspecialchars($row['receiver_name']); ?></td>

                <td><?php echo htmlspecialchars($row['destination']); ?></td>

                <td class="status <?php echo $statusClass; ?>">
                    <?php echo htmlspecialchars($row['status']); ?>
                </td>

                <td><?php echo $row['created_at']; ?></td>

                <td>
                    <a
                        class="action-btn"
                        href="view_booking.php?id=<?php echo $row['id']; ?>"
                    >
                        View
                    </a>
                </td>

            </tr>

            <?php endforeach; ?>

        </table>

    </div>

</div>

</body>
</html>