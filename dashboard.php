<?php
session_start();
include 'config.php';  // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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

// Fetch medical history
$query = "SELECT allergies, additional_info FROM medical_history WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$medical_result = $stmt->get_result();
$medical = $medical_result->fetch_assoc();

// Fetch vital statistics
$query = "SELECT systolic_bp, diastolic_bp, hdl_cholesterol, ldl_cholesterol, fasting_blood_sugar, post_meal_blood_sugar 
          FROM vital_statistics WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$vital_result = $stmt->get_result();
$vital = $vital_result->fetch_assoc();

// Fetch renal test results
$query = "SELECT urea, creatinine, uric_acid, calcium FROM renal_tests WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$renal_result = $stmt->get_result();
$renal = $renal_result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GoCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    <div class="max-w-4xl mx-auto my-10 p-6 bg-white shadow-lg rounded-lg">
        <h2 class="text-2xl font-bold text-center text-green-700">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>

        <!-- User Information -->
        <div class="mt-6">
            <h3 class="text-xl font-semibold text-gray-800">Basic Information</h3>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($user['contact_number']); ?></p>
        </div>

        <!-- Profile Section -->
        <div class="mt-6">
            <h3 class="text-xl font-semibold text-gray-800">Profile Details</h3>
            <div class="flex items-center">
                <img src="<?php echo $profile['profile_photo'] ?: 'default-profile.png'; ?>" alt="Profile Photo" class="w-20 h-20 rounded-full">
                <div class="ml-4">
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($profile['age'] ?? 'N/A'); ?></p>
                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($profile['gender'] ?? 'N/A'); ?></p>
                    <p><strong>Height:</strong> <?php echo htmlspecialchars($profile['height'] ?? 'N/A') . ' ' . $profile['height_unit']; ?></p>
                    <p><strong>Weight:</strong> <?php echo htmlspecialchars($profile['weight'] ?? 'N/A') . ' ' . $profile['weight_unit']; ?></p>
                    <p><strong>Blood Group:</strong> <?php echo htmlspecialchars($profile['blood_group'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>

        <!-- Medical History -->
        <div class="mt-6">
            <h3 class="text-xl font-semibold text-gray-800">Medical History</h3>
            <p><strong>Allergies:</strong> <?php echo htmlspecialchars($medical['allergies'] ?? 'None'); ?></p>
            <p><strong>Additional Info:</strong> <?php echo htmlspecialchars($medical['additional_info'] ?? 'N/A'); ?></p>
        </div>

        <!-- Vital Statistics -->
        <div class="mt-6">
            <h3 class="text-xl font-semibold text-gray-800">Vital Statistics</h3>
            <p><strong>Blood Pressure:</strong> <?php echo htmlspecialchars($vital['systolic_bp'] ?? 'N/A') . '/' . htmlspecialchars($vital['diastolic_bp'] ?? 'N/A'); ?> mmHg</p>
            <p><strong>HDL Cholesterol:</strong> <?php echo htmlspecialchars($vital['hdl_cholesterol'] ?? 'N/A'); ?> mg/dL</p>
            <p><strong>LDL Cholesterol:</strong> <?php echo htmlspecialchars($vital['ldl_cholesterol'] ?? 'N/A'); ?> mg/dL</p>
            <p><strong>Fasting Blood Sugar:</strong> <?php echo htmlspecialchars($vital['fasting_blood_sugar'] ?? 'N/A'); ?> mg/dL</p>
            <p><strong>Post Meal Blood Sugar:</strong> <?php echo htmlspecialchars($vital['post_meal_blood_sugar'] ?? 'N/A'); ?> mg/dL</p>
        </div>

        <!-- Renal Test Results -->
        <div class="mt-6">
            <h3 class="text-xl font-semibold text-gray-800">Renal Test Results</h3>
            <p><strong>Urea:</strong> <?php echo htmlspecialchars($renal['urea'] ?? 'N/A'); ?> mg/dL</p>
            <p><strong>Creatinine:</strong> <?php echo htmlspecialchars($renal['creatinine'] ?? 'N/A'); ?> mg/dL</p>
            <p><strong>Uric Acid:</strong> <?php echo htmlspecialchars($renal['uric_acid'] ?? 'N/A'); ?> mg/dL</p>
            <p><strong>Calcium:</strong> <?php echo htmlspecialchars($renal['calcium'] ?? 'N/A'); ?> mg/dL</p>
        </div>

        <div class="mt-6 text-center">
            <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Logout</a>
        </div>
    </div>

</body>
</html>
