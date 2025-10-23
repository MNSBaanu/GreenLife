<?php
require_once('../php/dbconnect.php');

// Initialize variables
$id = $name = $category = $summary = $benefits = $image = '';
$is_active = 1;
$booking_text = 'Book Session';
$errors = [];
$success = false;
$edit_mode = false;

// Handle Delete
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $success = 'Service deleted successfully!';
    } else {
        $errors['database'] = 'Error deleting service: ' . $stmt->error;
    }
    $stmt->close();
}

// Handle Edit (load data)
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $name = $row['name'];
        $category = $row['category'];
        $summary = $row['summary'];
        $benefits = $row['benefits'];
        $image = $row['image'];
        $is_active = $row['is_active'];
        $booking_text = $row['booking_text'];
        $edit_mode = true;
    }
    $stmt->close();
}

// Handle Add or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $benefits = trim($_POST['benefits'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $booking_text = 'Book Session';
    $edit_mode = $id > 0;

    // Validation
    if (empty($name)) $errors['name'] = 'Service name is required';
    if (empty($category)) $errors['category'] = 'Category is required';
    if (empty($summary)) $errors['summary'] = 'Summary is required';
    if (empty($benefits)) $errors['benefits'] = 'Benefits are required';

    // Handle file upload (optional for edit)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/services/';
        $fileName = basename($_FILES['image']['name']);
        $uploadPath = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($uploadPath, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) $errors['image'] = 'File is not an image';
        if ($_FILES['image']['size'] > 5000000) $errors['image'] = 'File is too large (max 5MB)';
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileType, $allowedTypes)) $errors['image'] = 'Only JPG, JPEG, PNG & GIF files are allowed';
        if (file_exists($uploadPath)) $errors['image'] = 'File already exists';
        if (empty($errors['image'])) {
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors['image'] = 'There was an error uploading your file';
            } else {
                $image = $fileName;
            }
        }
    } elseif (!$edit_mode) {
        $errors['image'] = 'Image is required';
    }

    // Add or Update
    if (empty($errors)) {
        if ($edit_mode) {
            // Update
            if (!empty($image)) {
                $stmt = $conn->prepare("UPDATE services SET name=?, category=?, summary=?, benefits=?, image=?, booking_text=?, is_active=? WHERE id=?");
                $stmt->bind_param("ssssssii", $name, $category, $summary, $benefits, $image, $booking_text, $is_active, $id);
            } else {
                $stmt = $conn->prepare("UPDATE services SET name=?, category=?, summary=?, benefits=?, booking_text=?, is_active=? WHERE id=?");
                $stmt->bind_param("ssssssi", $name, $category, $summary, $benefits, $booking_text, $is_active, $id);
            }
            if ($stmt->execute()) {
                $success = 'Service updated successfully!';
                $edit_mode = false;
                $id = $name = $category = $summary = $benefits = $image = '';
                $is_active = 1;
            } else {
                $errors['database'] = 'Error updating service: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            // Add
            $stmt = $conn->prepare("INSERT INTO services (name, category, summary, benefits, image, booking_text, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $name, $category, $summary, $benefits, $image, $booking_text, $is_active);
            if ($stmt->execute()) {
                $success = 'Service added successfully!';
                $id = $name = $category = $summary = $benefits = $image = '';
                $is_active = 1;
            } else {
                $errors['database'] = 'Error saving to database: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all services
$services = [];
$result = $conn->query("SELECT * FROM services ORDER BY id DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

// Determine active tab from URL
$activeTab = $_GET['tab'] ?? 'add';
if (!in_array($activeTab, ['add', 'edit', 'delete'])) $activeTab = 'add';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services | GreenLife Wellness Center</title>
    <link rel="stylesheet" href="../css/updateservice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <?php
                $headerTitle = 'Manage Services';
                if ($activeTab === 'add') {
                    $headerTitle = 'Add New Service';
                } elseif ($activeTab === 'edit') {
                    $headerTitle = $edit_mode ? 'Editing Service' : 'Edit Service';
                } elseif ($activeTab === 'delete') {
                    $headerTitle = 'Delete Service';
                }
            ?>
            <h1><?= $headerTitle ?></h1>
            <a href="../html/admin_dashboard.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </header>

        <main class="admin-main">
            <?php if ($success): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['database'])): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errors['database']) ?>
                </div>
            <?php endif; ?>

            <!-- Tabbed Navigation -->
            <div class="tabs">
                <button data-tab="add" class="tab<?= $activeTab === 'add' ? ' active' : '' ?>">Add New Service</button>
                <button data-tab="edit" class="tab<?= $activeTab === 'edit' ? ' active' : '' ?>">Edit Service</button>
                <button data-tab="delete" class="tab<?= $activeTab === 'delete' ? ' active' : '' ?>">Delete Service</button>
            </div>

            <?php if ($activeTab === 'add'): ?>
            <div class="tab-content active" id="tab-content-add">
                <div class="admin-form-card">
                    <form action="updateservice.php?tab=add" method="POST" enctype="multipart/form-data" class="service-form">
                        <input type="hidden" name="id" value="">
                        <div class="form-group">
                            <label for="name">Service Name*</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                            <?php if (!empty($errors['name'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="category">Category*</label>
                            <input type="text" id="category" name="category" value="<?= htmlspecialchars($category) ?>" required>
                            <?php if (!empty($errors['category'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['category']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="summary">Brief Summary*</label>
                            <textarea id="summary" name="summary" rows="3" required><?= htmlspecialchars($summary) ?></textarea>
                            <?php if (!empty($errors['summary'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['summary']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="benefits">Benefits (one per line)*</label>
                            <textarea id="benefits" name="benefits" rows="5" required><?= htmlspecialchars($benefits) ?></textarea>
                            <?php if (!empty($errors['benefits'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['benefits']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="image">Service Image<?= $edit_mode ? ' (leave blank to keep current)' : '*' ?></label>
                            <input type="file" id="image" name="image" accept="image/*" <?= $edit_mode ? '' : 'required' ?> >
                            <?php if (!empty($errors['image'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['image']) ?></span>
                            <?php endif; ?>
                            <?php if ($edit_mode && $image): ?>
                                <img src="../images/services/<?= htmlspecialchars($image) ?>" alt="Current Image" style="max-width:100px;margin-top:8px;">
                            <?php endif; ?>
                        </div>

                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" value="1" <?= $is_active ? 'checked' : '' ?>>
                            <label for="is_active">Active (show on website)</label>
                        </div>

                        <button type="submit" class="submit-btn"><?= $edit_mode ? 'Update Service' : 'Add Service' ?></button>
                        <?php if ($edit_mode): ?>
                            <a href="updateservice.php" class="submit-btn" style="background:#aaa;margin-left:10px;">Cancel Edit</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php elseif ($activeTab === 'edit'): ?>
            <div class="tab-content active" id="tab-content-edit">
                <?php if ($edit_mode): ?>
                <div class="admin-form-card">
                    <h2 style="margin-bottom:1.5rem;">Editing Service: <?= htmlspecialchars($name) ?></h2>
                    <form action="updateservice.php?tab=edit&edit_id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="service-form">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                        
                        <div class="form-group">
                            <label for="name">Service Name*</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                            <?php if (!empty($errors['name'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="category">Category*</label>
                            <input type="text" id="category" name="category" value="<?= htmlspecialchars($category) ?>" required>
                            <?php if (!empty($errors['category'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['category']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="summary">Brief Summary*</label>
                            <textarea id="summary" name="summary" rows="3" required><?= htmlspecialchars($summary) ?></textarea>
                            <?php if (!empty($errors['summary'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['summary']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="benefits">Benefits (one per line)*</label>
                            <textarea id="benefits" name="benefits" rows="5" required><?= htmlspecialchars($benefits) ?></textarea>
                            <?php if (!empty($errors['benefits'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['benefits']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="image">Service Image (leave blank to keep current)</label>
                            <input type="file" id="image" name="image" accept="image/*">
                            <?php if (!empty($errors['image'])): ?>
                                <span class="error-message"><?= htmlspecialchars($errors['image']) ?></span>
                            <?php endif; ?>
                            <?php if ($edit_mode && $image): ?>
                                <img src="../images/services/<?= htmlspecialchars($image) ?>" alt="Current Image" style="max-width:100px;margin-top:8px;">
                            <?php endif; ?>
                        </div>

                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" value="1" <?= $is_active ? 'checked' : '' ?>>
                            <label for="is_active">Active (show on website)</label>
                        </div>
                        
                        <button type="submit" class="submit-btn">Update Service</button>
                        <a href="updateservice.php?tab=edit" class="submit-btn" style="background:#aaa;margin-left:10px;">Cancel</a>
                    </form>
                </div>
                <?php else: ?>
                <div class="admin-list-card">
                    <h2 style="margin-top:0;">Select a Service to Edit</h2>
                    <div class="service-card-grid">
                        <?php foreach ($services as $srv): ?>
                        <div class="service-card">
                            <div class="service-card-img">
                                <?php if ($srv['image']): ?>
                                    <img src="../images/services/<?= htmlspecialchars($srv['image']) ?>" alt="<?= htmlspecialchars($srv['name']) ?>">
                                <?php endif; ?>
                            </div>
                            <div class="service-card-content">
                                <h3><?= htmlspecialchars($srv['name']) ?></h3>
                                <div class="service-card-actions">
                                    <a href="updateservice.php?tab=edit&edit_id=<?= $srv['id'] ?>" class="action-btn edit"><i class="fas fa-edit"></i> Edit</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php elseif ($activeTab === 'delete'): ?>
            <div class="tab-content active" id="tab-content-delete">
                <div class="admin-list-card">
                    <h2 style="margin-top:2.5rem;">Delete Service</h2>
                    <div class="service-card-grid">
                        <?php foreach ($services as $srv): ?>
                        <div class="service-card">
                            <div class="service-card-img">
                                <?php if ($srv['image']): ?>
                                    <img src="../images/services/<?= htmlspecialchars($srv['image']) ?>" alt="<?= htmlspecialchars($srv['name']) ?>">
                                <?php endif; ?>
                            </div>
                            <div class="service-card-content">
                                <h3><?= htmlspecialchars($srv['name']) ?></h3>
                                <div class="service-card-actions">
                                    <form action="updateservice.php?tab=delete" method="POST">
                                        <input type="hidden" name="delete_id" value="<?= $srv['id'] ?>">
                                        <button type="submit" class="action-btn delete"><i class="fas fa-trash"></i> Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tabs .tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    window.location.href = 'updateservice.php?tab=' + tabName;
                });
            });
        });
    </script>
</body>
</html>