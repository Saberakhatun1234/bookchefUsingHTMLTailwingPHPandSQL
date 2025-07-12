<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $specialty = $_POST['specialty'];
    $experience = $_POST['experience'];
    $fees = $_POST['fees'];

    $pic = "";
    if (!empty($_FILES['picture']['name'])) {
        $pic = uniqid() . "_" . $_FILES['picture']['name'];
        move_uploaded_file($_FILES['picture']['tmp_name'], "../assets/uploads/" . $pic);
    }

    $stmt = $conn->prepare("INSERT INTO chefs (name, specialty, experience, fees, picture) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $name, $specialty, $experience, $fees, $pic);
    $stmt->execute();
    $stmt->close();

    header("Location: chefs.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Chef - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <form method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded shadow-md w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-4 text-center text-green-600">Add Chef</h2>

    <input type="text" name="name" placeholder="Chef Name" required class="w-full mb-4 p-2 border rounded">
    <input type="text" name="specialty" placeholder="Specialty" required class="w-full mb-4 p-2 border rounded">
    <input type="text" name="experience" placeholder="Experience (e.g. 5 years)" required class="w-full mb-4 p-2 border rounded">
    <input type="number" name="fees" placeholder="Fees in ₹" required class="w-full mb-4 p-2 border rounded">
    <label>Chef Picture:</label>
    <input type="file" name="picture" accept="image/*" required class="mb-4">

    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Add Chef</button>
  </form>

</body>
</html>
