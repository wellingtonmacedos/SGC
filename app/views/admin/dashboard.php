<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Dashboard Administrativo</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Compartilhar</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="stat-card stat-card-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-2" style="opacity: 0.8;">Candidatos</h6>
                    <h2 class="mb-0 display-6 fw-bold"><?php echo (int)$totalCandidates; ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="mt-3 small" style="opacity: 0.8;">
                <a href="index.php?r=admin/candidates" class="text-white text-decoration-none">Ver detalhes <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stat-card stat-card-green">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-2" style="opacity: 0.8;">Cursos</h6>
                    <h2 class="mb-0 display-6 fw-bold"><?php echo (int)$totalCourses; ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            <div class="mt-3 small" style="opacity: 0.8;">
                <a href="index.php?r=admin/courses" class="text-white text-decoration-none">Gerenciar cursos <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stat-card stat-card-purple">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-2" style="opacity: 0.8;">Certificados</h6>
                    <h2 class="mb-0 display-6 fw-bold"><?php echo (int)$totalCertificates; ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-certificate"></i>
                </div>
            </div>
            <div class="mt-3 small" style="opacity: 0.8;">
                <a href="index.php?r=admin/certificates" class="text-white text-decoration-none">Emitir/Ver <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stat-card stat-card-orange">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-2" style="opacity: 0.8;">Certificáveis</h6>
                    <h2 class="mb-0 display-6 fw-bold"><?php echo (int)$totalCertificateStatus; ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-award"></i>
                </div>
            </div>
            <div class="mt-3 small" style="opacity: 0.8;">
                <span title="Inscrições com status 'certificado disponível'">Aguardando emissão</span>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="index.php?r=admin/courses&action=create" class="btn btn-outline-primary text-start p-3">
                        <i class="fas fa-plus-circle me-2"></i> Criar Novo Curso
                    </a>
                    <a href="index.php?r=admin/candidates" class="btn btn-outline-secondary text-start p-3">
                        <i class="fas fa-user-plus me-2"></i> Gerenciar Candidatos
                    </a>
                    <a href="index.php?r=report/dashboard" class="btn btn-outline-info text-start p-3">
                        <i class="fas fa-chart-pie me-2"></i> Ver Relatórios Gerenciais
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-info-circle text-info me-2"></i>Status do Sistema</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        Status do Servidor
                        <span class="badge bg-success rounded-pill">Online</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        Versão do PHP
                        <span class="text-muted"><?php echo phpversion(); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        Data do Servidor
                        <span class="text-muted"><?php echo date('d/m/Y H:i'); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
