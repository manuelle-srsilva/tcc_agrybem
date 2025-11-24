<?php

namespace Controller;

use Model\Cliente;

class ClienteController{
    private $clienteModel;
    
    public function __construct(){
        $this->clienteModel = new Cliente();
    }

    //Cadastro de cliente
    public function cadastroCliente($nome, $email, $telefone, $senha){
        if(empty($nome) or empty($email) or empty($senha) or empty($telefone)){
            return false;
        }

        return $this->clienteModel->registerClient($nome, $email, $telefone, $senha);
    }
    
    // E-MAIL JÁ CADASTRADO?
    public function checkClienteByEmail($email){
        return $this->clienteModel->getClientByEmail($email);
    }

    //Login de cliente
    public function loginCliente($email, $senha){
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $cliente = $this->clienteModel->getClientByEmail($email);
        
        if($cliente && password_verify($senha, $cliente['senha'])){
            $_SESSION['id'] = $cliente['id'];
            $_SESSION['nome'] = $cliente['nome'];
            $_SESSION['email'] = $cliente['email'];
            $_SESSION['telefone'] = $cliente['telefone'];

            return true;
        }
        return false;
    }

    //Verificar se o cliente está logado
    public function isLoggedIn(){
        return isset($_SESSION['id']);
    }

    // Buscar nome do cliente
    public function getClienteName($id, $nome){
        return $this->clienteModel->getClienteName($id, $nome);
    }

    // Buscar informações do cliente (pelo cliente)
    public function getClienteInfo($id, $nome, $email, $telefone, $senha){
        return $this->clienteModel->getClienteInfo($id, $nome, $email, $telefone, $senha);
    }

    //Atualizar informações de cadastro do cliente
    public function updateClient($id, $nome, $email, $telefone){

        if(empty($id) or empty($nome) or empty($email) or empty($telefone)){
            return false;
        }

        return $this->clienteModel->updateClient($id, $nome, $email, $telefone);
    }

    //Excluir cadastro do cliente
    public function deleteCliente($id){
        if(empty($id)){
            return false;
        }
        return $this->clienteModel->deleteCliente($id);
    }

    // Redefinir senha por email
    public function resetPasswordByEmail($email, $senha){
        // sanitização e validação básica
        $email = filter_var(trim($email ?? ''), FILTER_VALIDATE_EMAIL);
        $senha = trim($senha ?? '');
        if(!$email || empty($senha) || strlen($senha) < 6) return false;
        return $this->clienteModel->updatePasswordByEmail($email, $senha);
    }

}
?>