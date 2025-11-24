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
                <a href="../View/cliente_login.php" class="nav-button">Voltar</a>
            </nav>

        </header>

        

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Botão Anterior (Esquerda) -->
       
            

            <!-- Formulário -->
            <div class="form-container">
                <h1 class="form-title">Insira seus dados para recuperação de senha!</h1>
                
                <?php
                session_start();
                require_once __DIR__ . '/../vendor/autoload.php';
                use Controller\ClienteController;

                $error = '';
                $emailVal = '';
                if(
                    $_SERVER['REQUEST_METHOD'] === 'POST'
                ){
                    $emailVal = trim($_POST['email'] ?? '');
                    $emailSan = filter_var($emailVal, FILTER_VALIDATE_EMAIL);
                    if(!$emailSan){
                        $error = 'Por favor, informe um e-mail válido.';
                    } else {
                        $ctrl = new ClienteController();
                        $client = $ctrl->checkClienteByEmail($emailSan);
                        if(!$client){
                            $error = 'E-mail não encontrado. Verifique e tente novamente.';
                        } else {
                            // grava email na sessão e redireciona para redefinição
                            $_SESSION['reset_email'] = $emailSan;
                            header('Location: cliente_redefinicao_senha.php');
                            exit();
                        }
                    }
                }
                ?>
                <form class="form" method="post" action="">
                    <?php if($error): ?>
                        <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="" value="<?php echo htmlspecialchars($emailVal); ?>" required>
                    </div>

<!-- BOTÃO OK -->
<div class="form-group form-button-ok">
  <button type="submit" class="ok-button">Continuar</button>
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

