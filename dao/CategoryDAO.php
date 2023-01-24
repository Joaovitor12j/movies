<?php

    include_once("models/Categories.php");

    class CategoryDAO implements CategoryDAOInterface {
        private $conn;
        private $url;

        public function __construct(PDO $conn, $url) {
            $this->conn = $conn;
            $this->url = $url;
        }

        public function getAllCategories() {
      
            $stmt = $this->conn->prepare("SELECT id, description FROM categories ORDER BY description");
            
            $stmt->execute();

            if($stmt->rowCount() > 0) {
      
              $categories = $stmt->fetchAll();
            
              return $categories;
              
            } else {
              return false;
            }
      
          }
    }

?>