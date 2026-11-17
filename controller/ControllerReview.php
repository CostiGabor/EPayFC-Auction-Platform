<?php
require_once "framework/Controller.php";
require_once "framework/View.php";
require_once "utils/Functions.php";
require_once "utils/Auth.php";
require_once "model/User.php";
require_once "model/Review.php";
require_once "model/Item.php";

class ControllerReview extends Controller{
    protected function require_login(): object{
        if(!Auth::user_logged()){
            $this->redirect("user","login");
        }
        return $_SESSION['user'];
    }

    public function index(array $errors = [], array $old = []): void{
        $this->require_login();
        $item_id = $_GET['param1'] ?? null;
        $reviews = Review::getReviewsByItem($item_id);
        $item = Item::get_item((int)$item_id);

        (new View("show_item"))->show([
            'reviews' => $reviews,
            'item' => $item,
            'errors' => $errors,
            'old' => $old
        ]);
    }

    public function store(): void{
        $this->require_login();
        $errors = [];
        $user = Auth::get_user_or_false();
        $old = [
            "rating" => $_POST['rating'] ?? "",
            "comment" => $_POST['comment']?? "",
        ];
        //verifier si user a deja fait un review pour cet item
        
        if(Review::hasAlreadyReviewed( (int)($_POST['item_id']), $user->getId())) {     
            $errors['already_reviewed'] = [" You have Already reviewed this item"];
            $this->index($errors,$old);
        }else{
            //creation de l'objet Review
            $review= new Review(
                0,
                (int)$_POST['item_id'],
                $user->getId(),
                (int)($_POST['rating'] ?? 0),
                $_POST['comment'] ?? null,
                date('Y-m-d H:i:s')
            );
            // validation
            $errors = $review->validate("add_review");
            //si pas d'erreurs, persist et redirect
            if(empty($errors)){
                $review->persist();            
                $this->redirect("item", "show_item/".(int)$_POST['item_id']);            
            }
            //si il y a des erreurs, reafficher la page
            $this->index($errors,$old);
        }
    }

    public function destroy(): void {
        $this->require_login();
        $user = Auth::get_user_or_false();      
        $errors = [];
        $old = [];


        if(!empty($_POST['review_destroy'])){    
            $rev = Review::getReviewById((int)$_POST['review_destroy']);
            if($rev === null) {
                $this->redirect("item", "index");
            }
            //verification admin/author
            if($user->getId() != $rev->getAuthor() && !Auth::isAdmin()){
                $this->redirect('item',"index");
            }
    
            $errors = $rev->validate("review_destroy");
        }       
        
        if(empty($errors) && isset($rev)){
            $rev->delete();
            $this->redirect("item","show_item/".$rev->getItem());
        }

        $this->index($errors,$old);
    }

    public function getReviews(): void{
        header('Content-Type: application/json; charset=utf-8');
        $item_id = $_GET['param1'] ?? null;
        $reviewsObjects = Review::getReviewsByItem($item_id);
    
        $reviews = array_map(fn($rev) => [
                'id'       => $rev->getId(),
                'item_id'   => $rev->getItem(),
                'author' => $rev->getAuthor(),
                'rating' => $rev->getRating(),
                'comment' => $rev->getComment(),
                'created_at'=> $rev->getCreationDate()
            ], $reviewsObjects);
        echo json_encode($reviews, JSON_UNESCAPED_UNICODE);
    }

    public function destroyAjax() : void{
        header('Content-Type: application/json; charset=utf-8');
        $this->require_login();
        $user = Auth::get_user_or_false();      
        $errors = [];


        if(!empty($_POST['review_destroy'])){    
            $rev = Review::getReviewById((int)$_POST['review_destroy']);
            if($rev === null) {
                $errors['already_reviewed'] = [" No such review for this item"];
                echo json_encode(["success" => false, "errors" => $errors], JSON_UNESCAPED_UNICODE);
                return;
            }
            //verification admin/author
            if($user->getId() != $rev->getAuthor() && !Auth::isAdmin()){
                $errors['already_reviewed'] = [" Not the owner or admin of this review "];
                echo json_encode(["success" => false, "errors" => $errors], JSON_UNESCAPED_UNICODE);
                return;
            }
    
            $errors = $rev->validate("review_destroy");
        }       
        
        if(empty($errors) && isset($rev)){
            $rev->delete();
            echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE); 
                return; 
        }

        //si il y a des erreurs, reafficher la page
            echo json_encode(["success" => false, "errors" => $errors], JSON_UNESCAPED_UNICODE);
    }        

    public function storeAjax(): void{
        header('Content-Type: application/json; charset=utf-8');
        $this->require_login();
        $errors = [];
        $user = Auth::get_user_or_false();
        $old = [
            "rating" => $_POST['rating'] ?? "",
            "comment" => $_POST['comment']?? "",
        ];
        //verifier si user a deja fait un review pour cet item
        
        if(Review::hasAlreadyReviewed( (int)($_POST['item_id']), $user->getId())) {     
            $errors['already_reviewed'] = [" You have Already reviewed this item"];
            echo json_encode(["success" => false, "errors" => $errors], JSON_UNESCAPED_UNICODE);
        }else{
            //creation de l'objet Review
            $review= new Review(
                0,
                (int)$_POST['item_id'],
                $user->getId(),
                (int)($_POST['rating'] ?? 0),
                $_POST['comment'] ?? null,
                date('Y-m-d H:i:s')
            );
            // validation
            $errors = $review->validate("add_review");
            //si pas d'erreurs, persist et redirect
            if(empty($errors)){
                $review->persist();            
                echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE); 
                return;          
            }
            //si il y a des erreurs, reafficher la page
            echo json_encode(["success" => false, "errors" => $errors], JSON_UNESCAPED_UNICODE);
        }
    }

    
}   
?>