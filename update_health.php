<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get POST data with defaults and validation
$fields = [
    'fasting_blood_sugar' => ['type' => 'float', 'required' => true, 'min' => 0],
    'post_meal_blood_sugar' => ['type' => 'float', 'required' => true, 'min' => 0],
    'systolic_bp' => ['type' => 'int', 'required' => true, 'min' => 1],
    'diastolic_bp' => ['type' => 'int', 'required' => true, 'min' => 1],
    'hdl_cholesterol' => ['type' => 'float', 'required' => true, 'min' => 0],
    'ldl_cholesterol' => ['type' => 'float', 'required' => true, 'min' => 0],
    'urea' => ['type' => 'float', 'required' => true, 'min' => 7, 'max' => 20],
    'creatinine' => ['type' => 'float', 'required' => true, 'min' => 0.7, 'max' => 1.3],
    'uric_acid' => ['type' => 'float', 'required' => true, 'min' => 3.5, 'max' => 7.2],
    'calcium' => ['type' => 'float', 'required' => true, 'min' => 8.5, 'max' => 10.5]
];

$errors = [];
$data = [];

foreach ($fields as $field => $config) {
    $value = isset($_POST[$field]) ? trim($_POST[$field]) : null;

    if ($config['required'] && $value === null) {
        $errors[] = "$field is required.";
        continue;
    }

    if ($value !== null) {
        switch ($config['type']) {
            case 'int':
                $value = filter_var($value, FILTER_VALIDATE_INT);
                if ($value === false || ($config['min'] && $value < $config['min'])) {
                    $errors[] = "$field must be a valid integer greater than or equal to " . $config['min'] . ".";
                }
                break;
            case 'float':
                $value = filter_var($value, FILTER_VALIDATE_FLOAT);
                if ($value === false) {
                    $errors[] = "$field must be a valid number.";
                } elseif ($config['min'] && $value < $config['min']) {
                    $errors[] = "$field must be at least " . $config['min'] . ".";
                } elseif (isset($config['max']) && $value > $config['max']) {
                    $errors[] = "$field must not exceed " . $config['max'] . ".";
                }
                break;
        }
        $data[$field] = $value;
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit();
}

$conn->begin_transaction();

try {
    // Update or insert into vital_statistics
    $stmt = $conn->prepare("INSERT INTO vital_statistics (user_id, systolic_bp, diastolic_bp, hdl_cholesterol, ldl_cholesterol, fasting_blood_sugar, post_meal_blood_sugar) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE systolic_bp = VALUES(systolic_bp), 
                                                    diastolic_bp = VALUES(diastolic_bp), 
                                                    hdl_cholesterol = VALUES(hdl_cholesterol), 
                                                    ldl_cholesterol = VALUES(ldl_cholesterol), 
                                                    fasting_blood_sugar = VALUES(fasting_blood_sugar), 
                                                    post_meal_blood_sugar = VALUES(post_meal_blood_sugar)");
    $stmt->bind_param("iiidddd", $user_id, $data['systolic_bp'], $data['diastolic_bp'], $data['hdl_cholesterol'], $data['ldl_cholesterol'], $data['fasting_blood_sugar'], $data['post_meal_blood_sugar']);
    $stmt->execute();
    $stmt->close();

    // Update or insert into renal_tests
    $stmt = $conn->prepare("INSERT INTO renal_tests (user_id, urea, creatinine, uric_acid, calcium) 
                            VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE urea = VALUES(urea), 
                                                    creatinine = VALUES(creatinine), 
                                                    uric_acid = VALUES(uric_acid), 
                                                    calcium = VALUES(calcium)");
    $stmt->bind_param("idddd", $user_id, $data['urea'], $data['creatinine'], $data['uric_acid'], $data['calcium']);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Health data updated successfully!']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
exit();
?>
