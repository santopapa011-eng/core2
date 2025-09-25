<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Plans</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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

        .plan-id {
            color: #4285f4;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .plan-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .plan-icon.basic { background: linear-gradient(135deg, #28a745, #20c997); }
        .plan-icon.pro { background: linear-gradient(135deg, #4285f4, #1a73e8); }
        .plan-icon.enterprise { background: linear-gradient(135deg, #6f42c1, #8b5cf6); }

        .plan-name {
            font-weight: 600;
            font-size: 1rem;
            color: #212529;
        }

        .plan-description {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 2px;
        }

        .price {
            font-weight: 700;
            font-size: 1.1rem;
            color: #28a745;
        }

        .price-period {
            color: #6c757d;
            font-weight: normal;
            font-size: 0.85rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-popular {
            background: #cce7ff;
            color: #0056b3;
        }

        .status-premium {
            background: #e2d9f3;
            color: #5a2d7c;
        }

        .features-list {
            color: #6c757d;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .subscribe-btn {
            background: linear-gradient(135deg, #4285f4, #1a73e8);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .subscribe-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
        }

        .subscribe-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            transform: translateX(400px);
            transition: all 0.3s ease;
        }

        .notification.success {
            background: #28a745;
        }

        .notification.error {
            background: #dc3545;
        }

        .notification.show {
            transform: translateX(0);
        }

        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading.show {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4285f4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
            
            .table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Plan ID</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Features</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span class="plan-id">#BASIC</span>
                        </td>
                        <td>
                            <div class="plan-icon basic">🌟</div>
                        </td>
                        <td>
                            <div class="plan-name">Basic Plan</div>
                            <div class="plan-description">Get started with essentials</div>
                        </td>
                        <td>
                            <div class="features-list">
                                5 products auto-boosted 6hrs daily<br>
                                2,000 homepage impressions
                            </div>
                        </td>
                        <td>
                            <span class="price">0
                            </span>
                            <span class="price-period">/month</span>
                        </td>
                        <td>
                            <span class="status-badge status-active">✅ Active</span>
                        </td>
                        <td>
                            <button class="subscribe-btn" onclick="subscribe('BASIC', 'Basic Plan', 29.00)">
                                Subscribe
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="plan-id">#PRO</span>
                        </td>
                        <td>
                            <div class="plan-icon pro">⚡</div>
                        </td>
                        <td>
                            <div class="plan-name">Pro Plan</div>
                            <div class="plan-description">Scale your business reach</div>
                        </td>
                        <td>
                            <div class="features-list">
                                10 products boosted 12hrs daily<br>
                                5,000 impressions + banner slots
                            </div>
                        </td>
                        <td>
                            <span class="price">499.00</span>
                            <span class="price-period">/month</span>
                        </td>
                        <td>
                            <span class="status-badge status-popular">⭐ Popular</span>
                        </td>
                        <td>
                            <button class="subscribe-btn" onclick="subscribe('PRO', 'Pro Plan', 79.00)">
                                Subscribe
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="plan-id">#ENTERPRISE</span>
                        </td>
                        <td>
                            <div class="plan-icon enterprise">👑</div>
                        </td>
                        <td>
                            <div class="plan-name">Enterprise Plan</div>
                            <div class="plan-description">Maximum visibility & growth</div>
                        </td>
                        <td>
                            <div class="features-list">
                                20 products boosted 24/7<br>
                                15,000 impressions + priority placement
                            </div>
                        </td>
                        <td>
                            <span class="price">1999.00</span>
                            <span class="price-period">/month</span>
                        </td>
                        <td>
                            <span class="status-badge status-premium">👑 Premium</span>
                        </td>
                        <td>
                            <button class="subscribe-btn" onclick="subscribe('ENTERPRISE', 'Enterprise Plan', 199.00)">
                                Subscribe
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Loading overlay -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <!-- Notification -->
    <div class="notification" id="notification"></div>

    <script>
        function subscribe(planId, planName, price) {
            // Show loading
            document.getElementById('loading').classList.add('show');
            
            // Disable button
            event.target.disabled = true;
            event.target.textContent = 'Processing...';

            // Prepare data
            const subscriptionData = {
                plan_id: planId,
                plan_name: planName,
                price: price,
                user_email: 'user@example.com', // You can modify this to get actual user email
                user_name: 'Sample User' // You can modify this to get actual user name
            };

            // Send to backend
            fetch('subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(subscriptionData)
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading
                document.getElementById('loading').classList.remove('show');
                
                // Show notification
                showNotification(data.message, data.success ? 'success' : 'error');
                
                // Reset button
                event.target.disabled = false;
                event.target.textContent = data.success ? 'Subscribed!' : 'Subscribe';
                
                if (data.success) {
                    // Keep button as "Subscribed!" for 3 seconds
                    setTimeout(() => {
                        event.target.textContent = 'Subscribe';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Hide loading
                document.getElementById('loading').classList.remove('show');
                
                // Show error notification
                showNotification('Something went wrong. Please try again.', 'error');
                
                // Reset button
                event.target.disabled = false;
                event.target.textContent = 'Subscribe';
            });
        }

        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.classList.add('show');

            // Hide after 4 seconds
            setTimeout(() => {
                notification.classList.remove('show');
            }, 4000);
        }
    </script>
</body>
</html>