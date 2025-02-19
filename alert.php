<?php
// No session_start() here - parent file handles it
if (!isset($_SESSION['user_id'])) {
    die('User not logged in');
}

$user_id = $_SESSION['user_id'];

// Fetch user email and name for sending notifications
$query = "SELECT email, name FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$user_email = $user['email'];
$user_name = htmlspecialchars($user['name']);

// Fetch latest vital statistics
$query = "SELECT systolic_bp, diastolic_bp, hdl_cholesterol, ldl_cholesterol, fasting_blood_sugar, post_meal_blood_sugar 
          FROM vital_statistics WHERE user_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$vital_result = $stmt->get_result();
$vital = $vital_result->fetch_assoc();

// Fetch latest renal tests
$query = "SELECT urea, creatinine, uric_acid, calcium 
          FROM renal_tests WHERE user_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$renal_result = $stmt->get_result();
$renal = $renal_result->fetch_assoc();

// Define threshold limits for alerts
$thresholds = [
    'systolic_bp' => ['min' => 90, 'max' => 120, 'message' => 'High blood pressure detected'],
    'diastolic_bp' => ['min' => 60, 'max' => 80, 'message' => 'High diastolic blood pressure detected'],
    'fasting_blood_sugar' => ['max' => 100, 'message' => 'High fasting blood sugar detected'],
    'post_meal_blood_sugar' => ['max' => 140, 'message' => 'High post-meal blood sugar detected'],
    'hdl_cholesterol' => ['min' => 40, 'message' => 'Low HDL cholesterol detected'],
    'ldl_cholesterol' => ['max' => 130, 'message' => 'High LDL cholesterol detected'],
    'urea' => ['min' => 7, 'max' => 20, 'message' => 'Abnormal urea levels detected'],
    'creatinine' => ['min' => 0.7, 'max' => 1.3, 'message' => 'Abnormal creatinine levels detected'],
    'uric_acid' => ['min' => 3.5, 'max' => 7.2, 'message' => 'Abnormal uric acid levels detected'],
    'calcium' => ['min' => 8.5, 'max' => 10.5, 'message' => 'Abnormal calcium levels detected']
];

// Check for alerts and create notifications
$notifications = [];
foreach ($thresholds as $field => $limits) {
    if (isset($vital[$field]) || isset($renal[$field])) {
        $value = $vital[$field] ?? $renal[$field];
        if ($value !== null) {
            $alert = false;
            if (isset($limits['min']) && $value < $limits['min']) {
                $alert = true;
            }
            if (isset($limits['max']) && $value > $limits['max']) {
                $alert = true;
            }

            if ($alert) {
                $message = $limits['message'] . ": " . $value . " " . ($field === 'systolic_bp' || $field === 'diastolic_bp' ? 'mmHg' : 'mg/dL');
                $notifications[] = $message;

                // Check if notification already exists
                $check_query = "SELECT id FROM notifications WHERE user_id = ? AND message = ? ORDER BY date DESC LIMIT 1";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("is", $user_id, $message);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows == 0) {
                    // Insert new notification
                    $insert_query = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("is", $user_id, $message);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }

                $check_stmt->close();
            }
        }
    }
}

// Send email if there are notifications
if (!empty($notifications)) {
    $alerts_list = '';
    foreach ($notifications as $alert) {
        $alerts_list .= '<li style="color: #666666; font-size: 14px; margin: 0 0 10px; padding-left: 20px;">' . htmlspecialchars($alert) . '</li>';
    }

    $email_body = "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Health Alert from GoCheck</title></head><body style='margin: 0; padding: 0; font-family: Arial, sans-serif; line-height: 1.6; background-color: #f5f5f5;'>
        <table role='presentation' border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
            <tr>
                <td style='padding: 20px;'>
                    <table role='presentation' border='0' cellpadding='0' cellspacing='0' width='100%'>
                        <tr>
                            <td style='text-align: center; padding-bottom: 20px;'>
                                <h1 style='color: #2F4F2F; font-size: 24px; margin: 0;'>GoCheck Health Alert</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 20px 0;'>
                                <p style='color: #333333; font-size: 16px; margin: 0 0 10px;'>Dear " . $user_name . ",</p>
                                <p style='color: #666666; font-size: 14px; margin: 0 0 20px;'>We have detected the following health alerts based on your recent data. Please review your health metrics and consult a healthcare provider if necessary.</p>
                                <ul style='list-style-type: none; padding: 0; margin: 0;'>" . $alerts_list . "</ul>
                                <p style='color: #666666; font-size: 14px; margin: 20px 0 0;'><a href='http://yourdomain.com' style='color: #2F4F2F; text-decoration: underline;'>Log in to your GoCheck account</a> for more details.</p>
                                <p style='color: #666666; font-size: 14px; margin: 10px 0 0;'>Best regards,<br>GoCheck Team</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='background-color: #F0F8F0; padding: 15px; text-align: center; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;'>
                                <p style='color: #2F4F2F; font-size: 12px; margin: 0;'>This is an automated message. Please do not reply directly to this email.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body></html>";

    $email_subject = "Health Alert from GoCheck";
    $headers = "From: noreply@gocheck.com\r\n";
    $headers .= "Reply-To: noreply@gocheck.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (mail($user_email, $email_subject, $email_body, $headers)) {
        error_log("Email sent to " . $user_email);
    } else {
        error_log("Failed to send email to " . $user_email);
    }
}
?>
