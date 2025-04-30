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

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get user ID and details
$username = $_SESSION['username'];
$user_sql = "SELECT id, username, email FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_id = $user['id'];
$user_stmt->close();

// Handle profile update
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);

    // Validate inputs
    $errors = [];
    if (empty($new_username) || strlen($new_username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Check if username is already in use
    $check_username_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
    $check_username_stmt = $conn->prepare($check_username_sql);
    $check_username_stmt->bind_param("si", $new_username, $user_id);
    $check_username_stmt->execute();
    $check_username_result = $check_username_stmt->get_result();
    if ($check_username_result->num_rows > 0) {
        $errors[] = "Username is already in use.";
    }
    $check_username_stmt->close();

    // Check if email is already in use
    $check_email_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("si", $new_email, $user_id);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    if ($check_email_result->num_rows > 0) {
        $errors[] = "Email is already in use.";
    }
    $check_email_stmt->close();

    if (empty($errors)) {
        $update_sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
        if ($update_stmt->execute()) {
            $message = "Profile updated successfully!";
            $_SESSION['username'] = $new_username; // Update session
            $user['username'] = $new_username;
            $user['email'] = $new_email;
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
        $update_stmt->close();
    } else {
        $message = implode(" ", $errors);
    }
}

// Handle logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Fetch borrowing history
$history_sql = "SELECT b.title, b.author, bb.borrow_date 
                FROM borrowed_books bb 
                JOIN books b ON bb.book_id = b.id 
                WHERE bb.user_id = ? 
                ORDER BY bb.borrow_date DESC";
$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System - Profile</title>
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

        .user-actions a, .logout-btn {
            color: #ffffff;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin-left: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .user-actions a:hover, .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        /* Profile Section */
        .profile-section {
            padding: 5rem 2rem;
            max-width: 800px;
            margin: 0 auto;
            background: linear-gradient(180deg, #ffffff, #f9fafb);
        }

        .profile-section h2 {
            font-size: 2.25rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
            text-align: center;
        }

        .profile-form {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 1s ease-out;
        }

        .profile-form label {
            display: block;
            font-size: 1rem;
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .profile-form input {
            width: 100%;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .profile-form input:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .profile-form button {
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

        .profile-form button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .message {
            text-align: center;
            margin: 1rem 0;
            font-size: 1.1rem;
            font-weight: 500;
            animation: slideUp 0.5s ease-out;
        }

        .message.success {
            color: #22c55e;
        }

        .message.error {
            color: #ef4444;
        }

        /* Borrowing History */
        .history-section {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 1s ease-out;
        }

        .history-section h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1.5rem;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th,
        .history-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .history-table th {
            font-weight: 600;
            color: #2d3748;
            background: rgba(59, 130, 246, 0.1);
        }

        .history-table td {
            color: #4b5563;
        }

        .history-table tr:hover {
            background: rgba(59, 130, 246, 0.05);
        }

        /* Footer Styles */
        footer {
            background: #2d3748;
            color: #ffffff;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
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
            .profile-section {
                padding: 3rem 1rem;
            }

            .profile-form,
            .history-section {
                padding: 1.5rem;
            }

            .history-table th,
            .history-table td {
                font-size: 0.9rem;
                padding: 0.5rem;
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
            <form method="POST" action="profile.php">
                <button type="submit" name="logout" class="logout-btn">Logout</button>
            </form>
        </div>
    </header>

    <!-- Profile Section -->
    <section class="profile-section">
        <h2>Your Profile</h2>
        <div class="profile-form">
            <form method="POST" action="profile.php">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <button type="submit" name="update_profile">Update Profile</button>
            </form>
            <?php if ($message): ?>
                <p class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="history-section">
            <h3>Borrowing History</h3>
            <?php if ($history_result->num_rows > 0): ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Borrow Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $history_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                </table>
            <?php else: ?>
                <p>No borrowing history found.</p>
            <?php endif; ?>
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
        document.querySelectorAll('.profile-form input').forEach(input => {
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

<?php 
$history_stmt->close();
$conn->close(); 
?>
</body>
</html>