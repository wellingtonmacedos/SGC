<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4 text-primary"><i class="fas fa-list me-2"></i>Minhas Inscrições</h2>
    </div>
</div>

<?php if (empty($enrollments)): ?>
    <div class="alert alert-info py-4 text-center">
        <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
        Você ainda não se inscreveu em nenhum curso. 
        <br>
        <a href="index.php?r=candidate/dashboard" class="btn btn-primary mt-3">Ver Cursos Disponíveis</a>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Curso</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?php echo e($enrollment['course_name']); ?></div>
                                <small class="text-muted"><i class="fas fa-chalkboard-teacher me-1"></i> <?php echo e($enrollment['instructor']); ?></small>
                            </td>
                            <td>
                                <?php
                                $status = $enrollment['status'];
                                $badgeClass = 'bg-secondary';
                                $statusText = 'Inscrito';
                                
                                if ($status === 'certificate_available') {
                                    $badgeClass = 'bg-success';
                                    $statusText = 'Concluído';
                                } elseif ($status === 'completed') {
                                    $badgeClass = 'bg-info';
                                    $statusText = 'Finalizado';
                                } elseif ($status === 'active') {
                                    $badgeClass = 'bg-primary';
                                    $statusText = 'Em andamento';
                                }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2"><?php echo $statusText; ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php?r=candidate/courseDetails&id=<?php echo $enrollment['course_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> Detalhes
                                    </a>
                                    
                                    <?php if ($status === 'certificate_available'): ?>
                                        <?php 
                                        $certId = 0;
                                        foreach ($certificates as $cert) {
                                            if ((int)$cert['course_id'] === (int)$enrollment['course_id']) {
                                                $certId = $cert['id'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <?php if ($certId > 0): ?>
                                            <a href="index.php?r=candidate/downloadCertificate&id=<?php echo $certId; ?>" class="btn btn-sm btn-success text-white" target="_blank">
                                                <i class="fas fa-download me-1"></i> Certificado
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
