<?php
require_once 'config/database.php';

class Content {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createContent($title, $content, $userId) {
        $query = "INSERT INTO content (title, content, created_by) VALUES (:title, :content, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":user_id", $userId);
        
        return $stmt->execute();
    }

    public function updateContent($id, $title, $content, $userId) {
        $query = "UPDATE content SET title = :title, content = :content WHERE id = :id AND created_by = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":user_id", $userId);
        
        return $stmt->execute();
    }

    public function deleteContent($id, $userId, $isAdmin = false) {
        if ($isAdmin) {
            $query = "DELETE FROM content WHERE id = :id";
        } else {
            $query = "DELETE FROM content WHERE id = :id AND created_by = :user_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        if (!$isAdmin) {
            $stmt->bindParam(":user_id", $userId);
        }
        
        return $stmt->execute();
    }

    public function getContent($id = null) {
        if ($id) {
            $query = "SELECT c.*, u.username as author FROM content c 
                     JOIN users u ON c.created_by = u.id 
                     WHERE c.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
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