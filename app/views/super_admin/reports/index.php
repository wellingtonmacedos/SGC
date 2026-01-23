<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 text-dark">Relatórios Avançados</h2>
            <p class="text-muted">Visão geral e estatísticas do sistema.</p>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-download me-2"></i>Exportar
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="index.php?r=superAdmin/exportReports&format=csv&start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&course_id=<?= $filters['course_id'] ?>&status=<?= $filters['status'] ?>"><i class="fas fa-file-csv me-2"></i>CSV (Excel)</a></li>
                <li><a class="dropdown-item" href="index.php?r=superAdmin/exportReports&format=pdf&start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&course_id=<?= $filters['course_id'] ?>&status=<?= $filters['status'] ?>" target="_blank"><i class="fas fa-print me-2"></i>Imprimir / PDF</a></li>
            </ul>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-primary border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 text-uppercase small fw-bold">Candidatos</p>
                            <h2 class="mb-0 text-dark"><?php echo $stats['total_candidates']; ?></h2>
                        </div>
                        <div class="text-primary bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-success border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 text-uppercase small fw-bold">Administradores</p>
                            <h2 class="mb-0 text-dark"><?php echo $stats['total_admins']; ?></h2>
                        </div>
                        <div class="text-success bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-user-shield fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-info border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 text-uppercase small fw-bold">Cursos</p>
                            <h2 class="mb-0 text-dark"><?php echo $stats['total_courses']; ?></h2>
                        </div>
                        <div class="text-info bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-graduation-cap fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-warning border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 text-uppercase small fw-bold">Inscrições</p>
                            <h2 class="mb-0 text-dark"><?php echo $stats['total_enrollments']; ?></h2>
                        </div>
                        <div class="text-warning bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-file-signature fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0">Evolução de Inscrições (Últimos 12 Meses)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['monthly_enrollments'])): ?>
                        <div class="text-center py-5 text-muted">
                            <p>Dados insuficientes para exibir o gráfico.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mês/Ano</th>
                                        <th>Total de Inscrições</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['monthly_enrollments'] as $monthStats): ?>
                                        <tr>
                                            <td><?php echo date('m/Y', strtotime($monthStats['month'])); ?></td>
                                            <td><?php echo $monthStats['total']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="card-title mb-0">Resumo</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Média de Inscrições/Curso
                            <span class="badge bg-primary rounded-pill">
                                <?php echo ($stats['total_courses'] > 0) ? round($stats['total_enrollments'] / $stats['total_courses'], 1) : 0; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Média de Cursos/Candidato
                            <span class="badge bg-primary rounded-pill">
                                <?php echo ($stats['total_candidates'] > 0) ? round($stats['total_enrollments'] / $stats['total_candidates'], 1) : 0; ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Detailed Report -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent border-bottom py-3">
            <h5 class="card-title mb-0">Relatório Detalhado de Inscrições</h5>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form action="index.php" method="GET" class="row g-3 mb-4 align-items-end">
                <input type="hidden" name="r" value="superAdmin/reports">
                
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $filters['start_date']; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Data Final</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $filters['end_date']; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="course_id" class="form-label">Curso</label>
                    <select class="form-select" id="course_id" name="course_id">
                        <option value="">Todos os Cursos</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo ($filters['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="confirmed" <?php echo ($filters['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmado</option>
                        <option value="pending" <?php echo ($filters['status'] === 'pending') ? 'selected' : ''; ?>>Pendente</option>
                        <option value="cancelled" <?php echo ($filters['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button>
                </div>
            </form>

            <!-- Results Table -->
            <?php if (empty($reportData)): ?>
                <div class="alert alert-info text-center">
                    Nenhum registro encontrado para os filtros selecionados.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Curso</th>
                                <th>Candidato</th>
                                <th>Email</th>
                                <th>CPF</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $row): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cpf']); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = match($row['status']) {
                                            'confirmed' => 'success',
                                            'pending' => 'warning',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        $statusLabel = match($row['status']) {
                                            'confirmed' => 'Confirmado',
                                            'pending' => 'Pendente',
                                            'cancelled' => 'Cancelado',
                                            default => $row['status']
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
