<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// Session timeout duration (30 minutes)
define('SESSION_TIMEOUT', 1800);

/**
 * Database connection
 */
function getDBConnection() {
    $db_host = "localhost";
    $db_port = "3306";
    $db_user = "root";
    $db_pass = ""; 
    $db_name = "core2_test";

    try {
        $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Mailer configuration (Gmail with App Password)
 */
function getMailer() {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'mahinlorouvel@gmail.com'; // ✅ your Gmail
    $mail->Password   = 'vixt qyty emfx lnrd';    // ✅ your App Password (16-char, no spaces!)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Make sure "From" matches your Gmail
    $mail->setFrom('mahinlorouvel@gmail.com', 'Secure Login');
    return $mail;
}

// --- SESSION CHECK ---
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    } else {
        $_SESSION['last_activity'] = time();
        header("Location: homes.php");
        exit();
    }
}

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
        header("Location: login.php");
        exit();
    }

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields!";
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email, password FROM sellers WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // ✅ Generate OTP
                $otp = rand(100000, 999999);

                // 🔹 Store OTP safely (as string for consistency)
                $_SESSION['pending_user']   = $user['id'];
                $_SESSION['pending_email']  = $user['email'];
                $_SESSION['otp']            = (string)$otp;
                $_SESSION['otp_expires']    = time() + 300;

                // ✅ Debug (optional) - to verify OTP is stored
                // echo "Generated OTP: " . $_SESSION['otp']; exit;

                try {
                    $mail = getMailer();
                    $mail->addAddress($user['email'], $user['name']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP Code';
                    $mail->Body    = "Hello {$user['name']},<br><br>Your OTP is: <b>{$otp}</b><br><br>This code expires in 5 minutes.";

                    $mail->send();

                    header("Location: verify_otp.php");
                    exit();

                } catch (Exception $e) {
                    $error = "OTP could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Invalid email or password!";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg p-4">
                <h3 class="text-center mb-4">Login</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if(isset($_GET['timeout'])): ?>
                    <div class="alert alert-warning">Your session has expired due to inactivity. Please log in again.</div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <p class="text-center mt-3">Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
