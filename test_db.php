<?php

require 'db.php';

try {

    $stmt = $pdo->query("SELECT DATABASE()");
    $database = $stmt->fetchColumn();

    echo "<h2 style='color:green'>✅ Database Connected Successfully</h2>";
    echo "<p>Current Database: <strong>$database</strong></p>";

} catch(Exception $e) {

    echo "<h2 style='color:red'>❌ Database Connection Failed</h2>";
    echo $e->getMessage();

}