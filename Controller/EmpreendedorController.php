<?php
namespace Controller;

use Model\Empreendedor;

class EmpreendedorController{
    private $empreendedorModel;
    
    public function __construct(){
        $this->empreendedorModel = new Empreendedor();
    }

    public function salvarEmpreendedor($nome, $email, $senha, $cnpj_cpf) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['form_empreendimento']['empreendedor'] = [
            'nome' => trim($nome ?? ''),
            'email' => trim($email ?? ''),
            'senha' => trim($senha ?? ''),
            'cnpj_cpf' => trim($cnpj_cpf ?? '')
        ];
        // return true so calling views can redirect / continue flow
        return true;
    }

    //Verificar se empreendedor está loggado
    public function isLoggedIn(){
        return isset ($_SESSION['id_empreendimento']);
    }

    //Login do empreendedor
    public function loginEmpreendedor($cnpj_cpf, $senha){
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $empreendedor = $this->empreendedorModel->getEmpreendedorByCNPJ_CPF($cnpj_cpf);

        if($empreendedor && password_verify($senha, $empreendedor['senha'])){
            $_SESSION['id_empreendedor'] = $empreendedor['id'];
            $_SESSION['id_empreendimento'] = $empreendedor['id_empreendimento'];
            $_SESSION['id_endereco'] = $empreendedor['id_endereco'];

            return true;
        }
        return false;
    }

    //Buscar CNPJ/CPF já cadastrado
    public function getEmpreendedorByCNPJ_CPF($cnpj_cpf){
        return $this->empreendedorModel->getEmpreendedorByCNPJ_CPF($cnpj_cpf);
    }

    // Redefinir senha por email
    public function resetPasswordByEmail($email, $senha){
        $email = filter_var(trim($email ?? ''), FILTER_VALIDATE_EMAIL);
        $senha = trim($senha ?? '');
        if(!$email || empty($senha) || strlen($senha) < 6) return false;
        return $this->empreendedorModel->updatePasswordByEmail($email, $senha);
    }

    //Buscarr pelo nome do empreendedor
    public function getEmpreendedorName($id, $nome){
        return $this->empreendedorModel->getEmpreendedorName($id, $nome);
    }

    //Busca de informações do empreendedor (pelo empreendedor)
    public function getEmpreendedorInfo($id){
        return $this->empreendedorModel->getEmpreendedorInfo($id);
    }

    //Atualizar informações de cadastro do empreendedor
    public function updateEmpreendedor($id, $nome, $email, $cnpj_cpf){
        if(empty($id) || empty($nome) || empty($email) || empty($cnpj_cpf)){
            return false;
        }
        return $this->empreendedorModel->updateEmpreendedor($id, $nome, $email, $cnpj_cpf);
    }

    //Deletar cadastro do empreendedor
    public function deleteEmpreendedor($id){
        if(empty($id)){
            return false;
        }
        return $this->empreendedorModel->deleteEmpreendedor($id);
    }









    public function finalizarCadastroCompleto() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['form_empreendimento'])) {
        error_log("finalizarCadastroCompleto chamado sem dados na sessão.");
        return false;
    }

    $dados = $_SESSION['form_empreendimento'];

    // Instancia todos os models necessários
    $enderecoModel = new \Model\Endereco();
    $empreendimentoModel = new \Model\Empreendimento();
    // $this->empreendedorModel já está disponível no controller

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
            throw new \Exception("Falha ao registrar o endereço.");
        }

        // ETAPA 2: Salvar Empreendimento com o ID do endereço e pegar seu ID
        $idEmpreendimento = $empreendimentoModel->registerEmpreendimento(
            $dados['empreendimento1']['nome'] ?? null,
            $dados['empreendimento1']['telefone'] ?? null,
            $dados['empreendimento1']['link_whatsapp'] ?? null,
            $dados['empreendimento2']['descricao'] ?? null,
            $dados['empreendimento1']['hr_funcionamento'] ?? null,
            $dados['empreendimento3']['foto'] ?? null,
            $idEndereco // <-- A "cola"
        );

        if (!$idEmpreendimento) {
            throw new \Exception("Falha ao registrar o empreendimento.");
        }

        // ETAPA 3: Salvar Empreendedor com o ID do empreendimento
        $idEmpreendedor = $this->empreendedorModel->registerEmpreendedor(
            $dados['empreendedor']['nome'] ?? null,
            $dados['empreendedor']['email'] ?? null,
            $dados['empreendedor']['senha'] ?? null,
            $dados['empreendedor']['cnpj_cpf'] ?? null,
            $idEmpreendimento // <-- A "cola" final
        );

        if (!$idEmpreendedor) {
            throw new \Exception("Falha ao registrar o empreendedor.");
        }

        // Se tudo deu certo, limpa a sessão e retorna sucesso
        unset($_SESSION['form_empreendimento']);
        return true;

    } catch (\Exception $e) {
        error_log("Erro no cadastro completo: " . $e->getMessage());
        return false;
    }
}
}
?>