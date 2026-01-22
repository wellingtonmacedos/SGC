<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Inscritos por Curso</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="index.php?r=report/enrollmentsByCourse&export=csv" class="btn btn-sm btn-outline-secondary">Exportar CSV</a>
            <a href="index.php?r=report/enrollmentsByCourse&export=pdf" target="_blank" class="btn btn-sm btn-outline-secondary">Imprimir PDF</a>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Curso</th>
                <th class="text-center">Total Inscritos</th>
                <th class="text-center">Limite de Vagas</th>
                <th class="text-center">Vagas Restantes</th>
                <th class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td class="text-center">
                    <span class="badge bg-primary rounded-pill"><?php echo $row['total_enrollments']; ?></span>
                </td>
                <td class="text-center">
                    <?php echo $row['max_enrollments'] > 0 ? $row['max_enrollments'] : '<span class="text-muted">Ilimitado</span>'; ?>
                </td>
                <td class="text-center">
                    <?php 
                    if ($row['remaining_seats'] !== null) {
                        $badgeClass = $row['remaining_seats'] == 0 ? 'bg-danger' : ($row['remaining_seats'] < 5 ? 'bg-warning' : 'bg-success');
                        echo '<span class="badge ' . $badgeClass . '">' . $row['remaining_seats'] . '</span>';
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td class="text-end">
                    <a href="index.php?r=course/view&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">Ver Curso</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
