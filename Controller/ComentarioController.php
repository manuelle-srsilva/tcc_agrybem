<?php

namespace Controller;

use Model\Comentario;

class ComentarioController{
    private $comentarioModel;
    public function __construct(){
        $this->comentarioModel = new Comentario();
    }

    //Cadastro de comentário
    public function cadastroComentario($comentario, $id_cliente, $id_empreendimento = null){
        if(empty($comentario) || empty($id_cliente)){
            return false;
        }
        return $this->comentarioModel->registerComentario($comentario, $id_cliente, $id_empreendimento);
    }

    // Buscar comentário (não utilizada no fluxo atual, mas mantida)
    public function getComentario($id, $comentario, $id_cliente, $data_post){
        return $this->comentarioModel->getComentarioInfo($id, $comentario, $id_cliente, $data_post);
    }

    //Atualizar comentário
    public function updateComentario($id, $comentario){
        // CORREÇÃO: Removida a verificação de $id_cliente que não existe neste escopo.
        if(empty($comentario) || empty($id)){
            return false;
        }
        return $this->comentarioModel->updateComentario($id, $comentario);
    }

    //Excluir cadastro do comentario
    public function deleteComentario($id){
        if(empty($id)){
            return false;
        }
        return $this->comentarioModel->deleteComentario($id);
    }

    // Listar todos os comentários
    public function listarComentarios($id_empreendimento = null) {
        return $this->comentarioModel->listComentarios($id_empreendimento);
    }

    public function countComentarios($id_empreendimento = null){
        return $this->comentarioModel->countComentarios($id_empreendimento);
    }
}
?>