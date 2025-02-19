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
    <title>GOCheck - Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4A5D4B',
                        secondary: '#C4B5A6'
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
<body class="min-h-screen bg-gray-50 flex flex-col">
    <header class="bg-primary py-6">
        <h1 class="text-white text-4xl text-center font-['Pacifico']">GOCheck</h1>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-2xl font-semibold text-primary mb-8 text-center">Create Your Account</h2>
                
                <form action="register.php" method="POST" class="space-y-6" id="registerForm">
                    <?php if ($error): ?>
                        <p class="text-red-600 text-sm text-center mb-4"><?php echo $error; ?></p>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <p class="text-green-600 text-sm text-center mb-4"><?php echo $success; ?></p>
                    <?php endif; ?>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-user-line text-gray-400 w-5 h-5 flex items-center justify-center"></i>
                        </div>
                        <input type="text" name="name" required class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm" placeholder="Full Name">
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-mail-line text-gray-400 w-5 h-5 flex items-center justify-center"></i>
                        </div>
                        <input type="email" name="email" required class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm" placeholder="Enter your email">
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-phone-line text-gray-400 w-5 h-5 flex items-center justify-center"></i>
                        </div>
                        <input type="text" name="contact_number" required class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm" placeholder="Contact Number">
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-lock-line text-gray-400 w-5 h-5 flex items-center justify-center"></i>
                        </div>
                        <input type="password" name="password" required class="block w-full pl-10 pr-10 py-3 border border-gray-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm" placeholder="Enter your password">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="ri-eye-line text-gray-400 w-5 h-5 flex items-center justify-center"></i>
                        </button>
                    </div>

                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-button hover:bg-primary/90 transition-colors duration-200 !rounded-button whitespace-nowrap font-medium">
                        Register
                    </button>

                    <div class="flex items-center justify-between text-sm">
                        <a href="#" class="text-primary hover:underline">Forgot password?</a>
                        <a href="login.php" class="text-primary hover:underline">Already have an account?</a>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-600">
                        Or continue with
                    </p>
                    <div class="mt-4 flex gap-4 justify-center">
                        <button class="flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 hover:border-primary transition-colors duration-200">
                            <i class="ri-google-fill text-xl"></i>
                        </button>
                        <button class="flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 hover:border-primary transition-colors duration-200">
                            <i class="ri-apple-fill text-xl"></i>
                        </button>
                        <button class="flex items-center justify-center w-12 h-12 rounded-full border border-gray-200 hover:border-primary transition-colors duration-200">
                            <i class="ri-github-fill text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="notification" class="fixed top-4 right-4 bg-white rounded-lg shadow-lg p-4 transform translate-x-full transition-transform duration-300 flex items-center gap-3">
        <i class="ri-checkbox-circle-line text-green-500 text-xl"></i>
        <span class="text-sm font-medium">Successfully registered!</span>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('togglePassword')?.closest('div').querySelector('input[type="password"]');
        const notification = document.getElementById('notification');

        togglePassword.addEventListener('click', function() {
            if (passwordInput) {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                togglePassword.innerHTML = type === 'password' ? 
                    '<i class="ri-eye-line text-gray-400 w-5 h-5 flex items-center justify-center"></i>' : 
                    '<i class="ri-eye-off-line text-gray-400 w-5 h-5 flex items-center justify-center"></i>';
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.querySelector('input[name="name"]').value;
            const email = document.querySelector('input[name="email"]').value;
            const contact_number = document.querySelector('input[name="contact_number"]').value;
            const password = document.querySelector('input[name="password"]').value;

            if (!name || !email || !contact_number || !password) {
                return;
            }

            // Submit the form if JavaScript is enabled
            this.submit();
            
            // Show notification on success (you can customize this based on PHP response)
            if ('<?php echo $success; ?>' !== '') {
                notification.style.transform = 'translateX(0)';
                setTimeout(() => {
                    notification.style.transform = 'translateX(100%)';
                }, 3000);
            }
        });
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>