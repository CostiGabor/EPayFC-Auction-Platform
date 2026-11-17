<?php
class Functions {
    public static function header(string $page_title = null, string $icon, $back_url = null): void {
        $web_root = Configuration::get("web_root");
        require "view/partials/header.php";
    }

    public static function footer(): void {
        require "view/partials/footer.php";
    }

    public static function card(array $item,bool $is_participating,$from_url = null,$search = null): void{
        require "view/partials/card.php";
    }
    public static function card_sales_purchases(object $item,object $bid): void{
        require "view/partials/card_sales_purchases.php";
    }

    public static function template_card(): void{
        require "view/partials/template_card.php";
    }
    public static function search_bar(string $search ,string $catSelected ): void{
        require "view/partials/search_bar.php";
    }
   

}