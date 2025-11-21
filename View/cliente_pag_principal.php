<?php

session_start();
require_once '../vendor/autoload.php';

use Controller\ClienteController;
use Controller\EmpreendimentoController;

$clienteController = new ClienteController();

$userInfo = null;

if (!$clienteController->isLoggedIn()) {
    header('Location: cliente_login.php');
    exit();
}

$id = $_SESSION['id'];
$nome = $_SESSION['nome'];

$clienteNome = $clienteController->getClienteName($id, $nome);

$empreendimentoController = new EmpreendimentoController();
$empreendimentos = $empreendimentoController->getAllEmpreendimentos();

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
    <link rel="stylesheet" href="../templates/assets/css/clientepagprincipal.css">
</head>

<body>  
    <!-- Header -->
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">

                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
                <?php if ($clienteNome): ?>
                    <a href="#" class="nav-link"><?php echo htmlspecialchars($clienteNome['nome']) ?></a>
                <?php endif; ?>

                 <div class="menu-container">
                    <button class="menu-toggle" id="menuToggle">
                        <img src="../templates/assets/img/menu.png" alt="Menu" class="menu-icon-img">
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="../View/cliente_perfil.php" class="menu-item">
                            <img src="../templates/assets/img/perfil.png" alt="Perfil" class="menu-item-icon">
                            <span>Perfil</span>
                        </a>
                        <a href="../View/cliente_pedido.php" class="menu-item">
                            <img src="../templates/assets/img/pedido.png" alt="Meus pedidos" class="menu-item-icon">
                            <span>Meus Pedidos </span>
                        </a>
                        <a href="../View/cliente_login.php" class="menu-item">
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
            <img src="../templates/assets/img/Banner para loja de cosméticos orgânico verde.png" alt="Imagem de fundo da seção hero" class="hero-img">
            <div class="hero-image-overlay"></div>
            <div class="hero-content">
                <div class="hero-blur-box">
                    <h1 class="hero-title">Orgulho em ter você com a gente!</h1>
                </div>
            </div>
        </section>
    
    <section class="products">
            <div class="container">
                <h2 class="section-title"> Escolha lojas locais e leve frescor e sabor pra sua mesa</h2>
                <div class="products-grid">
                    <div class="product-card">
                        <img src="../templates/assets/img/propaganda cliente principal 1.png" alt="Sementes" class="product-img">
                    </div>
                    <div class="product-card">
                        <img src="../templates/assets/img/propaganda cliente principal 2.png" alt="Tomates" class="product-img">
                    </div>
                    <div class="product-card">
                        <img src="../templates/assets/img/propaganda cliente principal 3.png" alt="Café" class="product-img">
                    </div>
                    <div class="product-card">
                        <img src="../templates/assets/img/propaganda cliente principal 4.0.png" alt="Castanhas" class="product-img">
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
                  No Agrybem, você também pode fazer a diferença, doe alimentos em bom estado e ajude ONGs e instituições da sua região
                </p>
                <a href="../View/doacao_visualizacao_cliente.php" class="donation-button">Participar</a>
            </div>
            <div class="donation-image">
                <img src="../templates/assets/img/agricultordoação" alt="Agricultor com vegetais" class="donation-img">
            </div>
        </div>
    </section>
    
     <!-- Seção de Pesquisa -->
        <section class="search-section">
            
            <h2 class="search-title">Descubra lojas perto de você</h2>
            <div class="search-container">
                <div class="search-input-wrapper">
                    <input type="text" class="search-input" placeholder="Pesquisar...">
                    <button class="search-btn-icon" id="searchIcon">
                        <img src="../templates/assets/img/lupa de pesquisa.png" alt="Pesquisar" class="search-icon-img">
                    </button>
                </div>
            </div>
        </section>
    
        <!-- Grade de Cartões -->
        <section class="cards-section">
            <div class="cards-grid">
                <?php if (!empty($empreendimentos)): ?>
                    <?php foreach ($empreendimentos as $empreendimento): ?>
                        <?php
                            // Converte o binário da foto para base64 para exibição
                            $foto_base64 = base64_encode($empreendimento['foto']);
                            $foto_src = 'data:image/jpeg;base64,' . $foto_base64;
                            // Cria o link para a página da loja, passando o ID do empreendimento
                            $link_loja = "../View/loja.php?id=" . $empreendimento['id'];
                            $cidade_bairro = htmlspecialchars($empreendimento['cidade']) . ' (' . htmlspecialchars($empreendimento['bairro']) . ')';
                        ?>
                        <a href="<?php echo $link_loja; ?>" class="card-link" data-city="<?php echo htmlspecialchars($empreendimento['cidade']); ?>">
                            <div class="card" data-city="<?php echo htmlspecialchars($empreendimento['cidade']); ?>">
                                <div class="card-image">
                                    <img src="<?php echo $foto_src; ?>" alt="<?php echo htmlspecialchars($empreendimento['nome']); ?>">
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title"><?php echo htmlspecialchars($empreendimento['nome']); ?></h3>
                                    <p class="card-category"><?php echo $cidade_bairro; ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhuma empresa cadastrada no momento.</p>
                <?php endif; ?>
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
    <script src="../templates/assets/js/menu_profissional.js"></script>

    <style>
    /* preserve card styling: hide only the outer link wrapper when filtered */
    .card-link.filtered-out { display: none !important; }
    </style>
    <script>
    // Filtro por cidade (cliente_pag_principal.php) — toggle class on .card-link to avoid touching inner card styles
    (function(){
        const searchInput = document.querySelector('.search-section .search-input');
        const cardsGrid = document.querySelector('.cards-grid');
        if(!searchInput || !cardsGrid) return;
        searchInput.addEventListener('input', function(){
            const q = this.value.trim().toLowerCase();
            const links = cardsGrid.querySelectorAll('.card-link');
            let anyVisible = false;
            links.forEach(link => {
                const city = (link.dataset.city || link.querySelector('.card')?.dataset.city || '').toLowerCase();
                const match = !q || city.indexOf(q) !== -1;
                if(match){
                    link.classList.remove('filtered-out');
                    anyVisible = true;
                } else {
                    link.classList.add('filtered-out');
                }
            });
            // optional: show no-results (not implemented)
        });
    })();
    </script>

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