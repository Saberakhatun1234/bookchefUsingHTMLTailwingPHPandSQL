<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    
    $profile_pic = $user['profile_pic'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $pic_name = uniqid() . "_" . $_FILES['profile_pic']['name'];
        $target = "../assets/uploads/" . $pic_name;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target);
        $profile_pic = $pic_name;
    }

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address=?, profile_pic=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $address, $profile_pic, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['user_name'] = $name;
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile - BookChef</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white p-8 rounded shadow-md w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-4 text-center text-blue-600">Edit Profile</h2>

    <form method="POST" enctype="multipart/form-data">
      <label class="block mb-1 font-medium">Name</label>
      <input type="text" name="name" value="<?= $user['name'] ?>" required class="w-full mb-4 p-2 border rounded">

      <label class="block mb-1 font-medium">Email</label>
      <input type="email" name="email" value="<?= $user['email'] ?>" required class="w-full mb-4 p-2 border rounded">

      <label class="block mb-1 font-medium">Phone</label>
      <input type="text" name="phone" value="<?= $user['phone'] ?>" required class="w-full mb-4 p-2 border rounded">

      <label class="block mb-1 font-medium">Address</label>
      <textarea name="address" required class="w-full mb-4 p-2 border rounded"><?= $user['address'] ?></textarea>

      <label class="block mb-1 font-medium">Change Profile Picture</label>
      <input type="file" name="profile_pic" accept="image/*" class="mb-4">

      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update</button>
    </form>
  </div>

</body>
</html>
