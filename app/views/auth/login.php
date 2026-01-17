<?php ?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <h1 class="h4 mb-3 text-center">Acesso ao SGC</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?r=auth/login">
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
        <div class="mt-3 text-center">
            <a href="index.php?r=auth/register">Ainda não tenho cadastro</a>
        </div>
        <div class="mt-1 text-center">
            <a href="index.php?r=auth/forgot">Esqueci minha senha</a>
        </div>
    </div>
</div>
