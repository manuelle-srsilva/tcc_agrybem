<?php

session_start();
require_once '../vendor/autoload.php';

use Controller\EmpreendedorController;
use Controller\EmpreendimentoController;

$empreendedorController = new EmpreendedorController();
$empreendimentoController = new EmpreendimentoController();


$id = $_SESSION['id_empreendimento'] ?? null;
if (!$id) {
    header("HTTP/1.0 404 Not Found");
    exit('ID não encontrado');
}

$result = $empreendimentoController->getEmpreendimentoFoto($id);

if ($result && isset($result['foto'])) {
    $data = $result['foto'];
    // detect mime type for data URI
    $_info = @getimagesizefromstring($data);
    $mime = $_info['mime'] ?? 'image/jpeg';
} else {
    header("HTTP/1.0 404 Not Found");
}


$userInfo = null;

if(!$empreendedorController->isLoggedIn()) {
    header('Location: login_empresa.php');
    exit();
}

$empreendimentoNome = $empreendimentoController->getEmpreendimentoName($_SESSION['id_empreendimento']);

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agrybem - Página Principal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/empresa_painel_profissional.css">
</head>
<body>
    <!-- Header -->
   <header class="header">
        <div class="container">
            <div class="logo">
               
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
                <?php if($empreendimentoNome): ?>
                    <a href="#" class="nav-link"><?php echo htmlspecialchars($empreendimentoNome['nome'])?></a>
                <?php endif; ?>
                <div class="menu-container">
                   <div class="menu-container">
                    <button class="menu-toggle" id="menuToggle">
                        <img src="../templates/assets/img/menu.png" alt="Menu" class="menu-icon-img">
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="../View/empresa_perfil.php" class="menu-item">
                            <img src="../templates/assets/img/perfil.png" alt="Perfil" class="menu-item-icon">
                            <span>Perfil</span>
                        </a>
                        <a href="../View/empresa_produto.php" class="menu-item">
                            <img src="../templates/assets/img/sacolas-de-compras.png" alt="Meus produtos" class="menu-item-icon">
                            <span>Produtos</span>
                        </a>
                         <a href="../View/empresa_loja.php" class="menu-item">
                            <img src="../templates/assets/img/loja empresa.png" alt="Sair" class="menu-item-icon">
                            <span>minha loja</span>
                        </a>

                         <a href="../View/empresa_pedido.php" class="menu-item">
                            <img src="../templates/assets/img/pedido.png" alt="Sair" class="menu-item-icon">
                            <span>Pedidos</span>
                        </a>
        
                        <a href="../View/login_empresa.php" class="menu-item">
                            <img src="../templates/assets/img/sair.png" alt="Sair" class="menu-item-icon">
                            <span>Sair</span>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

     <!-- Hero Section -->
    <section class="hero">
        <img src="../templates/assets/img/tetste painel profissional.png" alt="Imagem de fundo do painel profissional" class="hero-img">
        <div class="hero-image-overlay"></div>
        <div class="hero-content">
            <div class="hero-blur-box">
                <h1 class="hero-title">Orgulho em ter você com a gente!</h1>
            </div>
        </div>
    </section>

    
    <!-- Seção de Produtos em Destaque -->
    <section class="featured-products">
        <div class="container">
            <h2 class="featured-title">Gestão do Seu Negócio no Agrybem</h2>
            <p class="featured-subtitle">Eleve a visibilidade dos seus produtos e do seu negócio</p>

            <div class="product-cards-container">
                <!-- Card de Produto 1 -->
                <div class="product-card">
                    <div class="product-image-placeholder">
                                             <img src="../templates/assets/img/product-1.png" alt="">
                    </div>
                    <div class="product-info-overlay">
                        <div class="product-rating">
                            <span>★★★★★</span> (5/5)
                        </div>
                        <h3 class="product-name">Cadastro de Produtos</h3>
                        <p class="product-description">Gerencie seu portfólio de forma eficiente. Cadastre, edite e mantenha seus produtos atualizados para ampliar sua visibilidade no Agrybem.</p>
                    </div>
                    <button class="add-to-cart-btn">+</button>
                </div>

                <!-- Card de Produto 2 -->
                <div class="product-card-2">
                    <div class="product-image-placeholder">
                        <img src="../templates/assets/img/product-2.png" alt="">
                    </div>
                    <div class="product-info-overlay">
                        <div class="product-rating">
                            <span>★★★★★</span> (5/5)
                        </div>
                        <h3 class="product-name-2">Perfil Profissional</h3>
                        <p class="product-description-2">Construa uma presença sólida na plataforma. Um perfil completo reforça a credibilidade do seu negócio e facilita novas conexões comerciais.</p>
                    </div>
                    <button class="add-to-cart-btn">+</button>
                </div>

                <!-- Card de Produto 3 -->
                <div class="product-card">
                    <div class="product-image-placeholder">
                         <img src="../templates/assets/img/product-3.png" alt="">
                    </div>
                    <div class="product-info-overlay">
                        <div class="product-rating">
                            <span>★★★★★</span> (5/5)
                        </div>
                        <h3 class="product-name">Estratégias de Destaque</h3>
                        <p class="product-description">Aprimore sua vitrine digital com boas práticas de apresentação. Utilize descrições objetivas e imagens de qualidade para atrair mais clientes.</p>
                    </div>
                    <button class="add-to-cart-btn">+</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Divisor -->
     <section class="barra-diviso"> 
<div class="divider"></div>


     </section>
    
    
    <!-- sessão da doação -->
    <section class="donation">
        <div class="container donation-container">
            <div class="donation-content">
                <h2 class="donation-title">Doe com o Agry<span class="donation-highlight">bem</span>!</h2>
                <p class="donation-text">
                   No Agrybem, agricultores e comerciantes podem doar alimentos em bom estado, e instituições cadastradas como ONGs e igrejas podem receber as doações.
                </p>
                <a href="doacao_visualização_empresa.html" class="donation-button">participar</a>
            </div>
            <div class="donation-image">
                <img src="../templates/assets/img/agricultordoação" alt="Agricultor com vegetais" class="donation-img">
            </div>
        </div>
    </section>


    <!-- Footer -->
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

   <script src="../templates/assets/js/menu_profissional.js"></script>
</body>
</html> 