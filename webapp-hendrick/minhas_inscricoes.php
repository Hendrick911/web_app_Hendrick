<?php
require_once 'config/helpers.php';
require_once 'config/database.php';

if (!usuario_autenticado()) {
    redirecionar('index.php');
}

$usuario_id = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['nome_completo'];
$tipo_usuario = $_SESSION['nivel_acesso'];

$consulta_reservas = $conexao->prepare("SELECT r.*, a.nome as nome_evento, a.data, a.hora, a.local, a.descricao
                        FROM reservas r 
                        JOIN eventos a ON r.id_evento = a.id 
                        WHERE r.login_usuario = ? 
                        ORDER BY a.data ASC, a.hora ASC");
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
    <title>Minhas Inscrições - Sistema de Eventos</title>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="minhas_inscricoes.php">
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-bookmark-check me-2"></i>Minhas Inscrições</h2>
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Voltar aos Eventos
            </a>
        </div>
        
        <?php if ($minhas_inscricoes->num_rows > 0): ?>
            <div class="row g-4">
                <?php while($inscricao = $minhas_inscricoes->fetch_assoc()): ?>
                    <?php
                    $data_brasil = date('d/m/Y', strtotime($inscricao['data']));
                    $hora_brasil = date('H:i', strtotime($inscricao['hora']));
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title fw-bold mb-0"><?= htmlspecialchars($inscricao['nome_evento']) ?></h5>
                                    <?php if ($inscricao['status'] == 'A'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Ativa</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Cancelada</span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text text-muted small"><?= htmlspecialchars($inscricao['descricao']) ?></p>
                                <ul class="list-unstyled mb-3">
                                    <li class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i><?= htmlspecialchars($inscricao['local']) ?></li>
                                    <li class="mb-2"><i class="bi bi-calendar text-primary me-2"></i><?= $data_brasil ?></li>
                                    <li><i class="bi bi-clock text-primary me-2"></i><?= $hora_brasil ?></li>
                                </ul>
                            </div>
                            <?php if ($inscricao['status'] == 'A'): ?>
                                <div class="card-footer bg-white border-0 pb-3">
                                    <a href="processar/processar_cancelamento.php?id_inscricao=<?= $inscricao['id'] ?>" 
                                       class="btn btn-outline-danger w-100 cancelar-inscricao">
                                        <i class="bi bi-trash me-2"></i>Cancelar Inscrição
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">Nenhuma inscrição encontrada</h4>
                <p class="text-muted">Você ainda não se inscreveu em nenhum evento.</p>
                <a href="dashboard.php" class="btn btn-primary mt-3">
                    <i class="bi bi-calendar-event me-2"></i>Ver Eventos Disponíveis
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.cancelar-inscricao').click(function(e) {
                if (!confirm('Tem certeza que deseja cancelar esta inscrição?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
