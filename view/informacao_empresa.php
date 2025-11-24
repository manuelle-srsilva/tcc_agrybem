<?php

session_start();
require_once '../vendor/autoload.php';

use Controller\EmpreendimentoController;

$empreendimentoController = new EmpreendimentoController();

$registerMessage = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['nome'], $_POST['telefone'], $_POST['link_whatsapp'], $_POST['hr_funcionamento'])) {
        $nome = $_POST['nome'];
        $telefone = $_POST['telefone'];
        $link_whatsapp = $_POST['link_whatsapp'];
        $hr_funcionamento = $_POST['hr_funcionamento'];

        if($empreendimentoController->salvarEmpreendimentoInfo($nome, $telefone, $link_whatsapp, $hr_funcionamento)) {
                header('Location: endereco_empresa.php');
                exit();
        }
        else {
            $registerMessage = 'Erro ao registrar usuário';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações do Negócio</title>
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
               
            </div>
        </header>

        <!-- Barra de Progresso -->
        <div class="progress-bar-container">
            <div class="progress-step active">
                <div class="progress-fill"></div>
            </div>
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
           

            

            <!-- Formulário -->
            <div class="form-container">
                <h1 class="form-title">Informações do seu negócio!</h1>
                
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome da empresa </label>
                        <input type="text" id="nome" name="nome" class="form-input" placeholder="">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Telefone comercial</label>
                        <input type="text" id="email" name="telefone" class="form-input" placeholder="">
                    </div>

                    <div class="form-group">
                        <label for="cnpj" class="form-label">Horário de funcionamento</label>
                        <input type="text" id="cnpj" name="hr_funcionamento" class="form-input" placeholder="">
    
                    </div>

                    <div class="form-group">
                        <label for="senha" class="form-label">Link para WhatsApp</label>
                        <input type="text" id="senha" name="link_whatsapp" class="form-input" placeholder="">
                    </div>
                        <!-- BOTÃO OK -->
                    <div class="form-group form-button-ok">
                        <button type="submit" class="ok-button">OK</button>
                    </div>

        
                </form>
            </div>

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