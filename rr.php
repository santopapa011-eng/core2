<?php
session_start();

// Check for seller ID - multiple session variable possibilities
$seller_id = $_SESSION['seller_id'] ?? $_SESSION['id'] ?? $_SESSION['user_id'] ?? null;

// If no seller_id found, redirect to login
if (!$seller_id) {
    header("Location: login.php");
    exit;
}

// Enhanced DB Connection function with better error handling
function getDBConnection() {
    $db_host = "localhost";
    $db_port = "3306";
    $db_user = "root";
    $db_pass = "";
    $db_name = "core2_test";

    try {
        // First test basic connection
        $testPdo = new PDO("mysql:host=$db_host;port=$db_port;charset=utf8mb4", $db_user, $db_pass);
        $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if database exists
        $stmt = $testPdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$db_name]);
        
        if (!$stmt->fetch()) {
            // Database doesn't exist, create it
            $testPdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
        
        // Connect to the specific database
        $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
        
    } catch (PDOException $e) {
        displayConnectionError("Database Connection Failed: " . $e->getMessage());
        exit;
    }
}

function displayConnectionError($errorMsg) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100">
        <div class="min-h-screen flex items-center justify-center py-12 px-4">
            <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-8">
                <div class="text-center mb-6">
                    <div class="mx-auto h-12 w-12 text-red-600 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Database Connection Error</h2>
                </div>
                
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="text-red-800 text-sm">
                        <?= htmlspecialchars($errorMsg) ?>
                    </div>
                </div>
                
                <div class="text-center">
                    <button onclick="location.reload()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Retry Connection
                    </button>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Initialize database connection
$pdo = getDBConnection();
$sellerId = (int)$seller_id; // Ensure it's an integer

// Handle Approve / Complete / Cancel / Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $orderId = (int)$_POST['order_id'];
    $action = trim($_POST['action']);

    // Validate order belongs to seller first
    try {
        $checkStmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND seller_id = ?");
        $checkStmt->execute([$orderId, $sellerId]);
        
        if (!$checkStmt->fetch()) {
            $_SESSION['message'] = 'Order not found or access denied!';
            $_SESSION['message_type'] = 'error';
        } else {
            // Process the action
            switch ($action) {
                case 'approve':
                    $stmt = $pdo->prepare("UPDATE orders SET refund_status = 'approved' WHERE id = ? AND seller_id = ?");
                    $stmt->execute([$orderId, $sellerId]);
                    $_SESSION['message'] = 'Refund request approved successfully!';
                    $_SESSION['message_type'] = 'success';
                    break;
                    
                case 'process':
                    $stmt = $pdo->prepare("UPDATE orders SET refund_status = 'processing' WHERE id = ? AND seller_id = ?");
                    $stmt->execute([$orderId, $sellerId]);
                    $_SESSION['message'] = 'Refund is now being processed!';
                    $_SESSION['message_type'] = 'success';
                    break;
                    
                case 'complete':
                    $stmt = $pdo->prepare("UPDATE orders SET refund_status = 'completed', refund_processed_date = NOW() WHERE id = ? AND seller_id = ?");
                    $stmt->execute([$orderId, $sellerId]);
                    $_SESSION['message'] = 'Refund completed successfully!';
                    $_SESSION['message_type'] = 'success';
                    break;
                    
                case 'reject':
                    $rejectionReason = trim($_POST['rejection_reason'] ?? '');
                    if (empty($rejectionReason)) {
                        $_SESSION['message'] = 'Rejection reason is required!';
                        $_SESSION['message_type'] = 'error';
                    } else {
                        $stmt = $pdo->prepare("UPDATE orders SET refund_status = 'rejected', rejection_reason = ?, rejection_date = NOW() WHERE id = ? AND seller_id = ?");
                        $stmt->execute([$rejectionReason, $orderId, $sellerId]);
                        $_SESSION['message'] = 'Refund request rejected successfully!';
                        $_SESSION['message_type'] = 'success';
                    }
                    break;
                
                case 'add_note':
                    $_SESSION['message'] = 'This action is only available to administrators.';
                    $_SESSION['message_type'] = 'error';
                    break;
                    
                default:
                    $_SESSION['message'] = 'Invalid action!';
                    $_SESSION['message_type'] = 'error';
            }
        }
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error updating refund status: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    
    header("Location: " . $_SERVER['PHP_SELF'] . ($_GET ? '?' . http_build_query($_GET) : ''));
    exit;
}

// Fetch all orders for seller with enhanced error handling
$orders = [];
try {
    // Modified query to handle missing tables gracefully
    $stmt = $pdo->prepare("
        SELECT o.*, 
            COALESCE(
                CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,'')), 
                CONCAT('Customer #', o.customer_id)
            ) AS customer_name, 
            COALESCE(c.email, '') AS customer_email, 
            COALESCE(c.phone, '') AS customer_phone,
            COALESCE(p.name, CONCAT('Product #', o.product_id)) AS product_name, 
            COALESCE(p.price, 0) AS product_price, 
            COALESCE(p.image, '') AS product_image
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        LEFT JOIN products p ON o.product_id = p.id
        WHERE o.seller_id = ? AND o.refund_status IS NOT NULL AND o.refund_status != ''
        ORDER BY 
            CASE o.refund_status
                WHEN 'requested' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'processing' THEN 3
                WHEN 'completed' THEN 4
                WHEN 'rejected' THEN 5
                ELSE 6
            END,
            o.created_at DESC
    ");
    $stmt->execute([$sellerId]);
    $rows = $stmt->fetchAll();

    // Process orders - handle duplicates properly
    foreach($rows as $row) {
        $id = $row['id'];
        if(!isset($orders[$id])) {
            $orders[$id] = $row;
            $orders[$id]['products'] = [];
            // Clean up customer name
            $customerName = trim($orders[$id]['customer_name']);
            $orders[$id]['customer_name'] = $customerName ?: 'Customer #' . $orders[$id]['customer_id'];
        }
        
        // Add product info
        $orders[$id]['products'][] = [
            'name' => $row['product_name'],
            'price' => floatval($row['product_price']),
            'image' => $row['product_image'],
            'quantity' => intval($row['quantity'] ?? 1)
        ];
    }

} catch (PDOException $e) {
    // More specific error handling
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $_SESSION['message'] = 'Database tables are not properly set up. Please contact your administrator.';
    } else {
        $_SESSION['message'] = 'Unable to fetch refund data: ' . $e->getMessage();
    }
    $_SESSION['message_type'] = 'error';
    $orders = [];
}

// Get filter from URL and sanitize
$allowedFilters = ['all', 'requested', 'approved', 'processing', 'completed', 'rejected'];
$filter = in_array($_GET['status'] ?? 'all', $allowedFilters) ? $_GET['status'] : 'all';
$filteredOrders = [];

if ($filter === 'all') {
    $filteredOrders = $orders;
} else {
    $filteredOrders = array_filter($orders, function($o) use ($filter) {
        return ($o['refund_status'] ?? 'requested') === $filter;
    });
}

// Get status counts
$statusCounts = [
    'all' => count($orders),
    'requested' => count(array_filter($orders, fn($o) => ($o['refund_status'] ?? 'requested') === 'requested')),
    'approved' => count(array_filter($orders, fn($o) => ($o['refund_status'] ?? 'requested') === 'approved')),
    'processing' => count(array_filter($orders, fn($o) => ($o['refund_status'] ?? 'requested') === 'processing')),
    'completed' => count(array_filter($orders, fn($o) => ($o['refund_status'] ?? 'requested') === 'completed')),
    'rejected' => count(array_filter($orders, fn($o) => ($o['refund_status'] ?? 'requested') === 'rejected'))
];

// Helper function to get status badge classes
function getStatusBadgeClass($status) {
    $classes = [
        'requested' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'approved' => 'bg-blue-100 text-blue-800 border-blue-200',
        'processing' => 'bg-orange-100 text-orange-800 border-orange-200',
        'completed' => 'bg-green-100 text-green-800 border-green-200',
        'rejected' => 'bg-red-100 text-red-800 border-red-200'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
}

// Helper function to format currency
function formatCurrency($amount) {
    return '₱' . number_format(floatval($amount), 2);
}

// Helper function to format date
function formatDate($date, $format = 'M d, Y') {
    if (!$date || $date === '0000-00-00 00:00:00') return 'N/A';
    return date($format, strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return/Refund Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom scrollbar for modals */
        .modal-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .modal-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .modal-scroll::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }
        .modal-scroll::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Include navbar -->
    <?php if (file_exists('includes/navbar.php')): ?>
        <?php include 'includes/navbar.php'; ?>
    <?php endif; ?>

    <!-- Include sidebar -->
    <?php if (file_exists('includes/sidebar.php')): ?>
        <?php include 'includes/sidebar.php'; ?>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="<?= file_exists('includes/sidebar.php') ? 'ml-64' : '' ?> <?= file_exists('includes/navbar.php') ? 'pt-16' : '' ?>">
        <div class="p-6">
            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="bi bi-arrow-counterclockwise mr-3 text-blue-600"></i>
                    Return/Refund Management
                </h1>
                <p class="text-gray-600 mt-2">Manage customer return and refund requests</p>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 p-4 rounded-lg border <?= $_SESSION['message_type'] === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200' ?>" id="alertMessage">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="<?= $_SESSION['message_type'] === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle' ?> mr-2"></i>
                        <?= htmlspecialchars($_SESSION['message']) ?>
                    </div>
                    <button onclick="closeAlert()" class="text-current hover:opacity-70 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            endif; 
            ?>

            <!-- Status Filter Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 overflow-x-auto">
                        <?php 
                        $tabs = [
                            'all' => ['label' => 'All', 'color' => 'blue'],
                            'requested' => ['label' => 'Requested', 'color' => 'yellow'],
                            'approved' => ['label' => 'Approved', 'color' => 'blue'],
                            'processing' => ['label' => 'Processing', 'color' => 'orange'],
                            'completed' => ['label' => 'Completed', 'color' => 'green'],
                            'rejected' => ['label' => 'Rejected', 'color' => 'red']
                        ];
                        
                        foreach ($tabs as $key => $tab):
                            $isActive = $filter === $key;
                            $activeClass = $isActive ? "border-{$tab['color']}-500 text-{$tab['color']}-600" : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                        ?>
                        <a href="?status=<?= $key ?>" 
                           class="<?= $activeClass ?> whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                            <?= $tab['label'] ?> 
                            <span class="ml-1 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                                <?= $statusCounts[$key] ?>
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </div>

            <!-- Refunds Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <?php if(empty($filteredOrders)): ?>
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 text-gray-300">
                            <i class="fas fa-undo text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 mb-2">No refund requests found</h3>
                        <p class="text-gray-500">
                            <?= $filter === 'all' ? 'When customers request refunds, they\'ll appear here.' : 'No ' . $filter . ' refund requests at the moment.' ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach($filteredOrders as $order): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#<?= $order['id'] ?></div>
                                        <div class="text-sm text-gray-500"><?= formatDate($order['created_at']) ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 max-w-48 truncate">
                                            <?= htmlspecialchars($order['customer_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500 max-w-48 truncate">
                                            <?= htmlspecialchars($order['customer_email'] ?: 'No email') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= formatCurrency($order['refund_amount'] ?? $order['total_price']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php $status = $order['refund_status'] ?? 'requested'; ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border <?= getStatusBadgeClass($status) ?>">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= formatDate($order['refund_requested_date']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="openModal('modal<?= $order['id'] ?>')" 
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                            <i class="fas fa-eye mr-1"></i>
                                            View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Refund Detail Modals -->
    <?php foreach($orders as $order): ?>
    <div id="modal<?= $order['id'] ?>" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                        Refund Details - Order #<?= $order['id'] ?>
                    </h3>
                    <button onclick="closeModal('modal<?= $order['id'] ?>')" 
                            class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-scroll overflow-y-auto max-h-[calc(90vh-140px)]">
                <div class="px-6 py-4 space-y-6">
                    <!-- Status & Date -->
                    <div class="flex items-center justify-between">
                        <?php $status = $order['refund_status'] ?? 'requested'; ?>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full border <?= getStatusBadgeClass($status) ?>">
                            <?= ucfirst($status) ?>
                        </span>
                        <div class="text-sm text-gray-500">
                            Requested: <?= formatDate($order['refund_requested_date'], 'M d, Y H:i') ?>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">Customer Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Name:</span>
                                <span class="ml-2 text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Email:</span>
                                <span class="ml-2 text-gray-900"><?= htmlspecialchars($order['customer_email'] ?: 'N/A') ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Phone:</span>
                                <span class="ml-2 text-gray-900"><?= htmlspecialchars($order['customer_phone'] ?: 'N/A') ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Customer ID:</span>
                                <span class="ml-2 text-gray-900">#<?= $order['customer_id'] ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Refund Request Details -->
                    <?php 
                    $hasRefundReason = !empty($order['refund_reason']) || !empty($order['cancellation_reason']);
                    $hasPartialReason = !empty($order['partial_refund_reason']);
                    ?>
                    <?php if($hasRefundReason || $hasPartialReason): ?>
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <h4 class="font-medium text-yellow-900 mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Refund Request Details
                        </h4>
                        
                        <?php if($hasRefundReason): ?>
                        <div class="mb-3">
                            <span class="font-medium text-yellow-800">Customer's Reason:</span>
                            <div class="mt-2 p-3 bg-white rounded border border-yellow-300 text-gray-900">
                                <?php
                                $reason = $order['refund_reason'] ?: $order['cancellation_reason'] ?: 'No reason provided';
                                echo nl2br(htmlspecialchars($reason));
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($hasPartialReason): ?>
                        <div>
                            <span class="font-medium text-yellow-800">Partial Refund Details:</span>
                            <div class="mt-2 p-3 bg-white rounded border border-yellow-300 text-gray-900">
                                <?= nl2br(htmlspecialchars($order['partial_refund_reason'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Order Items -->
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Order Items</h4>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                <?php 
                                $totalAmount = 0;
                                foreach($order['products'] as $p): 
                                    $subtotal = $p['price'] * $p['quantity'];
                                    $totalAmount += $subtotal;
                                ?>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900"><?= htmlspecialchars($p['name']) ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-900"><?= $p['quantity'] ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-900"><?= formatCurrency($p['price']) ?></td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?= formatCurrency($subtotal) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Total:</td>
                                        <td class="px-4 py-3 text-sm font-bold text-gray-900"><?= formatCurrency($totalAmount) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Refund Information -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">Refund Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Refund Amount:</span>
                                <span class="ml-2 text-lg font-bold text-green-600"><?= formatCurrency($order['refund_amount'] ?? $totalAmount) ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Refund Method:</span>
                                <span class="ml-2 text-gray-900"><?= htmlspecialchars($order['refund_method'] ?: 'Same as payment method') ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="ml-2 text-gray-900"><?= htmlspecialchars($order['payment_method'] ?: 'N/A') ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Reference #:</span>
                                <span class="ml-2 text-gray-900"><?= htmlspecialchars($order['refund_reference'] ?: 'Not generated') ?></span>
                            </div>
                        </div>
                        
                        <?php if(!empty($order['refund_reason']) || !empty($order['cancellation_reason'])): ?>
                        <div class="mt-4">
                            <span class="font-medium text-gray-600">Refund Reason:</span>
                            <div class="mt-2 p-3 bg-white rounded border text-gray-900">
                                <?php
                                $reason = $order['refund_reason'] ?: $order['cancellation_reason'] ?: 'No reason provided';
                                echo nl2br(htmlspecialchars($reason));
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($order['partial_refund_reason'])): ?>
                        <div class="mt-4">
                            <span class="font-medium text-gray-600">Partial Refund Reason:</span>
                            <div class="mt-2 p-3 bg-white rounded border border-orange-200 text-gray-900">
                                <?= nl2br(htmlspecialchars($order['partial_refund_reason'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Rejection Information (if rejected) -->
                    <?php if($order['refund_status'] === 'rejected'): ?>
                    <div class="bg-red-50 rounded-lg p-4">
                        <h4 class="font-medium text-red-900 mb-3">
                            <i class="fas fa-times-circle mr-2"></i>Rejection Details
                        </h4>
                        <div class="space-y-3">
                            <?php if($order['rejection_date']): ?>
                            <div class="text-sm">
                                <span class="text-red-600 font-medium">Rejected on:</span>
                                <span class="ml-2 text-red-900"><?= formatDate($order['rejection_date'], 'M d, Y H:i') ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($order['rejection_reason'])): ?>
                            <div>
                                <span class="font-medium text-red-600">Rejection Reason:</span>
                                <div class="mt-2 p-3 bg-white rounded border border-red-200 text-red-900">
                                    <?= nl2br(htmlspecialchars($order['rejection_reason'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Processing Information -->
                    <?php if(in_array($order['refund_status'], ['processing', 'completed'])): ?>
                    <div class="bg-green-50 rounded-lg p-4">
                        <h4 class="font-medium text-green-900 mb-3">
                            <i class="fas fa-info-circle mr-2"></i>Processing Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <?php if(!empty($order['refund_processed_date'])): ?>
                            <div>
                                <span class="text-green-600 font-medium">Processed on:</span>
                                <span class="ml-2 text-green-900"><?= formatDate($order['refund_processed_date'], 'M d, Y H:i') ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($order['created_at'])): ?>
                            <div>
                                <span class="text-green-600 font-medium">Order created:</span>
                                <span class="ml-2 text-green-900"><?= formatDate($order['created_at'], 'M d, Y H:i') ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Admin Decision/Dispute Resolution -->
                    <?php if(!empty($order['refund_notes'])): ?>
                    <div class="bg-amber-50 rounded-lg p-4">
                        <h4 class="font-medium text-amber-900 mb-3">
                            <i class="fas fa-gavel mr-2"></i>Admin Decision / Dispute Resolution
                        </h4>
                        
                        <div class="p-3 bg-white rounded border border-amber-200">
                            <div class="text-sm text-amber-600 mb-1 font-medium">Administrator's Notes:</div>
                            <div class="text-gray-900 whitespace-pre-wrap"><?= htmlspecialchars($order['refund_notes']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Modal Footer with Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                <div class="flex flex-wrap justify-end gap-3">
                    <?php $currentStatus = $order['refund_status'] ?? 'requested'; ?>
                    
                    <?php if($currentStatus === 'requested'): ?>
                    <form method="post" class="inline" onsubmit="return confirm('Approve this refund request?')">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <i class="fas fa-check mr-1"></i>
                            Approve
                        </button>
                    </form>
                    
                    <button onclick="openRejectModal(<?= $order['id'] ?>)"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <i class="fas fa-times mr-1"></i>
                        Reject
                    </button>
                    
                    <?php elseif($currentStatus === 'approved'): ?>
                    <form method="post" class="inline" onsubmit="return confirm('Start processing this refund?')">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="action" value="process">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                            <i class="fas fa-cog mr-1"></i>
                            Start Processing
                        </button>
                    </form>
                    
                    <?php elseif($currentStatus === 'processing'): ?>
                    <form method="post" class="inline" onsubmit="return confirm('Mark this refund as completed?')">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="action" value="complete">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-check-circle mr-1"></i>
                            Mark Complete
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <button onclick="closeModal('modal<?= $order['id'] ?>')" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Rejection Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-[70] p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Reject Refund Request</h3>
            </div>
            <form id="rejectForm" method="post" onsubmit="return validateRejectForm()">
                <div class="px-6 py-4">
                    <input type="hidden" name="order_id" id="rejectOrderId">
                    <input type="hidden" name="action" value="reject">
                    
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Rejection Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea name="rejection_reason" 
                              id="rejection_reason" 
                              rows="4" 
                              required
                              maxlength="500"
                              placeholder="Please explain why this refund request is being rejected..."
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"></textarea>
                    <p class="mt-2 text-sm text-gray-500">This reason will be visible to the customer. (Max 500 characters)</p>
                    <p id="charCount" class="mt-1 text-xs text-gray-400">0/500</p>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeRejectModal()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            id="rejectSubmitBtn"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <i class="fas fa-times mr-1"></i>
                        Reject Refund
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal Functions
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Reject modal functions
    function openRejectModal(orderId) {
        // Close any open order modals first
        const openOrderModals = document.querySelectorAll('[id^="modal"]:not(.hidden)');
        openOrderModals.forEach(modal => {
            modal.classList.add('hidden');
        });
        
        document.getElementById('rejectOrderId').value = orderId;
        document.getElementById('rejection_reason').value = '';
        updateCharCount();
        document.getElementById('rejectModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus on textarea
        setTimeout(() => {
            document.getElementById('rejection_reason').focus();
        }, 100);
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.body.style.overflow = '';
        // Reset form
        document.getElementById('rejection_reason').value = '';
        updateCharCount();
    }

    // Validate reject form
    function validateRejectForm() {
        const reason = document.getElementById('rejection_reason').value.trim();
        if (reason.length < 10) {
            alert('Please provide a more detailed rejection reason (at least 10 characters).');
            return false;
        }
        return confirm('Are you sure you want to reject this refund request?');
    }

    // Character count for textarea
    function updateCharCount() {
        const textarea = document.getElementById('rejection_reason');
        const charCount = document.getElementById('charCount');
        const current = textarea.value.length;
        const max = 500;
        
        charCount.textContent = `${current}/${max}`;
        
        if (current > max * 0.9) {
            charCount.classList.add('text-red-500');
        } else {
            charCount.classList.remove('text-red-500');
        }
    }

    // Alert close function
    function closeAlert() {
        const alert = document.getElementById('alertMessage');
        if (alert) {
            alert.style.display = 'none';
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide alerts after 5 seconds
        const alert = document.getElementById('alertMessage');
        if (alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        }

        // Character count for rejection reason
        const textarea = document.getElementById('rejection_reason');
        if (textarea) {
            textarea.addEventListener('input', updateCharCount);
        }

        // Prevent double submission
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    setTimeout(() => {
                        submitBtn.disabled = false;
                    }, 2000);
                }
            });
        });
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const visibleModals = document.querySelectorAll('[id^="modal"]:not(.hidden), #rejectModal:not(.hidden)');
            visibleModals.forEach(modal => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            });
        }
    });

    // Close modal on backdrop click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('bg-gray-600') && e.target.classList.contains('bg-opacity-50')) {
            e.target.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });

    // Smooth scroll for modals
    function smoothScrollToTop(element) {
        element.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Add loading states for actions
    function showLoading(button, text = 'Processing...') {
        const originalText = button.innerHTML;
        button.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i>${text}`;
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }

    // Enhanced form submission with loading states
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                showLoading(submitBtn);
            }
        });
    });
    </script>

</body>
</html>