<?php Functions::header("Change Password", "bi-key",$back_url); ?>
<div class="p-4 pb-0 text-white-50">
 
    <form action="user/change_password" method="post">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div class="card bg-app-card-header mb-4 ">
            <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
                    Current Password
            </div>
            <div class="bg-app-card">
                <div class="mb-3 p-3">
                    <label for="currentPassword" class="text-light form-label fw-bold ">Current Password *</label>
                    <input type="password" id="currentPassword" name="current_password" class="form-control text-light border border-secondary <?= !empty($errors['current_password'])? "border border-danger": "" ?>">
                    <?php if (!empty($errors['current_password'])): ?>
                        <div class='errors text-danger'>
                            <ul>
                            <?php foreach ($errors['current_password'] as $error): ?>
                                <li class="text-danger list-unstyled"><?= $error ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <span class="text-secondary fw-semibold">Enter your current password to verify your identity.</span>
                </div>
            </div>     
        </div>
        

        <div class="card bg-app-card-header mb-4">
            <div class="card-header text-light fs-5 fw-semibold border-bottom-app-color">
                New Password
            </div>
            <div class="bg-app-card">
                <div class="p-3">
                    <label for="newPassword" class="text-light form-label fw-bold ">New Password *</label>
                    <input type="password" id="newPassword" name="new_password"  class="form-control text-light border border-secondary <?= !empty($errors['new_password']) ? "border border-danger": "" ?>" >
                    <?php if (!empty($errors['new_password'])): ?>
                        <div class='errors'>
                            <ul>
                            <?php foreach ($errors['new_password'] as $error): ?>
                                <li class="text-danger list-unstyled"><?= $error ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <span class="text-secondary fw-semibold">Password must be 8-16 with uppercase, number, and punctuation</span>
                </div>
            </div>     
            <div class="bg-app-card">
                <div class="mb-3 px-3">
                    <label for="confirm_password" class="text-light form-label fw-bold ">Current New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control text-light border border-secondary <?= !empty($errors['confirm_password']) ? "border border-danger": "" ?>">
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <div class='errors'>
                            <ul>
                            <?php foreach ($errors['confirm_password'] as $error): ?>
                                <li class="text-danger list-unstyled"><?= $error ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <span class="text-secondary fw-semibold">Re-enter your new password to confirm it matches</span>
                </div>
            </div>     
        </div>
        <button type="submit" class="position-fixed top-0 end-0 z-3 border-0 bg-transparent fs-4 me-4 text-app-title"><i class="bi bi-floppy-fill"></i></button>

    </form>
</div>

<?php Functions::footer(); ?>
