# 🌾 AgryBem

**Conectando pequenos agricultores, consumidores locais e instituições de apoio — menos desperdício, mais impacto social.**

🔗 **Aplicação hospedada:** [https://agrybem.kesug.com/](https://agrybem.kesug.com/)
📦 **Repositório:** [https://github.com/Gloterianota349/AgryBem](https://github.com/Gloterianota349/AgryBem)

---

# 📘 Índice

1. [Visão Geral](#visão-geral)
2. [Problemática](#problemática)
3. [Solução — AgryBem](#solução--agrybem)
4. [Tecnologias Utilizadas](#tecnologias-utilizadas)
5. [Arquitetura do Projeto](#arquitetura-do-projeto)
6. [Funcionalidades Principais](#funcionalidades-principais)
7. [Modelagem do Banco de Dados](#modelagem-do-banco-de-dados)
8. [Prototipagem no Figma](#prototipagem-no-figma)
9. [Gestão do Projeto](#gestão-do-projeto)
10. [Testes Automatizados](#testes-automatizados)
11. [Como Executar o Projeto](#como-executar-o-projeto)
12. [Autores](#autores)

---

# 🌱 Visão Geral

O **AgryBem** é uma plataforma digital que conecta **pequenos agricultores, consumidores locais e instituições de caridade**, promovendo economia local, inclusão digital, redução de desperdício e fortalecimento da segurança alimentar.

O projeto foi desenvolvido seguindo boas práticas de engenharia de software, prototipagem, responsividade e arquitetura MVC.

---

# 🧩 Problemática

O Brasil é uma potência agrícola mundial, produzindo alimentos diversos e de alto valor nutricional. No entanto, **grande parte dessa produção é direcionada à exportação**, enquanto **a agricultura familiar — responsável por grande parte do alimento que chega ao brasileiro — é invisibilizada**.

Com isso, problemas graves persistem:

* Baixa visibilidade dos pequenos agricultores
* Falta de acesso à tecnologia
* Dificuldade no escoamento da produção
* Desperdício de alimentos
* Má distribuição para populações vulneráveis

Essa desigualdade estrutural impacta tanto produtores quanto consumidores e instituições que dependem de doações.

---

# 💡 Solução — **AgryBem**

O **AgryBem** surge como alternativa tecnológica que:

### ✔️ Dá visibilidade aos pequenos agricultores

Exibindo produtos, preços, informações e localização.

### ✔️ Conecta produtores e consumidores locais

Fortalece o comércio nas comunidades e reduz intermediários.

### ✔️ Reduz o desperdício

Agiliza o escoamento da produção e evita descarte de alimentos de qualidade.

### ✔️ Fortalece a segurança alimentar

Com uma aba exclusiva para **doações**, vinculada a instituições de caridade.

### ✔️ Promove inclusão digital

Via integração da **API VLibras**, garantindo acessibilidade para pessoas surdas.

### ✔️ Ajuda na logística

Com integração da **Google Maps API**, mostrando produtores e instituições próximos.

A plataforma atua como uma **ponte social** entre produção, consumo e solidariedade.

---

# 🧪 Tecnologias Utilizadas

### **Front-end**

* HTML5
* CSS3
* JavaScript
* Layout responsivo (media queries + flexbox)

### **Back-end**

* PHP
* PDO
* Programação Orientada a Objetos (POO)
* Arquitetura MVC

### **Banco de Dados**

* MySQL
* Modelagem conceitual, lógica e física

### **APIs Integradas**

* VLibras
* Google Maps

### **Testes Automatizados**

* PHPUnit

### **Ferramentas de Produção**

* Jira (gestão do projeto)
* Figma (prototipagem)
* GitHub (versionamento e colaboração)

---

# 🏛️ Arquitetura do Projeto

O AgryBem utiliza **MVC (Model–View–Controller)** para organização e escalabilidade.

### **Model**

Representa entidades, regras e comunicação com o banco de dados.

### **View**

Interface gráfica e páginas navegáveis.

### **Controller**

Processa requisições, executa regras e retorna respostas para o usuário.

Essa arquitetura garante:

* Melhor manutenção
* Código mais limpo
* Evita duplicações
* Facilita evolução do projeto

---

# ⭐ Funcionalidades Principais

### 👨‍🌾 Área do Agricultor

* Cadastro de produtos
* Atualização de preços
* Gerenciamento de estoque
* Localização no mapa

### 🛒 Área do Consumidor

* Exploração dos produtos
* Busca por agricultores
* Visualização no mapa
* Contato rápido

### 💚 Área de Doações

* Cadastro de instituições
* Doação de alimentos excedentes
* Mapa de instituições próximas

### ♿ Acessibilidade

* Integração completa com **VLibras**

### 🗺️ Localização Geográfica

* Google Maps API integrada em:

  * Instituições
  * Produtores
  * Comércio local

---

# 🗄️ Modelagem do Banco de Dados

Projetado seguindo:

### ✔️ Modelo Conceitual

DER com entidades como:

* Usuário
* Agricultor
* Produto
* Doação
* Instituição

### ✔️ Modelo Lógico

* Normalização
* Chaves primárias e estrangeiras
* Cardinalidades definidas adequadamente

### ✔️ Modelo Físico (MySQL)

* Tabelas otimizadas
* Integridade referencial
* Alta performance

---

# 🎨 Prototipagem no Figma

A equipe desenvolveu um protótipo navegável contendo:

* Fluxos de cadastro e login
* Catálogo de produtos
* Área para doações
* Navegação completa da plataforma

Também foram aplicados:

* Princípios de acessibilidade
* Testes de navegabilidade
* Cores e tipografias adequadas ao público-alvo

---

# 🗂️ Gestão do Projeto (Jira)

O Jira foi usado para:

* Criar tarefas
* Definir prioridades
* Dividir responsabilidades
* Acompanhar o avanço via Kanban
* Organizar todo o ciclo de desenvolvimento

---

# 🧪 Testes Automatizados

Utilizamos **PHPUnit** para garantir estabilidade e confiabilidade dos métodos críticos:

* Cadastro
* Atualização
* Consulta
* Comunicação com banco

Isso reduz erros e facilita a manutenção futura.

---

# ▶️ Como Executar o Projeto

### 1. Clone o repositório

```bash
git clone https://github.com/Gloterianota349/AgryBem.git
```

### 2. Configure o servidor local

Use XAMPP, WAMP ou equivalente.

Cole o projeto em:

```
/htdocs/agrybem
```

### 3. Importe o banco de dados

* Abra o phpMyAdmin ou MySQL
* Crie um banco chamado `agrybem`
* Crie as tabelas conforme Tabelas - Banco de dados.pdf

### 4. Configure a conexão no arquivo `config.php`

```php
$dbname = "agrybem";
$host = "localhost";
$user = "root";
$pass = "";
$port = "3306" (ou a porta ao qual está contida seu banco);
```

### 5. Acesse o sistema

```
http://localhost/agrybem
```

---

# 👥 Autores

Projeto desenvolvido pela **Equipe AgryBem**, com foco em tecnologia social, inclusão e fortalecimento da agricultura familiar.
