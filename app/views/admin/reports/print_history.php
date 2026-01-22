<?php
$orgSettings = $orgSettings ?? [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Inscrições</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { display: flex; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .logo { width: 80px; height: 80px; object-fit: contain; margin-right: 20px; }
        .org-info { flex: 1; }
        .org-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .org-details { font-size: 12px; color: #555; line-height: 1.4; }
        h1 { font-size: 18px; margin-bottom: 15px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; color: #333; }
        .footer { font-size: 10px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 10px; margin-top: 30px; }
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

    <h1>Histórico de Inscrições</h1>
    <p>
        <strong>Período:</strong> <?php echo date('d/m/Y', strtotime($startDate)); ?> a <?php echo date('d/m/Y', strtotime($endDate)); ?><br>
        <strong>Média Diária:</strong> <?php echo number_format($average, 2, ',', '.'); ?>
    </p>

    <table>
        <thead>
            <tr>
                <th>Período</th>
                <th>Curso</th>
                <th>Total de Inscrições</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalPeriod = 0;
            foreach ($data as $row): 
                $totalPeriod += $row['total'];
            ?>
            <tr>
                <td><?php echo $row['period']; ?></td>
                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                <td><?php echo $row['total']; ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="2">Total no Período</td>
                <td><?php echo $totalPeriod; ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Gerado pelo Sistema de Gestão de Cursos
    </div>
</body>
</html>
