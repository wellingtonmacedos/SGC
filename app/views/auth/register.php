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
            <form method="post" action="index.php?r=auth/register" novalidate>
                <div class="mb-3">
                    <label class="form-label">Nome completo</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">CPF</label>
                    <input type="text" name="cpf" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar senha</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
            </form>
        <?php endif; ?>
    </div>
</div>
