<?php
session_start();
include 'config.php';


// Check if user is not logged in
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

// Fetch all vital statistics (previous data) ordered by date
$query = "SELECT id, systolic_bp, diastolic_bp, hdl_cholesterol, ldl_cholesterol, fasting_blood_sugar, post_meal_blood_sugar, 
          NOW() AS record_date 
          FROM vital_statistics WHERE user_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$vital_results = $stmt->get_result();
$previous_vitals = $vital_results->fetch_all(MYSQLI_ASSOC);

// Fetch latest vital statistics
$query = "SELECT systolic_bp, diastolic_bp, hdl_cholesterol, ldl_cholesterol, fasting_blood_sugar, post_meal_blood_sugar 
          FROM vital_statistics WHERE user_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$latest_vital_result = $stmt->get_result();
$latest_vital = $latest_vital_result->fetch_assoc();

// Fetch all renal tests (previous data) ordered by date
$query = "SELECT id, urea, creatinine, uric_acid, calcium, NOW() AS record_date 
          FROM renal_tests WHERE user_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$renal_results = $stmt->get_result();
$previous_renals = $renal_results->fetch_all(MYSQLI_ASSOC);

// Fetch latest renal tests
$query = "SELECT urea, creatinine, uric_acid, calcium 
          FROM renal_tests WHERE user_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$latest_renal_result = $stmt->get_result();
$latest_renal = $latest_renal_result->fetch_assoc();

// Close statements
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Data Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .date-input::-webkit-calendar-picker-indicator {
            display: none;
        }
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
            .flex.flex-wrap.gap-4 { flex-direction: column; align-items: stretch; gap: 0.5rem; }
            .flex-1 { flex: none; width: 100%; }
            .min-w-[300px] { min-width: 0; }
            .text-lg { font-size: 1rem; }
            .text-xs { font-size: 0.75rem; }
            .p-6 { padding: 0.75rem; }
            .px-6 { padding-left: 0.75rem; padding-right: 0.75rem; }
            .py-3 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .w-8 { width: 2rem; }
            .h-8 { height: 2rem; }
            .ri-xl { font-size: 1rem; }
            .grid-cols-1.md:grid-cols-2 { grid-template-columns: 1fr; }
            .h-300 { height: 200px; }
            table { display: block; overflow-x: auto; }
            th, td { padding: 0.5rem; font-size: 0.75rem; }
        }
        @media (min-width: 641px) and (max-width: 1024px) {
            .grid-cols-12 { grid-template-columns: repeat(6, 1fr); }
            .col-span-3 { grid-column: span 6; }
            .col-span-9 { grid-column: span 6; }
            .grid-cols-2 { grid-template-columns: 1fr 1fr; }
            .h-300 { height: 250px; }
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
                       
                        <a href="reports.php" class="text-white px-2 sm:px-3 py-1 sm:py-2 text-sm font-medium">Reports</a>
                        <a href="settings.php" class="text-secondary hover:text-white px-2 sm:px-3 py-1 sm:py-2 text-sm font-medium">Settings</a>
                    </div>
                    <div class="flex items-center space-x-2 sm:space-x-4 flex-wrap">
                        <a href="notifications.php" class="text-secondary hover:text-white w-6 sm:w-8 h-6 sm:h-8 flex items-center justify-center">
                            <i class="ri-notification-3-line text-lg sm:text-xl"></i>
                        </a>
                        <a href="profile.php" class="text-secondary hover:text-white w-6 sm:w-8 h-6 sm:h-8 flex items-center justify-center">
                            <i class="ri-user-line text-lg sm:text-xl"></i>
                        </a>
                        <a href="logout.php" class="!rounded-button bg-secondary/20 text-secondary hover:text-white px-2 sm:px-4 py-1 sm:py-2 text-sm font-medium hover:bg-secondary/30">Logout</a>
                        <button id="exportBtn" class="!rounded-button bg-secondary text-primary px-2 sm:px-4 py-1 sm:py-2 flex items-center text-xs sm:text-sm">
                            <i class="ri-download-line mr-1 sm:mr-2 text-sm sm:text-base"></i>
                            Export Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-primary text-white px-2 py-2">
            <a href="dashboard.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Dashboard</a>
             
            <a href="reports.php" class="block px-2 py-1 text-sm font-medium text-white">Reports</a>
            <a href="settings.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white">Settings</a>
            <a href="notifications.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white flex items-center">
                <i class="ri-notification-3-line mr-2 text-lg"></i> Notifications
            </a>
            <a href="profile.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white flex items-center">
                <i class="ri-user-line mr-2 text-lg"></i> Profile
            </a>
            <a href="logout.php" class="block px-2 py-1 text-sm font-medium text-secondary hover:text-white !rounded-button bg-secondary/20 hover:bg-secondary/30">Logout</a>
            <button id="mobileExportBtn" class="block w-full px-2 py-1 text-sm font-medium text-primary bg-secondary !rounded-button flex items-center justify-center">
                <i class="ri-download-line mr-1 text-sm"></i> Export Data
            </button>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-2 sm:px-4 py-4 sm:py-6 pt-16 sm:pt-20"> <!-- Adjusted padding for fixed navbar -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-4 mb-4 sm:mb-6">
                <div class="flex-1 min-w-[200px] sm:min-w-[300px] relative">
                    <input type="text" id="searchInput" placeholder="Search health data..." class="w-full pl-8 sm:pl-10 pr-4 py-1 sm:py-2 border border-gray-200 rounded focus:outline-none focus:border-primary text-xs sm:text-sm">
                    <i class="ri-search-line absolute left-2 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm sm:text-base"></i>
                </div>
                <div class="flex gap-2 sm:gap-4 flex-wrap">
                    <div class="relative">
                        <input type="date" id="dateFilter" class="date-input pl-8 sm:pl-10 pr-4 py-1 sm:py-2 border border-gray-200 rounded focus:outline-none focus:border-primary text-xs sm:text-sm">
                        <i class="ri-calendar-line absolute left-2 sm:left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm sm:text-base"></i>
                    </div>
                    <div class="relative">
                        <button id="filterBtn" class="px-2 sm:px-4 py-1 sm:py-2 border border-gray-200 rounded flex items-center text-xs sm:text-sm">
                            <i class="ri-filter-3-line mr-1 sm:mr-2 text-sm sm:text-base"></i>
                            Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 sm:gap-4 mb-4 sm:mb-6" id="categoryButtons">
                <button class="px-2 sm:px-4 py-1 sm:py-2 bg-primary text-white !rounded-full active text-xs sm:text-sm" data-category="vital">Vital Statistics</button>
                <button class="px-2 sm:px-4 py-1 sm:py-2 bg-gray-100 text-gray-600 !rounded-full text-xs sm:text-sm" data-category="renal">Renal Tests</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                <div class="bg-white rounded-lg shadow p-3 sm:p-4" id="vitalChartContainer">
                    <div class="flex justify-between items-center mb-2 sm:mb-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Vital Statistics Trends</h3>
                        <button class="text-primary hover:text-primary/80">
                            <i class="ri-more-2-fill text-sm sm:text-base"></i>
                        </button>
                    </div>
                    <div id="vitalChart" style="width: 100%; height: 200px sm:h-300;"></div>
                </div>

                <div class="bg-white rounded-lg shadow p-3 sm:p-4" id="renalChartContainer" style="display: none;">
                    <div class="flex justify-between items-center mb-2 sm:mb-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Renal Tests Trends</h3>
                        <button class="text-primary hover:text-primary/80">
                            <i class="ri-more-2-fill text-sm sm:text-base"></i>
                        </button>
                    </div>
                    <div id="renalChart" style="width: 100%; height: 200px sm:h-300;"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-3 sm:p-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Detailed Health Data</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fasting Blood Sugar</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post Meal Blood Sugar</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blood Pressure</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cholesterol</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urea</th>
                                <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creatinine</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="healthDataTable">
                            <?php if (!empty($previous_vitals) || !empty($previous_renals)): ?>
                                <?php foreach (array_merge($previous_vitals, $previous_renals) as $record): ?>
                                    <tr>
                                        <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm"><?php echo htmlspecialchars($record['record_date']); ?></td>
                                        <?php if (isset($record['systolic_bp'])): ?>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm"><?php echo htmlspecialchars($record['fasting_blood_sugar'] ?? 'N/A'); ?> mg/dL</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm"><?php echo htmlspecialchars($record['post_meal_blood_sugar'] ?? 'N/A'); ?> mg/dL</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm"><?php echo htmlspecialchars($record['systolic_bp'] ?? 'N/A') . '/' . htmlspecialchars($record['diastolic_bp'] ?? 'N/A'); ?> mmHg</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm"><?php echo htmlspecialchars($record['hdl_cholesterol'] ?? 'N/A') . '/' . htmlspecialchars($record['ldl_cholesterol'] ?? 'N/A'); ?> mg/dL</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm">-</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm">-</td>
                                        <?php else: ?>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm">-</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm">-</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm">-</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm">-</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm"><?php echo htmlspecialchars($record['urea'] ?? 'N/A'); ?> mg/dL</td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm"><?php echo htmlspecialchars($record['creatinine'] ?? 'N/A'); ?> mg/dL</td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="px-3 sm:px-6 py-2 sm:py-4 text-center text-gray-600 text-xs sm:text-sm">No previous data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="filterModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-4 sm:p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-2 sm:mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Filter Options</h3>
                <button onclick="toggleFilterModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="ri-close-line text-lg sm:text-xl"></i>
                </button>
            </div>
            <div class="space-y-2 sm:space-y-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <select id="dateRange" class="w-full border border-gray-200 rounded p-1 sm:p-2 text-xs sm:text-sm">
                        <option value="all">All</option>
                        <option value="last7">Last 7 days</option>
                        <option value="last30">Last 30 days</option>
                        <option value="last90">Last 3 months</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Data Types</label>
                    <div class="space-y-1 sm:space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" id="vitalFilter" class="w-4 h-4 rounded text-primary" checked>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm">Vital Statistics</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="renalFilter" class="w-4 h-4 rounded text-primary" checked>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm">Renal Tests</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 sm:space-x-3 mt-2 sm:mt-6">
                    <button onclick="toggleFilterModal()" class="px-2 sm:px-4 py-1 sm:py-2 border border-gray-200 rounded text-xs sm:text-sm text-gray-600">Cancel</button>
                    <button onclick="applyFilters()" class="px-2 sm:px-4 py-1 sm:py-2 bg-primary text-white !rounded-button text-xs sm:text-sm">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

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

        const searchInput = document.getElementById('searchInput');
        const dateFilter = document.getElementById('dateFilter');
        const filterBtn = document.getElementById('filterBtn');
        const exportBtn = document.getElementById('exportBtn');
        const mobileExportBtn = document.getElementById('mobileExportBtn');
        const healthDataTable = document.getElementById('healthDataTable');
        const filterModal = document.getElementById('filterModal');
        const dateRange = document.getElementById('dateRange');
        const vitalFilter = document.getElementById('vitalFilter');
        const renalFilter = document.getElementById('renalFilter');
        const categoryButtons = document.getElementById('categoryButtons').getElementsByTagName('button');
        const vitalChartContainer = document.getElementById('vitalChartContainer');
        const renalChartContainer = document.getElementById('renalChartContainer');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');

        function showToast(message) {
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        function toggleFilterModal() {
            filterModal.classList.toggle('hidden');
        }

        function applyFilters() {
            filterData();
            toggleFilterModal();
        }

        function filterData() {
            const searchTerm = searchInput.value.toLowerCase();
            const dateValue = dateFilter.value;
            const dateRangeValue = dateRange.value;
            const showVitals = vitalFilter.checked;
            const showRenals = renalFilter.checked;

            const rows = healthDataTable.getElementsByTagName('tr');
            for (let row of rows) {
                if (row.cells.length > 1) { // Skip header row
                    const dateCell = row.cells[0].textContent;
                    const isVital = row.cells[1].textContent.includes('mg/dL') && !row.cells[4].textContent.includes('-');
                    const matchesSearch = dateCell.toLowerCase().includes(searchTerm) || 
                                        row.cells[1].textContent.toLowerCase().includes(searchTerm) || 
                                        row.cells[4].textContent.toLowerCase().includes(searchTerm);
                    let matchesDate = true;

                    if (dateValue) {
                        matchesDate = dateCell.includes(dateValue.split('-').reverse().join('-'));
                    }

                    if (dateRangeValue !== 'all') {
                        const recordDate = new Date(dateCell);
                        const now = new Date();
                        let daysDiff;
                        switch (dateRangeValue) {
                            case 'last7': daysDiff = 7; break;
                            case 'last30': daysDiff = 30; break;
                            case 'last90': daysDiff = 90; break;
                        }
                        if (daysDiff) {
                            const diffMs = now - recordDate;
                            const diffDays = diffMs / (1000 * 60 * 60 * 24);
                            matchesDate = diffDays <= daysDiff;
                        }
                    }

                    const matchesFilter = (isVital && showVitals) || (!isVital && showRenals);

                    row.style.display = matchesSearch && matchesDate && matchesFilter ? 'table-row' : 'none';
                }
            }
        }

        // Category button functionality
        Array.from(categoryButtons).forEach(button => {
            button.addEventListener('click', function() {
                Array.from(categoryButtons).forEach(btn => {
                    btn.classList.remove('active', 'bg-primary', 'text-white');
                    btn.classList.add('bg-gray-100', 'text-gray-600');
                });
                this.classList.add('active', 'bg-primary', 'text-white');
                this.classList.remove('bg-gray-100', 'text-gray-600');

                const category = this.getAttribute('data-category');
                if (category === 'vital') {
                    vitalChartContainer.style.display = 'block';
                    renalChartContainer.style.display = 'none';
                } else {
                    vitalChartContainer.style.display = 'none';
                    renalChartContainer.style.display = 'block';
                }
                initializeCharts(category);
            });
        });

        // Initialize charts with real data
        function initializeCharts(category = 'vital') {
            const vitalChart = echarts.init(document.getElementById('vitalChart'));
            const renalChart = echarts.init(document.getElementById('renalChart'));

            const vitalDates = <?php echo json_encode(array_column($previous_vitals, 'record_date')); ?>;
            const renalDates = <?php echo json_encode(array_column($previous_renals, 'record_date')); ?>;
            const vitalData = <?php echo json_encode(array_column($previous_vitals, 'fasting_blood_sugar')); ?>;
            const renalData = <?php echo json_encode(array_column($previous_renals, 'creatinine')); ?>;

            const vitalOption = {
                animation: false,
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#eee',
                    borderWidth: 1,
                    textStyle: { color: '#1f2937', fontSize: 10 }
                },
                xAxis: {
                    type: 'category',
                    data: vitalDates.length ? vitalDates : ['No Data'],
                    axisLine: { lineStyle: { color: '#eee' } },
                    axisLabel: { fontSize: 8 }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { lineStyle: { color: '#eee' } },
                    axisLabel: { fontSize: 8 }
                },
                series: [{
                    name: 'Fasting Blood Sugar',
                    data: vitalData.map(v => parseFloat(v) || 0),
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
            };

            const renalOption = {
                animation: false,
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    borderColor: '#eee',
                    borderWidth: 1,
                    textStyle: { color: '#1f2937', fontSize: 10 }
                },
                xAxis: {
                    type: 'category',
                    data: renalDates.length ? renalDates : ['No Data'],
                    axisLine: { lineStyle: { color: '#eee' } },
                    axisLabel: { fontSize: 8 }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { lineStyle: { color: '#eee' } },
                    axisLabel: { fontSize: 8 }
                },
                series: [{
                    name: 'Creatinine',
                    data: renalData.map(v => parseFloat(v) || 0),
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
            };

            if (category === 'vital') {
                vitalChart.setOption(vitalOption);
                renalChart.clear();
                vitalChartContainer.style.display = 'block';
                renalChartContainer.style.display = 'none';
            } else {
                vitalChart.clear();
                renalChart.setOption(renalOption);
                vitalChartContainer.style.display = 'none';
                renalChartContainer.style.display = 'block';
            }

            window.addEventListener('resize', () => {
                vitalChart.resize();
                renalChart.resize();
            });
        }

        searchInput.addEventListener('input', filterData);
        dateFilter.addEventListener('change', filterData);
        filterBtn.addEventListener('click', toggleFilterModal);

        [exportBtn, mobileExportBtn].forEach(btn => btn.addEventListener('click', () => {
            const reportContent = `
                <h1 style="font-size: 24px; font-weight: bold; color: #2F4F2F; text-align: center;">GoCheck Health Report</h1>
                <h2 style="font-size: 18px; font-weight: bold; color: #333; margin-top: 20px;">Patient Information</h2>
                <p>Name: <?php echo htmlspecialchars($user['name']); ?></p>
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                <p>Contact: <?php echo htmlspecialchars($user['contact_number']); ?></p>
                <h2 style="font-size: 18px; font-weight: bold; color: #333; margin-top: 20px;">Latest Data</h2>
                <h3 style="font-size: 16px; font-weight: bold; color: #666;">Vital Statistics</h3>
                <p>Fasting Blood Sugar: <?php echo htmlspecialchars($latest_vital['fasting_blood_sugar'] ?? 'N/A'); ?> mg/dL</p>
                <p>Post Meal Blood Sugar: <?php echo htmlspecialchars($latest_vital['post_meal_blood_sugar'] ?? 'N/A'); ?> mg/dL</p>
                <p>Blood Pressure: <?php echo htmlspecialchars($latest_vital['systolic_bp'] ?? 'N/A') . '/' . htmlspecialchars($latest_vital['diastolic_bp'] ?? 'N/A'); ?> mmHg</p>
                <p>HDL Cholesterol: <?php echo htmlspecialchars($latest_vital['hdl_cholesterol'] ?? 'N/A'); ?> mg/dL</p>
                <p>LDL Cholesterol: <?php echo htmlspecialchars($latest_vital['ldl_cholesterol'] ?? 'N/A'); ?> mg/dL</p>
                <h3 style="font-size: 16px; font-weight: bold; color: #666;">Renal Tests</h3>
                <p>Urea: <?php echo htmlspecialchars($latest_renal['urea'] ?? 'N/A'); ?> mg/dL</p>
                <p>Creatinine: <?php echo htmlspecialchars($latest_renal['creatinine'] ?? 'N/A'); ?> mg/dL</p>
                <p>Uric Acid: <?php echo htmlspecialchars($latest_renal['uric_acid'] ?? 'N/A'); ?> mg/dL</p>
                <p>Calcium: <?php echo htmlspecialchars($latest_renal['calcium'] ?? 'N/A'); ?> mg/dL</p>
                <h2 style="font-size: 18px; font-weight: bold; color: #333; margin-top: 20px;">Previous Data</h2>
                <?php foreach (array_merge($previous_vitals, $previous_renals) as $record): ?>
                    <div style="margin-bottom: 15px; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px;">
                        <p style="font-size: 14px; color: #666;">Date: <?php echo htmlspecialchars($record['record_date']); ?></p>
                        <?php if (isset($record['systolic_bp'])): ?>
                            <p>Fasting Blood Sugar: <?php echo htmlspecialchars($record['fasting_blood_sugar'] ?? 'N/A'); ?> mg/dL</p>
                            <p>Post Meal Blood Sugar: <?php echo htmlspecialchars($record['post_meal_blood_sugar'] ?? 'N/A'); ?> mg/dL</p>
                            <p>Blood Pressure: <?php echo htmlspecialchars($record['systolic_bp'] ?? 'N/A') . '/' . htmlspecialchars($record['diastolic_bp'] ?? 'N/A'); ?> mmHg</p>
                            <p>HDL Cholesterol: <?php echo htmlspecialchars($record['hdl_cholesterol'] ?? 'N/A'); ?> mg/dL</p>
                            <p>LDL Cholesterol: <?php echo htmlspecialchars($record['ldl_cholesterol'] ?? 'N/A'); ?> mg/dL</p>
                        <?php else: ?>
                            <p>Urea: <?php echo htmlspecialchars($record['urea'] ?? 'N/A'); ?> mg/dL</p>
                            <p>Creatinine: <?php echo htmlspecialchars($record['creatinine'] ?? 'N/A'); ?> mg/dL</p>
                            <p>Uric Acid: <?php echo htmlspecialchars($record['uric_acid'] ?? 'N/A'); ?> mg/dL</p>
                            <p>Calcium: <?php echo htmlspecialchars($record['calcium'] ?? 'N/A'); ?> mg/dL</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            `;

            const blob = new Blob([reportContent], { type: 'text/html' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'health_report_' + <?php echo $user_id; ?> + '.html';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            showToast('Report exported successfully!');
        }));

        // Initial load
        initializeCharts('vital');
        filterData();

        // Ensure charts are initialized on page load
        window.addEventListener('load', () => {
            initializeCharts('vital');
        });
    </script>
</body>
</html>