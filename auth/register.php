<?php
include '../config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $profile_pic = "";
    if ($_FILES['profile_pic']['name']) {
        $targetDir = "../assets/uploads/";
        $profile_pic = uniqid() . "_" . basename($_FILES["profile_pic"]["name"]);
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetDir . $profile_pic);
    }

    $sql = "INSERT INTO users (name, email, password, phone, address, profile_pic) 
            VALUES ('$name', '$email', '$pass', '$phone', '$address', '$profile_pic')";

    if ($conn->query($sql) === TRUE) {
        $message = "Registration successful. <a href='login.php' class='text-blue-600'>Login here</a>";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - BookChef</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Create Account</h2>

    <?php if ($message): ?>
      <div class="mb-4 text-center text-green-600 font-semibold"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <input type="text" name="name" placeholder="Name" required class="w-full mb-4 p-2 border rounded">
      <input type="email" name="email" placeholder="Email" required class="w-full mb-4 p-2 border rounded">
      <input type="password" name="password" placeholder="Password" required class="w-full mb-4 p-2 border rounded">
      <input type="text" name="phone" placeholder="Phone" required class="w-full mb-4 p-2 border rounded">
      <input type="text" name="address" placeholder="Address" required class="w-full mb-4 p-2 border rounded">
      <label class="block mb-2 font-medium">Profile Picture:</label>
      <input type="file" name="profile_pic" accept="image/*" class="mb-4">
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Register</button>
    </form>

    <p class="mt-4 text-center text-sm">
      Already have an account? <a href="login.php" class="text-blue-600">Login</a>
    </p>
  </div>
</body>
</html>
