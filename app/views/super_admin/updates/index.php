<?php
$pageTitle = 'Atualizações do Sistema';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-gray-800">Atualizações e Versionamento</h2>
    </div>

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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Para atualizar o sistema, execute o script de atualização via linha de comando (SSH).
                    </div>
                    <pre class="bg-light p-3 border rounded">php update_system.php</pre>
                    <small class="text-muted">Certifique-se de que os backups automáticos estão configurados no Cron Job.</small>
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>
