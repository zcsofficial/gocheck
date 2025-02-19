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
    <title>Profile Setup - GoCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => section.classList.add('hidden'));
            document.getElementById(sectionId).classList.remove('hidden');
        }
    </script>
</head>
<body class="bg-gray-900 text-white">
<div class="container mx-auto p-8">
    <h2 class="text-2xl font-bold mb-4">Complete Your Profile</h2>

    <form method="POST" enctype="multipart/form-data">
        <div id="profile-section" class="section">
            <h3 class="text-lg font-semibold mb-4">Profile Information</h3>
            <input type="file" name="profile_photo" class="block w-full p-2 mb-2 bg-gray-700 text-white">
            <input type="number" name="age" placeholder="Age" class="block w-full p-2 mb-2 bg-gray-700 text-white" min="0" max="120">
            <select name="gender" class="block w-full p-2 mb-2 bg-gray-700 text-white">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <input type="number" name="height" placeholder="Height" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white">
            <select name="height_unit" class="block w-full p-2 mb-2 bg-gray-700 text-white">
                <option value="cm">cm</option>
                <option value="ft">ft</option>
            </select>
            <input type="number" name="weight" placeholder="Weight" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white">
            <select name="weight_unit" class="block w-full p-2 mb-2 bg-gray-700 text-white">
                <option value="kg">kg</option>
                <option value="lbs">lbs</option>
            </select>
            <select name="blood_group" class="block w-full p-2 mb-2 bg-gray-700 text-white">
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
            </select>
            <button type="button" onclick="showSection('medical-section')" class="mt-4 bg-green-500 px-4 py-2 rounded">Next</button>
        </div>

        <div id="medical-section" class="section hidden">
            <h3 class="text-lg font-semibold mb-4">Medical History</h3>
            <input type="text" name="allergies" placeholder="Allergies (comma-separated)" class="block w-full p-2 mb-2 bg-gray-700 text-white">
            <textarea name="additional_info" placeholder="Additional Medical Information" class="block w-full p-2 mb-2 bg-gray-700 text-white" rows="4"></textarea>
            <button type="button" onclick="showSection('vital-section')" class="mt-4 bg-green-500 px-4 py-2 rounded">Next</button>
        </div>

        <div id="vital-section" class="section hidden">
            <h3 class="text-lg font-semibold mb-4">Vital Statistics</h3>
            <input type="number" name="systolic_bp" placeholder="Systolic Blood Pressure" class="block w-full p-2 mb-2 bg-gray-700 text-white" min="1">
            <input type="number" name="diastolic_bp" placeholder="Diastolic Blood Pressure" class="block w-full p-2 mb-2 bg-gray-700 text-white" min="1">
            <input type="number" name="hdl_cholesterol" placeholder="HDL Cholesterol" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white">
            <input type="number" name="ldl_cholesterol" placeholder="LDL Cholesterol" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white">
            <input type="number" name="fasting_blood_sugar" placeholder="Fasting Blood Sugar" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white">
            <input type="number" name="post_meal_blood_sugar" placeholder="Post-Meal Blood Sugar" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white">
            <button type="button" onclick="showSection('renal-section')" class="mt-4 bg-green-500 px-4 py-2 rounded">Next</button>
        </div>

        <div id="renal-section" class="section hidden">
            <h3 class="text-lg font-semibold mb-4">Renal Tests</h3>
            <input type="number" name="urea" placeholder="Urea Level" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white" min="7" max="20">
            <input type="number" name="creatinine" placeholder="Creatinine Level" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white" min="0.7" max="1.3">
            <input type="number" name="uric_acid" placeholder="Uric Acid Level" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white" min="3.5" max="7.2">
            <input type="number" name="calcium" placeholder="Calcium Level" step="0.01" class="block w-full p-2 mb-2 bg-gray-700 text-white" min="8.5" max="10.5">
            <button type="submit" class="mt-4 bg-blue-500 px-4 py-2 rounded">Submit</button>
        </div>
    </form>

    <?php if ($error): ?>
        <p class="text-red-500"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="text-green-500"><?php echo $success; ?></p>
    <?php endif; ?>
</div>
</body>
</html>