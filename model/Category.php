<?php
require_once "framework/Model.php";
class Category extends Model
{
    public function __construct(
        private int $id,
        private string $name,
        private int $priority
    ) {}
    private static array $rules;

    private static function getRules(): array {
        if (!isset(self::$rules)) {
            $config = file_exists("config/dev.ini") 
                ? parse_ini_file("config/dev.ini", true) 
                : parse_ini_file("config/prod.ini", true);
            
            self::$rules = $config["RulesCategory"];
        }
        return self::$rules;
    }

    public function persist(): Category{
        if($this->id !== 0 && self::getCategoryObjectById($this->id)){
            self::execute("UPDATE Categories SET name = :name , priority = :priority WHERE id =:id",["id" => $this->id,"name" => $this->name,"priority" => $this->priority]);
        }else{
            self::execute(
                "INSERT INTO Categories(name,priority)
                VALUES(?,?)",
                [$this->name,$this->priority]
            );
            $this->id = (int)self::lastInsertId();
        }
        
        return $this;
    }

    public function delete(): void{
        $query = self::execute("SELECT * from categories where priority > :priority",["priority" => $this->priority]);
        $datas = $query->fetchAll();
        self::execute(
            "DELETE FROM categories 
            WHERE id = :id",
            [
                "id" => $this->id     
            ]
        );   
        foreach ($datas as $data) {
            $cat = new Category(
                $data['id'],
                $data['name'],
                $data['priority']
            );
            $cat->setPriority($cat->getPriority()-1);
            $cat->persist();
        }     
    }

    public static function getAll() : array {
        $query = self::execute("SELECT * FROM categories ORDER BY priority",[]);
        $datas = $query->fetchAll();
        $cats = [];

        foreach ($datas as $data) {
            $cats[] = new Category(
                $data['id'],
                $data['name'],
                $data['priority']
            );
        }
        return $cats;
    }
        public static function getAllDesc() : array {
        $query = self::execute("SELECT * FROM categories ORDER BY priority DESC",[]);
        $datas = $query->fetchAll();
        $cats = [];

        foreach ($datas as $data) {
            $cats[] = new Category(
                $data['id'],
                $data['name'],
                $data['priority']
            );
        }
        return $cats;
    }
    

    public static function categoriesCount() : int{
        $query = self::execute("SELECT COUNT(*) FROM categories",[]);
        $data = (int) $query->fetchColumn();
        return $data;
    }

    public static function getMaxPriority() : int{
         $query = self::execute(
            "SELECT MAX(priority) FROM categories ",[]
        );
        $maxPriority = $query->fetchColumn();
        return $maxPriority;
    }

    public static function getMinPriority() : int{
         $query = self::execute(
            "SELECT MIN(priority) FROM categories ",[]
        );
        $minPriority = $query->fetchColumn();
        return $minPriority;
    }
    
    
    public static function exist(int $id): bool{
        $query = self::execute("SELECT 1 FROM categories where id = :id", ["id" => $id]);
        return $query->fetch() !== false;
    }

    public static function getCategoryObjectById(int $id) : ?Category {
        $query = self::execute(
            "SELECT * from categories
            where id= :id",['id'=>$id]
        );
        $data = $query->fetch();
        if (!$data) {
            return new Category(0, "", 0);
        }
        return new Category(
            $data['id'],
            $data['name'],
            $data['priority']            
        );
    }

    
    public static function getCategoryObjectByPriority(int $priority) : ?Category {
        $query = self::execute(
            "SELECT * from categories
            where priority= :priority",['priority'=>$priority]
        );
        $data = $query->fetch();

        return new Category(
            $data['id'],
            $data['name'],
            $data['priority']            
        );
    }
    public static function getCategoriesByItem(int $item_id): array{
        $query = self::execute(
            "SELECT categories.* from categories
            JOIN item_categories on categories.id = item_categories.category
            WHERE item_categories.item = :item ORDER BY name ASC",
            ["item" => $item_id]
        );

        $datas = $query->fetchAll();       

        $cats=[];

        foreach ($datas as $cat) {
            $cats[] = new Category(
                $cat['id'],
                $cat['name'],
                $cat['priority']
            );
        }
        return $cats;
    }

    public function categoryNameExist(string $categoryName): bool{
        $query = self::execute(
            "SELECT COUNT(*) FROM categories 
            WHERE name = :name",
            [            
            "name" => $categoryName
            ]
        );
        return (int)$query->fetchColumn() != 0;
    }

    public function getAllItemCategoryObjectInArray() : ?array {
        return ItemCategory::getItemsCategoriesByCategoryIdInArray($this->id);
    }
    
    public function swapCategoryPriority(string $direction): void {
        $priorityTMP= $this->priority;
        if($direction == "up"){
            $catBefore = $this->getCategoryObjectByPriority($priorityTMP-1);
            $catBefore->setPriority($priorityTMP);
            $this->setPriority(0);
            $this->persist();
            $catBefore->persist();
            $this->setPriority($priorityTMP-1);
            $this->persist();
        }

        if($direction == "down"){
            $catAfter = $this->getCategoryObjectByPriority($priorityTMP + 1);
            $catAfter->setPriority($priorityTMP);
            $this->setPriority(0);
            $this->persist();
            $catAfter->persist();
            $this->setPriority($priorityTMP + 1);
            $this->persist();
        }

    }

    public static function redefinedPriority(string $direction,int $catID, int $newPriority): void {

        $leSwaper = self::getCategoryObjectById($catID);
        $oldPriority =$leSwaper->getPriority();
        $leSwaper->setPriority(0);  
        $leSwaper->persist();
        
        if($direction=="down"){
            foreach (self::getAll() as $key => $value) {
                if($value->getPriority() > $oldPriority && $value->getPriority()<=$newPriority){
                    $value->setPriority($value->getPriority()-1);
                    $value->persist();
                }  
            }
        }
        
        if($direction=="up"){            
            foreach (self::getAllDesc() as $key => $value) {
                if($value->getPriority() >= $newPriority && $value->getPriority()<$oldPriority){
                    $value->setPriority($value->getPriority()+1);
                    $value->persist();
                }
            }
        }
      
        $leSwaper->setPriority($newPriority);
        $leSwaper->persist();
    }

    public function hasHigherPriority(): bool{
        return $this->priority == $this->getMinPriority();
    }

    public function hasLowerPriority(): bool{    
        return $this->priority==$this->getMaxPriority();
    }
    
    public function hasItemCategories(): bool{
        return !empty($this->getAllItemCategoryObjectInArray());
    }
     
    public function getId(): int{
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPriority(): int {
        return $this->priority;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setPriority(int $priority): void {
        $this->priority = $priority;
    }
    
    // Partie Validation
    public function validate(string $context = "default"): array {
            $errors = [];

            switch ($context) {
                case "add_edit_item":                  
                    $errors['categoriesIDS'] = $this::validate_categories_manage_on_item($_POST["categoriesIDS"] ?? [],$this->id ?? null);
                break;     
                case "manage_category_name":                  
                    $errors['category_name'] = $this->validate_categories_manage($_POST['category_name'] ?? "");             
                break;         
                case "manage_category_destroy":                  
                    $errors['category_destroy'] = $this->validate_destroy($this->id ?? null);             
                break;  
                case "manage_category_update":                  
                    $errors['category_update'] = $this->validate_update($this->id ?? null, $_POST['category_update_name'] ?? "");             
                break;                
                case "manage_category_swap_priority_up":                  
                    $errors['category_swap_priority_up'] = $this->validate_swap_priority_up($this->id ?? null, $_POST['category_up'] ?? "");             
                break;                                
            }
            return array_filter($errors);
        }

    private function validate_id_exists(?int $catID): array {
        if ($catID === null || !self::exist($catID)) {
            return ["It looks like an incorrect category has been selected."];
        }
        return [];
    }
    private function validate_swap_priority_up(?int $catID): array {
        $errors = [];
        if ($this->hasHigherPriority()) {
            return ["Your category already has the highest priority."];
        }
        return [];
    }
    private function validate_swap_priority_down(?int $catID): array {
        $errors = [];
        if ($this->hasLowerPriority()) {
            return ["Your category already has the lowest priority."];
        }
        return [];
    }
    private function validate_update(int $catID, string $categoryName): array{
        return array_merge(
            $this->validate_id_exists($catID),
            $this->validate_categories_manage($categoryName)
        );
    }
        
    private function validate_categories_manage_on_item(array $categoriesIDS, int $catID): ?array {
        $errors = $this->validate_id_exists($catID);
        $rules = self::getRules();
    
        if(count($categoriesIDS) > $rules['max_categories'])
            $errors[] = "You can select a maximum of {$rules['max_categories']} categories.";      
        return $errors;
    }

    private function validate_destroy(int $catID): ?array{
        $errors = $this->validate_id_exists($catID);
        if (!empty($errors)) {
            return $errors;
        }
        if(!empty($this->getAllItemCategoryObjectInArray())){
            $errors[] = "This category cannot be deleted because it is assigned to one or more items.";   
        }      
        return $errors;
    }

    private function validate_categories_manage(string $category): ?array{
        $errors = [];
        $rules = self::getRules();
        if (!strlen($category)) {
            $errors[] = "Category name is required.";
        }
        if($this->categoryNameExist($category)){
            $errors[] = "Category name already exist.";
        }
        if(strlen($category) < $rules['category_min_length'] ||  strlen($category) > $rules['category_max_length']  ){
            $errors[] = "Category name must be between 3 and 25 characters.";
        }
        return $errors;
    }

}