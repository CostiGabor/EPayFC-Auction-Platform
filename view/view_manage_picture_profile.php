<?php Functions::header("Manage Profile Picture", "bi-person-bounding-box",$back_url); ?>
<div class="p-4 pb-0 text-white-50">
    <div class="card bg-app-card-header mb-4 text-white-50 ">
        <div class="card-header  fs-5 fw-semibold border-bottom-app-color text-white">
                Current Profile Picture
        </div>
        <div class="bg-app-card  text-center pt-3">
        <?php if($user->hasPicture()){ ?>
            <img class="rounded-circle img-thumbnail img-thumbnail-regular mb-3" src="<?= $user->getPicturePath(true); ?>"  alt="">
        <?php }else{ ?>
            <i class="bi bi-person-circle " style="font-size:80px;"></i>
        <?php }?>
            <h4 class="text-light mb-0"><?= Tools::sanitize($user->getFullName()) ?></h4>
            <span class="d-block text-white-50 pb-3 fs-5">@<?= Tools::sanitize($user->getPseudo()) ?></span>
        </div>
    </div>

    <div class="card bg-app-card-header mb-4">
        <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
            Upload New Picture
        </div>
        <div class="bg-app-card">
            <div class="mb-3 p-3">
                 <?php if (count($errors) != 0): ?>
                    <div class='errors text-light mb-2'>
                        <p>Please correct the following error(s) :</p>
                        <ul>
                            <?php foreach ($errors['image'] as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="user/manage_picture_profile" method="post" enctype='multipart/form-data'>
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <label for="formFile" class="text-light form-label fw-bold ">Select Image *</label>
                    <input class="form-control text-light border border-secondary" name="image" type="file" id="formFile">
                    <p class="text-secondary fw-bold mt-1">Supported formats: JPG, PNG, GIF, WebP. This will replace your current profile picture.</p>
                    <button type="submit" class="btn btn-primary">Upload Picture</button>
                </form>               
            </div>
        </div>          
    </div>

    <div class="card bg-app-card-header mb-4 ">
        <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
            Remove Current Picture
        </div>
        <div class="bg-app-card p-3">
            <div class="alert alert-warning" role="alert">
                This will permanently delete your current profile picture. You can upload a new one anytime.
            </div>
            <form action="user/manage_picture_profile_delete" method="post">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="btn btn-danger">Delete Picture</button>
            </form>
        </div>
    </div>
</div>


<?php Functions::footer(); ?>