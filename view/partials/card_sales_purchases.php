<div class="col-12 col-md-4 col-lg-3 col-xxl-2 zoom-card">
    <a href="item/show_item/<?= $item->getId() ?>" class="text-decoration-none">
        <div class="card auction-card bg-dark text-white border-0 rounded-4 overflow-hidden shadow">
            <div class="position-relative">
                <img src="<?= $item->get_priority_picture() ?>" class="card-img-top" alt="">

                <div class="position-absolute top-0 end-0 m-2 vstack gap-1">
                    <?php if ($item->getVueItemStatus()->isAuction()): ?>
                        <span class="badge bg-warning text-dark rounded-pill badge-size">
                            <i class="bi bi-hammer"></i> Auction
                        </span>
                    <?php endif; ?>
                    <?php if ($item->getVueItemStatus()->isDirectSale() || $item->getVueItemStatus()->hasBuyNow()): ?>
                        <span class="badge bg-primary rounded-pill badge-size">
                            <i class="bi bi-bag"></i> Buy Now
                        </span>
                    <?php endif; ?>
                </div>

                <span class="badge bg-black bg-opacity-75 rounded-pill position-absolute bottom-0 end-0 m-2 badge-size">
                    <i class="bi bi-images"></i> <?= count($item->get_item_pictures()) ?> images
                </span>
            </div>

            <div class="card-body">
                <p class="card-title mb-1"><?= $item->getTitle() ?></p>
                <small class="text-white-50">by <?= $item->getOwnerPseudo() ?></small>

                <div class="d-flex justify-content-between align-items-end mt-3">
                    <div class="pb-1">
                        <?php if ($item->getVueItemStatus()->hasBids() && !$item->getVueItemStatus()->hasBuyNow()): ?>
                            <span class="fw-bold text-primary fs-5">€ <?= $item->getVueItemStatus()->getMaxBid() ?></span>
                        <?php endif; ?>
                        <?php if ($item->getVueItemStatus()->hasBuyNow()): ?>
                            <span class="fw-bold text-primary fs-5">€ <?= $item->getBuyNowPrice() ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($item->getVueItemStatus()->isAuction()): ?>
                        <div class="text-end">
                            <small class="text-white-50 d-block">Current bid</small>
                            <span class="fw-bold text-success">€ <?= $bid->getAmount() ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-3">
                    <span class="text-white-50 fw-bold badge-size">
                        <i class="bi bi-clock"></i> Closed
                    </span>
                </div>
            </div>
        </div>
    </a>

    <!-- Infos sous la card -->
    <div class="p-3 text-white-50 rounded-3">
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-currency-dollar me-2 text-secondary"></i>
            <span>Final price € <?= $bid->getAmount() ?></span>
        </div>
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-trophy me-2 text-secondary"></i>
            <span><?= $bid->getOwnerPseudo() ?></span>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-clock me-2"></i>
            <span>Closed on <?= $bid->getCreatedAt() ?></span>
        </div>
    </div>
</div>