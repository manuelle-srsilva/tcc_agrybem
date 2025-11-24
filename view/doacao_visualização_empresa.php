<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
use Controller\InstituicaoController;

$instController = new InstituicaoController();
$instituicoes = $instController->getAllInstituicoes();
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
    <link rel="stylesheet" href="../templates/assets/css/doacao_pag_principal1.css">
</head>
<body>
   <!-- Header -->
   <header class="header">
        <div class="container">
            <div class="logo">
               
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
             <nav class="nav">  
                <a href="../View/painel_profissional_empresa.php" class="nav-button">Voltar</a>
            </nav>
          
        </div>
    </header>

   
    <!-- Hero Section -->
    <section class="hero">
        <img src="../templates/assets/img/principal.doação.png" alt="Imagem de fundo da seção hero" class="hero-img">
        <div class="hero-image-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Conectando quem ajuda a quem precisa
</h1>
        </div>
    </section>
<!-- Seção de Produtos em Destaque -->
    <section class="featured-products">
        <div class="container">
            <h2 class="featured-title">Instituições que Transformam Vidas</h2>
            <p class="featured-subtitle">Descubra projetos que fazem a diferença e espalham solidariedade pela sua região</p>

            <div class="product-cards-container">
                <!-- Card de Produto 1 -->
                <div class="product-card">
                    <div class="product-image-placeholder">
                     <img src="../templates/assets/img/dd.png" alt="">
                    </div>
                    <div class="product-info-overlay">
        
                        <h3 class="product-name">Apoie Quem Cuida</h3>
                        <p class="product-description">Conheça instituições dedicadas a ajudar quem mais precisa. Sua contribuição pode mudar histórias!</p>
                    </div>
                    <button class="add-to-cart-btn">+</button>
                </div>

             

                <!-- Card de Produto 3 -->
                <div class="product-card">
                    <div class="product-image-placeholder">
                         <img src="../templates/assets/img/doacao (1).png" alt="">
                    </div>
                    <div class="product-info-overlay">
                        
                        <h3 class="product-name">Conecte-se ao Bem</h3>
                        <p class="product-description">Encontre instituições próximas a você e fortaleça o impacto da solidariedade local</p>
                    </div>
                    <button class="add-to-cart-btn">+</button>
                </div>

                   <!-- Card de Produto 3 -->
                <div class="product-card">
                    <div class="product-image-placeholder">
                         <img src="../templates/assets/img/doacao (2).png" alt="">
                    </div>
                    <div class="product-info-overlay">
                        <h3 class="product-name">Catálogo Solidário</h3>
                        <p class="product-description">Um espaço para descobrir, apoiar e divulgar o trabalho de quem faz o bem todos os dias</p>
                    </div>
                    <button class="add-to-cart-btn">+</button>
                </div>

                   <!-- Card de Produto 3 -->
                <div class="product-card">
                    <div class="product-image-placeholder">
                         <img src="../templates/assets/img/doacao (3).png" alt="">
                    </div>
                    <div class="product-info-overlay">
                       
                        <h3 class="product-name">Estratégias de Destaque</h3>
                        <p class="product-description">Aprimore sua vitrine digital com boas práticas de apresentação. Utilize descrições objetivas e imagens de qualidade para atrair mais clientes</p>
                    </div>
                    <button class="add-to-cart-btn">+</button>
                </div>
            </div>
        </div>
    </section>

   


   <!-- Seção de Pesquisa -->
        <section class="search-section">
            
            <h2 class="search-title">Descubra instituições perto de você</h2>
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
            <?php if(!empty($instituicoes)): ?>
                <?php foreach($instituicoes as $inst): ?>
                    <?php
                        $foto_src = '';
                        if(!empty($inst['foto'])){
                            $foto_base64 = base64_encode($inst['foto']);
                            $foto_src = 'data:image/jpeg;base64,' . $foto_base64;
                        } else {
                            $foto_src = '../templates/assets/img/dd.png';
                        }
                        $link = "../View/instituicao_vitrine_empresa.php?id=" . urlencode($inst['id']);
                        $cidade = htmlspecialchars($inst['cidade'] ?? '');
                        $bairro = htmlspecialchars($inst['bairro'] ?? '');
                        $cidade_bairro = trim($cidade . ($cidade && $bairro ? ' (' . $bairro . ')' : '')) ?: 'Cidade (Bairro)';
                    ?>
                    <a href="<?php echo $link; ?>" class="card-link" data-city="<?php echo $cidade; ?>">
                        <div class="card">
                            <div class="card-image">
                                <img src="<?php echo $foto_src; ?>" alt="<?php echo htmlspecialchars($inst['nome']); ?>">
                            </div>
                            <div class="card-content">
                                <h3 class="card-title"><?php echo htmlspecialchars($inst['nome']); ?></h3>
                                <p class="card-category"><?php echo $cidade_bairro; ?></p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhuma instituição cadastrada no momento</p>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const input = document.querySelector('.search-input');
        const btn = document.getElementById('searchIcon');

        function filter(){
            const q = (input && input.value || '').trim().toLowerCase();
            const cards = document.querySelectorAll('.card-link');
            let anyShown = false;
            cards.forEach(a => {
                const city = (a.dataset.city || '').toLowerCase();
                const titleEl = a.querySelector('.card-title');
                const title = (titleEl && titleEl.textContent || '').toLowerCase();
                const show = !q || city.includes(q) || title.includes(q);
                a.style.display = show ? '' : 'none';
                if(show) anyShown = true;
            });
            // optional: show message when no results
            let noMsg = document.getElementById('noResultsMsg');
            if(!noMsg){
                noMsg = document.createElement('p');
                noMsg.id = 'noResultsMsg';
                noMsg.style.display = 'none';
                const grid = document.querySelector('.cards-grid');
            }
            noMsg.style.display = anyShown ? 'none' : '';
        }

        if(input){
            input.addEventListener('input', filter);
            input.addEventListener('keyup', filter);
        }

        if(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                filter();
            });
        }

        // initial filter (in case page preserves state)
        filter();
    });
    </script>

   <script src="../templates/assets/js/menu_profissional.js"></script>
</body>
</html> 