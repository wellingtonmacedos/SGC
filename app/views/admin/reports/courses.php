<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatório de Cursos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="index.php?r=report/courses&export=csv&<?php echo http_build_query($filters); ?>" class="btn btn-sm btn-outline-secondary">Exportar CSV</a>
            <a href="index.php?r=report/courses&export=pdf&<?php echo http_build_query($filters); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Imprimir PDF</a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="r" value="report/courses">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $filters['start_date'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $filters['end_date'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Ativo</option>
                    <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                    <option value="closed" <?php echo ($filters['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Encerrado</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-header">Total</div>
            <div class="card-body">
                <h3><?php echo $stats['total']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center text-success">
            <div class="card-header">Ativos</div>
            <div class="card-body">
                <h3><?php echo $stats['active']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center text-danger">
            <div class="card-header">Inativos/Encerrados</div>
            <div class="card-body">
                <h3><?php echo $stats['inactive']; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Status</th>
                <th>Carga Horária</th>
                <th>Criado em</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo $course['id']; ?></td>
                <td><?php echo htmlspecialchars($course['name']); ?></td>
                <td>
                    <?php 
                    $statusMap = [
                        'active' => '<span class="badge bg-success">Ativo</span>',
                        'inactive' => '<span class="badge bg-secondary">Inativo</span>',
                        'closed' => '<span class="badge bg-danger">Encerrado</span>'
                    ];
                    echo $statusMap[$course['status']] ?? $course['status']; 
                    ?>
                </td>
                <td><?php echo $course['workload']; ?>h</td>
                <td><?php echo date('d/m/Y H:i', strtotime($course['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
