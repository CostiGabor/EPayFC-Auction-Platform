<?php

require_once 'Configuration.php';

class View {

    private string $file;

    public function __construct(string $action) {
        // if(str_contains($action,".")){
        //     $action = $this->get_folder($action);
        //     $this->file = "$action.php";
        // }else{
        //     $this->file = "view/view_$action.php";
        // }
        $this->file = "view/view_$action.php";       
    }

    //affiche la vue en lui passant les données reçues
    //sous forme de variables
    public function show(array $data = array()): void {
        if (file_exists($this->file)) {
            extract($data);
            $web_root = Configuration::get("web_root");
            require $this->file;
        } else {
            throw new Exception("File '$this->file' does'nt exist");
        }
    }

    // public function get_folder(string $action) : String{        
    //         $pos = stripos($action,".");
    //         $folder = substr($action,0,$pos);
    //         $view_path = substr($action,$pos+1);
    //         $path = "view/".$folder."/view_".$view_path;
    //         return $path;      
    // }

}
