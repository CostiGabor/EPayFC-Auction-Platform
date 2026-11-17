<?php Functions::header("Edit Profile", "bi-person-fill-gear",$back_url); ?>

<div class="p-4 pb-0 text-white-50">    
    <form action="user/edit_profile" method="post">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div class="card bg-app-card-header mb-4 ">
            <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
                    Personal Information
            </div>
            <div class="bg-app-card">
                <div class="p-3">              
                    <label class="text-light form-label fw-bold">Full Name *</label>
                    <input type="text" name="full_name" class="form-control text-light border <?= !empty($errors['full_name']) ? 'border-danger' : 'border-secondary' ?>" value="<?= Tools::sanitize($old['full_name'] ?? $user->getFullName()) ?>">

                    <?php if (!empty($errors['full_name'])): ?>
                        <div class="text-danger mt-1">
                            <?php foreach ($errors['full_name'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <span class="text-secondary fw-semibold">Your complete name as it should appear on your profile</span>
                </div>
                <div class="mb-3 px-3">
                    <label class="text-light form-label fw-bold">Username *</label>
                    <input type="text" name="pseudo" class="form-control text-light border <?= !empty($errors['pseudo']) ? 'border-danger' : 'border-secondary' ?>" value="<?= Tools::sanitize($old['pseudo'] ?? $user->getPseudo()) ?>">

                    <?php if (!empty($errors['pseudo'])): ?>
                        <div class="text-danger mt-1">
                            <?php foreach ($errors['pseudo'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <span class="text-secondary fw-semibold">This will be your public display name on the platform</span>
                </div>
            </div>     
        </div>
        

        <div class="card bg-app-card-header mb-4">
            <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
                Contact & Payment
            </div>
            <div class="bg-app-card">
                <div class="p-3">                    
                    <label class="text-light form-label fw-bold">Email Address *</label>
                    <input type="email" name="email" class="form-control text-light border <?= !empty($errors['email']) ? 'border-danger' : 'border-secondary' ?>" value="<?= Tools::sanitize($old['email'] ?? $user->getEmail()) ?>">

                    <?php if (!empty($errors['email'])): ?>
                        <div class="text-danger mt-1">
                            <?php foreach ($errors['email'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <span class="text-secondary fw-semibold">Your complete name as it should appear on your profile</span>
                </div>
                <div class="mb-3 px-3">
                    <label class="text-light form-label fw-bold">
                        IBAN <span class="text-secondary">(Optional)</span>
                    </label>
                    <input type="text" name="iban" class="form-control text-light border <?= !empty($errors['iban']) ? 'border-danger' : 'border-secondary' ?>" value="<?= Tools::sanitize($old['iban'] ?? '') ?>">

                    <?php if (!empty($errors['iban'])): ?>
                        <div class="text-danger mt-1">
                            <?php foreach ($errors['iban'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <span class="text-secondary fw-semibold">For receiving payments from your sales. Leave empty if you don't want to receive payments directly</span>
                </div>
            </div>     
        </div>
        <button type="submit" class="position-fixed top-0 end-0 z-3 border-0 bg-transparent fs-4 me-4 text-app-title"><i class="bi bi-floppy-fill"></i></button>
    </form>
</div>


<?php Functions::footer(); ?>