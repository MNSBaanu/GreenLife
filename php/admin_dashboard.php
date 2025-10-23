<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../html/login.html");
    exit();
}

// Fetch admin user data
$user_id = $_SESSION['user_id'];
$user_query = "SELECT fullname, username, email, phone, profile_image, created_at FROM users WHERE id = ?";
$stmt_user = $conn->prepare($user_query);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_data = $user_result->fetch_assoc();

// Fetch clients for dashboard
$clients_query = "SELECT 
    u.id, 
    u.fullname, 
    u.email,
    u.phone,
    u.profile_image, 
    u.last_session, 
    u.assigned_therapist_id, 
    u.created_at,
    t.fullname as therapist_name,
    COUNT(a.id) as total_sessions,
    COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_sessions
FROM users u 
LEFT JOIN users t ON u.assigned_therapist_id = t.id 
LEFT JOIN appointments a ON u.id = a.client_id
WHERE u.role = 'client' 
GROUP BY u.id, u.fullname, u.email, u.phone, u.profile_image, u.last_session, u.assigned_therapist_id, u.created_at, t.fullname
ORDER BY u.created_at DESC";
$clients_result = $conn->query($clients_query);

// Fetch therapists for dashboard - fixed to use users table
$therapists_query = "SELECT 
    u.id,
    u.fullname,
    u.email,
    u.phone,
    u.profile_image,
    u.created_at,
    COUNT(c.id) as client_count
FROM users u
LEFT JOIN users c ON u.id = c.assigned_therapist_id AND c.role = 'client'
WHERE u.role = 'therapist' AND u.is_active = 1
GROUP BY u.id, u.fullname, u.email, u.phone, u.profile_image, u.created_at
ORDER BY u.id ASC";
$therapists_result = $conn->query($therapists_query);

// Fetch services for dashboard
$services_query = "SELECT id, name, category, image FROM services WHERE is_active = 1 ORDER BY name ASC";
$services_result = $conn->query($services_query);
$services = [];
while ($service = $services_result->fetch_assoc()) {
    $services[] = $service;
}

// Fetch resources for dashboard
$articles_query = "SELECT id, title, category, image, publish_date FROM articles WHERE is_active = 1 ORDER BY publish_date DESC LIMIT 6";
$videos_query = "SELECT id, title, category, youtube_id FROM videos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6";
$tips_query = "SELECT id, title, category, image FROM health_tips WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6";

$articles_result = $conn->query($articles_query);
$videos_result = $conn->query($videos_query);
$tips_result = $conn->query($tips_query);

$articles = [];
$videos = [];
$tips = [];

while ($article = $articles_result->fetch_assoc()) {
    $articles[] = $article;
}
while ($video = $videos_result->fetch_assoc()) {
    $videos[] = $video;
}
while ($tip = $tips_result->fetch_assoc()) {
    $tips[] = $tip;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | GreenLife</title>
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
                        <a href="services.php">Services</a>
                        <a href="therapists.php">Therapists</a>
                        <a href="resources.php">Resources</a>
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
                        ?>" alt="Admin" class="user-avatar">
                        <span class="user-name user-name-dropdown"><?php echo htmlspecialchars($user_data['fullname'] ?? 'Admin'); ?></span>
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
            <h1>Welcome back, <span class="user-name user-name-header"><?php echo htmlspecialchars($user_data['fullname'] ?? 'Admin'); ?></span></h1>
            <div class="header-actions">
                <div class="dropdown">
                    <button class="btn primary dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-plus"></i> Quick Add <i class="fa-solid fa-chevron-down"></i>
                </button>
                    <div class="dropdown-menu">
                        <a href="#" onclick="quickAdd('client'); return false;"><i class="fas fa-user-plus"></i> New Client</a>
                        <a href="#" onclick="quickAdd('therapist')"><i class="fas fa-user-md"></i> New Therapist</a>
                        <a href="#" onclick="quickAdd('service'); return false;"><i class="fas fa-concierge-bell"></i> New Service</a>
                        <a href="#" onclick="quickAdd('article')"><i class="fas fa-newspaper"></i> New Article</a>
                        <a href="#" onclick="quickAdd('video')"><i class="fab fa-youtube"></i> New Video</a>
                        <a href="#" onclick="quickAdd('tip')"><i class="fas fa-heart"></i> New Health Tip</a>
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
            <button class="tab-btn" data-section="user-management">
                <i class="fas fa-users"></i> User Management
            </button>
            <button class="tab-btn" data-section="appointment-management">
                <i class="fas fa-calendar-alt"></i> Appointment Management
            </button>
            <button class="tab-btn" data-section="service-management">
                <i class="fas fa-concierge-bell"></i> Service Management
            </button>
            <button class="tab-btn" data-section="resource-management">
                <i class="fas fa-book-open"></i> Resource Management
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
                                <h3><?php echo htmlspecialchars($user_data['fullname'] ?? 'Admin'); ?></h3>
                                <p class="username">@<?php echo htmlspecialchars($user_data['username'] ?? 'admin'); ?></p>
                                <p class="role">Administrator</p>
                            </div>
                        </div>
                        
                        <!-- Admin Information -->
                        <div class="wellness-info">
                            <div class="info-item">
                                <span class="label">Role:</span>
                                <span class="value">System Administrator</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Permissions:</span>
                                <span class="value">Full Access</span>
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

        <!-- User Management Section -->
        <div class="dashboard-section" id="user-management">
            <div class="section-header">
                <h2>User Management</h2>
                <p>Manage client and therapist accounts</p>
            </div>
            <div class="management-container">
                <!-- Client Management Subsection -->
                <div class="user-subsection">
                    <h3><i class="fas fa-users"></i> Client Management</h3>
                    <div class="clients-grid">
                        <?php if ($clients_result && $clients_result->num_rows > 0): ?>
                            <?php while($client = $clients_result->fetch_assoc()): ?>
                            <div class="client-card">
                                <div class="client-header">
                                            <img src="<?php 
                                                $profile_img = $client['profile_image'] ?? 'default_avatar.png';
                                                if (empty($profile_img) || $profile_img == 'default_avatar.png') {
                                                    // Use cycling fallback only if no specific image is assigned
                                                    echo '../images/client' . (($client['id'] % 3) + 1) . '.jpg';
                                                } else {
                                                    // Use the specific image from database
                                                if (strpos($profile_img, 'profile_') === 0) {
                                                    echo '../images/uploads/' . htmlspecialchars($profile_img);
                                                } else {
                                                    echo '../images/' . htmlspecialchars($profile_img);
                                                    }
                                                }
                                    ?>" alt="<?php echo htmlspecialchars($client['fullname']); ?>"
                                    onerror="this.src='../images/client<?php echo ($client['id'] % 3) + 1; ?>.jpg'; this.style.opacity='0.8';">
                                    <div class="client-status">
                                        <span class="status-badge active">Active</span>
                                        </div>
                                </div>
                                <div class="client-content">
                                    <h3><?php echo htmlspecialchars($client['fullname']); ?></h3>
                                    <p class="client-email"><?php echo htmlspecialchars($client['email']); ?></p>
                                    <div class="client-therapist">
                                        <span><i class="fas fa-user-md"></i> <?php echo htmlspecialchars($client['therapist_name'] ?? 'Not assigned'); ?></span>
                                    </div>
                                    <div class="client-stats">
                                        <span><i class="fas fa-calendar"></i> <?php echo $client['last_session'] ? date("M d, Y", strtotime($client['last_session'])) : 'No sessions yet'; ?></span>
                                    </div>
                                </div>
                                <div class="client-actions">
                                    <button class="btn outline" onclick="viewClient(<?php echo $client['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn danger" onclick="deleteClient(<?php echo $client['id']; ?>, '<?php echo htmlspecialchars($client['fullname']); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-clients">
                                <i class="fas fa-users"></i>
                                <h3>No Clients Found</h3>
                                <p>No clients are currently registered in the system.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Therapist Management Subsection -->
                <div class="user-subsection">
                    <h3><i class="fas fa-user-md"></i> Therapist Management</h3>
                    <div class="therapists-grid">
                        <?php if ($therapists_result && $therapists_result->num_rows > 0): ?>
                            <?php while($therapist = $therapists_result->fetch_assoc()): ?>
                            <div class="therapist-card">
                                <div class="therapist-header">
                                            <img src="<?php 
                                                $profile_img = $therapist['profile_image'] ?? 'default_avatar.png';
                                                if (strpos($profile_img, 'profile_') === 0) {
                                                    echo '../images/uploads/' . htmlspecialchars($profile_img);
                                                } else {
                                                    echo '../images/' . htmlspecialchars($profile_img);
                                                }
                                    ?>" alt="<?php echo htmlspecialchars($therapist['fullname']); ?>"
                                    onerror="this.src='../images/therapist1.jpg'; this.style.opacity='0.8';">
                                    <div class="therapist-status">
                                        <span class="status-badge active">Active</span>
                                            </div>
                                        </div>
                                <div class="therapist-content">
                                    <h3><?php echo htmlspecialchars($therapist['fullname']); ?></h3>
                                    <p class="therapist-email"><?php echo htmlspecialchars($therapist['email']); ?></p>
                                    <div class="therapist-specialization">
                                        <span><i class="fas fa-stethoscope"></i> Wellness Therapist</span>
                                    </div>
                                    <div class="therapist-stats">
                                        <span><i class="fas fa-users"></i> <?php echo htmlspecialchars($therapist['client_count']); ?> clients</span>
                                        <span><i class="fas fa-calendar"></i> Joined <?php echo date('M Y', strtotime($therapist['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="therapist-actions">
                                    <button class="btn outline" onclick="viewTherapist(<?php echo $therapist['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn outline" onclick="manageSchedule(<?php echo $therapist['id']; ?>)">
                                        <i class="fas fa-calendar"></i> Schedule
                                    </button>
                                    <button class="btn danger" onclick="deleteTherapist(<?php echo $therapist['id']; ?>, '<?php echo htmlspecialchars($therapist['fullname']); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-therapists">
                                <i class="fas fa-user-md"></i>
                                <h3>No Therapists Found</h3>
                                <p>No therapists are currently registered in the system.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment Management Section -->
        <div class="dashboard-section" id="appointment-management">
            <div class="section-header">
                <h2>Appointment Management</h2>
                <p>Manage and monitor all appointments and schedules</p>
            </div>
            <div class="management-container">
                <div class="appointments-grid">
                    <!-- Sample appointment data - in real implementation, this would come from database -->
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <div class="appointment-status">
                                <span class="status-badge confirmed">Confirmed</span>
                            </div>
                            <div class="appointment-date">
                                <span class="date">Dec 15</span>
                                <span class="time">10:00 AM</span>
                            </div>
                        </div>
                        <div class="appointment-content">
                            <h3>Ayurvedic Consultation</h3>
                            <div class="appointment-details">
                                <span><i class="fas fa-user"></i> Sarah Johnson</span>
                                <span><i class="fas fa-user-md"></i> Thehan De Silva</span>
                                <span><i class="fas fa-clock"></i> 60 min session</span>
                            </div>
                            <div class="appointment-notes">
                                <p>Follow-up consultation for stress management</p>
                            </div>
                        </div>
                        <div class="appointment-actions">
                            <button class="btn outline" onclick="viewAppointment(1)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn outline" onclick="editAppointment(1)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn outline" onclick="rescheduleAppointment(1)">
                                <i class="fas fa-calendar-alt"></i> Reschedule
                            </button>
                        </div>
                    </div>

                    <div class="appointment-card">
                        <div class="appointment-header">
                            <div class="appointment-status">
                                <span class="status-badge pending">Pending</span>
                            </div>
                            <div class="appointment-date">
                                <span class="date">Dec 16</span>
                                <span class="time">2:30 PM</span>
                            </div>
                        </div>
                        <div class="appointment-content">
                            <h3>Yoga Session</h3>
                            <div class="appointment-details">
                                <span><i class="fas fa-user"></i> Michael Brown</span>
                                <span><i class="fas fa-user-md"></i> Kiara Jayawardena</span>
                                <span><i class="fas fa-clock"></i> 45 min session</span>
                            </div>
                            <div class="appointment-notes">
                                <p>Beginner yoga for flexibility improvement</p>
                            </div>
                        </div>
                        <div class="appointment-actions">
                            <button class="btn outline" onclick="viewAppointment(2)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn outline" onclick="editAppointment(2)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn outline" onclick="rescheduleAppointment(2)">
                                <i class="fas fa-calendar-alt"></i> Reschedule
                            </button>
                        </div>
                    </div>

                    <div class="appointment-card">
                        <div class="appointment-header">
                            <div class="appointment-status">
                                <span class="status-badge completed">Completed</span>
                            </div>
                            <div class="appointment-date">
                                <span class="date">Dec 14</span>
                                <span class="time">11:00 AM</span>
                            </div>
                        </div>
                        <div class="appointment-content">
                            <h3>Massage Therapy</h3>
                            <div class="appointment-details">
                                <span><i class="fas fa-user"></i> Emma Wilson</span>
                                <span><i class="fas fa-user-md"></i> Rayan Dias</span>
                                <span><i class="fas fa-clock"></i> 90 min session</span>
                            </div>
                            <div class="appointment-notes">
                                <p>Deep tissue massage for back pain relief</p>
                            </div>
                        </div>
                        <div class="appointment-actions">
                            <button class="btn outline" onclick="viewAppointment(3)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn outline" onclick="viewReport(3)">
                                <i class="fas fa-file-alt"></i> Report
                            </button>
                            <button class="btn outline" onclick="scheduleFollowUp(3)">
                                <i class="fas fa-calendar-plus"></i> Follow-up
                            </button>
                        </div>
                    </div>

                    <div class="appointment-card">
                        <div class="appointment-header">
                            <div class="appointment-status">
                                <span class="status-badge cancelled">Cancelled</span>
                            </div>
                            <div class="appointment-date">
                                <span class="date">Dec 13</span>
                                <span class="time">3:00 PM</span>
                            </div>
                        </div>
                        <div class="appointment-content">
                            <h3>Meditation Session</h3>
                            <div class="appointment-details">
                                <span><i class="fas fa-user"></i> David Lee</span>
                                <span><i class="fas fa-user-md"></i> Kiara Jayawardena</span>
                                <span><i class="fas fa-clock"></i> 30 min session</span>
                            </div>
                            <div class="appointment-notes">
                                <p>Cancelled due to client emergency</p>
                            </div>
                        </div>
                        <div class="appointment-actions">
                            <button class="btn outline" onclick="viewAppointment(4)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn outline" onclick="rescheduleAppointment(4)">
                                <i class="fas fa-calendar-alt"></i> Reschedule
                            </button>
                            <button class="btn danger" onclick="deleteAppointment(4)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
                   </div>
               </div>

        <!-- Service Management Section -->
        <div class="dashboard-section" id="service-management">
            <div class="section-header">
                    <h2>Service Management</h2>
                <p>Manage wellness services and packages</p>
                    </div>
            <div class="management-container">
                <div class="services-grid">
                    <?php foreach ($services as $service): ?>
                    <div class="service-card">
                            <div class="service-header">
                                <img src="../images/services/<?php echo htmlspecialchars($service['image'] ?? 'default-service.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($service['name']); ?>"
                                     onerror="this.src='../images/services/default-service.jpg'">
                                <div class="service-status">
                                    <span class="status-badge active">Active</span>
                                </div>
                            </div>
                            <div class="service-content">
                                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                                <p class="service-category"><?php echo htmlspecialchars($service['category']); ?></p>
                                <div class="service-stats">
                                    <span><i class="fas fa-users"></i> 45 clients</span>
                                    <span><i class="fas fa-star"></i> 4.8 rating</span>
                                </div>
                            </div>
                            <div class="service-actions">
                                <button class="btn outline" onclick="editService(<?php echo $service['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn danger" onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['name']); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Resource Management Section -->
        <div class="dashboard-section" id="resource-management">
            <div class="section-header">
                <h2>Resource Management</h2>
                <p>Manage wellness articles, videos, and health tips</p>
            </div>
            <div class="management-container">
                <!-- Articles Management -->
                <div class="resource-section">
                    <h3><i class="fas fa-newspaper"></i> Articles</h3>
                    <div class="resources-grid">
                        <?php foreach ($articles as $article): ?>
                        <div class="resource-card">
                            <div class="resource-header">
                                <img src="../images/<?php echo htmlspecialchars($article['image'] ?? 'default-article.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($article['title']); ?>"
                                     onerror="this.src='../images/ayurveda-tips.jpg'; this.style.opacity='0.8';">
                                <div class="resource-status">
                                    <span class="status-badge active">Active</span>
                                </div>
                            </div>
                            <div class="resource-content">
                                <h4><?php echo htmlspecialchars($article['title']); ?></h4>
                                <p class="resource-category"><?php echo htmlspecialchars($article['category']); ?></p>
                                <div class="resource-meta">
                                    <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($article['publish_date'])); ?></span>
                                </div>
                            </div>
                            <div class="resource-actions">
                                <button class="btn outline" onclick="editResource('article', <?php echo $article['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn danger" onclick="deleteResource('article', <?php echo $article['id']; ?>, '<?php echo htmlspecialchars($article['title']); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Videos Management -->
                <div class="resource-section">
                    <h3><i class="fab fa-youtube"></i> Videos</h3>
                    <div class="resources-grid">
                        <?php foreach ($videos as $video): ?>
                        <div class="resource-card">
                            <div class="resource-header">
                                <img src="https://img.youtube.com/vi/<?php echo htmlspecialchars($video['youtube_id']); ?>/mqdefault.jpg" 
                                     alt="<?php echo htmlspecialchars($video['title']); ?>"
                                     onerror="this.src='../images/meditation.jpg'; this.style.opacity='0.8';">
                                <div class="resource-status">
                                    <span class="status-badge active">Active</span>
                                </div>
                            </div>
                            <div class="resource-content">
                                <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                                <p class="resource-category"><?php echo htmlspecialchars($video['category']); ?></p>
                                <div class="resource-meta">
                                    <span><i class="fab fa-youtube"></i> YouTube Video</span>
                                </div>
                            </div>
                            <div class="resource-actions">
                                <button class="btn outline" onclick="editResource('video', <?php echo $video['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn danger" onclick="deleteResource('video', <?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['title']); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Health Tips Management -->
                <div class="resource-section">
                    <h3><i class="fas fa-heart"></i> Health Tips</h3>
                    <div class="resources-grid">
                        <?php foreach ($tips as $tip): ?>
                        <div class="resource-card">
                            <div class="resource-header">
                                <img src="../images/<?php echo htmlspecialchars($tip['image'] ?? 'default-tip.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($tip['title']); ?>"
                                     onerror="this.src='../images/nutrition-tip.jpg'; this.style.opacity='0.8';">
                                <div class="resource-status">
                                    <span class="status-badge active">Active</span>
                                </div>
                            </div>
                            <div class="resource-content">
                                <h4><?php echo htmlspecialchars($tip['title']); ?></h4>
                                <p class="resource-category"><?php echo htmlspecialchars($tip['category']); ?></p>
                                <div class="resource-meta">
                                    <span><i class="fas fa-clock"></i> Daily Tip</span>
                                </div>
                            </div>
                            <div class="resource-actions">
                                <button class="btn outline" onclick="editResource('tip', <?php echo $tip['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn danger" onclick="deleteResource('tip', <?php echo $tip['id']; ?>, '<?php echo htmlspecialchars($tip['title']); ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
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
        document.addEventListener('DOMContentLoaded', function() {
            
            // Tab navigation
            const tabBtns = document.querySelectorAll('.tab-btn');
            const dashboardSections = document.querySelectorAll('.dashboard-section');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetSection = this.getAttribute('data-section');
                    
                    // Remove active class from all tabs and sections
                    tabBtns.forEach(b => b.classList.remove('active'));
                    dashboardSections.forEach(section => section.classList.remove('active'));
                    
                    // Add active class to clicked tab and target section
                    this.classList.add('active');
                    document.getElementById(targetSection).classList.add('active');
                });
            });

            // Quick Add function
            function quickAdd(type) {
                switch(type) {
                    case 'client':
                        showAddClientModal();
                        break;
                    case 'therapist':
                        showAddTherapistModal();
                        break;
                    case 'service':
                        showAddServiceModal();
                        break;
                    case 'article':
                        showAddArticleModal();
                        break;
                    case 'video':
                        showAddVideoModal();
                        break;
                    case 'tip':
                        showAddTipModal();
                        break;
                    default:
                        alert('Invalid option selected');
                }
            }

            // Add Client Modal Functions
            function showAddClientModal() {
                const modal = document.getElementById('addClientModal');
                if (modal) {
                    modal.style.display = 'block';
                }
            }

            function closeAddClientModal() {
                const modal = document.getElementById('addClientModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.getElementById('addClientForm').reset();
                }
            }

            // Add Service Modal Functions
            function showAddServiceModal() {
                const modal = document.getElementById('addServiceModal');
                if (modal) {
                    modal.style.display = 'block';
                }
            }

            function closeAddServiceModal() {
                const modal = document.getElementById('addServiceModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.getElementById('addServiceForm').reset();
                }
            }

            // Add Therapist Modal Functions
            function showAddTherapistModal() {
                const modal = document.getElementById('addTherapistModal');
                if (modal) {
                    modal.style.display = 'block';
                }
            }

            function closeAddTherapistModal() {
                const modal = document.getElementById('addTherapistModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.getElementById('addTherapistForm').reset();
                }
            }

            // Add Article Modal Functions
            function showAddArticleModal() {
                const modal = document.getElementById('addArticleModal');
                if (modal) {
                    modal.style.display = 'block';
                }
            }

            function closeAddArticleModal() {
                const modal = document.getElementById('addArticleModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.getElementById('addArticleForm').reset();
                }
            }

            // Add Video Modal Functions
            function showAddVideoModal() {
                const modal = document.getElementById('addVideoModal');
                if (modal) {
                    modal.style.display = 'block';
                }
            }

            function closeAddVideoModal() {
                const modal = document.getElementById('addVideoModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.getElementById('addVideoForm').reset();
                }
            }

            // Add Tip Modal Functions
            function showAddTipModal() {
                const modal = document.getElementById('addTipModal');
                if (modal) {
                    modal.style.display = 'block';
                }
            }

            function closeAddTipModal() {
                const modal = document.getElementById('addTipModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.getElementById('addTipForm').reset();
                }
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                const clientModal = document.getElementById('addClientModal');
                const serviceModal = document.getElementById('addServiceModal');
                const therapistModal = document.getElementById('addTherapistModal');
                const articleModal = document.getElementById('addArticleModal');
                const videoModal = document.getElementById('addVideoModal');
                const tipModal = document.getElementById('addTipModal');
                if (event.target === clientModal) {
                    closeAddClientModal();
                }
                if (event.target === serviceModal) {
                    closeAddServiceModal();
                }
                if (event.target === therapistModal) {
                    closeAddTherapistModal();
                }
                if (event.target === articleModal) {
                    closeAddArticleModal();
                }
                if (event.target === videoModal) {
                    closeAddVideoModal();
                }
                if (event.target === tipModal) {
                    closeAddTipModal();
                }
            }

            // Make functions globally available
            window.quickAdd = quickAdd;
            window.showAddClientModal = showAddClientModal;
            window.closeAddClientModal = closeAddClientModal;
            window.showAddServiceModal = showAddServiceModal;
            window.closeAddServiceModal = closeAddServiceModal;
            window.showAddTherapistModal = showAddTherapistModal;
            window.closeAddTherapistModal = closeAddTherapistModal;
            window.showAddArticleModal = showAddArticleModal;
            window.closeAddArticleModal = closeAddArticleModal;
            window.showAddVideoModal = showAddVideoModal;
            window.closeAddVideoModal = closeAddVideoModal;
            window.showAddTipModal = showAddTipModal;
            window.closeAddTipModal = closeAddTipModal;
            
            // Handle success/error messages from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success');
            const error = urlParams.get('error');
            const clientName = urlParams.get('client_name');
            const serviceName = urlParams.get('service_name');
            const therapistName = urlParams.get('therapist_name');
            const articleName = urlParams.get('article_name');
            const videoName = urlParams.get('video_name');
            const tipName = urlParams.get('tip_name');
            const deletedName = urlParams.get('deleted_name');
            const deletedType = urlParams.get('deleted_type');
            
            const alertContainer = document.getElementById('alert-container');
            
            if (success === 'client_added' && clientName) {
                        alertContainer.innerHTML = `
                    <div class="alert success" id="main-alert">
                                        <i class="fas fa-check-circle"></i>
                        Client "${clientName}" added successfully!
                            </div>
                        `;
                        
                        // Auto-hide alert after 3 seconds
                        setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                        }, 3000);
                        
                // Clear URL parameters
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (success === 'service_added' && serviceName) {
                        alertContainer.innerHTML = `
                    <div class="alert success" id="main-alert">
                        <i class="fas fa-check-circle"></i>
                        Service "${serviceName}" added successfully!
                            </div>
                        `;
                        
                        setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                        }, 3000);
                        
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (success === 'therapist_added' && therapistName) {
                alertContainer.innerHTML = `
                    <div class="alert success" id="main-alert">
                        <i class="fas fa-check-circle"></i>
                        Therapist "${therapistName}" added successfully!
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (success === 'article_added' && articleName) {
                alertContainer.innerHTML = `
                    <div class="alert success" id="main-alert">
                        <i class="fas fa-check-circle"></i>
                        Article "${articleName}" added successfully!
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (success === 'video_added' && videoName) {
                alertContainer.innerHTML = `
                    <div class="alert success" id="main-alert">
                        <i class="fas fa-check-circle"></i>
                        Video "${videoName}" added successfully!
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (success === 'tip_added' && tipName) {
                alertContainer.innerHTML = `
                    <div class="alert success" id="main-alert">
                        <i class="fas fa-check-circle"></i>
                        Health Tip "${tipName}" added successfully!
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (success === 'deleted' && deletedName && deletedType) {
                alertContainer.innerHTML = `
                    <div class="alert success" id="main-alert">
                        <i class="fas fa-check-circle"></i>
                        ${deletedType} "${deletedName}" deleted successfully!
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (error === 'missing_fields') {
                        alertContainer.innerHTML = `
                    <div class="alert error" id="main-alert">
                                <i class="fas fa-exclamation-circle"></i>
                        Please fill all required fields.
                            </div>
                        `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (error === 'user_exists') {
                alertContainer.innerHTML = `
                    <div class="alert error" id="main-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        Username or email already exists.
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (error === 'invalid_email') {
                alertContainer.innerHTML = `
                    <div class="alert error" id="main-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        Please enter a valid email address.
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (error === 'invalid_url') {
                alertContainer.innerHTML = `
                    <div class="alert error" id="main-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        Please enter a valid video URL.
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (error === 'database_error') {
                alertContainer.innerHTML = `
                    <div class="alert error" id="main-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        An error occurred. Please try again.
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (error === 'delete_failed') {
                alertContainer.innerHTML = `
                    <div class="alert error" id="main-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        Failed to delete item. Please try again.
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (error === 'client_not_found' || error === 'therapist_not_found' || error === 'service_not_found') {
                alertContainer.innerHTML = `
                    <div class="alert error" id="main-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        Item not found. It may have been already deleted.
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (error === 'invalid_request') {
                alertContainer.innerHTML = `
                    <div class="alert error" id="main-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        Invalid request. Please try again.
                    </div>
                `;
                
                setTimeout(function() {
                    const alertBox = document.getElementById('main-alert');
                    if(alertBox) alertBox.remove();
                }, 3000);
                
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>

    <!-- Delete Functions - Global Scope -->
    <script>
        function deleteClient(clientId, clientName) {
            if (confirm(`Are you sure you want to delete ${clientName}?`)) {
                window.location.href = `delete_client.php?id=${clientId}`;
            }
        }

        function deleteTherapist(therapistId, therapistName) {
            if (confirm(`Are you sure you want to delete ${therapistName}?`)) {
                window.location.href = `delete_therapist.php?id=${therapistId}`;
            }
        }

        function deleteService(serviceId, serviceName) {
            if (confirm(`Are you sure you want to delete ${serviceName}?`)) {
                window.location.href = `delete_service.php?id=${serviceId}`;
            }
        }

        function deleteResource(type, id, name) {
            if (confirm(`Are you sure you want to delete ${name}?`)) {
                window.location.href = `delete_${type}.php?id=${id}`;
            }
        }
    </script>

    <!-- Add Client Modal -->
    <div id="addClientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Client</h2>
            </div>
            <form id="addClientForm" action="add_client.php" method="POST">
                <div class="form-group">
                    <label for="fullname">Full Name *</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeAddClientModal()" class="btn secondary">Cancel</button>
                    <button type="submit" class="btn primary">Add Client</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Service</h2>
            </div>
            <form id="addServiceForm" action="add_service.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="service_name">Service Name *</label>
                    <input type="text" id="service_name" name="service_name" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Traditional">Traditional</option>
                        <option value="Mind-Body">Mind-Body</option>
                        <option value="Wellness">Wellness</option>
                        <option value="Bodywork">Bodywork</option>
                        <option value="Holistic">Holistic</option>
                        <option value="Rehabilitation">Rehabilitation</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="summary">Summary *</label>
                    <textarea id="summary" name="summary" rows="3" required placeholder="Brief description of the service"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="benefits">Benefits *</label>
                    <textarea id="benefits" name="benefits" rows="4" required placeholder="List the benefits of this service"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="booking_text">Booking Button Text</label>
                    <input type="text" id="booking_text" name="booking_text" value="Book Session" placeholder="e.g., Book Session, Book Consultation">
                </div>
                
                <div class="form-group">
                    <label for="service_image">Service Image</label>
                    <input type="file" id="service_image" name="service_image" accept="image/*">
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeAddServiceModal()" class="btn secondary">Cancel</button>
                    <button type="submit" class="btn primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Therapist Modal -->
    <div id="addTherapistModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Therapist</h2>
            </div>
            <form id="addTherapistForm" action="add_therapist.php" method="POST">
                <div class="form-group">
                    <label for="fullname">Full Name *</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="specialization">Specialization *</label>
                    <select id="specialization" name="specialization" required>
                        <option value="">Select Specialization</option>
                        <option value="Wellness Therapy">Wellness Therapy</option>
                        <option value="Mind-Body Therapy">Mind-Body Therapy</option>
                        <option value="Physical Therapy">Physical Therapy</option>
                        <option value="Holistic Healing">Holistic Healing</option>
                        <option value="Ayurveda">Ayurveda</option>
                        <option value="Yoga Therapy">Yoga Therapy</option>
                        <option value="Nutrition Counseling">Nutrition Counseling</option>
                        <option value="Stress Management">Stress Management</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="experience">Years of Experience *</label>
                    <input type="number" id="experience" name="experience" min="0" max="50" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeAddTherapistModal()" class="btn secondary">Cancel</button>
                    <button type="submit" class="btn primary">Add Therapist</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Article Modal -->
    <div id="addArticleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Article</h2>
            </div>
            <form id="addArticleForm" action="add_article.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Article Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Wellness Tips">Wellness Tips</option>
                        <option value="Health & Nutrition">Health & Nutrition</option>
                        <option value="Mental Health">Mental Health</option>
                        <option value="Physical Wellness">Physical Wellness</option>
                        <option value="Lifestyle">Lifestyle</option>
                        <option value="Mindfulness">Mindfulness</option>
                        <option value="Exercise & Fitness">Exercise & Fitness</option>
                        <option value="Alternative Medicine">Alternative Medicine</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="summary">Summary *</label>
                    <textarea id="summary" name="summary" rows="3" required placeholder="Brief summary of the article"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" rows="8" required placeholder="Full article content"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="author">Author *</label>
                    <input type="text" id="author" name="author" required>
                </div>
                
                <div class="form-group">
                    <label for="article_image">Article Image</label>
                    <input type="file" id="article_image" name="article_image" accept="image/*">
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeAddArticleModal()" class="btn secondary">Cancel</button>
                    <button type="submit" class="btn primary">Add Article</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Video Modal -->
    <div id="addVideoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Video</h2>
            </div>
            <form id="addVideoForm" action="add_video.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Video Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Wellness Tips">Wellness Tips</option>
                        <option value="Health & Nutrition">Health & Nutrition</option>
                        <option value="Mental Health">Mental Health</option>
                        <option value="Physical Wellness">Physical Wellness</option>
                        <option value="Lifestyle">Lifestyle</option>
                        <option value="Mindfulness">Mindfulness</option>
                        <option value="Exercise & Fitness">Exercise & Fitness</option>
                        <option value="Alternative Medicine">Alternative Medicine</option>
                        <option value="Yoga & Meditation">Yoga & Meditation</option>
                        <option value="Nutrition Guides">Nutrition Guides</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="summary">Summary *</label>
                    <textarea id="summary" name="summary" rows="3" required placeholder="Brief summary of the video"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="video_url">Video URL *</label>
                    <input type="url" id="video_url" name="video_url" required placeholder="YouTube, Vimeo, or direct video URL">
                </div>
                
                <div class="form-group">
                    <label for="duration">Duration (minutes) *</label>
                    <input type="number" id="duration" name="duration" min="1" max="300" required>
                </div>
                
                <div class="form-group">
                    <label for="author">Author/Instructor *</label>
                    <input type="text" id="author" name="author" required>
                </div>
                
                <div class="form-group">
                    <label for="thumbnail">Thumbnail Image</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeAddVideoModal()" class="btn secondary">Cancel</button>
                    <button type="submit" class="btn primary">Add Video</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Tip Modal -->
    <div id="addTipModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Health Tip</h2>
            </div>
            <form id="addTipForm" action="add_tip.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Tip Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Nutrition">Nutrition</option>
                        <option value="Exercise">Exercise</option>
                        <option value="Mental Health">Mental Health</option>
                        <option value="Sleep">Sleep</option>
                        <option value="Hydration">Hydration</option>
                        <option value="Stress Management">Stress Management</option>
                        <option value="Mindfulness">Mindfulness</option>
                        <option value="Lifestyle">Lifestyle</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="content">Tip Content *</label>
                    <textarea id="content" name="content" rows="6" required placeholder="Write your health tip here"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="author">Author *</label>
                    <input type="text" id="author" name="author" required>
                </div>
                
                <div class="form-group">
                    <label for="tip_image">Tip Image</label>
                    <input type="file" id="tip_image" name="tip_image" accept="image/*">
                </div>
                
                <div class="form-actions">
                    <button type="button" onclick="closeAddTipModal()" class="btn secondary">Cancel</button>
                    <button type="submit" class="btn primary">Add Tip</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>