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
                <a href="../View/empresa_senha.php" class="nav-button">Voltar</a>
            </nav>

        </header>

        

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Botão Anterior (Esquerda) -->
       
            

            <!-- Formulário -->
            <div class="form-container">
                <h1 class="form-title">Insira sua nova senha!</h1>
                
                <?php
                session_start();
                require_once __DIR__ . '/../vendor/autoload.php';
                use Controller\EmpreendedorController;

                $error = '';
                if(!isset($_SESSION['reset_email'])){
                    header('Location: empresa_senha.php');
                    exit();
                }

                if($_SERVER['REQUEST_METHOD'] === 'POST'){
                    $senha = trim($_POST['senha'] ?? '');
                    $senha2 = trim($_POST['senha2'] ?? '');
                    if(empty($senha) || empty($senha2)){
                        $error = 'Preencha ambos os campos de senha.';
                    } elseif($senha !== $senha2){
                        $error = 'As senhas não coincidem.';
                    } elseif(strlen($senha) < 6){
                        $error = 'A senha deve ter ao menos 6 caracteres.';
                    } else {
                        $ctrl = new EmpreendedorController();
                        $email = $_SESSION['reset_email'];
                        $ok = $ctrl->resetPasswordByEmail($email, $senha);
                        if($ok){
                            unset($_SESSION['reset_email']);
                            header('Location: login_empresa.php?reset=1');
                            exit();
                        } else {
                            $error = 'Falha ao atualizar a senha. Tente novamente.';
                        }
                    }
                }
                ?>

                <form class="form" method="post" action="">
                    <?php if($error): ?>
                        <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" id="senha" name="senha" class="form-input" placeholder="" required>
                    </div>

                    <div class="form-group">
                        <label for="senha2" class="form-label">Confirme sua senha</label>
                        <input type="password" id="senha2" name="senha2" class="form-input" placeholder="" required>
                    </div>

<!-- BOTÃO OK -->
<div class="form-group form-button-ok">
  <button type="submit" class="ok-button">Continuar</button>
</div>
        </main>

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

