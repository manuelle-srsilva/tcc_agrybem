<?php

session_start();
require_once '../vendor/autoload.php';

use Controller\InstituicaoController;

$instituicaoController = new InstituicaoController();

$registerMessage = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['cnpj'], $_POST['link_whatsapp'])) {

        if ($_POST['senha'] !== $_POST['confimacao_senha']) {
            $registerMessage = "As senhas não coincidem. Tente novamente.";
        }
        else{
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $senha = $_POST['senha'];
            $cnpj = $_POST['cnpj'];
            $link_whatsapp = $_POST['link_whatsapp'];

            if($instituicaoController->getInstituicaoByCNPJ($cnpj)) {
                $registerMessage = 'Já existe um usuário cadastrado com essa identificação (CNPJ/CPF)';
            }
            else {
                if($instituicaoController->salvarInstituicao($nome, $email, $senha, $cnpj, $link_whatsapp)) {
                    header('Location: instituicao_endereco.php');
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
                <a href="../View/instituicao_login.php" class="nav-button">Voltar</a>
            </nav>

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
        </div>

        

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Botão Anterior (Esquerda) -->
       
            

            <!-- Formulário -->
            <div class="form-container">
                <h1 class="form-title">Cadastre sua conta aqui!</h1>
                
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome da instituição</label>
                        <input type="text" id="nome" name="nome" class="form-input" placeholder="">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">E-mail Institucional</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="">
                    </div>

                     <div class="form-group">
                        <label for="cnpj" class="form-label">CNPJ  </label>
                        <input type="text" id="cepfcnpj" name="cnpj" class="form-input" placeholder="">
                    </div>

                     <div class="form-group">
                        <label for="Link" class="form-label">Links para contato</label>
                        <input type="text" id="link" name="link_whatsapp" class="form-input" placeholder="">
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="senha" class="form-label">senha</label>
                        <input type="password" id="senha" name="senha" class="form-input" placeholder="">
                    </div>
                    
                    <div class="form-group">
                        <label for="senha" class="form-label">Confirmação de senha</label>
                        <input type="password" id="senha" name="confimacao_senha" class="form-input" placeholder="">
                    </div>

                    <!-- BOTÃO OK -->
                    <div class="form-group form-button-ok">
                        <button type="submit" class="ok-button">OK</button>
                    </div>

                    <p> <?php echo $registerMessage; ?> </p>
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