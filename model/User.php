<?php

require_once "framework/Model.php";

class User extends Model {

    private ?int $id;
    private string $full_name;
    private string $email;
    private string $pseudo;
    private string $hashed_password;
    private string $role;
    private ?string $picture_path;
    private ?string $iban;

    private static array $rules;

    public function __construct(
        ?int $id,
        string $full_name,
        string $email,
        string $pseudo,
        string $hashed_password,
        string $role,
        ?string $picture_path = null,
        ?string $iban = null
    ) {
        $this->id = $id;
        $this->full_name = $full_name;
        $this->email = $email;
        $this->pseudo = $pseudo;
        $this->hashed_password = $hashed_password;
        $this->role = $role;
        $this->picture_path = $picture_path;
        $this->iban = $iban;
    }

    private static function getRules(): array {
        if (!isset(self::$rules)) {
            $config = file_exists("config/dev.ini") 
                ? parse_ini_file("config/dev.ini", true) 
                : parse_ini_file("config/prod.ini", true);
            
            self::$rules = $config["RulesUser"];
        }
        return self::$rules;
    }

    
    public function validate(string $context = "default", array $file = null): array {
        $errors = [];

        switch ($context) {
            case "signup":
                $errors['email'] = $this->validate_email($_POST['email'] ?? '');
                $errors['full_name'] = $this->validate_full_name($_POST['full_name'] ?? '');
                $errors['pseudo'] = $this->validate_pseudo($_POST['pseudo'] ?? '');
                $errors['password'] = $this->validate_password($_POST['password'] ?? '');
                $errors['password_confirm'] = $this->equals_passwords($_POST['password'] ?? '',$_POST['password_confirm'] ??'');
                break;

            case "edit_profile":
                $errors['email'] = $this->validate_email($_POST['email'] ?? '');
                $errors['full_name'] = $this->validate_full_name($_POST['full_name'] ?? '');
                $errors['pseudo'] = $this->validate_pseudo($_POST['pseudo'] ?? '');
                $errors['iban'] = $this->validate_iban($_POST['iban'] ?? '');
                break;

            case "change_password":
                $errors['current_password'] =
                    $this->check_old_password($_POST['current_password'] ?? '');

                $errors['new_password'] =
                    $this->validate_password($_POST['new_password'] ?? '');

                $errors['confirm_password'] =
                    $this->equals_passwords(
                        $_POST['new_password'] ?? '',
                        $_POST['confirm_password'] ?? ''
                    );
                break;

            case "manage_picture":
                $errors['image'] = $this->validate_photo($file);
                break;
        }

        // cela supprimes les clés qui n'ont pas dérreur dans le tableau $errors.
        return array_filter($errors);
    }



    
    public function persist(): array {
        $errors = $this->validate();
        if (!empty($errors)) return $errors;

        if ($this->id !== null && self::get_member_by_id($this->id)) {
            self::execute(
                "UPDATE Users
                 SET full_name=:full_name, email=:email, pseudo=:pseudo,
                     password=:password, role=:role, picture_path=:picture_path, iban=:iban
                 WHERE id=:id",
                [
                    "id" => $this->id,
                    "full_name" => $this->full_name,
                    "email" => $this->email,
                    "pseudo" => $this->pseudo,
                    "password" => $this->hashed_password,
                    "role" => $this->role,
                    "picture_path" => $this->picture_path,
                    "iban" => $this->iban
                ]
            );
        } else {
            self::execute(
                "INSERT INTO Users(full_name,email,pseudo,password,role,picture_path,iban)
                 VALUES(:full_name,:email,:pseudo,:password,:role,:picture_path,:iban)",
                [
                    "full_name" => $this->full_name,
                    "email" => $this->email,
                    "pseudo" => $this->pseudo,
                    "password" => $this->hashed_password,
                    "role" => $this->role,
                    "picture_path" => $this->picture_path,
                    "iban" => $this->iban
                ]
            );
            $this->id = self::lastInsertId();
        }

        return [];
    }

    // validations
    public function validate_photo(array $file): array {
        $errors = [];
        $maxSize = 5 * 1024 * 1024;
        if (isset($file['name']) && $file['name'] != '') {
            if ($file['error'] == 0) {
                $valid_types = ["image/gif", "image/jpeg", "image/png", "image/webp"];
                if (!in_array($file['type'], $valid_types)) {
                    $errors[] = "Unsupported image format : gif, jpg/jpeg, png or WEBP.";
                }
                if($file["size"] > $maxSize){
                    $errors[] = "Image is too large. Maximum allowed size is 5 MB.";
                }
            } else {
                $errors[] = "Error while uploading file.";
            }
        }
        return $errors;
    }

    private function validate_email(): array {
        $errors = [];

        if (strlen($this->email) === 0) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email must be a valid email address.";
        }

        $existingUser = self::get_member_by_email($this->email);
        if ($existingUser) {            
            if ($this->id === null) { // on crée un nouveau compte on a pas d'id, mais il existe quelqu'un avec cet email en DB
                $errors[] = "This email is already used.";
            }
            
            elseif ($existingUser->getId() !== $this->id) { // on modifie notre email en mettant l'email d'une personne qui a un id différent du notre. Email déjà utilisé !
                $errors[] = "This email is already used.";
            }
        }


        return $errors;
    }

    private function validate_full_name(): array {
        $errors = [];
        $rules = self::getRules();

        if (!strlen($this->full_name)) {
            $errors[] = "Full name is required.";
        }

        if (strlen($this->full_name) < $rules['full_name_min_length']) {
            $errors[] = "Full name must be at least {$rules['full_name_min_length']} characters long.";
        }

        if (strlen($this->full_name) > $rules['full_name_max_length']) {
            $errors[] = "Full name must be at most {$rules['full_name_max_length']} characters long.";
        }

        if (!preg_match($rules['full_name_regex'], $this->full_name)) {
            $errors[] = "Full name format is invalid.";
        }

        if (!empty($rules['full_name_unique'])) {
            // Vérifie si le full_name est déjà utilisé par un autre utilisateur
            if ($this->id === null && self::full_name_exists_signup($this->full_name)) {
                $errors[] = "Full Name already used.";
            } elseif ($this->id !== null && self::full_name_already_used_by_other($this->full_name, $this->id)) {
                $errors[] = "Full Name already used.";
            }
        }

        return $errors;
    }

    private function validate_pseudo(): array {
        $errors = [];
        $rules = self::getRules();

        if (!strlen($this->pseudo)) {
            $errors[] = "Pseudo is required.";
        }

        if (strlen($this->pseudo) < $rules['pseudo_min_length'] || strlen($this->pseudo) > $rules['pseudo_max_length']) {
            $errors[] = "Pseudo length must be between {$rules['pseudo_min_length']} and {$rules['pseudo_max_length']}.";
        }

        if (!preg_match($rules['pseudo_regex'], $this->pseudo)) {
            $errors[] = "Pseudo format is invalid.";
        }

        if (!empty($rules['pseudo_unique'])) {
            // Vérifie si le pseudo est déjà utilisé par un autre utilisateur
            if ($this->id === null && self::pseudo_exists_signup($this->pseudo)) {
                $errors[] = "Pseudo already used.";
            } elseif ($this->id !== null && self::pseudo_already_used_by_other($this->pseudo, $this->id)) {
                $errors[] = "Pseudo already used.";
            }
        }

        return $errors;
    }

    private function validate_iban(): array {
        $errors = [];
        $rules = self::getRules();

        if ($this->iban === null || $this->iban === '') return $errors;

        if (!preg_match($rules['iban_regex'], $this->iban)) {
            $errors[] = "IBAN format is invalid.";
        }

        return $errors;
    }

    
    public function validate_login(string $password): array {
        $errors = [];
        $member = self::get_member_by_email($this->email);

        if (!$member) {
            $errors[] = "Can't find a member with the email '{$this->email}'. Please sign up.";
        } elseif (!password_verify($password, $member->hashed_password)) {
            $errors[] = "Wrong password. Please try again.";
        }

        return $errors;
    }
    public function validate_password(string $password): array {
        $errors = [];
        $rules = self::getRules();

        if (strlen($password) === 0) {
            $errors[] = "Password is required.";
        }

        if (strlen($password) < $rules['password_min_length']) {
            $errors[] = "Password must be at least {$rules['password_min_length']} characters long.";
        }

        if (strlen($password) > $rules['password_max_length']) {
            $errors[] = "Password must be at most {$rules['password_max_length']} characters long.";
        }

        if (!preg_match($rules['password_regex'], $password)) {
            $errors[] = "Password format is invalid. It must contain letters, numbers, and special characters.";
        }

        return $errors;
    }

    public function equals_passwords(string $password, string $confirm): array {
        if ($password !== $confirm) {
            return ["Passwords do not match."];
        }
        return [];
    }
    public function check_old_password(string $current_password): array {
        $errors = [];
        if (!password_verify($current_password, $this->hashed_password)) {
            $errors[] = "Current password is incorrect.";
        }
        return $errors;
    }

    public function change_password(string $new_password): void {
        $this->hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $this->persist();      
    }
    public function hasPicture() : bool {
        return !empty($this->picture_path) && file_exists($this->picture_path);
    }
    // GETTER ET SETTERS
    public static function full_name_exists_signup(string $full_name): bool {
        $query = self::execute(
            "SELECT id FROM Users WHERE full_name = :full_name",
            ["full_name" => $full_name]
        );
        return $query->rowCount() > 0;
    }

    public static function full_name_already_used_by_other(string $full_name, int $id): bool {
        $query = self::execute(
            "SELECT id FROM Users WHERE full_name = :full_name AND id != :id",
            [
                "full_name" => $full_name,
                "id" => $id
            ]
        );
        return $query->rowCount() > 0;
    }

    public static function pseudo_exists_signup(string $pseudo): bool {
        $query = self::execute(
            "SELECT id FROM Users WHERE pseudo = :pseudo",
            ["pseudo" => $pseudo]
        );
        return $query->rowCount() > 0;
    }

    public static function pseudo_already_used_by_other(string $pseudo, int $id): bool {
        $query = self::execute(
            "SELECT id FROM Users WHERE pseudo = :pseudo AND id != :id",
            [
                "pseudo" => $pseudo,
                "id" => $id
            ]
        );
        return $query->rowCount() > 0;
    }

    public static function get_member_by_id(int $id) : User|false {
        $query = self::execute("SELECT * FROM Users where id = :id", ["id"=>$id]);
        $data = $query->fetch(); 
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new User(
                $data["id"],
                $data["full_name"],
                $data["email"],
                $data["pseudo"],
                $data["password"],
                $data["role"] ?? "user",
                $data["picture_path"] ?? null,
                $data["iban"] ?? null
            );
        }
    }

    // Purchases - trouver le nom du TopSeller par rapport a id Idem du Bids
    public static function getTopSellerName (int $itemId) : string{
        $query = self::execute(
            "SELECT pseudo FROM users
            JOIN v_items_status v ON v.owner = users.id
            JOIN bids ON v.id = bids.item
            WHERE bids.item = :idItem
            LIMIT 1",["idItem"=>$itemId]
        );
        $sellerName = $query->fetch();
        return $sellerName;
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
    public static function get_member_by_email(string $email) : User|false {
        $query = self::execute("SELECT * FROM Users where email = :email", ["email"=>$email]);
        $data = $query->fetch(); 
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new User(
                $data["id"],
                $data["full_name"],
                $data["email"],
                $data["pseudo"],
                $data["password"],
                $data["role"] ?? "user",
                $data["picture_path"] ?? null,
                $data["iban"] ?? null
            );
        }
    }

    public static function getAllUsers() : array{
        $query = self::execute('Select * from Users',[]);
        $datas = $query->fetchAll();
        $users =[];
        foreach ($datas as $data) {
            $users[]= new User(
                $data["id"],
                $data["full_name"],
                $data["email"],
                $data["pseudo"],
                $data["password"],
                $data["role"] ?? "user",
                $data["picture_path"] ?? null,
                $data["iban"] ?? null
            );
        }
        return $users;
    }

    public function getId(): ?int { 
        return $this->id; 
    }

    public function getFullName(): string { 
        return $this->full_name;
    }

    public function getEmail(): string { 
        return $this->email;
    }

    public function getPseudo(): string {
        return $this->pseudo;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getHashedPassword(): string {
        return $this->hashed_password; 
    }

    public function getIban(): ?string { 
        return $this->iban; 
    }

    public function setFullName(string $fullName): void {
        $this->full_name = $fullName;
    }
    
    public function setEmail(string $email): void { 
        $this->email = $email; 
    }

    public function setPseudo(string $pseudo): void {
        $this->pseudo = $pseudo; 
    }

    public function setIban(?string $iban): void { 
        $this->iban = $iban;
    }
    
    public function setPicturePath(?string $picturePath): void { 
        $this->picture_path = $picturePath;
    }
}
