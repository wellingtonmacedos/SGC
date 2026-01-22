<div class="container-fluid p-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="index.php?r=superAdmin/admins" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2 class="h3 text-dark mb-0"><?php echo $action === 'create' ? 'Novo Administrador' : 'Editar Administrador'; ?></h2>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="index.php?r=superAdmin/<?php echo $action === 'create' ? 'createAdmin' : 'editAdmin&id=' . $admin['id']; ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3 text-muted border-bottom pb-2">Dados Pessoais</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? e($_POST['name']) : (isset($admin) ? e($admin['name']) : ''); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="cpf" class="form-label">CPF <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo isset($_POST['cpf']) ? e($_POST['cpf']) : (isset($admin) ? e($admin['cpf']) : ''); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? e($_POST['email']) : (isset($admin) ? e($admin['email']) : ''); ?>" required>
                            </div>

                            <div class="col-12 mt-4">
                                <h5 class="mb-3 text-muted border-bottom pb-2">Dados de Acesso</h5>
                            </div>

                            <div class="col-md-6">
                                <label for="username" class="form-label">Usuário de Login <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? e($_POST['username']) : (isset($admin) ? e($admin['username']) : ''); ?>" required>
                            </div>

                            <?php if ($action === 'edit'): ?>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo (isset($admin) && $admin['status'] === 'active') ? 'selected' : ''; ?>>Ativo</option>
                                        <option value="inactive" <?php echo (isset($admin) && $admin['status'] === 'inactive') ? 'selected' : ''; ?>>Inativo</option>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Senha <?php echo $action === 'edit' ? '<span class="text-muted fw-light">(Deixe em branco para manter)</span>' : '<span class="text-danger">*</span>'; ?></label>
                                <input type="password" class="form-control" id="password" name="password" <?php echo $action === 'create' ? 'required' : ''; ?>>
                            </div>

                            <div class="col-md-6">
                                <?php if ($action === 'create'): ?>
                                    <label for="confirm_password" class="form-label">Confirmar Senha <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <?php elseif ($action === 'edit'): ?>
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="force_change" name="force_change">
                                        <label class="form-check-label" for="force_change">
                                            Forçar alteração de senha no próximo login
                                        </label>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="index.php?r=superAdmin/admins" class="btn btn-light">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
