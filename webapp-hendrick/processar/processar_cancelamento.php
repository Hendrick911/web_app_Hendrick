<?php
require_once '../config/helpers.php';
require_once '../config/database.php';

if (!usuario_autenticado()) {
    redirecionar('../index.php');
}

$id_inscricao = intval($_GET['id_inscricao'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

$verificar = $conexao->prepare("SELECT * FROM reservas WHERE id = ? AND login_usuario = ?");
$verificar->bind_param("is", $id_inscricao, $usuario_id);
$verificar->execute();
$inscricao = $verificar->get_result()->fetch_assoc();
$verificar->close();

if ($inscricao && $inscricao['status'] == 'A') {
    $cancelar = $conexao->prepare("UPDATE reservas SET status = 'C' WHERE id = ?");
    $cancelar->bind_param("i", $id_inscricao);
    
    if ($cancelar->execute()) {
        redirecionar('../dashboard.php?msg_sucesso=Inscrição cancelada com sucesso!');
    } else {
        redirecionar('../dashboard.php?msg_erro=Erro ao cancelar inscrição.');
    }
    $cancelar->close();
} else {
    redirecionar('../dashboard.php?msg_erro=Inscrição não encontrada.');
}
?>
