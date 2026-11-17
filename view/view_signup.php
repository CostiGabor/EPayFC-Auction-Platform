<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <base href="<?= $web_root ?>">
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="font/bootstrap-icons.css">
</head>
<body class="bg-app">
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card bg-app-card" style="width: 22rem;">  
            <div class="card-body mb-2">
                <h3 class="text-light text-center border-bottom border-dark-subtle pb-3 mb-3">Sign Up</h3>
                <form id="signupform" action="user/signup" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <!-- Email -->
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-icon-form border border-secondary" id="basic-addon1">
                            <i class="bi bi-envelope text-light"></i>
                        </span>
                        <input id="email" name="email" type="text" 
                               class="form-control border <?= !empty($errors['email']) ? 'border-danger' : 'border-secondary' ?>" 
                               placeholder="Email" aria-label="Email" aria-describedby="basic-addon1" 
                               value="<?= Tools::sanitize($old['email'] ?? '') ?>">
                    </div>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="text-danger mb-2">
                            <?php foreach ($errors['email'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Full Name -->
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-icon-form border border-secondary" id="basic-addon1">
                            <i class="bi bi-person text-light"></i>
                        </span>
                        <input id="full_name" name="full_name" type="text" 
                               class="form-control border <?= !empty($errors['full_name']) ? 'border-danger' : 'border-secondary' ?>" 
                               placeholder="Full Name" aria-label="Full_name" aria-describedby="basic-addon1" 
                               value="<?= Tools::sanitize($old['full_name'] ?? '') ?>">
                    </div>
                    <?php if (!empty($errors['full_name'])): ?>
                        <div class="text-danger mb-2">
                            <?php foreach ($errors['full_name'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Pseudo -->
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-icon-form border border-secondary" id="basic-addon1">
                            <i class="bi bi-person-circle text-light"></i>
                        </span>
                        <input id="pseudo" name="pseudo" type="text" 
                               class="form-control border <?= !empty($errors['pseudo']) ? 'border-danger' : 'border-secondary' ?>" 
                               placeholder="Pseudo" aria-label="Pseudo" aria-describedby="basic-addon1" 
                               value="<?= Tools::sanitize($old['pseudo'] ?? '') ?>">
                    </div>
                    <?php if (!empty($errors['pseudo'])): ?>
                        <div class="text-danger mb-2">
                            <?php foreach ($errors['pseudo'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Password -->
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-icon-form border border-secondary" id="basic-addon1">
                            <i class="bi bi-key text-light"></i>
                        </span>
                        <input id="password" name="password" type="password" 
                               class="form-control border <?= !empty($errors['password']) ? 'border-danger' : 'border-secondary' ?>" 
                               placeholder="Password" aria-label="Password" aria-describedby="basic-addon1">
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="text-danger mb-2">
                            <?php foreach ($errors['password'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Password Confirm -->
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-icon-form border border-secondary" id="basic-addon1">
                            <i class="bi bi-key text-light"></i>
                        </span>
                        <input id="password_confirm" name="password_confirm" type="password" 
                               class="form-control border <?= !empty($errors['password_confirm']) ? 'border-danger' : 'border-secondary' ?>" 
                               placeholder="Confirm your password" aria-label="Password_confirm" aria-describedby="basic-addon1">
                    </div>
                    <?php if (!empty($errors['password_confirm'])): ?>
                        <div class="text-danger mb-2">
                            <?php foreach ($errors['password_confirm'] as $error): ?>
                                <div><?= $error ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary w-100 mb-2">Sign Up</button>
                    <a href="user/login" class="btn btn-outline-danger w-100 mb-3 me-1">Cancel</a>
                </form>
            </div>
        </div>        
    </div>
</body>
</html>
