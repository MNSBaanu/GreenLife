<?php
require_once 'dbconnect.php';

// Get all therapists from the database
$query = "SELECT * FROM users WHERE role = 'therapist' ORDER BY id ASC";
$result = mysqli_query($conn, $query);

$therapists = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $therapists[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Our Therapists - GreenLife Wellness Center</title>
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico" />
    <link rel="stylesheet" href="../css/therapist.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                        <a href="services.php">Services</a>
                        <a href="therapists.php">Therapists</a>
                        <a href="resources.php">Resources</a>
                    </div>
                </div>

                <a href="../html/login.html" class="login">Login</a>
            </nav>
        </div>
    </header>

    <!-- Therapists Section -->
    <section class="therapists">
        <div class="container">
            <h2>Meet Our Therapists</h2>
            <p class="subtitle">Passionate professionals dedicated to your wellness journey.</p>
            
            <!-- Therapist Categories -->
            <div class="therapy-categories">
                <div class="category-buttons">
                    <button class="category-btn active" data-category="all">All Therapists</button>
                    <button class="category-btn" data-category="ayurveda">Ayurveda</button>
                    <button class="category-btn" data-category="yoga">Yoga & Meditation</button>
                    <button class="category-btn" data-category="nutrition">Nutrition</button>
                    <button class="category-btn" data-category="holistic">Holistic Healing</button>
                </div>
            </div>

            <div class="therapist-grid">
                <?php if (empty($therapists)): ?>
                    <div class="no-therapists">
                        <p>No therapists available at the moment. Please check back later.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($therapists as $therapist): ?>
                        <div class="therapist-card" data-category="<?php echo strtolower($therapist['specialty'] ?? 'holistic'); ?>">
                            <div class="therapist-main">
                                <img src="<?php echo $therapist['profile_image'] ? '../uploads/' . $therapist['profile_image'] : '../images/therapist2.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($therapist['name']); ?>">
                                <div class="therapist-basic-info">
                                    <h3><?php echo htmlspecialchars($therapist['name']); ?></h3>
                                    <p class="specialty"><?php echo htmlspecialchars($therapist['specialty'] ?? 'Wellness Therapist'); ?></p>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <span>4.5 (128 reviews)</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="therapist-details">
                                <div class="detail-row">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span><?php echo htmlspecialchars($therapist['qualifications'] ?? 'Certified Wellness Professional'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-briefcase"></i>
                                    <span><?php echo htmlspecialchars($therapist['experience'] ?? '5+ years experience'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-calendar-alt"></i>
                                    <div class="availability">
                                        <?php 
                                        // Get therapist availability
                                        $availability_query = "SELECT day FROM therapist_availability WHERE therapist_id = " . $therapist['id'];
                                        $availability_result = mysqli_query($conn, $availability_query);
                                        $available_days = [];
                                        if ($availability_result) {
                                            while ($day = mysqli_fetch_assoc($availability_result)) {
                                                $available_days[] = substr($day['day'], 0, 3); // Get first 3 letters
                                            }
                                        }
                                        if (empty($available_days)) {
                                            $available_days = ['Mon', 'Wed', 'Fri']; // Default
                                        }
                                        foreach ($available_days as $day): ?>
                                            <span><?php echo $day; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-language"></i>
                                    <span><?php echo htmlspecialchars($therapist['languages'] ?? 'English, Sinhala'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-envelope"></i>
                                    <span><?php echo htmlspecialchars($therapist['email']); ?></span>
                                </div>
                                <div class="therapist-bio">
                                    <p><?php echo htmlspecialchars($therapist['bio'] ?? 'Dedicated wellness professional committed to helping clients achieve their health and wellness goals through personalized care and evidence-based practices.'); ?></p>
                                </div>
                            </div>
                            
                            <div class="therapist-actions">
                                <button class="btn-book" data-therapist-id="<?php echo $therapist['id']; ?>" 
                                        data-therapist-name="<?php echo htmlspecialchars($therapist['name']); ?>">
                                    Book Session
                                </button>
                                <button class="btn-view-profile" data-therapist-id="<?php echo $therapist['id']; ?>">
                                    View Full Profile
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2>Client Testimonials</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <i class="fas fa-quote-left"></i>
                        <p>Dr. Maya's Ayurvedic treatments transformed my digestive health completely. Her personalized approach made all the difference.</p>
                        <div class="client-info">
                            <img src="../images/client1.jpg" alt="Client">
                            <div>
                                <h4>Nimal Perera</h4>
                                <p>Client since 2022</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <i class="fas fa-quote-left"></i>
                        <p>Kasuni's therapeutic yoga sessions helped me recover from chronic back pain when nothing else worked. Highly recommend!</p>
                        <div class="client-info">
                            <img src="../images/client2.jpg" alt="Client">
                            <div>
                                <h4>Anoma Silva</h4>
                                <p>Client since 2021</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <h3>Ready to Begin Your Wellness Journey?</h3>
            <p>Book your first session today and experience the GreenLife difference</p>
            <a href="../html/appointment.html" class="btn-cta">Schedule an Appointment</a>
        </div>
    </section>

    <footer class="footer">
        <div class="container footer-content">
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="../html/index.html">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="therapists.php">Therapists</a></li>
                    <li><a href="../html/appointment.html">Appointments</a></li>
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
                    <li><a href="https://www.facebook.com/GreenLifeWellnessCenter" class="social-link" target="_blank">
                    <i class="fab fa-facebook-f"></i></a>
                    </li>
                    <li><a href="https://www.instagram.com/GreenLifeWellnessCenter" class="social-link" target="_blank">
                    <i class="fab fa-instagram"></i></a>
                    </li>
                    <li><a href="https://twitter.com/GreenLifeWellness" class="social-link" target="_blank">
                    <i class="fab fa-twitter"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 GreenLife Wellness Center. Embrace your well-being </p>
        </div>
    </footer>

    <script>
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

        // Therapist category filtering
        const categoryButtons = document.querySelectorAll('.category-btn');
        const therapistCards = document.querySelectorAll('.therapist-card');

        categoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                const category = button.dataset.category;
                therapistCards.forEach(card => {
                    if (category === 'all' || card.dataset.category === category) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Booking buttons
        const bookButtons = document.querySelectorAll('.btn-book');
        bookButtons.forEach(button => {
            button.addEventListener('click', () => {
                const therapistName = button.dataset.therapistName;
                const therapistId = button.dataset.therapistId;
                localStorage.setItem('selectedTherapist', therapistName);
                localStorage.setItem('selectedTherapistId', therapistId);
                window.location.href = '../html/appointment.html';
            });
        });

        // View profile buttons
        const viewProfileButtons = document.querySelectorAll('.btn-view-profile');
        viewProfileButtons.forEach(button => {
            button.addEventListener('click', () => {
                const therapistId = button.dataset.therapistId;
                // You can implement a modal or redirect to a detailed profile page
                alert('Detailed profile view coming soon!');
            });
        });
    </script>

</body>
</html> 