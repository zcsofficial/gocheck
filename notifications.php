<?php
session_start();
include 'config.php';  // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch notifications from database
$query = "SELECT id, message, date, read_status FROM notifications WHERE user_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications_result = $stmt->get_result();
$notifications = $notifications_result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - GoCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery for AJAX -->
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
        .read { text-decoration: line-through; color: #a0aec0; }
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
                        <a href="patients.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Patients</a>
                        <a href="reports.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Reports</a>
                        <a href="settings.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Settings</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4 flex-wrap">
                    <a href="notifications.php" class="text-white px-3 py-2 text-sm font-medium">Notifications</a>
                    <a href="profile.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Profile</a>
                    <a href="logout.php" class="text-secondary hover:text-white px-4 py-2 text-sm font-medium !rounded-button hover:bg-secondary/20">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-20 pb-8 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Notifications</h2>

                <?php if (empty($notifications)): ?>
                    <p class="text-gray-600">No notifications available.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="p-4 bg-gray-50 rounded-lg flex justify-between items-center" data-id="<?php echo $notification['id']; ?>">
                                <div>
                                    <p class="text-sm text-gray-900 <?php echo $notification['read_status'] ? 'read' : ''; ?>"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <p class="text-xs text-gray-500">Date: <?php echo date('F d, Y H:i', strtotime($notification['date'])); ?></p>
                                </div>
                                <div class="flex space-x-2">
                                    <?php if (!$notification['read_status']): ?>
                                        <button class="text-primary hover:text-primary/80" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                            <i class="ri-checkbox-circle-line"></i> Mark as Read
                                        </button>
                                    <?php endif; ?>
                                    <button class="text-red-500 hover:text-red-700" onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                        <i class="ri-delete-bin-line"></i> Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

        function markAsRead(notificationId) {
            $.ajax({
                url: 'update_notification.php',
                type: 'POST',
                data: { id: notificationId, action: 'read' },
                success: function(response) {
                    if (response.success) {
                        showToast('Notification marked as read!');
                        const notification = $(`div[data-id="${notificationId}"]`);
                        notification.find('.text-sm').addClass('read').removeClass('text-gray-900').addClass('text-gray-400');
                        notification.find('button:contains("Mark as Read")').remove();
                    } else {
                        showToast('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showToast('An error occurred: ' + error);
                }
            });
        }

        function deleteNotification(notificationId) {
            if (confirm('Are you sure you want to delete this notification?')) {
                $.ajax({
                    url: 'delete_notification.php',
                    type: 'POST',
                    data: { id: notificationId },
                    success: function(response) {
                        if (response.success) {
                            showToast('Notification deleted!');
                            $(`div[data-id="${notificationId}"]`).remove();
                        } else {
                            showToast('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('An error occurred: ' + error);
                    }
                });
            }
        }
    </script>
</body>
</html>