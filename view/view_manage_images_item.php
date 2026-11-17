<?php Functions::header("Manage images", "bi-cart",$back_url); ?>

<div class="p-4 pb-0 text-white-50">
    <div class="position-relative">
        <h1 class=" mb-0">Manage Items for "the name" of product from DB"</h1>
    </div>
    <form action="item/manage_images_item/<?= $item->getId() ."/".$picture_priority ."/".$from_url ?>" method="post" enctype='multipart/form-data'>
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="card bg-app-card-header mb-4">
            <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
                Add New Images
            </div>
            <div class="bg-app-card">
                <div class="mb-3 p-3">
                    <label for="formFile" class="text-light form-label fw-bold ">Select Images</label>
                    <input name="image[]" class="form-control text-light border border-secondary" type="file" id="formFile" multiple>
                    <span class="text-secondary fw-semibold">You can select multiple images(JPG, PNG,GIF,WebP).
                        Images will be added to the end of your current list.</span>
                    <div>
                        <button type="submit" class="btn btn-primary d_block">Upload Images</button>
                    </div>
                <?php if (!empty($errors['addImage'])): ?>
                    <div class="text-danger mt-3">
                        <?php foreach ($errors['addImage'] as $filename => $fileErrors): ?>
                            <?php if (!empty($fileErrors)): ?>
                                <div><strong><?= htmlspecialchars($filename) ?> :</strong></div>
                                <ul>
                                    <?php foreach ($fileErrors as $err): ?>
                                        <li><?= htmlspecialchars($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>



                </div>
               
            </div>
        </div>

        <div class="card bg-app-card-header mb-4">
            <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
                Manage Existing Images
            </div>

            <div class="card-body bg-app-card">
                <div id="sortable" class="d-flex justify-content-start gap-4 flex-wrap">

                    <?php if (count($item->getItemPictures()) == 0) {
                        echo '<p class="w-100 text-center text-light">Aucune image pour cet item</p>';
                    } ?>
                
                    <?php foreach ($item->getItemPictures() as $picture) { ?>
                        <div class="text-center">
                            <div class="m-2">
                                <img src="<?= $picture->getPicturePath(true); ?>" class="rounded border border-light ui-state-default" alt="Image de <?= $item->getTitle() ?>" data-item-id="<?= $item->getId() ?>">
                            </div>

                            <div class="d-flex justify-content-center gap-2">

                                <button name="addPriority" value="<?= $picture->getPriority() ?>" class="btn btn-secondary btn-sm" <?= $picture->getPriority() == 1 ? "disabled" : "" ?>>
                                    <i class="bi bi-arrow-left"></i>
                                </button>

                                <button name="removePriority" value="<?= $picture->getPriority() ?>" class="btn btn-secondary btn-sm" <?= $picture->getPriority() == count($item->getItemPictures()) ? "disabled" : "" ?>>
                                    <i class="bi bi-arrow-right"></i>
                                </button>

                                <button name="delete" value="<?= $picture->getPriority() ?>" class="btn btn-danger btn-sm">
                                    <i class="bi bi-file-excel"></i>
                                </button>

                            </div>
                        </div>
                    <?php } ?>

                </div>

                <?php if (!empty($errors['priority'])) { ?>
                    <div class="text-danger mt-3">
                        <?php foreach ($errors['priority'] as $error): ?>
                            <div><?= $error ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php } ?>
            </div>

        </div>
        
    </form>
</div>


<?php Functions::footer(); ?>