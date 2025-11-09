<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | TravelGO Orbit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #111827;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center">
    <!-- Login Card -->
    <div class="login-card rounded-xl p-8 w-full max-w-md shadow-2xl">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-white mb-2">Welcome Back</h2>
            <p class="text-blue-200">Sign in to your TravelGO account</p>
        </div>

        <!-- Error Message -->
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-sm">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="auth.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="login">

            <div>
                <label for="login" class="block text-white text-sm font-medium mb-1">Email or Username</label>
                <div class="relative">
                    <i data-feather="user" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                    <input type="text" id="login" name="login" required
                        class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 placeholder-gray-500">
                </div>
            </div>

            <div>
                <label for="password" class="block text-white text-sm font-medium mb-1">Password</label>
                <div class="relative">
                    <i data-feather="lock" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-300"></i>
                    <input type="password" id="password" name="password" required
                        class="w-full pl-10 pr-4 py-3 rounded-lg bg-white/90 focus:outline-none focus:ring-2 focus:ring-blue-300 text-gray-800 placeholder-gray-500">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="mr-2">
                    <label for="remember" class="text-white text-sm">Remember me</label>
                </div>
                <a href="forget_password.php" class="text-blue-300 text-sm hover:text-white transition-colors">Forgot password?</a>
            </div>

            <button type="submit"
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-blue-900 font-bold py-3 px-4 rounded-lg transition-colors mt-4">
                Sign In
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-blue-200">Don't have an account?
                <a href="register.php" class="text-white font-medium hover:text-yellow-400 transition-colors">Sign up</a>
            </p>
        </div>
    </div>

    <script>
        feather.replace();
    </script>
</body>

</html>