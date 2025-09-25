<?php
$host = "localhost";
$user = "root"; // change if you use another user
$pass = "";     // change if you set a password
$db   = "ecommerce";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shop_name = $_POST["shop_name"];
    $contact_info = $_POST["contact_info"];
    $policies = $_POST["policies"];
    $payment_methods = $_POST["payment_methods"];

    // Handle file uploads (logo + banner)
    $logo = null;
    $banner = null;

    if (!empty($_FILES["logo"]["name"])) {
        $logo = "uploads/" . basename($_FILES["logo"]["name"]);
        move_uploaded_file($_FILES["logo"]["tmp_name"], $logo);
    }

    if (!empty($_FILES["banner"]["name"])) {
        $banner = "uploads/" . basename($_FILES["banner"]["name"]);
        move_uploaded_file($_FILES["banner"]["tmp_name"], $banner);
    }

    // Insert or update (assuming only 1 shop)
    $check = $conn->query("SELECT * FROM shop_settings WHERE id=1");
    if ($check->num_rows > 0) {
        $sql = "UPDATE shop_settings SET 
                    shop_name='$shop_name',
                    contact_info='$contact_info',
                    policies='$policies',
                    payment_methods='$payment_methods'";

        if ($logo) $sql .= ", logo='$logo'";
        if ($banner) $sql .= ", banner='$banner'";

        $sql .= " WHERE id=1";
    } else {
        $sql = "INSERT INTO shop_settings (id, shop_name, logo, banner, contact_info, policies, payment_methods) 
                VALUES (1, '$shop_name', '$logo', '$banner', '$contact_info', '$policies', '$payment_methods')";
    }
    $conn->query($sql);
    header("Location: shop_settings.php?success=1");
    exit();
}

// Fetch existing settings
$result = $conn->query("SELECT * FROM shop_settings WHERE id=1");
$shop = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shop Profile & Settings</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Shop Profile & Settings</h1>

    <?php if (isset($_GET["success"])): ?>
      <p class="success">Settings updated successfully!</p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Shop Logo:</label>
        <input type="file" name="logo" accept="image/*">
        <?php if (!empty($shop["logo"])): ?>
            <img src="<?= $shop["logo"] ?>" alt="Shop Logo" class="preview">
        <?php endif; ?>

        <label>Shop Banner:</label>
        <input type="file" name="banner" accept="image/*">
        <?php if (!empty($shop["banner"])): ?>
            <img src="<?= $shop["banner"] ?>" alt="Shop Banner" class="preview">
        <?php endif; ?>

        <label>Shop Name:</label>
        <input type="text" name="shop_name" value="<?= $shop["shop_name"] ?? '' ?>" required>

        <label>Contact Info:</label>
        <textarea name="contact_info" rows="3"><?= $shop["contact_info"] ?? '' ?></textarea>

        <label>Shop Policies:</label>
        <textarea name="policies" rows="4"><?= $shop["policies"] ?? '' ?></textarea>

        <label>Payment Methods:</label>
        <input type="text" name="payment_methods" value="<?= $shop["payment_methods"] ?? '' ?>">

        <button type="submit">Save Settings</button>
    </form>
</div>
</body>
</html>
