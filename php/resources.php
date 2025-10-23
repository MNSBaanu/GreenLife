<?php
require_once 'dbconnect.php';

// Fetch data from database
$articles_sql = "SELECT * FROM articles WHERE is_active = 1 ORDER BY publish_date DESC";
$videos_sql = "SELECT * FROM videos WHERE is_active = 1 ORDER BY created_at DESC";
$tips_sql = "SELECT * FROM health_tips WHERE is_active = 1 ORDER BY created_at DESC";

$articles_result = $conn->query($articles_sql);
$videos_result = $conn->query($videos_sql);
$tips_result = $conn->query($tips_sql);

$articles = [];
$videos = [];
$tips = [];

if ($articles_result->num_rows > 0) {
    while($row = $articles_result->fetch_assoc()) {
        $articles[] = $row;
    }
}

if ($videos_result->num_rows > 0) {
    while($row = $videos_result->fetch_assoc()) {
        $videos[] = $row;
    }
}

if ($tips_result->num_rows > 0) {
    while($row = $tips_result->fetch_assoc()) {
        $tips[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Resources - GreenLife Wellness Center</title>
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico" />
    <link rel="stylesheet" href="../css/resources.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="container header-container">
            <div class="brand">
                <img src="../images/logo.png" alt="GreenLife Logo" class="logo-img">
                <span>GreenLife</span>
            </div>
            <nav class="nav">
                <a href="../html/index.html">Home</a>
                <a href="../html/about.html">About</a>
                <a href="../html/contact.html">Contact</a>

                <div class="dropdown">
                    <button class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                        Explore <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="../php/services.php">Services</a>
                        <a href="../php/therapists.php">Therapists</a>
                        <a href="../php/resources.php">Resources</a>
                    </div>
                </div>

                <a href="../html/login.html" class="login">Login</a>
            </nav>
        </div>
    </header>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Search articles, videos, tips, and guides..." class="search-input">
                    <button class="search-btn" type="button">
                        <i class="fas fa-search"></i>
                        <span>Search</span>
                    </button>
                </div>
                <div class="filter-options">
                    <select class="filter-select">
                        <option value="all">All Categories</option>
                        <option value="articles">Articles & Blogs</option>
                        <option value="videos">Videos & Tutorials</option>
                        <option value="tips">Daily Health Tips</option>
                        <option value="meditation">Meditation & Mindfulness</option>
                        <option value="nutrition">Nutrition & Diet</option>
                        <option value="exercise">Exercise & Fitness</option>
                    </select>
                    <button class="advanced-filter-btn">
                        <i class="fas fa-filter"></i>
                        <span>Advanced</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Purpose Section -->
    <section class="purpose">
        <div class="container">
            <h2 class="section-title">Our Purpose</h2>
            <div class="purpose-content">
                <img src="../images/purpose-image.jpg" alt="Wellness Purpose">
                <div class="purpose-text">
                    <p>At GreenLife Wellness Center, we believe in providing accessible, evidence-based wellness resources to empower your health journey. Our carefully curated collection of articles, videos, and guides are designed to support your physical, mental, and emotional well-being.</p>
                    <p>Whether you're looking for expert advice, practical tips, or inspirational content, our resources are here to guide you toward a healthier, more balanced life.</p>
                    <a href="#" class="btn-small">Learn More About Our Mission</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Health Tips Section -->
    <section class="health-tips">
        <div class="container">
            <h2 class="section-title">Daily Health Tips</h2>
            <div class="tips-grid">
                <?php foreach($tips as $tip): ?>
                <div class="tip-card">
                    <div class="tip-badge"><?php echo htmlspecialchars($tip['category']); ?></div>
                    <img src="../images/<?php echo htmlspecialchars($tip['image']); ?>" alt="<?php echo htmlspecialchars($tip['title']); ?>">
                    <div class="tip-content">
                        <h4><?php echo htmlspecialchars($tip['title']); ?></h4>
                        <p><?php echo htmlspecialchars($tip['description']); ?></p>
                        <div class="tip-meta">
                            <span class="tip-duration"><i class="far fa-clock"></i> <?php echo htmlspecialchars($tip['duration']); ?></span>
                            <span class="tip-benefits"><i class="fas fa-heart"></i> <?php echo htmlspecialchars($tip['benefits']); ?></span>
                        </div>
                        <button class="btn-tip">Try This Tip</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Videos Section -->
    <section class="videos" id="videos">
        <div class="container">
            <h2 class="section-title">Featured Videos</h2>
            <div class="video-grid">
                <?php foreach($videos as $video): ?>
                <div class="video-card">
                    <div class="video-container">
                        <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video['youtube_id']); ?>" frameborder="0" allowfullscreen></iframe>
                    </div>
                    <div class="video-content">
                        <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                        <p><?php echo htmlspecialchars($video['description']); ?></p>
                        <div class="video-actions">
                            <a href="<?php echo htmlspecialchars($video['youtube_url']); ?>" class="btn-small" target="_blank"><i class="fab fa-youtube"></i> Watch on YouTube</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Articles Section -->
    <section class="articles" id="articles">
        <div class="container">
            <h2 class="section-title">Wellness Articles</h2>
            <div class="articles-grid">
                <?php foreach($articles as $article): ?>
                <div class="article-card">
                    <div class="article-image">
                        <img src="../images/<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <span class="article-category"><?php echo htmlspecialchars($article['category']); ?></span>
                    </div>
                    <div class="article-content">
                        <div class="article-meta">
                            <span><i class="far fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($article['publish_date'])); ?></span>
                            <span><i class="far fa-clock"></i> <?php echo $article['read_time']; ?> min read</span>
                        </div>
                        <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                        <p><?php echo htmlspecialchars($article['summary']); ?></p>
                        <a href="#" class="read-more">Read Article <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="../html/index.html">Home</a></li>
                    <li><a href="../php/services.php">Services</a></li>
                    <li><a href="../php/therapists.php">Therapists</a></li>
                    <li><a href="../html/contact.html">Contact</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4>Contact Us</h4>
                <p>123 Green Road, Colombo, Sri Lanka</p>
                <p>+94 11 234 5678</p>
                <p><a href="mailto:wellness@greenlife.lk">wellness@greenlife.lk</a></p>
            </div>

            <div class="footer-column">
                <h4>Follow Us</h4>
                <ul>
                    <li><a href="https://www.facebook.com/GreenLifeWellnessCenter" class="social-link" target="_blank"><i class="fab fa-facebook"></i></a></li>
                    <li><a href="https://www.instagram.com/GreenLifeWellnessCenter" class="social-link" target="_blank"><i class="fab fa-instagram"></i></a></li>
                    <li><a href="https://twitter.com/GreenLifeWellness" class="social-link" target="_blank"><i class="fab fa-twitter"></i></a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 GreenLife Wellness Center. Embrace your well-being</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <script>
        // Dropdown functionality
        const dropdown = document.querySelector('.dropdown');
        const toggleButton = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        const icon = toggleButton.querySelector('i');

        let isOpen = false;

        toggleButton.addEventListener('click', (e) => {
            e.stopPropagation();
            isOpen = !isOpen;
            menu.style.opacity = isOpen ? '1' : '0';
            menu.style.visibility = isOpen ? 'visible' : 'hidden';
            icon.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
        });

        document.addEventListener('click', () => {
            isOpen = false;
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
            icon.style.transform = 'rotate(0deg)';
        });

        dropdown.addEventListener('mouseenter', () => {
            menu.style.opacity = '1';
            menu.style.visibility = 'visible';
            icon.style.transform = 'rotate(180deg)';
        });

        dropdown.addEventListener('mouseleave', () => {
            if (!isOpen) {
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
                icon.style.transform = 'rotate(0deg)';
            }
        });
    </script>

</body>
</html>

<?php
$conn->close();
?> 