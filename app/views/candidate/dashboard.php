<?php
// Calculate stats for the cards
$totalEnrollments = count($enrollments);
$openEnrollments = 0;
$closedEnrollments = 0;

foreach ($enrollments as $enr) {
    if ($enr['status'] === 'completed' || $enr['status'] === 'certificate_available') {
        $closedEnrollments++;
    } else {
        $openEnrollments++;
    }
}
?>

<!-- Stats Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="stat-card stat-card-blue d-flex align-items-center justify-content-between">
            <div>
                <h3 class="mb-0 fw-bold"><?php echo $totalEnrollments; ?></h3>
                <div class="fw-medium">Cursos Cadastrados</div>
            </div>
            <div class="icon"><i class="fas fa-clipboard-list"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-green d-flex align-items-center justify-content-between">
            <div>
                <h3 class="mb-0 fw-bold"><?php echo $openEnrollments; ?></h3>
                <div class="fw-medium">Inscrições Abertas</div>
            </div>
            <div class="icon"><i class="fas fa-clipboard"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-red d-flex align-items-center justify-content-between">
            <div>
                <h3 class="mb-0 fw-bold"><?php echo $closedEnrollments; ?></h3>
                <div class="fw-medium">Inscrições Fechadas</div>
            </div>
            <div class="icon"><i class="fas fa-clipboard-check"></i></div>
        </div>
    </div>
</div>

<!-- My Enrollments -->
<?php if (!empty($enrollments)): ?>
    <div class="card border-0 shadow-sm mb-5" id="enrollments">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Minhas Inscrições</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Curso</th>
                            <th>Data Inscrição</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo e($enrollment['course_name']); ?></div>
                                    <div class="small text-muted"><?php echo e($enrollment['course_location'] ?? ''); ?></div>
                                </td>
                                <td><?php echo formatDateBr($enrollment['created_at']); ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    $statusLabel = $enrollment['status'];
                                    
                                    switch ($enrollment['status']) {
                                        case 'pending':
                                            $statusClass = 'bg-warning text-dark';
                                            $statusLabel = 'Pendente';
                                            break;
                                        case 'confirmed':
                                            $statusClass = 'bg-primary';
                                            $statusLabel = 'Confirmado';
                                            break;
                                        case 'completed':
                                            $statusClass = 'bg-success';
                                            $statusLabel = 'Concluído';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'bg-danger';
                                            $statusLabel = 'Cancelado';
                                            break;
                                        case 'certificate_available':
                                            $statusClass = 'bg-success';
                                            $statusLabel = 'Certificado Disponível';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?> rounded-pill">
                                        <?php echo e($statusLabel); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="index.php?r=candidate/courseDetails&id=<?php echo $enrollment['course_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- My Certificates -->
<?php if (!empty($certificates)): ?>
    <div class="card border-0 shadow-sm mb-5" id="certificates">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-certificate me-2"></i>Meus Certificados</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Curso</th>
                            <th>Data Emissão</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $cert): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo e($cert['course_name'] ?? 'Curso não identificado'); ?></div>
                                    <div class="small text-muted">Código: <?php echo e($cert['validation_code']); ?></div>
                                </td>
                                <td><?php echo formatDateBr($cert['issued_at']); ?></td>
                                <td class="text-end pe-4">
                                    <a href="index.php?r=candidate/downloadCertificate&id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                        <i class="fas fa-download me-1"></i> Baixar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Available Courses Section -->
<div class="mb-5">
    <h4 class="text-center mb-4 fw-bold text-dark">Cursos e Palestras Disponíveis</h4>
    
    <!-- Search Bar -->
    <form method="get" class="mb-4">
        <input type="hidden" name="r" value="candidate/dashboard">
        <div class="input-group input-group-lg shadow-sm">
            <span class="input-group-text bg-white border-end-0 ps-4"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="q" class="form-control border-start-0" 
                   placeholder="Buscar Curso" 
                   value="<?php echo isset($filterSearch) ? e($filterSearch) : ''; ?>">
            <!-- Optional Date Filter (Hidden or Small) -->
             <?php if (!empty($filterDate)): ?>
                <input type="hidden" name="date" value="<?php echo e($filterDate); ?>">
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($availableCourses)): ?>
        <div class="alert alert-info text-center py-4">
            <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
            Nenhum curso disponível para inscrição no momento.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($availableCourses as $course): ?>
                <div class="col-md-4 d-flex align-items-stretch">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition w-100" style="border-radius: 15px; overflow: hidden;">
                        <?php if (!empty($course['cover_image'])): ?>
                            <div style="height: 200px; overflow: hidden;">
                                <img src="index.php?r=file/cover&file=<?php echo urlencode($course['cover_image']); ?>" class="card-img-top" alt="Capa do curso" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                            </div>
                        <?php else: ?>
                            <div class="card-img-top text-white d-flex align-items-center justify-content-center" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="text-center p-3">
                                    <i class="fas fa-graduation-cap fa-3x mb-3 opacity-75"></i>
                                    <h5 class="fw-bold text-truncate" style="max-width: 250px;"><?php echo e($course['name']); ?></h5>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold mb-3 text-dark"><?php echo e($course['name']); ?></h5>
                            
                            <div class="mb-4">
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="fas fa-chalkboard-teacher me-2 text-primary width-20"></i>
                                    <span class="fw-medium"><?php echo e($course['instructor']); ?></span>
                                </div>
                                <?php if (!empty($course['date'])): ?>
                                    <div class="d-flex align-items-center text-muted small mb-2">
                                        <i class="far fa-calendar-alt me-2 text-primary width-20"></i>
                                        <span><?php echo formatDateBr($course['date']); ?><?php echo !empty($course['time']) ? ' às ' . formatTimeBr($course['time']) : ''; ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($course['location'])): ?>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="fas fa-map-marker-alt me-2 text-primary width-20"></i>
                                        <span><?php echo e($course['location']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto">
                                <?php 
                                    $maxEnrollments = (int)($course['max_enrollments'] ?? 0);
                                    // Use a safer check for remaining spots if available in the array, otherwise rely on controller logic
                                    // For visual feedback only (controller does the real check)
                                ?>
                                
                                <?php if ($maxEnrollments > 0): ?>
                                    <div class="mb-3">
                                        <span class="badge bg-light text-dark border"><i class="fas fa-users me-1"></i> Vagas Limitadas</span>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="fas fa-infinity me-1"></i> Vagas Ilimitadas</span>
                                    </div>
                                <?php endif; ?>

                                <div class="d-grid gap-2">
                                    <a href="index.php?r=candidate/courseDetails&id=<?php echo $course['id']; ?>" class="btn btn-outline-primary fw-bold py-2 shadow-sm btn-hover-effect">
                                        <i class="fas fa-info-circle me-2"></i> Ver Detalhes
                                    </a>
                                    <form method="post" action="index.php?r=candidate/dashboard" class="d-grid">
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                        <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm btn-hover-effect">
                                            <i class="fas fa-check-circle me-2"></i> Inscrever-se
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.width-20 { width: 20px; text-align: center; }
.hover-shadow:hover { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; transform: translateY(-2px); }
.transition { transition: all 0.3s ease; }
</style>
