<?php

namespace Controller;

use Model\Empreendimento;

class EmpreendimentoController{
    private $empreendimentoModel;
    public function __construct(){
        $this->empreendimentoModel = new Empreendimento();
    }

    public function salvarEmpreendimentoInfo($nome, $telefone, $link_whatsapp, $hr_funcionamento) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['form_empreendimento']['empreendimento1'] = [
            'nome' => trim($nome ?? ''),
            'telefone' => trim($telefone ?? ''),
            'link_whatsapp' => trim($link_whatsapp ?? ''),
            'hr_funcionamento' => trim($hr_funcionamento ?? '')
        ];
        // return true so calling views can redirect / continue flow
        return true;
    }

    public function salvarEmpreendimentoDescricao($descricao) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['form_empreendimento']['empreendimento2'] = [
            'descricao' => trim($descricao ?? '')
        ];
        // return true so calling views can redirect / continue flow
        return true;
    }

    public function salvarEmpreendimentoFoto($foto) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // store raw photo binary (do not trim binary data)
        $_SESSION['form_empreendimento']['empreendimento3'] = [
            'foto' => ($foto ?? null)
        ];
        // return true so calling views can redirect / continue flow
        return true;
    }

    //Cadastro de empreendimento
    public function cadastroempreendimento($nome, $telefone, $link_whatsapp, $descricao, $hr_funcionamento, $foto){
        if(empty($nome) or empty($telefone) or empty($link_whatsapp) or empty($descricao) or empty($hr_funcionamento) or empty($foto)){
            return false;
        }

        return $this->empreendimentoModel->registerEmpreendimento($nome, $telefone, $link_whatsapp, $descricao, $hr_funcionamento, $foto, $_SESSION['id_endereco']);
    }

    // Buscar nome do empreendimento
    public function getempreendimentoName($id){
        return $this->empreendimentoModel->getempreendimentoName($id);
    }

    public function getempreendimentoDescricao($id){
        return $this->empreendimentoModel->getempreendimentoDescricao($id);
    }

    public function getEmpreendimentoTelefone($id){
        return $this->empreendimentoModel->getEmpreendimentoTelefone($id);
    }

    public function getEmpreendimentoFuncionamento($id){
        return $this->empreendimentoModel->getEmpreendimentoFuncionamento($id);
    }

    public function getEmpreendimentoWhatsapp($id){
        return $this->empreendimentoModel->getEmpreendimentoWhatsapp($id);
    }

    public function getEmpreendimentoFoto($id){
        return $this->empreendimentoModel->getEmpreendimentoFoto($id);
    }

    // Buscar informações do empreendimento (pelo empreendimento)
    public function getAllEmpreendimentos(){
        return $this->empreendimentoModel->getAllEmpreendimentos();
    }

    // Buscar informações do empreendimento (pelo empreendimento)
    public function getempreendimentoInfo($id){
        return $this->empreendimentoModel->getempreendimentoInfo($id);
    }

    //Atualizar informações de cadastro do empreendimento
    public function updateEmpreendimento($id, $nome, $telefone, $link_whatsapp, $descricao, $hr_funcionamento){

        if(empty($id) or empty($nome) or empty($telefone) or empty($link_whatsapp) or empty($descricao) or empty($hr_funcionamento)){
            return false;
        }

        return $this->empreendimentoModel->updateEmpreendimento($id, $nome, $telefone, $link_whatsapp, $descricao, $hr_funcionamento);
    }

    public function updateEmpreendimentoFoto($id, $foto){
        if(empty($id) or empty($foto)){
            return false;
        }

        return $this->empreendimentoModel->updateEmpreendimentoFoto($id, $foto);
    }

    //Excluir cadastro do empreendimento
    public function deleteEmpreendimento($id){
        if(empty($id)){
            return false;
        }
        return $this->empreendimentoModel->deleteEmpreendimento($id);
    }

}
?>