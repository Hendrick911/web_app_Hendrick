<?php
    if (isset($_POST['submit'])){
        include_once('config.php');
        $ra = $_POST['ra'];
        $senha = $_POST['senha'];

        $resultado = mysqli_query($conexao,'SELECT * FROM tabela WHERE ra="'.$ra.'"AND senha ="'.$senha.'"');
        if (mysqli_num_rows($resultado)>0){
            echo "<script>alert('você foi logado')</script>";
        }
    }
    if (isset($_POST['cadastrar'])){
        header('Location: formulario-a.php');
    }
?>

<!DOCTYPE html> <!-- Declaração do tipo de documento -->
<html lang="en">
<head>
    <!-- Configurações básicas da página -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela de Login</title>

    <!-- Estilização da página -->
    <style>
        /* Estilo do corpo da página */
        body{
            font-family: Arial, Helvetica, sans-serif;
            background-image: linear-gradient(45deg, rgb(40, 187, 187), rgb(34, 48, 238));
        }

        /* Estilo da caixa central de login */
        div{
            background-color: rgba(0, 0, 0, 0.8);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 60px;
            border-radius: 15px;
            color: #fff;
        }

        /* Estilo dos campos de entrada */
        input{
            padding: 15px;
            border: none;
            outline: none;
            font-size: 15px;
        }

        /* Estilo do botão */
        button{
            background-color: dodgerblue;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 10px;
            color: white;
            font-size: 15px;
        }

        /* Efeito ao passar o mouse no botão */
        button:hover{
            background-color: deepskyblue;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <form action="" method='POST'>
        <!-- Estrutura da tela de login -->
        <div>
            <h1>Login</h1>
            <input type="text" id='ra' name ='ra' placeholder="RA">
            <br><br>
            <input type="password" id='senha' name='senha' placeholder="Senha">
            <br><br>
            <button type='submit' name ='submit' id='submit'>Entrar</button>
            </form>
            <br><br>
                <button id='cadastrar' name = 'cadastrar'>
                    Cadastrar
                </button>
        </div>
    
</body>
</html>