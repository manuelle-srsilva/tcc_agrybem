<?php
namespace Controller;

use Model\Instituicao;

require_once __DIR__ .'/../Config/configuration.php';

class InstituicaoController{
    private $instituicaoModel;
    public function __construct(){
        $this->instituicaoModel = new Instituicao();
    }

    public function salvarInstituicao($nome, $email, $senha, $cnpj, $link_whatsapp) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['form_instituicao']['instituicao1'] = [
            'nome' => trim($nome ?? ''),
            'email' => trim($email ?? ''),
            'senha' => trim($senha ?? ''),
            'cnpj' => trim($cnpj ?? ''),
            'link_whatsapp' => trim($link_whatsapp ?? '')
        ];
        // return true so calling views can redirect / continue flow
        return true;
    }

    public function salvarInstituicaoDescricao($descricao) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['form_instituicao']['instituicao2'] = [
            'descricao' => trim($descricao ?? '')
        ];
        // return true so calling views can redirect / continue flow
        return true;
    }

    public function salvarInstituicaoFoto($foto) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // store raw photo binary (do not trim binary data)
        $_SESSION['form_instituicao']['instituicao3'] = [
            'foto' => ($foto ?? null)
        ];
        // return true so calling views can redirect / continue flow
        return true;
    }

    //Cadastro de instituição
    public function registerInstituicao($nome, $email, $senha, $cnpj, $link_whatsapp, $descricao, $foto, $id_endereco){
        if(empty($nome) || empty($email) || empty($senha) || empty($cnpj) || empty($link_whatsapp) || empty($descricao) || empty($foto) || empty($id_endereco)){
            return false;
        }
        return $this->instituicaoModel->registerInstituicao($nome, $email, $senha, $cnpj, $link_whatsapp, $descricao, $foto, $id_endereco);
    }

    //Verificar se instituição está loggada
    public function isLoggedIn(){
        return isset ($_SESSION['id_instituicao']);
    }

    //Login da instituição
    public function loginInstituicao($cnpj, $senha){

        $instituicao = $this->instituicaoModel->getInstituicaoByCNPJ($cnpj);

        if($instituicao && password_verify($senha, $instituicao['senha'])){
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $_SESSION['id_instituicao'] = $instituicao['id'];
            $_SESSION['nome_instituicao'] = $instituicao['nome'];
            $_SESSION['id_endereco'] = $instituicao['id_endereco'];

            return true;
        }
        return false;
    }

    //Buscar instituição pelo CNPJ
    public function getInstituicaoByCNPJ($cnpj){
        return $this->instituicaoModel->getInstituicaoByCNPJ($cnpj);
    }

    //Buscar nome da instituição
    public function getInstituicaoName($id, $nome){
        return $this->instituicaoModel->getInstituicaoName($id, $nome);
    }

    public function getInstituicaoFoto($id){
        return $this->instituicaoModel->getInstituicaoFoto($id);
    }


    //Buscar informações da instituição (pelo empreendedor)
    public function getInstituicaoInfo($id){
        return $this->instituicaoModel->getInstituicaoInfo($id);
    }

    public function getAllInstituicoes(){
        return $this->instituicaoModel->getAllInstituicoes();
    }

    //Atualizar informações de cadastro da instituição
    public function updateInstituicao($id, $nome, $email, $cnpj, $link_whatsapp){
        if(empty($id) || empty($nome) || empty($email) || empty($cnpj) || empty($link_whatsapp)){
            return false;
        }
        return $this->instituicaoModel->updateInstituicao($id, $nome, $email, $cnpj, $link_whatsapp);
    }

    public function updateInstituicaoDescricao($id, $descricao){
        if(empty($id) || empty($descricao)){
            return false;
        }
        return $this->instituicaoModel->updateInstituicaoDescricao($id, $descricao);
    }

    public function updateInstituicaoFoto($id, $foto){
        if(empty($id) or empty($foto)){
            return false;
        }

        return $this->instituicaoModel->updateInstituicaoFoto($id, $foto);
    }

    //Apagar cadastro de instituição
    public function deleteInstituicao($id){
        if(empty($id)){
            return false;
        }
        return $this->instituicaoModel->deleteInstituicao($id);
    }



    

    public function finalizarCadastroCompleto() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['form_instituicao'])) {
        error_log("Cadastro de instituição finalizado sem dados na sessão.");
        return false;
    }

    $dados = $_SESSION['form_instituicao'];
    $enderecoModel = new \Model\Endereco();

    try {
        // ETAPA 1: Salvar Endereço e pegar seu ID
        $idEndereco = $enderecoModel->registerEndereco(
            $dados['endereco']['cep'] ?? null,
            $dados['endereco']['rua'] ?? null,
            $dados['endereco']['numero'] ?? null,
            $dados['endereco']['bairro'] ?? null,
            $dados['endereco']['cidade'] ?? null,
            $dados['endereco']['estado'] ?? null,
            $dados['endereco']['complemento'] ?? null
        );

        if (!$idEndereco) {
            throw new \Exception("Falha ao registrar o endereço da instituição.");
        }

        // ETAPA 2: Salvar Instituição com o ID do endereço
        // A função registerInstituicao no Model já foi ajustada para retornar o ID
        $idInstituicao = $this->instituicaoModel->registerInstituicao(
            $dados['instituicao1']['nome'] ?? null,
            $dados['instituicao1']['email'] ?? null,
            $dados['instituicao1']['senha'] ?? null,
            $dados['instituicao1']['cnpj'] ?? null,
            $dados['instituicao1']['link_whatsapp'] ?? null,
            $dados['instituicao2']['descricao'] ?? null,
            $dados['instituicao3']['foto'] ?? null,
            $idEndereco // <-- A "cola"
        );

        if (!$idInstituicao) {
            throw new \Exception("Falha ao registrar a instituição.");
        }

        unset($_SESSION['form_instituicao']);
        return true;

    } catch (\Exception $e) {
        error_log("Erro no cadastro de instituição: " . $e->getMessage());
        return false;
    }
}
}
?>