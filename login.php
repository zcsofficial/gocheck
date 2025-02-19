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
    <title>GOCheck - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        @media (max-width: 640px) {
            .text-4xl { font-size: 2rem; }
            .text-2xl { font-size: 1.5rem; }
            .p-8 { padding: 1rem; }
            .py-6 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
            .py-3 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .px-10 { padding-left: 1rem; padding-right: 1rem; }
            .w-5 { width: 1rem; }
            .h-5 { height: 1rem; }
            .text-sm { font-size: 0.875rem; }
            .space-y-6 { gap: 0.75rem; }
            .border-t { border-top-width: 1px; }
            .mt-8 { margin-top: 1rem; }
            .pt-6 { padding-top: 0.75rem; }
            .w-12 { width: 3rem; }
            .h-12 { height: 3rem; }
            .text-xl { font-size: 1.25rem; }
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
<body class="min-h-screen bg-gray-50 flex flex-col">
    <header class="bg-primary py-4 sm:py-6">
        <h1 class="text-white text-3xl sm:text-4xl text-center font-['Pacifico']">GOCheck</h1>
    </header>

    <main class="flex-1 flex items-center justify-center px-2 sm:px-4 py-6 sm:py-12">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-lg shadow-xl p-4 sm:p-8">
                <h2 class="text-xl sm:text-2xl font-semibold text-primary mb-4 sm:mb-8 text-center">Welcome Back</h2>
                
                <form action="login.php" method="POST" class="space-y-4 sm:space-y-6" id="loginForm">
                    <?php if ($error): ?>
                        <p class="text-red-600 text-xs sm:text-sm text-center mb-2 sm:mb-4"><?php echo $error; ?></p>
                    <?php endif; ?>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-2 sm:pl-3 flex items-center pointer-events-none">
                            <i class="ri-mail-line text-gray-400 w-4 sm:w-5 h-4 sm:h-5 flex items-center justify-center"></i>
                        </div>
                        <input type="email" name="email" required class="block w-full pl-7 sm:pl-10 pr-3 sm:pr-10 py-2 sm:py-3 border border-gray-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-xs sm:text-sm" placeholder="Enter your email">
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-2 sm:pl-3 flex items-center pointer-events-none">
                            <i class="ri-lock-line text-gray-400 w-4 sm:w-5 h-4 sm:h-5 flex items-center justify-center"></i>
                        </div>
                        <input type="password" name="password" required class="block w-full pl-7 sm:pl-10 pr-7 sm:pr-10 py-2 sm:py-3 border border-gray-200 rounded-lg focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-xs sm:text-sm" placeholder="Enter your password">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-2 sm:pr-3 flex items-center">
                            <i class="ri-eye-line text-gray-400 w-4 sm:w-5 h-4 sm:h-5 flex items-center justify-center"></i>
                        </button>
                    </div>

                    <button type="submit" class="w-full bg-primary text-white py-2 sm:py-3 rounded-button hover:bg-primary/90 transition-colors duration-200 !rounded-button whitespace-nowrap font-medium text-xs sm:text-sm">
                        Sign In
                    </button>

                    <div class="flex flex-col sm:flex-row items-center justify-between text-xs sm:text-sm">
                        <a href="#" class="text-primary hover:underline mb-2 sm:mb-0">Forgot password?</a>
                        <a href="register.php" class="text-primary hover:underline">Create account</a>
                    </div>
                </form>

                <div class="mt-4 sm:mt-8 pt-4 sm:pt-6 border-t border-gray-200 text-center">
                    <p class="text-xs sm:text-sm text-gray-600">
                        Or continue with
                    </p>
                    <div class="mt-2 sm:mt-4 flex gap-2 sm:gap-4 justify-center">
                        <button class="flex items-center justify-center w-8 sm:w-12 h-8 sm:h-12 rounded-full border border-gray-200 hover:border-primary transition-colors duration-200">
                            <i class="ri-google-fill text-sm sm:text-xl"></i>
                        </button>
                        <button class="flex items-center justify-center w-8 sm:w-12 h-8 sm:h-12 rounded-full border border-gray-200 hover:border-primary transition-colors duration-200">
                            <i class="ri-apple-fill text-sm sm:text-xl"></i>
                        </button>
                        <button class="flex items-center justify-center w-8 sm:w-12 h-8 sm:h-12 rounded-full border border-gray-200 hover:border-primary transition-colors duration-200">
                            <i class="ri-github-fill text-sm sm:text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="notification" class="fixed top-4 right-4 bg-white rounded-lg shadow-lg p-2 sm:p-4 transform translate-x-full transition-transform duration-300 flex items-center gap-2 sm:gap-3">
        <i class="ri-checkbox-circle-line text-green-500 text-sm sm:text-xl"></i>
        <span class="text-xs sm:text-sm font-medium">Successfully logged in!</span>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.querySelector('input[name="password"]');
        const notification = document.getElementById('notification');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.innerHTML = type === 'password' ? 
                '<i class="ri-eye-line text-gray-400 w-4 sm:w-5 h-4 sm:h-5 flex items-center justify-center"></i>' : 
                '<i class="ri-eye-off-line text-gray-400 w-4 sm:w-5 h-4 sm:h-5 flex items-center justify-center"></i>';
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;

            if (!email || !password) {
                return;
            }

            // Submit the form if JavaScript is enabled
            this.submit();
            
            // Show notification (you can customize this based on your PHP redirect)
            notification.style.transform = 'translateX(0)';
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
            }, 3000);
        });
    </script>
</body>
</html>

<?php mysqli_close($conn); ?>