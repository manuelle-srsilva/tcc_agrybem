<?php
session_start();
require_once '../vendor/autoload.php';

use Controller\ProdutoController;

// Verifica se o empreendedor está logado e pega o id_empreendimento
if (!isset($_SESSION['id_empreendimento'])) {
    // Redireciona para o login se não estiver logado
    header('Location: login_empresa.php');
    exit();
}

$id_empreendimento = $_SESSION['id_empreendimento'];
$produtoController = new ProdutoController();
$produtos = $produtoController->getProdutosByEmpreendimento($id_empreendimento);

// Lógica para processar a exclusão
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id_produto_delete'])) {
    $id_produto = $_POST['id_produto_delete'];
    if ($produtoController->deleteProduto($id_produto)) {
        header('Location: empresa_produto.php?status=deleted');
        exit();
    } else {
		// Tratar erro de exclusão
    }
}

// Lógica para processar a edição
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id_produto'], $_POST['nome'], $_POST['preco'])) {
    $id_produto = $_POST['id_produto'];
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    
    // Remove o prefixo "R$" do preço, se existir, e substitui vírgula por ponto
    $preco_limpo = str_replace(['R$', ' '], '', $preco);
    $preco_float = (float) str_replace(',', '.', $preco_limpo);

    if ($produtoController->updateproduto($id_produto, $nome, $preco_float)) {
        header('Location: empresa_produto.php?status=updated');
        exit();
    } else {
        // Tratar erro de edição
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>empresa produto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/assets/css/empresa_produto.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <img src="../templates/assets/img/clienete pag principal.png" alt="Imagem de fundo da seção hero" class="hero-img">
        <div class="hero-image-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Meus Produtos</h1>
        </div>
    </section>
<!-- Painel Profissional -->
    <section class="professional-panel">
    <h2 class="panel-title">Escolha uma foto de qualidade para maior confiança</h2>

    <div class="button-group">
        <a href="../View/empresa_cadastro_produto.php" class="btn btn-primary">Cadastrar produto</a>
    </div>
    
</section>


    <!-- Divisor -->
     <section class="barra-diviso"> 
<div class="divider"></div>


     </section>
    

     <!-- Seção de Pesquisa -->
        <section class="search-section">
            
            <h2 class="search-title">Seus Produtos</h2>
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
			<?php if (!empty($produtos)): ?>
                    <?php foreach ($produtos as $produto): ?>
	                    <?php
	                        $foto_base64 = base64_encode($produto['foto']);
	                        $foto_src = 'data:image/jpeg;base64,' . $foto_base64;
	                        $preco_formatado = 'R$ ' . number_format($produto['preco'], 2, ',', '.');
	                    ?>
                        <div class="card" data-id="<?php echo $produto['id']; ?>" data-nome="<?php echo htmlspecialchars($produto['nome']); ?>" data-preco="<?php echo $preco_formatado; ?>" data-categoria="<?php echo htmlspecialchars($produto['categoria']); ?>">
	                        <div class="edit-icon" data-id="<?php echo $produto['id']; ?>"><i class="fas fa-pencil-alt"></i></div>
	                        <div class="card-image">
	                            <img src="<?php echo $foto_src; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
	                        </div>
	                        <div class="card-content">
	                            <h3 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h3>	
	                            <p><span>R$</span><?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
	                            <p class="card-category"><?php echo htmlspecialchars($produto['categoria']); ?></p>
	                        </div>
	                    </div>
	                <?php endforeach; ?>
	            <?php else: ?>
	                <p>Nenhum produto cadastrado.</p>
	            <?php endif; ?>          
		    </section>

		 

		    <!-- MODAL DE EDIÇÃO/EXCLUSÃO -->
		    <div id="editModal" class="modal-overlay">
		        <div class="modal-content">
		            
		            <!-- Botão Excluir (Topo) -->
		            <form method="POST" id="editProductForm" class="modal-form">
						<input type="hidden" name="action" value="delete">
						<input type="hidden" name="id_produto_delete" id="modal-delete-id"
						value="<?php $produto['id']; ?>">
						<button type="submit" class="btn-modal btn-excluir-topo" id="btnExcluir">Excluir</button>
					</form>
		            
		            <!-- Acordeão para Edição -->
		            <div class="accordion-container">
		                <div id="accordionEdit" class="accordion-header">
		                    <span class="accordion-title">Alterar</span>
		                    <span class="accordion-icon"><i class="fas fa-chevron-down"></i></span>
		                </div>
		                
		                <div id="accordionContent" class="accordion-content">
		                    <form id="editProductForm" class="modal-form" method="POST">
		                        <input type="hidden" name="action" value="edit">
			                    <input type="hidden" name="id_produto" id="modal-product-id"
								value="<?php $produto['id']; ?>">
		                        
		                        <label for="modal-nome">Nome</label>
		                        <input type="text" id="modal-nome" name="nome" placeholder="">
		                        
		                        <label for="modal-preco">Preço</label>
		                        <input type="text" id="modal-preco" name="preco" placeholder="">
		                        
		                        <!-- Campo Categoria oculto para compatibilidade com JS -->
		                        <input type="hidden" id="modal-categoria">

								<!-- Botão OK (Base) -->
								<button type="submit" class="btn-modal btn-ok-base" id="btnOK">OK</button>
		                    </form>
		                </div>
		            </div>
		        </div>
		    </div>

		   <script src="../templates/assets/js/empresa_modal.js"></script>
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
    // Filtro por nome do produto (empresa_produto.php)
    (function(){
        const searchInput = document.querySelector('.search-section .search-input');
        const cardsGrid = document.querySelector('.cards-grid');
        if(!searchInput || !cardsGrid) return;
        searchInput.addEventListener('input', function(){
            const q = this.value.trim().toLowerCase();
            const cards = cardsGrid.querySelectorAll('.card');
            cards.forEach(card => {
                const name = (card.dataset.nome || '').toLowerCase();
                if(!q || name.indexOf(q) !== -1) card.style.display = '';
                else card.style.display = 'none';
            });
        });
    })();
    </script>
		</body>
		</html>