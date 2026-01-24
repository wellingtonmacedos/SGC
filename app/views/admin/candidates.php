<?php
$totalCandidates = count($candidates);
?>
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold"><i class="fas fa-users me-2 text-primary"></i>Gestão de Candidatos</h2>
            <p class="text-muted small mb-0">Gerencie os usuários cadastrados no sistema.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="index.php?r=admin/create-candidate" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-2"></i>Novo Candidato
            </a>
            <div class="d-none d-md-block">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body py-2 px-4">
                        <div class="d-flex align-items-center">
                            <div class="display-6 fw-bold me-3"><?php echo $totalCandidates; ?></div>
                            <div class="small lh-sm">Total de<br>Candidatos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo e($success); ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Lista de Usuários</h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="candidateSearch" class="form-control border-start-0 bg-light" placeholder="Buscar por nome, email ou CPF...">
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (empty($candidates)): ?>
            <div class="card-body text-center py-5">
                <div class="mb-3 text-muted opacity-25">
                    <i class="fas fa-users-slash fa-4x"></i>
                </div>
                <h4 class="text-muted">Nenhum candidato encontrado</h4>
                <p class="text-muted small">Não há candidatos cadastrados no sistema ainda.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="candidatesTable">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-4">Candidato</th>
                            <th>Documentos</th>
                            <th>Contato</th>
                            <th>Localização</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($candidates as $candidate): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <?php if (!empty($candidate['photo'])): ?>
                                            <img src="index.php?r=file/photo&file=<?php echo e($candidate['photo']); ?>" alt="Foto" class="rounded-circle shadow-sm object-fit-cover" style="width: 45px; height: 45px;">
                                        <?php else: ?>
                                            <div class="avatar-circle bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 45px; height: 45px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark candidate-name"><?php echo e($candidate['name']); ?></div>
                                        <div class="small text-muted candidate-username"><i class="fas fa-at me-1 text-xs"></i><?php echo e($candidate['username'] ?? '-'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge bg-light text-dark border candidate-cpf"><i class="far fa-id-card me-1 text-muted"></i> <?php echo e($candidate['cpf']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="mb-1 candidate-email"><i class="far fa-envelope me-2 text-muted width-20"></i><?php echo e($candidate['email']); ?></div>
                                    <div class="candidate-phone"><i class="fas fa-phone me-2 text-muted width-20"></i><?php echo e($candidate['phone'] ?? '-'); ?></div>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo e($candidate['address'] ?? ''); ?>">
                                    <i class="fas fa-map-marker-alt me-1 text-muted"></i> <?php echo e(mb_strimwidth($candidate['address'] ?? '-', 0, 30, '...')); ?>
                                </small>
                            </td>
                            <td class="text-end pe-4">
                                <a href="index.php?r=admin/edit-candidate&id=<?php echo $candidate['id']; ?>" class="btn btn-sm btn-outline-primary shadow-sm" title="Editar Cadastro">
                                    <i class="fas fa-pen"></i> <span class="d-none d-lg-inline ms-1">Editar</span>
                                </a>
                                <a href="index.php?r=admin/candidates&delete=<?php echo $candidate['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm ms-1" onclick="return confirm('Tem certeza que deseja excluir este candidato? Esta ação não pode ser desfeita e removerá o acesso do usuário.');" title="Excluir Candidato">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">
                <small class="text-muted" id="tableInfo">Exibindo <?php echo count($candidates); ?> candidatos</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.width-20 { width: 20px; text-align: center; }
.text-xs { font-size: 0.75rem; }
.object-fit-cover { object-fit: cover; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('candidateSearch');
    const table = document.getElementById('candidatesTable');
    
    if (searchInput && table) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let visibleCount = 0;
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const name = row.querySelector('.candidate-name').textContent.toLowerCase();
                const username = row.querySelector('.candidate-username').textContent.toLowerCase();
                const email = row.querySelector('.candidate-email').textContent.toLowerCase();
                const cpf = row.querySelector('.candidate-cpf').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || username.includes(searchTerm) || email.includes(searchTerm) || cpf.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }
            
            const info = document.getElementById('tableInfo');
            if (info) info.textContent = `Exibindo ${visibleCount} candidatos filtrados`;
        });
    }
});
</script>
