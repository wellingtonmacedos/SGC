<?php
$pageTitle = 'Atualizações do Sistema';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-gray-800">Atualizações e Versionamento</h2>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                Sistema atualizado com sucesso! <?php echo isset($_GET['count']) ? $_GET['count'] . ' migrações executadas.' : ''; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['success'] === 'no_changes'): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                O sistema já está atualizado. Nenhuma pendência encontrada.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Erro na atualização: <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Versão Atual</div>
                            <div class="h1 mb-0 font-weight-bold text-gray-800"><?php echo e($version); ?></div>
                            <p class="text-muted mt-2 mb-0">Esta é a versão atualmente instalada no servidor.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-code-branch fa-3x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status do Sistema</h6>
                </div>
                <div class="card-body">
                    <p>O sistema está operando normalmente.</p>

                    <div class="d-grid gap-2 mb-3">
                        <form method="post" action="index.php?r=superAdmin/runUpdate" onsubmit="return confirm('Tem certeza? Recomenda-se fazer um backup antes.');">
                            <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-sync-alt me-2"></i> Executar Atualização Agora
                            </button>
                        </form>
                    </div>

                    <div class="alert alert-light border">
                        <i class="fas fa-terminal me-2"></i>
                        Alternativa via SSH:
                        <pre class="bg-light p-2 mt-2 mb-0 border rounded">php update_system.php</pre>
                    </div>
                    
                    <small class="text-muted">Certifique-se de que os backups estão em dia.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Instruções de Atualização</h6>
        </div>
        <div class="card-body">
            <ol>
                <li>Acesse o servidor via SSH.</li>
                <li>Navegue até a pasta raiz do projeto.</li>
                <li>Execute o comando <code>php update_system.php</code>.</li>
                <li>O script irá automaticamente:
                    <ul>
                        <li>Criar um backup completo (Banco + Arquivos).</li>
                        <li>Executar migrações de banco de dados pendentes.</li>
                        <li>Atualizar a versão do sistema.</li>
                        <li>Registrar a operação nos logs.</li>
                    </ul>
                </li>
            </ol>
        </div>
    </div>
</div>
