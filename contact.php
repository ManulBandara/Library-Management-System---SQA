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

// Handle contact form submission
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message_text = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $message = "Error: All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Error: Invalid email format.";
    } else {
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $subject, $message_text);

        if ($stmt->execute()) {
            $message = "Message sent successfully! We'll get back to you soon.";
        } else {
            $message = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System - Contact</title>
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

        /* Contact Section */
        .contact-section {
            padding: 5rem 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 140px);
            background: linear-gradient(180deg, #ffffff, #f9fafb);
        }

        .contact-form {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 1s ease-out;
        }

        .contact-form h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1.5rem;
        }

        .contact-form input,
        .contact-form textarea {
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

        .contact-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .contact-form button {
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

        .contact-form button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .contact-form .message {
            margin-top: 1rem;
            font-size: 0.95rem;
            font-weight: 500;
            animation: slideUp 0.5s ease-out;
        }

        .contact-form .message.success {
            color: #22c55e;
        }

        .contact-form .message.error {
            color: #ef4444;
        }

        .contact-form .contact-info {
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6b7280;
        }

        .contact-form .contact-info a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .contact-form .contact-info a:hover {
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
            .contact-form {
                padding: 2rem;
                max-width: 90%;
            }

            .contact-form h2 {
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

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-form">
            <h2>Contact Us</h2>
            <form method="POST" action="contact.php">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <input type="text" name="subject" placeholder="Subject" required>
                <textarea name="message" placeholder="Your Message" required></textarea>
                <button type="submit">Send Message</button>
                <?php if ($message): ?>
                    <p class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                        <?php echo $message; ?>
                    </p>
                <?php endif; ?>
                <p class="contact-info">
                    Alternatively, reach us at <a href="mailto:support@librarysystem.com">support@librarysystem.com</a>
                </p>
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
        document.querySelectorAll('.contact-form input, .contact-form textarea').forEach(input => {
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