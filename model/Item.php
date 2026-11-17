<?php

    require_once "framework/Model.php";
    require_once "utils/AppTime.php";

    class Item extends Model{
        public function __construct(
            private int $id,
            private string $title,
            private ?string $description,
            private int $owner,
            private string $created_at,
            private ?float $buy_now_price,
            private int $duration_days,
            private float $starting_bid
        ){}

        private static array $rules;
        
        private static function getRules(): array {
            if (!isset(self::$rules)) {
                $config = file_exists("config/dev.ini") 
                    ? parse_ini_file("config/dev.ini", true) 
                    : parse_ini_file("config/prod.ini", true);
                
                self::$rules = $config["RulesItem"];
            }
            return self::$rules;
        }
        public function delete(): void{
            self::execute(
                "DELETE FROM items 
                WHERE id = :id",
                ["id"=>$this->id]            
            );
        } 
        public function persist(): array {
            // $errors = $this->validate();
            // if (!empty($errors)) return $errors;

            if ($this->id !== null && self::get_item($this->id)) {
                self::execute(
                   "UPDATE Items 
                    SET title = :title, 
                        description = :description, 
                        owner = :owner, 
                        created_at = :created_at, 
                        buy_now_price = :buy_now_price, 
                        duration_days = :duration_days, 
                        starting_bid = :starting_bid
                    WHERE id = :id",
                    [
                        "id"            => $this->id,
                        "title"         => $this->title,
                        "description"   => $this->description,
                        "owner"         => $this->owner,
                        "created_at"    => $this->created_at,
                        "buy_now_price" => $this->buy_now_price,
                        "duration_days" => $this->duration_days,
                        "starting_bid"  => $this->starting_bid
                    ]
                );
            } else {
               self::execute(
                    "INSERT INTO Items (title, description, owner, created_at, buy_now_price, duration_days, starting_bid)
                    VALUES (:title, :description, :owner, NOW(), :buy_now_price, :duration_days, :starting_bid)",
                    [
                        "title"         => $this->title,
                        "description"   => $this->description,
                        "owner"         => $this->owner,
                        "buy_now_price" => $this->buy_now_price,
                        "duration_days" => $this->duration_days,
                        "starting_bid"  => $this->starting_bid
                    ]
                );
                $this->id = (int)self::lastInsertId();
            }

            return [];
        }

        public function get_item_pictures() : array{
            $query = self::execute(
                "SELECT * FROM item_pictures WHERE item = :id ORDER BY priority ASC",
                ["id" => $this->id]
            );

            $datas = $query->fetchAll();            

            foreach ($datas as $picture) {
                $pictures[] = new ItemPicture(
                    (int)$picture['item'],
                    (int)$picture['priority'],
                    $picture['picture_path']
                );
            }

            return $pictures;
        }


        public static function get_item(int $id) : ?object{
            $query = self::execute(
                "SELECT * from items where id =:id",["id"=>$id]
            );
            $data = $query->fetch();
            if (!$data) {
               return null;
            }

            return new Item(
                   $data['id'],
                   $data['title'],
                   $data['description'],
                   $data['owner'],
                   $data['created_at'],
                   $data['buy_now_price'],
                   $data['duration_days'],
                   $data['starting_bid']
            );                        
        }

       //recup items du owner qui ne sont pas dans la categorie choisie
       public static function get_items_not_in_category(int $userId, int $catId) : array{
            $query = self::execute("SELECT items.* from items where owner =:user and items.id not in 
                (SELECT item from item_categories WHERE category =:cat) ORDER BY items.title;",[":user"=>$userId,":cat"=>$catId]);
            $datas = $query->fetchAll();
            $itemsList = [];
            foreach ($datas as $data) {
                $itemsList[]= new Item(
                    $data['id'],
                    $data['title'],
                    $data['description'],
                    $data['owner'],
                    $data['created_at'],
                    $data['buy_now_price'],
                    $data['duration_days'],
                    $data['starting_bid']
                );
            }
            return $itemsList;

       }

       //recup le nb de categories pour un item
       public static function get_nb_cat(int $itemId) : int{
            $query=self::execute("SELECT COUNT(*) from item_categories where item =:itemId",["itemId"=>$itemId]);
            return (int)$query->fetchColumn();
       }

        public static function get_all_available_items(?string $search=null,?string $cat = null): array|null{
            $currentTime = AppTime::get_current_datetime();
            $params = ["currentTime"=>$currentTime];

            $categoryCondition = "";
            if ($cat !== null && $cat !== "" && $cat !== "0") {
                $categoryCondition = " AND v_items_status.id IN (SELECT item FROM item_categories WHERE category = :category)";
                $params["category"] = $cat;
            }

            $searchCondition = "";
            if (!empty($search)) {
                $searchCondition = " AND (v_items_status.title LIKE '%$search%' OR users.pseudo LIKE '%$search%' OR v_items_status.description LIKE '%$search%')";
            }
            $query = self::execute(
                "SELECT DISTINCT v_items_status.*, users.pseudo as owner_pseudo,COUNT(item_pictures.priority) as pictures_count
                FROM v_items_status
                LEFT JOIN item_pictures on v_items_status.id = item_pictures.item
                JOIN users ON users.id = v_items_status.owner               
                WHERE v_items_status.end_at > :currentTime
                {$searchCondition} 
                {$categoryCondition} 
                GROUP BY v_items_status.id,users.pseudo
                ORDER BY v_items_status.end_at ASC",
                $params
            );
            $data = $query->fetchAll();
            return $data;
        }
        
        public static function get_items_im_participating(int $id,?string $search=null, ?string $cat=null): array|null{
            $currentTime = AppTime::get_current_datetime();
            $params = ["id"=>$id,"currentTime"=>$currentTime];

            $categoryCondition = "";
            if ($cat !== null && $cat !== "" && $cat !== "0") {
                $categoryCondition = " AND v_items_status.id IN (SELECT item FROM item_categories WHERE category = :category)";
                $params["category"] = $cat;
            }

            $searchCondition = "";
            if (!empty($search)) {
                $searchCondition = " AND (v_items_status.title LIKE '%$search%' OR users.pseudo LIKE '%$search%' OR v_items_status.description LIKE '%$search%')";
            }
    
            $query = self::execute(
                "SELECT 
                    v_items_status.*,
                    users.pseudo as owner_pseudo, COUNT(DISTINCT item_pictures.priority) as pictures_count, 
                CASE 
                    WHEN max(bids.amount) = v_items_status.max_bid THEN TRUE
                    ELSE FALSE
                END as is_highest_bidder
                FROM v_items_status
                JOIN users ON users.id = v_items_status.owner
                LEFT JOIN item_pictures on v_items_status.id = item_pictures.item
                JOIN bids on v_items_status.id = bids.item
                WHERE bids.owner = :id
                AND v_items_status.end_at > :currentTime
                AND v_items_status.is_auction = 1
                AND v_items_status.buy_now_reached = 0
                {$searchCondition} 
                {$categoryCondition} 
                GROUP BY v_items_status.id,users.pseudo
                ORDER BY v_items_status.end_at ASC", 
                $params
            );
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
                           
            return $data?: [];
        }

        public static function get_other_available_items(int $id,?string $search=null,?string $cat=null): array|null{
            $currentTime = AppTime::get_current_datetime();
            $params = ["id"=>$id,"currentTime"=>$currentTime];

            $categoryCondition = "";
            if ($cat !== null && $cat !== "" && $cat !== "0") {
                $categoryCondition = " AND v_items_status.id IN (SELECT item FROM item_categories WHERE category = :category)";
                $params["category"] = $cat;
            }

            $searchCondition = "";
            if($search !== "_empty_" && $search !=null){
                $searchCondition = " AND (v_items_status.title LIKE '%$search%' OR users.pseudo LIKE '%$search%' OR v_items_status.description LIKE '%$search%')";
            }

            $query = self::execute(
                "SELECT DISTINCT v_items_status.*, users.pseudo as owner_pseudo,COUNT(item_pictures.priority) as pictures_count
                FROM v_items_status 
                LEFT JOIN item_pictures on v_items_status.id = item_pictures.item
                JOIN users ON users.id = v_items_status.owner
                WHERE v_items_status.id not in (select item from bids where owner = :id)
                and v_items_status.owner <> :id
                and v_items_status.end_at > :currentTime                 
                and v_items_status.buy_now_reached = 0
                {$searchCondition} 
                {$categoryCondition} 
                GROUP BY v_items_status.id,users.pseudo
                ORDER BY v_items_status.end_at ASC",
                $params
            );

            $data = $query->fetchAll();

            return $data; 
        }

        public static function get_purchased_items(int $id){
            $currentTime = AppTime::get_current_datetime();
            $query = self::execute(
                "SELECT DISTINCT v_items_status.*, users.pseudo as owner_pseudo,COUNT(item_pictures.priority) as pictures_count
                FROM v_items_status 
                LEFT JOIN item_pictures on v_items_status.id = item_pictures.item
                JOIN users ON users.id = v_items_status.owner
                WHERE v_items_status.owner = :id
                and (v_items_status.is_direct_sale = 1 and
                    v_items_status.buy_now_reached =1 and
                    v_items_status.not_purchased_direct_sale = 0)
                or (v_items_status.is_auction =1 and
                    v_items_status.has_bids = 1 and
                    v_items_status.end_at < :currentTime) 
                GROUP BY v_items_status.id,users.pseudo
                ORDER BY v_items_status.end_at ASC",
                ["id"=>$id,"currentTime"=>$currentTime]
            );

            $data = $query->fetchAll();
            
            return $data;
        }
       
        public function getItemBids(): array
        {
            return Bid::getBidsByItem($this->id);
        }

        public function getCategories(): array {
            return Category::getCategoriesByItem($this->id);
        }    
 
        public function getPicture(int $priority): object|null{
            $query = self::execute(
                "SELECT * FROM item_pictures WHERE item = :itemId AND priority = :priority",
                ["itemId" => $this->id, 'priority' => $priority]
            );
            $data = $query->fetch();
            if(!$data)
                return null;
            return new ItemPicture(
                (int) $data['item'],
                (int) $data['priority'],
                $data['picture_path']
            );
        }

        public function getItemPictures(): array{
            $query = self::execute(
                "SELECT * FROM item_pictures WHERE item = :id ORDER BY priority ASC",
                ["id" => $this->id]
            );

            $datas = $query->fetchAll();       

            $pictures=[];

            foreach ($datas as $picture) {
                $pictures[] = new ItemPicture(
                    (int)$picture['item'],
                    (int)$picture['priority'],
                    $picture['picture_path']
                );
            }

            return $pictures;
        }
        //MY ITEMS
        public static function get_my_active_items(int $id,?string $search=null, ?string $cat=null): array|null{
            $currentTime = AppTime::get_current_datetime();
            $params = ["id"=>$id,"currentTime"=>$currentTime];

            $categoryCondition = "";
            if ($cat !== null && $cat !== "" && $cat !== "0") {
                $categoryCondition = " AND v_items_status.id IN (SELECT item FROM item_categories WHERE category = :category)";
                $params["category"] = $cat;
            }

            $searchCondition = "";
            if (!empty($search)) {
                $searchCondition = " AND (v_items_status.title LIKE '%$search%' OR users.pseudo LIKE '%$search%' OR v_items_status.description LIKE '%$search%')";
            }
            $query = self::execute(
                "SELECT v_items_status.*, users.pseudo as owner_pseudo,COUNT(item_pictures.priority) as pictures_count
                FROM v_items_status
                LEFT JOIN item_pictures on v_items_status.id = item_pictures.item 
                JOIN users ON users.id = v_items_status.owner
                where v_items_status.owner = :id
                and (v_items_status.end_at > :currentTime and v_items_status.buy_now_reached = 0)
                {$searchCondition}
                {$categoryCondition}
                GROUP BY v_items_status.id,users.pseudo
                ORDER BY v_items_status.end_at ASC", $params
            );
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            return $data? : [];
        }
        //MY ITEMS
        public static function get_my_closed_unsold_items(int $id,?string $search=null, ?string $cat=null): array|null{
            $currentTime = AppTime::get_current_datetime();
            $params = ["id"=>$id,"currentTime"=>$currentTime];

            $categoryCondition = "";
            if ($cat !== null && $cat !== "" && $cat !== "0") {
                $categoryCondition = " AND v_items_status.id IN (SELECT item FROM item_categories WHERE category = :category)";
                $params["category"] = $cat;
            }

            $searchCondition = "";
            if (!empty($search)) {
                $searchCondition = " AND (v_items_status.title LIKE '%$search%' OR users.pseudo LIKE '%$search%' OR v_items_status.description LIKE '%$search%')";
            }
            $query = self::execute(
                "SELECT v_items_status.*, users.pseudo as owner_pseudo ,COUNT(item_pictures.priority) as pictures_count 
                FROM v_items_status
                LEFT JOIN item_pictures on v_items_status.id = item_pictures.item 
                JOIN users ON users.id = v_items_status.owner
                where v_items_status.owner = :id 
                and v_items_status.end_at < :currentTime
                and ((is_direct_sale = 1 AND buy_now_reached = 0)
                {$searchCondition}
                {$categoryCondition}
                OR (is_auction = 1 AND has_bids = 0)) 
                GROUP BY v_items_status.id,users.pseudo
                ORDER BY v_items_status.end_at ASC", $params
            );
            
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            return $data? : [];
        }
        
        //MY ITEMS
        public static function get_my_sold_items(int $id,?string $search=null, ?string $cat=null): array|null{
            $currentTime = AppTime::get_current_datetime();
            $params = ["id"=>$id,"currentTime"=>$currentTime];

            $categoryCondition = "";
            if ($cat !== null && $cat !== "" && $cat !== "0") {
                $categoryCondition = " AND v_items_status.id IN (SELECT item FROM item_categories WHERE category = :category)";
                $params["category"] = $cat;
            }

            $searchCondition = "";
            if (!empty($search)) {
                $searchCondition = " AND (v_items_status.title LIKE '%$search%' OR users.pseudo LIKE '%$search%' OR v_items_status.description LIKE '%$search%')";
            }
            $query = self::execute(
                "SELECT v_items_status.*, users.pseudo as owner_pseudo,COUNT(item_pictures.priority) as pictures_count 
                FROM v_items_status 
                LEFT JOIN item_pictures on v_items_status.id = item_pictures.item
                JOIN users ON users.id = v_items_status.owner
                WHERE v_items_status.owner = :id
                AND (buy_now_reached = 1 or (:currentTime >= end_at and has_bids = 1))
                {$searchCondition}
                {$categoryCondition}
                GROUP BY v_items_status.id,users.pseudo
                ORDER BY v_items_status.end_at ASC",
                $params
            );
            $data = $query->fetchAll(PDO::FETCH_ASSOC);
            return $data? : [];
        }
        
        //Purchases - recuperer tous les items qui sont vendus et qui ne sont pas a toi
        public static function get_otherOwner_purchased_items(int $id) : array|null{
            $currentTime = AppTime::get_current_datetime();
            $query = self::execute(
                "SELECT v_items_status.*
                FROM v_items_status  
                WHERE v_items_status.has_bids = 1
                AND (
                    (v_items_status.is_auction = 1 AND v_items_status.end_at < :currentTime)
                    OR (v_items_status.buy_now_reached = 1)
                )
                AND (
                    SELECT owner 
                    FROM bids 
                    WHERE bids.item = v_items_status.id 
                    ORDER BY bids.amount DESC 
                    LIMIT 1
                ) = :id
                ORDER BY v_items_status.end_at ASC",
                [":id" => $id, ":currentTime" => $currentTime]
            );
            $items = [];
            $datas = $query->fetchAll();
            foreach($datas as $data){
                $items[] = new Item(
                    $data['id'],
                    $data['title'],
                    $data['description'],
                    $data['owner'],
                    $data['created_at'],
                    $data['buy_now_price'],
                    $data['duration_days'],
                    $data['starting_bid']
                );
            }
            return $items;
        }

        //Sales - recuperer les Item que User a vendu
         public static function get_owner_sold_items(int $id) : array{
            $currentTime = AppTime::get_current_datetime();
            $query = self::execute(
                "SELECT v_items_status.*
                FROM v_items_status  
                WHERE v_items_status.owner = :id
                AND (buy_now_reached = 1 or (:currentTime >= end_at and has_bids = 1))
                ORDER BY v_items_status.end_at ASC",
                [":id" => $id, ":currentTime" => $currentTime]
            );
            $datas = $query->fetchAll();
            $items = [];
            foreach($datas as $data){
                $items[] = new Item(
                    $data['id'],
                    $data['title'],
                    $data['description'],
                    $data['owner'],
                    $data['created_at'],
                    $data['buy_now_price'],
                    $data['duration_days'],
                    $data['starting_bid']
                );
            }
            return $items;
        }

        public function get_priority_picture(): string{
             $query = self::execute(
                "SELECT picture_path from item_pictures where item =:id and priority = 1",["id"=>$this->id]
            );
            $data = $query->fetchColumn();
            return $data;
        }

        //verification title unique 
        public static function is_title_unique_for_owner(string $title, int $ownerId, int $excludeItemId = 0): bool{
            $query = self::execute(
                "SELECT COUNT(*) FROM items 
                WHERE owner = :owner AND title = :title AND id != :id",
                [
                "owner" => $ownerId,
                "title" => $title,
                "id" => $excludeItemId
                ]
            );
            return (int)$query->fetchColumn() === 0;
        }

        public function getVueItemStatus(): VueItemStatus{
           return VueItemStatus::get_item_status($this->id);
        }

         public function getId(): int
        {
            return $this->id;
        }

        public function getTitle(): string
        {
            return $this->title;
        }

        public function getDescription(): ?string
        {
            return $this->description;
        }

        public function getOwner(): int
        {
            return $this->owner;
        }
        public function getOwnerPseudo(): string{
            return User::get_member_by_id($this->owner)->getPseudo();
        }
        public function getCreatedAt(): string
        {
            return $this->created_at;
        }

        public function getBuyNowPrice(): ?float
        {
            return $this->buy_now_price;
        }

        public function getDurationDays(): int
        {
            return $this->duration_days;
        }

        public function getStartingBid(): float
        {
            return $this->starting_bid;
        }

        // Setters
        public function setId(int $id): void
        {
            $this->id = $id;
        }

        public function setTitle(string $title): void
        {
            $this->title = $title;
        }

        public function setDescription(?string $description): void
        {
            $this->description = $description;
        }

        public function setOwner(int $owner): void
        {
            $this->owner = $owner;
        }

        public function setCreatedAt(string $created_at): void
        {
            $this->created_at = $created_at;
        }

        public function setBuyNowPrice(?float $buy_now_price): void
        {
            $this->buy_now_price = $buy_now_price;
        }

        public function setDurationDays(int $duration_days): void
        {
            $this->duration_days = $duration_days;
        }

        public function setStartingBid(float $starting_bid): void
        {
            $this->starting_bid = $starting_bid;
        }
        public function getMinimumBidAmount(): float
        {
            $highestBid = Bid::getBiggerBidByItem($this->id);

            if ($highestBid) {
                return $highestBid->getAmount() + 1;
            }
        
            return $this->starting_bid + 1;
        }


        // Partie Validation
        public function validate(string $context = "default"): array {
                $errors = [];
                switch ($context) {
                    case "add_edit_item":
                        $errors['title'] = $this->validate_title($this->title);
                        $errors['description'] = $this->validate_description($this->description ?? "");
                        $errors['sale_duration'] = $this->validate_saleDuration($this->duration_days);
                        $errors['starting_bid'] = $this->validate_startingBid($this->starting_bid ?? "");
                        $priceError = $this->validate_buyNowPrice($this->buy_now_price, $this->starting_bid);
                        $errors['buy_now_price'] = $priceError;
                        $errors['direct_sale'] = $priceError;
                    break;                   
                }
                // cela supprimes les clés qui n'ont pas dérreur dans le tableau $errors.
                return array_filter($errors);
            }


        private function validate_title(string $title): ?array {
            $errors = [];
            $rules = self::getRules();
            if (!strlen($title)) {
                $errors[] = "Title is required.";
            }

            if (strlen($title) < $rules['title_min_lenght']) {
                $errors[] = "Title must be at least " . $rules['title_min_lenght'] . " characters long.";
            }

            $query = self::execute("SELECT title from items where owner = :owner and title = :title and id != :id", ["owner" => $this->owner, "title" => $title, "id" => $this->id]);
            $data = $query->fetchAll(); 
            if (count($data) > $rules['title_unique_per_owner']) {
                $errors[] = "Vous avez déjà un item portant ce nom.";
            }                
            return $errors;
        }

        private function validate_description(string $description): ?array{
            $errors = [];
            $rules = self::getRules();
            if (strlen($description) > 0) {
                if (strlen($description) < $rules['description_min_lenght']) {
                    $errors[] = "La description doit être de minimum " . $rules['title_min_lenght'] . " caractères.";
                }
            }
            return $errors;
        }

        private function validate_saleDuration(int $duration): ?array{
            $errors = [];
            $rules = self::getRules();
            if($duration < $rules['duration_days_min'] || $duration > $rules['duration_days_max']){
                 $errors[] = "La duration days doit être compris entre {$rules['duration_days_min']} et {$rules['duration_days_max']} ";
            }
            return $errors;
        }

        private function validate_startingBid($starting_bid): ? array{
            $errors = [];
            $rules = self::getRules();
            if (!empty($starting_bid)) {
                if (!preg_match($rules['starting_bid_now_price_format'], $starting_bid)) {
                    $errors[] = "Le starting bid doit être un nombre valide, exemple: 40, sans lettres ou caractères spéciaux";
                }
                if($starting_bid <= $rules['starting_bid_min']){
                    $errors[] = "Le prix de départ doit être supérieur à 0";
                }
            }
            return $errors;
        }

        private function validate_buyNowPrice($buy_now_price,$starting_bid): ? array{
            $errors = [];
            $rules = self::getRules();

            if (empty($starting_bid) && empty($buy_now_price)) {
                $errors[] = "Veuillez remplir une enchère ou un achat immediat.";
            }
            if ($buy_now_price != "" &&  $buy_now_price !== null) {
                if (!preg_match($rules['starting_bid_now_price_format'], $buy_now_price)) {
                    $errors[] = "Le buy now price doit être un nombre valide, exemple: 40, sans lettres ou caractères spéciaux, 8 chiffres avant la virgule maximum, et deux après maximum.";
                }
                if($buy_now_price <= $rules['buy_now_price_min']){
                    $errors[] = "Le prix d'achat doit être supérieur à 0";
                }
                if($buy_now_price <= $starting_bid){
                    $errors[] = "Le prix d'achat doit être supérieur au prix d'enchère";
                }
            }
            return $errors;

        }
        
        public static function validate_starting_bid_respected(int $itemIdBid, $amount): array {
            $errors = [];
            $query = self::execute(
                "SELECT * FROM Items WHERE id = :itemId AND starting_bid >= :amount",
                [
                "itemId" => $itemIdBid,
                "amount" => $amount,
                ]
            );
            if($query->rowCount() > 0) {
                $errors[]= "Le montant proposé doit etre supérieure a starting bid.";
                return $errors;
            };
            return $errors;
        }
    }  