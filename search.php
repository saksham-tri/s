<?php
include 'db.php';

$search = $_GET['q'] ?? '';
$search = $conn->real_escape_string($search);

$query = $conn->query("SELECT proid, productname FROM products WHERE productname LIKE '%$search%' LIMIT 10");

$results = [];
while ($row = $query->fetch_assoc()) {
    $results[] = $row;
}

header('Content-Type: application/json');
echo json_encode($results);
?>