<?php
session_start();
require_once '../vendor/autoload.php';

use Controller\EmpreendimentoController;
use Controller\EnderecoController;
use Controller\ProdutoController;

$empreendimento_id = $_GET['id'] ?? null;

if (!$empreendimento_id) {
    // Redireciona ou mostra uma mensagem de erro se o ID não for fornecido
    header('Location: cliente_pag_principal.php');
    exit();
}

$empreendimentoController = new EmpreendimentoController();
$enderecoController = new EnderecoController();
$produtoController = new ProdutoController();

// Busca as informações do empreendimento
$empreendimentoInfo = $empreendimentoController->getempreendimentoInfo($empreendimento_id);
$empreendimentoFoto = $empreendimentoController->getEmpreendimentoFoto($empreendimento_id);
$produtos = $produtoController->getProdutosByEmpreendimento($empreendimento_id);

// Busca as informações do endereço do empreendimento
$enderecoInfo = null;
if ($empreendimentoInfo && isset($empreendimentoInfo['id_endereco'])) {
    $enderecoInfo = $enderecoController->getEnderecoInfo($empreendimentoInfo['id_endereco']);
}

// Converte a foto para base64
$foto_base64 = '';
if ($empreendimentoFoto && isset($empreendimentoFoto['foto'])) {
    $foto_base64 = base64_encode($empreendimentoFoto['foto']);
}
$foto_src = 'data:image/jpeg;base64,' . $foto_base64;

// PREPARAÇÃO DA URL DO MAPA
$mapQuery = 'Brasil'; // Valor padrão caso não haja endereço
if ($enderecoInfo) {
    $mapParts = [
        $enderecoInfo['rua'] ?? '',
        $enderecoInfo['numero'] ?? '',
        $enderecoInfo['bairro'] ?? '',
        $enderecoInfo['cidade'] ?? '',
        $enderecoInfo['estado'] ?? '',
        $enderecoInfo['cep'] ?? ''
    ];
    // Remove partes vazias e junta com vírgula
    $addressString = implode(',', array_filter($mapParts));
    // Codifica a string para ser usada em uma URL (troca espaços por '+', etc.)
    $mapQuery = urlencode($addressString);
}
$googleMapsApiKey = 'AIzaSyC0tzkSQhRrMOcoMZ1XU0Ty4RwCER5gxLo'; // IMPORTANTE: Substitua pela sua chave de API do Google Maps
$mapUrl = "https://www.google.com/maps/embed/v1/place?key={$googleMapsApiKey}&q={$mapQuery}";

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
    <link rel="stylesheet" href="../templates/assets/css/loja.css">
    <script src="js/carrinho.js" defer></script>
    <style>
    /* Toast simples para confirmação de adição ao carrinho */
    .ag-toast{position:fixed;right:20px;bottom:20px;background:#323232;color:#fff;padding:12px 16px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.2);opacity:0;transform:translateY(12px);transition:opacity .25s ease,transform .25s ease;z-index:9999;font-family:inherit}
    .ag-toast.show{opacity:1;transform:translateY(0)}
    /* Product filter: hide by toggling class to preserve product-card styles */
    .product-card.filtered-out { display: none !important; }
    </style>
</head>
<body>
    <!-- Header -->
  <header class="header">
        <div class="container">
            <div class="logo">
                <span class="logo-text">Agry<span class="logo-highlight">bem</span></span>
            </div>
            <nav class="nav">
             
                <a href="../View/cliente_pag_principal.php" class="nav-button">Voltar</a>
              
            </nav>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
       
        </div>
    </section>
    <!-- CONTEÚDO DO PERFIL INTEGRADO (Sem tag <main> para evitar conflitos) -->
        <!-- HEADER SECTION WITH LOGO AND TITLE -->
        <section class="header-section">
            <div class="container">
                <div class="header-top">
                    <div class="logo-institution">
                       
                        <img src="<?php echo $foto_src; ?>" alt="<?php echo htmlspecialchars($empreendimentoInfo['nome']); ?>" class="logo-img">
                    </div>

                    <div class="header-info">
                        <h1 class="institution-title"><?php echo htmlspecialchars($empreendimentoInfo['nome']); ?></h1>
        
                    </div>
                    <!-- Ícone do Carrinho Movido para Aqui -->
                    <a href="../view/carrinho.php" class="cart-icon-link-profile">
                        <img src="../templates/assets/img/vista-lateral-vazia-do-carrinho-de-compras.png" alt="Carrinho de Compras" class="cart-icon-profile">
                        <span class="cart-count-profile" id="cart-count-profile">0</span>
                    </a>
                </div>
                
            </div>
        </section>

        <div class="container">
                <div class="comment-feed">
                <a href="pag_comentario.php?id=<?php echo urlencode($empreendimento_id); ?>"> Comentários</a>
            </div>
        </div>

        <!-- DESCRIPTION CARD -->
        <section class="card-section">
            <div class="container">
                <div class="card" data-card="description">
                  
                    <!-- REMOVIDO: style="display: none;" -->
                    <div class="card-actions">
                        <button class="card-save-button" title="Salvar">✓</button>
                        <button class="card-cancel-button" title="Cancelar">✕</button>
                    </div>
                    <div class="form-field">
                       
                        <textarea id="company-description" disabled><?php echo htmlspecialchars($empreendimentoInfo['descricao']); ?></textarea>
                    </div>
                </div>
            </div>
        </section>

        <!-- INSTITUTION INFO CARD -->
        <section class="card-section">
            <div class="container">
                <div class="card" data-card="institution">
                   
                    <!-- REMOVIDO: style="display: none;" -->
                    <div class="card-actions">
                        <button class="card-save-button" title="Salvar">✓</button>
                        <button class="card-cancel-button" title="Cancelar">✕</button>
                    </div>
                    <form class="institution-form" id="institutionForm">
                        

                        <div class="form-field">
                            <label for="company-name">Nome </label>
                            <input type="text" id="company-name" value="<?php echo htmlspecialchars($empreendimentoInfo['nome']); ?>" disabled>
                        </div>

                       

                        <div class="form-field">
                            <label for="phone">Telefone</label>
                            <input type="tel" id="phone" value="<?php echo htmlspecialchars($empreendimentoInfo['telefone']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="hours">Horário de Funcionamento</label>
                            <input type="text" id="hours" value="<?php echo htmlspecialchars($empreendimentoInfo['hr_funcionamento']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="whatsapp">Link para WhatsApp</label>
                            <input type="text" id="whatsapp" value="<?php echo htmlspecialchars($empreendimentoInfo['link_whatsapp']); ?>" disabled>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- ADDRESS CARD -->
        <section class="card-section">
            <div class="container">
                <div class="card" data-card="address">
                    <div class="card-header">
                        <h2 class="card-title">Endereço</h2>
                      
                    </div>
                    <!-- REMOVIDO: style="display: none;" -->
                    <div class="card-actions">
                        <button class="card-save-button" title="Salvar">✓</button>
                        <button class="card-cancel-button" title="Cancelar">✕</button>
                    </div>
                    <form class="address-form" id="addressForm">
                        <div class="form-field">
                            <label for="cep">CEP</label>
                            <input type="text" id="cep" value="<?php echo htmlspecialchars($enderecoInfo['cep']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="street">Rua</label>
                            <input type="text" id="street" value="<?php echo htmlspecialchars($enderecoInfo['rua']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="number">Número</label>
                            <input type="text" id="number" value="<?php echo htmlspecialchars($enderecoInfo['numero']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="neighborhood">Bairro</label>
                            <input type="text" id="neighborhood" value="<?php echo htmlspecialchars($enderecoInfo['bairro']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="city">Cidade</label>
                            <input type="text" id="city" value="<?php echo htmlspecialchars($enderecoInfo['cidade']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="state">Estado</label>
                            <input type="text" id="state" value="<?php echo htmlspecialchars($enderecoInfo['estado']); ?>" disabled>
                        </div>

                        <div class="form-field">
                            <label for="reference">Ponto de Referência</label>
                            <input type="text" id="reference" value="<?php echo htmlspecialchars($enderecoInfo['complemento']); ?>" disabled>
                        </div>
                    </form>

                </div>
            </div>
        </section>

      <!-- MAP SECTION -->
        <section class="map-section">
            <div class="container">
                <div class="map-placeholder">
                    <iframe
                        id="google-map-iframe"
                        width="600"
                        height="450"
                        style="border:0"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        src="<?= htmlspecialchars($mapUrl) ?>">
                    </iframe>
                </div>
            </div>
        </section>
    <!-- FIM DO CONTEÚDO DO PERFIL INTEGRADO -->
     <!-- Seção de Pesquisa -->
        <section class="search-section">
            
            <h2 class="search-title">Produtos</h2>
            <div class="search-container">
                <div class="search-input-wrapper">
                    <input type="text" class="search-input" placeholder="Pesquisar...">
                    <button class="search-btn-icon" id="searchIcon">
                        <img src="../templates/assets/img/lupa de pesquisa.png" alt="Pesquisar" class="search-icon-img">
                    </button>
                </div>
            </div>
        </section>
      
     <!-- Seção de Produtos em Destaque -->
    <section class="featured-products">
        <div class="container">
          

            <div class="product-cards-container">
                    <?php if (!empty($produtos)): ?>
                        <?php foreach ($produtos as $produto): ?>

                            <?php
                                $foto_base64 = base64_encode($produto['foto']);
                                $foto_src = 'data:image/jpeg;base64,' . $foto_base64;
                                $preco_formatado = number_format($produto['preco'], 2, ',', '.');
                                $unidade_medida = strtolower($produto['medida']) === 'kg' ? 'kg' : 'un';
                            ?>

                            <div class="product-card"
                                data-product-id="<?php echo $produto['id']; ?>"
                                data-product-name="<?php echo htmlspecialchars($produto['nome']); ?>"
                                data-product-category="<?php echo htmlspecialchars($produto['categoria']); ?>"
                                data-product-price="<?php echo $produto['preco']; ?>"
                                data-product-image="<?php echo $foto_src; ?>"
                                data-product-measure="<?php echo htmlspecialchars($produto['medida']); ?>">

                                <div class="product-image-placeholder">
                                    <img src="<?php echo $foto_src; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                                </div>

                                <div class="product-info-overlay">
                                    <h3 class="product-name"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                                    <h3 class="product-description">
                                        <?php echo htmlspecialchars($produto['categoria']); ?> (<?php echo htmlspecialchars($produto['medida']); ?>)<br>
                                        R$ <?php echo $preco_formatado; ?>
                                    </h3>
                                </div>

                                <button class="add-to-cart-btn" data-product-id="<?php echo $produto['id']; ?>">+</button>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Nenhum produto encontrado.</p>
                    <?php endif; ?>
            </div>
        </div>
    </section>
    
    
    <div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
    </div>
    <script>
    (function(){
        async function post(data){
            const res = await fetch('../Controller/CartController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return res.json();
        }

        async function updateCartCount(){
            try{
                const r = await post({ action: 'count' });
                const countEl = document.getElementById('cart-count-profile');
                if(countEl) countEl.textContent = r.count || 0;
            }catch(e){ console.error(e); }
        }

        document.addEventListener('DOMContentLoaded', function(){
            updateCartCount();
            document.querySelectorAll('.add-to-cart-btn').forEach(btn=>{
                btn.addEventListener('click', async function(e){
                    e.preventDefault();
                    const card = btn.closest('.product-card');
                    if(!card) return;
                    const originalId = card.dataset.productId;
                    const name = card.dataset.productName || '';
                    const category = card.dataset.productCategory || '';
                    const price = parseFloat(card.dataset.productPrice) || 0;
                    const image = card.dataset.productImage || '';
                    const measure = (card.dataset.productMeasure || 'un').toLowerCase();
                    const mappedId = (measure === 'kg') ? '1' : (measure === 'un') ? '2' : originalId;

                    try{
                        const res = await post({ action: 'add', product_id: originalId, mapped_id: mappedId, name, category, price, image, measure });
                        if(res.success){ 
                            updateCartCount();
                            showToast('Produto adicionado: ' + name);
                            btn.classList.add('added-to-cart');
                            setTimeout(()=> btn.classList.remove('added-to-cart'), 600);
                        } else {
                            showToast('Erro ao adicionar o produto');
                        }
                    }catch(err){ console.error(err); showToast('Erro ao adicionar o produto'); }
                });
            });
            // add product search filter (by product name or category)
            const searchInput = document.querySelector('.search-section .search-input');
            const productGrid = document.querySelector('.product-cards-container');
            if(searchInput && productGrid){
                searchInput.addEventListener('input', function(){
                    const q = this.value.trim().toLowerCase();
                    const cards = productGrid.querySelectorAll('.product-card');
                    cards.forEach(card => {
                        const name = (card.dataset.productName || '').toLowerCase();
                        const cat = (card.dataset.productCategory || '').toLowerCase();
                        const match = !q || name.indexOf(q) !== -1 || cat.indexOf(q) !== -1;
                        if(match) card.classList.remove('filtered-out'); else card.classList.add('filtered-out');
                    });
                });
            }
        });

        window.agCart = { updateCartCount };
    })();
    
    // Toast helper (fora do IIFE para possível uso global)
    function showToast(message, ms = 2500){
        let el = document.getElementById('ag-toast');
        if(!el){ el = document.createElement('div'); el.id = 'ag-toast'; el.className = 'ag-toast'; document.body.appendChild(el); }
        el.textContent = message;
        // force reflow then show
        void el.offsetWidth;
        el.classList.add('show');
        clearTimeout(el._hideTimeout);
        el._hideTimeout = setTimeout(()=>{ el.classList.remove('show'); }, ms);
    }
    </script>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>
    
    
</body>
</html>