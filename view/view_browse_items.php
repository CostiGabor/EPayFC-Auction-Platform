
<?php Functions::header("Browse", "bi-cart4"); ?>
<div class="container mt-4" id="browse_items">
    <?php Functions::search_bar($search, $catSelected);     
        $from_url ="from_browse";
        Functions::template_card();
        if (Auth::user_logged()) { 
            
        ?>
        <div id="participatingIn" class="my-4">
            <h3 class="text-app-title mb-3">Items I'm Participating In</h3>
            <div class="row gy-4">
               
                <?php
                if (empty($items)) {
                    echo '<p class="text-center text-light mt-3">
                                Vous ne participez à aucune annonce.
                            </p>';
                }
                foreach ($items as $item) {  ?>
                <?php Functions::card($item,true,$from_url);?>
                <?php    } ?>


            </div>
        </div>
    <?php } ?>
    <div id="otherItems" class="mt-6">
        <h3 class="text-app-title mb-3"> Other Available Items</h3>
        <div class="row gy-4">

            <?php
            if (empty($available_items)) {
                echo '<p class="text-center text-light mt-3">
                               Il n\'y a aucune annonce.
                            </p>';
            }
            foreach ($available_items as $item) {  ?>
                <?php Functions::card($item,false,$from_url);?>
            <?php } ?>
        </div>
    </div>


</div>
    <?php Functions::footer(); ?>