<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold"><i class="fas fa-paint-brush me-2 text-primary"></i>Personalização do Login</h1>
            <p class="text-muted small mb-0">Configure a aparência da tela de login do sistema.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> Configurações atualizadas com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo e($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-sliders-h me-2"></i>Configurações Gerais</h6>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="index.php?r=superAdmin/loginSettings" enctype="multipart/form-data">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Título de Boas-vindas</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-heading text-muted"></i></span>
                                    <input type="text" name="login_title" class="form-control" value="<?php echo e($settings['login_title'] ?? 'Bem-vindo ao SGC'); ?>" required>
                                </div>
                                <div class="form-text small">Ex: "Bem-vindo ao Portal do Aluno"</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Subtítulo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-align-left text-muted"></i></span>
                                    <input type="text" name="login_subtitle" class="form-control" value="<?php echo e($settings['login_subtitle'] ?? 'Faça login para continuar'); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Cor Primária (Botões/Destaques)</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="primaryColorInput" name="login_primary_color" value="<?php echo e($settings['login_primary_color'] ?? '#0d1b2a'); ?>" title="Escolha a cor">
                                    <input type="text" class="form-control" id="primaryColorText" value="<?php echo e($settings['login_primary_color'] ?? '#0d1b2a'); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Cor de Fundo (Sólida)</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="bgColorInput" name="login_background_color" value="<?php echo e($settings['login_background_color'] ?? '#0d1b2a'); ?>" title="Escolha a cor">
                                    <input type="text" class="form-control" id="bgColorText" value="<?php echo e($settings['login_background_color'] ?? '#0d1b2a'); ?>" readonly>
                                </div>
                                <div class="form-text small">Usada se a imagem de fundo não carregar ou for removida.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Ícone Principal (Font Awesome)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="<?php echo e($settings['login_icon'] ?? 'fas fa-graduation-cap'); ?>"></i></span>
                                <input type="text" name="login_icon" class="form-control" value="<?php echo e($settings['login_icon'] ?? 'fas fa-graduation-cap'); ?>" placeholder="Ex: fas fa-graduation-cap">
                            </div>
                            <div class="form-text small">Use classes do Font Awesome 5. Ex: <code>fas fa-user-graduate</code>, <code>fas fa-university</code>.</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Logo da Tela de Login</label>
                                <input type="file" name="login_logo" class="form-control" accept="image/*">
                                <div class="form-text small">Deixe em branco para manter a atual. Substitui o ícone se enviada.</div>
                                <?php if (!empty($settings['login_logo'])): ?>
                                    <div class="mt-2 p-2 border rounded bg-light text-center">
                                        <img src="storage/organization/<?php echo e($settings['login_logo']); ?>" alt="Logo Login" style="max-height: 50px;">
                                        <div class="small text-muted mt-1">Logo Atual</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Imagem de Fundo</label>
                                <input type="file" name="login_background_image" class="form-control" accept="image/*">
                                <div class="form-text small">Recomendado: 1920x1080px (JPG/PNG). Deixe em branco para manter.</div>
                                <?php if (!empty($settings['login_background_image'])): ?>
                                    <div class="mt-2 p-2 border rounded bg-light text-center">
                                        <img src="storage/organization/<?php echo e($settings['login_background_image']); ?>" alt="Background" style="max-height: 50px; max-width: 100%;">
                                        <div class="small text-muted mt-1">Imagem Atual</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="fas fa-save me-2"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-primary text-white mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-eye me-2"></i>Visualização Prévia</h5>
                    <p class="small opacity-75">As alterações podem levar alguns instantes para aparecerem para todos os usuários devido ao cache do navegador.</p>
                    <a href="index.php?r=auth/login" target="_blank" class="btn btn-light text-primary fw-bold w-100">
                        Ver Tela de Login <i class="fas fa-external-link-alt ms-2"></i>
                    </a>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-secondary"><i class="fas fa-info-circle me-2"></i>Dicas</h6>
                </div>
                <div class="card-body">
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Escolha cores que contrastem bem com o texto branco.</li>
                        <li class="mb-2">Para a imagem de fundo, prefira imagens escuras ou use uma cor de fundo escura se a imagem demorar a carregar.</li>
                        <li class="mb-2">O ícone principal aparece acima do título "Bem-vindo".</li>
                        <li>Se você fizer upload de uma logo, ela substituirá o ícone padrão.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Sync color inputs with text inputs
    document.getElementById('primaryColorInput').addEventListener('input', function(e) {
        document.getElementById('primaryColorText').value = e.target.value;
    });
    document.getElementById('bgColorInput').addEventListener('input', function(e) {
        document.getElementById('bgColorText').value = e.target.value;
    });
</script>
