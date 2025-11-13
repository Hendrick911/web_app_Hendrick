<?php
require_once 'config/helpers.php';
require_once 'config/database.php';

if (usuario_autenticado()) {
    redirecionar('dashboard.php');
}

$mensagem_erro = '';
$mensagem_sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_login = limpar_entrada($_POST['usuario_login'] ?? '');
    $senha_usuario = trim($_POST['senha_usuario'] ?? '');

    if (empty($usuario_login) || empty($senha_usuario)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } else {
        $consulta = $conexao->prepare("SELECT * FROM usuarios WHERE login = ?");
        $consulta->bind_param("s", $usuario_login);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $dados_usuario = $resultado->fetch_assoc();
        $consulta->close();

        if ($dados_usuario) {
            if ($dados_usuario['status'] === 'B') {
                $mensagem_erro = "Sua conta está bloqueada. Entre em contato com o administrador.";
            } elseif (password_verify($senha_usuario, $dados_usuario['senha'])) {
                $_SESSION['usuario_id'] = $dados_usuario['login'];
                $_SESSION['nome_completo'] = $dados_usuario['nome'];
                $_SESSION['nivel_acesso'] = $dados_usuario['tipo'];

                if ($dados_usuario['qtde_acesso'] == 0) {
                    redirecionar('primeira_entrada.php');
                } else {
                    $atualizar = $conexao->prepare("UPDATE usuarios SET qtde_acesso = qtde_acesso + 1 WHERE login = ?");
                    $atualizar->bind_param("s", $usuario_login);
                    $atualizar->execute();
                    $atualizar->close();
                    redirecionar('dashboard.php');
                }
            } else {
                if (!isset($_SESSION['tentativas_login_' . $usuario_login])) {
                    $_SESSION['tentativas_login_' . $usuario_login] = 0;
                }
                $_SESSION['tentativas_login_' . $usuario_login]++;

                if ($_SESSION['tentativas_login_' . $usuario_login] >= 3) {
                    $bloquear = $conexao->prepare("UPDATE usuarios SET status = 'B' WHERE login = ?");
                    $bloquear->bind_param("s", $usuario_login);
                    $bloquear->execute();
                    $bloquear->close();
                    $mensagem_erro = "Você errou a senha 3 vezes. Sua conta foi bloqueada por segurança.";
                    unset($_SESSION['tentativas_login_' . $usuario_login]);
                } else {
                    $restantes = 3 - $_SESSION['tentativas_login_' . $usuario_login];
                    $mensagem_erro = "Usuário ou senha incorretos. Tentativas restantes: $restantes";
                }
            }
        } else {
            $mensagem_erro = "Usuário ou senha incorretos.";
        }
    }
}

if (!empty($_GET['msg_sucesso'])) {
    $mensagem_sucesso = limpar_entrada($_GET['msg_sucesso']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Eventos - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="bg-gradient">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-calendar-check text-primary" style="font-size: 3rem;"></i>
                            <h3 class="fw-bold mt-3">Sistema de Eventos</h3>
                            <p class="text-muted">Faça login para continuar</p>
                        </div>

                        <?php if (!empty($mensagem_sucesso)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i><?= $mensagem_sucesso ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($mensagem_erro)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i><?= $mensagem_erro ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="formLogin">
                            <div class="mb-3">
                                <label class="form-label">Usuário</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" name="usuario_login" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" name="senha_usuario" id="senhaInput" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleSenha">
                                        <i class="bi bi-eye" id="iconeSenha"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                            </button>
                            <div class="text-center">
                                <a href="recuperar_acesso.php" class="text-decoration-none">
                                    <small>Esqueceu sua senha?</small>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle mostrar/ocultar senha
            $('#toggleSenha').click(function() {
                const senhaInput = $('#senhaInput');
                const icone = $('#iconeSenha');
                
                if (senhaInput.attr('type') === 'password') {
                    senhaInput.attr('type', 'text');
                    icone.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    senhaInput.attr('type', 'password');
                    icone.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // Animação ao submeter formulário
            $('#formLogin').submit(function() {
                $(this).find('button[type="submit"]').html('<span class="spinner-border spinner-border-sm me-2"></span>Entrando...').prop('disabled', true);
            });
        });
    </script>
</body>
</html>
