<?php ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <h1 class="h4 mb-3 text-center">Cadastro de Candidato</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">Cadastro realizado com sucesso. Você já pode fazer login.</div>
        <?php else: ?>
            <form method="post" action="index.php?r=auth/register" enctype="multipart/form-data" novalidate>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Usuário (Login) <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>">
                        <div class="form-text">Sem espaços</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">CPF <span class="text-danger">*</span></label>
                        <input type="text" name="cpf" class="form-control" required value="<?php echo isset($_POST['cpf']) ? e($_POST['cpf']) : ''; ?>" placeholder="000.000.000-00">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">E-mail <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? e($_POST['phone']) : ''; ?>" placeholder="(00) 00000-0000">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Endereço <span class="text-danger">*</span></label>
                    <textarea name="address" class="form-control" required rows="2"><?php echo isset($_POST['address']) ? e($_POST['address']) : ''; ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Foto de Perfil</label>
                    <input type="file" name="photo" id="photoInput" class="form-control" accept="image/jpeg,image/png">
                    <div class="form-text">Opcional. Apenas JPG ou PNG. Máx 2MB.</div>
                    <div class="mt-2 d-none" id="photoPreviewContainer">
                        <img id="photoPreview" src="" alt="Prévia" class="img-thumbnail" style="max-height: 150px;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Senha <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirmar senha <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
            </form>
            
            <script>
            document.getElementById('photoInput').addEventListener('change', function(event) {
                const file = event.target.files[0];
                const preview = document.getElementById('photoPreview');
                const container = document.getElementById('photoPreviewContainer');
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        container.classList.remove('d-none');
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.src = '';
                    container.classList.add('d-none');
                }
            });
            </script>
        <?php endif; ?>
    </div>
</div>
