<?php

$dbhost = 'localhost';  
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'web_app';


$conexao = new mysqli($dbhost, $dbUsername, $dbPassword, $dbName);


if ($conexao->connect_errno) {
    echo "Erro na conexão: " . $conexao->connect_error; 
} else {
    echo "Conexão efetuada com sucesso";
}

?>
