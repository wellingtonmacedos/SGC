<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Histórico de Inscrições</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="index.php?r=report/enrollmentsHistory&export=csv&<?php echo http_build_query($filters); ?>" class="btn btn-sm btn-outline-secondary">Exportar CSV</a>
            <a href="index.php?r=report/enrollmentsHistory&export=pdf&<?php echo http_build_query($filters); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Imprimir PDF</a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-3">
            <input type="hidden" name="r" value="report/enrollmentsHistory">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $filters['start_date']; ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $filters['end_date']; ?>">
            </div>
            <div class="col-md-3">
                <label for="group_by" class="form-label">Agrupar por</label>
                <select class="form-select" id="group_by" name="group_by">
                    <option value="day" <?php echo $filters['group_by'] === 'day' ? 'selected' : ''; ?>>Dia</option>
                    <option value="month" <?php echo $filters['group_by'] === 'month' ? 'selected' : ''; ?>>Mês</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="course_id" class="form-label">Curso (Opcional)</label>
                <select class="form-select" id="course_id" name="course_id">
                    <option value="">Todos os Cursos</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo $filters['course_id'] === $course['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Atualizar Relatório</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-info">
            <strong>Média Diária no Período:</strong> <?php echo number_format($average, 2, ',', '.'); ?> inscrições/dia
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Período</th>
                <th>Curso</th>
                <th class="text-center">Total de Inscrições</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalPeriod = 0;
            foreach ($data as $row): 
                $totalPeriod += $row['total'];
            ?>
            <tr>
                <td><?php echo $row['period']; ?></td>
                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                <td class="text-center"><?php echo $row['total']; ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="table-dark">
                <td colspan="2"><strong>Total no Período</strong></td>
                <td class="text-center"><strong><?php echo $totalPeriod; ?></strong></td>
            </tr>
        </tbody>
    </table>
</div>
