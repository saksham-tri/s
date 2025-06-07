<?php
include 'db.php';

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM category WHERE catid=$id");
}

header("Location: index.php");
exit();
