<?php
require_once 'config/helpers.php';
require_once 'config/database.php';

if (!usuario_autenticado()) {
    redirecionar('index.php');
}

$usuario_id = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['nome_completo'];
$tipo_usuario = $_SESSION['nivel_acesso'];

$consulta = "SELECT a.*, 
        (SELECT COUNT(*) FROM reservas WHERE id_evento = a.id AND status = 'A') as eventos_ativos
        FROM eventos a 
        ORDER BY a.data ASC, a.hora ASC";
$resultado_eventos = $conexao->query($consulta);

$consulta_reservas = $conexao->prepare("SELECT r.*, a.nome as nome_evento, a.data, a.hora 
                        FROM reservas r 
                        JOIN eventos a ON r.id_evento = a.id 
                        WHERE r.login_usuario = ? 
                        ORDER BY a.data ASC");
$consulta_reservas->bind_param("s", $usuario_id);
$consulta_reservas->execute();
$minhas_inscricoes = $consulta_reservas->get_result();
$consulta_reservas->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Eventos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="bg-light">
    <!-- Navbar moderna com Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="dashboard.php">
                <i class="bi bi-calendar-check me-2"></i>Sistema de Eventos
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Adicionado link para Minhas Inscrições -->
                    <li class="nav-item">
                        <a class="nav-link" href="minhas_inscricoes.php">
                            <i class="bi bi-bookmark-check me-1"></i>Minhas Inscrições
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($nome_usuario) ?>
                            <?php if ($tipo_usuario == '0'): ?>
                                <span class="badge bg-warning text-dark ms-2">Admin</span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <?php if ($tipo_usuario == '0'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="gerenciar_eventos.php">
                                <i class="bi bi-calendar-event me-1"></i>Gerenciar Eventos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gerenciar_contas.php">
                                <i class="bi bi-people me-1"></i>Gerenciar Usuários
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="processar/sair.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <?php if (isset($_GET['msg_sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($_GET['msg_sucesso']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['msg_erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['msg_erro']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <h2 class="mb-4 fw-bold"><i class="bi bi-calendar3 me-2"></i>Eventos Disponíveis</h2>

        <div class="row g-4">
            <?php if ($resultado_eventos->num_rows > 0): ?>
                <?php while($evento = $resultado_eventos->fetch_assoc()): ?>
                    <?php
                    $vagas_livres = $evento['capacidade'] - $evento['eventos_ativos'];
                    $data_brasil = date('d/m/Y', strtotime($evento['data']));
                    $hora_brasil = date('H:i', strtotime($evento['hora']));
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card evento-card h-100 shadow-sm">
                            <?php if (!empty($evento['imagem']) && file_exists($evento['imagem'])): ?>
                                <img src="<?= htmlspecialchars($evento['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($evento['nome']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-primary bg-gradient text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-calendar-event" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($evento['nome']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($evento['descricao']) ?></p>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i><?= htmlspecialchars($evento['local']) ?></li>
                                    <li class="mb-2"><i class="bi bi-calendar text-primary me-2"></i><?= $data_brasil ?> às <?= $hora_brasil ?></li>
                                    <li><i class="bi bi-people text-primary me-2"></i><?= $vagas_livres ?> vagas disponíveis</li>
                                </ul>
                            </div>
                            <div class="card-footer bg-white border-0 pb-3">
                                <?php if ($vagas_livres > 0): ?>
                                    <a href="confirmar_inscricao.php?id_evento=<?= $evento['id'] ?>" class="btn btn-primary w-100">
                                        <i class="bi bi-check-circle me-2"></i>Fazer Inscrição
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-x-circle me-2"></i>Esgotado
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>Nenhum evento disponível no momento.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Removida seção "Minhas Inscrições" -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</body>
</html>
