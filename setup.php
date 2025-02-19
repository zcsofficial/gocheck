<?php
session_start();
include 'config.php'; // Database connection

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $height = $_POST['height'];
    $height_unit = $_POST['height_unit'];
    $weight = $_POST['weight'];
    $weight_unit = $_POST['weight_unit'];
    $blood_group = $_POST['blood_group'];
    $allergies = $_POST['allergies'];
    $additional_info = $_POST['additional_info'];
    $systolic_bp = $_POST['systolic_bp'];
    $diastolic_bp = $_POST['diastolic_bp'];
    $hdl_cholesterol = $_POST['hdl_cholesterol'];
    $ldl_cholesterol = $_POST['ldl_cholesterol'];
    $fasting_blood_sugar = $_POST['fasting_blood_sugar'];
    $post_meal_blood_sugar = $_POST['post_meal_blood_sugar'];
    $urea = $_POST['urea'];
    $creatinine = $_POST['creatinine'];
    $uric_acid = $_POST['uric_acid'];
    $calcium = $_POST['calcium'];

    // Profile Photo Upload
    $profile_photo = null;
    if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
        $target_dir = "uploads/";
        $profile_photo = $target_dir . basename($_FILES["profile_photo"]["name"]);
        move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $profile_photo);
    }

    $conn->begin_transaction();

    try {
        // Insert or update user_profiles
        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, profile_photo, age, gender, height, height_unit, weight, weight_unit, blood_group) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE profile_photo=VALUES(profile_photo), age=VALUES(age), gender=VALUES(gender), 
                                                        height=VALUES(height), height_unit=VALUES(height_unit), weight=VALUES(weight), 
                                                        weight_unit=VALUES(weight_unit), blood_group=VALUES(blood_group)");
        $stmt->bind_param("isissssss", $user_id, $profile_photo, $age, $gender, $height, $height_unit, $weight, $weight_unit, $blood_group);
        $stmt->execute();
        $stmt->close();

        // Insert or update medical_history
        $stmt = $conn->prepare("INSERT INTO medical_history (user_id, allergies, additional_info) 
                                VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE allergies=VALUES(allergies), additional_info=VALUES(additional_info)");
        $stmt->bind_param("iss", $user_id, $allergies, $additional_info);
        $stmt->execute();
        $stmt->close();

        // Insert or update vital_statistics
        $stmt = $conn->prepare("INSERT INTO vital_statistics (user_id, systolic_bp, diastolic_bp, hdl_cholesterol, ldl_cholesterol, fasting_blood_sugar, post_meal_blood_sugar) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE systolic_bp=VALUES(systolic_bp), diastolic_bp=VALUES(diastolic_bp), 
                                                        hdl_cholesterol=VALUES(hdl_cholesterol), ldl_cholesterol=VALUES(ldl_cholesterol), 
                                                        fasting_blood_sugar=VALUES(fasting_blood_sugar), post_meal_blood_sugar=VALUES(post_meal_blood_sugar)");
        $stmt->bind_param("iiiiiii", $user_id, $systolic_bp, $diastolic_bp, $hdl_cholesterol, $ldl_cholesterol, $fasting_blood_sugar, $post_meal_blood_sugar);
        $stmt->execute();
        $stmt->close();

        // Insert or update renal_tests
        $stmt = $conn->prepare("INSERT INTO renal_tests (user_id, urea, creatinine, uric_acid, calcium) 
                                VALUES (?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE urea=VALUES(urea), creatinine=VALUES(creatinine), uric_acid=VALUES(uric_acid), calcium=VALUES(calcium)");
        $stmt->bind_param("idddd", $user_id, $urea, $creatinine, $uric_acid, $calcium);
        $stmt->execute();
        $stmt->close();

        // Mark profile as completed
        $conn->query("UPDATE users SET profile_completed = 1 WHERE id = $user_id");

        $conn->commit();
        $success = "Profile updated successfully!";
        header("Location: dashboard.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Profile Setup - GoCheck</title>
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
        .hidden { display: none; }
        @media (max-width: 640px) {
            .text-2xl { font-size: 1.5rem; }
            .text-lg { font-size: 1rem; }
            .text-sm { font-size: 0.875rem; }
            .p-6 { padding: 1rem; }
            .px-4 { padding-left: 0.75rem; padding-right: 0.75rem; }
            .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .w-32 { width: 8rem; }
            .h-32 { height: 8rem; }
            .w-24 { width: 6rem; }
            .ri-3x { font-size: 1.5rem; }
            .space-y-8 { gap: 1rem; }
            .gap-8 { gap: 1rem; }
            .gap-6 { gap: 0.75rem; }
            .grid-cols-2 { grid-template-columns: 1fr; }
            .flex-1 { flex: none; width: 100%; }
            .w-48 { width: 12rem; }
            .h-2 { height: 0.375rem; }
            .px-6 { padding-left: 1rem; padding-right: 1rem; }
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
    <div class="max-w-5xl mx-auto px-2 sm:px-4 py-4 sm:py-8">
        <header class="mb-4 sm:mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Health Profile Setup</h1>
                <div class="flex items-center gap-1 sm:gap-2 mt-2 sm:mt-0">
                    <span class="text-xs sm:text-sm text-gray-600">Progress</span>
                    <div class="w-32 sm:w-48 h-1 sm:h-2 bg-gray-200 rounded-full">
                        <div class="w-0 h-full bg-primary rounded-full transition-all duration-500" id="progressBar"></div>
                    </div>
                </div>
            </div>
        </header>

        <form method="POST" enctype="multipart/form-data" id="healthProfileForm" class="space-y-4 sm:space-y-8">
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-2 sm:px-4 py-2 sm:py-3 rounded relative" role="alert">
                    <span class="block sm:inline text-xs sm:text-sm"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-2 sm:px-4 py-2 sm:py-3 rounded relative" role="alert">
                    <span class="block sm:inline text-xs sm:text-sm"><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <section class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Personal Information</h2>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-8">
                    <div class="flex flex-col items-center">
                        <div class="w-24 sm:w-32 h-24 sm:h-32 rounded-full bg-gray-100 flex items-center justify-center relative overflow-hidden mb-1 sm:mb-2">
                            <img id="previewImage" class="w-full h-full object-cover hidden">
                            <i class="ri-user-3-line text-gray-400 ri-2x sm:ri-3x"></i>
                            <input type="file" name="profile_photo" id="profilePhoto" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                        </div>
                        <span class="text-xs sm:text-sm text-gray-600">Upload Photo</span>
                    </div>
                    
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-6 w-full">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Age</label>
                            <input type="number" name="age" value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Enter age" min="0" max="120">
                        </div>
                        
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <div class="flex flex-wrap gap-2 sm:gap-4">
                                <label class="flex items-center">
                                    <input type="radio" name="gender" value="Male" class="w-3 sm:w-4 h-3 sm:h-4 text-primary" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'checked' : ''; ?>>
                                    <span class="ml-1 sm:ml-2 text-xs sm:text-sm">Male</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="gender" value="Female" class="w-3 sm:w-4 h-3 sm:h-4 text-primary" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'checked' : ''; ?>>
                                    <span class="ml-1 sm:ml-2 text-xs sm:text-sm">Female</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="gender" value="Other" class="w-3 sm:w-4 h-3 sm:h-4 text-primary" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'checked' : ''; ?>>
                                    <span class="ml-1 sm:ml-2 text-xs sm:text-sm">Other</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Height</label>
                            <div class="flex gap-1 sm:gap-2 items-center">
                                <input type="number" name="height" value="<?php echo isset($_POST['height']) ? htmlspecialchars($_POST['height']) : ''; ?>" class="flex-1 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Height" step="0.01">
                                <select name="height_unit" class="w-16 sm:w-20 px-1 sm:px-2 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm">
                                    <option value="cm" <?php echo (isset($_POST['height_unit']) && $_POST['height_unit'] == 'cm') ? 'selected' : ''; ?>>cm</option>
                                    <option value="ft" <?php echo (isset($_POST['height_unit']) && $_POST['height_unit'] == 'ft') ? 'selected' : ''; ?>>ft</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Weight</label>
                            <div class="flex gap-1 sm:gap-2 items-center">
                                <input type="number" name="weight" value="<?php echo isset($_POST['weight']) ? htmlspecialchars($_POST['weight']) : ''; ?>" class="flex-1 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Weight" step="0.01">
                                <select name="weight_unit" class="w-16 sm:w-20 px-1 sm:px-2 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm">
                                    <option value="kg" <?php echo (isset($_POST['weight_unit']) && $_POST['weight_unit'] == 'kg') ? 'selected' : ''; ?>>kg</option>
                                    <option value="lbs" <?php echo (isset($_POST['weight_unit']) && $_POST['weight_unit'] == 'lbs') ? 'selected' : ''; ?>>lbs</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Blood Group</label>
                            <select name="blood_group" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm">
                                <option value="">Select blood group</option>
                                <option value="A+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="O+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                                <option value="AB+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Medical History</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Allergies</label>
                        <div class="relative">
                            <input type="text" name="allergies" id="allergyInput" value="<?php echo isset($_POST['allergies']) ? htmlspecialchars($_POST['allergies']) : ''; ?>" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Type to add allergies">
                            <div id="allergyTags" class="flex flex-wrap gap-1 sm:gap-2 mt-1 sm:mt-2"></div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Additional Medical Information</label>
                        <textarea name="additional_info" class="w-full px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" rows="2" placeholder="Enter any additional medical information"><?php echo isset($_POST['additional_info']) ? htmlspecialchars($_POST['additional_info']) : ''; ?></textarea>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Vital Statistics</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Blood Pressure</label>
                        <div class="flex gap-1 sm:gap-2 items-center">
                            <input type="number" name="systolic_bp" value="<?php echo isset($_POST['systolic_bp']) ? htmlspecialchars($_POST['systolic_bp']) : ''; ?>" class="w-16 sm:w-24 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Systolic" min="1">
                            <span class="text-gray-500">/</span>
                            <input type="number" name="diastolic_bp" value="<?php echo isset($_POST['diastolic_bp']) ? htmlspecialchars($_POST['diastolic_bp']) : ''; ?>" class="w-16 sm:w-24 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Diastolic" min="1">
                            <span class="text-xs sm:text-sm text-gray-500">mmHg</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Cholesterol</label>
                        <div class="flex gap-1 sm:gap-2 items-center">
                            <input type="number" name="hdl_cholesterol" value="<?php echo isset($_POST['hdl_cholesterol']) ? htmlspecialchars($_POST['hdl_cholesterol']) : ''; ?>" class="w-16 sm:w-24 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="HDL" step="0.01">
                            <span class="text-gray-500">/</span>
                            <input type="number" name="ldl_cholesterol" value="<?php echo isset($_POST['ldl_cholesterol']) ? htmlspecialchars($_POST['ldl_cholesterol']) : ''; ?>" class="w-16 sm:w-32 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="LDL" step="0.01">
                            <span class="text-xs sm:text-sm text-gray-500">mg/dL</span>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Blood Sugar</label>
                        <div class="flex gap-1 sm:gap-2 items-center">
                            <input type="number" name="fasting_blood_sugar" value="<?php echo isset($_POST['fasting_blood_sugar']) ? htmlspecialchars($_POST['fasting_blood_sugar']) : ''; ?>" class="w-16 sm:w-24 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Fasting" step="0.01">
                            <span class="text-gray-500">/</span>
                            <input type="number" name="post_meal_blood_sugar" value="<?php echo isset($_POST['post_meal_blood_sugar']) ? htmlspecialchars($_POST['post_meal_blood_sugar']) : ''; ?>" class="w-16 sm:w-24 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="PP" step="0.01">
                            <span class="text-xs sm:text-sm text-gray-500">mg/dL</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Renal Function Tests</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-6">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Urea</label>
                        <div class="flex gap-1 sm:gap-2 items-center">
                            <input type="number" name="urea" value="<?php echo isset($_POST['urea']) ? htmlspecialchars($_POST['urea']) : ''; ?>" class="flex-1 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Enter value" step="0.01" min="7" max="20">
                            <span class="text-xs sm:text-sm text-gray-500 whitespace-nowrap">mg/dL</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Creatinine</label>
                        <div class="flex gap-1 sm:gap-2 items-center">
                            <input type="number" name="creatinine" value="<?php echo isset($_POST['creatinine']) ? htmlspecialchars($_POST['creatinine']) : ''; ?>" class="flex-1 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Enter value" step="0.01" min="0.7" max="1.3">
                            <span class="text-xs sm:text-sm text-gray-500 whitespace-nowrap">mg/dL</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Uric Acid</label>
                        <div class="flex gap-1 sm:gap-2 items-center">
                            <input type="number" name="uric_acid" value="<?php echo isset($_POST['uric_acid']) ? htmlspecialchars($_POST['uric_acid']) : ''; ?>" class="flex-1 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Enter value" step="0.01" min="3.5" max="7.2">
                            <span class="text-xs sm:text-sm text-gray-500 whitespace-nowrap">mg/dL</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Calcium</label>
                        <div class="flex gap-1 sm:gap-2 items-center">
                            <input type="number" name="calcium" value="<?php echo isset($_POST['calcium']) ? htmlspecialchars($_POST['calcium']) : ''; ?>" class="flex-1 px-2 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-xs sm:text-sm" placeholder="Enter value" step="0.01" min="8.5" max="10.5">
                            <span class="text-xs sm:text-sm text-gray-500 whitespace-nowrap">mg/dL</span>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-col sm:flex-row justify-between items-center gap-2 sm:gap-4">
                <button type="button" class="w-full sm:w-auto px-3 sm:px-6 py-1 sm:py-2 bg-white border border-gray-300 text-gray-700 rounded-button hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 text-xs sm:text-sm">Save as Draft</button>
                <button type="submit" class="w-full sm:w-auto px-3 sm:px-6 py-1 sm:py-2 bg-primary text-white rounded-button hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 text-xs sm:text-sm">Save & Continue</button>
            </div>
        </form>
    </div>

    <div id="toast" class="fixed top-4 right-4 bg-white shadow-lg rounded-lg p-2 sm:p-4 hidden">
        <div class="flex items-center gap-1 sm:gap-2">
            <i class="ri-check-line text-green-500 text-sm sm:text-base"></i>
            <span id="toastMessage" class="text-xs sm:text-sm text-gray-700"></span>
        </div>
    </div>

    <script>
        const profilePhoto = document.getElementById('profilePhoto');
        const previewImage = document.getElementById('previewImage');
        const allergyInput = document.getElementById('allergyInput');
        const allergyTags = document.getElementById('allergyTags');
        const form = document.getElementById('healthProfileForm');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const progressBar = document.getElementById('progressBar');

        let allergies = new Set();

        profilePhoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        allergyInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                e.preventDefault();
                const allergy = this.value.trim();
                if (!allergies.has(allergy)) {
                    allergies.add(allergy);
                    const tag = document.createElement('span');
                    tag.className = 'px-1 sm:px-2 py-0.5 sm:py-1 bg-primary/10 text-primary rounded-full text-xs sm:text-sm flex items-center gap-0.5 sm:gap-1';
                    tag.innerHTML = `${allergy}<button type="button" class="hover:text-primary/80"><i class="ri-close-line"></i></button>`;
                    tag.querySelector('button').onclick = function() {
                        allergies.delete(allergy);
                        tag.remove();
                    };
                    allergyTags.appendChild(tag);
                }
                this.value = '';
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Collect allergies into a single string
            const allergyArray = Array.from(allergies);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'allergies';
            hiddenInput.value = allergyArray.join(', ');
            this.appendChild(hiddenInput);

            this.submit();
        });

        function showToast(message) {
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        function updateProgress() {
            const inputs = form.querySelectorAll('input, select, textarea');
            let filled = 0;
            inputs.forEach(input => {
                if (input.value.trim() || (input.type === 'radio' && input.checked)) filled++;
            });
            const progress = (filled / inputs.length) * 100;
            progressBar.style.width = `${progress}%`;
        }

        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('change', updateProgress);
        });

        // Initial progress update
        updateProgress();
    </script>
</body>
</html>