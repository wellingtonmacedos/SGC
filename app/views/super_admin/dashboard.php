<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="h3 text-dark">Painel de Controle (Super Usuário)</h2>
            <p class="text-muted">Visão geral do sistema e ferramentas de administração global.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Admins Card -->
        <div class="col-md-6 col-lg-3">
            <div class="stat-card stat-card-purple">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2" style="opacity: 0.8">Administradores</h6>
                        <h2 class="mb-0"><?php echo $stats['total_admins']; ?></h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-users-cog"></i>
                    </div>
                </div>
                <div class="mt-3 small">
                    <a href="index.php?r=superAdmin/admins" class="text-white text-decoration-none">
                        Gerenciar <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Candidates Card -->
        <div class="col-md-6 col-lg-3">
            <div class="stat-card stat-card-blue">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2" style="opacity: 0.8">Candidatos</h6>
                        <h2 class="mb-0"><?php echo $stats['total_candidates']; ?></h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-3 small">
                    <span class="opacity-75">Cadastrados no sistema</span>
                </div>
            </div>
        </div>

        <!-- Courses Card -->
        <div class="col-md-6 col-lg-3">
            <div class="stat-card stat-card-green">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2" style="opacity: 0.8">Cursos</h6>
                        <h2 class="mb-0"><?php echo $stats['total_courses']; ?></h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="mt-3 small">
                    <span class="opacity-75">Ofertados</span>
                </div>
            </div>
        </div>

        <!-- Backups Card -->
        <div class="col-md-6 col-lg-3">
            <div class="stat-card stat-card-orange">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-2" style="opacity: 0.8">Backups</h6>
                        <h2 class="mb-0"><?php echo $stats['total_backups']; ?></h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
                <div class="mt-3 small">
                    <a href="index.php?r=superAdmin/backups" class="text-white text-decoration-none">
                        Acessar módulo <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-rocket me-2 text-primary"></i>Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="index.php?r=superAdmin/createAdmin" class="btn btn-outline-primary w-100 p-3 text-start hover-shadow btn-hover-effect">
                                <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                                <span class="fw-bold">Novo Administrador</span>
                                <div class="small text-muted mt-1">Adicionar um novo gestor ao sistema</div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="index.php?r=superAdmin/backups" class="btn btn-outline-success w-100 p-3 text-start hover-shadow btn-hover-effect">
                                <i class="fas fa-save fa-2x mb-2 d-block"></i>
                                <span class="fw-bold">Novo Backup</span>
                                <div class="small text-muted mt-1">Realizar backup manual do sistema</div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="index.php?r=superAdmin/reports" class="btn btn-outline-info w-100 p-3 text-start hover-shadow btn-hover-effect">
                                <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                                <span class="fw-bold">Relatório Geral</span>
                                <div class="small text-muted mt-1">Visualizar estatísticas consolidadas</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
