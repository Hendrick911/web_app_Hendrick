<?php
require_once 'config/database.php'; 
require_once 'config/helpers.php';

if (!usuario_autenticado()) {
    redirecionar('index.php');
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem_erro = "";
$mensagem_sucesso = "";

$consulta = $conexao->prepare("SELECT qtde_acesso FROM usuarios WHERE login = ?");
$consulta->bind_param("s", $usuario_id);
$consulta->execute();
$resultado = $consulta->get_result();
$dados_conta = $resultado->fetch_assoc();

$exibir_formulario = ($dados_conta['qtde_acesso'] == 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $senha_nova = trim($_POST['senha_nova'] ?? '');
    $senha_confirmacao = trim($_POST['senha_confirmacao'] ?? '');

    if (empty($senha_nova) || empty($senha_confirmacao)) {
        $mensagem_erro = "Preencha todos os campos.";
    } elseif ($senha_nova !== $senha_confirmacao) {
        $mensagem_erro = "As senhas não coincidem!";
    } elseif (strlen($senha_nova) < 6) {
        $mensagem_erro = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $hash_senha = password_hash($senha_nova, PASSWORD_DEFAULT);
        $atualizar = $conexao->prepare("UPDATE usuarios SET senha = ?, qtde_acesso = qtde_acesso + 1 WHERE login = ?");
        $atualizar->bind_param("ss", $hash_senha, $usuario_id);

        if ($atualizar->execute()) {
            $mensagem_sucesso = "Senha definida com sucesso! Redirecionando...";
            header("refresh:2;url=dashboard.php");
        } else {
            $mensagem_erro = "Erro ao atualizar senha. Tente novamente.";
        }
        $atualizar->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Primeiro Acesso - Definir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="bg-gradient">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <?php if ($exibir_formulario): ?> 
                    <div class="card shadow-lg border-0 rounded-4">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="bi bi-shield-lock text-primary" style="font-size: 3rem;"></i>
                                <h3 class="fw-bold mt-3">Primeiro Acesso</h3>
                                <p class="text-muted">Olá, <?= htmlspecialchars($_SESSION['nome_completo']) ?>! Por favor, defina uma nova senha.</p>
                            </div>

                            <?php if ($mensagem_sucesso): ?>
                                <div class="alert alert-success" role="alert">
                                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($mensagem_sucesso) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($mensagem_erro): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($mensagem_erro) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control" name="senha_nova" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control" name="senha_confirmacao" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle me-2"></i>Salvar Nova Senha
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-lg border-0 rounded-4">
                        <div class="card-body p-5 text-center">
                            <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                            <h3 class="fw-bold mt-3">Acesso Negado</h3>
                            <p class="text-muted">Você já definiu sua senha anteriormente.</p>
                            <a href="dashboard.php" class="btn btn-primary">Ir para o Dashboard</a>
                        </div>
                    </div> 
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
