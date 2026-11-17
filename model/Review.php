<?php
require_once "framework/Model.php";

class Review extends Model{
    public function __construct(
        private int $id,
        private int $item,
        private int $author,
        private int $rating,
        private ?string $comment,
        private string $created_at
    )
    {}

    private static array $rules;

    private static function getRules(): array {
        if (!isset(self::$rules)) {
            $config = file_exists("config/dev.ini") 
                ? parse_ini_file("config/dev.ini", true) 
                : parse_ini_file("config/prod.ini", true);
            
            self::$rules = $config["RulesReview"];
        }
        return self::$rules;
    }

    public function getId(): int{
        return $this->id;
    }
    public function getItem(): int{
        return $this->item;
    }
    public function getAuthor(): int{
        return $this->author;
    }
    public function getRating(): int{
        return $this->rating;
    }
    public function getComment(): ?string{
        return $this->comment;
    }
    public function getCreationDate(): string{
        return $this->created_at;
    }

    public function persist(): Review{
        
            
            self::execute(
                "INSERT INTO Reviews(item,author,rating,comment,created_at)
                VALUES(?,?,?,?,?)",
                [$this->item,$this->author,$this->rating,$this->comment,$this->created_at]
            );
            $this->id = (int)self::lastInsertId();
        
        
        return $this;
    }

    public function delete(): void{
        self::execute(
            "DELETE FROM Reviews 
            WHERE id = :id",
            [
                "id" => $this->id     
            ]
        );   
             
    }

    public static function exist(int $id): bool{
        $query = self::execute("SELECT 1 FROM Reviews where id = :id", ["id" => $id]);
        return $query->fetch() !== false;
    }

    private function validate_id_exists(?int $revID): array {
        if ($revID === null || !self::exist($revID)) {
            return ["It looks like an incorrect review has been selected."];
        }
        return [];
    }

    public static function getReviewsByItem(int $item_id) : array{
        $query = self::execute(
            "SELECT * from Reviews
            where item= :item_id
            Order By created_at DESC",['item_id'=>$item_id]
        );
        $data = $query->fetchAll();
        if (!$data) {
            return [];
        }
        $rev =[];
        foreach ($data as $key => $value) {
            $rev[] = new Review(
            $value['id'],
            $value['item'],
            $value['author'],
            $value['rating'],
            $value['comment'],
            $value['created_at']            
        );
        }
        return $rev;
    }

    public static function getReviewById(int $id) : ?Review {
        $query = self::execute(
            "SELECT * From Reviews
            Where id= :id",['id'=>$id]
        );
        $data = $query->fetch();
        if(!$data){
            return null;
        }
        return new Review(
            $data['id'],
            $data['item'],
            $data['author'],
            $data['rating'],
            $data['comment'],
            $data['created_at']
        );
    }

    public static function hasAlreadyReviewed(int $item_id, int $user_id): bool{
        $query = self::execute(
            "SELECT COUNT(*) from Reviews
            where item= :item_id
            AND author= :author",['item_id'=>$item_id,'author'=>$user_id]
        );

        return (int)$query->fetchColumn() > 0;
    }

    //Validations
    public function validate(string $context = "default"): array {
        $errors = [];

        switch($context) {
            case "add_review":
                $errors['rating'] = $this->validate_rating($this->rating);
                $errors['comment'] = $this->validate_comment($this->comment);
            break;
            case "review_destroy":
                $errors['category_destroy'] = $this->validate_destroy($this->id ?? null);
            break;
        }
        return array_filter($errors);
    }

    private function validate_destroy(int $revID): ?array{
        $errors = $this->validate_id_exists($revID);
        if (!empty($errors)) {
            return $errors;
        }
              
        return $errors;
    }

    private function validate_rating(int $rating): ?array{
        $errors = [];
        $rules = self::getRules();
    
        if($rating > $rules['max_rating'] || $rating < $rules['min_rating'])
            $errors[] = "You can rate betwen {$rules['min_rating']} and {$rules['max_rating']}.";      
        return $errors;
    }

    private function validate_comment(?string $comment): ?array{
        $errors = [];
        $rules = self::getRules();
    
        if($comment != null && strlen($comment)> $rules['max_comment_length'])
            $errors[] = "You can write a comment of max {$rules['max_comment_length']} caracteres.";      
        return $errors;
    }

}    
?>