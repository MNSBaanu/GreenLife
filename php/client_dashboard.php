<?php
session_start();
include 'dbconnect.php';

// Prevent browser caching to avoid resubmit alerts
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../html/login.html");
    exit();
}

// Fetch client user data
$user_id = $_SESSION['user_id'];
$user_query = "SELECT u.fullname, u.username, u.email, u.phone, u.profile_image, u.assigned_therapist_id, u.progress, u.created_at, t.fullname as therapist_name FROM users u LEFT JOIN users t ON u.assigned_therapist_id = t.id WHERE u.id = ?";
$stmt_user = $conn->prepare($user_query);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_data = $user_result->fetch_assoc();

// Fetch services from database
$services_query = "SELECT id, name, category FROM services WHERE is_active = 1 ORDER BY name ASC";
$services_result = $conn->query($services_query);
$services = [];
while ($service = $services_result->fetch_assoc()) {
    $services[] = $service;
}

// Fetch all available therapists
$therapists_query = "SELECT id, fullname, profile_image FROM users WHERE role = 'therapist' AND is_active = 1 ORDER BY fullname ASC";
$therapists_result = $conn->query($therapists_query);
$therapists = [];
while ($therapist = $therapists_result->fetch_assoc()) {
    $therapists[] = $therapist;
}

// Fetch client's appointments
$appointments_query = "SELECT a.id, a.appointment_date, a.start_time, a.end_time, a.status, a.notes, a.duration,
                              s.name as service_name, t.fullname as therapist_name
                       FROM appointments a
                       INNER JOIN services s ON a.service_id = s.id
                       INNER JOIN users t ON a.therapist_id = t.id
                       WHERE a.client_id = ?
                       ORDER BY a.appointment_date DESC, a.start_time DESC";
$stmt_appointments = $conn->prepare($appointments_query);
$stmt_appointments->bind_param("i", $user_id);
$stmt_appointments->execute();
$appointments_result = $stmt_appointments->get_result();
$appointments = [];
while ($appointment = $appointments_result->fetch_assoc()) {
    $appointments[] = $appointment;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard | GreenLife</title>
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico" />
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

      <div class="dropdown user-dropdown">
        <div class="dropdown-toggle">
          <img src="<?php 
            $profile_img = $user_data['profile_image'] ?? 'default_avatar.png';
            if (strpos($profile_img, 'profile_') === 0) {
                echo '../images/uploads/' . htmlspecialchars($profile_img);
            } else {
                echo '../images/' . htmlspecialchars($profile_img);
            }
          ?>" alt="User" class="user-avatar">
          <span class="user-name user-name-dropdown"><?php echo htmlspecialchars($user_data['fullname'] ?? 'User'); ?></span>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="dropdown-menu">
          <a href="#my-profile" class="nav-link" data-section="my-profile"><i class="fas fa-user"></i> My Profile</a>
          <a href="#account-settings" class="nav-link" data-section="account-settings"><i class="fas fa-cog"></i> Account Settings</a>
          <a href="logout.php" id="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    </nav>
  </div>
</header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="main-header">
                <h1>Welcome back, <span class="user-name user-name-header"><?php echo htmlspecialchars($user_data['fullname'] ?? 'User'); ?></span></h1>
                <div class="header-actions">
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                </div>
            </header>

            <!-- Navigation Tabs -->
            <div class="dashboard-tabs">
                <button class="tab-btn active" data-section="my-profile">
                    <i class="fas fa-user"></i> My Profile
                </button>
                <button class="tab-btn" data-section="book-appointment">
                    <i class="fas fa-calendar-plus"></i> Book Appointment
                </button>
                <button class="tab-btn" data-section="view-appointments">
                    <i class="fas fa-calendar-check"></i> View Appointments
                </button>
                <button class="tab-btn" data-section="submit-inquiry">
                    <i class="fas fa-question-circle"></i> Submit Inquiry
                </button>
            </div>

            <!-- My Profile Section -->
            <div class="dashboard-section active" id="my-profile">
                <div class="section-header">
                    <h2>My Profile</h2>
                    <p>Manage your personal information and account settings</p>
                </div>
                <div class="profile-container">
                    <div class="profile-grid">
                        <!-- Profile Information -->
                        <div class="profile-card">
                            <div class="profile-header">
                                <div class="profile-avatar">
                                    <img src="<?php 
                                        $profile_img = $user_data['profile_image'] ?? 'default_avatar.png';
                                        if (strpos($profile_img, 'profile_') === 0) {
                                            echo '../images/uploads/' . htmlspecialchars($profile_img);
                                        } else {
                                            echo '../images/' . htmlspecialchars($profile_img);
                                        }
                                    ?>" alt="Profile Picture" id="profile-avatar-img">
                                    <button class="change-avatar-btn" onclick="document.getElementById('profile-picture-input').click()">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                    <input type="file" id="profile-picture-input" accept="image/*" style="display: none;">
                                </div>
                                <div class="profile-info">
                                    <h3><?php echo htmlspecialchars($user_data['fullname'] ?? 'User'); ?></h3>
                                    <p class="username">@<?php echo htmlspecialchars($user_data['username'] ?? 'username'); ?></p>
                                    <p class="role">Client</p>
                                </div>
                            </div>
                            
                            <!-- Wellness Information -->
                            <div class="wellness-info">
                                <div class="info-item">
                                    <span class="label">Assigned Therapist:</span>
                                    <span class="value"><?php echo htmlspecialchars($user_data['therapist_name'] ?? 'Not assigned'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Progress Level:</span>
                                    <span class="value"><?php echo htmlspecialchars($user_data['progress'] ?? 0); ?>%</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Member Since:</span>
                                    <span class="value"><?php echo $user_data['created_at'] ? date('F Y', strtotime($user_data['created_at'])) : date('F Y'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Account Status:</span>
                                    <span class="value status-active">Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Details Form -->
                        <div class="profile-form-card">
                            <h3>Personal Information</h3>
                            
                            <div id="profile-alert-container"></div>
                            
                            <form class="profile-form" id="profile-form">
                                <input type="hidden" name="update_profile" value="1">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="fullname">Full Name</label>
                                        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user_data['fullname'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="username">Username</label>
                                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn primary">Save Changes</button>
                                    <button type="button" class="btn outline" onclick="resetProfileForm()">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Settings Section -->
            <div class="dashboard-section" id="account-settings">
                <div class="section-header">
                    <h2>Account Settings</h2>
                    <p>Manage your account preferences and security settings</p>
                </div>
                <div class="settings-container">
                    <div class="settings-grid">
                        <!-- Account Settings -->
                        <div class="settings-card">
                            <h3>Account Settings</h3>
                            <div class="settings-list">
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h4>Change Password</h4>
                                        <p>Update your account password</p>
                                    </div>
                                    <button class="btn outline" onclick="showPasswordModal()">
                                        <i class="fas fa-key"></i> Change
                                    </button>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h4>Notification Preferences</h4>
                                        <p>Manage your notification settings</p>
                                    </div>
                                    <button class="btn outline">
                                        <i class="fas fa-bell"></i> Configure
                                    </button>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h4>Privacy Settings</h4>
                                        <p>Control your privacy and data sharing</p>
                                    </div>
                                    <button class="btn outline">
                                        <i class="fas fa-shield-alt"></i> Manage
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="settings-card">
                            <h3>Security Settings</h3>
                            <div class="settings-list">
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h4>Two-Factor Authentication</h4>
                                        <p>Add an extra layer of security to your account</p>
                                    </div>
                                    <button class="btn outline">
                                        <i class="fas fa-mobile-alt"></i> Enable
                                    </button>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h4>Login History</h4>
                                        <p>View your recent login activity</p>
                                    </div>
                                    <button class="btn outline">
                                        <i class="fas fa-history"></i> View
                                    </button>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h4>Account Recovery</h4>
                                        <p>Set up recovery options for your account</p>
                                    </div>
                                    <button class="btn outline">
                                        <i class="fas fa-life-ring"></i> Setup
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Data & Privacy -->
                        <div class="settings-card">
                            <h3>Data & Privacy</h3>
                            <div class="settings-list">
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h4>Data Export</h4>
                                        <p>Download a copy of your personal data</p>
                                    </div>
                                    <button class="btn outline">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h4>Delete Account</h4>
                                        <p>Permanently delete your account and data</p>
                                    </div>
                                    <button class="btn danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book Appointment Section -->
            <div class="dashboard-section" id="book-appointment">
                <div class="section-header">
                    <h2>Book New Appointment</h2>
                    <p>Schedule your next wellness session with your preferred therapist</p>
                </div>
                <div id="alert-container"></div>
                <div class="booking-form-container">
                    <form class="booking-form" id="appointment-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="appointment-type">Service</label>
                                <select id="appointment-type" name="appointment_type" required>
                                    <option value="">Select a service</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?php echo htmlspecialchars($service['id']); ?>">
                                            <?php echo htmlspecialchars($service['name']); ?> (<?php echo htmlspecialchars($service['category']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="therapist-selection">Select Therapist</label>
                                <select id="therapist-selection" name="therapist_id" required disabled>
                                    <option value="">First select a service</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="appointment-date">Preferred Date</label>
                                <input type="date" id="appointment-date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="appointment-time">Preferred Time</label>
                                <select id="appointment-time" name="appointment_time" required disabled>
                                    <option value="">First select therapist and date</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="session-duration">Session Duration</label>
                            <select id="session-duration" name="session_duration" required>
                                <option value="60">60 minutes</option>
                                <option value="90">90 minutes</option>
                                <option value="120">120 minutes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="appointment-notes">Notes (Optional)</label>
                            <textarea id="appointment-notes" name="appointment_notes" rows="4" placeholder="Any specific topics you'd like to discuss or concerns you have..."></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn outline" onclick="resetForm()">Clear Form</button>
                            <button type="submit" class="btn primary">Book Appointment</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- View Appointments Section -->
            <div class="dashboard-section" id="view-appointments">
                <div class="section-header">
                    <h2>Your Appointments</h2>
                    <p>Manage and view all your scheduled appointments</p>
                </div>
                <div class="appointments-container">
                    <div class="appointments-filters">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="upcoming">Upcoming</button>
                        <button class="filter-btn" data-filter="past">Past</button>
                        <button class="filter-btn" data-filter="cancelled">Cancelled</button>
                    </div>
                    <div class="appointments-list">
                        <?php if (!empty($appointments)): ?>
                            <?php foreach ($appointments as $appointment): ?>
                                <?php 
                                    $appointment_date = new DateTime($appointment['appointment_date']);
                                    $is_past = $appointment_date < new DateTime();
                                    $status_class = strtolower($appointment['status']);
                                    $card_class = $is_past ? 'past' : 'upcoming';
                                ?>
                                <div class="appointment-card <?php echo $card_class; ?>">
                            <div class="appointment-header">
                                <div class="appointment-date">
                                            <span class="day"><?php echo $appointment_date->format('d'); ?></span>
                                            <span class="month"><?php echo $appointment_date->format('M'); ?></span>
                                </div>
                                <div class="appointment-info">
                                            <h3><?php echo htmlspecialchars($appointment['service_name']); ?></h3>
                                            <p><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($appointment['start_time'])) . ' - ' . date('g:i A', strtotime($appointment['end_time'])); ?></p>
                                            <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($appointment['therapist_name']); ?></p>
                                </div>
                                <div class="appointment-status">
                                            <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($appointment['status']); ?></span>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                        <?php if ($appointment['status'] === 'confirmed' && !$is_past): ?>
                                <button class="btn primary"><i class="fas fa-video"></i> Join Session</button>
                                <button class="btn outline"><i class="fas fa-edit"></i> Reschedule</button>
                                <button class="btn danger"><i class="fas fa-times"></i> Cancel</button>
                                        <?php elseif ($appointment['status'] === 'pending'): ?>
                                            <button class="btn outline"><i class="fas fa-clock"></i> Waiting for Confirmation</button>
                                        <?php elseif ($is_past): ?>
                                <button class="btn outline"><i class="fas fa-file-alt"></i> View Notes</button>
                                <button class="btn outline"><i class="fas fa-star"></i> Rate Session</button>
                                        <?php endif; ?>
                            </div>
                        </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-appointments">
                                <i class="fas fa-calendar"></i>
                                <h3>No Appointments Found</h3>
                                <p>You don't have any appointments scheduled yet.</p>
                                <button class="btn primary" onclick="showSection('book-appointment')">Book Your First Appointment</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Submit Inquiry Section -->
            <div class="dashboard-section" id="submit-inquiry">
                <div class="section-header">
                    <h2>Submit Inquiry</h2>
                    <p>Have questions or need assistance? Send us a message and we'll get back to you soon.</p>
                </div>
                <div class="inquiry-form-container">
                    <!-- Alert container for inquiry form -->
                    <div id="inquiry-alert-container"></div>
                    
                    <form class="inquiry-form" id="inquiry-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="inquiry-subject">Subject</label>
                                <input type="text" id="inquiry-subject" name="inquiry_subject" placeholder="Brief description of your inquiry" required>
                            </div>
                            <div class="form-group">
                                <label for="inquiry-category">Category</label>
                                <select id="inquiry-category" name="inquiry_category" required>
                                    <option value="">Select category</option>
                                    <option value="appointment">Appointment Related</option>
                                    <option value="billing">Billing & Payment</option>
                                    <option value="technical">Technical Support</option>
                                    <option value="general">General Question</option>
                                    <option value="feedback">Feedback</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inquiry-message">Message</label>
                            <textarea id="inquiry-message" name="inquiry_message" rows="6" placeholder="Please provide details about your inquiry..." required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="inquiry-priority">Priority Level</label>
                            <select id="inquiry-priority" name="inquiry_priority" required>
                                <option value="low">Low - General question</option>
                                <option value="medium">Medium - Need assistance</option>
                                <option value="high">High - Urgent matter</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn outline" onclick="resetInquiryForm()">Clear Form</button>
                            <button type="submit" class="btn primary">Submit Inquiry</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container footer-content">
                <div class="footer-column">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="../html/index.html">Home</a></li>
                        <li><a href="../php/services.php">Services</a></li>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tab navigation
            const tabBtns = document.querySelectorAll('.tab-btn');
            const sections = document.querySelectorAll('.dashboard-section');
            const navLinks = document.querySelectorAll('.nav-link');

            function showSection(sectionId) {
                // Hide all sections
                sections.forEach(section => section.classList.remove('active'));
                
                // Remove active class from all tabs
                tabBtns.forEach(btn => btn.classList.remove('active'));
                
                // Show selected section
                const targetSection = document.getElementById(sectionId);
                if (targetSection) {
                    targetSection.classList.add('active');
                }
                
                // Add active class to corresponding tab
                const targetTab = document.querySelector(`[data-section="${sectionId}"]`);
                if (targetTab) {
                    targetTab.classList.add('active');
                }
            }

            // Tab button clicks
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const sectionId = this.getAttribute('data-section');
                    showSection(sectionId);
                });
            });

            // Navigation link clicks
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const sectionId = this.getAttribute('data-section');
                    showSection(sectionId);
                });
            });

            // Profile form submission via AJAX
            document.getElementById('profile-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Disable submit button and show loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                
                fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Show alert
                    const alertContainer = document.getElementById('profile-alert-container');
                    const alertClass = data.success ? 'success' : 'error';
                    const iconClass = data.success ? 'fa-check-circle' : 'fa-exclamation-circle';
                    
                    alertContainer.innerHTML = `
                        <div class="alert ${alertClass}" id="profile-alert">
                            <i class="fas ${iconClass}"></i>
                            ${data.message}
                        </div>
                    `;
                    
                    // Auto-hide alert after 3 seconds
                    setTimeout(function() {
                        const alertBox = document.getElementById('profile-alert');
                        if(alertBox) {
                            alertBox.style.transition = 'opacity 0.5s';
                            alertBox.style.opacity = 0;
                            setTimeout(function() { alertBox.remove(); }, 500);
                        }
                    }, 3000);
                    
                    // Update user name in header if successful
                    if (data.success) {
                        const newName = document.getElementById('fullname').value;
                        document.querySelectorAll('.user-name').forEach(element => {
                            element.textContent = newName;
                        });
                        
                        // Clear browser history to prevent resubmit alerts
                        if (window.history && window.history.replaceState) {
                            window.history.replaceState(null, null, window.location.pathname);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alertContainer = document.getElementById('profile-alert-container');
                    alertContainer.innerHTML = `
                        <div class="alert error" id="profile-alert">
                            <i class="fas fa-exclamation-circle"></i>
                            An error occurred while updating your profile. Please try again.
                        </div>
                    `;
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });

            // Appointment form submission
            document.getElementById('appointment-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Disable submit button and show loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Booking...';
                
                fetch('book_appointment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const alertContainer = document.getElementById('alert-container');
                        alertContainer.innerHTML = `
                            <div class="alert success" id="appointment-alert">
                                <i class="fas fa-check-circle"></i>
                                ${data.message}
                            </div>
                        `;
                        
                        // Reset form
                this.reset();
                        
                        // Reset dropdowns
                        document.getElementById('therapist-selection').innerHTML = '<option value="">First select a service</option>';
                        document.getElementById('therapist-selection').disabled = true;
                        document.getElementById('appointment-time').innerHTML = '<option value="">First select therapist and date</option>';
                        document.getElementById('appointment-time').disabled = true;
                        
                        // Auto-hide alert after 5 seconds
                        setTimeout(function() {
                            const alertBox = document.getElementById('appointment-alert');
                            if(alertBox) {
                                alertBox.style.transition = 'opacity 0.5s';
                                alertBox.style.opacity = 0;
                                setTimeout(function() { alertBox.remove(); }, 500);
                            }
                        }, 5000);
                    } else {
                        // Show error message
                        const alertContainer = document.getElementById('alert-container');
                        alertContainer.innerHTML = `
                            <div class="alert error" id="appointment-alert">
                                <i class="fas fa-exclamation-circle"></i>
                                ${data.error}
                            </div>
                        `;
                        
                        // Auto-hide alert after 5 seconds
                        setTimeout(function() {
                            const alertBox = document.getElementById('appointment-alert');
                            if(alertBox) {
                                alertBox.style.transition = 'opacity 0.5s';
                                alertBox.style.opacity = 0;
                                setTimeout(function() { alertBox.remove(); }, 500);
                            }
                        }, 5000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alertContainer = document.getElementById('alert-container');
                    alertContainer.innerHTML = `
                        <div class="alert error" id="appointment-alert">
                            <i class="fas fa-exclamation-circle"></i>
                            An error occurred while booking your appointment. Please try again.
                        </div>
                    `;
                    
                    // Auto-hide alert after 5 seconds
                    setTimeout(function() {
                        const alertBox = document.getElementById('appointment-alert');
                        if(alertBox) {
                            alertBox.style.transition = 'opacity 0.5s';
                            alertBox.style.opacity = 0;
                            setTimeout(function() { alertBox.remove(); }, 500);
                        }
                    }, 5000);
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });

            // Inquiry form submission
            document.getElementById('inquiry-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show success message
                const alertContainer = document.getElementById('inquiry-alert-container');
                alertContainer.innerHTML = `
                    <div class="alert success" id="inquiry-alert">
                        <i class="fas fa-check-circle"></i>
                        Inquiry submitted successfully! We will get back to you within 24 hours.
                    </div>
                `;
                
                // Reset form
                this.reset();
                
                // Auto-hide alert after 3 seconds
                setTimeout(function() {
                    const alertBox = document.getElementById('inquiry-alert');
                    if(alertBox) {
                        alertBox.style.transition = 'opacity 0.5s';
                        alertBox.style.opacity = 0;
                        setTimeout(function() { alertBox.remove(); }, 500);
                    }
                }, 3000);
            });

            // Appointment filters
            const filterBtns = document.querySelectorAll('.filter-btn');
            const appointmentCards = document.querySelectorAll('.appointment-card');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    
                    // Update active filter button
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter appointment cards
                    appointmentCards.forEach(card => {
                        if (filter === 'all' || card.classList.contains(filter)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Update user name in all places
            const userName = '<?php echo htmlspecialchars($user_data['fullname'] ?? 'User'); ?>';
            document.querySelectorAll('.user-name').forEach(element => {
                element.textContent = userName;
            });
            
            // Dynamic booking form functionality
            const serviceSelect = document.getElementById('appointment-type');
            const therapistSelect = document.getElementById('therapist-selection');
            const dateInput = document.getElementById('appointment-date');
            const timeSelect = document.getElementById('appointment-time');
            
            // Load therapists when service is selected
            serviceSelect.addEventListener('change', function() {
                const serviceId = this.value;
                therapistSelect.disabled = true;
                therapistSelect.innerHTML = '<option value="">Loading therapists...</option>';
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">First select therapist and date</option>';
                
                if (serviceId) {
                    fetch(`get_therapist_availability.php?action=get_therapists&service_id=${serviceId}`)
                        .then(response => response.json())
                        .then(data => {
                            therapistSelect.innerHTML = '<option value="">Select a therapist</option>';
                            data.therapists.forEach(therapist => {
                                const option = document.createElement('option');
                                option.value = therapist.id;
                                option.textContent = therapist.fullname;
                                therapistSelect.appendChild(option);
                            });
                            therapistSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error loading therapists:', error);
                            therapistSelect.innerHTML = '<option value="">Error loading therapists</option>';
                        });
                } else {
                    therapistSelect.innerHTML = '<option value="">First select a service</option>';
                }
            });
            
            // Load available times when therapist and date are selected
            function loadAvailableTimes() {
                const therapistId = therapistSelect.value;
                const date = dateInput.value;
                
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">Loading available times...</option>';
                
                if (therapistId && date) {
                    fetch(`get_therapist_availability.php?action=get_availability&therapist_id=${therapistId}&date=${date}`)
                        .then(response => response.json())
                        .then(data => {
                            timeSelect.innerHTML = '<option value="">Select a time</option>';
                            if (data.available_slots.length === 0) {
                                timeSelect.innerHTML = '<option value="">No available times for this date</option>';
                            } else {
                                data.available_slots.forEach(slot => {
                                    const option = document.createElement('option');
                                    option.value = slot.time;
                                    option.textContent = slot.display;
                                    timeSelect.appendChild(option);
                                });
                            }
                            timeSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error loading availability:', error);
                            timeSelect.innerHTML = '<option value="">Error loading availability</option>';
                        });
                } else {
                    timeSelect.innerHTML = '<option value="">First select therapist and date</option>';
                }
            }
            
            therapistSelect.addEventListener('change', loadAvailableTimes);
            dateInput.addEventListener('change', loadAvailableTimes);
            
            // Profile picture upload functionality
            const profilePictureInput = document.getElementById('profile-picture-input');
            const profileAvatarImg = document.getElementById('profile-avatar-img');
            
            profilePictureInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB');
                        return;
                    }
                    
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Only JPG, PNG, and GIF files are allowed');
                        return;
                    }
                    
                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profileAvatarImg.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    
                    // Upload file
                    const formData = new FormData();
                    formData.append('profile_picture', file);
                    
                    // Show loading state
                    const changeBtn = document.querySelector('.change-avatar-btn');
                    const originalIcon = changeBtn.innerHTML;
                    changeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    changeBtn.disabled = true;
                    
                    fetch('upload_profile_picture.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            const alertContainer = document.getElementById('profile-alert-container');
                            alertContainer.innerHTML = `
                                <div class="alert success" id="profile-alert">
                                    <i class="fas fa-check-circle"></i>
                                    ${data.message}
                                </div>
                            `;
                            
                            // Update all profile images on the page
                            document.querySelectorAll('.user-avatar').forEach(avatar => {
                                avatar.src = '../images/uploads/' + data.filename;
                            });
                            
                            // Auto-hide alert after 3 seconds
                            setTimeout(function() {
                                const alertBox = document.getElementById('profile-alert');
                                if(alertBox) {
                                    alertBox.style.transition = 'opacity 0.5s';
                                    alertBox.style.opacity = 0;
                                    setTimeout(function() { alertBox.remove(); }, 500);
                                }
                            }, 3000);
                        } else {
                            // Show error message
                            alert(data.message);
                            // Revert preview
                            profileAvatarImg.src = '<?php 
                                $profile_img = $user_data['profile_image'] ?? 'default_avatar.png';
                                if (strpos($profile_img, 'profile_') === 0) {
                                    echo '../images/uploads/' . htmlspecialchars($profile_img);
                                } else {
                                    echo '../images/' . htmlspecialchars($profile_img);
                                }
                            ?>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while uploading the image. Please try again.');
                        // Revert preview
                        profileAvatarImg.src = '<?php 
                            $profile_img = $user_data['profile_image'] ?? 'default_avatar.png';
                            if (strpos($profile_img, 'profile_') === 0) {
                                echo '../images/uploads/' . htmlspecialchars($profile_img);
                            } else {
                                echo '../images/' . htmlspecialchars($profile_img);
                            }
                        ?>';
                    })
                    .finally(() => {
                        // Reset button state
                        changeBtn.innerHTML = originalIcon;
                        changeBtn.disabled = false;
                        // Clear file input
                        profilePictureInput.value = '';
                    });
                }
            });
        });

        // Form reset functions
        function resetForm() {
            document.getElementById('appointment-form').reset();
        }

        function resetInquiryForm() {
            document.getElementById('inquiry-form').reset();
        }

        function resetProfileForm() {
            document.getElementById('profile-form').reset();
        }

        function showPasswordModal() {
            alert('Password change functionality will be implemented in the next update.');
        }
    </script>
</body>
</html> 