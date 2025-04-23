<?php
// Start a new session to access stored user data
session_start();
// Include required classes for authentication and content management
require_once 'classes/Auth.php';
require_once 'classes/Content.php';

// Check if user is logged in and has a valid session token
if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
    // Redirect to login page if not authenticated
    header('Location: index.php');
    exit();
}

// Create instances of Auth and Content classes
$auth = new Auth();
$content = new Content();
// Check if the current user is an admin
$isAdmin = $auth->isAdmin($_SESSION['user_id']);

// Handle content operations when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                // Only admins can create content
                if ($isAdmin) {
                    $content->createContent($_POST['title'], $_POST['content'], $_SESSION['user_id']);
                }
                break;
            case 'update':
                // Only admins can update content
                if ($isAdmin) {
                    $content->updateContent($_POST['id'], $_POST['title'], $_POST['content'], $_SESSION['user_id']);
                }
                break;
            case 'delete':
                // Users can delete their own content, admins can delete any content
                $content->deleteContent($_POST['id'], $_SESSION['user_id'], $isAdmin);
                break;
        }
    }
}

// Get all content to display
$contents = $content->getContent();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic HTML meta tags and title -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secure Auth System</title>
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styling for the dashboard */
        body {
            background-color: #f8f9fa;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .content-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <!-- Dashboard header with welcome message and action buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Welcome to Your Dashboard</h2>
                <div>
                    <?php if ($isAdmin): ?>
                        <!-- Create content button (only visible to admins) -->
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createModal">
                            Create Content
                        </button>
                    <?php endif; ?>
                    <!-- Logout button -->
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>

            <!-- Content display grid -->
            <div class="row">
                <?php foreach ($contents as $item): ?>
                    <div class="col-md-6">
                        <div class="card content-card">
                            <div class="card-body">
                                <!-- Content title -->
                                <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <!-- Content body -->
                                <p class="card-text"><?php echo htmlspecialchars($item['content']); ?></p>
                                <!-- Content author -->
                                <p class="card-text"><small class="text-muted">By: <?php echo htmlspecialchars($item['author']); ?></small></p>
                                <!-- Edit and Delete buttons (visible to admins and content owners) -->
                                <?php if ($isAdmin || $item['created_by'] == $_SESSION['user_id']): ?>
                                    <div class="btn-group">
                                        <!-- Edit button with data attributes for modal -->
                                        <button class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal"
                                                data-id="<?php echo $item['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($item['title']); ?>"
                                                data-content="<?php echo htmlspecialchars($item['content']); ?>">
                                            Edit
                                        </button>
                                        <!-- Delete form -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this content?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Create Content Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Content Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="edit-title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" id="edit-content" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize edit modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', function(event) {
                // Get data from the clicked edit button
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const title = button.getAttribute('data-title');
                const content = button.getAttribute('data-content');
                
                // Populate the edit form with the content data
                document.getElementById('edit-id').value = id;
                document.getElementById('edit-title').value = title;
                document.getElementById('edit-content').value = content;
            });
        });
    </script>
</body>
</html> 