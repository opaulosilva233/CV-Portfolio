# Portefólio e CV Online Profissional

## Objetivo do Projeto

Este projeto consiste no desenvolvimento de um site pessoal que atua simultaneamente como portefólio e Currículo Online (CV). O objetivo principal é disponibilizar uma plataforma centralizada onde é possível apresentar projetos, competências e experiências profissionais de forma moderna, limpa e profissional.

Além da vertente pública, o site inclui uma área de administração (backoffice) completa, permitindo a gestão autónoma de todos os conteúdos (textos, imagens, projetos, dados curriculares) sem necessidade de intervenção direta no código-fonte.

## Tecnologias Utilizadas

O projeto é construído sobre uma stack robusta e moderna, garantindo performance, escalabilidade e facilidade de manutenção:

- **Backend framework**: [Laravel](https://laravel.com/) (PHP)
- **Frontend framework**: [Vue.js](https://vuejs.org/) (JavaScript)
- **Estilização**: Tailwind CSS (Recomendado/A definir)
- **Base de Dados**: MySQL/PostgreSQL (A definir na configuração)

## Estrutura Geral do Projeto

A estrutura segue as convenções padrão do Laravel com Vue.js:

- `app/`: Lógica de backend (Models, Controllers, etc.)
- `resources/js/`: Código fonte Vue.js (Componentes, Views, Stores)
- `resources/is/Components`: Componentes Vue reutilizáveis
- `resources/is/Pages`: Páginas da aplicação (Frontend e Backoffice)
- `routes/`: Definições de rotas (API e Web)
- `database/`: Migrations e Seeders

## Funcionalidades Previstas

### 1. Área Pública (Frontend)
- **Homepage**: Introdução impactante com design clean e moderno.
- **Portefólio**: Galeria de projetos com filtros, detalhes e links.
- **Sobre Mim / CV**: Linha do tempo de experiências, formação e competências.
- **Contactos**: Formulário de contacto e links para redes sociais.

### 2. Área de Administração (Backoffice)
- **Dashboard**: Visão geral do site.
- **Gestão de Perfil**: Edição de dados pessoais, foto, bio.
- **Gestão de Projetos**: Criar, editar e remover projetos do portefólio.
- **Gestão de CV**: Adicionar experiências profissionais, educação e skills.
- **Gestão de Conteúdos**: Edição de textos estáticos do site.

## Instruções de Instalação e Execução

### Pré-requisitos
- PHP >= 8.1
- Composer
- Node.js & NPM

### Passo a Passo

1. **Clonar o repositório**
   ```bash
   git clone <url-do-repositorio>
   cd <nome-da-pasta>
   ```

2. **Instalar dependências de Backend**
   ```bash
   composer install
   ```

3. **Instalar dependências de Frontend**
   ```bash
   npm install
   ```

4. **Configurar Ambiente**
   - Copie o ficheiro de exemplo `.env`:
     ```bash
     cp .env.example .env
     ```
   - Gere a chave da aplicação:
     ```bash
     php artisan key:generate
     ```
   - Configure os dados da base de dados no ficheiro `.env`.

5. **Executar Migrações**
   ```bash
   php artisan migrate
   ```

6. **Iniciar Servidores de Desenvolvimento**
   - Backend (Num terminal):
     ```bash
     php artisan serve
     ```
   - Frontend (Noutro terminal):
     ```bash
     npm run dev
     ```

7. **Aceder**
   - O site estará disponível em `http://localhost:8000`.
