<?php
include 'db.php';

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM category WHERE catid=$id");
    $row = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Category</title>
</head>
<body>
    <h2>Edit Category</h2>
    <form action="update.php" method="post">
        <input type="hidden" name="catid" value="<?= $row['catid']; ?>">
        <input type="text" name="category" value="<?= $row['category']; ?>" required>
        <button type="submit" name="update">Update</button>
        <a href="index.php">Cancel</a>
    </form>
</body>
</html>

<?php
if (isset($_POST['update'])) {
    $id = $_POST['catid'];
    $category = $conn->real_escape_string($_POST['category']);
    $conn->query("UPDATE category SET category='$category' WHERE catid=$id");
    header("Location: index.php");
    exit();
}
?>
