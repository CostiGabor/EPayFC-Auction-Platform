<?php Functions::header("Sales", "bi-cart-check",$back_url); ?>
<div class="p-4 text-light">
    <div class="position-relative">
        <span class="badge text-bg-success position-absolute top-0 end-0 badge-size rounded-pill"><?= count($biggerBids) ?> sales</span>
        <h1 class=" mb-0">Completed Sales</h1>
        <p class="text-white-50">A snapshot of forever the deals you've wrapped up.</p>
    </div>   
    <div class="row gy-3">
        <div class="col-12 col-sm-6 col-md-4 col-lg-4">        
            <div class="rounded bg-app-card p-3 stats-card-min-height"> 
                <p class="text-uppercase text-white-50 mb-0">TOTAL REVENUE</p>
                <?php 
                    $totalGain = 0;
                    foreach($biggerBids as $bid){
                        $totalGain = $totalGain + $bid->getAmount();
                    }
                ?>
                <h1 class="mb-0">€ <?= $totalGain ?></h1>
                <p class="mb-0 text-white-50 ">Across <?= count($biggerBids) ?> sales</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-4">    
            <div class="rounded bg-app-card p-3 stats-card-min-height"> 
                <p class="text-uppercase text-white-50 mb-0">AVERAGE TICKET</p>
                <h1 class="mb-0">
                € <?= count($biggerBids) > 0 ? $totalGain / count($biggerBids) : 0 ?>
                </h1>
                <p class="mb-0 text-white-50 ">Median buyer appetite indicator</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-4">    
            <div class="rounded bg-app-card p-3 stats-card-min-height"> 
                <p class="text-uppercase text-white-50 mb-0">LOYAL BIDDER</p>
                <p class="fw-bold mb-0 fs-4"><?= $topBuyer ?></p>
                <p class="text-white-50 mb-0">Most recurring winning bidder</p>
            </div>
        </div>
    </div>
    <div class="row gy-3 my-4"> 
        <?php foreach($biggerBids as $bid) { 
            $item = $bid->getItemObject();?>
        <!-- Carte AUCTION -->
        <?php Functions::card_sales_purchases($item,$bid);?>
        <?php } ?>
 </div>
</div>
<?php Functions::footer(); ?>