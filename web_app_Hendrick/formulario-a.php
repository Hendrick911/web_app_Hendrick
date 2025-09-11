<?php

    if(isset($_POST['submit']))
    {
        // print_r('Nome: ' . $_POST['nome']);
       // print_r('<br>');
       // print_r('RA: ' . $_POST['ra']);
       // print_r('<br>');
       // print_r('Digito: ' . $_POST['digito']);
       // print_r('<br>');
       // print_r('Senha: ' . $_POST['senha']);
       // print_r('<br>');
       // print_r('Csenha: ' . $_POST['csenha']);
       // print_r('<br>');
       // print_r('Escola: ' . $_POST['escola']);

       include_once('config.php');

       $nome = $_POST['nome'];
       $ra = $_POST['ra'];
       $digito = $_POST['digito'];
       $senha = $_POST['senha'];
       $escola = $_POST['escola'];

       $result = mysqli_query($conexao, "INSERT INTO tabela(nome,ra,digito,senha,escola) VALUES ('$nome','$ra','$digito','$senha','$escola')");
    }

?>
<!DOCTYPE html>
<html lang="en"> <!-- Define o tipo de documento e a linguagem -->
<head>
    <meta charset="UTF-8"> <!-- Define a codificação de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Deixa o site responsivo -->
    <title>Cadastro</title> <!-- Título da aba do navegador -->
    <style>
        /* Estilo do corpo da página */
        body{
            font-family: Arial, Helvetica, sans-serif; /* Fonte padrão */
            background-image: linear-gradient(to right, rgb(20, 147, 220),rgb(17, 54, 71)); /* Fundo com gradiente */
        }

        /* Caixa central do formulário */
        .box{
            color: white; /* Cor do texto */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%); /* Centraliza a caixa na tela */
            background-color: rgba(0, 0, 0, 0.6); /* Fundo preto semi-transparente */
            padding: 15px;
            border-radius: 15px; /* Bordas arredondadas */
            width: 25%; /* Largura da caixa */
        }

        /* Borda do formulário */
        fieldset{
            border: 3px solid dodgerblue; /* Borda azul */
        }

        /* Título dentro da borda */
        legend{
            border: 1px solid dodgerblue;
            padding: 10px;
            text-align: center;
            background-color: dodgerblue;
            border-radius: 5px;
        }

        /* Container para inputs */
        .inputBox{
            position: relative;
        }

        /* Estilo dos campos de entrada */
        .inputUser{
            background: none; /* Sem fundo */
            border: none; /* Sem borda */
            border-bottom: 1px solid white; /* Apenas linha inferior */
            outline: none; /* Remove a borda padrão ao clicar */
            color: white;
            font-size: 15px;
            width: 100%;
            letter-spacing: 2px; /* Espaçamento entre letras */
        }

        /* Estilo dos rótulos (labels) */
        .labelInput{
            position: absolute;
            top: 0px;
            left: 0px;
            pointer-events: none; /* Impede clique no label */
            transition: .5s; /* Animação suave */
        }

        /* Efeito flutuante no label quando o input está preenchido ou em foco */
        .inputUser:focus ~ .labelInput,
        .inputUser:valid ~ .labelInput{
            top: -20px;
            font-size: 12px;
            color: dodgerblue;
        }

        /* Botão de envio */
        #submit,#login{
            background-image: linear-gradient(to right,rgb(0,92,197), rgb(90, 20, 220));
            width: 100%;
            border: none;
            padding: 15px;
            color: white;
            font-size: 15px;
            cursor: pointer; /* Muda o cursor ao passar o mouse */
            border-radius: 10px;
        }

        /* Efeito hover no botão */
        #submit:hover{
            background-image: linear-gradient(to right,rgb(0,80,172), rgb(80, 19, 195));
        }
    </style>
</head>
<body>
    <!-- Caixa central -->
    <div class="box">
        <!-- Formulário de cadastro -->
        <form action="formulario-a.php" method="POST">
            <fieldset>
                <legend><b>Cadastro do Aluno</b></legend> <!-- Título do formulário -->
                <br>

                <!-- Campo Nome -->
                <div class="inputBox">
                    <input type="text" name="nome" id="nome" class="inputUser" required>
                    <label for="nome" class="labelInput">Nome Completo</label>
                </div>
                <br><br>

                <!-- Campo RA -->
                <div class="inputBox">
                    <input type="text" name="ra" id="ra" class="inputUser" required>
                    <label for="ra" class="labelInput">RA</label>
                </div>
                <br><br>

                <!-- Campo Digito -->
                <div class="inputBox">
                    <input type="text" name="digito" id="digito" class="inputUser" required>
                    <label for="digito" class="labelInput">Digito</label>
                </div>
                <br><br>

                <!-- Campo Senha -->
                <div class="inputBox">
                    <input type="text" name="senha" id="senha" class="inputUser" required>
                    <label for="senha" class="labelInput">Senha</label>
                </div>
                <br><br>

                <!-- Campo csenha -->
                <div class="inputBox">
                    <input type="text" name="csenha" id="csenha" class="inputUser" required>
                    <label for="csenha" class="labelInput">Confirmar Senha</label>
                </div>
                <br><br>

                <!-- Campo Escola -->
                <div class="inputBox">
                    <input type="text" name="escola" id="escola" class="inputUser" required>
                    <label for="escola" class="labelInput">Escola</label>
                </div>
                <br><br>

                <!-- Botão de cadastro -->
                <input type="submit" name="submit" id="submit" value="Cadastrar-se">
                <br><br>
            </form>
            <a href="login.php">
                <button id='login'>Login</button>
            </a>

            </fieldset>
    </div>
</body>
</html>