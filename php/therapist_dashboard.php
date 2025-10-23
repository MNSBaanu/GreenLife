<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is a therapist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    header("Location: ../html/login.html");
    exit();
}

// Fetch therapist user data
$user_id = $_SESSION['user_id'];
$user_query = "SELECT fullname, username, email, phone, profile_image, created_at FROM users WHERE id = ?";
$stmt_user = $conn->prepare($user_query);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_data = $user_result->fetch_assoc();

// Fetch assigned clients
$clients_query = "SELECT id, fullname, profile_image, last_session, progress FROM users WHERE role = 'client' AND assigned_therapist_id = ? LIMIT 5";
$stmt_clients = $conn->prepare($clients_query);
$stmt_clients->bind_param("i", $user_id);
$stmt_clients->execute();
$clients_result = $stmt_clients->get_result();

// Fetch upcoming appointments
$appointments_query = "SELECT a.id, a.appointment_date, a.start_time, a.end_time, a.status, a.notes, 
                              c.fullname as client_name, c.profile_image as client_image,
                              s.name as service_name
                       FROM appointments a 
                       JOIN users c ON a.client_id = c.id 
                       JOIN services s ON a.service_id = s.id 
                       WHERE a.therapist_id = ? 
                       ORDER BY a.appointment_date ASC, a.start_time ASC 
                       LIMIT 10";
$stmt_appointments = $conn->prepare($appointments_query);
$stmt_appointments->bind_param("i", $user_id);
$stmt_appointments->execute();
$appointments_result = $stmt_appointments->get_result();

// Debug: Log appointment count
$appointment_count = $appointments_result->num_rows;
error_log("Therapist $user_id has $appointment_count appointments");

// Fetch therapist's services
$services_query = "SELECT id, name, category, image FROM services WHERE is_active = 1";
$services_result = $conn->query($services_query);

// Fetch assigned inquiries
$inquiries_query = "SELECT id, client_name, client_email, client_phone, subject, message, category, status, created_at 
                    FROM inquiries 
                    WHERE assigned_therapist_id = ? 
                    ORDER BY 
                        CASE 
                            WHEN status = 'urgent' THEN 1
                            WHEN status = 'open' THEN 2
                            WHEN status = 'pending' THEN 3
                            ELSE 4
                        END,
                        created_at DESC 
                    LIMIT 10";
$stmt_inquiries = $conn->prepare($inquiries_query);
$stmt_inquiries->bind_param("i", $user_id);
$stmt_inquiries->execute();
$inquiries_result = $stmt_inquiries->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Therapist Dashboard | GreenLife</title>
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
                        Manage <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="../php/services.php">Services</a>
                        <a href="../php/therapists.php">Therapists</a>
                        <a href="../phpresources.php">Resources</a>
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
                        ?>" alt="Therapist" class="user-avatar">
                        <span class="user-name user-name-dropdown"><?php echo htmlspecialchars($user_data['fullname'] ?? 'Therapist'); ?></span>
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
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success" id="success-alert">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error" id="error-alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Header -->
        <header class="main-header">
            <h1>Welcome back, <span class="user-name user-name-header"><?php echo htmlspecialchars($user_data['fullname'] ?? 'Therapist'); ?></span></h1>
            <div class="header-actions">
                <div class="dropdown">
                    <button class="btn primary dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-plus"></i> Quick Add <i class="fa-solid fa-chevron-down"></i>
                </button>
                    <div class="dropdown-menu">
                        <a href="#" onclick="quickAdd('appointment')"><i class="fas fa-calendar-plus"></i> New Appointment</a>
                        <a href="#" onclick="quickAdd('note')"><i class="fas fa-sticky-note"></i> Add Note</a>
                        <a href="#" onclick="quickAdd('report')"><i class="fas fa-file-alt"></i> Create Report</a>
                    </div>
                </div>
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
            <button class="tab-btn" data-section="client-management">
                <i class="fas fa-users"></i> Client Management
            </button>
            <button class="tab-btn" data-section="appointment-management">
                <i class="fas fa-calendar-alt"></i> Appointment Management
            </button>
            <button class="tab-btn" data-section="inquiries">
                <i class="fas fa-comments"></i> Inquiries
            </button>
        </div>

        <!-- My Profile Section -->
        <div class="dashboard-section active" id="my-profile">
            <div class="section-header">
                <h2>My Profile</h2>
                <p>Manage your personal information and professional details</p>
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
                                <h3><?php echo htmlspecialchars($user_data['fullname'] ?? 'Therapist'); ?></h3>
                                <p class="username">@<?php echo htmlspecialchars($user_data['username'] ?? 'therapist'); ?></p>
                                <p class="role">Licensed Therapist</p>
                            </div>
                        </div>
                        
                        <!-- Therapist Information -->
                        <div class="wellness-info">
                            <div class="info-item">
                                <span class="label">Specialization:</span>
                                <span class="value">Ayurvedic Therapy, Yoga, Meditation</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Experience:</span>
                                <span class="value">5+ Years</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Member Since:</span>
                                <span class="value"><?php echo $user_data['created_at'] ? date('F Y', strtotime($user_data['created_at'])) : date('F Y'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Status:</span>
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

        <!-- Client Management Section -->
        <div class="dashboard-section" id="client-management">
            <div class="section-header">
                <h2>Client Management</h2>
                <p>Manage your assigned clients and monitor their progress</p>
            </div>
            <div class="management-container">
                <div class="clients-grid">
                    <?php if ($clients_result && $clients_result->num_rows > 0): ?>
                        <?php while($client = $clients_result->fetch_assoc()): ?>
                        <div class="client-card">
                            <div class="client-header">
                                <img src="<?php 
                                    $profile_img = $client['profile_image'] ?? 'default_avatar.png';
                                    if (strpos($profile_img, 'profile_') === 0) {
                                        echo '../images/uploads/' . htmlspecialchars($profile_img);
                                    } else {
                                        echo '../images/' . htmlspecialchars($profile_img);
                                    }
                                ?>" alt="<?php echo htmlspecialchars($client['fullname']); ?>"
                                onerror="this.src='../images/client1.jpg'; this.style.opacity='0.8';">
                                <div class="client-status">
                                    <span class="status-badge active">Active</span>
                                </div>
                            </div>
                            <div class="client-content">
                                <h3><?php echo htmlspecialchars($client['fullname']); ?></h3>
                                <div class="client-progress">
                                    <div class="progress-info">
                                        <span class="progress-label">Progress</span>
                                        <span class="progress-percentage"><?php echo htmlspecialchars($client['progress']); ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo htmlspecialchars($client['progress']); ?>%"></div>
                                    </div>
                                </div>
                                <div class="client-stats">
                                    <span><i class="fas fa-calendar"></i> <?php echo $client['last_session'] ? date("M d, Y", strtotime($client['last_session'])) : 'No sessions yet'; ?></span>
                                </div>
                            </div>
                            <div class="client-actions">
                                <button class="btn outline" onclick="viewClient(<?php echo $client['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn outline" onclick="viewProgress(<?php echo $client['id']; ?>)">
                                    <i class="fas fa-chart-line"></i> Progress
                                </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-clients">
                            <i class="fas fa-users"></i>
                            <h3>No Clients Assigned</h3>
                            <p>You don't have any clients assigned to you yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Appointment Management Section -->
        <div class="dashboard-section" id="appointment-management">
            <div class="section-header">
                <h2>Appointment Management</h2>
                <p>Manage your upcoming appointments and schedule</p>
            </div>
            <div class="management-container">
                <div class="appointments-grid">
                    <?php if ($appointments_result && $appointments_result->num_rows > 0): ?>
                        <?php while($appointment = $appointments_result->fetch_assoc()): ?>
                        <div class="appointment-card">
                            <div class="appointment-header">
                                <div class="appointment-status">
                                    <span class="status-badge <?php echo strtolower($appointment['status']); ?>"><?php echo ucfirst($appointment['status']); ?></span>
                                </div>
                                <div class="appointment-date">
                                    <span class="date"><?php echo date('M d', strtotime($appointment['appointment_date'])); ?></span>
                                    <span class="time"><?php echo date('g:i A', strtotime($appointment['start_time'])); ?></span>
                                </div>
                            </div>
                            <div class="appointment-content">
                                <h3><?php echo htmlspecialchars($appointment['service_name']); ?></h3>
                                <div class="appointment-details">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($appointment['client_name']); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($appointment['start_time'])) . ' - ' . date('g:i A', strtotime($appointment['end_time'])); ?></span>
                                </div>
                                <?php if ($appointment['notes']): ?>
                                <div class="appointment-notes">
                                    <p><?php echo htmlspecialchars($appointment['notes']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="appointment-actions">
                                <?php if ($appointment['status'] === 'pending' || $appointment['status'] === 'open'): ?>
                                    <button class="btn primary" onclick="confirmAppointment(<?php echo $appointment['id']; ?>)">
                                        <i class="fas fa-check"></i> Confirm
                                    </button>
                                <?php endif; ?>
                                <button class="btn outline" onclick="viewAppointment(<?php echo $appointment['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn outline" onclick="editAppointment(<?php echo $appointment['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-appointments">
                            <i class="fas fa-calendar"></i>
                            <h3>No Upcoming Appointments</h3>
                            <p>You don't have any upcoming appointments scheduled.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Inquiries Section -->
        <div class="dashboard-section" id="inquiries">
            <div class="section-header">
                <h2>Inquiries</h2>
                <p>Respond to client inquiries and messages</p>
            </div>
            <div class="management-container">
                <div class="inquiries-grid">
                    <?php if ($inquiries_result && $inquiries_result->num_rows > 0): ?>
                        <?php while($inquiry = $inquiries_result->fetch_assoc()): ?>
                        <div class="inquiry-card">
                            <div class="inquiry-header">
                                <div class="inquiry-status">
                                    <span class="status-badge <?php echo strtolower($inquiry['status']); ?>"><?php echo ucfirst($inquiry['status']); ?></span>
                                </div>
                                <div class="inquiry-date">
                                    <span class="date"><?php echo date('M d', strtotime($inquiry['created_at'])); ?></span>
                                    <span class="time"><?php echo date('g:i A', strtotime($inquiry['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="inquiry-content">
                                <h3><?php echo htmlspecialchars($inquiry['client_name']); ?></h3>
                                <div class="inquiry-details">
                                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($inquiry['client_email']); ?></span>
                                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($inquiry['client_phone']); ?></span>
                                    <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($inquiry['subject']); ?></span>
                                </div>
                                <div class="inquiry-message">
                                    <p><?php echo htmlspecialchars($inquiry['message']); ?></p>
                                </div>
                            </div>
                            <div class="inquiry-actions">
                                <button class="btn outline" onclick="viewInquiry(<?php echo $inquiry['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn primary" onclick="respondToInquiry(<?php echo $inquiry['id']; ?>)">
                                    <i class="fas fa-reply"></i> Respond
                                </button>
                                <button class="btn outline" onclick="scheduleConsultation(<?php echo $inquiry['id']; ?>)">
                                    <i class="fas fa-calendar"></i> Schedule
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-inquiries">
                            <i class="fas fa-comments"></i>
                            <h3>No Inquiries</h3>
                            <p>You don't have any inquiries yet.</p>
                        </div>
                    <?php endif; ?>
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

                    <!-- Professional Settings -->
                    <div class="settings-card">
                        <h3>Professional Settings</h3>
                        <div class="settings-list">
                            <div class="setting-item">
                                <div class="setting-info">
                                    <h4>Availability Schedule</h4>
                                    <p>Set your working hours and availability</p>
                                </div>
                                <button class="btn outline">
                                    <i class="fas fa-calendar-alt"></i> Manage
                                </button>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <h4>Service Preferences</h4>
                                    <p>Configure your service offerings</p>
                                </div>
                                <button class="btn outline">
                                    <i class="fas fa-cog"></i> Configure
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
                                    <p>Download a copy of your professional data</p>
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
    </main>

    <!-- Footer -->
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

            // Update user name in all places
            const userName = '<?php echo htmlspecialchars($user_data['fullname'] ?? 'Therapist'); ?>';
            document.querySelectorAll('.user-name').forEach(element => {
                element.textContent = userName;
            });
            
            // Profile picture upload functionality
            const profilePictureInput = document.getElementById('profile-picture-input');
            const profileAvatarImg = document.getElementById('profile-avatar-img');
            
            if (profilePictureInput && profileAvatarImg) {
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
            }

            // Profile form submission via AJAX
            const profileForm = document.getElementById('profile-form');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
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
            }

            // Form reset functions
            function resetProfileForm() {
                document.getElementById('profile-form').reset();
            }

            // Client management functions
            function viewClient(clientId) {
                // Redirect to client view page
                window.location.href = `../php/client_dashboard.php?view_id=${clientId}`;
            }

            function viewProgress(clientId) {
                // Show progress update form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'update_progress.php';
                form.innerHTML = `
                    <input type="hidden" name="client_id" value="${clientId}">
                    <input type="number" name="progress_percentage" min="0" max="100" required placeholder="Progress %">
                    <textarea name="notes" placeholder="Progress notes"></textarea>
                    <textarea name="goals_achieved" placeholder="Goals achieved"></textarea>
                    <textarea name="next_goals" placeholder="Next goals"></textarea>
                    <button type="submit">Update Progress</button>
                `;
                
                // Create modal for form
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <h3>Update Client Progress</h3>
                        ${form.outerHTML}
                        <button onclick="this.parentElement.parentElement.remove()">Cancel</button>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            // Appointment management functions
            function confirmAppointment(appointmentId) {
                // Confirm appointment
                if (confirm('Confirm this appointment?')) {
                    fetch('confirm_appointment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `appointment_id=${appointmentId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Appointment confirmed successfully!');
                            location.reload(); // Refresh the page to show updated status
                        } else {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while confirming the appointment.');
                    });
                }
            }

            function viewAppointment(appointmentId) {
                // Show appointment details
                alert(`Viewing appointment ${appointmentId} details`);
            }

            function editAppointment(appointmentId) {
                // Show appointment edit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'edit_appointment.php';
                form.innerHTML = `
                    <input type="hidden" name="appointment_id" value="${appointmentId}">
                    <input type="date" name="appointment_date" required>
                    <input type="time" name="start_time" required>
                    <textarea name="notes" placeholder="Appointment notes"></textarea>
                    <button type="submit">Update Appointment</button>
                `;
                
                // Create modal for form
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <h3>Edit Appointment</h3>
                        ${form.outerHTML}
                        <button onclick="this.parentElement.parentElement.remove()">Cancel</button>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            // Inquiry management functions
            function viewInquiry(inquiryId) {
                // Show inquiry details in a modal
                window.location.href = `view_inquiry.php?id=${inquiryId}`;
            }

            function respondToInquiry(inquiryId) {
                // Show response form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'respond_inquiry.php';
                form.innerHTML = `
                    <input type="hidden" name="inquiry_id" value="${inquiryId}">
                    <textarea name="response" required placeholder="Enter your response..." rows="5"></textarea>
                    <button type="submit">Send Response</button>
                `;
                
                // Create modal for form
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <h3>Respond to Inquiry</h3>
                        ${form.outerHTML}
                        <button onclick="this.parentElement.parentElement.remove()">Cancel</button>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            function scheduleConsultation(inquiryId) {
                // Show consultation scheduling form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'schedule_consultation.php';
                form.innerHTML = `
                    <input type="hidden" name="inquiry_id" value="${inquiryId}">
                    <input type="date" name="consultation_date" required>
                    <input type="time" name="consultation_time" required>
                    <textarea name="notes" placeholder="Consultation notes"></textarea>
                    <button type="submit">Schedule Consultation</button>
                `;
                
                // Create modal for form
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <h3>Schedule Consultation</h3>
                        ${form.outerHTML}
                        <button onclick="this.parentElement.parentElement.remove()">Cancel</button>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            function viewResponse(inquiryId) {
                // Show previous response
                window.location.href = `view_response.php?id=${inquiryId}`;
            }

            function markResolved(inquiryId) {
                // Mark inquiry as resolved
                if (confirm('Mark this inquiry as resolved?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'mark_resolved.php';
                    form.innerHTML = `<input type="hidden" name="inquiry_id" value="${inquiryId}">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            function scheduleEmergency(inquiryId) {
                // Schedule emergency session
                if (confirm('Schedule emergency session for this inquiry?')) {
                    window.location.href = `schedule_emergency.php?inquiry_id=${inquiryId}`;
                }
            }

            // Quick Add function
            function quickAdd(type) {
                switch(type) {
                    case 'appointment':
                        window.location.href = 'schedule_appointment.php';
                        break;
                    case 'note':
                        window.location.href = 'add_note.php';
                        break;
                    case 'report':
                        window.location.href = 'create_report.php';
                        break;
                    default:
                        alert('Invalid option selected');
                }
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = 0;
                    setTimeout(function() { alert.remove(); }, 500);
                });
            }, 5000);
        });
    </script>
</body>
</html> 