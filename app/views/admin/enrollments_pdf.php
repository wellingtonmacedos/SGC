<?php
if (!isset($enrollments) || !isset($course)) {
    echo 'Dados inválidos';
    exit;
}
$orgSettings = $orgSettings ?? [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Inscritos - <?php echo htmlspecialchars($course['name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { display: flex; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .logo { width: 80px; height: 80px; object-fit: contain; margin-right: 20px; }
        .org-info { flex: 1; }
        .org-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .org-details { font-size: 12px; color: #555; line-height: 1.4; }
        h1 { font-size: 18px; margin-bottom: 15px; margin-top: 20px; }
        p { margin-bottom: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; color: #333; }
        .footer { font-size: 10px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 10px; margin-top: 30px; }
        .status-badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-certificate { background-color: #cce5ff; color: #004085; }
        .status-enrolled { background-color: #fff3cd; color: #856404; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #0d6efd; color: white; border: none; border-radius: 4px;">Imprimir / Salvar como PDF</button>
    </div>

    <div class="header">
        <?php if (!empty($orgSettings['logo'])): ?>
            <img src="index.php?r=file/logo&file=<?php echo htmlspecialchars($orgSettings['logo']); ?>" alt="Logo" class="logo">
        <?php endif; ?>
        <div class="org-info">
            <?php if (!empty($orgSettings['organization_name'])): ?>
                <div class="org-name"><?php echo htmlspecialchars($orgSettings['organization_name']); ?></div>
            <?php endif; ?>
            <div class="org-details">
                <?php 
                $details = [];
                if (!empty($orgSettings['address'])) $details[] = $orgSettings['address'];
                if (!empty($orgSettings['city']) && !empty($orgSettings['state'])) $details[] = $orgSettings['city'] . ' - ' . $orgSettings['state'];
                if (!empty($orgSettings['phone'])) $details[] = 'Tel: ' . $orgSettings['phone'];
                if (!empty($orgSettings['email'])) $details[] = 'E-mail: ' . $orgSettings['email'];
                if (!empty($orgSettings['cnpj'])) $details[] = 'CNPJ: ' . $orgSettings['cnpj'];
                
                echo implode(' | ', $details);
                ?>
            </div>
        </div>
    </div>

    <h1>Lista de Inscritos</h1>
    <p>
        <strong>Curso:</strong> <?php echo htmlspecialchars($course['name']); ?><br>
        <strong>Instrutor:</strong> <?php echo htmlspecialchars($course['instructor']); ?><br>
        <strong>Período:</strong> <?php echo htmlspecialchars($course['period']); ?> 
        <?php if (!empty($course['date'])): ?> (<?php echo date('d/m/Y', strtotime($course['date'])); ?>)<?php endif; ?><br>
        <strong>Total de Inscritos:</strong> <?php echo count($enrollments); ?>
    </p>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>CPF</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($enrollments as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['cpf'] ?? '-'); ?></td>
                <td>
                    <?php 
                    $statusClass = 'status-enrolled';
                    $statusLabel = 'Inscrito';
                    
                    if ($row['status'] === 'completed') {
                        $statusClass = 'status-completed';
                        $statusLabel = 'Concluído';
                    } elseif ($row['status'] === 'certificate_available') {
                        $statusClass = 'status-certificate';
                        $statusLabel = 'Certificado Disponível';
                    }
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo $statusLabel; ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Gerado em <?php echo date('d/m/Y H:i:s'); ?>
    </div>
</body>
</html>
