<?php Functions::header("Purchases","bi-cart4",$back_url);?>   
<div class="p-4 text-light">
    <div class="position-relative">
        <span class="badge bg-primary position-absolute top-0 end-0  rounded-pill "><?= count($myBids) ?> purchases </span>
        <h1 class=" mb-0">My Purchases</h1>
        <p class="text-white-50">Track the gear you've successfully secured.</p>
    </div>   
    <div class="row gy-3">
        <div class="col-12 col-sm-6 col-md-4 col-lg-4">        
            <div class="rounded bg-app-card p-3 stats-card-min-height"> 
                <p class="text-uppercase text-white-50 mb-0">TOTAL SPEND</p>
                <?php 
                    $totalSomme=0;
                    foreach($myBids as $bids){
                        $totalSomme =$totalSomme+ $bids->getAmount();
                    }
                ?>
                <h1 class="mb-0">€ <?= $totalSomme ?></h1>
                <p class="mb-0 text-white-50 ">Across <?= count($myBids) ?> purchases</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-4">    
            <div class="rounded bg-app-card p-3 stats-card-min-height"> 
                <p class="text-uppercase text-white-50 mb-0">AVERAGE TICKET</p>
                <?php 
                    
                    $averageAmount = $totalSomme > 0 ? $totalSomme / count($myBids): 0;
                ?>
                <h1 class="mb-0">€ <?= $averageAmount ?></h1>
                <p class="mb-0 text-white-50 ">Helps you plan future bids</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-4">    
            <div class="rounded bg-app-card p-3 stats-card-min-height"> 
                <p class="text-uppercase text-white-50 mb-0">TOP SELLER</p>
                <p class="fw-bold mb-0 fs-4"><?= $topSeller ?></p>
                <p class="text-white-50 mb-0">Who you've bought from the most</p>
            </div>
        </div>
    </div>
    <div class="row gy-3 my-4"> 
        <?php foreach($myBids as $bid) { 
            $item = $bid->getItemObject();?>
        <!-- Carte AUCTION -->
            <?php Functions::card_sales_purchases($item,$bid);?>    
        <?php } ?>
    </div>
</div>
<?php Functions::footer(); ?>