<?php
// Database configuration
$host = "localhost";
$user = "root"; 
$pass = "";     
$db   = "ecommerce";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

$success = false;
$error = "";
$shop = null;

// Get shop info
$id = 1;
$sql = "SELECT * FROM shop_settings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$shop = $result->fetch_assoc();

// If no record exists, create default
if (!$shop) {
    $insert_sql = "INSERT INTO shop_settings (shop_name, contact_info, policies, payment_methods, logo, banner) VALUES (?, '', '', '', NULL, NULL)";
    $insert_stmt = $conn->prepare($insert_sql);
    $default_name = "My Shop";
    $insert_stmt->bind_param("s", $default_name);
    
    if ($insert_stmt->execute()) {
        $id = $conn->insert_id;
        // Get the newly created record
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $shop = $result->fetch_assoc();
    }
    $insert_stmt->close();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $shop_name = trim($_POST["shop_name"]);
        $contact_info = trim($_POST["contact_info"]);
        $policies = trim($_POST["policies"]);
        $payment_methods = trim($_POST["payment_methods"]);
        
        $logo = $shop["logo"];
        $banner = $shop["banner"];

        // Create uploads directory
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Handle logo upload
        if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES["logo"]["type"];
            
            if (in_array($file_type, $allowed_types) && $_FILES["logo"]["size"] <= 5000000) { // 5MB limit
                $file_extension = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
                $new_filename = "logo_" . time() . "." . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["logo"]["tmp_name"], $upload_path)) {
                    // Delete old logo
                    if ($shop["logo"] && file_exists($shop["logo"])) {
                        unlink($shop["logo"]);
                    }
                    $logo = $upload_path;
                }
            }
        }

        // Handle banner upload
        if (isset($_FILES["banner"]) && $_FILES["banner"]["error"] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES["banner"]["type"];
            
            if (in_array($file_type, $allowed_types) && $_FILES["banner"]["size"] <= 5000000) { // 5MB limit
                $file_extension = pathinfo($_FILES["banner"]["name"], PATHINFO_EXTENSION);
                $new_filename = "banner_" . time() . "." . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["banner"]["tmp_name"], $upload_path)) {
                    // Delete old banner
                    if ($shop["banner"] && file_exists($shop["banner"])) {
                        unlink($shop["banner"]);
                    }
                    $banner = $upload_path;
                }
            }
        }

        // Update database
        $update_sql = "UPDATE shop_settings SET shop_name = ?, contact_info = ?, policies = ?, payment_methods = ?, logo = ?, banner = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssssi", $shop_name, $contact_info, $policies, $payment_methods, $logo, $banner, $id);
        
        if ($update_stmt->execute()) {
            $success = true;
            // Refresh shop data
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $shop = $result->fetch_assoc();
        } else {
            $error = "Failed to update shop settings.";
        }
        $update_stmt->close();
        
    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Shop Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Shop Profile</h1>
            <p>Manage and update your shop's information and appearance</p>
        </div>

        <?php if ($success): ?>
            <div class="message success-message">
                ✅ Shop profile updated successfully!
            </div>
        <?php elseif ($error): ?>
            <div class="message error-message">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="shop_name">Shop Name</label>
                        <input type="text" id="shop_name" name="shop_name" 
                               value="<?php echo htmlspecialchars($shop['shop_name'] ?? ''); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="payment_methods">Payment Methods</label>
                        <input type="text" id="payment_methods" name="payment_methods" 
                               value="<?php echo htmlspecialchars($shop['payment_methods'] ?? ''); ?>" 
                               placeholder="e.g. Credit Card, PayPal, Bank Transfer">
                    </div>

                    <div class="form-group">
                        <label>Shop Logo</label>
                        <div class="file-upload-container">
                            <span class="upload-icon">📷</span>
                            <div class="upload-text">
                                <strong>Click to upload logo</strong><br>
                                PNG, JPG up to 5MB
                            </div>
                            <input type="file" name="logo" accept="image/*" onchange="previewImage(event, 'logoPreview')">
                        </div>
                        <?php if (!empty($shop['logo']) && file_exists($shop['logo'])): ?>
                            <img src="<?php echo htmlspecialchars($shop['logo']); ?>" class="current-image" alt="Current Logo">
                        <?php endif; ?>
                        <img id="logoPreview" class="image-preview" alt="Logo Preview">
                    </div>

                    <div class="form-group">
                        <label>Shop Banner</label>
                        <div class="file-upload-container">
                            <span class="upload-icon">🖼️</span>
                            <div class="upload-text">
                                <strong>Click to upload banner</strong><br>
                                PNG, JPG up to 5MB
                            </div>
                            <input type="file" name="banner" accept="image/*" onchange="previewImage(event, 'bannerPreview')">
                        </div>
                        <?php if (!empty($shop['banner']) && file_exists($shop['banner'])): ?>
                            <img src="<?php echo htmlspecialchars($shop['banner']); ?>" class="current-image" alt="Current Banner">
                        <?php endif; ?>
                        <img id="bannerPreview" class="image-preview" alt="Banner Preview">
                    </div>

                    <div class="form-group full-width">
                        <label for="contact_info">Contact Information</label>
                        <textarea id="contact_info" name="contact_info" rows="3" 
                                  placeholder="Enter your contact details, address, phone number, email, etc."><?php echo htmlspecialchars($shop['contact_info'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="policies">Shop Policies</label>
                        <textarea id="policies" name="policies" rows="4" 
                                  placeholder="Enter your shop policies, return policy, shipping info, etc."><?php echo htmlspecialchars($shop['policies'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="button-container">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        ← Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Update Shop Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(event, previewId) {
            const file = event.target.files[0];
            const preview = document.getElementById(previewId);
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.add('show');
                }
                reader.readAsDataURL(file);
            }
        }

        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                message.style.transition = 'opacity 0.5s';
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>