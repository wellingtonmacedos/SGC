<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 text-dark">Gestão de Administradores</h2>
            <p class="text-muted">Gerencie os usuários com privilégios administrativos no sistema.</p>
        </div>
        <a href="index.php?r=superAdmin/createAdmin" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Novo Admin
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php if ($_GET['success'] === 'created'): ?>
                Administrador criado com sucesso!
            <?php elseif ($_GET['success'] === 'updated'): ?>
                Administrador atualizado com sucesso!
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nome / Email</th>
                            <th>Usuário</th>
                            <th>CPF</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 opacity-50"></i>
                                    <p>Nenhum administrador encontrado.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold me-3" style="width: 40px; height: 40px;">
                                                <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo e($admin['name']); ?></div>
                                                <div class="small text-muted"><?php echo e($admin['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            @<?php echo e($admin['username']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo e($admin['cpf']); ?></td>
                                    <td>
                                        <?php if (isset($admin['status']) && $admin['status'] === 'inactive'): ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo isset($admin['created_at']) ? formatDateBr($admin['created_at']) : '-'; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="index.php?r=superAdmin/editAdmin&id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?r=superAdmin/toggleAdminStatus&id=<?php echo $admin['id']; ?>" class="btn btn-sm <?php echo (isset($admin['status']) && $admin['status'] === 'inactive') ? 'btn-outline-success' : 'btn-outline-warning'; ?>" title="<?php echo (isset($admin['status']) && $admin['status'] === 'inactive') ? 'Ativar' : 'Desativar'; ?>">
                                                <i class="fas <?php echo (isset($admin['status']) && $admin['status'] === 'inactive') ? 'fa-check' : 'fa-ban'; ?>"></i>
                                            </a>
                                            <a href="index.php?r=superAdmin/deleteAdmin&id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este administrador? Esta ação não pode ser desfeita.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
