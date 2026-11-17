<template id="template-card">
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 zoom-card">
        <a href="" class="text-decoration-none item-link"> <!--on va creer un id pour recuperer la balise en JS et remplir son attribut href-->
            <div class="card auction-card bg-dark text-white border-0 rounded-4 overflow-hidden shadow">
                <div class="position-relative">
                    <!--pour Image : on alterne entre classes has-picture et has-no-picture  -->
                    <img src="" class="has-picture card-img-top" alt="" > 

                    <div class="has-no-picture card-img-top d-flex align-items-center justify-content-center bg-secondary bg-opacity-25" style="height: 180px;">
                        <i class="bi bi-image" style="font-size: 4rem;"></i>
                    </div>

                    <!-- Badge top-left : uniquement si participating -->

                    <span class="badge bg-dark bg-gradient rounded-pill position-absolute top-0 start-0 m-2 badge-size bidder-badge" >
                        <i class="bi bi-hammer"></i> Bidder
                    </span>

                    <span class="badge bg-success rounded-pill position-absolute top-0 start-0 m-2 p-2 highest-bidder-badge" >
                        <i class="bi bi-trophy"></i> Highest Bidder
                    </span>

                    <!-- Badges top-right : Auction / Buy Now -->
                    <div class="position-absolute top-0 end-0 m-2 vstack gap-1">
                        <span class=" badge bg-warning text-dark rounded-pill badge-size auction-badge" >
                            <i class="bi bi-hammer"></i> Auction
                        </span>

                        <span class=" badge bg-primary rounded-pill badge-size buynow-badge" >
                                <i class=" bi bi-bag"></i> Buy Now
                        </span>
                    </div>

                    <!-- Badge bottom-right : images count -->
                    <span class=" badge bg-black bg-opacity-75 rounded-pill position-absolute bottom-0 end-0 m-2 badge-size badge-img-count">
                        <i class="bi bi-images"> </i>
                    </span>
                </div>

                <div class="card-body">
                    <p class="card-title mb-1 item-title fw-bold"></p>
                    <small class="text-white-50 item-owner"></small>

                    <div class="d-flex justify-content-between align-items-end mt-3">
                        <!-- Prix gauche -->
                        <div class="pb-1">
                            <span class="fw-bold fs-5 item-price"></span>
                        </div>                       
                        <!-- Current bid droite -->
                        <div class="text-end current-bid-area">
                            <small class="text-white-50 d-block">Current bid</small>
                            <span class="fw-bold text-success current-bid-value"></span>
                        </div>
                    </div>

                    <!-- Timer -->
                    <div class="mt-3">
                        <span class="fw-bold badge-size item-timer">
                            <i class="bi bi-clock"></i><span class="time-left"></span>
                        </span>
                    </div>
                </div>
            </div>   
        </a>
    </div>
</template>