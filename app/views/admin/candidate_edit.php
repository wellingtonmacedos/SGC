<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Candidato</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="index.php?r=admin/candidates" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" action="index.php?r=admin/edit-candidate&id=<?php echo $candidate['id']; ?>" enctype="multipart/form-data">
                    <h5 class="card-title mb-4">Dados Pessoais</h5>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required value="<?php echo e($candidate['name']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuário (Login) <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required value="<?php echo e($candidate['username'] ?? ''); ?>">
                            <div class="form-text">Sem espaços</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CPF <span class="text-danger">*</span></label>
                            <input type="text" name="cpf" class="form-control" required value="<?php echo e($candidate['cpf']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required value="<?php echo e($candidate['email']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo e($candidate['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Endereço <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" required rows="2"><?php echo e($candidate['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto de Perfil</label>
                        <div class="d-flex align-items-center mb-2">
                            <?php if (!empty($candidate['photo'])): ?>
                                <img src="index.php?r=file/photo&file=<?php echo e($candidate['photo']); ?>" alt="Foto Atual" class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover;">
                            <?php endif; ?>
                            <input type="file" name="photo" id="photoInput" class="form-control" accept="image/jpeg,image/png">
                        </div>
                        <div class="form-text">Opcional. Apenas JPG ou PNG. Máx 2MB.</div>
                        <div class="mt-2 d-none" id="photoPreviewContainer">
                            <img id="photoPreview" src="" alt="Prévia" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="card-title mb-3">Segurança</h5>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Deixe o campo de senha em branco se não desejar alterá-la.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nova Senha</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Preencha apenas para alterar">
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="force_password_change" id="forcePasswordChange" value="1">
                                <label class="form-check-label" for="forcePasswordChange">
                                    Forçar alteração de senha no próximo login
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('photoInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('photoPreview');
    const container = document.getElementById('photoPreviewContainer');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    } else {
        preview.src = '';
        container.classList.add('d-none');
    }
});
</script>