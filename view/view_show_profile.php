<?php Functions::header("Profile", "bi-person-fill"); ?>
<div class="p-4 pb-0 text-white-50">
    <div class="bg-app-card text-center rounded p-3 mb-4 border-profile-card">
        <?php if($user->hasPicture()){ ?>
            <img class="rounded-circle img-thumbnail img-thumbnail-regular mb-3" src="<?= Tools::sanitize($user->getPicturePath(true)); ?>"  alt="">
        <?php }else{ ?>
            <i class="bi bi-person-circle" style="font-size:80px;"></i>
        <?php }?>
        <h2 class="text-light "><?= Tools::sanitize($user->getFullName()) ?></h2>
        <span class="d-block">@<?= Tools::sanitize($user->getPseudo()) ?></span>
        <span class="d-block mb-3"><?= Tools::sanitize($user->getEmail()) ?></span>
    </div>

    <div class="card bg-app-card-header mb-4 ">
        <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
            My Activities
        </div>
        <div class="bg-app-card">
            <a href="item/sales_stats" class="text-decoration-none text-app-title" >
                <div class="m-3 rounded border-app-primary py-2 ps-3 mb-0 d-flex">
                    <i class="bi bi-receipt fs-4 mt-2 me-3 mt-1"></i>
                    <div class="d-flex flex-column fw-bold">
                        <span>Sales</span>
                        <small class="text-white-50">Items you have sold</small>
                    </div>            
                </div>
            </a>
        </div>  
        <div class="bg-app-card text-app-title pb-3">
            <a href="item/purchases" class="text-decoration-none text-app-title" >
                <div class="m-3 rounded border-app-primary py-2 ps-3 mb-0 d-flex">
                    <i class="bi bi-cart fs-4 me-3 mt-1"></i>
                    <div class="d-flex flex-column fw-bold">
                        <span>Purchases</span>
                        <small class="text-white-50">Items you have purchased</small>
                    </div>    
                </div>
            </a>
        </div>  
    </div>

    <div class="card bg-app-card-header mb-4 ">
        <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
            Account Settings
        </div>
        <div class="bg-app-card">
            <a href="user/edit_profile" class="text-decoration-none text-app-title" >
                <div class="m-3 rounded border-app-primary py-2 ps-3 mb-0 d-flex">
                    <i class="bi bi-person-gear fs-4 mt-2 me-3 mt-1"></i>
                    <div class="d-flex flex-column fw-bold">
                        <span>Edit Profile</span>
                        <small class="text-white-50">Update your personal information</small>
                    </div>            
                </div>
            </a>
        </div>  
        <div class="bg-app-card text-app-title">
            <a href="user/change_password" class="text-decoration-none text-app-title" >
                <div class="m-3 rounded border-app-primary py-2 ps-3 mb-0 d-flex">
                    <i class="bi bi-key fs-4 me-3 mt-1"></i>
                    <div class="d-flex flex-column fw-bold">
                        <span>Change Password</span>
                        <small class="text-white-50">Update your account security</small>
                    </div>    
                </div>
            </a>
        </div>  
         <div class="bg-app-card text-app-title">
            <a href="user/manage_picture_profile" class="text-decoration-none text-app-title" >
                <div class="m-3 rounded border-app-primary py-2 ps-3 mb-0 d-flex">
                    <i class="bi bi-person-circle fs-4 me-3 mt-1"></i>
                    <div class="d-flex flex-column fw-bold">
                        <span>Profile Picture</span>
                        <small class="text-white-50">Upload or change your profile picture</small>
                    </div>    
                </div>
            </a>
        </div>  
        <div class="bg-app-card  pb-3">
            <a href="user/user_logout" class="text-decoration-none text-danger logout" >
                <div class="m-3 rounded border border-danger py-2 ps-3 mb-0 d-flex">
                    <i class="bi bi-box-arrow-right fs-4 me-3 mt-1"></i>
                    <div class="d-flex flex-column fw-bold">
                        <span>Logout</span>
                        <small class="text-white-50">Sign out of your account</small>
                    </div>    
                </div>
            </a>
        </div>  
    </div>
</div>


<?php Functions::footer(); ?>