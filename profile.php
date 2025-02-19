<?php
session_start();
include 'config.php';  // Database connection

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Fetch user data
$query = "SELECT name, email, contact_number FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch profile data
$query = "SELECT profile_photo, age, gender, height, height_unit, weight, weight_unit, blood_group 
          FROM user_profiles WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile_result = $stmt->get_result();
$profile = $profile_result->fetch_assoc();

// Handle form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $height = $_POST['height'];
    $height_unit = $_POST['height_unit'];
    $weight = $_POST['weight'];
    $weight_unit = $_POST['weight_unit'];
    $blood_group = $_POST['blood_group'];

    // Profile Photo Upload
    $profile_photo = $profile['profile_photo'];
    if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
        $target_dir = "uploads/";
        $profile_photo = $target_dir . basename($_FILES["profile_photo"]["name"]);
        move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $profile_photo);
    }

    $conn->begin_transaction();

    try {
        // Update users table
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, contact_number = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $contact_number, $user_id);
        $stmt->execute();
        $stmt->close();

        // Update user_profiles table
        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, profile_photo, age, gender, height, height_unit, weight, weight_unit, blood_group) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE profile_photo = VALUES(profile_photo), 
                                                        age = VALUES(age), 
                                                        gender = VALUES(gender), 
                                                        height = VALUES(height), 
                                                        height_unit = VALUES(height_unit), 
                                                        weight = VALUES(weight), 
                                                        weight_unit = VALUES(weight_unit), 
                                                        blood_group = VALUES(blood_group)");
        $stmt->bind_param("isissssss", $user_id, $profile_photo, $age, $gender, $height, $height_unit, $weight, $weight_unit, $blood_group);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $success = "Profile updated successfully!";
        header("Location: profile.php?success=1");
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
    <title>Profile - GoCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
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
            .w-32 { width: 8rem; }
            .h-32 { height: 8rem; }
            .ri-3x { font-size: 1.5rem; }
            .space-y-6 { gap: 0.75rem; }
            .gap-8 { gap: 1rem; }
            .gap-6 { gap: 0.75rem; }
            .grid-cols-2 { grid-template-columns: 1fr; }
            .flex-1 { flex: none; width: 100%; }
            .w-20 { width: 5rem; }
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
                        <a href="settings.php" class="text-secondary hover:text-white px-2 sm:px-3 py-1 sm:py-2 text-sm font-medium">Settings</a>
                    </div>
                    <div class="flex items-center space-x-2 sm:space-x-4 flex-wrap">
                        <a href="notifications.php" class="text-secondary hover:text-white w-6 sm:w-8 h-6 sm:h-8 flex items-center justify-center">
                            <i class="ri-notification-3-line text-lg sm:text-xl"></i>
                        </a>
                        <a href="profile.php" class="text-white px-2 sm:px-3 py-1 sm:py-2 text-sm font-medium">Profile</a>
                        <a href="logout.php" class="!rounded-button bg-secondary/20 text-secondary hover:text-white px-2 sm:px-4 py-1 sm:py-2 text-sm font-medium hover:bg-secondary/30">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-primary text-white px-2 py-2">
            <a href="dashboard.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Dashboard</a>
            <a href="patients.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Patients</a>
            <a href="reports.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Reports</a>
            <a href="settings.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Settings</a>
            <a href="notifications.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white flex items-center">
                <i class="ri-notification-3-line mr-2 text-lg"></i> Notifications
            </a>
            <a href="profile.php" class="block px-2 py-1 text-sm font-medium text-white">Profile</a>
            <a href="logout.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white !rounded-button bg-secondary/20 hover:bg-secondary/30">Logout</a>
        </div>
    </nav>

    <main class="pt-16 sm:pt-20 pb-6 sm:pb-8 px-2 sm:px-4">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">My Profile</h2>

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

                <form method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-8">
                        <div class="flex flex-col items-center">
                            <div class="w-24 sm:w-32 h-24 sm:h-32 rounded-full bg-gray-100 flex items-center justify-center relative overflow-hidden mb-1 sm:mb-2">
                                <img id="previewImage" src="<?php echo $profile['profile_photo'] ?: 'https://public.readdy.ai/ai/img_res/9b4fe9c3650fdb605f69787c8e9898b5.jpg'; ?>" class="w-full h-full object-cover" alt="Profile Photo" style="<?php echo $profile['profile_photo'] ? '' : 'display: none;'; ?>">
                                <i class="ri-user-3-line text-gray-400 ri-2x sm:ri-3x <?php echo $profile['profile_photo'] ? 'hidden' : ''; ?>"></i>
                                <input type="file" name="profile_photo" id="profilePhoto" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                            </div>
                            <span class="text-xs sm:text-sm text-gray-600">Upload Photo</span>
                        </div>

                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-6 w-full">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Age</label>
                                <input type="number" name="age" value="<?php echo htmlspecialchars($profile['age'] ?? ''); ?>" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" min="0" max="120" required>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <select name="gender" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($profile['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($profile['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($profile['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Height</label>
                                <div class="flex gap-1 sm:gap-2 items-center">
                                    <input type="number" name="height" value="<?php echo htmlspecialchars($profile['height'] ?? ''); ?>" class="flex-1 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" step="0.01" required>
                                    <select name="height_unit" class="w-14 sm:w-20 px-1 sm:px-2 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" required>
                                        <option value="cm" <?php echo ($profile['height_unit'] == 'cm') ? 'selected' : ''; ?>>cm</option>
                                        <option value="ft" <?php echo ($profile['height_unit'] == 'ft') ? 'selected' : ''; ?>>ft</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Weight</label>
                                <div class="flex gap-1 sm:gap-2 items-center">
                                    <input type="number" name="weight" value="<?php echo htmlspecialchars($profile['weight'] ?? ''); ?>" class="flex-1 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" step="0.01" required>
                                    <select name="weight_unit" class="w-14 sm:w-20 px-1 sm:px-2 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" required>
                                        <option value="kg" <?php echo ($profile['weight_unit'] == 'kg') ? 'selected' : ''; ?>>kg</option>
                                        <option value="lbs" <?php echo ($profile['weight_unit'] == 'lbs') ? 'selected' : ''; ?>>lbs</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Blood Group</label>
                                <select name="blood_group" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" required>
                                    <option value="">Select Blood Group</option>
                                    <option value="A+" <?php echo ($profile['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                    <option value="A-" <?php echo ($profile['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                    <option value="B+" <?php echo ($profile['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                    <option value="B-" <?php echo ($profile['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                    <option value="O+" <?php echo ($profile['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                    <option value="O-" <?php echo ($profile['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                                    <option value="AB+" <?php echo ($profile['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                    <option value="AB-" <?php echo ($profile['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-3 sm:px-6 py-1 sm:py-2 bg-primary text-white rounded-button hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 text-xs sm:text-sm">Save Changes</button>
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

        const profilePhoto = document.getElementById('profilePhoto');
        const previewImage = document.getElementById('previewImage');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');

        function showToast(message) {
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        profilePhoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                    previewImage.classList.remove('hidden');
                    document.querySelector('.ri-user-3-line').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        // Show success toast if URL parameter indicates success
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') == 1) {
                showToast('Profile updated successfully!');
            }
        };
    </script>
</body>
</html>