<?php Functions::header("Categories", "bi-tags"); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-9">
            <div class="card bg-app-card border border-secondary shadow-lg p-3" id="manage_categories_container">
                                      
                <div class="category-list">
                    <?php foreach ($categories as $category): ?>
                        <div class="row g-2 align-items-center mb-1 pb-2 border-bottom border-secondary">
                            <div class="col">
                                <form action="category/update" method="post" id="category_update_<?=$category->getId()?>">
                                    <input type="hidden" name="category_update_id" value="<?= $category->getId()?>">
                                    <input type="text" class="form-control bg-dark text-white border-secondary shadow-none" name="category_update_name" value="<?= Tools::sanitize($old[$category->getId()]['category_name'] ?? $category->getName()) ?>">
                                </form>                                
                            </div>
                            <div class="col-auto">

                                <div class="d-flex align-items-center">
                                    <?php if($category->hasHigherPriority()){ ?>
                                        <button type="button" class="btn btn-link link-secondary text-decoration-none btn-sm fw-bold me-1" disabled>Up</button>
                                    <?php }else{?>
                                        <form action="category/upCategory" method="POST" class="d-inline">
                                            <input type="hidden" name="category_up" value="<?= $category->getId()?>">
                                            <button type="submit" class="btn btn-link link-secondary text-decoration-none btn-sm fw-bold border-secondary me-1">
                                                Up
                                            </button>
                                        </form>
                                    <?php }?>
                                    <?php if($category->hasLowerPriority()){ ?>    
                                        <button type="button" class="btn btn-link link-secondary text-decoration-none btn-sm fw-bold me-1" disabled>Down</button>
                                    <?php }else{?>
                                        <form action="category/downCategory" method="POST" class="d-inline">
                                            <input type="hidden" name="category_down" value="<?= $category->getId()?>">
                                            <button type="submit" class="btn btn-link link-secondary text-decoration-none btn-sm fw-bold border-secondary me-1">
                                                Down
                                            </button>
                                        </form>
                                    <?php }?>
                                    <button form="category_update_<?=$category->getId()?>" type="submit" class="btn btn-link link-success text-decoration-none btn-sm fw-bold border-secondary me-1">Save</button>
                                    <?php if($category->hasItemCategories()){ ?>
                                        <button type="button" class="btn btn-link link-danger text-decoration-none btn-sm fw-bold" disabled>Delete</button>
                                    <?php }else{?>
                                        <form action="category/destroy" method="post">
                                            <input type="hidden" name="category_destroy" value="<?= $category->getId()?>">
                                            <button type="submit" class="btn btn-link link-danger text-decoration-none btn-sm fw-bold border-secondary">Delete</button>
                                        </form>
                                    <?php }?>
                                   
                                </div>
                            </div>
                            <?php if(isset($errors[$category->getId()])): ?>
                                <div class="text-danger small mt-1">
                                    <?php foreach ($errors[$category->getId()]['category_update'] as $error): ?>
                                        <div><?= $error ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if(!empty($errors['category_destroy'])): ?>
                        <div class="text-danger">
                            <?php foreach ($errors['category_destroy'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                
                    <form action="category/store" method="post" class="row g-2 align-items-center pt-2">
                        <div class="col">
                            <input name="category_name" type="text" 
                                class="form-control bg-transparent text-white border-secondary shadow-none" 
                                placeholder="Category name" value="<?= $old["category_name"]??"" ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary px-3">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </form>
                    <?php if(!empty($errors['category_name'])): ?>
                        <div class="text-danger">
                            <?php foreach ($errors['category_name'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger border-2 bg-dark text-light">
      
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title text-danger fw-bold">
            ⚠️ Confirmation de suppression
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body text-center py-4">
        <p class="text-warning small mt-2">
          Êtes-vous sûr de vouloir supprimer la catégorie : <span id="modal-cat-name" class="text-danger fw-bold"></span>
        </p>
      </div>
      
      <div class="modal-footer border-top border-secondary">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Annuler</button>
        <button type="button" id="confirm-delete-btn" class="btn btn-danger px-4">Supprimer</button>
      </div>

    </div>
  </div>
</div>
<?php Functions::footer(); ?>