<?php

require_once "framework/Controller.php";
require_once "utils/Functions.php";
require_once "utils/Auth.php";
require_once "model/User.php";
require_once "model/Category.php";
require_once "model/ItemCategory.php";
require_once "model/Item.php";

class ControllerExam extends Controller{

    protected function require_admin(): object{
        if(!Auth::user_logged()){
            $this->redirect("user","login");
        }
        if(Auth::user_logged() && Auth::get_user_or_false()->getRole() != "admin"){
            $this->redirect("item","index");
        }
        return $_SESSION['user'];
    }

    public function index(array $errors = [], array $old = []): void{
        $this->require_admin();
        $usersList = User::getAllUsers();
        $categories = null; //values null inities avec et en function de GET
        
        (new View("exam"))->show([
            'usersList' => $usersList,
            
            'errors'=>$errors,
            'old'=>$old]);
    }

    public function getUsers() : void{
        header('Content-Type: application/json; charset=utf-8');
        $usersList = User::getAllUsers();
        $data = [];
        foreach($usersList as $user){
            $data[] =[
                'id' => $user->getId(),
                'pseudo' => $user->getPseudo(),
                'fullName'=> $user->getFullName() 
            ];
        }
        echo json_encode(['usersList' => $data], JSON_UNESCAPED_UNICODE);
    }

    public function getCategories() : void{
        header('Content-Type: application/json; charset=utf-8');
        $user = 0;
        $cats = [];
        if(isset($_GET['param1'])){
            $user = $_GET['param1'];
        }
        if($user != 0){
            $categories = Category::getAll();
            foreach ($categories as $key) {
                $cats[] =[
                    'id' => $key->getId(),
                    'name' => $key->getName()
                ];
            }
            echo json_encode(["cats" => $cats], JSON_UNESCAPED_UNICODE);
        }
    }
     public function goGetItems() : void{
        $userId = 0;
        $categoryId = 0;
        
        if(isset($_POST['catId']) && isset($_POST['userId'])){
            $categoryId = $_POST['catId'];
            $userId = $_POST['userId'];
        }
        if($categoryId !=0){
            $this->redirect("exam","index",$userId,$categoryId);
        }else{
            $this->redirect("exam","index",$userId);
        }

    }

    public function getItemsNotInCategory() : void{
        header('Content-Type: application/json; charset=utf-8');
        $userId = 0;
        $catId = 0;
        if(isset($_GET['param1']) && $_GET['param1'] != '' && isset($_GET['param2']) && $_GET['param2'] != ''){
            $userId = $_GET['param1'];
            $catId = $_GET['param2'];
            $itemList =Item::get_items_not_in_category($userId,$catId);
            $data =[];
            foreach ($itemList as $item) {
                $data[]=[
                    'id' => $item->getId(),
                    'title' =>$item->getTitle(),
                    'nbCat' => Item::get_nb_cat($item->getId())
                ];
            }
            echo json_encode(["items" => $data],JSON_UNESCAPED_UNICODE );
        }
    }

    public function registerItemtoCat(): void{
        header('Content-Type: application/json; charset=utf-8');
        $itemsList = [];
        $catId = 0;
        if(isset($_POST['itemList']) && isset($_POST['catId'])){
            $itemsList = $_POST['itemList'];
            $catId = $_POST['catId'];
            foreach ($itemsList as $key) {
                $newItemCat = new ItemCategory($key,$catId);
                $newItemCat->persist();
            }
            echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
        }
    }

}   
?>    