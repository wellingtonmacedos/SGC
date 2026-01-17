<div class="row">
    <div class="col-12 mb-4">
        <h1 class="h4">Dashboard Administrativo</h1>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <h2 class="card-title h5">Candidatos</h2>
                <p class="display-6 mb-0"><?php echo (int)$totalCandidates; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <h2 class="card-title h5">Cursos</h2>
                <p class="display-6 mb-0"><?php echo (int)$totalCourses; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-bg-info">
            <div class="card-body">
                <h2 class="card-title h5">Certificados</h2>
                <p class="display-6 mb-0"><?php echo (int)$totalCertificates; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-bg-warning">
            <div class="card-body">
                <h2 class="card-title h5">Cursos com certificado</h2>
                <p class="display-6 mb-0"><?php echo (int)$totalCertificateStatus; ?></p>
            </div>
        </div>
    </div>
</div>

