<?php
session_start();
include 'config.php';  // Database connection
include 'alert.php';

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
$query = "SELECT allergies FROM medical_history WHERE user_id = ?";
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

// Close statements
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoCheck Health Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
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
        .error { color: red; font-size: 0.75rem; margin-top: 0.25rem; display: none; }
        @media (max-width: 640px) {
            .grid-cols-12 { grid-template-columns: 1fr; }
            .col-span-3, .col-span-9 { grid-column: span 12; }
            .modal-content { width: 90%; }
            nav { padding: 0.5rem; }
            .flex.justify-between.h-16 { height: auto; flex-wrap: wrap; }
            .ml-10 { margin-left: 0; }
        }
        @media (min-width: 641px) and (max-width: 1024px) {
            .grid-cols-12 { grid-template-columns: repeat(6, 1fr); }
            .col-span-3 { grid-column: span 6; }
            .col-span-9 { grid-column: span 6; }
            .modal-content { width: 80%; }
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2F4F2F',
                        secondary: '#BFB1A4'
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
                        <a href="dashboard.php" class="text-white px-3 py-2 text-sm font-medium">Dashboard</a>
                        
                        <a href="reports.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Reports</a>
                        <a href="settings.php" class="text-secondary hover:text-white px-3 py-2 text-sm font-medium">Settings</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4 flex-wrap">
                    <a href="notifications.php" class="text-secondary hover:text-white w-8 h-8 flex items-center justify-center">
                        <i class="ri-notification-3-line text-xl"></i>
                    </a>
                    <a href="profile.php" class="text-secondary hover:text-white w-8 h-8 flex items-center justify-center">
                        <i class="ri-user-line text-xl"></i>
                    </a>
                    <a href="logout.php" class="text-secondary hover:text-white px-4 py-2 text-sm font-medium !rounded-button hover:bg-secondary/20">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-20 pb-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-3">
                    <div class="bg-white rounded-lg p-6 shadow-sm">
                        <div class="flex flex-col items-center">
                            <div class="w-24 h-24 rounded-full bg-secondary mb-4 overflow-hidden">
                                <img src="<?php echo $profile['profile_photo'] ?: 'https://public.readdy.ai/ai/img_res/9b4fe9c3650fdb605f69787c8e9898b5.jpg'; ?>" class="w-full h-full object-cover" alt="Profile">
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($user['name']); ?></h2>
                            <p class="text-sm text-gray-600 mb-4">Patient ID: PT-<?php echo date('Ymd') . '-' . $user_id; ?></p>
                            <div class="w-full space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Age:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($profile['age'] ?? 'N/A'); ?> years</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Gender:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($profile['gender'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Contact:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($user['contact_number'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <a href="profile.php" class="px-6 py-2 bg-primary text-white rounded-button hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Edit Profile</a>
                                <a href="reports.php" class="px-6 py-2 border border-primary text-primary rounded-button hover:bg-primary hover:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">View Details</a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded">
                                <div>
                                    <p class="text-sm text-gray-600">Last Check-up</p>
                                    <p class="font-medium text-gray-900"><?php echo date('M d, Y', strtotime('-4 days')); ?></p>
                                </div>
                                <i class="ri-calendar-check-line text-green-600"></i>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                                <div>
                                    <p class="text-sm text-gray-600">Next Appointment</p>
                                    <p class="font-medium text-gray-900"><?php echo date('M d, Y', strtotime('+14 days')); ?></p>
                                </div>
                                <i class="ri-calendar-todo-line text-blue-600"></i>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded">
                                <div>
                                    <p class="text-sm text-gray-600">Health Status</p>
                                    <p class="font-medium text-gray-900">Good</p>
                                </div>
                                <i class="ri-heart-pulse-line text-yellow-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-9">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-semibold text-gray-900">Health Metrics Dashboard</h1>
                        <button id="updateDataBtn" class="px-6 py-2 bg-primary text-white rounded-button hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 flex items-center">
                            <i class="ri-refresh-line mr-2"></i>
                            Update Data
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div class="bg-white p-6 rounded-lg shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Blood Sugar</h3>
                                    <p class="text-sm text-gray-600">Last updated: <?php echo date('F d, Y h:i A'); ?></p>
                                </div>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded">Normal</span>
                            </div>
                            <div id="sugarChart" class="w-full h-64"></div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Blood Pressure</h3>
                                    <p class="text-sm text-gray-600">Last updated: <?php echo date('F d, Y h:i A'); ?></p>
                                </div>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-sm rounded">Elevated</span>
                            </div>
                            <div id="bpChart" class="w-full h-64"></div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Cholesterol Levels</h3>
                                    <p class="text-sm text-gray-600">Last updated: <?php echo date('F d, Y h:i A'); ?></p>
                                </div>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded">Normal</span>
                            </div>
                            <div id="cholesterolChart" class="w-full h-64"></div>
                        </div>

                        <div class="bg-white p-6 rounded-lg shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Kidney Function (RFT)</h3>
                                    <p class="text-sm text-gray-600">Last updated: <?php echo date('F d, Y h:i A'); ?></p>
                                </div>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-sm rounded">Normal</span>
                            </div>
                            <div id="rftChart" class="w-full h-64"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="updateModal" class="modal items-center justify-center">
        <div class="bg-white rounded-lg shadow-sm modal-content p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Update Health Data</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <form id="healthDataForm" class="space-y-6" action="update_health.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fasting Blood Sugar (mg/dL)</label>
                    <input type="number" name="fasting_blood_sugar" value="<?php echo htmlspecialchars($vital['fasting_blood_sugar'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="0">
                    <span class="error" id="fasting_blood_sugar_error">Must be a positive number.</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Post Meal Blood Sugar (mg/dL)</label>
                    <input type="number" name="post_meal_blood_sugar" value="<?php echo htmlspecialchars($vital['post_meal_blood_sugar'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="0">
                    <span class="error" id="post_meal_blood_sugar_error">Must be a positive number.</span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Systolic BP (mmHg)</label>
                        <input type="number" name="systolic_bp" value="<?php echo htmlspecialchars($vital['systolic_bp'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="1">
                        <span class="error" id="systolic_bp_error">Must be greater than 0.</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Diastolic BP (mmHg)</label>
                        <input type="number" name="diastolic_bp" value="<?php echo htmlspecialchars($vital['diastolic_bp'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="1">
                        <span class="error" id="diastolic_bp_error">Must be greater than 0.</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HDL Cholesterol (mg/dL)</label>
                    <input type="number" name="hdl_cholesterol" value="<?php echo htmlspecialchars($vital['hdl_cholesterol'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="0">
                    <span class="error" id="hdl_cholesterol_error">Must be a positive number.</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">LDL Cholesterol (mg/dL)</label>
                    <input type="number" name="ldl_cholesterol" value="<?php echo htmlspecialchars($vital['ldl_cholesterol'] ?? ''); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="0">
                    <span class="error" id="ldl_cholesterol_error">Must be a positive number.</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urea (mg/dL)</label>
                    <div class="flex gap-2 items-center">
                        <input type="number" name="urea" value="<?php echo htmlspecialchars($renal['urea'] ?? ''); ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="7" max="20">
                        <span class="text-sm text-gray-600 whitespace-nowrap">mg/dL</span>
                    </div>
                    <span class="error" id="urea_error">Must be between 7 and 20.</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Creatinine (mg/dL)</label>
                    <div class="flex gap-2 items-center">
                        <input type="number" name="creatinine" value="<?php echo htmlspecialchars($renal['creatinine'] ?? ''); ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="0.7" max="1.3">
                        <span class="text-sm text-gray-600 whitespace-nowrap">mg/dL</span>
                    </div>
                    <span class="error" id="creatinine_error">Must be between 0.7 and 1.3.</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Uric Acid (mg/dL)</label>
                    <div class="flex gap-2 items-center">
                        <input type="number" name="uric_acid" value="<?php echo htmlspecialchars($renal['uric_acid'] ?? ''); ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="3.5" max="7.2">
                        <span class="text-sm text-gray-600 whitespace-nowrap">mg/dL</span>
                    </div>
                    <span class="error" id="uric_acid_error">Must be between 3.5 and 7.2.</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Calcium (mg/dL)</label>
                    <div class="flex gap-2 items-center">
                        <input type="number" name="calcium" value="<?php echo htmlspecialchars($renal['calcium'] ?? ''); ?>" class="flex-1 px-4 py-2 border border-gray-300 rounded-button focus:ring-2 focus:ring-primary focus:border-primary text-sm" required min="8.5" max="10.5">
                        <span class="text-sm text-gray-600 whitespace-nowrap">mg/dL</span>
                    </div>
                    <span class="error" id="calcium_error">Must be between 8.5 and 10.5.</span>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" id="cancelUpdate" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-button hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-button hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="fixed top-4 right-4 bg-white shadow-lg rounded-lg p-4 hidden">
        <div class="flex items-center gap-2">
            <i class="ri-check-line text-green-500"></i>
            <span id="toastMessage" class="text-sm text-gray-700"></span>
        </div>
    </div>

    <script>
        const realData = {
            sugar: {
                dates: ['Feb 13', 'Feb 14', 'Feb 15', 'Feb 16', 'Feb 17', 'Feb 18', 'Feb 19'],
                values: [
                    <?php echo isset($vital['fasting_blood_sugar']) ? floatval($vital['fasting_blood_sugar']) : 95; ?>,
                    <?php echo isset($vital['fasting_blood_sugar']) ? floatval($vital['fasting_blood_sugar']) : 98; ?>,
                    <?php echo isset($vital['fasting_blood_sugar']) ? floatval($vital['fasting_blood_sugar']) : 92; ?>,
                    <?php echo isset($vital['fasting_blood_sugar']) ? floatval($vital['fasting_blood_sugar']) : 96; ?>,
                    <?php echo isset($vital['fasting_blood_sugar']) ? floatval($vital['fasting_blood_sugar']) : 95; ?>,
                    <?php echo isset($vital['fasting_blood_sugar']) ? floatval($vital['fasting_blood_sugar']) : 97; ?>,
                    <?php echo isset($vital['fasting_blood_sugar']) ? floatval($vital['fasting_blood_sugar']) : 94; ?>
                ]
            },
            bp: {
                dates: ['Feb 13', 'Feb 14', 'Feb 15', 'Feb 16', 'Feb 17', 'Feb 18', 'Feb 19'],
                systolic: [
                    <?php echo isset($vital['systolic_bp']) ? floatval($vital['systolic_bp']) : 130; ?>,
                    <?php echo isset($vital['systolic_bp']) ? floatval($vital['systolic_bp']) : 132; ?>,
                    <?php echo isset($vital['systolic_bp']) ? floatval($vital['systolic_bp']) : 128; ?>,
                    <?php echo isset($vital['systolic_bp']) ? floatval($vital['systolic_bp']) : 131; ?>,
                    <?php echo isset($vital['systolic_bp']) ? floatval($vital['systolic_bp']) : 129; ?>,
                    <?php echo isset($vital['systolic_bp']) ? floatval($vital['systolic_bp']) : 130; ?>,
                    <?php echo isset($vital['systolic_bp']) ? floatval($vital['systolic_bp']) : 132; ?>
                ],
                diastolic: [
                    <?php echo isset($vital['diastolic_bp']) ? floatval($vital['diastolic_bp']) : 85; ?>,
                    <?php echo isset($vital['diastolic_bp']) ? floatval($vital['diastolic_bp']) : 84; ?>,
                    <?php echo isset($vital['diastolic_bp']) ? floatval($vital['diastolic_bp']) : 82; ?>,
                    <?php echo isset($vital['diastolic_bp']) ? floatval($vital['diastolic_bp']) : 83; ?>,
                    <?php echo isset($vital['diastolic_bp']) ? floatval($vital['diastolic_bp']) : 84; ?>,
                    <?php echo isset($vital['diastolic_bp']) ? floatval($vital['diastolic_bp']) : 85; ?>,
                    <?php echo isset($vital['diastolic_bp']) ? floatval($vital['diastolic_bp']) : 84; ?>
                ]
            },
            cholesterol: {
                dates: ['Feb 13', 'Feb 14', 'Feb 15', 'Feb 16', 'Feb 17', 'Feb 18', 'Feb 19'],
                values: [
                    <?php echo isset($vital['hdl_cholesterol']) ? floatval($vital['hdl_cholesterol']) : 45; ?>,
                    <?php echo isset($vital['hdl_cholesterol']) ? floatval($vital['hdl_cholesterol']) : 46; ?>,
                    <?php echo isset($vital['hdl_cholesterol']) ? floatval($vital['hdl_cholesterol']) : 44; ?>,
                    <?php echo isset($vital['hdl_cholesterol']) ? floatval($vital['hdl_cholesterol']) : 45; ?>,
                    <?php echo isset($vital['hdl_cholesterol']) ? floatval($vital['hdl_cholesterol']) : 46; ?>,
                    <?php echo isset($vital['hdl_cholesterol']) ? floatval($vital['hdl_cholesterol']) : 45; ?>,
                    <?php echo isset($vital['hdl_cholesterol']) ? floatval($vital['hdl_cholesterol']) : 46; ?>
                ]
            },
            rft: {
                dates: ['Feb 13', 'Feb 14', 'Feb 15', 'Feb 16', 'Feb 17', 'Feb 18', 'Feb 19'],
                values: [
                    <?php echo isset($renal['creatinine']) ? floatval($renal['creatinine']) : 0.9; ?>,
                    <?php echo isset($renal['creatinine']) ? floatval($renal['creatinine']) : 0.92; ?>,
                    <?php echo isset($renal['creatinine']) ? floatval($renal['creatinine']) : 0.88; ?>,
                    <?php echo isset($renal['creatinine']) ? floatval($renal['creatinine']) : 0.91; ?>,
                    <?php echo isset($renal['creatinine']) ? floatval($renal['creatinine']) : 0.89; ?>,
                    <?php echo isset($renal['creatinine']) ? floatval($renal['creatinine']) : 0.90; ?>,
                    <?php echo isset($renal['creatinine']) ? floatval($renal['creatinine']) : 0.89; ?>
                ]
            }
        };

        const chartOptions = {
            sugar: {
                animation: false,
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    textStyle: { color: '#1f2937' }
                },
                grid: { top: 20, right: 20, bottom: 20, left: 50 },
                xAxis: {
                    type: 'category',
                    data: realData.sugar.dates,
                    axisLine: { lineStyle: { color: '#e5e7eb' } }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { lineStyle: { color: '#e5e7eb' } }
                },
                series: [{
                    data: realData.sugar.values,
                    type: 'line',
                    smooth: true,
                    lineStyle: { color: 'rgba(87, 181, 231, 1)' },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                            offset: 0,
                            color: 'rgba(87, 181, 231, 0.3)'
                        }, {
                            offset: 1,
                            color: 'rgba(87, 181, 231, 0.1)'
                        }])
                    }
                }]
            },
            bp: {
                animation: false,
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    textStyle: { color: '#1f2937' }
                },
                grid: { top: 20, right: 20, bottom: 20, left: 50 },
                xAxis: {
                    type: 'category',
                    data: realData.bp.dates,
                    axisLine: { lineStyle: { color: '#e5e7eb' } }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { lineStyle: { color: '#e5e7eb' } }
                },
                series: [{
                    name: 'Systolic',
                    data: realData.bp.systolic,
                    type: 'line',
                    smooth: true,
                    lineStyle: { color: 'rgba(87, 181, 231, 1)' }
                }, {
                    name: 'Diastolic',
                    data: realData.bp.diastolic,
                    type: 'line',
                    smooth: true,
                    lineStyle: { color: 'rgba(141, 211, 199, 1)' }
                }]
            },
            cholesterol: {
                animation: false,
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    textStyle: { color: '#1f2937' }
                },
                grid: { top: 20, right: 20, bottom: 20, left: 50 },
                xAxis: {
                    type: 'category',
                    data: realData.cholesterol.dates,
                    axisLine: { lineStyle: { color: '#e5e7eb' } }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { lineStyle: { color: '#e5e7eb' } }
                },
                series: [{
                    data: realData.cholesterol.values,
                    type: 'line',
                    smooth: true,
                    lineStyle: { color: 'rgba(251, 191, 114, 1)' },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                            offset: 0,
                            color: 'rgba(251, 191, 114, 0.3)'
                        }, {
                            offset: 1,
                            color: 'rgba(251, 191, 114, 0.1)'
                        }])
                    }
                }]
            },
            rft: {
                animation: false,
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    textStyle: { color: '#1f2937' }
                },
                grid: { top: 20, right: 20, bottom: 20, left: 50 },
                xAxis: {
                    type: 'category',
                    data: realData.rft.dates,
                    axisLine: { lineStyle: { color: '#e5e7eb' } }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { lineStyle: { color: '#e5e7eb' } }
                },
                series: [{
                    data: realData.rft.values,
                    type: 'line',
                    smooth: true,
                    lineStyle: { color: 'rgba(252, 141, 98, 1)' },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                            offset: 0,
                            color: 'rgba(252, 141, 98, 0.3)'
                        }, {
                            offset: 1,
                            color: 'rgba(252, 141, 98, 0.1)'
                        }])
                    }
                }]
            }
        };

        const sugarChart = echarts.init(document.getElementById('sugarChart'));
        const bpChart = echarts.init(document.getElementById('bpChart'));
        const cholesterolChart = echarts.init(document.getElementById('cholesterolChart'));
        const rftChart = echarts.init(document.getElementById('rftChart'));

        sugarChart.setOption(chartOptions.sugar);
        bpChart.setOption(chartOptions.bp);
        cholesterolChart.setOption(chartOptions.cholesterol);
        rftChart.setOption(chartOptions.rft);

        window.addEventListener('resize', function() {
            sugarChart.resize();
            bpChart.resize();
            cholesterolChart.resize();
            rftChart.resize();
        });

        const modal = document.getElementById('updateModal');
        const updateBtn = document.getElementById('updateDataBtn');
        const closeBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelUpdate');
        const form = document.getElementById('healthDataForm');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');

        updateBtn.addEventListener('click', () => {
            modal.classList.add('active');
            document.querySelectorAll('.error').forEach(error => error.style.display = 'none');
        });

        const closeModal = () => {
            modal.classList.remove('active');
            document.querySelectorAll('.error').forEach(error => error.style.display = 'none');
        };

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        function showToast(message, type = 'success') {
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            if (type === 'error') {
                toast.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
                toast.classList.remove('bg-white', 'border-green-400', 'text-green-700');
            } else {
                toast.classList.add('bg-white', 'border-green-400', 'text-green-700');
                toast.classList.remove('bg-red-100', 'border-red-400', 'text-red-700');
            }
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            let isValid = true;
            const fields = {
                fasting_blood_sugar: { min: 0, errorId: 'fasting_blood_sugar_error', message: 'Must be a positive number.' },
                post_meal_blood_sugar: { min: 0, errorId: 'post_meal_blood_sugar_error', message: 'Must be a positive number.' },
                systolic_bp: { min: 1, errorId: 'systolic_bp_error', message: 'Must be greater than 0.' },
                diastolic_bp: { min: 1, errorId: 'diastolic_bp_error', message: 'Must be greater than 0.' },
                hdl_cholesterol: { min: 0, errorId: 'hdl_cholesterol_error', message: 'Must be a positive number.' },
                ldl_cholesterol: { min: 0, errorId: 'ldl_cholesterol_error', message: 'Must be a positive number.' },
                urea: { min: 7, max: 20, errorId: 'urea_error', message: 'Must be between 7 and 20.' },
                creatinine: { min: 0.7, max: 1.3, errorId: 'creatinine_error', message: 'Must be between 0.7 and 1.3.' },
                uric_acid: { min: 3.5, max: 7.2, errorId: 'uric_acid_error', message: 'Must be between 3.5 and 7.2.' },
                calcium: { min: 8.5, max: 10.5, errorId: 'calcium_error', message: 'Must be between 8.5 and 10.5.' }
            };

            // Validate each field
            for (let field in fields) {
                const input = form.querySelector(`input[name="${field}"]`);
                const value = parseFloat(input.value) || 0;
                const { min, max, errorId, message } = fields[field];
                const errorElement = document.getElementById(errorId);

                if (value < min || (max && value > max)) {
                    errorElement.style.display = 'block';
                    isValid = false;
                } else {
                    errorElement.style.display = 'none';
                }
            }

            if (!isValid) {
                return;
            }

            const formData = new FormData(this);
            fetch('update_health.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    closeModal();
                    window.location.reload(); // Refresh to show updated data
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showToast('An error occurred: ' + error, 'error');
            });
        });
    </script>
</body>
</html>