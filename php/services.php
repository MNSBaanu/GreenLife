<?php
require_once('../php/dbconnect.php');

// Fetch all active services from database - now ordered by category, then by id (newest last)
$query = "SELECT * FROM services WHERE is_active = 1 ORDER BY category, id";
$result = mysqli_query($conn, $query);

// Initialize services array
$services = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Process benefits into an array
        $row['benefits_array'] = array_filter(
            explode("\n", $row['benefits']),
            function($item) { return !empty(trim($item)); }
        );
        $services[] = $row;
    }
}

// Group services by category
$servicesByCategory = [];
foreach ($services as $service) {
    $servicesByCategory[$service['category']][] = $service;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Our Services | GreenLife Wellness Center</title>
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico" />
    <link rel="stylesheet" href="../css/services.css" />
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
          <a href="../php/services.php">Services</a>
          <a href="../html/therapists.html">Therapists</a>
          <a href="../php/resources.php">Resources</a>
        </div>
      </div>

      <a href="../html/login.html" class="login">Login</a>
    </nav>
  </div>
</header>

<section class="offerings">
        <div class="container">
            <div class="offerings-header">
                <h2>Our Offerings</h2>
                <p class="subtitle">Explore the range of healing services we offer</p>
                <?php if (empty($services)): ?>
                    <p class="no-services">We're currently updating our services. Please check back soon.</p>
                <?php endif; ?>
            </div>

            <div class="search-container">
                <input type="text" placeholder="Search services..." id="serviceSearch">
                <i class="fas fa-search"></i>
            </div>

            <div class="offerings-grid" id="servicesContainer">
                <?php foreach ($servicesByCategory as $category => $categoryServices): ?>
                    <div class="category-group">
                        <div class="category-services">
                            <?php foreach ($categoryServices as $service): ?>
                                <div class="offering-card card-<?= strtolower(str_replace(' ', '-', $service['category'])) ?>">
                                    <span class="card-category"><?= htmlspecialchars($service['category']) ?></span>
                                    <img src="../images/services/<?= htmlspecialchars($service['image']) ?>" 
                                         alt="<?= htmlspecialchars($service['name']) ?>">
                                    <div class="service-content">
                                        <h3><?= htmlspecialchars($service['name']) ?></h3>
                                        <p class="service-summary"><?= htmlspecialchars($service['summary']) ?></p>
                                        
                                        <div class="service-details">
                                            <ul class="service-benefits">
                                                <?php foreach ($service['benefits_array'] as $benefit): ?>
                                                    <li><?= htmlspecialchars($benefit) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        
                                        <button class="expand-toggle">
                                            View Details <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <button class="btn-book" data-service-id="<?= $service['id'] ?>">
                                            <?= htmlspecialchars($service['booking_text'] ?: 'Book Session') ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="self-help">
        <div class="container">
            <h2>Self-Help Resources</h2>
            <p class="subtitle">Tools to support your wellness journey at home</p>
            
            <div class="tools-grid">
                <div class="tool-card">
                    <h3>Guided Meditation Audio</h3>
                    <p>10-minute daily meditation practices to reduce stress and improve focus.</p>
                    <a href="#" class="tool-link">Access Now <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="tool-card">
                    <h3>Healthy Recipes</h3>
                    <p>Nutritionist-approved meal plans and recipes for optimal health.</p>
                    <a href="#" class="tool-link">Explore Recipes <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="tool-card">
                    <h3>Home Exercise Videos</h3>
                    <p>Yoga and physiotherapy routines you can do at home.</p>
                    <a href="#" class="tool-link">View Videos <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

        <footer class="footer">
        <div class="container footer-content">
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="../html/index.html">Home</a></li>
                    <li><a href="../html/services.php">Services</a></li>
                    <li><a href="../html/therapists.html">Therapists</a></li>
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
    e.stopPropagation(); // Prevent click from closing instantly
    isOpen = !isOpen;
    menu.style.opacity = isOpen ? '1' : '0';
    menu.style.visibility = isOpen ? 'visible' : 'hidden';
    icon.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
  });

  // Close on click outside
  document.addEventListener('click', () => {
    isOpen = false;
    menu.style.opacity = '0';
    menu.style.visibility = 'hidden';
    icon.style.transform = 'rotate(0deg)';
  });

  // Allow hover to also show menu
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

    // Optional: Alert or action for Book Now buttons
    const bookButtons = document.querySelectorAll(".btn-small");

    bookButtons.forEach(button => {
      button.addEventListener("click", function (e) {
        // Uncomment this line if you want to prevent redirection:
        // e.preventDefault();
        console.log("Redirecting to appointment booking...");
      });
    });
    
  });
   
           // Service Card Expansion
// Make the interaction more obvious
document.querySelectorAll('.offering-card').forEach(card => {
    // Add ARIA attributes for accessibility
    card.setAttribute('aria-expanded', 'false');
    card.setAttribute('role', 'button');
    card.setAttribute('tabindex', '0');
    
    const details = card.querySelector('.service-details');
    const button = card.querySelector('.btn-book');
    
    // Click handler
    const toggleCard = () => {
        const isExpanded = card.classList.toggle('expanded');
        card.setAttribute('aria-expanded', isExpanded);
        
        if (isExpanded) {
            details.style.maxHeight = details.scrollHeight + 'px';
            details.style.opacity = '1';
        } else {
            details.style.maxHeight = '0';
            details.style.opacity = '0';
        }
    };
    
    // Click/tap
    card.addEventListener('click', (e) => {
        if (e.target !== button) {
            toggleCard();
        }
    });
    
    // Keyboard accessibility
    card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleCard();
        }
    });
    
    // Prevent card toggle when clicking Book Now
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        const serviceName = card.querySelector('h3').textContent;
        alert(`Redirecting to booking for ${serviceName}`);
    });
});

// Add introductory help text
const offeringsHeader = document.querySelector('.offerings-header');
const helpText = document.createElement('p');
helpText.className = 'interaction-help';
offeringsHeader.appendChild(helpText);

        // Search Functionality
        document.getElementById('serviceSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const services = document.querySelectorAll('.offering-card');
            
            services.forEach(service => {
                const serviceName = service.querySelector('h3').textContent.toLowerCase();
                if (serviceName.includes(searchTerm)) {
                    service.style.display = 'block';
                } else {
                    service.style.display = 'none';
                }
            });
        });

        // Book Now Button Functionality
        document.querySelectorAll('.btn-book').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent card expansion when clicking book button
                const serviceName = this.closest('.offering-card').querySelector('h3').textContent;
                alert(`Redirecting to booking for ${serviceName}`);
                // In a real implementation, you would redirect to booking page:
                // window.location.href = `../html/appointment.html?service=${encodeURIComponent(serviceName)}`;
            });
        });

        // Animate cards when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.offering-card').forEach(card => {
            observer.observe(card);
        });

</script>

</body>
</html>