<?php ?>
<h1 class="h5 mb-3">Inscrições por curso</h1>
<form method="get" class="row g-3 mb-3">
    <input type="hidden" name="r" value="admin/enrollments">
    <div class="col-md-6">
        <select name="course_id" class="form-select" required>
            <option value="">Selecione um curso</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo (int)$course['id']; ?>" <?php echo $selectedCourseId === (int)$course['id'] ? 'selected' : ''; ?>>
                    <?php echo e($course['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100" type="submit">Filtrar</button>
    </div>
</form>
<?php if ($selectedCourseId && empty($enrollments)): ?>
    <p class="text-muted">Nenhuma inscrição para este curso.</p>
<?php elseif ($selectedCourseId): ?>
    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
            <thead>
            <tr>
                <th>Candidato</th>
                <th>E-mail</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($enrollments as $enrollment): ?>
                <tr>
                    <td><?php echo e($enrollment['user_name']); ?></td>
                    <td><?php echo e($enrollment['email']); ?></td>
                    <td>
                        <?php
                        $status = $enrollment['status'];
                        if ($status === 'certificate_available') {
                            echo 'Certificado disponível';
                        } elseif ($status === 'completed') {
                            echo 'Concluído';
                        } else {
                            echo 'Inscrito';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p class="text-muted">Selecione um curso para visualizar as inscrições.</p>
<?php endif; ?>
