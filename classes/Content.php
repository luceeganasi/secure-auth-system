<?php
// Include database configuration
require_once 'config/database.php';

class Content {
    // Database connection
    private $conn;

    // Constructor to initialize database connection
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new content with title and content
    public function createContent($title, $content, $userId) {
        // Insert new content into database
        $query = "INSERT INTO content (title, content, created_by) VALUES (:title, :content, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":user_id", $userId);
        
        // Return true if content creation successful
        return $stmt->execute();
    }

    // Update existing content
    public function updateContent($id, $title, $content, $userId) {
        // Update content in database (only if user is the creator)
        $query = "UPDATE content SET title = :title, content = :content WHERE id = :id AND created_by = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":user_id", $userId);
        
        // Return true if update successful
        return $stmt->execute();
    }

    // Delete content
    public function deleteContent($id, $userId, $isAdmin = false) {
        // Different query based on user role
        if ($isAdmin) {
            // Admins can delete any content
            $query = "DELETE FROM content WHERE id = :id";
        } else {
            // Regular users can only delete their own content
            $query = "DELETE FROM content WHERE id = :id AND created_by = :user_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        // Only bind user_id parameter for non-admin users
        if (!$isAdmin) {
            $stmt->bindParam(":user_id", $userId);
        }
        
        // Return true if deletion successful
        return $stmt->execute();
    }

    // Get content from database
    public function getContent($id = null) {
        if ($id) {
            // Get specific content by ID
            $query = "SELECT c.*, u.username as author FROM content c 
                     JOIN users u ON c.created_by = u.id 
                     WHERE c.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Get all content ordered by creation date
            $query = "SELECT c.*, u.username as author FROM content c 
                     JOIN users u ON c.created_by = u.id 
                     ORDER BY c.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?> 