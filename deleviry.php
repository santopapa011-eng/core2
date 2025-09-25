<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Coordination</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-left {
            flex: 1;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.75rem;
            font-weight: 600;
            color: #4285f4;
            margin-bottom: 5px;
        }

        .header-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #4285f4;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #1a73e8;
        }

        .btn-secondary {
            background: #ffffff;
            border: 1px solid #dee2e6;
            color: #4285f4;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #4285f4;
        }

        .filter-tabs {
            display: flex;
            gap: 2px;
            margin-bottom: 20px;
            background: #ffffff;
            border-radius: 8px;
            padding: 4px;
            border: 1px solid #dee2e6;
            width: fit-content;
        }

        .filter-tab {
            padding: 8px 16px;
            border: none;
            background: transparent;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-tab.active {
            background: #4285f4;
            color: white;
        }

        .filter-tab:not(.active):hover {
            background: #f8f9fa;
            color: #495057;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: #f8f9fa;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #dee2e6;
        }

        .table td {
            padding: 16px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover {
            background-color: #f8f9fa;
        }

        .order-id {
            color: #4285f4;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .logistics-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            font-weight: 600;
        }

        .logistics-jnt { background: linear-gradient(135deg, #ff6b35, #f7931e); }
        .logistics-lbc { background: linear-gradient(135deg, #dc3545, #c82333); }
        .logistics-ninja { background: linear-gradient(135deg, #6f42c1, #8b5cf6); }
        .logistics-flash { background: linear-gradient(135deg, #28a745, #20c997); }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-pickup { background: #cce7ff; color: #0056b3; }
        .status-transit { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .price {
            font-weight: 700;
            font-size: 1rem;
            color: #28a745;
        }

        .date-text {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .track-btn { background: #4285f4; color: white; }
        .track-btn:hover { background: #1a73e8; }

        .print-btn { background: #28a745; color: white; }
        .print-btn:hover { background: #218838; }

        .edit-btn { background: #ffc107; color: #212529; }
        .edit-btn:hover { background: #e0a800; }

        .delete-btn { background: #dc3545; color: white; }
        .delete-btn:hover { background: #c82333; }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .quick-action-card {
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 20px;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .quick-action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #4285f4;
        }

        .quick-action-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        .quick-action-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .quick-action-desc {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #495057;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.2s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #4285f4;
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
            
            .table {
                min-width: 1000px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: stretch;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1 class="header-title">
                    🚚 Logistics Coordination
                </h1>
                <p class="header-subtitle">Manage deliveries, track shipments, and coordinate with logistics partners</p>
            </div>
            <div class="action-buttons">
                <button class="btn btn-secondary" onclick="refreshDeliveries()">
                    🔄 Refresh
                </button>
                <button class="btn btn-primary" onclick="showBookPickupModal()">
                    📦 Book Pickup
                </button>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="quick-action-card" onclick="showBookPickupModal()">
                <span class="quick-action-icon">📦</span>
                <div class="quick-action-title">Book Pickup</div>
                <div class="quick-action-desc">Schedule pickup for your orders</div>
            </div>
            <div class="quick-action-card" onclick="showPrintLabelsModal()">
                <span class="quick-action-icon">🏷️</span>
                <div class="quick-action-title">Print Labels</div>
                <div class="quick-action-desc">Generate shipping labels</div>
            </div>
            <div class="quick-action-card" onclick="showTrackingModal()">
                <span class="quick-action-icon">📍</span>
                <div class="quick-action-title">Track Deliveries</div>
                <div class="quick-action-desc">Monitor shipment status</div>
            </div>
            <div class="quick-action-card" onclick="showLogisticsModal()">
                <span class="quick-action-icon">🏢</span>
                <div class="quick-action-title">Logistics Partners</div>
                <div class="quick-action-desc">Manage service providers</div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterDeliveries('all')">
                📋 All Deliveries
            </button>
            <button class="filter-tab" onclick="filterDeliveries('pending')">
                ⏳ Pending Pickup
            </button>
            <button class="filter-tab" onclick="filterDeliveries('transit')">
                🚛 In Transit
            </button>
            <button class="filter-tab" onclick="filterDeliveries('delivered')">
                ✅ Delivered
            </button>
        </div>

        <!-- Deliveries Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Logistics Partner</th>
                        <th>Customer</th>
                        <th>Destination</th>
                        <th>Service</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Pickup Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="deliveriesTable">
                    <tr data-status="pending">
                        <td><span class="order-id">#ORD-001</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="logistics-icon logistics-jnt">J&T</div>
                                <div>
                                    <div style="font-weight: 600;">J&T Express</div>
                                    <div style="font-size: 0.8rem; color: #6c757d;">Standard Delivery</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div style="font-weight: 600;">Maria Santos</div>
                                <div style="font-size: 0.8rem; color: #6c757d;">maria.santos@email.com</div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; line-height: 1.4;">
                                Quezon City, Metro Manila<br>
                                <span style="color: #6c757d;">1100 Philippines</span>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">
                                Same Day Delivery<br>
                                <span style="color: #6c757d;">2-4 hours</span>
                            </div>
                        </td>
                        <td><span class="price">₱85.00</span></td>
                        <td><span class="status-badge status-pending">⏳ Pending Pickup</span></td>
                        <td>
                            <div class="date-text">
                                Sep 23, 2025<br>
                                02:00 PM
                            </div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn track-btn" onclick="trackDelivery('ORD-001')" title="Track">🔍</button>
                                <button class="action-btn print-btn" onclick="printLabel('ORD-001')" title="Print Label">🖨️</button>
                                <button class="action-btn edit-btn" onclick="editDelivery('ORD-001')" title="Edit">✏️</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-status="transit">
                        <td><span class="order-id">#ORD-002</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="logistics-icon logistics-lbc">LBC</div>
                                <div>
                                    <div style="font-weight: 600;">LBC Express</div>
                                    <div style="font-size: 0.8rem; color: #6c757d;">Next Day Delivery</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div style="font-weight: 600;">Juan Cruz</div>
                                <div style="font-size: 0.8rem; color: #6c757d;">juan.cruz@email.com</div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; line-height: 1.4;">
                                Makati City, Metro Manila<br>
                                <span style="color: #6c757d;">1200 Philippines</span>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">
                                Express Delivery<br>
                                <span style="color: #6c757d;">Next day</span>
                            </div>
                        </td>
                        <td><span class="price">₱120.00</span></td>
                        <td><span class="status-badge status-transit">🚛 In Transit</span></td>
                        <td>
                            <div class="date-text">
                                Sep 22, 2025<br>
                                10:30 AM
                            </div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn track-btn" onclick="trackDelivery('ORD-002')" title="Track">🔍</button>
                                <button class="action-btn print-btn" onclick="printLabel('ORD-002')" title="Print Label">🖨️</button>
                            </div>
                        </td>
                    </tr>
                    <tr data-status="delivered">
                        <td><span class="order-id">#ORD-003</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="logistics-icon logistics-ninja">NV</div>
                                <div>
                                    <div style="font-weight: 600;">Ninja Van</div>
                                    <div style="font-size: 0.8rem; color: #6c757d;">Standard Delivery</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div style="font-weight: 600;">Ana Reyes</div>
                                <div style="font-size: 0.8rem; color: #6c757d;">ana.reyes@email.com</div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; line-height: 1.4;">
                                Pasig City, Metro Manila<br>
                                <span style="color: #6c757d;">1600 Philippines</span>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">
                                Standard Delivery<br>
                                <span style="color: #6c757d;">3-5 days</span>
                            </div>
                        </td>
                        <td><span class="price">₱65.00</span></td>
                        <td><span class="status-badge status-delivered">✅ Delivered</span></td>
                        <td>
                            <div class="date-text">
                                Sep 20, 2025<br>
                                09:15 AM
                            </div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn track-btn" onclick="trackDelivery('ORD-003')" title="Track">🔍</button>
                                <button class="action-btn print-btn" onclick="printLabel('ORD-003')" title="Print Label">🖨️</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Book Pickup Modal -->
    <div class="modal" id="bookPickupModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">📦 Book Pickup</h3>
                <button class="close-btn" onclick="closeModal('bookPickupModal')">&times;</button>
            </div>
            <form id="pickupForm">
                <div class="form-group">
                    <label class="form-label">Order ID</label>
                    <input type="text" class="form-input" id="pickupOrderId" placeholder="Enter order ID" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Logistics Partner</label>
                    <select class="form-select" id="pickupLogistics" required>
                        <option value="">Select logistics partner</option>
                        <option value="jnt">J&T Express</option>
                        <option value="lbc">LBC Express</option>
                        <option value="ninja">Ninja Van</option>
                        <option value="flash">Flash Express</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Service Type</label>
                    <select class="form-select" id="pickupService" required>
                        <option value="">Select service</option>
                        <option value="standard">Standard Delivery (3-5 days)</option>
                        <option value="express">Express Delivery (Next day)</option>
                        <option value="sameday">Same Day Delivery</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pickup Date & Time</label>
                    <input type="datetime-local" class="form-input" id="pickupDateTime" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Special Instructions</label>
                    <textarea class="form-textarea" id="pickupInstructions" placeholder="Any special pickup instructions..."></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('bookPickupModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Book Pickup</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Filter deliveries by status
        function filterDeliveries(status) {
            const tabs = document.querySelectorAll('.filter-tab');
            const rows = document.querySelectorAll('#deliveriesTable tr');

            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');

            rows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Modal functions
        function showBookPickupModal() {
            document.getElementById('bookPickupModal').classList.add('show');
        }

        function showPrintLabelsModal() {
            alert('Print Labels modal would be implemented here');
        }

        function showTrackingModal() {
            alert('Tracking modal would be implemented here');
        }

        function showLogisticsModal() {
            alert('Logistics Partners management modal would be implemented here');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Action functions
        function trackDelivery(orderId) {
            alert(`Tracking delivery for order ${orderId}`);
        }

        function printLabel(orderId) {
            alert(`Printing shipping label for order ${orderId}`);
        }

        function editDelivery(orderId) {
            alert(`Editing delivery for order ${orderId}`);
        }

        function refreshDeliveries() {
            alert('Refreshing deliveries...');
            // Add loading animation here
        }

        // Form submission
        document.getElementById('pickupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                orderId: document.getElementById('pickupOrderId').value,
                logistics: document.getElementById('pickupLogistics').value,
                service: document.getElementById('pickupService').value,
                dateTime: document.getElementById('pickupDateTime').value,
                instructions: document.getElementById('pickupInstructions').value
            };

            console.log('Booking pickup:', formData);
            alert('Pickup booked successfully!');
            closeModal('bookPickupModal');
            
            // Reset form
            this.reset();
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                }
            });
        });

        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const dateTimeInput = document.getElementById('pickupDateTime');
            dateTimeInput.min = now.toISOString().slice(0, 16);
        });
    </script>
</body>
</html>