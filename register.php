<?php
// Start the session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle registration form submission
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username or email already exists
    $check_sql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $message = "Error: Username or email already exists.";
    } else {
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        if ($conn->query($sql) === TRUE) {
            header("Location: login.php");
            exit();
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: #f9fafb;
            overflow-x: hidden;
        }

        /* Header Styles */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3748;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        nav ul li a {
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }

        .user-actions a {
            color: #ffffff;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin-left: 1rem;
            transition: all 0.3s ease;
        }

        .user-actions a:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        /* Register Section */
        .register-section {
            padding: 5rem 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 140px);
            background: linear-gradient(180deg, #ffffff, #f9fafb);
        }

        .register-form {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 1s ease-out;
        }

        .register-form h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1.5rem;
        }

        .register-form input {
            width: 100%;
            padding: 0.8rem 1.5rem;
            margin: 0.75rem 0;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .register-form input:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .register-form button {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            border: none;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .register-form button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .register-form .message {
            color: #ef4444;
            margin-top: 1rem;
            font-size: 0.95rem;
            font-weight: 500;
            animation: slideUp 0.5s ease-out;
        }

        .register-form .login-link {
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6b7280;
        }

        .register-form .login-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .register-form .login-link a:hover {
            color: #8b5cf6;
        }

        /* Footer Styles */
        footer {
            background: #2d3748;
            color: #ffffff;
            text-align: center;
            padding: 2rem;
        }

        footer p {
            font-size: 0.95rem;
            opacity: 0.8;
        }

        /* Animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-form {
                padding: 2rem;
                max-width: 90%;
            }

            .register-form h2 {
                font-size: 1.75rem;
            }

            header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            nav ul {
                flex-direction: column;
                gap: 0.5rem;
            }

            .user-actions {
                display: flex;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">LIBRARY</div>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="search.php">Book Search</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </nav>
        <div class="user-actions">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </header>

    <!-- Register Section -->
    <section class="register-section">
        <div class="register-form">
            <h2>Create Account</h2>
            <form method="POST" action="register.php">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Register</button>
                <?php if ($message): ?>
                    <p class="message"><?php echo $message; ?></p>
                <?php endif; ?>
                <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>© 2025 Library System. All rights reserved.</p>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Form input animation
        document.querySelectorAll('.register-form input').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', () => {
                if (!input.value) {
                    input.parentElement.classList.remove('focused');
                }
            });
        });
    </script>

<?php $conn->close(); ?>
</body>
</html>