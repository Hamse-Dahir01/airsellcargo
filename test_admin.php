<?php

require 'db.php';

$stmt = $pdo->query("SELECT id, username FROM admins");

while($row = $stmt->fetch()) {

    echo $row['id'] . " - " . $row['username'] . "<br>";

}