<?php

session_start();
require_once '../vendor/autoload.php';

use Controller\EmpreendedorController;

$empreendedorController = new EmpreendedorController();

$registerMessage = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['cnpj_cpf'])) {

        if ($_POST['senha'] !== $_POST['confirmacao_senha']) {
            $registerMessage = "As senhas não coincidem. Tente novamente.";
        }
        else {
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $senha = $_POST['senha'];
            $cnpj_cpf = $_POST['cnpj_cpf'];

            if($empreendedorController->getEmpreendedorByCNPJ_CPF($cnpj_cpf)) {
                $registerMessage = 'Já existe um usuário cadastrado com essa identificação (CNPJ/CPF)';
            }
            else {
                if($empreendedorController->salvarEmpreendedor($nome, $email, $senha, $cnpj_cpf)) {
                    header('Location: informacao_empresa.php');
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
                    <a href="login_empresa.php" class="nav-button">Voltar ao Início</a>
                </nav>
            </div>
        </header>

        <!-- Barra de Progresso -->
        <div class="progress-bar-container">
            <div class="progress-step active">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-step">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-step">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-step">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-step">
                <div class="progress-fill"></div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <main class="main-content">

            
<div class="form-container">
                    <h1 class="form-title">Cadastre sua conta aqui!</h1>
            <!-- Formulário -->
            <form method="POST" class="form">
                
                
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome do responsável</label>
                        <input type="text" id="nome" name="nome" class="form-input" placeholder="">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">E-mail Comercial</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="">
                    </div>

                    <div class="form-group">
                        <label for="cnpj" class="form-label">CNPJ ou CPF</label>
                        <input type="number" id="cnpj" name="cnpj_cpf" class="form-input" placeholder="">
    
                    </div>

                    <div class="form-group">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" id="senha" name="senha" class="form-input" placeholder="">
                    </div>

                    <div class="form-group">
                        <label for="confirmacao-senha" class="form-label">Confirmação de senha</label>
                        <input type="password" id="confirmacao-senha" name="confirmacao_senha" class="form-input" placeholder="">
                    </div>
                      <!-- BOTÃO OK -->
                    <div class="form-group form-button-ok">
                        <button type="submit" class="ok-button">OK</button>
                    </div>

                    <p> <?php echo $registerMessage; ?> </p>
                </div>
                
             
            </form>
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