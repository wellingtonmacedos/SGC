<?php
// Helper to safely output json for js
function jsonSafe(array $data): string {
    return htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 text-gray-800">Gerenciar Cursos</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal" onclick="clearForm()">
        <i class="fas fa-plus me-2"></i>Novo Curso
    </button>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <input type="hidden" name="r" value="admin/courses">
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold">Filtrar por data</label>
                <input type="date" name="date" class="form-control" value="<?php echo isset($filterDate) ? e($filterDate) : ''; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold">Filtrar por local</label>
                <input type="text" name="location" class="form-control" placeholder="Ex: Auditório..." value="<?php echo isset($filterLocation) ? e($filterLocation) : ''; ?>">
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" type="submit">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="index.php?r=admin/courses" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Courses List -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Curso</th>
                        <th>Instrutor</th>
                        <th>Data/Local</th>
                        <th>Status</th>
                        <th class="text-center">Inscrições</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-book-open fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">Nenhum curso encontrado.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?php echo e($course['name']); ?></div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;"><?php echo e($course['description']); ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle-sm bg-light text-primary me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <span><?php echo e($course['instructor']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="mb-1"><i class="far fa-calendar-alt me-1 text-muted"></i> <?php echo isset($course['date']) && $course['date'] ? formatDateBr($course['date']) : 'A definir'; ?></div>
                                    <div><i class="fas fa-map-marker-alt me-1 text-muted"></i> <?php echo isset($course['location']) && $course['location'] ? e($course['location']) : 'Online/A definir'; ?></div>
                                </div>
                            </td>
                            <td>
                                <?php if ($course['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                $max = isset($course['max_enrollments']) ? (int)$course['max_enrollments'] : 0;
                                $count = isset($course['enrollments_count']) ? (int)$course['enrollments_count'] : 0;
                                
                                if ($max > 0) {
                                    $percent = ($count / $max) * 100;
                                    $color = $percent >= 100 ? 'danger' : ($percent >= 80 ? 'warning' : 'success');
                                    echo "<span class='badge bg-{$color}'>{$count} / {$max}</span>";
                                    if ($count >= $max) {
                                        echo "<div class='small text-danger fw-bold'>Esgotado</div>";
                                    }
                                } else {
                                    echo "<span class='badge bg-info text-dark'>{$count} (Ilimitado)</span>";
                                }
                                ?>
                                <div class="mt-1">
                                    <?php if ((int)$course['allow_enrollment']): ?>
                                        <small class="text-success"><i class="fas fa-check-circle"></i> Abertas</small>
                                    <?php else: ?>
                                        <small class="text-danger"><i class="fas fa-times-circle"></i> Fechadas</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick='editCourse(<?php echo jsonSafe($course); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a class="btn btn-sm btn-outline-danger" 
                                       href="index.php?r=admin/courses&delete=<?php echo (int)$course['id']; ?>" 
                                       onclick="return confirm('Tem certeza que deseja excluir este curso? Todas as inscrições e certificados associados serão removidos.');">
                                        <i class="fas fa-trash-alt"></i>
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

<!-- Course Modal -->
<div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalLabel">Novo Curso (Admin)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="index.php?r=admin/courses" enctype="multipart/form-data" id="courseForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="courseId" value="">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Nome do Curso <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="courseName" class="form-control" required>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Descrição <span class="text-danger">*</span></label>
                            <textarea name="description" id="courseDescription" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-users-cog me-1"></i>Limite de Inscrições</label>
                            <input type="number" name="max_enrollments" id="courseMaxEnrollments" class="form-control" min="0" placeholder="0 para ilimitado">
                            <div class="form-text">Deixe vazio ou zero para inscrições ilimitadas</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Instrutor <span class="text-danger">*</span></label>
                            <input type="text" name="instructor" id="courseInstructor" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Carga Horária (horas)</label>
                            <input type="number" name="workload" id="courseWorkload" class="form-control" min="1">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Data</label>
                            <input type="date" name="date" id="courseDate" class="form-control">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Horário</label>
                            <input type="time" name="time" id="courseTime" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Período (Ex: Manhã)</label>
                            <input type="text" name="period" id="coursePeriod" class="form-control">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Local</label>
                            <input type="text" name="location" id="courseLocation" class="form-control" placeholder="Endereço ou Link">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Imagem de Capa</label>
                            <input type="file" name="cover" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG ou WEBP. Deixe em branco para manter a atual.</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="courseStatus" class="form-select">
                                <option value="active">Ativo</option>
                                <option value="inactive">Inativo</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_enrollment" id="courseAllowEnrollment" checked>
                                <label class="form-check-label" for="courseAllowEnrollment">
                                    Disponível para inscrições dos candidatos
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function clearForm() {
    document.getElementById('courseModalLabel').innerText = 'Novo Curso (Admin)';
    document.getElementById('courseForm').reset();
    document.getElementById('courseId').value = '';
    // Reset defaults
    document.getElementById('courseStatus').value = 'active';
    document.getElementById('courseAllowEnrollment').checked = true;
    document.getElementById('courseMaxEnrollments').value = '';
}

function editCourse(course) {
    document.getElementById('courseModalLabel').innerText = 'Editar Curso';
    document.getElementById('courseId').value = course.id;
    document.getElementById('courseName').value = course.name;
    document.getElementById('courseDescription').value = course.description;
    document.getElementById('courseInstructor').value = course.instructor;
    document.getElementById('courseWorkload').value = course.workload;
    document.getElementById('courseDate').value = course.date || '';
    document.getElementById('courseTime').value = course.time || '';
    document.getElementById('coursePeriod').value = course.period || '';
    document.getElementById('courseLocation').value = course.location;
    document.getElementById('courseStatus').value = course.status;
    document.getElementById('courseAllowEnrollment').checked = (parseInt(course.allow_enrollment) === 1);
    document.getElementById('courseMaxEnrollments').value = course.max_enrollments > 0 ? course.max_enrollments : '';
    
    var modal = new bootstrap.Modal(document.getElementById('courseModal'));
    modal.show();
}
</script>
