<?php
require 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $contact_number = trim($_POST["contact_number"]);
    $password = $_POST["password"];

    if (empty($name) || empty($email) || empty($contact_number) || empty($password)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $check_email = "SELECT id FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $check_email);
        
        if (mysqli_num_rows($result) > 0) {
            $error = "Email already registered!";
        } else {
            $sql = "INSERT INTO users (name, email, contact_number, password_hash) VALUES ('$name', '$email', '$contact_number', '$password_hash')";
            if (mysqli_query($conn, $sql)) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GoCheck</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#394D2B] text-white">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-[#DAC0A3] p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold text-[#394D2B] text-center">GoCheck - Register</h2>
            
            <?php if ($error): ?>
                <p class="text-red-600 text-sm text-center mt-2"><?php echo $error; ?></p>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <p class="text-green-600 text-sm text-center mt-2"><?php echo $success; ?></p>
            <?php endif; ?>

            <form action="register.php" method="POST" class="mt-4">
                <label class="block text-sm font-medium text-[#394D2B]">Full Name</label>
                <input type="text" name="name" class="w-full p-2 border border-gray-300 rounded-lg mt-1" required>
                
                <label class="block text-sm font-medium text-[#394D2B] mt-2">Email</label>
                <input type="email" name="email" class="w-full p-2 border border-gray-300 rounded-lg mt-1" required>

                <label class="block text-sm font-medium text-[#394D2B] mt-2">Contact Number</label>
                <input type="text" name="contact_number" class="w-full p-2 border border-gray-300 rounded-lg mt-1" required>

                <label class="block text-sm font-medium text-[#394D2B] mt-2">Password</label>
                <input type="password" name="password" class="w-full p-2 border border-gray-300 rounded-lg mt-1" required>

                <button type="submit" class="w-full bg-[#394D2B] text-white py-2 px-4 rounded-lg mt-4 hover:bg-green-700 transition">
                    Register
                </button>
            </form>

            <p class="text-center text-sm text-[#394D2B] mt-4">
                Already have an account? <a href="login.php" class="text-blue-600">Login here</a>.
            </p>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>
