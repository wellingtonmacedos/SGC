<?php
use App\Core\Auth;

if (!isset($user)) {
    $user = Auth::user();
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatDateBr(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    $date = \DateTime::createFromFormat('Y-m-d', substr($value, 0, 10));
    if ($date === false) {
        return $value;
    }
    return $date->format('d/m/Y');
}

function formatTimeBr(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }
    $time = \DateTime::createFromFormat('H:i:s', $value);
    if ($time === false) {
        $time = \DateTime::createFromFormat('H:i', $value);
    }
    if ($time === false) {
        return $value;
    }
    return $time->format('H:i');
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - SGC' : 'SGC'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #0d1b2a;
            --sidebar-hover: #1b263b;
            --accent-color: #415a77;
        }
        body {
            background-color: #f0f2f5;
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar .user-profile {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar .user-avatar {
            width: 80px;
            height: 80px;
            background: #fff;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sidebar-bg);
            font-size: 2.5rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 25px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--sidebar-hover);
            color: #fff;
            border-left-color: #fff;
        }
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: all 0.3s;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
        }
        /* Card Styles */
        .stat-card {
            border: none;
            border-radius: 12px;
            padding: 25px;
            color: white;
            height: 100%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card-blue { background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%); }
        .stat-card-green { background: linear-gradient(135deg, #2a9d8f 0%, #264653 100%); }
        .stat-card-red { background: linear-gradient(135deg, #e63946 0%, #d62828 100%); }
        .stat-card-orange { background: linear-gradient(135deg, #f09819 0%, #edde5d 100%); color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .stat-card-purple { background: linear-gradient(135deg, #8e44ad 0%, #c0392b 100%); }
        .stat-card-info { background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%); }
        
        .report-card {
            border: none;
            border-radius: 10px;
            transition: all 0.3s;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            height: 100%;
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .report-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--accent-color);
        }
        
        /* Card Hover Effects */
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .btn-hover-effect {
            transition: all 0.3s;
        }
        .btn-hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        .width-20 {
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<?php if ($user): ?>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="user-profile">
            <div class="user-avatar" style="overflow: hidden; padding: 0;">
                <?php if (!empty($user['photo'])): ?>
                    <img src="index.php?r=file/photo&file=<?php echo urlencode($user['photo']); ?>" alt="Foto de Perfil" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <img src="assets/img/default-user.png" alt="Foto Padrão" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.parentNode.innerHTML='<i class=\'fas fa-user-secret\'></i>';">
                <?php endif; ?>
            </div>
            <div class="fw-bold mb-1"><?php echo e($user['name']); ?></div>
            <div class="small opacity-75 text-truncate"><?php echo e($user['email']); ?></div>
        </div>
        
        <nav class="nav flex-column">
            <?php 
            $route = $_GET['r'] ?? ''; 
            $section = $_GET['section'] ?? ''; 
            ?>
            
            <?php if ($user['role'] === 'candidate'): ?>
                <a class="nav-link <?php echo $route === 'candidate/dashboard' ? 'active' : ''; ?>" href="index.php?r=candidate/dashboard">
                    <i class="fas fa-desktop"></i> Painel
                </a>
                <a class="nav-link <?php echo $route === 'candidate/enrollments' ? 'active' : ''; ?>" href="index.php?r=candidate/enrollments">
                    <i class="fas fa-list"></i> Minhas Inscrições
                </a>
                <a class="nav-link <?php echo $route === 'candidate/certificates' ? 'active' : ''; ?>" href="index.php?r=candidate/certificates">
                    <i class="fas fa-certificate"></i> Meus Certificados
                </a>
                <a class="nav-link <?php echo $route === 'candidate/profile' ? 'active' : ''; ?>" href="index.php?r=candidate/profile">
                    <i class="fas fa-user-edit"></i> Editar Perfil
                </a>
            <?php elseif ($user['role'] === 'admin'): ?>
                <a class="nav-link <?php echo $route === 'admin/dashboard' ? 'active' : ''; ?>" href="index.php?r=admin/dashboard">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a class="nav-link <?php echo $route === 'admin/courses' ? 'active' : ''; ?>" href="index.php?r=admin/courses">
                    <i class="fas fa-book"></i> Cursos
                </a>
                <a class="nav-link <?php echo $route === 'admin/candidates' ? 'active' : ''; ?>" href="index.php?r=admin/candidates">
                    <i class="fas fa-users"></i> Candidatos
                </a>
                <a class="nav-link <?php echo $route === 'admin/enrollments' ? 'active' : ''; ?>" href="index.php?r=admin/enrollments">
                    <i class="fas fa-clipboard-check"></i> Inscrições
                </a>
                <a class="nav-link <?php echo $route === 'admin/certificates' ? 'active' : ''; ?>" href="index.php?r=admin/certificates">
                    <i class="fas fa-certificate"></i> Certificados
                </a>
                <a class="nav-link <?php echo strpos($route, 'report/') === 0 ? 'active' : ''; ?>" href="index.php?r=report/dashboard">
                    <i class="fas fa-chart-pie"></i> Relatórios
                </a>
                <a class="nav-link <?php echo $route === 'organization' ? 'active' : ''; ?>" href="index.php?r=organization">
                    <i class="fas fa-building"></i> Configurações do Órgão
                </a>
            <?php endif; ?>
            
            <a class="nav-link mt-4" href="index.php?r=auth/logout">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="top-bar d-md-flex d-none">
            <h4 class="mb-0"><i class="fas fa-desktop me-2"></i> Painel</h4>
            <div class="d-flex align-items-center text-muted">
                <span class="me-2">Olá, <?php echo e($user['name']); ?></span>
                <i class="fas fa-user-circle fa-lg"></i>
            </div>
        </div>
        
        <!-- Mobile Toggle -->
        <div class="d-md-none mb-3 d-flex justify-content-between align-items-center">
            <button class="btn btn-dark" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
            <span class="fw-bold">SGC</span>
        </div>

        <?php require $viewFile; ?>
    </div>

<?php else: ?>
    <!-- Guest Layout -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5">
        <div class="container">
            <a class="navbar-brand" href="index.php">SGC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?r=auth/login">Entrar</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?r=auth/register">Cadastro</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container">
        <?php require $viewFile; ?>
    </main>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
