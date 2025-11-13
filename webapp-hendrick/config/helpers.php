<?php
session_start();

function usuario_autenticado(): bool {
    return !empty($_SESSION['usuario_id']);
}

function redirecionar(string $destino): void {
    header("Location: $destino");
    exit;
}

function limpar_entrada($dado) {
    return htmlspecialchars(strip_tags(trim($dado)));
}

function validar_email($email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
?>
