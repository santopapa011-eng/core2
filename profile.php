<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
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
            max-width: 900px;
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

        .edit-btn {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px 16px;
            color: #4285f4;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .edit-btn:hover {
            background: #f8f9fa;
            border-color: #4285f4;
        }

        .profile-container {
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .profile-header {
            background: #f8f9fa;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4285f4, #1a73e8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            font-weight: bold;
        }

        .profile-info {
            flex: 1;
        }

        .user-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 5px;
        }

        .user-role {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            background: #cce7ff;
            color: #0056b3;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th {
            background: #f8f9fa;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #dee2e6;
            width: 25%;
        }

        .info-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table tr:hover {
            background-color: #f8f9fa;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            color: #6c757d;
        }

        .edit-form {
            display: none;
            padding: 20px;
        }

        .edit-form.active {
            display: block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            padding: 10px 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #4285f4;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #4285f4;
            color: white;
        }

        .btn-primary:hover {
            background: #1a73e8;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        .view-mode {
            display: block;
        }

        .view-mode.editing {
            display: none;
        }

        .actions-section {
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .actions-header {
            background: #f8f9fa;
            padding: 16px 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .actions-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
        }

        .actions-content {
            padding: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 10px 16px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            color: #495057;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: #e9ecef;
            border-color: #4285f4;
            color: #4285f4;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .action-buttons {
                flex-direction: column;
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
                    👤 User Profile
                </h1>
                <p class="header-subtitle">View and manage your account information</p>
            </div>
            <button class="edit-btn" onclick="toggleEdit()">
                ✏️ Edit Profile
            </button>
        </div>

        <!-- Profile Information -->
        <div class="profile-container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="avatar" id="userAvatar">JS</div>
                <div class="profile-info">
                    <div class="user-name" id="userName">John Smith</div>
                    <span class="user-role">
                        ⭐ Seller Account
                    </span>
                </div>
            </div>

            <!-- View Mode -->
            <div class="view-mode" id="viewMode">
                <table class="info-table">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Information</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="info-label">Email Address</span></td>
                            <td><span class="info-value" id="displayEmail">john.smith@example.com</span></td>
                            <td><span class="user-role">✅ Verified</span></td>
                            <td><span class="info-value">Sep 19, 2025</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-label">Phone Number</span></td>
                            <td><span class="info-value" id="displayPhone">+1 (555) 123-4567</span></td>
                            <td><span class="user-role">✅ Verified</span></td>
                            <td><span class="info-value">Sep 15, 2025</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-label">Store Name</span></td>
                            <td><span class="info-value" id="displayStore">Smith's Electronics</span></td>
                            <td><span class="user-role">⭐ Active</span></td>
                            <td><span class="info-value">Sep 10, 2025</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-label">Member Since</span></td>
                            <td><span class="info-value" id="displayJoined">March 15, 2024</span></td>
                            <td><span class="user-role">👑 Premium</span></td>
                            <td><span class="info-value">Mar 15, 2024</span></td>
                        </tr>
                        <tr>
                            <td><span class="info-label">Account Type</span></td>
                            <td><span class="info-value">Professional Seller</span></td>
                            <td><span class="user-role">⚡ Pro Plan</span></td>
                            <td><span class="info-value">Aug 20, 2025</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Edit Mode -->
            <div class="edit-form" id="editMode">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input" id="editName" value="John Smith">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" id="editEmail" value="john.smith@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-input" id="editPhone" value="+1 (555) 123-4567">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Store Name</label>
                        <input type="text" class="form-input" id="editStore" value="Smith's Electronics">
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-secondary" onclick="cancelEdit()">Cancel</button>
                    <button class="btn btn-primary" onclick="saveProfile()">Save Changes</button>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="actions-section">
            <div class="actions-header">
                <h3 class="actions-title">Account Actions</h3>
            </div>
            <div class="actions-content">
                <div class="action-buttons">
                    <button class="action-btn" onclick="changePassword()">🔒 Change Password</button>
                    <button class="action-btn" onclick="manageNotifications()">🔔 Notifications</button>
                    <button href="subcription.php"; class="action-btn">💳 Subscriptions</button>
                    <button class="action-btn" onclick="exportData()">📊 Export Data</button>
                    <button class="action-btn" onclick="accountSettings()">⚙️ Settings</button>
                    <button class="action-btn" onclick="supportTicket()">💬 Contact Support</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const userData = {
            name: "John Smith",
            email: "john.smith@example.com",
            phone: "+1 (555) 123-4567",
            storeName: "Smith's Electronics",
            joinDate: "March 15, 2024"
        };

        function initializeProfile() {
            document.getElementById('userName').textContent = userData.name;
            document.getElementById('displayEmail').textContent = userData.email;
            document.getElementById('displayPhone').textContent = userData.phone;
            document.getElementById('displayStore').textContent = userData.storeName;
            document.getElementById('displayJoined').textContent = userData.joinDate;
            
            // Update avatar with initials
            const initials = userData.name.split(' ').map(n => n[0]).join('');
            document.getElementById('userAvatar').textContent = initials;
        }

        function toggleEdit() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            const editBtn = document.querySelector('.edit-btn');

            if (viewMode.classList.contains('editing')) {
                // Switch to view mode
                viewMode.classList.remove('editing');
                editMode.classList.remove('active');
                editBtn.innerHTML = '✏️ Edit Profile';
            } else {
                // Switch to edit mode
                viewMode.classList.add('editing');
                editMode.classList.add('active');
                editBtn.innerHTML = '👁️ View Profile';
                
                // Populate edit form with current data
                document.getElementById('editName').value = userData.name;
                document.getElementById('editEmail').value = userData.email;
                document.getElementById('editPhone').value = userData.phone;
                document.getElementById('editStore').value = userData.storeName;
            }
        }

        function saveProfile() {
            // Update userData object
            userData.name = document.getElementById('editName').value;
            userData.email = document.getElementById('editEmail').value;
            userData.phone = document.getElementById('editPhone').value;
            userData.storeName = document.getElementById('editStore').value;

            // Update display
            initializeProfile();
            
            // Switch back to view mode
            toggleEdit();
            
            // Show success message
            alert('Profile updated successfully!');
        }

        function cancelEdit() {
            toggleEdit();
        }

        // Action button functions
        function changePassword() {
            alert('Change Password functionality would be implemented here');
        }

        function manageNotifications() {
            alert('Notification settings would be implemented here');
        }

        function viewSubscriptions() {
            alert('Subscription management would be implemented here');
        }

        function exportData() {
            alert('Data export functionality would be implemented here');
        }

        function accountSettings() {
            alert('Account settings would be implemented here');
        }

        function supportTicket() {
            alert('Support ticket system would be implemented here');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initializeProfile);
    </script>
</body>
</html>