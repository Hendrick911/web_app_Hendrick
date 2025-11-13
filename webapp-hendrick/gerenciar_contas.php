<?php
require_once 'config/helpers.php';
require_once 'config/database.php';

if (!usuario_autenticado() || $_SESSION['nivel_acesso'] != '0') {
    redirecionar('dashboard.php');
}

$mensagem_erro = '';
$mensagem_sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_conta'])) {
    $usuario_login = limpar_entrada($_POST['usuario_login'] ?? '');
    $nome_completo = limpar_entrada($_POST['nome_completo'] ?? '');
    $nivel_conta = $_POST['nivel_conta'] ?? '1';
    $senha_inicial = '123456';
    
    if (empty($usuario_login) || empty($nome_completo)) {
        $mensagem_erro = "Preencha todos os campos.";
    } else {
        $verificar = $conexao->prepare("SELECT * FROM usuarios WHERE login = ?");
        $verificar->bind_param("s", $usuario_login);
        $verificar->execute();
        $existe = $verificar->get_result()->fetch_assoc();
        $verificar->close();
        
        if ($existe) {
            $mensagem_erro = "Este usuário já está cadastrado.";
        } else {
            $hash_senha = password_hash($senha_inicial, PASSWORD_DEFAULT);
            $inserir = $conexao->prepare("INSERT INTO usuarios (login, senha, nome, tipo, qtde_acesso, status) VALUES (?, ?, ?, ?, 0, 'A')");
            $inserir->bind_param("ssss", $usuario_login, $hash_senha, $nome_completo, $nivel_conta);
            
            if ($inserir->execute()) {
                $mensagem_sucesso = "Conta criada com sucesso! Senha inicial: 123456";
            } else {
                $mensagem_erro = "Erro ao criar conta.";
            }
            $inserir->close();
        }
    }
}

if (isset($_GET['desbloquear_conta'])) {
    $usuario_desbloquear = limpar_entrada($_GET['desbloquear_conta']);
    $atualizar = $conexao->prepare("UPDATE usuarios SET status = 'A' WHERE login = ?");
    $atualizar->bind_param("s", $usuario_desbloquear);
    if ($atualizar->execute()) {
        $mensagem_sucesso = "Conta desbloqueada com sucesso!";
    }
    $atualizar->close();
}

$lista_contas = $conexao->query("SELECT * FROM usuarios ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
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
                        <a class="nav-link" href="gerenciar_eventos.php">
                            <i class="bi bi-calendar-event me-1"></i>Gerenciar Eventos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="gerenciar_contas.php">
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
        <h2 class="fw-bold mb-4"><i class="bi bi-people me-2"></i>Gerenciar Usuários</h2>
        
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
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Criar Nova Conta</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Usuário</label>
                        <input type="text" class="form-control" name="usuario_login" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" name="nome_completo" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nível de Acesso</label>
                        <select class="form-select" name="nivel_conta" required>
                            <option value="1">Usuário Comum</option>
                            <option value="0">Administrador</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="criar_conta" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Criar Conta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Contas Cadastradas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Usuário</th>
                                <th>Nome</th>
                                <th>Nível</th>
                                <th>Acessos</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($conta = $lista_contas->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($conta['login']) ?></td>
                                    <td><?= htmlspecialchars($conta['nome']) ?></td>
                                    <td>
                                        <?php if ($conta['tipo'] == '0'): ?>
                                            <span class="badge bg-warning text-dark">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Comum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $conta['qtde_acesso'] ?></td>
                                    <td>
                                        <?php if ($conta['status'] == 'A'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-lock me-1"></i>Bloqueado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($conta['status'] == 'B'): ?>
                                            <a href="?desbloquear_conta=<?= urlencode($conta['login']) ?>" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-unlock"></i> Desbloquear
                                            </a>
                                        <?php endif; ?>
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
</body>
</html>
