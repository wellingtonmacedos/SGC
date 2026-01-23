<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800">Logs do Sistema</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Últimos 100 Logs</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="logsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Descrição</th>
                        <th>IP</th>
                        <th>Data/Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Nenhum registro encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo e($log['id']); ?></td>
                                <td>
                                    <?php if ($log['user_name']): ?>
                                        <div class="fw-bold"><?php echo e($log['user_name']); ?></div>
                                        <div class="small text-muted"><?php echo e($log['user_email']); ?> (<?php echo e($log['user_role']); ?>)</div>
                                    <?php else: ?>
                                        <span class="text-muted">Sistema / Desconhecido</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo e($log['action']); ?></span></td>
                                <td><?php echo e($log['description']); ?></td>
                                <td><?php echo e($log['ip_address']); ?></td>
                                <td><?php echo formatDateBr($log['created_at']) . ' ' . date('H:i', strtotime($log['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
