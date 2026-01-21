<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Alteração de Senha Obrigatória</h3>
                
                <div class="alert alert-warning">
                    <i class="fas fa-lock"></i> Por motivos de segurança, você precisa alterar sua senha para continuar.
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>

                <form method="post" action="index.php?r=candidate/change-password">
                    <div class="mb-3">
                        <label class="form-label">Nova Senha</label>
                        <input type="password" name="password" class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar Nova Senha</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Alterar Senha e Continuar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>