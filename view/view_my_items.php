<?php Functions::header("My Items","bi-cart4");?>   
    <div class="container mt-4" id="my_own_items_page">
        <?php Functions::search_bar($search,$catSelected);?>
<!--         Declaration de la variable qui vaut un tableau de l'url + l'input de la SearchBar -->     
        <?php 
        $config = file_exists("config/dev.ini") 
            ? parse_ini_file("config/dev.ini", true) 
            : parse_ini_file("config/prod.ini", true);

        if($config["JavaScript"]['disable_js'] == false) {
        // Je déclare une variable qui vaut un tableau de l'url ou je suis + la recherche 
            $search = Tools::url_safe_encode($search); 
        }        
            $from_url ="from_myitems"; ?>
        <?php if(Auth::user_logged()){?>
            <?php Functions::template_card();?>
            <div id="activeItems" class="my-4">
                <h3 class="text-app-title mb-3">Active Items</h3>
                <div class="row gy-4">
                <?php 
                if(empty($my_active_items)){
                    echo '<p class="text-center text-light mt-3">
                                Aucune annonce active.
                            </p>';
                }
                    foreach ($my_active_items as $item){?>
                    <!-- passer la variable a mes cards car elles en auront besoin pour l'url de la card -->
                        <?php Functions::card($item,false,$from_url); ?>
                <?php } ?>
                </div>
        </div>
        <?php } ?>
        <div id="closedUnsoldItems" class="mt-6">        
            <h3 class="text-app-title mb-3"> Closed Unsold Items</h3>
            <div class="row gy-4">
                <?php 
                if(empty($my_closed_unsold_items)){
                    echo '<p class="text-center text-light mt-3">
                                Aucune Closed Unsold Items.
                            </p>';
                }
                foreach ($my_closed_unsold_items as $item) { ?>
                <!-- passer la variable a mes cards car elles en auront besoin pour l'url de la card -->
                    <?php Functions::card($item,false,$from_url); ?>
                <?php } ?>
            </div>
        </div>
         <div id="soldItems" class="mt-6">        
            <h3 class="text-app-title mb-3">Sold Items</h3>
            <div class="row gy-4"> 
                <?php 
                if(empty($my_sold_items)){
                    echo '<p class="text-center text-light mt-3">
                                Aucune Sold Items.
                            </p>';
                }
                foreach ($my_sold_items as $item) { ?>
                 <!-- passer la variable a mes cards car elles en auront besoin pour l'url de la card -->
                    <?php Functions::card($item,false,$from_url); ?>
                <?php } ?>
        </div>
    </div>
</div>
<?php Functions::footer(); ?>