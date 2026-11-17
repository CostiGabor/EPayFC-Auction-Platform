<?php

require_once "framework/Controller.php";
require_once "utils/Functions.php";
require_once "utils/Auth.php";
require_once "model/User.php";
require_once "model/Category.php";
require_once "model/ItemCategory.php";

class ControllerCategory extends Controller{

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
        $admin= $this->require_admin();
        $categories = Category::getAll();
        (new View("manage_categories"))->show(['categories'=>$categories,'errors'=>$errors,'old'=>$old]);
    }

    public function store(): void{
        $this->require_admin();
        $category = null;
        $errors = [];
        $old = [
            "category_name" => htmlspecialchars($_POST['category_name'] ?? ""),
        ];

        if(isset($_POST['category_name'])) {     
            $category = new Category( 
                0,
                $_POST['category_name'],
                Category::getMaxPriority()+1               
            );
        }
        $errors = $category->validate("manage_category_name");
        if(empty($errors)){
            $category->persist();            
            $this->redirect("category", "index");            
        }
        $this->index($errors,$old);
    }

    public function update(): void{
        $this->require_admin();   
        $errors = [];
        $old = [];
        $cat=null;
        if(!empty($_POST['category_update_id']) && Category::getCategoryObjectById($_POST['category_update_id'])->getId() != 0 && $_POST['category_update_name'] != Category::getCategoryObjectById($_POST['category_update_id'])->getName()){
            $cat = Category::getCategoryObjectById($_POST['category_update_id']);
            $cat->setName($_POST['category_update_name'] ?? "");
            $errors[$_POST['category_update_id']] = $cat->validate("manage_category_update");
      
            $old[$cat->getId()] = [
                "category_name" => $_POST['category_update_name'] ?? $cat->getName(),
            ];
            if(empty($errors[$_POST['category_update_id']]) && isset($cat)){
                $cat->persist();
                $this->redirect("category","index");
            }
        }     
        $this->index($errors,$old);
    }

    public function destroy(): void {
        $this->require_admin();      
        $errors = [];
        $old = [];

        if(!empty($_POST['category_destroy'])){    
            $cat = Category::getCategoryObjectById($_POST['category_destroy']);
            $errors = $cat->validate("manage_category_destroy");
        }       
    
        if(empty($errors) && isset($cat) && empty($cat->getAllItemCategoryObjectInArray())){
            $cat->delete();
            $this->redirect("category","index");
        }

        $this->index($errors,$old);
    }

    public function upCategory(): void{
        $this->require_admin();   
        $errors = [];
        $old = [];
        if(!empty($_POST['category_up'])){
            $cat = Category::getCategoryObjectById($_POST['category_up']);
            $errors = $cat->validate('manage_category_swap_priority_up');
        }

        if(empty($errors) && isset($cat)){
            $cat->swapCategoryPriority('up');
            $this->redirect("category","index");
        }
        $this->index($errors,$old);
    }

    public function downCategory(): void{
        $this->require_admin();   
        $errors = [];
        $old = [];
        if(!empty($_POST['category_down'])){
            $cat = Category::getCategoryObjectById($_POST['category_down']);
            // $errors = $cat->validate("manage_category_down");
        }

        if(empty($errors) && isset($cat)){
            $cat->swapCategoryPriority('down');
            $this->redirect("category","index");
        }

        $this->index($errors,$old);
    }

    public function getCategories(): void{
        header('Content-Type: application/json; charset=utf-8');
        $categoriesObjects = Category::getAll();
    
        $categories = array_map(fn($cat) => [
                'id'       => $cat->getId(),
                'name'     => $cat->getName(),
                'priority' => $cat->getPriority(),
                'itemCount' => count(ItemCategory::getItemsCategoriesByCategoryIdInArray($cat->getId()))
            ], $categoriesObjects);
        echo json_encode($categories, JSON_UNESCAPED_UNICODE);
    }

    public function swapCategories(): void{
        header('Content-Type: application/json; charset=utf-8');
        $direction = $_POST["direction"];
        $catID = $_POST["catID"];
        $newPriority = $_POST["newPriority"];
                    
        Category::redefinedPriority($direction, $catID,$newPriority);

        echo json_encode($direction, JSON_UNESCAPED_UNICODE);
    }

    public function editCategory(): void{
        header('Content-Type: application/json; charset=utf-8');
        $catID = $_POST["catID"];
        $catName = $_POST['category_update_name'];
        $category = Category::getCategoryObjectById($catID);
        $category->setName($catName);
        $errors = $category->validate("manage_category_update");
        if(empty($errors)){
            $category->persist();
            echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
        }else{          
            echo json_encode(["success" => false, "errors" => $errors], JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteCategory(): void {
    header('Content-Type: application/json; charset=utf-8');
    
    $catID = (int)$_POST["catID"]; // ← cast en int
    error_log("catID reçu : " . $catID); // ← log dans php error log
    
    $category = Category::getCategoryObjectById($catID);
    error_log("category id : " . $category->getId()); // ← log
    
    if ($category && $category->getId() != 0) {
        if (!$category->hasItemCategories()) {
            $category->delete();
            echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["success" => false, "error" => "Catégorie non vide"], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Catégorie introuvable"], JSON_UNESCAPED_UNICODE);
    }
}

    public function addCategory(): void {
        header('Content-Type: application/json; charset=utf-8');
        $catName = $_POST["category_name"];
        $cat = new Category( 
                0,
                $catName,
                Category::getMaxPriority()+1
        );
        $errors = $cat->validate("manage_category_name");
        if(empty($errors)){
            $cat->persist();
            echo json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
        }else{          
            echo json_encode(["success" => false, "errors" => $errors], JSON_UNESCAPED_UNICODE);
        }
    }

}
?>