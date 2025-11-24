<?php

namespace Controller;

use Model\Endereco;

class EnderecoController{
    private $enderecoModel;
    public function __construct(){
        $this->enderecoModel = new Endereco();
    }

    public function salvarEndereco($cep, $rua, $numero, $bairro, $cidade, $estado, $complemento) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['form_empreendimento']['endereco'] = [
            'cep' => trim($cep ?? ''),
            'rua' => trim($rua ?? ''),
            'numero' => trim($numero ?? ''),
            'bairro' => trim($bairro ?? ''),
            'cidade' => trim($cidade ?? ''),
            'estado' => trim($estado ?? ''),
            'complemento' => trim($complemento ?? '')
        ];
        // return true so calling views can redirect / continue flow
        return true;
    }

    public function salvarEnderecoInstituicao($cep, $rua, $numero, $bairro, $cidade, $estado, $complemento) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['form_instituicao']['endereco'] = [
            'cep' => trim($cep ?? ''),
            'rua' => trim($rua ?? ''),
            'numero' => trim($numero ?? ''),
            'bairro' => trim($bairro ?? ''),
            'cidade' => trim($cidade ?? ''),
            'estado' => trim($estado ?? ''),
            'complemento' => trim($complemento ?? '')
        ];
        // return true so calling views can redirect / continue flow
        return true;
    }


    //Cadastro de endereco
    public function cadastroEndereco($cep, $rua, $numero, $bairro, $cidade, $estado, $complemento){
        if(empty($cep) or empty($rua) or empty($numero) or empty($bairro) or empty($cidade) or empty($estado) or empty($complemento)){
            return false;
        }

        return $this->enderecoModel->registerEndereco($cep, $rua, $numero, $bairro, $cidade, $estado, $complemento);
    }

    // Buscar endereco resumido
    public function getEnderecoResumido($id, $cidade, $bairro){
        return $this->enderecoModel->getEnderecoResumido($id, $cidade, $bairro);
    }

    // Buscar informações do endereco (pelo endereco)
    public function getEnderecoInfo($id){
        return $this->enderecoModel->getEnderecoInfo($id);
    }

    //Atualizar informações de cadastro do endereco
    public function updateEndereco($id, $cep, $rua, $numero, $bairro, $cidade, $estado, $complemento){

        if(empty($id) or empty($cep) or empty($rua) or empty($numero) or empty($bairro) or empty($cidade) or empty($estado) or empty($complemento)){
            return false;
        }

        return $this->enderecoModel->updateEndereco($id, $cep, $rua, $numero, $bairro, $cidade, $estado, $complemento);
    }

    //Excluir cadastro do endereco
    public function deleteEndereco($id){
        if(empty($id)){
            return false;
        }
        return $this->enderecoModel->deleteEndereco($id);
    }

}
?>