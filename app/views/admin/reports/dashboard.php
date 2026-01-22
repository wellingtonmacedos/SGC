<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2">Relatórios Gerenciais</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="text-muted small align-self-center">
            Última atualização: <?php echo date('H:i'); ?>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="row mb-5">
    <div class="col-md-4 mb-3">
        <div class="stat-card stat-card-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-2" style="opacity: 0.8;">Cursos Ativos</h6>
                    <h2 class="mb-0 display-5 fw-bold"><?php echo $stats['active_courses']; ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
            <div class="mt-2 small" style="opacity: 0.8;">
                Disponíveis para inscrição
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card stat-card-green">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-2" style="opacity: 0.8;">Total de Inscrições</h6>
                    <h2 class="mb-0 display-5 fw-bold"><?php echo $stats['total_enrollments']; ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            <div class="mt-2 small" style="opacity: 0.8;">
                Histórico acumulado
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card stat-card-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-2" style="opacity: 0.8;">Novos este Mês</h6>
                    <h2 class="mb-0 display-5 fw-bold"><?php echo $stats['enrollments_this_month']; ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="mt-2 small" style="opacity: 0.8;">
                Inscrições recentes
            </div>
        </div>
    </div>
</div>

<h4 class="mb-3 text-secondary">Relatórios Detalhados</h4>
<div class="row">
    <!-- Card 1 -->
    <div class="col-md-4 mb-4">
        <div class="card report-card h-100">
            <div class="card-body text-center p-4">
                <div class="report-icon mb-3 text-primary">
                    <i class="fas fa-list-alt fa-2x"></i>
                </div>
                <h5 class="card-title">Relatório de Cursos</h5>
                <p class="card-text text-muted small">Visualize a lista completa de cursos, filtre por status (ativo/inativo) e período de criação.</p>
                <a href="index.php?r=report/courses" class="btn btn-outline-primary btn-sm stretched-link mt-2">Acessar Relatório</a>
            </div>
        </div>
    </div>
    
    <!-- Card 2 -->
    <div class="col-md-4 mb-4">
        <div class="card report-card h-100">
            <div class="card-body text-center p-4">
                <div class="report-icon mb-3 text-success">
                    <i class="fas fa-users-cog fa-2x"></i>
                </div>
                <h5 class="card-title">Inscritos por Curso</h5>
                <p class="card-text text-muted small">Acompanhe a ocupação das turmas, verifique limites de vagas e disponibilidade em tempo real.</p>
                <a href="index.php?r=report/enrollmentsByCourse" class="btn btn-outline-success btn-sm stretched-link mt-2">Acessar Relatório</a>
            </div>
        </div>
    </div>
    
    <!-- Card 3 -->
    <div class="col-md-4 mb-4">
        <div class="card report-card h-100">
            <div class="card-body text-center p-4">
                <div class="report-icon mb-3 text-info">
                    <i class="fas fa-chart-line fa-2x"></i>
                </div>
                <h5 class="card-title">Histórico de Inscrições</h5>
                <p class="card-text text-muted small">Analise a evolução das matrículas ao longo do tempo (diário/mensal) e veja tendências.</p>
                <a href="index.php?r=report/enrollmentsHistory" class="btn btn-outline-info btn-sm stretched-link mt-2">Acessar Relatório</a>
            </div>
        </div>
    </div>
</div>
