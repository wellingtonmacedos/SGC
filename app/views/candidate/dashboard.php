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
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                        <?php if (!empty($course['cover_image'])): ?>
                            <img src="storage/covers/<?php echo e($course['cover_image']); ?>" class="card-img-top" alt="Capa do curso" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-muted" style="height: 200px;">
                                <div class="text-center">
                                    <i class="fas fa-image fa-3x mb-2 opacity-50"></i>
                                    <div>Sem capa</div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold mb-3 text-dark"><?php echo e($course['name']); ?></h5>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="fas fa-chalkboard-teacher me-2 width-20"></i>
                                    <span><?php echo e($course['instructor']); ?></span>
                                </div>
                                <?php if (!empty($course['date'])): ?>
                                    <div class="d-flex align-items-center text-muted small mb-2">
                                        <i class="far fa-calendar-alt me-2 width-20"></i>
                                        <span><?php echo formatDateBr($course['date']); ?><?php echo !empty($course['time']) ? ' às ' . formatTimeBr($course['time']) : ''; ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($course['location'])): ?>
                                    <div class="d-flex align-items-center text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt me-2 width-20"></i>
                                        <span><?php echo e($course['location']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-2">
                                    <?php 
                                    $max = isset($course['max_enrollments']) ? (int)$course['max_enrollments'] : 0;
                                    $count = isset($course['enrollments_count']) ? (int)$course['enrollments_count'] : 0;
                                    
                                    if ($max > 0) {
                                        $percent = ($count / $max) * 100;
                                        $color = $percent >= 100 ? 'danger' : ($percent >= 80 ? 'warning' : 'success');
                                        
                                        if ($count >= $max) {
                                            echo "<span class='badge bg-danger w-100'><i class='fas fa-ban me-1'></i> Vagas Esgotadas ({$count}/{$max})</span>";
                                        } else {
                                            $remaining = $max - $count;
                                            echo "<span class='badge bg-{$color} text-wrap'><i class='fas fa-user-check me-1'></i> {$remaining} vagas restantes ({$count}/{$max})</span>";
                                        }
                                    } else {
                                        echo "<span class='badge bg-success'><i class='fas fa-infinity me-1'></i> Vagas Ilimitadas</span>";
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <p class="card-text text-secondary small flex-grow-1">
                                <?php echo e(mb_strimwidth($course['description'], 0, 100, '...')); ?>
                            </p>
                            
                            <form method="post" class="mt-3">
                                <input type="hidden" name="course_id" value="<?php echo (int)$course['id']; ?>">
                                <?php 
                                $max = isset($course['max_enrollments']) ? (int)$course['max_enrollments'] : 0;
                                $count = isset($course['enrollments_count']) ? (int)$course['enrollments_count'] : 0;
                                $isFull = ($max > 0 && $count >= $max);
                                ?>
                                <button type="submit" class="btn <?php echo $isFull ? 'btn-secondary' : 'btn-primary'; ?> w-100 py-2 fw-medium" <?php echo $isFull ? 'disabled' : ''; ?>>
                                    <?php if ($isFull): ?>
                                        <i class="fas fa-lock me-2"></i>Esgotado
                                    <?php else: ?>
                                        <i class="fas fa-plus-circle me-2"></i>Inscrever-se
                                    <?php endif; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- My Enrollments (Kept for functionality, but styled simpler) -->
<?php if (!empty($enrollments)): ?>
    <div class="card border-0 shadow-sm mb-5" id="enrollments">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Minhas Inscrições</h5>
        </div>
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
                                <div class="fw-medium"><?php echo e($enrollment['course_name']); ?></div>
                                <small class="text-muted"><?php echo e($enrollment['instructor']); ?></small>
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
                                <span class="badge <?php echo $badgeClass; ?> rounded-pill"><?php echo $statusText; ?></span>
                            </td>
                            <td class="text-end pe-4">
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
                                        <a href="index.php?r=candidate/downloadCertificate&id=<?php echo $certId; ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                            <i class="fas fa-download me-1"></i> Certificado
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
.width-20 { width: 20px; text-align: center; }
.hover-shadow:hover { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; transform: translateY(-2px); }
.transition { transition: all 0.3s ease; }
</style>
