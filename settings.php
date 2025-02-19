<?php
session_start();
include 'config.php';
include 'alert.php';  // Database connection

// Check if user is not logged in
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
            nav { padding: 0.5rem; }
            .flex.justify-between.h-16 { height: auto; flex-wrap: wrap; justify-content: center; }
            .ml-10 { margin-left: 0; }
            .text-2xl { font-size: 1.5rem; }
            .text-sm { font-size: 0.875rem; }
            .p-6 { padding: 0.75rem; }
            .px-4 { padding-left: 0.75rem; padding-right: 0.75rem; }
            .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .space-y-6 { gap: 0.75rem; }
            .space-y-4 { gap: 0.5rem; }
            .w-full { width: 100%; }
            .space-x-4 { gap: 0.5rem; }
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
    <!-- Mobile-Friendly Navigation with Hamburger Menu -->
    <nav class="bg-primary fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-2 sm:px-4">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center flex-wrap">
                    <span class="text-white text-xl sm:text-2xl font-['Pacifico']">GoCheck</span>
                </div>
                <div class="flex items-center gap-2 sm:gap-4 md:hidden">
                    <button id="menuButton" class="text-white focus:outline-none">
                        <i class="ri-menu-line ri-xl sm:ri-2x"></i>
                    </button>
                </div>
                <div class="hidden md:flex items-center gap-2 sm:gap-4 flex-wrap">
                    <div class="flex items-center space-x-2 sm:space-x-4 flex-wrap">
                        <a href="dashboard.php" class="text-secondary hover:text-white px-2 sm:px-3 py-1 sm:py-2 text-sm font-medium">Dashboard</a>
                       
                        <a href="reports.php" class="text-secondary hover:text-white px-2 sm:px-3 py-1 sm:py-2 text-sm font-medium">Reports</a>
                        <a href="settings.php" class="text-white px-2 sm:px-3 py-1 sm:py-2 text-sm font-medium">Settings</a>
                    </div>
                    <div class="flex items-center space-x-2 sm:space-x-4 flex-wrap">
                        <a href="notifications.php" class="text-secondary hover:text-white px-2 sm:px-3 py-1 sm:py-2 text-sm font-medium">Notifications</a>
                        <a href="profile.php" class="text-secondary hover:text-white px-2 sm:px-3 py-1 sm:py-2 text-sm font-medium">Profile</a>
                        <a href="logout.php" class="!rounded-button bg-secondary/20 text-secondary hover:text-white px-2 sm:px-4 py-1 sm:py-2 text-sm font-medium hover:bg-secondary/30">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-primary text-white px-2 py-2">
            <a href="dashboard.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Dashboard</a>
             
            <a href="reports.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Reports</a>
            <a href="settings.php" class="block px-2 py-1 text-sm font-medium text-white">Settings</a>
            <a href="notifications.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Notifications</a>
            <a href="profile.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Profile</a>
            <a href="logout.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white !rounded-button bg-secondary/20 hover:bg-secondary/30">Logout</a>
        </div>
    </nav>

    <main class="pt-16 sm:pt-20 pb-6 sm:pb-8 px-2 sm:px-4">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">Settings</h2>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-2 sm:px-4 py-2 sm:py-3 rounded relative mb-2 sm:mb-4" role="alert">
                        <span class="block sm:inline text-xs sm:text-sm"><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-2 sm:px-4 py-2 sm:py-3 rounded relative mb-2 sm:mb-4" role="alert">
                        <span class="block sm:inline text-xs sm:text-sm"><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4 sm:space-y-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Notification Preferences</label>
                        <div class="space-y-2 sm:space-y-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?> class="w-4 h-4 rounded text-primary">
                                <span class="ml-1 sm:ml-2 text-xs sm:text-sm text-gray-900">Email Notifications</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="sms_notifications" <?php echo $settings['sms_notifications'] ? 'checked' : ''; ?> class="w-4 h-4 rounded text-primary">
                                <span class="ml-1 sm:ml-2 text-xs sm:text-sm text-gray-900">SMS Notifications</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Display Preferences</label>
                        <label class="flex items-center">
                            <input type="checkbox" name="dark_mode" <?php echo $settings['dark_mode'] ? 'checked' : ''; ?> class="w-4 h-4 rounded text-primary">
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm text-gray-900">Dark Mode</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Change Password</label>
                        <div class="space-y-2 sm:space-y-2">
                            <input type="password" name="current_password" placeholder="Current Password" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" required>
                            <input type="password" name="new_password" placeholder="New Password" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm">
                            <input type="password" name="confirm_password" placeholder="Confirm New Password" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-3 sm:px-6 py-1 sm:py-2 bg-primary text-white rounded-button hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 text-xs sm:text-sm">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div id="toast" class="fixed top-4 right-4 bg-white shadow-lg rounded-lg p-2 sm:p-4 hidden">
        <div class="flex items-center gap-1 sm:gap-2">
            <i class="ri-check-line text-green-500 text-sm sm:text-base"></i>
            <span id="toastMessage" class="text-xs sm:text-sm text-gray-700"></span>
        </div>
    </div>

    <script>
        // Toggle mobile menu
        document.getElementById('menuButton').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });

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