<?php

session_start();
require_once '../vendor/autoload.php';

use Controller\EmpreendimentoController;

$empreendimentoController = new EmpreendimentoController();

$registerMessage = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['descricao'])) {
        $descricao = $_POST['descricao'];

        if($empreendimentoController->salvarEmpreendimentoDescricao($descricao)) {
                header('Location: foto_empresa.php');
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
    <title>Descrição do Negócio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/formulario_descricao.css">
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
            <div class="progress-step active">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-step active">
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
                <h1 class="form-title">Descrição do negócio!</h1>
                
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="nome" class="form-label-descricao">(Ex: “Produtor de frutas orgânicas de Camaçari”)</label>
                      <textarea id="nome" name="descricao" class="form-input" placeholder=""></textarea>
                    </div>
                      <!-- BOTÃO OK -->
                    <div class="form-group form-button-ok">
                        <button type="submit" class="ok-button">OK</button>
                    </div>

                   
                </form>
            </div>

        </main>

    </div>
</body>
</html>