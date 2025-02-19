<?php
session_start();
include 'config.php';
include 'alert.php';  // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Mock settings (replace with actual database query if you have a settings table)
$settings = [
    'email_notifications' => true,
    'sms_notifications' => false,
    'dark_mode' => false
];

// Handle form submission for settings update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
    $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $conn->begin_transaction();

    try {
        // Verify current password (simulated)
        $query = "SELECT password_hash FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();

        if (!password_verify($current_password, $user_data['password_hash'])) {
            throw new Exception("Current password is incorrect.");
        }

        if ($new_password && $new_password !== $confirm_password) {
            throw new Exception("New passwords do not match.");
        }

        // Update settings (simulated, replace with actual table)
        // For this example, we'll just update session variables or mock data
        $_SESSION['settings'] = [
            'email_notifications' => $email_notifications,
            'sms_notifications' => $sms_notifications,
            'dark_mode' => $dark_mode
        ];

        if ($new_password) {
            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password_hash, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        $success = "Settings updated successfully!";
        header("Location: settings.php?success=1");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - GoCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 50;
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            max-width: 90%;
            width: 28rem;
            max-height: 80vh;
            overflow-y: auto;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            transform: translate(-50%, -50%);
            left: 50%;
            top: 50%;
        }
        @media (max-width: 640px) {
            .grid-cols-12 { grid-template-columns: 1fr; }
            .col-span-3, .col-span-9 { grid-column: span 12; }
            nav { padding: 0.5rem; }
            .flex.justify-between.h-16 { height: auto; flex-wrap: wrap; justify-content: center; }
            .ml-10 { margin-left: 0; }
        }
        @media (min-width: 641px) and (max-width: 1024px) {
            .grid-cols-12 { grid-template-columns: repeat(6, 1fr); }
            .col-span-3 { grid-column: span 6; }
            .col-span-9 { grid-column: span 6; }
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2F4F2F',
                        secondary: '#C3B091'
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-primary fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center flex-wrap">
                    <span class="text-white text-2xl font-['Pacifico']">GoCheck</span>
                    <div class="ml-10 flex items-center space-x-4 flex-wrap">
                        <a href="dashboard.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Dashboard</a>
                        
                        <a href="reports.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Reports</a>
                        <a href="settings.php" class="text-white px-3 py-2 text-sm font-medium">Settings</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4 flex-wrap">
                    <a href="notifications.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Notifications</a>
                    <a href="profile.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Profile</a>
                    <a href="logout.php" class="text-secondary hover:text-white px-4 py-2 text-sm font-medium !rounded-button hover:bg-secondary/20">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-20 pb-8 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Settings</h2>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notification Preferences</label>
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?> class="rounded text-primary">
                                <span class="ml-2 text-sm text-gray-900">Email Notifications</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="sms_notifications" <?php echo $settings['sms_notifications'] ? 'checked' : ''; ?> class="rounded text-primary">
                                <span class="ml-2 text-sm text-gray-900">SMS Notifications</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Preferences</label>
                        <label class="flex items-center">
                            <input type="checkbox" name="dark_mode" <?php echo $settings['dark_mode'] ? 'checked' : ''; ?> class="rounded text-primary">
                            <span class="ml-2 text-sm text-gray-900">Dark Mode</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Change Password</label>
                        <div class="space-y-2">
                            <input type="password" name="current_password" placeholder="Current Password" class="w-full px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required>
                            <input type="password" name="new_password" placeholder="New Password" class="w-full px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                            <input type="password" name="confirm_password" placeholder="Confirm New Password" class="w-full px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-button hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div id="toast" class="fixed top-4 right-4 bg-white shadow-lg rounded-lg p-4 hidden">
        <div class="flex items-center gap-2">
            <i class="ri-check-line text-green-500"></i>
            <span id="toastMessage" class="text-sm text-gray-700"></span>
        </div>
    </div>

    <script>
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');

        function showToast(message) {
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        // Show success toast if URL parameter indicates success
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') == 1) {
                showToast('Settings updated successfully!');
            }
        };
    </script>
</body>
</html>