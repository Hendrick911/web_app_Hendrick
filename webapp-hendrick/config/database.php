<?php
$host_servidor = "localhost";
$usuario_bd = "root";
$senha_bd = "";
$nome_banco = "bd_crud_php";

$conexao = new mysqli($host_servidor, $usuario_bd, $senha_bd, $nome_banco);

if ($conexao->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conexao->connect_error);
}

$conexao->set_charset("utf8");
?>
