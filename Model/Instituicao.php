<?php
// CRIAR INSTITUIÇÃO
// LOGIN
// OBTER NOME DA INSTITUIÇÃO
// OBTER TODAS AS INFORMAÇÕES DA INSTITUIÇÃO

namespace Model;

use Model\Connection;

use PDO;
use PDOException;

class Instituicao {
    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function registerInstituicao ($nome, $email, $senha, $cnpj, $link_whatsapp, $descricao, $foto, $id_endereco) {
        try {
            $sql = 'INSERT INTO instituicao (nome, email, senha, cnpj, link_whatsapp, descricao, foto, id_endereco) VALUES (:nome, :email, :senha, :cnpj, :link_whatsapp, :descricao, :foto, :id_endereco)';

            $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":senha", $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
            $stmt->bindParam(":link_whatsapp", $link_whatsapp, PDO::PARAM_STR);
            $stmt->bindParam(":descricao", $descricao, PDO::PARAM_STR);
            $stmt->bindParam(":foto", $foto, PDO::PARAM_LOB);
            $stmt->bindParam(":id_endereco", $id_endereco, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // CORREÇÃO: Retorna o ID da instituição recém-criada
                return $this->db->lastInsertId();
            }
            return false;
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function getInstituicaoByCNPJ($cnpj) {
        try {
            $sql = "SELECT * FROM instituicao WHERE cnpj = :cnpj LIMIT 1";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $error) {
            echo "Erro ao buscar informações do usuário: " . $error->getMessage();
        }
    }

    public function getInstituicaoName ($id, $nome) {
        try {
            $sql = "SELECT nome FROM instituicao WHERE id = :id AND nome = :nome";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            echo "Erro ao buscar informações: " . $error->getMessage();
            return false;
        }
    }

    public function getInstituicaoFoto ($id) {
        try {
            $sql = "SELECT foto FROM instituicao WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            echo "Erro ao buscar informações: " . $error->getMessage();
            return false;
        }
    }

    public function getInstituicaoInfo ($id) {
        try {
            $sql = "SELECT nome, email, senha, cnpj, link_whatsapp, descricao, id_endereco FROM instituicao WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $error) {
            echo "Erro ao buscar informações: " . $error->getMessage();
            return false;
        }
    }

    public function getAllInstituicoes(){
        try {
            $sql = "SELECT i.id, i.nome, i.foto, e.cidade, e.bairro FROM instituicao i LEFT JOIN endereco e ON i.id_endereco = e.id ORDER BY i.nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $error){
            echo "Erro ao buscar instituições: " . $error->getMessage();
            return [];
        }
    }

    public function updateInstituicao ($id, $nome, $email, $cnpj, $link_whatsapp) {
        try {
            $sql = "UPDATE instituicao SET nome = :nome, email = :email, cnpj = :cnpj, link_whatsapp = :link_whatsapp WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":cnpj", $cnpj, PDO::PARAM_STR);
            $stmt->bindParam(":link_whatsapp", $link_whatsapp, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function updateInstituicaoDescricao ($id, $descricao) {
        try {
            $sql = "UPDATE instituicao SET descricao = :descricao WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":descricao", $descricao, PDO::PARAM_STR);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function updateInstituicaoFoto ($id, $foto) {
        try {
            $sql = 'UPDATE instituicao SET foto = :foto WHERE id = :id';

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":foto", $foto, PDO::PARAM_LOB);
            
            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function deleteInstituicao ($id) {
        try {
            $sql = "DELETE FROM instituicao WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            return $stmt->execute();
        }
        catch (PDOException $error) {
            echo "Erro ao executar o comando " . $error->getMessage();
            return false;
        }
    }

    public function updatePasswordByEmail($email, $senha){
        try{
            $sql = "UPDATE instituicao SET senha = :senha WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $hashed = password_hash($senha, PASSWORD_DEFAULT);
            $stmt->bindParam(":senha", $hashed, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            return $stmt->execute();
        } catch(PDOException $error){
            echo "Erro ao atualizar senha: " . $error->getMessage();
            return false;
        }
    }
}

?>