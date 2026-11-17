<?php 
$title = !empty($_GET['param1']) ? "Edit Item" : "Add Item";
Functions::header($title, "bi-cart-plus", $back_url);
?>
<div class="modal fade" id="leaveConfirmModal" tabindex="-1" role="dialog" aria-labelledby="leaveConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-app-card text-light">
      <div class="modal-header border-bottom-app-color">
        <h1 class="modal-title fs-5" id="leaveConfirmModalLabel">
            <i class="bi bi-exclamation-triangle text-warning me-2"></i>Unsaved changes</h1>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
            You have unsaved chnages. Leave anyway? 
      </div>
      <div class="modal-footer border-top-app-color">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="leaveConfirmCancel">Close</button>
        <button type="button" class="btn btn-danger" id="leaveConfirmLeave">Leave</button>
      </div>
    </div>
  </div>
</div>
<div class="p-4 pb-0 text-white-50">     <!-- ajout de param2 et param3 s'ils existent , pour être dispo lors du POST -->
    <form action="item/add_edit_item<?= empty($_GET["param1"]) ? "" : "/".$_GET["param1"].(!empty($_GET["param2"]) ? "/".$_GET["param2"] : "/1").(!empty($_GET["param3"]) ? "/".$_GET["param3"] : "") ?>" method="post">
        <div class="card bg-app-card-header mb-4 ">
            <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
                    Basic Information
            </div>
            <div class="bg-app-card">
                <div class="p-3">
                    <label for="itemTitle" class="text-light form-label fw-bold ">Item Title *</label>
                    <input name="title" type="text" id="itemTitle" class="form-control border border-secondary <?= !empty($errors['title']) ? 'border-danger' : 'border-secondary' ?>" value="<?= Tools::sanitize($old['title'] ?? $item->getTitle()) ?>" placeholder="">
                    <div class="invalid-feedback" id="title-feedback"></div>
                    <?php if (!empty($errors['title'])): ?>
                        <div class="text-danger mt-1">
                            <?php foreach ($errors['title'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="px-3">
                    <label for="itemDescription" class="text-light form-label fw-bold ">Description</label>
                    <textarea name="description" class="form-control border border-secondary" id="itemDescription" rows="3"><?= Tools::sanitize($old['description'] ?? '') ?></textarea>
                    <div class="invalid-feedback" id="description-feedback"></div>
                    <?php if (!empty($errors['description'])): ?>
                        <div class="text-danger mt-1">
                            <?php foreach ($errors['description'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                 <div class="mb-3 p-3">
                    <label for="itemDays" class="text-light form-label fw-bold ">Sale Duration (days) *</label>
                    <input name="sale_duration" type="number" id="itemDays" class="form-control border border-secondary <?= !empty($errors['sale_duration']) ? 'border-danger' : 'border-secondary' ?>" 
                        value="<?= Tools::sanitize($old['sale_duration'] ?? '') ?>">
                        <div class="invalid-feedback" id="sale_duration-feedback"></div>
                        <?php if (!empty($errors['sale_duration'])): ?>
                        <div class="text-danger mt-1">
                            <?php foreach ($errors['sale_duration'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>     
        </div>
        

        <div class="card bg-app-card-header mb-4">
            <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
                Sale Type
            </div>

            <div class="bg-app-card px-3 pt-3">
                <div id="sale_type-feedback" class="text-danger"></div>
            </div>
            
            <div class="bg-app-card px-3 pt-3">
                <div class="card border-primary mb-3 bg-transparent">
                    <div class="card-header text-primary fs-5 bg-blue-transparent">
                        <i class="icon-bi-auction me-2"></i> <span class="fw-bold">Option 1: Auction</span>
                    </div>
                    <div class="card-body text-primary">
                        <div class="mb-2">
                            <label for="startingBid" class="text-light form-label fw-bold ">Starting Bid</label>
                            <input type="number" step="0.01" name="starting_bid" id="startingBid" 
                                class="form-control text-light border <?= !empty($errors['starting_bid']) ? 'border-danger' : 'border-secondary' ?>" 
                                value="<?= Tools::sanitize($old['starting_bid'] ?? '') ?>" placeholder="e.g., 50.00">
                            <div class="invalid-feedback" id="starting_bid-feedback"></div>
                        <?php if (!empty($errors['starting_bid'])): ?>
                            <div class="text-danger mt-1">
                                <?php foreach ($errors['starting_bid'] as $error): ?>
                                    <div><?= $error ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="instantPurchasePrice" class="text-light form-label fw-bold ">Instant Purchase Price <span class="text-secondary fw-bold">(Optional)</span></label>
                            <input type="number" step="0.01" name="buy_now_price" id="instantPurchasePrice" 
                                class="form-control text-light border <?= !empty($errors['buy_now_price']) ? 'border-danger' : 'border-secondary' ?>" 
                                value="<?= Tools::sanitize($old['buy_now_price'] ?? '') ?>" placeholder="e.g., 200.00">                   
                        <div class="invalid-feedback" id="buy_now_price-feedback"></div>
                        <?php if (!empty($errors['buy_now_price'])): ?>
                            <div class="text-danger mt-1">
                                <?php foreach ($errors['buy_now_price'] as $error): ?>
                                    <div><?= $error ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        </div>
                    </div>
                </div>               
            </div> 

            <div class="bg-app-card px-3">
                <div class="card border-success mb-3 bg-transparent">
                    <div class="card-header text-success fs-5 bg-green-transparent">
                        <i class="bi bi-cart4 me-2"></i> <span class="fw-bold">Option 2: Direct Sale</span>
                    </div>
                    <div class="card-body text-success">
                        <div class="mb-2">
                            <label for="salePrice" class="text-light form-label fw-bold ">Sale Price</label>
                            <input type="number" step="0.01" name="direct_sale" id="salePrice" 
                                class="form-control text-light border <?= !empty($errors['direct_sale']) ? 'border-danger' : 'border-secondary' ?>" 
                                value="<?= Tools::sanitize($old['direct_sale'] ?? '') ?>" placeholder="e.g., 150.00">
                        </div> 
                        <div class="invalid-feedback" id="direct_sale-feedback"></div> 
                    <?php if (!empty($errors['direct_sale'])): ?>
                        <div class="text-danger mt-1">
                            <?php foreach ($errors['direct_sale'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>                
                    </div>
                </div>               
            </div>       
        </div>
        <?php if (!empty(Category::getAll())) { ?>
            <div class="card bg-dark mb-4 border-secondary">
                <div class="card-header text-light fs-5 fw-semibold border-secondary">
                    Categories
                </div>
                <div class="card-body">
                    <p class="small mb-3 text-secondary">You can select up to 3 categories.</p>
                    <div class="row px-2" id="categories">
                        <?php
                            $catEnable = [];
                            if (!empty($old["categoriesIDS"]) && is_array($old["categoriesIDS"])) {
                            $catEnable = $old["categoriesIDS"];
                            }
                        ?>
                        <?php foreach (Category::getAll() as $cat) { ?>
                            <?php
                                $id = $cat->getId();
                                $checked = in_array($id, $catEnable) ? "checked" : "";
                            ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox"
                                id="cat_<?= $id ?>"
                                name="categoriesIDS[]"
                                value="<?= $id ?>"
                                <?= $checked ?>>
                                <label class="form-check-label text-light" for="cat_<?= $id ?>">
                                    <?= Tools::sanitize($cat->getName()) ?>
                                </label>
                            </div>
                        <?php } ?>

                        <?php if (!empty($errors['categoriesIDS'])) { ?>
                            <div class="text-danger mt-1">
                                <?php foreach ($errors['categoriesIDS'] as $error) { ?>
                                    <div><?= $error ?></div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <button type="submit" id="saveBtn" class="position-fixed top-0 end-0 z-3 border-0 bg-transparent fs-4 me-4 text-app-title">
            <i class="bi bi-floppy-fill"></i>
        </button>
    </form>
    <?php if(isset($item)) {?>
     <!-- champ caché avec Id de item ou 0  -->
        <input type="hidden" id="itemId" value="<?= $item->getId() != null ? $item->getId() : 0 ?>">
    <?php } ?>    
</div>

<?php Functions::footer(); ?>