<div class="row mb-4">
    <div class="col-12">
        <h1 class="h4">Configurações do Órgão</h1>
        <p class="text-muted">Gerencie os dados institucionais que serão exibidos no sistema.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="needs-validation">
    <div class="row">
        <!-- Dados Gerais -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-building me-2"></i> Dados da Instituição
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="organization_name" class="form-label">Nome do Órgão / Instituição *</label>
                        <input type="text" class="form-control" id="organization_name" name="organization_name" 
                               value="<?php echo htmlspecialchars($settings['organization_name'] ?? ''); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cnpj" class="form-label">CNPJ</label>
                            <input type="text" class="form-control" id="cnpj" name="cnpj" 
                                   value="<?php echo htmlspecialchars($settings['cnpj'] ?? ''); ?>" placeholder="00.000.000/0000-00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">E-mail Institucional *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                   value="<?php echo htmlspecialchars($settings['zip_code'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Endereço Completo</label>
                        <input type="text" class="form-control" id="address" name="address" 
                               value="<?php echo htmlspecialchars($settings['address'] ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="city" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($settings['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">Estado (UF)</label>
                            <input type="text" class="form-control" id="state" name="state" maxlength="2"
                                   value="<?php echo htmlspecialchars($settings['state'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-align-left me-2"></i> Informações Adicionais
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="institutional_text" class="form-label">Texto Institucional (Opcional)</label>
                        <textarea class="form-control" id="institutional_text" name="institutional_text" rows="4"><?php echo htmlspecialchars($settings['institutional_text'] ?? ''); ?></textarea>
                        <div class="form-text">Texto breve sobre o órgão para exibição em rodapés ou documentos.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logotipo -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-image me-2"></i> Logotipo
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if (!empty($settings['logo'])): ?>
                            <img src="index.php?r=file/logo&file=<?php echo urlencode($settings['logo']); ?>" 
                                 class="img-fluid img-thumbnail mb-2" id="logo-preview" style="max-height: 150px;">
                        <?php else: ?>
                            <img src="" class="img-fluid img-thumbnail mb-2 d-none" id="logo-preview" style="max-height: 150px;">
                            <div class="text-muted p-4 border border-dashed bg-light" id="no-logo-placeholder">
                                <i class="fas fa-image fa-2x mb-2"></i><br>
                                Sem logotipo
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3 text-start">
                        <label for="logo" class="form-label">Alterar Logotipo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/png, image/jpeg, image/svg+xml">
                        <div class="form-text">Formatos: PNG, JPG, SVG. Máx: 2MB.</div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 btn-lg">
                <i class="fas fa-save me-2"></i> Salvar Configurações
            </button>
        </div>
    </div>
</form>

<script>
document.getElementById('logo').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logo-preview');
            const placeholder = document.getElementById('no-logo-placeholder');
            
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        }
        reader.readAsDataURL(file);
    }
});
</script>
