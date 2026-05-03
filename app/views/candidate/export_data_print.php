<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Exportação de Dados</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="window.print()">Imprimir / Salvar em PDF</button>
            <a class="btn btn-outline-secondary" href="index.php?r=candidate/profile">Voltar</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Cadastro</h5>
            <div class="row g-2">
                <div class="col-md-6"><strong>Nome:</strong> <?php echo e($user['name'] ?? ''); ?></div>
                <div class="col-md-6"><strong>E-mail:</strong> <?php echo e($user['email'] ?? ''); ?></div>
                <div class="col-md-6"><strong>Usuário:</strong> <?php echo e($user['username'] ?? ''); ?></div>
                <div class="col-md-6"><strong>CPF:</strong> <?php echo e($user['cpf'] ?? ''); ?></div>
                <div class="col-md-6"><strong>Telefone:</strong> <?php echo e($user['phone'] ?? ''); ?></div>
                <div class="col-md-6"><strong>Data de Nascimento:</strong> <?php echo e($user['birth_date'] ?? ''); ?></div>
                <div class="col-12"><strong>Endereço:</strong> <?php echo e($user['address'] ?? ''); ?></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Consentimento</h5>
            <div class="row g-2">
                <div class="col-md-4"><strong>LGPD:</strong> <?php echo (int)($user['lgpd_consent'] ?? 0) === 1 ? 'Sim' : 'Não'; ?></div>
                <div class="col-md-4"><strong>Aceito em:</strong> <?php echo e($user['lgpd_consent_at'] ?? ''); ?></div>
                <div class="col-md-4"><strong>IP:</strong> <?php echo e($user['lgpd_consent_ip'] ?? ''); ?></div>
                <div class="col-md-4"><strong>Versão:</strong> <?php echo e($user['privacy_policy_version'] ?? ''); ?></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-3">Inscrições</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Certificado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $row): ?>
                            <tr>
                                <td><?php echo e($row['course_name'] ?? ''); ?></td>
                                <td><?php echo e($row['status'] ?? ''); ?></td>
                                <td><?php echo e($row['created_at'] ?? ''); ?></td>
                                <td><?php echo !empty($row['has_certificate']) ? 'Sim' : 'Não'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Certificados</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Arquivo</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $row): ?>
                            <tr>
                                <td><?php echo e($row['course_name'] ?? ''); ?></td>
                                <td><?php echo e($row['original_name'] ?? ''); ?></td>
                                <td><?php echo e($row['created_at'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

