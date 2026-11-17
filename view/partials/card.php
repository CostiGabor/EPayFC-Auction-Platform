<div class="col-12 col-sm-6 col-md-4 col-lg-3 zoom-card"> 
    <!-- 3. J'utilise la variable reçu et l'injecte dans le href -->
    <a href="item/show_item/<?= $item['id']; ?>/1/<?= $from_url; ?>" class="text-decoration-none">
        <div class="card auction-card bg-dark text-white border-0 rounded-4 overflow-hidden shadow">
            <div class="position-relative">

                <?php if (!empty(Tools::sanitize($item['picture_path']))): ?>
                    <img src="<?= Tools::sanitize($item['picture_path']) ?>" class="card-img-top" alt="">
                <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center bg-secondary bg-opacity-25" style="height: 180px;">
                        <i class="bi bi-image" style="font-size: 4rem;"></i>
                    </div>
                <?php endif; ?>

                <!-- Badge top-left : uniquement si participating -->
                <?php if ($is_participating): ?>
                    <?php if (!$item['is_highest_bidder']): ?>
                        <span class="badge bg-dark bg-gradient rounded-pill position-absolute top-0 start-0 m-2 badge-size">
                            <i class="bi bi-hammer"></i> Bidder
                        </span>
                    <?php else: ?>
                        <span class="badge bg-success rounded-pill position-absolute top-0 start-0 m-2 p-2">
                            <i class="bi bi-trophy"></i> Highest Bidder
                        </span>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Badges top-right : Auction / Buy Now -->
                <div class="position-absolute top-0 end-0 m-2 vstack gap-1">
                    <?php if ($item['is_auction']): ?>
                        <span class="badge bg-warning text-dark rounded-pill badge-size">
                            <i class="bi bi-hammer"></i> Auction
                        </span>
                    <?php endif; ?>
                    <?php if ($item['is_direct_sale'] || $item['has_buy_now']): ?>
                        <span class="badge bg-primary rounded-pill badge-size">
                            <i class="bi bi-bag"></i> Buy Now
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Badge bottom-right : images count -->
                <span class="badge bg-black bg-opacity-75 rounded-pill position-absolute bottom-0 end-0 m-2 badge-size">
                    <i class="bi bi-images"></i> <?= Tools::sanitize($item['pictures_count']) ?> images
                </span>
            </div>

            <div class="card-body">
                <p class="card-title fw-bold mb-1"><?= Tools::sanitize($item['title']) ?></p>
                <small class="text-white-50">by <?= Tools::sanitize($item['owner_pseudo']) ?></small>

                <div class="d-flex justify-content-between align-items-end mt-3">
                    <!-- Prix gauche -->
                    <div class="pb-1">
                        <?php if ($item['is_auction'] && !$item['has_buy_now']): ?>
                            <span class="fw-bold fs-5 text-warning">
                                <?= $item['starting_bid'] ?>
                            </span>
                        <?php elseif ($item['has_buy_now']): ?>
                            <span class="fw-bold text-primary fs-5">
                                <?= $item['buy_now_price'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Current bid droite -->
                    <?php
                    $show_current_bid = ($item['is_auction'] && $item['max_bid'] != null);
                    ?>
                    <?php if ($item['is_auction']): ?>
                        <div class="text-end">
                            <small class="text-white-50 d-block"><?= $item['max_bid'] ? "Current bid" : "Starting bid" ?></small>
                            <span class="fw-bold text-success"><?= Tools::sanitize($item['max_bid'] ? $item['max_bid'] : $item['starting_bid']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Timer -->
                <div class="mt-3">
                    <?php 
                        $timeLeft = AppTime::time_left($item['end_at']);
                        // On extrait le chiffre avant le 'd' (ex: "1d 20h" -> 1)
                        $daysLeft = (int)explode('d', $timeLeft)[0];
                        
                        // Logique de couleur : rouge si 0 jour, vert sinon
                        $timerClass = ($daysLeft === 0) ? 'text-warning' : 'text-success';
                    ?>
                    <span class="badge-size <?= $timerClass ?> fw-bold">
                        <i class="bi bi-clock"></i> <?= Tools::sanitize($timeLeft) ?>
                    </span>
                </div>
            </div>
        </div>
    </a>
</div>
