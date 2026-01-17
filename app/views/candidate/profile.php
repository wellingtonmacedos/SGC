<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 text-primary"><i class="fas fa-user-edit me-2"></i> Editar Perfil</h5>
            </div>
            <div class="card-body p-4">
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo e($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-check-circle me-2"></i> <?php echo e($success); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="index.php?r=candidate/profile">
                    <div class="mb-3">
                        <label for="name" class="form-label text-muted small text-uppercase fw-bold">Nome Completo</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo e($user['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label text-muted small text-uppercase fw-bold">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo e($user['email']); ?>" required>
                    </div>

                    <hr class="my-4 text-muted opacity-25">
                    <h6 class="mb-3 text-secondary">Alterar Senha <small class="fw-normal text-muted ms-2">(Opcional)</small></h6>

                    <div class="mb-3">
                        <label for="password" class="form-label text-muted small text-uppercase fw-bold">Nova Senha</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Deixe em branco para manter a atual">
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label text-muted small text-uppercase fw-bold">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repita a nova senha">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Salvar Alterações
                        </button>
                        <a href="index.php?r=candidate/dashboard" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Voltar ao Painel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
