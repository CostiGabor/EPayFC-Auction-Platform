<?php

require_once "framework/Controller.php";
require_once 'utils/Functions.php';
require_once 'utils/Auth.php';
require_once 'utils/Image.php';
require_once 'model/User.php';
require_once 'framework/Tools.php';
require_once 'lib/SimpleImage.php'; 

class ControllerUser extends Controller {
    const UPLOAD_ERR_OK = 0;

    protected function require_guest(): void {
        if (Auth::user_logged()) {
            $this->redirect("item", "index"); 
        }
    }

    protected function require_login(): object {
        if (!Auth::user_logged()) {
            $this->redirect("user", "login");
        }
        return $_SESSION['user'];
    }
    
    public function index(): void{
        $user = $this->require_login();
        $this->redirect("item", "index"); 
    }

    public function login(): void{        
        $this->require_guest();
        $email = '';
        $password = '';
        $errors = [];
        if (isset($_POST['email']) && isset($_POST['password'])) { 
            $email = $_POST['email'];
            $password = $_POST['password'];
            $user = User::get_member_by_email($email);
            if ($user) {
                $errors = $user->validate_login($password);
                if (empty($errors)) {
                    $this->log_user($user);
                    return;
                }
            } else {
                $errors[] = "Can't find a member with the email '$email'. Please sign up.";
            }
        }
        (new View("login"))->show(["email" => $email, "password" => $password, "errors" => $errors]);
    }

    public function user_logout(): void{
        $_SESSION = [];
        session_destroy();
        $this->redirect("user", "index");
    }

   public function debug_connexion(): void {
        if(!Configuration::is_dev())
            self::index();
        $email = $_POST['email'] ?? '';
        $errors = [];

        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email fourni n'est pas valide.";
        } else {
            $user = User::get_member_by_email($email);
            if (!$user) {
                $errors[] = "Aucun utilisateur trouvé avec cet email.";
            }
        }

        if (empty($errors)) {
            $this->log_user($user);         
        }

        (new View("login"))->show([
            "email" => $email,
            "errors" => $errors
        ]);
    }


    public function show_profile(): void{
        $user = $this->require_login();
        (new View("show_profile"))->show(['user' => $user]);
    }

    public function manage_picture_profile(): void {    
        $user = $this->require_login();
        $picture_path = '';
        $errors = [];
        Auth::generate_csrf_token();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === self::UPLOAD_ERR_OK) {   
            Auth::check_csrf(); 
            $file = $_FILES['image'];       
            $errors = $user->validate("manage_picture", $_FILES['image']);

            if (empty($errors)) {
                $userDirectory = "uploads/users/" . $user->getId() . "/";
                $saveTo = Image::generate_picture_name($user->getPseudo());

                if (!is_dir($userDirectory)) { 
                    mkdir($userDirectory);
                }

                if (!empty($user->getPicturePath())) {
                    $oldFileName = pathinfo($user->getPicturePath(), PATHINFO_FILENAME);
                    if (file_exists($userDirectory . $oldFileName . ".jpg")) {
                        unlink($userDirectory . $oldFileName . ".jpg");
                        unlink($userDirectory . $oldFileName . "_thumbnail.jpg");
                    }
                }        

                try {
                    $image = new \claviska\SimpleImage();
                    $basename = pathinfo($saveTo, PATHINFO_FILENAME); 
                    $image->fromFile($file['tmp_name'])
                          ->bestFit(1080, 1080) 
                          ->toFile($userDirectory . $saveTo . ".jpg", 'image/jpeg', 100);

                    $image->fromFile($file['tmp_name'])
                          ->bestFit(360, 360)
                          ->toFile($userDirectory . $saveTo . "_thumbnail.jpg", 'image/jpeg', 100);

                    $user->setPicturePath($userDirectory . $saveTo . '.jpg');
                    $user->persist();
                    $this->redirect("user", "manage_picture_profile");
                    return;

                } catch (\Exception $e) {
                    $errors[] = "Erreur lors du traitement de l'image : " . $e->getMessage();
                }
            } 
        }
        $back_url = "user/show_profile"; 

        (new View("manage_picture_profile"))->show([
            "user" => $user, 
            "errors" => $errors, 
            'csrf_token' => $_SESSION['csrf_token']
            ,'back_url' => $back_url
        ]);
    }

    public function manage_picture_profile_delete(): void {    
        $user = $this->require_login();
        $userDirectory = "uploads/users/" . $user->getId() . "/";

        $oldFileName = pathinfo($user->getPicturePath(), PATHINFO_FILENAME);
        if ($oldFileName && file_exists($userDirectory . $oldFileName . ".jpg")) {
            unlink($userDirectory . $oldFileName . ".jpg");
            unlink($userDirectory . $oldFileName . "_thumbnail.jpg");

            if (is_dir($userDirectory)) {
                rmdir($userDirectory);
            }

            $user->setPicturePath(null);
            $user->persist();
        }

        $this->redirect("user", "manage_picture_profile"); 
    }

    public function signup(): void {
        $this->require_guest();
        Auth::generate_csrf_token();

        $errors = [];
        $old = [
            'full_name' => '',
            'email' => '',
            'pseudo' => ''
        ];

        if (isset($_POST['email'], $_POST['full_name'], $_POST['pseudo'], $_POST['password'], $_POST['password_confirm'])) {
            Auth::check_csrf();

            $old = [
                'full_name' => trim($_POST['full_name']),
                'email' => trim($_POST['email']),
                'pseudo' => trim($_POST['pseudo'])
            ];

            $user = new User(
                null,
                $old['full_name'],
                $old['email'],
                $old['pseudo'],
                password_hash($_POST['password'], PASSWORD_BCRYPT),
                "user"
            );

            $errors = $user->validate("signup");

            if (empty($errors)) {
                $user->persist();
                $this->log_user($user);
                return;
            }
            
        }

        (new View("signup"))->show(['old' => $old,'errors' => $errors,'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function change_password(): void {
        $user = $this->require_login();  
        Auth::generate_csrf_token();

        $errors = []; 

        if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
            Auth::check_csrf();
            
            $errors = $user->validate("change_password");
            
            if (empty($errors)) {
                $user->change_password($_POST['new_password']);
                $this->redirect("user", "show_profile");
            }
        }
        $back_url = "user/show_profile"; 

        (new View("change_password"))->show(["csrf_token" => $_SESSION["csrf_token"], "errors" => $errors,"back_url"=>$back_url]);
    }



    public function edit_profile(): void{     
        $user = $this->require_login();   
        Auth::generate_csrf_token();
        $errors = []; 
        $old = [
            "full_name" => $user->getFullName(),
            "pseudo" => $user->getPseudo(),
            "email" => $user->getEmail(),
            "iban" => $user->getIban() 
        ];
       
        if(isset($_POST['email']) && isset($_POST['full_name']) && isset($_POST['pseudo'])){   
            Auth::check_csrf();
  
            $old = [
                "full_name" => trim($_POST["full_name"]),
                "pseudo" => trim($_POST["pseudo"]),
                "email" => trim($_POST["email"]),
                "iban" => trim($_POST["iban"] ?? "")
            ];     

            $tmp = new User(
                $user->getId(),
                $old["full_name"],
                $old["email"],
                $old["pseudo"],
                $user->getHashedPassword(),
                $user->getRole(),
                $user->getPicturePath(),
                $old["iban"] === "" ? null : $old["iban"]
            );

            $errors = $tmp->validate("edit_profile");

            if (empty($errors)) {
                $user->setFullName($tmp->getFullName());
                $user->setEmail($tmp->getEmail());
                $user->setPseudo($tmp->getPseudo());
                $user->setIban($tmp->getIban());

                $user->persist();
                $this->redirect("user", "edit_profile");
            }
            
        }
        $back_url = "user/show_profile"; 
               
        (new View("edit_profile"))->show([
            'user' => $user, 
            'errors' => $errors, 
            'old' => $old,
            'csrf_token' => $_SESSION['csrf_token']
            ,"back_url"=>$back_url
        ]);
    }
}
