<?php ?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <h1 class="h4 mb-3 text-center">Recuperar senha</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">Se o e-mail estiver cadastrado, enviaremos as instruções.</div>
        <?php else: ?>
            <form method="post" action="index.php?r=auth/forgot">
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Enviar link de recuperação</button>
            </form>
        <?php endif; ?>
    </div>
</div>
