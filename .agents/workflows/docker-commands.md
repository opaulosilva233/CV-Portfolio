---
description: Regra obrigatória - todos os comandos do projeto devem ser executados dentro da bash do container Docker
---

# Execução de Comandos no Projeto

> [!CAUTION]
> **NUNCA** executar comandos do projeto diretamente no terminal do host. Todos os comandos devem ser executados dentro da bash do container Docker.

## Regra

Todos os comandos relacionados com o projeto (PHP, Artisan, Composer, NPM, Node, etc.) **devem ser executados dentro do container Docker `app`**.

## Iniciar o Projeto

Ao abrir o projeto, é necessário recriar os containers para garantir que os volumes são montados corretamente (problema comum no WSL 2):

```bash
docker compose down && docker compose up -d
```

> [!IMPORTANT]
> **Sempre** usar `docker compose down && docker compose up -d` em vez de apenas `docker compose up -d` ao iniciar o projeto. Sem isto, os ficheiros podem não ser montados no container e comandos como `php artisan tinker` irão falhar.

## Como Executar

### Entrar na bash do container

```bash
docker compose exec app bash
```

### Ou executar um comando diretamente

```bash
docker compose exec app <comando>
```

### Exemplos

```bash
# Artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan cache:clear

# Composer
docker compose exec app composer install
docker compose exec app composer require <pacote>

# NPM
docker compose exec app npm install
docker compose exec app npm run dev
docker compose exec app npm run build

# PHP
docker compose exec app php -v
docker compose exec app php artisan tinker
```

## Informação do Container

- **Serviço Docker:** `app`
- **Working directory:** `/var/www/html`
- **Ficheiro compose:** `docker-compose.yml`
- **Diretório do projeto:** `/home/paulo/projetos/CV-Portfolio`
