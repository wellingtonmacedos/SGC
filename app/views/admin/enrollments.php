<?php
// Calculate summary stats if enrollments exist
$totalEnrolled = count($enrollments);
$completedCount = 0;
$pendingCount = 0;
foreach ($enrollments as $enr) {
    if ($enr['status'] === 'completed' || $enr['status'] === 'certificate_available') {
        $completedCount++;
    } else {
        $pendingCount++;
    }
}
?>
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold"><i class="fas fa-clipboard-check me-2 text-primary"></i>Gestão de Inscrições</h2>
            <p class="text-muted small mb-0">Gerencie os alunos matriculados em cada curso.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo e($_GET['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo e($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Enrollment Actions -->
    <div class="row g-3 mb-4">
        <!-- Filter Card -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <form method="get" class="row g-3 align-items-end">
                        <input type="hidden" name="r" value="admin/enrollments">
                        <div class="col-md-8">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Selecione o Curso</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-book text-muted"></i></span>
                                <select name="course_id" class="form-select border-start-0 ps-0" required onchange="this.form.submit()">
                                    <option value="">Escolha um curso para visualizar...</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo (int)$course['id']; ?>" <?php echo $selectedCourseId === (int)$course['id'] ? 'selected' : ''; ?>>
                                            <?php echo e($course['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100 fw-bold" type="submit">
                                <i class="fas fa-search me-2"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- New Enrollment Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-user-plus me-2 text-success"></i>Nova Inscrição</h6>
                    <button type="button" class="btn btn-success w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#newEnrollmentModal">
                        <i class="fas fa-plus-circle me-2"></i> Inscrever Candidato
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Enrollment Modal -->
    <div class="modal fade" id="newEnrollmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Nova Inscrição</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="index.php?r=admin/enrollments&action=create">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Candidato</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Selecione o candidato...</option>
                                <?php foreach ($candidates as $candidate): ?>
                                    <option value="<?php echo $candidate['id']; ?>">
                                        <?php echo e($candidate['name']); ?> (<?php echo e($candidate['cpf']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Curso</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">Selecione o curso...</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo $selectedCourseId === (int)$course['id'] ? 'selected' : ''; ?>>
                                        <?php echo e($course['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold">Confirmar Inscrição</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($selectedCourseId): ?>
        <?php if (empty($enrollments)): ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <div class="avatar-circle bg-light text-muted mx-auto" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="fas fa-user-slash fa-2x"></i>
                    </div>
                </div>
                <h4 class="text-muted">Nenhuma inscrição encontrada</h4>
                <p class="text-muted small">Este curso ainda não possui alunos matriculados.</p>
            </div>
        <?php else: ?>
            <!-- Stats Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="small text-muted fw-bold text-uppercase">Total Inscritos</div>
                                <div class="h3 mb-0 fw-bold text-dark"><?php echo $totalEnrolled; ?></div>
                            </div>
                            <div class="text-primary opacity-50"><i class="fas fa-users fa-2x"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="small text-muted fw-bold text-uppercase">Concluídos</div>
                                <div class="h3 mb-0 fw-bold text-dark"><?php echo $completedCount; ?></div>
                            </div>
                            <div class="text-success opacity-50"><i class="fas fa-check-circle fa-2x"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="small text-muted fw-bold text-uppercase">Em Andamento</div>
                                <div class="h3 mb-0 fw-bold text-dark"><?php echo $pendingCount; ?></div>
                            </div>
                            <div class="text-warning opacity-50"><i class="fas fa-clock fa-2x"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Lista de Alunos</h5>
                    <div class="btn-group">
                        <a href="index.php?r=admin/enrollments&course_id=<?php echo $selectedCourseId; ?>&export=csv" class="btn btn-outline-success btn-sm fw-bold">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </a>
                        <a href="index.php?r=admin/enrollments&course_id=<?php echo $selectedCourseId; ?>&export=doc" class="btn btn-outline-primary btn-sm fw-bold">
                            <i class="fas fa-file-word me-1"></i> Word
                        </a>
                        <a href="index.php?r=admin/enrollments&course_id=<?php echo $selectedCourseId; ?>&export=pdf" target="_blank" class="btn btn-outline-danger btn-sm fw-bold">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4">Candidato</th>
                                <th>Contato</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initials bg-primary bg-opacity-10 text-primary rounded-circle me-3 fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($enrollment['user_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo e($enrollment['user_name']); ?></div>
                                            <div class="small text-muted">Inscrito em: <?php echo date('d/m/Y', strtotime($enrollment['created_at'] ?? 'now')); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark"><i class="far fa-envelope me-1 text-muted width-20"></i> <?php echo e($enrollment['email']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $status = $enrollment['status'];
                                    $badgeClass = 'bg-secondary';
                                    $statusLabel = 'Inscrito';
                                    $icon = 'fa-user-clock';

                                    if ($status === 'certificate_available') {
                                        $badgeClass = 'bg-success';
                                        $statusLabel = 'Certificado Disp.';
                                        $icon = 'fa-certificate';
                                    } elseif ($status === 'completed') {
                                        $badgeClass = 'bg-info';
                                        $statusLabel = 'Concluído';
                                        $icon = 'fa-check';
                                    } elseif ($status === 'cancelled') {
                                        $badgeClass = 'bg-danger';
                                        $statusLabel = 'Cancelado';
                                        $icon = 'fa-times';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2">
                                        <i class="fas <?php echo $icon; ?> me-1"></i> <?php echo $statusLabel; ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="index.php?r=admin/enrollments&delete=<?php echo $enrollment['id']; ?>&course_id=<?php echo $selectedCourseId; ?>" 
                                       class="btn btn-sm btn-light text-danger" 
                                       onclick="return confirm('Tem certeza que deseja cancelar esta inscrição? O aluno será removido do curso.');"
                                       title="Cancelar Inscrição">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="mb-4 text-muted opacity-25">
                <i class="fas fa-graduation-cap fa-5x"></i>
            </div>
            <h3 class="text-muted fw-bold">Nenhum curso selecionado</h3>
            <p class="text-muted mb-4">Selecione um curso acima para gerenciar as inscrições e acompanhar o progresso dos alunos.</p>
            <div class="d-inline-block p-3 bg-white rounded shadow-sm border">
                <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Dica: Você pode exportar relatórios em PDF ou Excel após selecionar um curso.</small>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.width-20 { width: 20px; text-align: center; }
.avatar-initials { font-size: 1.1rem; }
</style>
