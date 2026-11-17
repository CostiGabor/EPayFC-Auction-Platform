<?php 
class Auth {
         //indique si un l'utilisateur est connecté
        public static function user_logged(): bool {
            return isset($_SESSION['user']);
        }

        //renvoie l'utilisateur connecté ou false personne n'est connecté
        public static function get_user_or_false(): object|false {
            return self::user_logged() ? $_SESSION['user'] : false;
        }

        public static function isAdmin(): bool{
            $user=self::get_user_or_false();
            return $user && $user->getRole() == 'admin';
        }

        public static function isOwnedByCurrentUser(int $userID){
            $currentUser = self::get_user_or_false();
            if (!$currentUser) {
                return false; 
            }
            return $currentUser->getId() === $userID;
        }

        public static function generate_csrf_token(): void {
            if (empty($_SESSION['csrf_token'])) {
                 $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
             }
        }

        public static function check_csrf(): void {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                Tools::abort("Invalid CSRF token");
                exit;
            }
        }

        
    }