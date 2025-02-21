<?php
session_start();
include 'config.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Enable detailed error logging
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_log("Starting exercise.php for user_id: $user_id");

// Fetch user data
$query = "SELECT name, email, contact_number FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($user_result) ?? [];

// Fetch profile data including profile_photo
$query = "SELECT profile_photo, age, gender, height, height_unit, weight, weight_unit FROM user_profiles WHERE user_id = '$user_id'";
$profile_result = mysqli_query($conn, $query);
$profile = mysqli_fetch_assoc($profile_result) ?? [];

// Set profile picture with fallback
$profile_photo = !empty($profile['profile_photo']) && file_exists($profile['profile_photo']) 
    ? htmlspecialchars($profile['profile_photo']) 
    : 'https://public.readdy.ai/ai/img_res/9b4fe9c3650fdb605f69787c8e9898b5.jpg';

// Fetch vital statistics
$query = "SELECT systolic_bp, diastolic_bp FROM vital_statistics WHERE user_id = '$user_id'";
$vital_result = mysqli_query($conn, $query);
$vital = mysqli_fetch_assoc($vital_result) ?? [];

// Fetch renal test results
$query = "SELECT urea, creatinine, uric_acid, calcium FROM renal_tests WHERE user_id = '$user_id'";
$renal_result = mysqli_query($conn, $query);
$renal = mysqli_fetch_assoc($renal_result) ?? [];

// Calculate BMI for additional context
$bmi = null;
if ($profile['height'] && $profile['weight'] && $profile['height_unit'] === 'cm' && $profile['weight_unit'] === 'kg') {
    $height_m = $profile['height'] / 100;
    $bmi = round($profile['weight'] / ($height_m * $height_m), 1);
}

// Prepare health data summary for API
$health_summary = "User Health Profile:\n";
$health_summary .= "- Age: " . ($profile['age'] ?? 'N/A') . " years\n";
$health_summary .= "- Gender: " . ($profile['gender'] ?? 'N/A') . "\n";
$health_summary .= "- Weight: " . ($profile['weight'] ?? 'N/A') . " " . ($profile['weight_unit'] ?? 'N/A') . "\n";
$health_summary .= "- Height: " . ($profile['height'] ?? 'N/A') . " " . ($profile['height_unit'] ?? 'N/A') . "\n";
$health_summary .= "- BMI: " . ($bmi ?? 'N/A') . " kg/m²\n";
$health_summary .= "- Systolic BP: " . ($vital['systolic_bp'] ?? 'N/A') . " mmHg (normal: <120)\n";
$health_summary .= "- Diastolic BP: " . ($vital['diastolic_bp'] ?? 'N/A') . " mmHg (normal: <80)\n";
$health_summary .= "- Urea: " . ($renal['urea'] ?? 'N/A') . " mg/dL (normal: 7-20)\n";
$health_summary .= "- Creatinine: " . ($renal['creatinine'] ?? 'N/A') . " mg/dL (normal: 0.7-1.3)\n";
$health_summary .= "- Uric Acid: " . ($renal['uric_acid'] ?? 'N/A') . " mg/dL (normal: 3.5-7.2)\n";
$health_summary .= "- Calcium: " . ($renal['calcium'] ?? 'N/A') . " mg/dL (normal: 8.5-10.5)\n";

// Hardcoded Plans (5 predefined plans)
$predefined_plans = [
    [
        "exercise_plan" => [
            ["name" => "Brisk Walking", "duration" => "30 min", "description" => "Boosts cardiovascular health and burns calories"],
            ["name" => "Bodyweight Squats", "duration" => "15 min", "description" => "Strengthens lower body muscles"],
            ["name" => "Stretching", "duration" => "10 min", "description" => "Improves flexibility and reduces tension"]
        ],
        "diet_plan" => [
            ["meal" => "Greek Yogurt with Fruit", "description" => "High in protein and antioxidants"],
            ["meal" => "Grilled Chicken Salad", "description" => "Lean protein with fresh veggies"],
            ["meal" => "Steamed Broccoli with Quinoa", "description" => "Nutrient-dense and filling"]
        ],
        "warnings" => ["General fitness plan suitable for most users"]
    ],
    [
        "exercise_plan" => [
            ["name" => "Yoga", "duration" => "25 min", "description" => "Enhances flexibility and reduces stress"],
            ["name" => "Light Jogging", "duration" => "20 min", "description" => "Improves endurance gently"],
            ["name" => "Core Planks", "duration" => "10 min", "description" => "Strengthens core muscles"]
        ],
        "diet_plan" => [
            ["meal" => "Oatmeal with Nuts", "description" => "Rich in fiber and healthy fats"],
            ["meal" => "Turkey Wrap with Veggies", "description" => "Balanced protein and carbs"],
            ["meal" => "Baked Salmon with Asparagus", "description" => "Omega-3s and vitamins"]
        ],
        "warnings" => ["Balanced plan for moderate activity levels"]
    ],
    [
        "exercise_plan" => [
            ["name" => "Cycling", "duration" => "30 min", "description" => "Low-impact cardio workout"],
            ["name" => "Push-Ups", "duration" => "15 min", "description" => "Builds upper body strength"],
            ["name" => "Leg Stretches", "duration" => "10 min", "description" => "Prevents muscle stiffness"]
        ],
        "diet_plan" => [
            ["meal" => "Smoothie with Spinach and Banana", "description" => "Packed with vitamins and minerals"],
            ["meal" => "Lentil Soup", "description" => "High in fiber and plant-based protein"],
            ["meal" => "Grilled Fish with Brown Rice", "description" => "Lean protein and whole grains"]
        ],
        "warnings" => ["Great for weight management"]
    ],
    [
        "exercise_plan" => [
            ["name" => "Swimming", "duration" => "25 min", "description" => "Full-body workout with low joint stress"],
            ["name" => "Lunges", "duration" => "15 min", "description" => "Tones legs and improves balance"],
            ["name" => "Dynamic Stretching", "duration" => "10 min", "description" => "Boosts mobility"]
        ],
        "diet_plan" => [
            ["meal" => "Chia Pudding with Berries", "description" => "High in omega-3s and antioxidants"],
            ["meal" => "Vegetable Stir-Fry with Tofu", "description" => "Low-calorie and nutrient-rich"],
            ["meal" => "Roasted Chicken with Sweet Potato", "description" => "Balanced macros for energy"]
        ],
        "warnings" => ["Ideal for joint-friendly fitness"]
    ],
    [
        "exercise_plan" => [
            ["name" => "HIIT Cardio", "duration" => "20 min", "description" => "High-intensity fat-burning workout"],
            ["name" => "Dumbbell Rows", "duration" => "15 min", "description" => "Strengthens back and arms"],
            ["name" => "Cool-Down Stretching", "duration" => "10 min", "description" => "Aids recovery"]
        ],
        "diet_plan" => [
            ["meal" => "Protein Shake with Almond Milk", "description" => "Supports muscle recovery"],
            ["meal" => "Beef and Veggie Bowl", "description" => "High protein and fiber"],
            ["meal" => "Avocado Toast with Egg", "description" => "Healthy fats and protein"]
        ],
        "warnings" => ["Intense plan for active users"]
    ]
];

// Shuffle predefined plans
shuffle($predefined_plans);

// Select one random hardcoded plan
$selected_plan = $predefined_plans[0];
$exercise_plan = $selected_plan['exercise_plan'];
$diet_plan = $selected_plan['diet_plan'];
$warnings = $selected_plan['warnings'];

// Check for cached API plan (valid for 24 hours)
$query = "SELECT exercise_plan, diet_plan, generated_at FROM health_plans WHERE user_id = '$user_id' AND generated_at > NOW() - INTERVAL 1 DAY";
$cache_result = mysqli_query($conn, $query);
$cache = mysqli_fetch_assoc($cache_result);

$api_exercise_plan = [];
$api_diet_plan = [];
$api_warnings = [];

if ($cache && !isset($_POST['refresh'])) {
    $api_exercise_plan = json_decode($cache['exercise_plan'], true) ?? [];
    $api_diet_plan = json_decode($cache['diet_plan'], true) ?? [];
    $api_warnings = ["Cached AI-generated plan"];
    error_log("Using cached API plan for user_id: $user_id");
} else {
    // Hugging Face API setup (as the 6th option)
    $api_token = "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";
    $api_url = "https://api-inference.huggingface.co/models/mixtral-8x7b-instruct-v0.1";
    $prompt = "[INST] Based on the following health data, create a detailed, personalized daily exercise and diet plan tailored to the user's specific conditions (e.g., age, weight, BMI, blood pressure, renal health). Avoid generic responses and use the provided data to make informed recommendations. Return the result in JSON format:\n\n$health_summary\n\nOutput format:\n```json\n{
  \"exercise_plan\": [
    {\"name\": \"Walking\", \"duration\": \"30 min\", \"description\": \"Low-impact exercise to improve circulation\"}
  ],
  \"diet_plan\": [
    {\"meal\": \"Oatmeal with Berries\", \"description\": \"High in fiber and antioxidants\"}
  ],
  \"warnings\": [\"High BP detected, avoid intense exercise\"]
}\n```[/INST]";

    $headers = [
        "Authorization: Bearer $api_token",
        "Content-Type: application/json"
    ];

    $data = [
        "inputs" => $prompt,
        "parameters" => [
            "max_new_tokens" => 800,
            "temperature" => 0.5,
            "top_p" => 0.9,
            "do_sample" => true,
            "return_full_text" => false
        ]
    ];

    // Make API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Log response
    error_log("API Response (HTTP $http_code): " . $response);
    if ($curl_error) error_log("cURL Error: " . $curl_error);

    if ($http_code == 200 && $response) {
        $api_result = json_decode($response, true);
        $generated_text = $api_result[0]['generated_text'] ?? '';
        error_log("Generated Text: " . $generated_text);

        // Extract JSON
        preg_match('/```json\s*(.*?)\s*```/s', $generated_text, $json_match);
        if ($json_match && isset($json_match[1])) {
            $plan_data = json_decode($json_match[1], true);
            if (json_last_error() === JSON_ERROR_NONE && $plan_data) {
                $api_exercise_plan = $plan_data['exercise_plan'] ?? [];
                $api_diet_plan = $plan_data['diet_plan'] ?? [];
                $api_warnings = $plan_data['warnings'] ?? [];

                // Validate and sanitize
                $api_exercise_plan = array_filter($api_exercise_plan, fn($e) => !empty($e['name']) && !empty($e['duration']) && !empty($e['description']));
                $api_diet_plan = array_filter($api_diet_plan, fn($d) => !empty($d['meal']) && !empty($d['description']));

                // Cache the API plan
                $exercise_json = mysqli_real_escape_string($conn, json_encode($api_exercise_plan));
                $diet_json = mysqli_real_escape_string($conn, json_encode($api_diet_plan));
                $query = "INSERT INTO health_plans (user_id, exercise_plan, diet_plan) VALUES ('$user_id', '$exercise_json', '$diet_json')
                          ON DUPLICATE KEY UPDATE exercise_plan = '$exercise_json', diet_plan = '$diet_json', generated_at = NOW()";
                mysqli_query($conn, $query) or error_log("DB Error: " . mysqli_error($conn));
            } else {
                error_log("JSON Parse Error: " . json_last_error_msg());
                $api_warnings[] = "Invalid API plan format.";
            }
        } else {
            error_log("No valid JSON in response: " . $generated_text);
            $api_warnings[] = "API response parsing failed.";
        }
    } else {
        $api_warnings[] = "API request failed (HTTP $http_code).";
    }

    // Use fallback if API fails
    if (empty($api_exercise_plan) || empty($api_diet_plan)) {
        $api_exercise_plan = [["name" => "Gentle Stretching", "duration" => "15 min", "description" => "Improves flexibility"]];
        $api_diet_plan = [["meal" => "Vegetable Soup", "description" => "Nutrient-rich and light"]];
        $api_warnings[] = "AI plan unavailable; using default.";
    }
}

// Combine hardcoded and API plans
$all_exercise_plans = array_merge($exercise_plan, $api_exercise_plan);
$all_diet_plans = array_merge($diet_plan, $api_diet_plan);
$all_warnings = array_merge($warnings, $api_warnings);

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoCheck Exercise & Diet Plan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .card { transition: all 0.3s ease; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15); }
        .progress-bar { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; }
        .progress-fill { height: 100%; background: #2F4F2F; transition: width 0.5s ease; }
        .profile-img { object-fit: cover; width: 100%; height: 100%; }
        @media (max-width: 640px) { .grid-cols-12 { grid-template-columns: 1fr; } .col-span-3, .col-span-9 { grid-column: span 12; } }
        @media (min-width: 641px) and (max-width: 1024px) { .grid-cols-12 { grid-template-columns: repeat(6, 1fr); } .col-span-3, .col-span-9 { grid-column: span 6; } }
    </style>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#2F4F2F', secondary: '#BFB1A4' }, borderRadius: { 'button': '8px' } } }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-primary fixed w-full top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <span class="text-white text-2xl font-['Pacifico']">GoCheck</span>
                <div class="flex items-center gap-4 md:hidden">
                    <button id="menuButton" class="text-white focus:outline-none"><i class="ri-menu-line ri-2x"></i></button>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="dashboard.php" class="text-white hover:text-secondary font-medium">Dashboard</a>
                    <a href="exercise.php" class="text-secondary font-medium">Exercise & Diet</a>
                    <a href="reports.php" class="text-white hover:text-secondary font-medium">Reports</a>
                    <a href="settings.php" class="text-white hover:text-secondary font-medium">Settings</a>
                    <a href="notifications.php" class="text-white hover:text-secondary"><i class="ri-notification-3-line"></i></a>
                    <a href="profile.php" class="text-white hover:text-secondary"><i class="ri-user-line"></i></a>
                    <a href="logout.php" class="bg-secondary/20 text-white px-4 py-2 rounded-button hover:bg-secondary/30 transition">Logout</a>
                </div>
            </div>
            <div id="mobileMenu" class="hidden md:hidden bg-primary text-white px-4 py-2">
                <a href="dashboard.php" class="block py-2 text-white">Dashboard</a>
                <a href="exercise.php" class="block py-2 text-secondary">Exercise & Diet</a>
                <a href="reports.php" class="block py-2 text-white">Reports</a>
                <a href="settings.php" class="block py-2 text-white">Settings</a>
                <a href="notifications.php" class="block py-2 text-white flex items-center"><i class="ri-notification-3-line mr-2"></i> Notifications</a>
                <a href="profile.php" class="block py-2 text-white flex items-center"><i class="ri-user-line mr-2"></i> Profile</a>
                <a href="logout.php" class="block py-2 text-white bg-secondary/20 rounded-button">Logout</a>
            </div>
        </div>
    </nav>

    <main class="pt-20 pb-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-3">
                    <div class="bg-white rounded-lg p-6 shadow-md card">
                        <div class="flex flex-col items-center">
                            <div class="w-24 h-24 rounded-full overflow-hidden mb-4">
                                <img src="<?php echo $profile_photo; ?>" alt="Profile Photo" class="profile-img">
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h2>
                            <p class="text-sm text-gray-600 mb-4">ID: PT-<?php echo date('Ymd') . '-' . $user_id; ?></p>
                            <div class="w-full space-y-2 text-sm">
                                <div class="flex justify-between"><span class="text-gray-600">Age:</span><span><?php echo htmlspecialchars($profile['age'] ?? 'N/A'); ?> years</span></div>
                                <div class="flex justify-between"><span class="text-gray-600">Gender:</span><span><?php echo htmlspecialchars($profile['gender'] ?? 'N/A'); ?></span></div>
                                <div class="flex justify-between"><span class="text-gray-600">BMI:</span><span><?php echo htmlspecialchars($bmi ?? 'N/A'); ?> kg/m²</span></div>
                            </div>
                            <div class="flex space-x-4 mt-4">
                                <a href="profile.php" class="px-4 py-2 bg-primary text-white rounded-button hover:bg-primary/90 transition">Edit Profile</a>
                                <a href="reports.php" class="px-4 py-2 border border-primary text-primary rounded-button hover:bg-primary hover:text-white transition">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-9">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Your Personalized Health Plan</h1>
                        <button id="refreshPlan" class="px-6 py-2 bg-primary text-white rounded-button hover:bg-primary/90 flex items-center transition">
                            <i class="ri-refresh-line mr-2"></i> Shuffle Plan
                        </button>
                    </div>

                    <?php if (!empty($all_warnings)): ?>
                        <div class="bg-yellow-50 p-4 rounded-lg mb-6 animate__animated animate__fadeIn">
                            <h3 class="text-lg font-semibold text-yellow-800 flex items-center"><i class="ri-alert-line mr-2"></i> Health Notices</h3>
                            <ul class="list-disc pl-5 mt-2 text-sm text-yellow-700">
                                <?php foreach ($all_warnings as $warning): ?>
                                    <li><?php echo htmlspecialchars($warning); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white p-6 rounded-lg shadow-md mb-6 card animate__animated animate__fadeInUp">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Exercise Routine</h3>
                        <div class="space-y-4">
                            <?php foreach ($all_exercise_plans as $index => $exercise): ?>
                                <div class="flex items-start p-4 bg-gray-50 rounded-lg card animate__animated animate__bounceIn" style="animation-delay: <?php echo $index * 0.2; ?>s;">
                                    <i class="ri-run-line text-primary text-2xl mr-4"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($exercise['name']); ?> - <span class="text-primary"><?php echo htmlspecialchars($exercise['duration']); ?></span></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($exercise['description']); ?></p>
                                        <div class="progress-bar mt-2"><div class="progress-fill" style="width: <?php echo min(100, (int)str_replace(' min', '', $exercise['duration']) * 2); ?>%;"></div></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md card animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Diet Plan</h3>
                        <div class="space-y-4">
                            <?php foreach ($all_diet_plans as $index => $diet): ?>
                                <div class="flex items-start p-4 bg-gray-50 rounded-lg card animate__animated animate__bounceIn" style="animation-delay: <?php echo ($index + count($all_exercise_plans)) * 0.2; ?>s;">
                                    <i class="ri-restaurant-line text-primary text-2xl mr-4"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($diet['meal']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($diet['description']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('menuButton').addEventListener('click', () => {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });

        document.getElementById('refreshPlan').addEventListener('click', () => {
            fetch('exercise.php', { method: 'POST', body: new URLSearchParams({ 'refresh': true }) })
                .then(response => response.text())
                .then(() => location.reload())
                .catch(err => console.error('Refresh failed:', err));
        });
    </script>
</body>
</html>