<?php
$policyVersion = $version ?? null;
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Política de Privacidade</h2>
            <?php if ($policyVersion): ?>
                <div class="text-muted small">Versão: <?php echo e($policyVersion); ?></div>
            <?php endif; ?>
        </div>
        <a class="btn btn-outline-secondary" href="index.php?r=auth/login">Voltar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Finalidade do tratamento</h5>
            <ul class="mb-4">
                <li>Realizar cadastro, autenticação e gestão de acesso ao SGC.</li>
                <li>Permitir inscrição em cursos/palestras e acompanhamento de histórico.</li>
                <li>Viabilizar emissão e gestão de certificados, quando aplicável.</li>
                <li>Atender obrigações legais e auditorias institucionais quando necessárias.</li>
            </ul>

            <h5 class="mb-3">Dados pessoais tratados</h5>
            <ul class="mb-4">
                <li>Identificação e contato: nome, e-mail, usuário, telefone e endereço.</li>
                <li>Dados cadastrais: CPF e data de nascimento (quando informada).</li>
                <li>Dados de conta: senha armazenada de forma criptografada.</li>
                <li>Registros operacionais: inscrições, certificados, logs e trilhas de auditoria.</li>
            </ul>

            <h5 class="mb-3">Direitos do titular</h5>
            <ul class="mb-4">
                <li>Confirmar a existência de tratamento e acessar seus dados.</li>
                <li>Solicitar correção, atualização, portabilidade e eliminação/anomização quando aplicável.</li>
                <li>Revogar consentimento, quando o tratamento se basear nele, respeitadas as bases legais e retenções.</li>
            </ul>

            <h5 class="mb-3">Retenção</h5>
            <p class="mb-4">
                Os dados são mantidos pelo período necessário para cumprir as finalidades do sistema e obrigações legais.
                Após o prazo aplicável, os dados poderão ser eliminados ou anonimizados quando permitido.
            </p>

            <h5 class="mb-3">Contato institucional</h5>
            <p class="mb-0">
                Para solicitações relacionadas a privacidade e dados pessoais, utilize o canal oficial da instituição responsável.
            </p>
        </div>
    </div>
</div>

