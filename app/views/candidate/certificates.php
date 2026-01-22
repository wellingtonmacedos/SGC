<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4 text-primary"><i class="fas fa-certificate me-2"></i>Meus Certificados</h2>
    </div>
</div>

<?php if (empty($certificates)): ?>
    <div class="alert alert-warning py-4 text-center">
        <i class="fas fa-certificate fa-2x mb-3 d-block"></i>
        Você ainda não possui certificados emitidos.
        <br>
        <small class="text-muted">Conclua cursos para obter seus certificados.</small>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($certificates as $cert): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow transition">
                    <div class="card-body p-4 text-center d-flex flex-column">
                        <div class="mb-3">
                            <i class="fas fa-award fa-4x text-warning mb-2"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-2"><?php echo e($cert['course_name'] ?? 'Curso Concluído'); ?></h5>
                        <p class="text-muted small mb-4">
                            Emitido em: <?php echo date('d/m/Y', strtotime($cert['created_at'])); ?>
                        </p>
                        
                        <div class="mt-auto">
                            <a href="index.php?r=candidate/downloadCertificate&id=<?php echo $cert['id']; ?>" class="btn btn-success w-100 text-white shadow-sm" target="_blank">
                                <i class="fas fa-download me-2"></i> Baixar PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.hover-shadow:hover { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; transform: translateY(-3px); }
.transition { transition: all 0.3s ease; }
</style>
