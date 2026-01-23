<style>
    .course-hero {
        background: linear-gradient(135deg, #74b928 0%, #a2d245 100%); /* Verde inspirado na imagem */
        color: white;
        padding: 60px 0;
        position: relative;
        overflow: hidden;
    }
    .course-hero::after {
        content: '';
        position: absolute;
        bottom: -50px;
        right: -50px;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .course-status-badge {
        background-color: #28a745;
        color: white;
        padding: 5px 15px;
        border-radius: 4px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.8rem;
        display: inline-block;
        margin-bottom: 15px;
    }
    .course-details-card {
        margin-top: -50px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        z-index: 10;
        position: relative;
    }
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 15px 25px;
    }
    .nav-tabs .nav-link.active {
        color: #74b928;
        border-bottom: 3px solid #74b928;
        background: none;
    }
    .info-icon {
        width: 40px;
        height: 40px;
        background: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #74b928;
        margin-right: 15px;
    }
    .cta-button {
        background-color: #00796b; /* Verde escuro do botão da imagem */
        color: white;
        padding: 12px 30px;
        border-radius: 30px;
        font-weight: bold;
        border: none;
        transition: all 0.3s;
    }
    .cta-button:hover {
        background-color: #004d40;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        color: white;
    }
    .cta-button:disabled {
        background-color: #ccc;
        cursor: not-allowed;
        transform: none;
    }
</style>

<!-- Hero Section -->
<div class="course-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <a href="index.php?r=candidate/dashboard" class="text-white text-decoration-none mb-3 d-inline-block"><i class="fas fa-arrow-left me-2"></i> Voltar para Cursos</a>
                
                <div class="d-block">
                    <?php if ($course['status'] === 'active' && $course['allow_enrollment']): ?>
                        <span class="badge bg-success mb-3 p-2">CURSO ABERTO</span>
                    <?php else: ?>
                        <span class="badge bg-secondary mb-3 p-2">ENCERRADO</span>
                    <?php endif; ?>
                </div>
                
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($course['name']); ?></h1>
                <p class="lead mb-4" style="opacity: 0.9;">
                    <?php echo htmlspecialchars(substr($course['description'], 0, 200)) . (strlen($course['description']) > 200 ? '...' : ''); ?>
                </p>
                
                <?php if (!$isEnrolled && !$isFull && $course['status'] === 'active' && $course['allow_enrollment']): ?>
                    <form action="index.php?r=candidate/dashboard" method="post" class="d-inline">
                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                        <button type="submit" class="btn cta-button btn-lg shadow-lg">
                            Inscreva-se Agora
                        </button>
                    </form>
                <?php elseif ($isEnrolled): ?>
                    <button class="btn btn-light btn-lg disabled" disabled>
                        <i class="fas fa-check-circle text-success me-2"></i> Inscrito
                    </button>
                <?php elseif ($isFull): ?>
                    <button class="btn btn-warning btn-lg disabled text-white" disabled>
                        <i class="fas fa-ban me-2"></i> Vagas Esgotadas
                    </button>
                <?php endif; ?>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <?php if (!empty($course['cover_image'])): ?>
                    <img src="index.php?r=file/cover&file=<?php echo urlencode($course['cover_image']); ?>" class="img-fluid rounded-3 shadow-lg" alt="Capa do curso" loading="lazy" style="transform: rotate(2deg); border: 5px solid rgba(255,255,255,0.2);">
                <?php else: ?>
                    <div class="bg-white rounded-3 shadow-lg p-5 text-center" style="transform: rotate(2deg); height: 300px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-graduation-cap fa-5x text-muted"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Details Section -->
<div class="container mb-5">
    <div class="course-details-card p-4">
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="courseTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="oferta-tab" data-bs-toggle="tab" data-bs-target="#oferta" type="button" role="tab">Sobre o Curso</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">Informações Adicionais</button>
            </li>
        </ul>
        
        <div class="tab-content" id="courseTabContent">
            <!-- Tab: Oferta (Sobre) -->
            <div class="tab-pane fade show active" id="oferta" role="tabpanel">
                <div class="row g-4">
                    <div class="col-md-8">
                        <h4 class="mb-3 text-primary">Descrição Detalhada</h4>
                        <p class="text-muted" style="line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                        </p>
                        
                        <?php if (!empty($course['target_audience'])): ?>
                            <h5 class="mt-4 mb-3 text-primary">Público Alvo</h5>
                            <p class="text-muted">
                                <?php echo nl2br(htmlspecialchars($course['target_audience'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="bg-light p-4 rounded-3">
                            <h5 class="mb-4">Resumo</h5>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="info-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                <div>
                                    <small class="text-muted d-block">Instrutor</small>
                                    <strong><?php echo htmlspecialchars($course['instructor']); ?></strong>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="info-icon"><i class="fas fa-clock"></i></div>
                                <div>
                                    <small class="text-muted d-block">Carga Horária</small>
                                    <strong><?php echo htmlspecialchars($course['workload']); ?>h</strong>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="info-icon"><i class="fas fa-calendar-alt"></i></div>
                                <div>
                                    <small class="text-muted d-block">Data</small>
                                    <strong><?php echo date('d/m/Y', strtotime($course['date'])); ?></strong>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div>
                                    <small class="text-muted d-block">Local</small>
                                    <strong><?php echo htmlspecialchars($course['location']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tab: Informações -->
            <div class="tab-pane fade" id="info" role="tabpanel">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Para obter o certificado, é necessário cumprir 75% de frequência mínima.
                </div>
                
                <h5 class="mb-3 mt-4">Certificação</h5>
                <p>O certificado será emitido pela Escola de Governo após a conclusão satisfatória do curso, estando disponível para download na área do aluno.</p>
                
                <h5 class="mb-3 mt-4">Dúvidas?</h5>
                <p>Entre em contato com a coordenação através do e-mail de suporte.</p>
            </div>
        </div>
    </div>
</div>
