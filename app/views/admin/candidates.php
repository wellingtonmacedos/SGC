<?php ?>
<h1 class="h5 mb-3">Candidatos</h1>
<?php if (empty($candidates)): ?>
    <p class="text-muted">Nenhum candidato cadastrado.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
            <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>E-mail</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($candidates as $candidate): ?>
                <tr>
                    <td><?php echo e($candidate['name']); ?></td>
                    <td><?php echo e($candidate['cpf']); ?></td>
                    <td><?php echo e($candidate['email']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
