<?php ?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <h1 class="h4 mb-3 text-center">Redefinir senha</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">Senha alterada com sucesso. Você já pode fazer login.</div>
        <?php else: ?>
            <form method="post" action="index.php?r=auth/reset&amp;token=<?php echo e($token); ?>">
                <div class="mb-3">
                    <label class="form-label">Nova senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar nova senha</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Salvar nova senha</button>
            </form>
        <?php endif; ?>
    </div>
</div>
