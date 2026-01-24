<?php
$title = 'Novo Candidato - Admin';
?>
<div class="container-fluid p-0">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="d-flex align-items-center mb-4">
                <a href="index.php?r=admin/candidates" class="btn btn-outline-secondary me-3 shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="h3 mb-0 text-gray-800 fw-bold">Novo Candidato</h2>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-user-plus me-2"></i>Dados do Candidato</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="index.php?r=admin/create-candidate" enctype="multipart/form-data" class="needs-validation">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required minlength="3" value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">CPF <span class="text-danger">*</span></label>
                                <input type="text" name="cpf" class="form-control" required placeholder="000.000.000-00" value="<?php echo isset($_POST['cpf']) ? e($_POST['cpf']) : ''; ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Usuário <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">@</span>
                                    <input type="text" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary small text-uppercase">E-mail <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Senha <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Confirmar Senha <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Telefone</label>
                                <input type="text" name="phone" class="form-control" placeholder="(00) 00000-0000" value="<?php echo isset($_POST['phone']) ? e($_POST['phone']) : ''; ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Foto</label>
                                <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png">
                                <div class="form-text small">JPG ou PNG, máx 2MB.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Endereço <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" required><?php echo isset($_POST['address']) ? e($_POST['address']) : ''; ?></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="index.php?r=admin/candidates" class="btn btn-light me-md-2">Cancelar</a>
                                    <button type="submit" class="btn btn-primary fw-bold px-4">
                                        <i class="fas fa-save me-2"></i> Criar Candidato
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
