<?php

session_start();
require_once '../vendor/autoload.php';

use Controller\InstituicaoController;

$instituicaoController = new InstituicaoController();
$loginMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['cnpj'], $_POST['senha'])) {
        $cnpj = $_POST['cnpj'];
        $senha = $_POST['senha'];

        if ($instituicaoController->loginInstituicao($cnpj, $senha)) {
            header('Location: instituicao_pag_principal.php');
            exit();
        }
        else {
            $loginMessage = "CNPJ e/ou senha incorreto";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agrybem - Login Empresarial</title>
    <link rel="stylesheet" href="../templates/assets/css/doacao_login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
   <!-- Header -->
        <header class="header">
            <div class="container">
                <div class="logo">
                
                    <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
                </div>
                <nav class="nav">  
                    <a href="doacao_suporte.php" class="nav-link">Suporte</a>
                    <a href="../index.php" class="nav-button">Voltar ao início</a>
                </nav>
            </div>
        </header>

    </header>

   <!-- Main Section -->
    <section class="main-section">
        <img src="../templates/assets/img/login doação.png" alt="Imagem de fundo da seção principal" class="hero-img">
        <div class="container main-container">
            

            <!-- Right Side - Login Form -->
            <div class="right-content">
                <div class="login-box">
                    <h2>Conecte-se à sua conta institucional </h2>
                    <p class="subtitle">Agrybem para o bem!</p>
                    
                    <form method="POST" class="login-form">
                        <div class="form-group">
                            <label for="cnpj">Digite seu CNPJ</label>
                            <input type="text" id="cnpj" name="cnpj" placeholder="">
                        </div>
                        
                        <div class="form-group">  
                            <label for="senha">Digite sua Senha</label>
                            <input type="password" id="senha" name="senha" placeholder="">
                            <div class="recuperacao-senha"><a href="../View/doacao_senha.php">Esqueceu a senha?</a></div>
                        </div>
                        
                        <button type="submit" class="btn-entrar">ENTRAR</button>
                        
                        <p class="cadastro-link">
                            Não tem conta ainda? <a href="../View/instituicao_cadastro.php">Cadastre-se</a>
                        </p>
                    </form>

                    <p style="height: 15px; font-size: 12px; color: black;"><?php echo $loginMessage;?></p>
                </div>
            </div>
        </div>
    </section>
<!-- Footer fixo para todas as pag -->
    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-logo">
                
                <span class="footer-logo-text">Agry<span class="footer-logo-highlight">bem</span></span>
            </div>
            <div class="footer-tagline">
                Mais que produção, uma relação com você!
            </div>
        </div>
    </footer>
  

     <!-- Seção de Copyright (Nova) -->
    <section class="copyright-section">
        <div class="container copyright-container">
            <!-- Coluna 1: Copyright e Direitos -->
            <div class="copyright-info">
                <p class="copyright-text">
                    &copy;  2025 - Agrybem 
                </p>
                <p class="copyright-text-small">
                    Agrybem Agência de Negócios Agrícolas e Bem-Estar 
                </p>
            </div>


            <!-- Coluna 2: CNPJ e Endereço (Adaptado para o contexto Agrybem) -->
            <div class="copyright-address">
                <p class="copyright-text-small">
                    Camaçari/BA  | agrybem@gmail.com
                </p>
            </div>


            <!-- Coluna 3: Ícones Sociais -->
            <div class="copyright-social">
                
                <a href="https://www.instagram.com/agrybem?igsh=MW55dW04M3B2bXJvMA%3D%3D&utm_source=qr" target="_blank">
                    <img src="../templates/assets/img/instagram (1).png" alt="Instagram Agrybem" class="icon social-icon">
                </a>
            </div>
        </div>
    </section>

</body>
</html>