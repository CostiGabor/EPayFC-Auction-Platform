<?php
require_once "framework/Model.php";
class ItemPicture extends Model
{
    public function __construct(
        private int $item,
        private int $priority,
        private string $picture_path
    ) {}


    public function delete(): void {
        $oldFileName = pathinfo($this->picture_path, PATHINFO_FILENAME);
        $pictureDirectory = "uploads/items/" . $this->item . "/";
        if (file_exists($pictureDirectory . $oldFileName . ".jpg")) {
            unlink($pictureDirectory . $oldFileName . ".jpg");
            unlink($pictureDirectory . $oldFileName . "_thumbnail.jpg");
        }

        self::execute(
            "DELETE FROM item_pictures 
            WHERE item = :item AND priority = :priority",
            [
                'item' => $this->item,
                'priority' => $this->priority
            ]
        );      
        // On supprime le dossier seulement s'il ne reste plus d'images en DB
        $remaining = self::execute(
            "SELECT 1 FROM item_pictures WHERE item = :item",
            ['item' => $this->item]
        )->fetch();

        if (!$remaining && is_dir($pictureDirectory)) {
            rmdir($pictureDirectory);
        }  
    }

    public function persist(): void {   
        $existing = self::execute(
            "SELECT 1 FROM item_pictures 
            WHERE item = :item AND picture_path = :picture_path",
            [
                'item' => $this->item,
                'picture_path' => $this->picture_path
            ]
        )->fetch();

        if ($existing) {
            self::execute(
                "UPDATE item_pictures
                SET priority = :priority
                WHERE item = :item AND picture_path = :picture_path",
                [
                    'item' => $this->item,
                    'priority' => $this->priority,
                    'picture_path' => $this->picture_path
                ]
            );
        } else {
            self::execute(
                "INSERT INTO item_pictures (item, priority, picture_path)
                VALUES (:item, :priority, :picture_path)",
                [
                    'item' => $this->item,
                    'priority' => $this->priority,
                    'picture_path' => $this->picture_path
                ]
            );
        }
    }

    public function removeAndUpdatePriorities() {
        $tmpPriority = $this->getPriority();
        // On supprime dabord l'image puis on récupère ce qu'il reste dans la db et on réappropri les priority
        $this->delete();
        
        foreach ($this->getPicturesSiblings() as $picture) {
            if ($picture->getPriority() > $tmpPriority) {
                $picture->setPriority($picture->getPriority() - 1);
                $picture->persist();
            }
        }
    }

    public  function getPicturesSiblings(): array{
        $query = self::execute(
            "SELECT * FROM item_pictures WHERE item = :itemId ORDER BY priority ASC",
            ["itemId" => $this->item]
        );

        $pictures = [];
        while ($row = $query->fetch()) {
            $pictures[] = new self(
                $row["item"],
                $row["priority"],
                $row["picture_path"]
            );
        }

        return $pictures;
    }

    // Partie Validation
    public function validate(string $context = "default", array $file = null): array {
        $errors = [];
        switch ($context) {
            case 'addPriority':
                $errors = $this->validate_priority($this->priority ?? '');
                break;
            case 'removePriority':
                $errors = $this->validate_priority($this->priority ?? '');
                break;
            case 'addImage':
                $errors = Image::validate_picture($file);
                break;
            default:
                # code...
                break;
        }
        // cela supprimes les clés qui n'ont pas dérreur dans le tableau $errors.
        return array_filter($errors);
    }

    public function validate_priority(int $priority): array{       
        $errors = [];
        if($priority < 1){            
            $errors[] = "L'image a déjà la priorité la plus haute.";
            return $errors;
        }
        $query = self::execute(
            "SELECT * FROM item_pictures WHERE item = :itemId AND priority = :priority",
            [
            "itemId" => $this->item,
            "priority" => $priority,
            ]
        );
        if($query->rowCount() == 0) {                
            $errors[]= "L'image a déjà la priorité la plus faible";
            return $errors;
        };
        return $errors;
    }
    

    public function getItem(): int
    {
        return $this->item;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getPicturePath($thumbnail = false) : string|null {  
        if ($thumbnail) {
            $ext = pathinfo($this->picture_path, PATHINFO_EXTENSION);
            $dirname = pathinfo($this->picture_path, PATHINFO_DIRNAME);
            $filename = pathinfo($this->picture_path, PATHINFO_FILENAME);

            return "{$dirname}/{$filename}_thumbnail.{$ext}";
        }
        return $this->picture_path;
    }


    public static function getAPictureByItemAndPriority(int $itemId,int $priority): object|null{
        $query = self::execute(
            "SELECT * FROM item_pictures 
            WHERE item = :item AND priority = :priority",
            ['item' => $itemId, 'priority' => $priority]
        );

         $data = $query->fetch();

        if (!$data) return null;
        return new ItemPicture(
            (int)$data['item'],
            (int)$data['priority'],
            $data['picture_path']
        );
    }
    
    public function setPriority(int $priority): void{
           $this->priority = $priority;
    }

    public function setPicturePath(?string $path): void{
            $this->picture_path = $path;
    }
}