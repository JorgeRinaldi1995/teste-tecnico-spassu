# Sistema de Gerenciamento de Livros

Aplicação desenvolvida em Symfony para gerenciamento de livros, autores e assuntos.

## Tecnologias

- PHP 8.4+
- Symfony 7.x
- Doctrine ORM
- MySQL
- Bootstrap 5
- Twig
- DDEV (Docker)
---

## Funcionalidades

### Livros

- Cadastro de livros
- Edição de livros
- Visualização de livros
- Listagem paginada
- Visualização detalhada
- Associação com Autores
- Associação com Assunto

### Autores

- Cadastro de autores
- Visualização de autores
- Edição de autores

### Assuntos

- Cadastro de assuntos
- Visualização de assuntos
- Edição de assuntos

### Relatórios

- Consulta consolidada através de View SQL
- Estatísticas de livros por autor
- Estatísticas de livros por assunto

---

## Instalação

Primeiro passo, instale o : [Docker](https://docs.docker.com/engine/install/) e o [DDEV](https://docs.ddev.com/en/stable/users/install/). 


Clone o projeto:

```bash
git clone git@github.com:usuario/projeto.git
```

Acesse o diretório:

```bash
cd projeto
```

Inicie o DDEV:

```bash
ddev start
```

Instale as dependências:

```bash
ddev composer install
```

Configure o arquivo `.env.local`:

```env
DATABASE_URL="mysql://db:db@db:3306/db?sslmode=disable&charset=utf8mb4&serverVersion=11.8.0-mariadb"
```

Execute as migrations:

```bash
ddev console doctrine:migrations:migrate
```

---

## Estrutura do Projeto

```text
src/
├── Controller/
├── Service/
├── Repository/
├── Entity/
├── Form/


templates/
├── livro/
├── autor/
├── assunto/
├── pages/
├── components/
└── partials/
```

---

## Arquitetura

O projeto segue uma separação em camadas:

```text
Controller
    ↓
Service
    ↓
Repository
    ↓
Doctrine ORM
    ↓
Banco de Dados
```

### Responsabilidades

#### Controller

Responsável por receber requisições HTTP e retornar respostas.

#### Service

Contém as regras de negócio.

#### Repository

Responsável pela persistência e consultas.

#### Entity

Representação do domínio da aplicação.

---

## Banco de Dados

### Entidades

#### Livro

| Campo | Tipo |
|---------|---------|
| codl | int |
| titulo | string |
| editora | string |
| edicao | int |
| anoPublicacao | string |
| valor | decimal |

#### Autor

| Campo | Tipo |
|---------|---------|
| codau | int |
| nome | string |

#### Assunto

| Campo | Tipo |
|---------|---------|
| codas | int |
| descricao | string |

---

## Comandos Úteis

Gerar migration:

```bash
ddev console make:migration
```

Executar migration:

```bash
ddev console doctrine:migrations:migrate
```

Limpar cache:

```bash
ddev console cache:clear
```

Validar entidades:

```bash
ddev console doctrine:schema:validate
```

---

## Boas Práticas Utilizadas

- SOLID
- Separation of Concerns
- Dependency Injection
- Service Layer Pattern
- Doctrine ORM
- Persistence Layer
- Validação com Symfony Validator
- Twig Components e Partials

---

## Licença

Projeto desenvolvido para fins de avaliação técnica e estudo.