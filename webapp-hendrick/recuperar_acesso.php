<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

$mensagem_erro = '';
$mensagem_sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_login = limpar_entrada($_POST['usuario_login'] ?? '');
    $senha_atual = trim($_POST['senha_atual'] ?? '');
    $senha_nova = trim($_POST['senha_nova'] ?? '');
    $senha_confirmacao = trim($_POST['senha_confirmacao'] ?? '');

    if (empty($usuario_login) || empty($senha_atual) || empty($senha_nova) || empty($senha_confirmacao)) {
        $mensagem_erro = "Preencha todos os campos.";
    } elseif ($senha_nova !== $senha_confirmacao) {
        $mensagem_erro = "A nova senha e a confirmação não coincidem.";
    } elseif (strlen($senha_nova) < 6) {
        $mensagem_erro = "A nova senha deve ter pelo menos 6 caracteres.";
    } else {
        $consulta = $conexao->prepare("SELECT * FROM usuarios WHERE login = ?");
        $consulta->bind_param("s", $usuario_login);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $dados_usuario = $resultado->fetch_assoc();
        $consulta->close();

        if ($dados_usuario && password_verify($senha_atual, $dados_usuario['senha'])) {
            $hash_nova = password_hash($senha_nova, PASSWORD_DEFAULT);
            $atualizar = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE login = ?");
            $atualizar->bind_param("ss", $hash_nova, $usuario_login);
            
            if ($atualizar->execute()) {
                $mensagem_sucesso = "Senha alterada com sucesso!";
                header("refresh:2;url=index.php?msg_sucesso=" . urlencode($mensagem_sucesso));
            } else {
                $mensagem_erro = "Erro ao alterar senha. Tente novamente.";
            }
            $atualizar->close();
        } else {
            $mensagem_erro = "Usuário ou senha atual incorretos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="bg-gradient">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-key text-primary" style="font-size: 3rem;"></i>
                            <h3 class="fw-bold mt-3">Recuperar Senha</h3>
                            <p class="text-muted">Altere sua senha de acesso</p>
                        </div>

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

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Usuário</label>
                                <input type="text" class="form-control" name="usuario_login" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha Atual</label>
                                <input type="password" class="form-control" name="senha_atual" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" name="senha_nova" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" name="senha_confirmacao" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-check-circle me-2"></i>Alterar Senha
                            </button>
                            <div class="text-center">
                                <a href="index.php" class="text-decoration-none">
                                    <small><i class="bi bi-arrow-left me-1"></i>Voltar para o login</small>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
