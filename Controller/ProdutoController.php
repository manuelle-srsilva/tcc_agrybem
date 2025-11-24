<?php

namespace Controller;

use Model\Produto;

class ProdutoController{
    private $produtoModel;
    public function __construct(){
        $this->produtoModel = new Produto();
    }

    //Cadastro de produto
    public function cadastroProduto($nome, $preco, $categoria, $medida, $foto, $id_empreendimento){
        if(empty($nome) or empty($preco) or empty($categoria) or empty($medida) or empty($foto) or empty($id_empreendimento)){
            return false;
        }

        return $this->produtoModel->registerProduto($nome, $preco, $medida, $categoria, $foto, $id_empreendimento);
    }

    // Buscar informações do produto (pelo produto)
    public function getProdutoInfo($id){
        return $this->produtoModel->getprodutoInfo($id);
    }

    // Buscar todos os produtos de um empreendimento
    public function getProdutosByEmpreendimento($id_empreendimento){
        return $this->produtoModel->getProdutosByEmpreendimento($id_empreendimento);
    }

    //Atualizar informações de cadastro do produto
    public function updateproduto($id, $nome, $preco){
        if(empty($nome) or empty($preco)){
            return false;
        }

        return $this->produtoModel->updateProduto($id, $nome, $preco);
    }

    //Excluir cadastro do produto
    public function deleteProduto($id){
        if(empty($id)){
            return false;
        }
        return $this->produtoModel->deleteProduto($id);
    }

}
?>