<?php
require_once "framework/Model.php";
class ItemCategory extends Model
{
    public function __construct(
        private int $item,
        private int $category
    ) {}

    public function persist(): ItemCategory{
            self::execute(
                "INSERT INTO item_categories(item,category)
            VALUES(?,?)",
            [$this->item,$this->category]
            );
        return $this;
    }
   

    public function delete(): void{
        self::execute(
            "DELETE FROM item_categories 
            WHERE item = :item and category = :category
            ",
            [
                "item" => $this->item,
                "category" => $this->category
                
            ]
        );        
    }
    public function getItemCategoryObjectByItemAndCategory(int $item, int $category): ItemCategory{
        $query = self::execute(
            "SELECT * from item_categories
            where item= :item AND category = :category",["item" => $this->item,"category" => $this->category]
        );
        $data = $query->fetch();

        return new ItemCategory(
            $data['item'],
            $data['category']            
        );
    }

    public static function getItemsCategoriesByCategoryIdInArray(int $category): ?array{
        $query = self::execute(
            "SELECT * from item_categories
            where category= :id",['id'=>$category]
        );
        $datas = $query->fetchAll();
        $catsItem=[];
        foreach ($datas as $key => $value) {
            $catsItem[] = new ItemCategory(
            $value['item'],
            $value['category']            
            );
        }
        return $catsItem;
    }


    public function getCategoriesObjectInArray() : array {
        $query = self::execute(
            "SELECT * from categories
            where id= :item",['item'=>$this->item]
        );
        $datas = $query->fetchAll();

        $categories = [];

        foreach ($datas as $cat) {
            $categories[] = new Category(
                $cat['id'],
                $cat['name'],
                $cat['priority']                
            );
        }
        return $categories;
    }
    
    public function getItem(): string {
        return $this->item;
    }

    public function getCategory(): int {
        return $this->category;
    }
}