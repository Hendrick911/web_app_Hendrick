<?php
require_once 'config/helpers.php';
require_once 'config/database.php';

if (!usuario_autenticado() || $_SESSION['nivel_acesso'] != '0') {
    redirecionar('dashboard.php');
}

$mensagem_erro = '';
$mensagem_sucesso = '';

$diretorio_upload = 'uploads/agendamentos/';
if (!file_exists($diretorio_upload)) {
    mkdir($diretorio_upload, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_agendamento'])) {
    $nome_agendamento = limpar_entrada($_POST['nome_agendamento'] ?? '');
    $descricao_agendamento = limpar_entrada($_POST['descricao_agendamento'] ?? '');
    $localizacao = trim($_POST['localizacao'] ?? '');
    $data_agendamento = $_POST['data_agendamento'] ?? '';
    $hora_agendamento = $_POST['hora_agendamento'] ?? '';
    $limite_vagas = intval($_POST['limite_vagas'] ?? 0);
    
    if (empty($nome_agendamento) || empty($descricao_agendamento) || empty($localizacao) || empty($data_agendamento) || empty($hora_agendamento) || $limite_vagas <= 0) {
        $mensagem_erro = "Preencha todos os campos corretamente.";
    } else {
        $caminho_imagem = null;
        
        if (isset($_FILES['imagem_agendamento']) && $_FILES['imagem_agendamento']['error'] == 0) {
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            $nome_arquivo = $_FILES['imagem_agendamento']['name'];
            $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $novo_nome = uniqid() . '.' . $extensao;
                $caminho_imagem = $diretorio_upload . $novo_nome;
                move_uploaded_file($_FILES['imagem_agendamento']['tmp_name'], $caminho_imagem);
            }
        }
        
        $inserir = $conexao->prepare("INSERT INTO eventos (nome, descricao, local, data, hora, capacidade, imagem) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $inserir->bind_param("sssssis", $nome_agendamento, $descricao_agendamento, $localizacao, $data_agendamento, $hora_agendamento, $limite_vagas, $caminho_imagem);
        
        if ($inserir->execute()) {
            $mensagem_sucesso = "Agendamento criado com sucesso!";
        } else {
            $mensagem_erro = "Erro ao criar agendamento.";
        }
        $inserir->close();
    }
}

if (isset($_GET['remover'])) {
    $id_remover = intval($_GET['remover']);
    
    $buscar = $conexao->prepare("SELECT imagem FROM eventos WHERE id = ?");
    $buscar->bind_param("i", $id_remover);
    $buscar->execute();
    $resultado = $buscar->get_result();
    $agendamento = $resultado->fetch_assoc();
    $buscar->close();
    
    if ($agendamento && !empty($agendamento['imagem']) && file_exists($agendamento['imagem'])) {
        unlink($agendamento['imagem']);
    }
    
    $deletar = $conexao->prepare("DELETE FROM eventos WHERE id = ?");
    $deletar->bind_param("i", $id_remover);
    if ($deletar->execute()) {
        $mensagem_sucesso = "Agendamento removido com sucesso!";
    }
    $deletar->close();
}

$lista_agendamentos = $conexao->query("SELECT a.*, 
                         (SELECT COUNT(*) FROM reservas WHERE id_evento = a.id AND status = 'A') as inscricoes_ativas
                         FROM eventos a ORDER BY a.data DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Agendamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="bg-light">
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
                        <a class="nav-link" href="minhas_inscricoes.php">
                            <i class="bi bi-bookmark-check me-1"></i>Minhas Inscrições
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="gerenciar_eventos.php">
                            <i class="bi bi-calendar-event me-1"></i>Gerenciar Eventos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gerenciar_contas.php">
                            <i class="bi bi-people me-1"></i>Gerenciar Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house me-1"></i>Dashboard
                        </a>
                    </li>
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
        <h2 class="fw-bold mb-4"><i class="bi bi-calendar-event me-2"></i>Gerenciar Agendamentos</h2>
        
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($mensagem_erro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($mensagem_sucesso): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($mensagem_sucesso) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Criar Novo Agendamento</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome do Agendamento</label>
                            <input type="text" class="form-control" name="nome_agendamento" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Coordenadas (latitude,longitude)</label>
                            <input type="text" class="form-control" name="localizacao" placeholder="Ex: -23.5505,-46.6333" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="descricao_agendamento" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data</label>
                            <input type="date" class="form-control" name="data_agendamento" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Hora</label>
                            <input type="time" class="form-control" name="hora_agendamento" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Limite de Vagas</label>
                            <input type="number" class="form-control" name="limite_vagas" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagem (opcional)</label>
                        <input type="file" class="form-control" name="imagem_agendamento" accept="image/*">
                    </div>
                    <button type="submit" name="criar_agendamento" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Criar Agendamento
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Agendamentos Cadastrados</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Localização</th>
                                <th>Data/Hora</th>
                                <th>Capacidade</th>
                                <th>Inscrições</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($agendamento = $lista_agendamentos->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($agendamento['nome']) ?></td>
                                    <td><small><?= htmlspecialchars($agendamento['local']) ?></small></td>
                                    <td><?= date('d/m/Y', strtotime($agendamento['data'])) ?> às <?= date('H:i', strtotime($agendamento['hora'])) ?></td>
                                    <td><?= $agendamento['capacidade'] ?></td>
                                    <td><span class="badge bg-info"><?= $agendamento['inscricoes_ativas'] ?> / <?= $agendamento['capacidade'] ?></span></td>
                                    <td>
                                        <a href="?remover=<?= $agendamento['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger remover-agendamento">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.remover-agendamento').click(function(e) {
                if (!confirm('Tem certeza que deseja remover este agendamento?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
