<?php
if (!isset($enrollments) || !isset($course)) {
    echo 'Dados inválidos';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Inscritos - <?php echo htmlspecialchars($course['name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        p { margin-bottom: 20px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { font-size: 10px; color: #999; text-align: right; border-top: 1px solid #eee; padding-top: 10px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()">Imprimir / Salvar como PDF</button>
    </div>

    <h1>Lista de Inscritos</h1>
    <p>
        <strong>Curso:</strong> <?php echo htmlspecialchars($course['name']); ?><br>
        <strong>Instrutor:</strong> <?php echo htmlspecialchars($course['instructor']); ?><br>
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
            <?php foreach ($enrollments as $enrollment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($enrollment['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($enrollment['email']); ?></td>
                    <td><?php echo htmlspecialchars($enrollment['cpf'] ?? '-'); ?></td>
                    <td>
                        <?php
                        echo match($enrollment['status']) {
                            'certificate_available' => 'Certificado disponível',
                            'completed' => 'Concluído',
                            default => 'Inscrito'
                        };
                        ?>
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
