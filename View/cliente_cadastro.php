<?php

require_once '../vendor/autoload.php';

use Controller\ClienteController;

$clienteController = new ClienteController();

$registerMessage = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['nome'], $_POST['email'], $_POST['telefone'], $_POST['senha'])) {

        if ($_POST['senha'] !== $_POST['confirmacao_senha']) {
            $registerMessage = "As senhas não coincidem. Tente novamente.";
        }
        else {
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $telefone = $_POST['telefone'];
            $senha = $_POST['senha'];

            if($clienteController->checkClienteByEmail($email)) {
                $registerMessage = 'Já existe um usuário cadastrado com esse e-mail';
            }
            else {
                if($clienteController->cadastroCliente($nome, $email, $telefone, $senha)) {
                    header('Location: cliente_login.php');
                    exit();
                }
                else {
                    $registerMessage = 'Erro ao registrar usuário';
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Empresa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/formulario.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Header Fixo -->
        <header class="header">
            <div class="container">
                <div class="logo">
                    <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
                </div>
                 <nav class="nav">  
                <a href="cliente_login.php" class="nav-button">Voltar</a>
            </nav>

        </header>

        

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Botão Anterior (Esquerda) -->
       
            

            <!-- Formulário -->
            <div class="form-container">
                <h1 class="form-title">Informações do seu produto!</h1>
                
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="nome" class="form-label">Digite seu nome </label>
                        <input type="text" id="nome" name="nome" class="form-input" placeholder="">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="">
                    </div>

                    <div class="form-group">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" class="form-input" placeholder="">
                    </div>

                      <div class="form-group">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" id="senha" name="senha" class="form-input" placeholder="">
                    </div>

                      <div class="form-group">
                        <label for="confirmacao_senha" class="form-label">Confirmação de senha</label>
                        <input type="password" id="confirmacao_senha" name="confirmacao_senha" class="form-input" placeholder="">
                    </div>
                    <div class="form-group form-button-ok">
                    <button type="submit" class="ok-button">OK</button>
                    </div>
                </form>
                <p> <?php echo $registerMessage; ?> </p>
        </main>

      
    </div>
     <!-- Api Vlibras -->
    <div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>
</body>
</html>

