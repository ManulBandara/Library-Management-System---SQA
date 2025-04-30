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

// Handle borrowing
$borrow_message = '';
if (isset($_POST['borrow_book'])) {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }

    $book_id = $_POST['book_id'];
    $username = $_SESSION['username'];
    $user_sql = "SELECT id FROM users WHERE username = '$username'";
    $user_result = $conn->query($user_sql);
    $user = $user_result->fetch_assoc();
    $user_id = $user['id'];

    $borrow_sql = "INSERT INTO borrowed_books (user_id, book_id) VALUES ('$user_id', '$book_id')";
    if ($conn->query($borrow_sql) === TRUE) {
        $borrow_message = "Book borrowed successfully!";
    } else {
        $borrow_message = "Error borrowing book: " . $conn->error;
    }
}

// Fetch books from the database with category filtering
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$search_query = isset($_GET['query']) ? $_GET['query'] : '';
$sql = "SELECT * FROM books";
$conditions = [];
if ($search_query) {
    $conditions[] = "(title LIKE '%$search_query%' OR author LIKE '%$search_query%')";
}
if ($category !== 'all') {
    $conditions[] = "category = '$category'";
}
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System - Book Search</title>
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

        /* Search Section */
        .search-section {
            padding: 5rem 2rem;
            text-align: center;
            background: linear-gradient(180deg, #ffffff, #f9fafb);
        }

        .search-section h2 {
            font-size: 2.25rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 2rem;
            animation: slideUp 1s ease-out;
        }

        /* Category Filters */
        .category-filters {
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .category-filter {
            padding: 0.6rem 1.5rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #2d3748;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category-filter:hover {
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .category-filter.active {
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            color: #ffffff;
        }

        .search-form {
            margin-bottom: 3rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-form input[type="text"] {
            padding: 0.8rem 1.5rem;
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .search-form input[type="text"]:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }

        .search-form button {
            padding: 0.8rem 2rem;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            border: none;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .search-form button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        /* Book Grid */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .book-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .book-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .book-card:hover::before {
            opacity: 1;
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .book-card img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .book-card:hover img {
            transform: scale(1.03);
        }

        .book-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .book-card .price {
            color: #3b82f6;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .book-card p {
            font-size: 0.95rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .book-card input[type="checkbox"] {
            margin-top: 0.5rem;
            accent-color: #3b82f6;
            transform: scale(1.2);
        }

        .borrow-btn {
            margin-top: 1rem;
            padding: 0.6rem 1.5rem;
            background: linear-gradient(45deg, #22c55e, #4ade80);
            border: none;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .borrow-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }

        .borrow-message {
            color: #22c55e;
            text-align: center;
            margin: 2rem 0;
            font-size: 1.1rem;
            font-weight: 500;
            animation: slideUp 0.5s ease-out;
        }

        /* Calculation Section */
        .calculation {
            margin: 3rem 0;
            text-align: center;
        }

        .calculation button {
            padding: 0.8rem 2rem;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            border: none;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .calculation button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .calculation p {
            margin-top: 1rem;
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
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
            .search-section h2 {
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

            .search-form {
                flex-direction: column;
                align-items: center;
            }

            .search-form input[type="text"] {
                max-width: 100%;
            }

            .category-filters {
                flex-direction: column;
                align-items: center;
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
            </ul>
        </nav>
        <div class="user-actions">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </header>

    <!-- Search Section -->
    <section class="search-section">
        <h2>Find Your Next Book</h2>
        <div class="category-filters">
            <button class="category-filter <?php echo $category === 'all' ? 'active' : ''; ?>" onclick="window.location.href='search.php?category=all'">All</button>
            <button class="category-filter <?php echo $category === 'Programming' ? 'active' : ''; ?>" onclick="window.location.href='search.php?category=Programming'">Programming</button>
            <button class="category-filter <?php echo $category === 'Fiction' ? 'active' : ''; ?>" onclick="window.location.href='search.php?category=Fiction'">Fiction</button>
            <button class="category-filter <?php echo $category === 'Non-Fiction' ? 'active' : ''; ?>" onclick="window.location.href='search.php?category=Non-Fiction'">Non-Fiction</button>
        </div>
        <form class="search-form" method="GET" action="search.php">
            <input type="text" name="query" placeholder="Search by title or author..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>

        <div class="book-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="book-card" data-category="<?php echo $row['category']; ?>">
                        <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['title']; ?>" data-src="<?php echo $row['image_url']; ?>">
                        <h3><?php echo $row['title']; ?></h3>
                        <div class="price">$<?php echo $row['price']; ?></div>
                        <p><?php echo $row['author']; ?></p>
                        <input type="checkbox" class="book-select" data-price="<?php echo $row['price']; ?>">
                        <form method="POST" action="search.php">
                            <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="borrow_book" class="borrow-btn">Borrow</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No books found.</p>
            <?php endif; ?>
        </div>

        <!-- Display borrow message if set -->
        <?php if ($borrow_message): ?>
            <p class="borrow-message"><?php echo $borrow_message; ?></p>
        <?php endif; ?>

        <!-- Calculation Section -->
        <div class="calculation">
            <button onclick="calculateTotal()">Calculate Total Cost</button>
            <p>Total: $<span id="total-cost">0</span></p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>© 2025 Library System. All rights reserved.</p>
    </footer>

    <script>
        function calculateTotal() {
            const checkboxes = document.querySelectorAll('.book-select:checked');
            let total = 0;
            checkboxes.forEach(checkbox => {
                total += parseFloat(checkbox.getAttribute('data-price'));
            });
            document.getElementById('total-cost').textContent = total.toFixed(2);
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Lazy load images
        document.addEventListener('DOMContentLoaded', () => {
            const images = document.querySelectorAll('img');
            const options = {
                rootMargin: '100px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        observer.unobserve(img);
                    }
                });
            }, options);

            images.forEach(img => {
                img.dataset.src = img.src;
                img.src = '';
                observer.observe(img);
            });
        });
    </script>

<?php $conn->close(); ?>
</body>
</html>