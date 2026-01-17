<?php ?>
<div class="row">
    <div class="col-md-5 mb-4">
        <h1 class="h5">Upload de certificado</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo e($success); ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?r=admin/certificates" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Candidato</label>
                <select name="user_id" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($candidates as $candidate): ?>
                        <option value="<?php echo (int)$candidate['id']; ?>"><?php echo e($candidate['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Curso</label>
                <select name="course_id" class="form-select" required>
                    <option value="">Selecione</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo (int)$course['id']; ?>"><?php echo e($course['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Arquivo PDF</label>
                <input type="file" name="certificate" class="form-control" accept="application/pdf" required>
            </div>
            <button type="submit" class="btn btn-primary">Enviar certificado</button>
        </form>
    </div>
    <div class="col-md-7 mb-4">
        <h1 class="h5">Certificados enviados</h1>
        <?php if (empty($certificatesByUser)): ?>
            <p class="text-muted">Nenhum certificado cadastrado.</p>
        <?php else: ?>
            <?php foreach ($certificatesByUser as $userId => $certificates): ?>
                <?php if (empty($certificates)) continue; ?>
                <h2 class="h6 mt-3">
                    <?php
                    $name = isset($candidatesById[$userId]) ? $candidatesById[$userId]['name'] : ('ID ' . (int)$userId);
                    echo e($name);
                    ?>
                </h2>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Arquivo</th>
                            <th>Data</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($certificates as $cert): ?>
                            <tr>
                                <td><?php echo e($cert['course_name']); ?></td>
                                <td><?php echo e($cert['original_name']); ?></td>
                                <td><?php echo e($cert['created_at']); ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-danger" href="index.php?r=admin/certificates&delete=<?php echo (int)$cert['id']; ?>" onclick="return confirm('Excluir este certificado?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
