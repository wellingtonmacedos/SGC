<?php
// Calculate stats
$totalCertificates = 0;
foreach ($certificatesByUser as $certs) {
    $totalCertificates += count($certs);
}
?>
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold"><i class="fas fa-certificate me-2 text-primary"></i>Gestão de Certificados</h2>
            <p class="text-muted small mb-0">Envie e gerencie os certificados dos alunos.</p>
        </div>
        <div class="d-none d-md-block">
             <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">
                <i class="fas fa-check-circle me-1"></i> <?php echo $totalCertificates; ?> Certificados Emitidos
             </span>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo e($error); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo e($success); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Upload Form Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-upload me-2"></i>Novo Certificado</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">Selecione o aluno e o curso para vincular o certificado em PDF.</p>
                    
                    <form method="post" action="index.php?r=admin/certificates" enctype="multipart/form-data" class="needs-validation">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Candidato</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Selecione o aluno...</option>
                                <?php foreach ($candidates as $candidate): ?>
                                    <option value="<?php echo (int)$candidate['id']; ?>"><?php echo e($candidate['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Curso</label>
                            <select name="course_id" id="courseSelect" class="form-select" required disabled>
                                <option value="">Selecione o aluno primeiro...</option>
                            </select>
                            <div class="form-text text-muted small mt-1">
                                <i class="fas fa-info-circle me-1"></i> Lista apenas cursos onde o aluno está inscrito.
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Arquivo PDF</label>
                            <div class="input-group">
                                <input type="file" name="certificate" class="form-control" accept="application/pdf" required id="certFile">
                                <label class="input-group-text" for="certFile"><i class="fas fa-file-pdf text-danger"></i></label>
                            </div>
                            <div class="form-text text-muted small mt-2">
                                <i class="fas fa-info-circle me-1"></i> Apenas arquivos .pdf são permitidos.
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold py-2">
                                <i class="fas fa-paper-plane me-2"></i> Enviar Certificado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- List Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>Certificados Enviados</h5>
                    <div class="input-group input-group-sm w-auto">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="certSearch" class="form-control border-start-0 bg-light" placeholder="Filtrar...">
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($certificatesByUser)): ?>
                        <div class="text-center py-5">
                            <div class="mb-3 text-muted opacity-25">
                                <i class="fas fa-file-contract fa-3x"></i>
                            </div>
                            <h5 class="text-muted">Nenhum certificado enviado</h5>
                            <p class="text-muted small mb-0">Utilize o formulário ao lado para enviar o primeiro certificado.</p>
                        </div>
                    <?php else: ?>
                        <div class="accordion accordion-flush" id="accordionCertificates">
                            <?php $counter = 0; ?>
                            <?php foreach ($certificatesByUser as $userId => $certificates): ?>
                                <?php 
                                    if (empty($certificates)) continue; 
                                    $counter++;
                                    $userName = isset($candidatesById[$userId]) ? $candidatesById[$userId]['name'] : ('ID ' . (int)$userId);
                                    $userPhoto = isset($candidatesById[$userId]) ? $candidatesById[$userId]['photo'] : null;
                                ?>
                                <div class="accordion-item cert-user-group">
                                    <h2 class="accordion-header" id="heading<?php echo $counter; ?>">
                                        <button class="accordion-button <?php echo $counter > 1 ? 'collapsed' : ''; ?> bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $counter; ?>" aria-expanded="<?php echo $counter === 1 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $counter; ?>">
                                            <div class="d-flex align-items-center w-100">
                                                <div class="me-3">
                                                    <?php if ($userPhoto): ?>
                                                        <img src="index.php?r=file/photo&file=<?php echo e($userPhoto); ?>" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                            <?php echo strtoupper(substr($userName, 0, 1)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="fw-bold text-dark cert-user-name">
                                                    <?php echo e($userName); ?>
                                                </div>
                                                <div class="ms-auto me-3 badge bg-secondary rounded-pill">
                                                    <?php echo count($certificates); ?>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $counter; ?>" class="accordion-collapse collapse <?php echo $counter === 1 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $counter; ?>" data-bs-parent="#accordionCertificates">
                                        <div class="accordion-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="bg-white text-muted small text-uppercase">
                                                        <tr>
                                                            <th class="ps-4 w-50">Curso</th>
                                                            <th>Arquivo</th>
                                                            <th>Data Envio</th>
                                                            <th class="text-end pe-4">Ações</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($certificates as $cert): ?>
                                                            <tr>
                                                                <td class="ps-4 fw-medium text-dark"><?php echo e($cert['course_name']); ?></td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                                                        <small class="text-muted text-truncate" style="max-width: 150px;" title="<?php echo e($cert['original_name']); ?>">
                                                                            <?php echo e($cert['original_name']); ?>
                                                                        </small>
                                                                    </div>
                                                                </td>
                                                                <td class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($cert['created_at'])); ?></td>
                                                                <td class="text-end pe-4">
                                                                    <a href="index.php?r=admin/certificates&delete=<?php echo (int)$cert['id']; ?>" 
                                                                       class="btn btn-sm btn-light text-danger" 
                                                                       onclick="return confirm('Tem certeza que deseja excluir este certificado permanentemente?');"
                                                                       title="Excluir Certificado">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('certSearch');
    const userGroups = document.querySelectorAll('.cert-user-group');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            userGroups.forEach(group => {
                const name = group.querySelector('.cert-user-name').textContent.toLowerCase();
                if (name.includes(searchTerm)) {
                    group.style.display = '';
                } else {
                    group.style.display = 'none';
                }
            });
        });
    }

    // Dynamic Course Loading
    const candidateSelect = document.querySelector('select[name="user_id"]');
    const courseSelect = document.getElementById('courseSelect');
    
    if (candidateSelect && courseSelect) {
        candidateSelect.addEventListener('change', function() {
            const userId = this.value;
            
            // Reset state
            courseSelect.innerHTML = '<option value="">Carregando...</option>';
            courseSelect.disabled = true;
            
            if (!userId) {
                courseSelect.innerHTML = '<option value="">Selecione o aluno primeiro...</option>';
                return;
            }

            fetch(`index.php?r=admin/certificates&ajax=courses&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        courseSelect.innerHTML = '<option value="">Selecione o curso...</option>';
                        data.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = course.name;
                            courseSelect.appendChild(option);
                        });
                        courseSelect.disabled = false;
                    } else {
                         courseSelect.innerHTML = '<option value="">Nenhum curso inscrito</option>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    courseSelect.innerHTML = '<option value="">Erro ao carregar cursos</option>';
                });
        });
    }
});
</script>
