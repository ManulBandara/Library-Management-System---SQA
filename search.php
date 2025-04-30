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
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f4f4f4;
        }

        /* Header Styles */
        header {
            background-color: #f5a623;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: #333;
        }

        .user-actions a {
            color: #fff;
            margin-left: 15px;
            text-decoration: none;
            font-weight: bold;
        }

        /* Search Section */
        .search-section {
            padding: 40px 20px;
            text-align: center;
            animation: fadeIn 2s ease-in-out;
        }

        .search-section h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }

        /* Category Filters */
        .category-filters {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .category-filter {
            padding: 10px 20px;
            background-color: #f5a623;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .category-filter:hover {
            transform: scale(1.05);
        }

        .search-form {
            margin-bottom: 30px;
        }

        .search-form input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .search-form button {
            padding: 10px 20px;
            background-color: #f5a623;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }

        .search-form button:hover {
            transform: scale(1.1);
        }

        /* Book Grid */
        .book-grid {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .book-card {
            background-color: #fff;
            width: 200px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-10px);
        }

        .book-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }

        .book-card h3 {
            font-size: 18px;
            margin: 10px 0;
        }

        .book-card .price {
            color: #f5a623;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .book-card input[type="checkbox"] {
            margin-top: 10px;
        }

        .borrow-btn {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #28a745;
            border: none;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }

        .borrow-btn:hover {
            transform: scale(1.05);
        }

        .borrow-message {
            color: green;
            text-align: center;
            margin-top: 10px;
            font-size: 16px;
        }

        /* Calculation Section */
        .calculation {
            margin: 30px 0;
            text-align: center;
        }

        .calculation button {
            padding: 10px 20px;
            background-color: #f5a623;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }

        .calculation button:hover {
            transform: scale(1.1);
        }

        .calculation p {
            margin-top: 10px;
            font-size: 18px;
            font-weight: bold;
        }

        /* Footer Styles */
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 20px;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        footer p {
            margin: 0;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
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
            </ul>
        </nav>
        <div class="user-actions">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </header>

    <!-- Search Section -->
    <section class="search-section">
        <h2>Search Books</h2>
        <div class="category-filters">
            <button class="category-filter" onclick="window.location.href='search.php?category=all'">All</button>
            <button class="category-filter" onclick="window.location.href='search.php?category=Programming'">Programming</button>
            <button class="category-filter" onclick="window.location.href='search.php?category=Fiction'">Fiction</button>
            <button class="category-filter" onclick="window.location.href='search.php?category=Non-Fiction'">Non-Fiction</button>
        </div>
        <form class="search-form" method="GET" action="search.php">
            <input type="text" name="query" placeholder="Search by title or author..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Search</button>
        </form>

        <div class="book-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="book-card" data-category="<?php echo $row['category']; ?>">
                        <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['title']; ?>">
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

        // Add smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>

<?php $conn->close(); ?>
</body>
</html>