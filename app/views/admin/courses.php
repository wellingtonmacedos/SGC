<?php ?>
<div class="row">
    <div class="col-md-5 mb-4">
        <h1 class="h5">Cadastro de Curso</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?r=admin/courses" enctype="multipart/form-data">
            <input type="hidden" name="id" value="">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Carga horária</label>
                <input type="number" name="workload" class="form-control" min="1">
            </div>
            <div class="mb-3">
                <label class="form-label">Instrutor</label>
                <input type="text" name="instructor" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Período</label>
                <input type="text" name="period" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Data</label>
                <input type="date" name="date" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Horário</label>
                <input type="time" name="time" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Local</label>
                <input type="text" name="location" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Capa (JPG, PNG ou WEBP)</label>
                <input type="file" name="cover" class="form-control" accept="image/jpeg,image/png,image/webp">
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                </select>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="allow_enrollment" id="allow_enrollment" checked>
                <label class="form-check-label" for="allow_enrollment">
                    Disponível para inscrição
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>
    </div>
    <div class="col-md-7 mb-4">
        <h1 class="h5">Cursos cadastrados</h1>
        <form method="get" class="row g-2 mb-3">
            <input type="hidden" name="r" value="admin/courses">
            <div class="col-md-4">
                <label class="form-label">Filtrar por data</label>
                <input type="date" name="date" class="form-control" value="<?php echo isset($filterDate) ? e($filterDate) : ''; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Filtrar por local</label>
                <input type="text" name="location" class="form-control" value="<?php echo isset($filterLocation) ? e($filterLocation) : ''; ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100" type="submit">Filtrar</button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="index.php?r=admin/courses" class="btn btn-outline-secondary w-100">Limpar</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead>
                <tr>
                    <th>Nome</th>
                    <th>Instrutor</th>
                    <th>Data</th>
                    <th>Local</th>
                    <th>Status</th>
                    <th>Inscrições</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo e($course['name']); ?></td>
                        <td><?php echo e($course['instructor']); ?></td>
                        <td><?php echo isset($course['date']) && $course['date'] ? formatDateBr($course['date']) : '-'; ?></td>
                        <td><?php echo isset($course['location']) && $course['location'] ? e($course['location']) : '-'; ?></td>
                        <td><?php echo e($course['status']); ?></td>
                        <td><?php echo (int)$course['allow_enrollment']; ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-danger" href="index.php?r=admin/courses&delete=<?php echo (int)$course['id']; ?>" onclick="return confirm('Excluir este curso?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
