<?php ?>
<h1 class="h5 mb-3">Candidatos</h1>
<?php if (empty($candidates)): ?>
    <p class="text-muted">Nenhum candidato cadastrado.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
            <thead>
            <tr>
                <th>Foto</th>
                <th>Nome</th>
                <th>Usuário</th>
                <th>CPF</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Endereço</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($candidates as $candidate): ?>
                <tr>
                    <td>
                        <?php if (!empty($candidate['photo'])): ?>
                            <img src="index.php?r=file/photo&file=<?php echo e($candidate['photo']); ?>" alt="Foto" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                        <?php else: ?>
                            <span class="badge bg-secondary rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user"></i>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($candidate['name']); ?></td>
                    <td><?php echo e($candidate['username'] ?? '-'); ?></td>
                    <td><?php echo e($candidate['cpf']); ?></td>
                    <td><?php echo e($candidate['email']); ?></td>
                    <td><?php echo e($candidate['phone'] ?? '-'); ?></td>
                    <td><small><?php echo e(mb_strimwidth($candidate['address'] ?? '-', 0, 30, '...')); ?></small></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
