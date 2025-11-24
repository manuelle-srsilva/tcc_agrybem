<?php

session_start();
require_once '../vendor/autoload.php';

use Controller\EmpreendedorController;

$empreendedorController = new EmpreendedorController();
$loginMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['cnpj_cpf'], $_POST['senha'])) {
        $cnpj_cpf = $_POST['cnpj_cpf'];
        $senha = $_POST['senha'];

        if ($empreendedorController->loginEmpreendedor($cnpj_cpf, $senha)) {
            header('Location: painel_profissional_empresa.php');
            exit();
        }
        else {
            $loginMessage = "CNPJ/CPF e/ou senha incorreto";
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
    <link rel="stylesheet" href="../templates/assets/css/empresa_login.css">
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

                <a href="../index.php" class="nav-button">Voltar ao início</a>
                 
            </nav>
        </div>
    </header>
    

    <!-- Main Section -->
    <section class="main-section">
        <img src="../templates/assets/img/login_empresa_img.png" alt="Imagem de fundo da seção principal" class="hero-img">
        <div class="hero-image-overlay"></div>
        <div class="container main-container">
            <!-- Left Side - Keywords -->
            <div class="left-content">
                <div class="keywords">
                    <div class="keyword-item icon-keyword">
                        <div class="icon-box">
                            <img src="../templates/assets/img/grafico_crescimento.png" alt="Negócios">
                        </div>
                        <span class="keyword-text-bordered">Negócios</span>
                    </div>
                    <div class="keyword-item">
                        <span class="keyword-text-plain">que cultivam</span>
                    </div>
                    <div class="keyword-item highlight-keyword">
                        <span class="keyword-text-highlight">Valores</span>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="right-content">
                <div class="login-box">
                    <h2>Conecte-se à sua conta comercial</h2>
                    <p class="subtitle">Você e o Agrybem, juntos!</p>
                    
                    <form method="POST" class="login-form">
                        <div class="form-group">
                            <label for="cnpj">Digite seu CNPJ ou CPF</label>
                            <input type="text" id="cnpj" name="cnpj_cpf" placeholder="">
                        </div>
                        
                        <div class="form-group">
                            <label for="senha">Digite sua Senha</label>
                            <input type="password" id="senha" name="senha" placeholder="">
                              <div class="recuperacao-senha"><a href="../View/empresa_senha.php">Esqueceu a senha?</a></div>
                        </div>
                        
                        <button type="submit" class="btn-entrar">ENTRAR</button>
                        
                        <p class="cadastro-link">
                            Não tem conta ainda? <a href="cadastro_empresa.php">Cadastre-se</a>
                        </p>
                    </form>

                    <p style="height: 15px; font-size: 12px; color: black;"><?php echo $loginMessage;?></p>
                </div>
            </div>
        </div>
    </section>

     <!-- Listras Decorativas -->
        <section class="decorative-stripes">
            <div class="stripe stripe-red"></div>
            <div class="stripe stripe-yellow"></div>
        </section>
        <section class="decorative-stripes">
            <div class="stripe stripe-red"></div>
            <div class="stripe stripe-yellow"></div>
        </section>
        <!-- Nova Seção 1: Seu negócio pede visibilidade -->
    <section class="partner-info-1">
        <div class="container partner-container">
            <div class="partner-image-wrapper">
                <!-- A imagem aqui deve ser o círculo com a mulher -->
                <img src="../templates/assets/img/PARTINER 1.png" alt="Mulher com sacola" class="partner-img">
            </div>
            <div class="partner-content">
                <h2 class="partner-title">Dê visibilidade ao seu negócio e veja suas vendas decolarem!</h2>
                <p class="partner-text">Com o Agrybem, sua loja ganha visibilidade e alcança novos públicos.
Aproveite nossas ferramentas de divulgação para fortalecer sua presença no mercado e atrair clientes que valorizam o comércio local e a produção regional.</p>
                <a href="../View/login_empresa.php" class="partner-button">Quero vender mais</a>
            </div>
        </div>
    </section>

    <!-- Nova Seção 2: Vantagens de ser um parceiro -->
    <section class="partner-info-2">
        <div class="container">
            <h2 class="section-title">Vantagens de ser um parceiro Agrybem</h2>
            <div class="advantages-grid">
                <div class="advantage-item">
                        <img src="../templates/assets/img/patiner 2 visibilidade.png" alt="Ícone Gestão" class="advantage-icon">

                    <h3 class="advantage-title">Maior Visibilidade</h3>
                    <p class="advantage-text">Divulgue seus produtos de forma  eficiente!
O Agrybem aproxima você de novos clientes e parceiros por meio de uma plataforma que valoriza o comércio local e a produção regional.</p>
                </div>
                <div class="advantage-item">
                    <img src="../templates/assets/img/patiner 2 gestão.png" alt="Ícone Gestão" class="advantage-icon">
                    <h3 class="advantage-title">Gestão prática</h3>
                    <p class="advantage-text">Tenha controle sobre seus produtos e informações de forma simples.
Atualize seu catálogo e acompanhe seu perfil diretamente pelo celular</p>
                </div>
                <div class="advantage-item">
                    <img src="../templates/assets/img/patiner 2 orientação.png" alt="Ícone Consultoria" class="advantage-icon">
                    <h3 class="advantage-title">Orientação gratuita</h3>
                    <p class="advantage-text">A equipe do Agrybem oferece apoio através do nosso suporte, para ajudar você a divulgar melhor o seu negócio e aproveitar todos os recursos da plataforma.
                         
                    </p>
                </div>
                <div class="advantage-item">
                    <img src="../templates/assets/img/patiner 2 destaque.png" alt="Ícone Crédito" class="advantage-icon">
                    <h3 class="advantage-title">Credibilidade</h3>
                    <p class="advantage-text">No Agrybem, seu perfil é sua vitrine digital!
Mostre onde você está, o que oferece e como entrar em contato — tudo de um jeito fácil, bonito e acessível para quem busca por perto.</p>
                </div>
            </div>
             <div class="inform-suporte">
               <p> Acesse o suporte para mais informações! </p>
             </div>
            <div class="text-center">
                <a href="../View/empresa_suporte.php" class="partner-button-secondary">Suporte</a>
              
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