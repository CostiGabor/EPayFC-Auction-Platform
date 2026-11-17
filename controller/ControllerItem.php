<?php

require_once "framework/Controller.php";
require_once 'utils/Functions.php';
require_once 'utils/Auth.php';
require_once 'utils/Image.php';
require_once 'model/Item.php';
require_once 'model/User.php';
require_once 'model/Bid.php';
require_once 'model/ItemPicture.php';
require_once 'model/VueItemStatus.php';
require_once 'lib/SimpleImage.php';
require_once 'model/Category.php';
require_once 'model/ItemCategory.php';
require_once 'model/Review.php';

use claviska\SimpleImage;

class ControllerItem extends Controller
{
    const UPLOAD_ERR_OK = 0;

    protected function require_login(): object  {
        if (!Auth::user_logged()) {
            $this->redirect("user", "login");
        }
        return $_SESSION['user'];
    }

    protected function notRightUser(int $id): void
    {
        if (!Auth::isOwnedByCurrentUser($id)) {
            $this->redirect("item", "index");
        }
    }
    public function index(): void
    {
        if (self::get_user_or_false()) {

            $userId = self::get_user_or_false()->getId();

            $items = Item::get_items_im_participating($userId);
            foreach ($items as &$item) {
                $the_item = Item::get_item($item['id']);
                $item['picture_path'] = $the_item ? $the_item->get_priority_picture() : null;
            }

            $available_items = Item::get_other_available_items($userId);
            foreach ($available_items as &$item) {
                $av_item = Item::get_item($item['id']);
                $item['picture_path'] = $av_item->get_priority_picture();
            }      
            
            $search = "";
            $catSelected = "";
            if(isset($_GET['param1'])) {
                $param_nav_search = $_GET['param1'];
                $catSelected = Tools::url_safe_decode($param_nav_search)[1] ?? "";
                $search = Tools::url_safe_decode($param_nav_search)[2] ?? "";
            }
            (new View("browse_items"))->show([
                "items" => $items,
                "available_items" => $available_items,
                "search" => $search,
                "catSelected" => $catSelected
            ]);
            
        } else {

            $available_items = Item::get_all_available_items();
            foreach ($available_items as &$item) {
                $av_item = Item::get_item($item['id']);
                $item['picture_path'] = $av_item->get_priority_picture();
            }
            $search = "";
            $catSelected =0;
            if(isset($_GET['param1'])) {
                $param_nav_search = $_GET['param1'];
                $catSelected = Tools::url_safe_decode($param_nav_search)[1] ?? 0;
                $search = Tools::url_safe_decode($param_nav_search)[2] ?? "";
            }
            (new View("browse_items"))->show([
                "available_items" => $available_items,
                "search" => $search,
                "catSelected" => $catSelected
            ]);
           
        }
    }

    public function my_items(): void
    {
        //My active Items.
        $my_active_items = Item::get_my_active_items(self::get_user_or_redirect()->getId());
        
        foreach ($my_active_items as &$item) {
            $active_item = Item::get_item($item['id']);
            $item['picture_path'] = $active_item->get_priority_picture();
        }

        //Closed Unsold Items
        $my_closed_unsold_items = Item::get_my_closed_unsold_items(self::get_user_or_redirect()->getId());
        foreach ($my_closed_unsold_items as &$item) {
            $closed_sold_item = Item::get_item($item['id']);
            $item['picture_path'] = $closed_sold_item->get_priority_picture();
        }

        //Sold Items
        $my_sold_items = Item::get_my_sold_items(self::get_user_or_redirect()->getId());
        foreach ($my_sold_items as &$item) {
            $sold_items = Item::get_item($item['id']);
            $item['picture_path'] = $sold_items->get_priority_picture();
        }
        $search= "";     
        $catSelected = 0;
        if(isset($_GET['param1'])){
            $param_nav_search =$_GET['param1'];
            $catSelected = Tools::url_safe_decode($param_nav_search[1]) ?? 0;
            $search = Tools::url_safe_decode($param_nav_search)[2] ?? "";
        }
        (new View("my_items"))->show([
            "my_active_items" => $my_active_items,
            "my_closed_unsold_items" => $my_closed_unsold_items,
            "my_sold_items" => $my_sold_items,
            "search" => $search,
            "catSelected" => $catSelected
        ]);
    }

    public function show_item(): void
    {
        $vItemStatus = VueItemStatus::get_item_status((int)$_GET["param1"]);
        
        if ($vItemStatus === null) {
            throw new Exception("Item introuvable");
        }
        $back_url = $_GET['param3'] ?? "item/index"; // declaration avant pour eviter Undefined VAriable
        $from_url = "";
        $priority = $_GET['param2'] ?? "1";

        if(isset($_GET['param3'])){            
            if(str_contains($_GET['param3'],'from_browse')){
                $back_url = "item/index";
                $from_url = "from_browse";
            }
            else if(str_contains($_GET['param3'],'myitems')){
                $back_url = "item/my_items";
                $from_url = "from_myitems";
            }
            else{
                $param_nav_search = Tools::url_safe_decode($_GET['param3']);                 
                $back_url= $param_nav_search[0].'/'.$_GET['param3'];
                $from_url = $_GET['param3'];
            }
        }

        
        $pictureObj = ItemPicture::getAPictureByItemAndPriority($vItemStatus->getItem()->getId(), $priority);
        $picturePrincipale = $pictureObj ? $pictureObj : null;
        $item = $vItemStatus->getItem();
        $errors = [];
        Auth::generate_csrf_token();
        if (isset($_POST['bidAmount'])) {
            if(!is_numeric($_POST['bidAmount'])){
                 $errors[] = "Le montant doit être un nombre valide.";
            }else{
            $errors = array_merge(
                Bid::validate_newMax_bid($item->getId(), $_POST['bidAmount']),
                $vItemStatus->validation_isBuyNowReached(),
            );
            }

            if (empty($errors)) {
                $bid = new Bid(Auth::get_user_or_false()->getId(), $item->getId(), AppTime::get_current_datetime(), $_POST['bidAmount']);
                $bid->save();
                $this->redirect("item", "show_item",$item->getId(),$priority,$from_url); // ajout de param2 et param3 
            }
        }
        if (isset($_POST['buyNow'])) {
            $errors = array_merge(
                $vItemStatus->validation_isBuyNowReached(),
            );

            if (empty($errors)) {
                $bid = new Bid(Auth::get_user_or_false()->getId(), $item->getId(), AppTime::get_current_datetime(), $item->getBuyNowPrice());
                $bid->save();
                $this->redirect("item", "show_item",$item->getId(),$priority,$from_url); 
            }           
        }

        $reviews = Review::getReviewsByItem($item->getId());
        $old = ['rating' => '','comment' => ''];
        (new View("show_item"))->show(['vItemStatus' => $vItemStatus,
         'item' => $item, 
         'picturePrincipale' => $picturePrincipale,
          'errors' => $errors, 
          'back_url'=>$back_url,
          'from_url'=>$from_url,
            'reviews' => $reviews,
            'old' => $old
        ]);
    }

    public function delete_item(): void
    {
        $this->require_login();
        $item = Item::get_item($_GET["param1"]);
        $from_url="";
        if(isset($_GET["param3"])){
            $from_url = $_GET["param3"];
        }
        $vItemStatus = $item->getVueItemStatus();
        if ($vItemStatus->isClosed() || $vItemStatus->hasBids()) {        
            $this->redirect("item", "show_item", $item->getId());
        }
        Auth::generate_csrf_token();
        if ($item->getOwner() != Auth::get_user_or_false()->getId() || empty($item)) {
            $this->redirect("item", "my_items");
        }

        if (isset($_POST["delete"])) {
            Auth::check_csrf();
            if (!empty($item->getItemBids())) {
                foreach ($item->getItemBids() as $bid) {
                    $bid->delete();
                }
            }
            if (!empty($item->getItemPictures())) {
                foreach ($item->getItemPictures() as $picture) {
                    $picture->delete();
                }
            }
            $item->delete();
            $this->redirect("item", "my_items");
        }
        (new View("delete_item"))->show(["item" => $item,'csrf_token' => $_SESSION['csrf_token'],"from_url"=> $from_url]);
    }


   public function manage_images_item(): void
    {
        $this->require_login();
        $item = Item::get_item($_GET["param1"]);
        self::notRightUser($item->getOwner());
        $vItemStatus = $item->getVueItemStatus();
        if ($vItemStatus->isClosed()) {
            // Si l'item est fermé, on redirige vers la page de l'item
            $this->redirect("item", "show_item", $item->getId());
            return; 
        }
        Auth::generate_csrf_token();
        $errors = [];
        // Gestion de back url et from url
        $picture_priority="1";
        if(isset($_GET["param2"]))
            $picture_priority =  $_GET["param2"];

        $from_url="";
        if(isset($_GET["param3"]))
            $from_url= $_GET["param3"];

        $back_url = "item/show_item/" . $item->getId() ."/". $picture_priority ."/". $from_url;

        if (isset($_POST["addPriority"]) || isset($_POST["removePriority"]) || isset($_POST["delete"]) || isset($_FILES['image'])) {
            Auth::check_csrf();

            // Ajouts d'image
            if (isset($_FILES['image']) && !empty($_FILES['image']['name'][0])) {
                $files = $_FILES['image'];
                $errors["addImage"] = [];

                foreach ($files["name"] as $index => $fileData) {

                    $file = [
                        'name' => $files['name'][$index],
                        'type' => $files['type'][$index],
                        'tmp_name' => $files['tmp_name'][$index],
                        'error' => $files['error'][$index],
                        'size' => $files['size'][$index],
                    ];

                    if ($file['error'] !== self::UPLOAD_ERR_OK) {
                        $errors["addImage"][$file['name']][] = "Erreur d'upload.";
                        continue;
                    }

                    $itemDirectory = "uploads/items/" . $item->getId() . "/";
                    if (!is_dir($itemDirectory)) { 
                        mkdir($itemDirectory);
                    }
                    $saveTo = Image::generate_picture_name($file["name"], true);

                    $newImage = new ItemPicture(
                        $item->getId(),
                        count($item->getItemPictures()) + 1,
                        $itemDirectory . $saveTo . '.jpg'
                    );

                    $validationErrors = $newImage->validate("addImage", $file);
                    if (!empty($validationErrors)) {
                        $errors["addImage"][$file['name']] = $validationErrors;
                        continue;
                    }

                    try {
                        $image = new SimpleImage();
                        $image->fromFile($file['tmp_name'])
                            ->bestFit(1080, 1080)
                            ->toFile($itemDirectory . $saveTo . ".jpg");

                        $image->fromFile($file['tmp_name'])
                            ->bestFit(360, 360)
                            ->toFile($itemDirectory . $saveTo . "_thumbnail.jpg");

                        $newImage->persist();
                    } catch (\Exception $e) {
                        $errors["addImage"][$file['name']][] = "Erreur traitement image.";
                    }
                }

                if (empty($errors["addImage"])) {
                    $this->redirect("item", "manage_images_item", $item->getId(), $picture_priority , $from_url);
                }
            } 


            // Augmentation de la priorité
            if (isset($_POST["addPriority"])) {
                $currentPriority = $_POST["addPriority"];
                $tmpPicture = $item->getPicture($currentPriority);              
                $errors["priority"] = $tmpPicture->validate("addPriority");

                if (empty($errors["priority"])) {
                    $oldPicturePriority = $item->getPicture($currentPriority - 1);
                    $tmpPicture->setPriority(count($item->get_item_pictures())+1);/*Je met la priority a (maxpriority+1), ceci est temporaire*/  
                    $tmpPicture->persist();
                    $oldPicturePriority->setPriority($currentPriority);                                      
                    $oldPicturePriority->persist();
                    $tmpPicture->setPriority($currentPriority-1);
                    $tmpPicture->persist();
                    $this->redirect("item", "manage_images_item", $item->getId(),$picture_priority, $from_url);
                }
                      
            }

            // Diminution de la priorité
            if (isset($_POST["removePriority"])) {
                $currentPriority = $_POST["removePriority"];/*Je récupère la priority du formulaire, c'est a dire de celle qu'on doit déplacer*/
                $tmpPictureAdeplacer = $item->getPicture($currentPriority);    /*Je récuèpre l'image concerné qu'il faut déplacer exmeple prio 1*/
                $errors["priority"] = $tmpPictureAdeplacer->validate("removePriority");

                if (empty($errors["priority"])) {                    
                    $pictureRemplacer = $item->getPicture($currentPriority+1); /*L'image qui va etre remplacer exemple prio 2*/
                    $pictureRemplacer->setPriority(count($item->get_item_pictures())+1); /*Je lui met la priorité nombre d'image + 1 exemple 4 pour etre sur que ce ne soit pas occupé*/
                    $pictureRemplacer->persist(); /*J'enregistre dans la Db cest a dire qu'a ce moment là la prio 2 n'est plus utilisée*/
                    $tmpPictureAdeplacer->setPriority($currentPriority+1); /*Je set la priority de celle que je déplace a 2 et je l'enregistre dans la DB*/ 
                    $tmpPictureAdeplacer->persist();
                    $pictureRemplacer->setPriority($currentPriority); /*Je remplace la prioruity temporaire qui était 4 par 1 et j'enregistre*/
                    $pictureRemplacer->persist();
                    $this->redirect("item", "manage_images_item", $item->getId(),$picture_priority, $from_url);
                    
                }
            }

            // Supression d'une image
            if (isset($_POST["delete"])) {
                $imageToBeRemoved = $item->getPicture($_POST["delete"]);
                $imageToBeRemoved->removeAndUpdatePriorities();
                $from_url;
                
                $getAllPictures = $item->getItemPictures();

                if (empty($getAllPictures)) {
                    // Si aucune image 
                    $this->redirect("item", "manage_images_item", $item->getId() ,"1",$from_url);
                } else {
                    // On compte le nomtre d'image, on prend la plus grande priorité sinon on ne met qu'une chaine de caractere vide dans le redirect
                    $maxPriority = count($getAllPictures);   
                    $newPriority = $maxPriority ? $maxPriority : "";      
                }
                $this->redirect("item", "manage_images_item", $item->getId(),$newPriority,$from_url);
            }
        }

            
        (new View("manage_images_item"))->show(["item" => $item,"errors" => $errors,'csrf_token' => $_SESSION['csrf_token'],'back_url'=>$back_url,'from_url'=>$from_url,'picture_priority' => $picture_priority]);
    }

    public function add_edit_item(): void{
        $this->require_login();
        $user = $this->require_login();
        
        $errors = [];
        $item=null;
        $back_url = "item/index";
        // $back_url ="item/index"; // valeur par default si pas de param3 , on extrait les params GET dès le début
            $picture_priority="";
            if(isset($_GET["param2"]))
                $picture_priority =$_GET["param2"]; //ajout de "/"
            $from_url ="";
            if(isset($_GET["param3"]))
                $from_url = $_GET["param3"];//ajout de "/"
                 
        

        $old = [
            "title" => "",
            "description" => "",
            "sale_duration" => "",
            "starting_bid" => "",
            "buy_now_price" => "",
            "direct_sale" => "",
            "categoriesIDS" => "",
        ];
        
        // On vérifie si il y a un param1 pour savoir si on est en edition
        if (isset($_GET["param1"])) {
   
            $item = Item::get_item($_GET["param1"]);     
            $vItemStatus=$item->getVueItemStatus();          

            // on vérifie que l'item edité appartient a l'utilisateur connecté sinon cela redirige.
            self::notRightUser($vItemStatus->getOwner()->getId());   
            // Si l'item est closed alors on redirige, on ne peut pas modifier un item terminé.       
            if ($vItemStatus->isClosed() || $vItemStatus->hasBids()) {
                $this->redirect("item", "show_item", $item->getId());
                return;
            }
            if ($item) {
                $categoriesIDS = [];
                if(!empty($item->getCategories())){
                    foreach ($item->getCategories() as $value){
                        $categoriesIDS[] = $value->getId();
                    }
                }
                $old = [
                    "title" => $item->getTitle(),
                    "description" => $item->getDescription(),
                    "sale_duration" => $item->getDurationDays(),
                    "starting_bid" => $item->getStartingBid() > 0 ? $item->getStartingBid() : "",
                    "buy_now_price" => $item->getStartingBid() > 0 ? $item->getBuyNowPrice() : "",
                    "direct_sale" => $item->getStartingBid() == 0 ? $item->getBuyNowPrice() : "",
                    "categoriesIDS" =>$item->getCategories() > 0 ? $categoriesIDS : [],
                ];
            }
           $back_url = "item/show_item/" . $item->getId(). '/' .$picture_priority. '/' .$from_url;
            
        }

        if (isset($_POST["title"]) && isset($_POST["sale_duration"]) && 
        (isset($_POST["starting_bid"]) || isset($_POST["direct_sale"])) ) {           
            if(empty($item)){       
                $item = new Item(                 
                   0,
                   $_POST["title"],
                   $_POST['description'] ?? "",
                   $user->getId(),
                   AppTime::get_current_datetime(),
                   empty($_POST['starting_bid']) ? (float)$_POST['direct_sale'] : (!empty($_POST['buy_now_price']) ? (float)$_POST['buy_now_price'] : null),
                   (int)$_POST['sale_duration'],
                   (float)$_POST['starting_bid'] ?? 0
                );  
            }else{                 
                $isAuction = !empty($_POST['starting_bid']) && (float)$_POST['starting_bid'] > 0;
                $item->setTitle($_POST['title']);
                $item->setDescription($_POST['description'] ?? "");
                $item->setDurationDays((int)$_POST['sale_duration']);
                $item->setStartingBid($isAuction ? (float)$_POST['starting_bid'] : 0);
                $bnPrice = $isAuction ? ($_POST['buy_now_price'] ?? "") : ($_POST['direct_sale'] ?? "");
                $item->setBuyNowPrice($bnPrice !== "" ? (float)$bnPrice : null);
            }
            $old = [
                "title"         => $_POST["title"],
                "description"   => $_POST["description"],
                "sale_duration" => $_POST["sale_duration"],
                "starting_bid"  => $_POST["starting_bid"] ?? "",
                "buy_now_price" => $_POST["buy_now_price"] ?? "",
                "direct_sale"   => $_POST["direct_sale"] ?? "",
                "categoriesIDS" => $_POST["categoriesIDS"] ?? [],
            ];
            
            // Validation des categories
            $catErrors[] = null;
            if(!empty($_POST["categoriesIDS"])){
                foreach ($_POST["categoriesIDS"] as $key => $value) {
                    $cat = Category::getCategoryObjectById($value);
                    $catErrors = $cat->validate("add_edit_item");
                }
                $errors = array_merge($item->validate("add_edit_item"),$catErrors);
            }else{
                $catErrors = $item->validate("add_edit_item");
            }
            if(empty($errors)){
                $item->setCreatedAt(AppTime::get_current_datetime());
                $item->persist();

                //supprimer les anciennes itemCategory sur l'item avant update
                foreach ($item->getCategories() as $key => $value) {
                    $newItemCategory = new ItemCategory($item->getId(), $value->getId());     
                    $newItemCategory->delete();           
                }

                //créer la nouvelle liste de ItemCategory
                foreach($_POST["categoriesIDS"] ?? [] as $categoriesID){
                    $newItemCategory = new ItemCategory($item->getId(), $categoriesID);
                    $newItemCategory->persist();
                }

                $this->redirect("item","show_item",$item->getId(),$picture_priority,$from_url);
            }  
       
        }
        

        (new View("add_edit_item"))->show(["errors" => $errors,
                                            "old" => $old,
                                            "back_url"=>$back_url,
                                            'from_url'=>$from_url,
                                            'picture_priority' => $picture_priority,
                                            'item'=> $item]);
    }
   
        public function purchases(): void
        {
            $this->require_login();
            $userConnected = Auth::get_user_or_false()->getId();

            $items = Item::get_otherOwner_purchased_items($userConnected);

            $myBids = [];
            $allSelers = [];
            $topSeller = null;

            if (!empty($items)) {
                foreach ($items as $item) {
                    $biggerBider = Bid::getBiggerBidByItem($item->getId());
                    if ($biggerBider && $biggerBider->getOwner() == $userConnected) {
                        $myBids[] = $biggerBider;
                        $allSelers[] = $biggerBider->getItemObject()->getOwner();
                    }
                }

                if (!empty($allSelers)) {
                    $counts = array_count_values($allSelers);
                    $maxCount = max($counts);
                    $mostFrequent = array_search($maxCount, $counts);
                    $topSeller = User::get_member_by_id($mostFrequent)->getPseudo();
                }
            }

            $back_url = "user/show_profile";

            (new View("purchases"))->show([
                'myBids' => $myBids,
                'topSeller' => $topSeller,
                'back_url' => $back_url
            ]);
        }

        public function sales_stats(): void{
            $this->require_login();
            $userConnected = Auth::get_user_or_false()->getId();
            $items = Item::get_owner_sold_items($userConnected);
            $biggerBids = [];
            $topBuyer = null;
            $back_url = "user/show_profile";
            if(!empty($items)){
                foreach ($items as $item) {
                    $myAnouncesBids = Bid::getBiggerBidByItem($item->getId());
                    $biggerBids[] = $myAnouncesBids;
                    $allBuyers[] = $myAnouncesBids->getOwner();
                }

                $counts = array_count_values($allBuyers);
                $maxCount = max($counts);
                $mostFrequent = array_search($maxCount, $counts);
                $topBuyer = User::get_member_by_id($mostFrequent)->getPseudo();
                
            }
                    
            (new View("sales_stats"))->show(['biggerBids' => $biggerBids, 'topBuyer' => $topBuyer,'back_url'=>$back_url]);
        
        }

        public function getMyItemsBySearch(): void{
            header('Content-Type: application/json; charset=utf-8');
            $cat =$_GET["param1"] ?? "";
            $search = $_GET["param2"] ?? "";
            $param_nav_search = Tools::url_safe_encode(["item/my_items",$cat,$search]);
            if (self::get_user_or_false()) {
                $userId = self::get_user_or_false()->getId();
                //partie my_items - active items
                $active_items = Item::get_my_active_items($userId, $search,$cat);
             
                foreach ($active_items as &$item) {
                    $actv_item = Item::get_item($item['id']);
                    $item['picture_path'] = $actv_item->get_priority_picture();
                    $item['picture_time'] = AppTime::get_current_datetime();
                    $item['time_left'] = AppTime::time_left($item['end_at']);
                }
                //partie my_items - closed items
                $closed_items = Item::get_my_closed_unsold_items($userId, $search,$cat);
                foreach ($closed_items as &$item) {
                    $cl_item = Item::get_item($item['id']);
                    $item['picture_path'] = $cl_item->get_priority_picture();
                    $item['picture_time'] = AppTime::get_current_datetime();
                    $item['time_left'] = AppTime::time_left($item['end_at']);            
                }

                //partie my_items - sold items
                $sold_items = Item::get_my_sold_items($userId, $search,$cat);
                foreach ($sold_items as &$item) {
                    $s_item = Item::get_item($item['id']);
                    $item['picture_path'] = $s_item->get_priority_picture();
                    $item['picture_time'] = AppTime::get_current_datetime();
                    $item['time_left'] = AppTime::time_left($item['end_at']);
                }
                echo json_encode(["my_active_items"=>$active_items,"my_closed_items"=>$closed_items,"my_sold_items"=>$sold_items,"param_nav_search" => $param_nav_search],JSON_UNESCAPED_UNICODE);

            }

        }

        public function getItemsBySearch(): void {
            header('Content-Type: application/json; charset=utf-8');         
            $cat = $_GET["param1"] ?? "";
            $search = $_GET["param2"] ?? "";
            
        
            $param_nav_search = Tools::url_safe_encode(["item/index",$cat,$search]);
            if (self::get_user_or_false()) {
                
                $userId = self::get_user_or_false()->getId();

                $items = Item::get_items_im_participating($userId,$search,$cat);
                foreach ($items as &$item) {
                    $the_item = Item::get_item($item['id']);
                    $item['picture_path'] = $the_item ? $the_item->get_priority_picture() : null;
                    $item['current_time'] = AppTime::get_current_datetime();
                    $item['time_left'] = AppTime::time_left($item['end_at']);
                }

                $available_items = Item::get_other_available_items($userId,$search,$cat);
                foreach ($available_items as &$item) {
                    $av_item = Item::get_item($item['id']);
                    $item['picture_path'] = $av_item->get_priority_picture();
                    $item['current_time'] = AppTime::get_current_datetime();
                    $item['time_left'] = AppTime::time_left($item['end_at']);
                }
                echo json_encode(["items_im_participating"=>$items,"other_available_items"=>$available_items,"param_nav_search" => $param_nav_search],JSON_UNESCAPED_UNICODE);

            } else {

                $available_items = Item::get_all_available_items($search,$cat);
                foreach ($available_items as &$item) {
                    $av_item = Item::get_item($item['id']);
                    $item['picture_path'] = $av_item->get_priority_picture();
                    $item['current_time'] = AppTime::get_current_datetime();
                    $item['time_left'] = AppTime::time_left($item['end_at']);
                }
                //echo json_encode($available_items,JSON_UNESCAPED_UNICODE);
                echo json_encode([
                    "other_available_items"=> $available_items,
                    "param_nav_search"=>$param_nav_search
                ],JSON_UNESCAPED_UNICODE);
            }
        }

        public function updateImagePriority(): void{
            header('Content-Type: application/json; charset=utf-8');
            $itemId = $_POST['itemId'];            
            $orderPriority = $_POST['order'] ?? [];
            $item = Item::get_item($itemId);
            $pictures = $item->getItemPictures();
            try {
                // On parcours toutes nos pictures aux quelles on met une priorité qui excede le nombre d'image si trois images elles auront les priorités 4,5,6
                // indexDeLaBoucle commence a 0.
                // getPicture() permet de recuperer une image via sa priority
                foreach ($orderPriority as $indexDeLaBoucle => $currentPriority) {
                    $picture = $item->getPicture($currentPriority);
                    $picture->setPriority(count($pictures) + 1 + $indexDeLaBoucle);
                    $picture->persist();
            }
                // 2ème passage : vraies priorités attribuées
                foreach ($orderPriority as $indexDeLaBoucle => $currentPriority) {
                    $picture = $item->getPicture(count($pictures) + 1 + $indexDeLaBoucle);
                    $picture->setPriority($indexDeLaBoucle + 1);
                    $picture->persist();
                }
            } catch (error) {
                echo json_encode(false);
            }
    
            echo json_encode($orderPriority,JSON_UNESCAPED_UNICODE);
        }

        public function getCarouselImages(): void{
            header('Content-Type: application/json; charset=utf-8');
            $itemId = $_GET['param1'];
            $item = Item::get_item($itemId);
            $pictures = $item->getItemPictures();
            $picturesPaths= [];
            foreach($pictures as $picture){
                $picturesPaths[] = $picture->getPicturePath();
            }
            echo json_encode($picturesPaths,JSON_UNESCAPED_UNICODE);
        }
        public function getRules() : void{
            header('Content-Type: application/json; charset=utf-8');
            $config = file_exists("config/dev.ini") 
                ? parse_ini_file("config/dev.ini", true) 
                : parse_ini_file("config/prod.ini", true);
            
            $rules = $config["RulesItem"] ?? [];
            echo json_encode($rules, JSON_UNESCAPED_UNICODE);
        }

        public function checkTitleUnique() : void{
            header('Content-Type: application/json; charset=utf-8');

            if(!Auth::user_logged()){
                echo json_encode(["unique"=>false]);
                return;
            }

            $userId = Auth::get_user_or_false()->getId();
            $title = $_GET["param1"] ?? "";
            $itemId = isset($_POST["itemId"]) ? (int)$_POST["itemId"] : 0;

            //title vide
            if(trim($title) === ""){
                echo json_encode(["unique" => true]);
                return;
            }

            $unique = Item::is_title_unique_for_owner($title,$userId, $itemId);


            echo json_encode(["unique"=> $unique]);
        }

}
