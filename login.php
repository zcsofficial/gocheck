<?php
require 'config.php';
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required!";
    } else {
        $sql = "SELECT id, name, password_hash, is_first_login, profile_completed FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];

                // Check if it's the user's first login
                if ($row['is_first_login'] == 1) {
                    // Update first login status
                    $update_sql = "UPDATE users SET is_first_login = 0 WHERE id = " . $row['id'];
                    mysqli_query($conn, $update_sql);
                    header("Location: setup.php");
                    exit();
                }

                // Check if profile is completed
                if ($row['profile_completed'] == 0) {
                    header("Location: setup.php"); // Stay on setup page until profile is complete
                    exit();
                }

                // Redirect to dashboard if everything is completed
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password!";
            }
        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GoCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#394D2B] text-white">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-[#DAC0A3] p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold text-[#394D2B] text-center">GoCheck - Login</h2>
            
            <?php if ($error): ?>
                <p class="text-red-600 text-sm text-center mt-2"><?php echo $error; ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST" class="mt-4">
                <label class="block text-sm font-medium text-[#394D2B]">Email</label>
                <input type="email" name="email" class="w-full p-2 border border-gray-300 rounded-lg mt-1" required>

                <label class="block text-sm font-medium text-[#394D2B] mt-2">Password</label>
                <input type="password" name="password" class="w-full p-2 border border-gray-300 rounded-lg mt-1" required>

                <button type="submit" class="w-full bg-[#394D2B] text-white py-2 px-4 rounded-lg mt-4 hover:bg-green-700 transition">
                    Login
                </button>
            </form>

            <p class="text-center text-sm text-[#394D2B] mt-4">
                Don't have an account? <a href="register.php" class="text-blue-600">Register here</a>.
            </p>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>
