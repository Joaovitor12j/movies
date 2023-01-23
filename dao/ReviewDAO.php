<?php

  include_once("models/Review.php");
  include_once("models/Message.php");

  // Necessário para resgatar dados do usuário
  include_once("dao/userDAO.php");

  class ReviewDAO implements REviewDAOInterface {

    private $conn;
    private $url;
    private $message;

    public function __construct(PDO $conn, $url) {
      $this->conn = $conn;
      $this->url = $url;
      $this->message = new Message($url);
    }

    public function buildReview($data) {

      $reviewObject = new Review();

      $reviewObject->id = $data["id"];
      $reviewObject->rating = $data["rating"];
      $reviewObject->review = $data["review"];
      $reviewObject->user_id = $data["user_id"];
      $reviewObject->movie_id = $data["movie_id"];

      return $reviewObject;

    }

    public function create(Review $review) {

      $stmt = $this->conn->prepare("INSERT INTO review (
        rating, review, user_id, movie_id
      ) VALUES (
        :rating, :review, :user_id, :movie_id
      )");

      $stmt->bindParam(":rating", $review->rating);
      $stmt->bindParam(":review", $review->review);
      $stmt->bindParam(":user_id", $review->user_id);
      $stmt->bindParam(":movie_id", $review->movie_id);

      $stmt->execute();

      // Redireciona e apresenta mensagem de sucesso
      $this->message->setMessage("Comentário adicionado com sucesso!", "success", "index.php");

    }

    
    public function getMovieReviews($id) {

      $reviews = [];

      // encontrar as reviews na tabela de review
      $stmt = $this->conn->prepare("SELECT * FROM review WHERE movie_id = :id");

      $stmt->bindParam("id", $id);

      $stmt->execute();

      if($stmt->rowCount() > 0) {

        $userDao = new userDAO($this->conn, $this->url);

        $reviewsArray = $stmt->fetchAll();

        foreach($reviewsArray as $review) {

          $reviewObject = $this->buildReview($review);

          // pegar dados do usuário
          $user = $userDao->findById($reviewObject->user_id);

          // Adiciona usuário na review
          $reviewObject->user = $user;
          
          $reviews[] = $reviewObject;

        }
        
      }

      return $reviews;

    }

    public function hasAlreadyReviewed($id, $userId) {

      // verificar se usuário já fez review no filme
      $stmt = $this->conn->prepare("SELECT * FROM review WHERE movie_id = :id AND user_id = :userid");

      $stmt->bindParam(":id", $id);
      $stmt->bindParam(":userid", $userId);

      $stmt->execute();

      if($stmt->rowCount() > 0) {
        return true;
      } else {
        return false;
      }

    }

    // Recebe todas as avaliações de um filme, e calcula a média
    public function getRatings($id) {

      $stmt = $this->conn->prepare("SELECT * FROM review WHERE movie_id = :id");

      $stmt->bindParam(":id", $id);

      $stmt->execute();

      $rating = "Não avaliado";

      if($stmt->rowCount() > 0) {

        $rating = 0;

        $reviews = $stmt->fetchAll();

        foreach($reviews as $review) {
          $rating += $review["rating"];
        }

        $rating = $rating / count($reviews);
  
      }

      return $rating;

    }

  }