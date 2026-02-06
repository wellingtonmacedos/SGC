<?php
// Default values if settings not set
$bgImage = !empty($settings['login_background_image']) 
    ? 'storage/organization/' . $settings['login_background_image'] 
    : 'assets/img/login_bg.jpg';

$bgColor = $settings['login_background_color'] ?? '#0d1b2a';
$primaryColor = $settings['login_primary_color'] ?? '#0d1b2a';
$title = $settings['login_title'] ?? 'Bem-vindo ao SGC';
$subtitle = $settings['login_subtitle'] ?? 'Faça login para continuar';
$iconClass = $settings['login_icon'] ?? 'fas fa-graduation-cap';
$logo = $settings['login_logo'] ?? null;
?>
<style>
    body {
        /* Fallback color */
        background-color: <?php echo $bgColor; ?>;
        /* Background Image */
        background: url('<?php echo $bgImage; ?>') no-repeat center center fixed;
        background-size: cover;
    }
    /* Overlay to darken background slightly if needed */
    
    .login-container {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card-login {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 12px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        backdrop-filter: blur(5px);
    }
    .input-group-text {
        background-color: #fff;
        border-right: none;
    }
    .form-control {
        border-left: none;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control {
        border-color: <?php echo $primaryColor; ?>;
    }
    .btn-login {
        background-color: <?php echo $primaryColor; ?>;
        border-color: <?php echo $primaryColor; ?>;
        transition: all 0.3s;
    }
    .btn-login:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }
    .text-primary-custom {
        color: <?php echo $primaryColor; ?> !important;
    }
</style>
<link rel="stylesheet" href="public/css/login.css">

<div class="container login-container">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card card-login border-0 p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="owl-container">
                    <div class="owl">
                        <img src="assets/owl/owl-base.svg" class="owl-base" alt="Coruja">
                        <img src="assets/owl/owl-arms.svg" class="owl-arms" alt="">
                    </div>
                </div>
                <h1 class="h4 fw-bold text-dark mb-1"><?php echo e($title); ?></h1>
                <p class="text-muted small"><?php echo e($subtitle); ?></p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small border-0 bg-danger bg-opacity-10 text-danger text-center mb-4">
                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php?r=auth/login">
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold">E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text text-muted"><i class="fas fa-user"></i></span>
                        <input type="text" name="identifier" class="form-control" required autofocus placeholder="">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small text-muted fw-bold">Senha</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" required placeholder="">
                        <button type="button" id="togglePassword" aria-label="Mostrar senha" class="toggle-password">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-login w-100 py-2 fw-bold text-uppercase mb-3">
                    Entrar
                </button>
            </form>

            <div class="d-flex justify-content-between align-items-center mt-3 small">
                <a href="index.php?r=auth/register" class="text-decoration-none fw-bold text-dark">Ainda não tenho cadastro</a>
                <a href="index.php?r=auth/forgot" class="text-decoration-none text-muted">Esqueci a senha</a>
            </div>
        </div>
        
        <div class="text-center mt-4 text-white-50 small">
            &copy; <?php echo date('Y'); ?> SGC - Sistema de Gestão de Cursos
        </div>
    </div>
</div>
<script src="public/js/login.js" defer></script>