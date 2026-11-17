<?php Functions::header("Show Item","bi-cart4",$back_url);?>
    <div class="container mt-4">
        <div class="row g-3">     
            <div class="col-lg-8">
                <div class="rounded-3 overflow-hidden shadow-sm">
        
                    <div class="position-relative">
                    <!-- Partie carousel avec Bootstrap 5.3 Javascript activé -->
                        <?php if(parse_ini_file("config/dev.ini", true)["JavaScript"]['disable_js'] == false) { ?>
                            <div id="carouselItem" class="carousel slide" data-item-id="<?= $item->getId()?>">
                                <div class="carousel-inner">                                                                
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselItem" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselItem" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        <?php }else{?>     
                            <!-- Sans javascript , sans carousel     -->
                            <?php if ($picturePrincipale !== null) { ?>
                                <img src="<?= Tools::sanitize($picturePrincipale->getPicturePath()); ?>"
                                    class="w-100" style="max-height:420px; object-fit:cover;" alt="<?= Tools::sanitize($item->getTitle()); ?>">
                            <?php } else { ?>
                                <div class="w-100 d-flex align-items-center justify-content-center bg-secondary bg-opacity-25"
                                    style="height:420px;">
                                    <i class="bi bi-image" style="font-size: 4rem;"></i>
                                </div>
                            <?php } ?>
                    
                        <?php } ?>        
                    </div>
                    <div class="p-3 bg-dark">
                        <div class="position-relative">
                            <h2 class="text-light"><?= $item->getTitle(); ?></h2>
                        <?php if($vItemStatus->isAuction()){?>
                            <span class="badge bg-warning text-dark rounded-pill badge-size position-absolute top-0 end-0 "><i class="bi bi-hammer"></i> Auction</span>
                        <?php }?>    
                        </div> 
                        <p class="mb-3 text-white-50"><?= $item->getDescription(); ?></p>
                        <?php $cat = $item->getCategories();?>
                        
                        <?php 
                            if(!empty($cat)){?>
                                <p class="mb-0 text-white-50">Catègories</p>
                                    <ul class="mb-0">
                                     <?php   foreach($cat as $category){ ?>
                                            <li class="text-light"> <?= Tools::sanitize($category->getName()) ?></li>
                                <?php }
                            } ?>
                        </ul>
                        <p class="mb-0 text-white-50">Start: <?= $item->getCreatedAt(); ?></p> 
                        <!-- Start: 10/11/2025 04:16:04 -->
                        <p class="mb-0 text-white-50">Ends: <?= $vItemStatus->getEndAt(); ?></p>
                
                    </div>
                </div>
                <section id="images-additional">
                    <?php if(parse_ini_file("config/dev.ini", true)["JavaScript"]['disable_js'] == false) { ?>
                        <div class="rounded-3 mt-3">
                            <div class="mb-2 text-white-50">Additional Images</div>
                            <div class="d-flex gap-2 thumbnail-container">                                    
                            </div>
                        </div> 
                    <?php }else{ ?>
                    <?php  if($item->getItemPictures() != null) {?>         
                        <div class="rounded-3 mt-3">
                            <div class="mb-2 text-white-50">Additional Images</div>
                            <div class="d-flex gap-2">
                                <?php foreach ($item->getItemPictures() as $picture) { ?>
                                    <?php
                                        $isActive = $picturePrincipale !== null
                                            && $picturePrincipale->getPriority() === $picture->getPriority();
                                    ?>
                                    <a href="item/show_item/<?= $item->getId()."/".$item->getPicture($picture->getPriority())->getPriority() ."/". $from_url?>">    
                                        <img class="img-open-item <?= $isActive ? 'border border-info' : '' ?>" src="<?= $picture->getPicturePath() !== null ? $picture->getPicturePath(true) : "";?>" alt="">
                                    </a>
                                <?php }?> 
                            </div>
                        </div> 
                    <?php     } ?>
                <?php     } ?>  
                </section>
                <?php if($vItemStatus->isAuction()){?>
                <div class="mt-3 p-3 bg-dark text-white-50">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Bid History</span>
                        <span class="badge bg-secondary text-white rounded-pill badge-size"> <?= $vItemStatus->getBidCount(); ?> entrie<?= $vItemStatus->getBidCount()>1 ? "s" : "" ?></span>
                    </div>

                    <div class="table-responsive ">
                        <table class="table table-dark table-borderless align-middle mb-0 ">
                            <tbody>
                                
                            <?php 
                            if($vItemStatus->getBidCount() > 0){
                            foreach ($item->getItemBids() as $index => $bid) { ?>
                            <tr class="<?php if($index<$vItemStatus->getBidCount()-1){ ?>border-bottom<?php }?>">
                                <td class="fw-semibold"><?= $bid->getOwnerPseudo();?></td>
                                <td class="text-success fw-semibold">€ <?= $bid->getAmount();?></td>
                                <td class="text-end "><?= (new DateTime($bid->getCreatedAt()))->format('d/m/Y H:i:s') ?></td>
                            </tr>
                            <?php }
                            }else{?>  
                            No bids yet. Be the first participate!
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <div class="col-lg-4 ">            
                <div class="p-3 mb-3 shadow-sm bg-dark rounded">
                    <p class="mb-3 text-secondary">Pricing</p>
                    <?php 
                        $userConnected = Auth::get_user_or_false();
                        $userConnectedId = $userConnected !== false ? $userConnected->getId() : null;
                        $userConnectedIsTheOwner = $userConnected !== false && $userConnected->getId() == $item->getOwner();
                    ?>
                    <?php if($vItemStatus->getMaxBid()>0){?>            
                    <div class="d-flex justify-content-between">
                        <span class="text-light">Current Bid</span>  
                        <span class="fw-bold text-success">€ <?= $vItemStatus->getMaxBid() ?></span> 
                    </div>
                    <?php }?>
                    <?php if($vItemStatus->hasBuyNow() && $vItemStatus->isAuction()){ ?>
                    <div class="d-flex justify-content-between"> <!-- Annonce ouverte && Achat immédiat possible-->
                        <span class="text-light">Buy Now</span>
                        <span class="fw-bold text-light">€ <?= $vItemStatus->getItem()->getBuyNowPrice()?></span>
                    </div>
                    <?php }?>
                    <?php if(!$vItemStatus->isClosed() && $vItemStatus->hasBuyNow() && !$vItemStatus->isAuction()){ ?>
                    <div class="d-flex justify-content-between"> <!-- Annonce ouverte && Achat immédiat possible uniquement-->
                        <span class="text-light">Price</span>
                        <span class="fw-bold text-success">€ <?= $item->getBuyNowPrice()?></span>
                    </div>
                    <?php }?>
                    <?php if(!$vItemStatus->isClosed() && $vItemStatus->isAuction() && !$vItemStatus->hasBids()){ ?>
                    <div class="d-flex justify-content-between">  <!-- Annonce ouverte && Achat immédiat impossible && pas encore d'enchère-->
                        <p class="mb-0 text-white-50">Starting at € <?= $item->getMinimumBidAmount() ?></p>
                    </div>
                    <?php }?>
                    <div class="border border-secondary border-bottom my-1"></div>
                    <?php if (count($errors) != 0): ?>
                            <div class='text-danger mt-1'>                   
                                <ul class="ps-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li class="list-unstyled"><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>                 
                        <?php if(!$vItemStatus->isClosed() && $vItemStatus->isAuction() && !$userConnectedIsTheOwner && $userConnectedId !== null){ ?>
                        <?php $priority = $picturePrincipale != null ? $picturePrincipale->getPriority()  : "1"; ?>
                        <form action="item/show_item/<?= $item->getId() . "/" . $priority . "/" . $from_url ?>" method="post">
                        <div class="mt-3">                        
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-light">€</span>
                                    <input name="bidAmount" type="float" class="form-control bg-dark border-secondary text-light" value="<?= $item->getMinimumBidAmount() ?>">
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-3 mb-0">
                                <button type="submit" class="btn btn-success fw-semibold">Place Bid</button>
                            </div>
                        </form>
                        <?php }?>     
                                               
                            <?php if(!$vItemStatus->isClosed() && $vItemStatus->isAuction() && $userConnectedIsTheOwner && $userConnectedId !== null){ ?>
                                <div class="d-grid gap-2 mb-2 mt-2">  
                                    <button class="btn btn-success fw-semibold" disabled>Place Bid</button>   <!-- Annonce ouverte && achat immediat possible && enchère possible && Utilisateur créateur ! -->
                                <p class="mb-0 text-white-50">You cannot bid yon your own listing.</p>
                                </div>
                                <?php } ?>
                            <?php if(!$vItemStatus->isClosed() && $vItemStatus->isAuction() && $vItemStatus->hasBuyNow() && !$userConnectedIsTheOwner && $userConnectedId !== null){ ?>
                            <form action="item/show_item/<?= $item->getId() ."/". $picturePrincipale->getPriority() ."/". $from_url ?>" method="post"> <!-- ajout de param2 et param3 -->
                                <div class="d-grid gap-2 mb-2 mt-2">   
                                    <button type="submit" name="buyNow" class="btn btn-outline-info fw-semibold">Buy Now at € <?= $item->getBuyNowPrice()?></button>
                                </div>
                            </form>
                            <?php } ?>
                            <?php if(!$vItemStatus->isClosed() && $vItemStatus->isDirectSale() && !$userConnectedIsTheOwner && $userConnectedId !== null){ ?>
                            <form action="item/show_item/<?= $item->getId()."/". $picturePrincipale->getPriority() ."/". $from_url  ?>" method="post"> <!-- ajout de param2 et param3 -->
                            <div class="d-grid gap-2 mb-2 mt-2">   
                                <button type="submit" name="buyNow" class="btn btn-success fw-semibold"><i class="bi bi-cart-check"></i> Buy Now</button> <!-- Annonce ouverte && Achat immédiat uniquement -->
                            </div>
                            </form>
                            <?php } ?>
                           
                            <?php if(!$vItemStatus->isClosed() && $vItemStatus->isDirectSale() && $userConnectedIsTheOwner && $userConnectedId !== null){ ?>
                            <div class="d-grid gap-2 mb-2 mt-2">  
                                <button class="btn btn-success fw-semibold" disabled><i class="bi bi-cart-check"></i> Buy Now</button> <!-- Annonce ouverte && Achat immédiat uniquement && Utilisateur créateur -->
                            </div>
                            <?php } ?>                      
                        <!-- </div> -->
                 
                    <?php if(!$vItemStatus->isClosed() && $vItemStatus->isDirectSale() && $userConnectedIsTheOwner){ ?>
                    <div class="d-flex justify-content-between">  <!-- Annonce ouverte && Achat immédiat uniquement && enchère on/off && Utilisateur créateur !-->
                        <p class="mb-0 text-white-50">You cannot bid yon your own listing.</p>
                    </div>
                    <?php } ?>
                    <?php if($vItemStatus->isClosed() && !$vItemStatus->hasBids() && $userConnectedId !== null){ ?>
                    <div class="d-flex justify-content-between">  <!-- Annonce fermée && pas d'acheteur !-->
                        <p class="mb-0 text-white-50">This listing ended without a buyer.</p>
                    </div>
                    <?php } ?>
                    <?php if($vItemStatus->isClosed() && $userConnectedId !== null && $vItemStatus->hasBids() && $userConnectedId != Bid::getBiggerBidByItem($item->getId())->getOwner()){ ?>
                    <div class="d-flex justify-content-between">  <!-- Annonce fermée && acheteur gagnant != différent de la personne connecté !-->
                        <p class="mb-0 text-white-50"><?= Bid::getBiggerBidByItem($item->getId())->getOwnerPseudo(); ?> won this item for € <?= Bid::getBiggerBidByItem($item->getId())->getAmount();?></p>
                    </div>
                    <?php } ?>
                    <?php if($vItemStatus->isClosed() && $userConnectedId !== null && $vItemStatus->hasBids() && $userConnectedId == Bid::getBiggerBidByItem($item->getId())->getOwner()){ ?>
                    <div class="d-flex justify-content-between">  <!-- Annonce fermée && acheteur gagnant == Utilisateur connecté qui regarde l'annonce !-->
                        <p class="mb-0 text-white-50">Congratulations! Your purchased this item for € <?= Bid::getBiggerBidByItem($item->getId())->getAmount()?></p>
                    </div>
                    <?php } ?>
                    
                </div>

                <div class="p-3 shadow-sm bg-dark rounded">
                    <p class="mb-3 text-secondary">Seller Information</p>
                    <div class="d-flex align-items-center">                    
                        <?php if($vItemStatus->getOwner()->hasPicture()){ ?>
                            <img class="rounded-circle img-thumbnail img-thumbnail-regular mb-3 me-2" src="<?= Tools::sanitize($vItemStatus->getOwner()->getPicturePath(true)); ?>"  alt="">
                        <?php }else{ ?>
                            <i class="bi bi-person-circle text-white-50 me-2" style="font-size:40px;"></i>
                        <?php }?>
                        <div>
                            <div class="fw-semibold text-light"><?=$vItemStatus->getOwner()->getPseudo();?></div>
                            <div class="text-secondary"><?=$vItemStatus->getOwner()->getRole() == "user" ? "Member" : "Admin";?></div>
                        </div>
                    </div>
                </div>
                <?php if ($userConnectedIsTheOwner && !($vItemStatus->isClosed() || $vItemStatus->hasBids())) { ?>
                    <div class="p-3 shadow-sm bg-dark rounded mt-3">
                        <p class="mb-3 text-secondary">Manage Your Item</p>
                        <div class="d-grid gap-2">
                            <a href="item/add_edit_item/<?= $item->getId() ?><?= $picturePrincipale !== null ? "/".$picturePrincipale->getPriority()."/" : "/1/" ?><?= $from_url ?>" class="btn btn-outline-secondary fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-pencil"></i>
                                Edit Item Details
                            </a>

                            <a href="item/manage_images_item/<?= $item->getId()?><?= $picturePrincipale !== null ? "/".$picturePrincipale->getPriority()."/"  : "/1/" ?><?= $from_url ?>" class="btn btn-outline-info fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-image"></i>
                                Manage Images
                            </a>

                            <a href="item/delete_item/<?= $item->getId()?><?= $picturePrincipale !== null ? "/".$picturePrincipale->getPriority()."/"  : "/1/" ?><?= $from_url ?>" class="btn btn-outline-danger fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-trash"></i>
                                Delete Item
                            </a>
                        </div>
                    </div>
                <?php }?>   
            </div>            
        </div>
</div>
<section id="reviews">
    <input type="hidden" id="item-id" value="<?= $item->getId() ?>">
    
    <input type="hidden" id="connected-user-id" value="<?= $userConnectedId ?? 0 ?>">
    <input type="hidden" id="is-admin" value="<?= Auth::isAdmin() ? '1' : '0' ?>">

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Liste des reviews -->
                <div class="bg-dark rounded p-3 mb-3" >
                    <h5 class="text-secondary mb-3">Reviews</h5>
                    <div id="reviews-list">
                        <?php if(empty($reviews)): ?>
                            <p class="text-white-50">No reviews yet.</p>
                        <?php else: ?>
                            
                            <?php foreach($reviews as $review): ?>
                                <div class="border-bottom border-secondary pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-light fw-bold">
                                            ⭐ <?= $review->getRating() ?>/5
                                        </span>
                                        <small class="text-white-50">
                                            <?= $review->getCreationDate() ?>
                                        </small>
                                    </div>
                                    <p class="text-white-50 mb-1">
                                        <?= Tools::sanitize($review->getComment() ?? "") ?>
                                    </p>
                                    <!-- Bouton delete si auteur ou admin -->
                                    
                                    <?php if($userConnectedId == $review->getAuthor() || Auth::isAdmin()): ?>
                                        <form action="review/destroy" method="post">
                                            <input type="hidden" name="review_destroy" value="<?= $review->getId() ?>">
                                            <button type="submit" class="btn btn-link link-danger btn-sm p-0">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>    
                </div>

                <!-- Formulaire d'ajout -->
                <?php if($userConnectedId !== null ): ?>
                    <div class="bg-dark rounded p-3" >
                        <h5 class="text-secondary mb-3">Leave a Review</h5>
                        
                        <?php if(!empty($errors['already_reviewed'])): ?>
                            <div class="text-danger mb-2"><?= $errors['already_reviewed'][0] ?></div>
                        <?php endif; ?>

                        <form id="review-form" action="review/store" method="post"class="<?= Review::hasAlreadyReviewed($item->getId(), $userConnectedId) ? 'd-none' : '' ?>">
                            
                            
                            <!-- Rating -->
                            <div class="mb-3">
                                <label class="text-light fw-bold">Rating *</label>
                                <select name="rating" class="form-select bg-dark text-light border-secondary">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($old['rating'] ?? "") == $i ? 'selected' : '' ?>>
                                            <?= $i ?> ⭐
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <?php if(!empty($errors['rating'])): ?>
                                    <div class="text-danger mt-1">
                                        <?php foreach($errors['rating'] as $error): ?>
                                            <div><?= $error ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Comment -->
                            <div class="mb-3">
                                <label class="text-light fw-bold">Comment <span class="text-secondary">(optional)</span></label>
                                <textarea name="comment" class="form-control bg-dark text-light border-secondary" rows="3"><?= Tools::sanitize($old['comment'] ?? "") ?></textarea>
                                <?php if(!empty($errors['comment'])): ?>
                                    <div class="text-danger mt-1">
                                        <?php foreach($errors['comment'] as $error): ?>
                                            <div><?= $error ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div id="review-errors" class="mb-2"></div>

                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>    

<?php Functions::footer(); ?>