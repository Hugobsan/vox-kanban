
<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

# Vox Kanban


**Vox Kanban** é um sistema completo de gerenciamento de quadros Kanban, colaborativo e moderno, desenvolvido em Laravel, com frontend desacoplado via Vite. A infraestrutura já está preparada para integração em tempo real usando WebSockets (Laravel Reverb) e cache/filas com Redis, mas **até o momento não há nenhum channel implementado com Reverb nem mecanismos explícitos de cache utilizando Redis** — esses recursos foram planejados para uso futuro, caso houvesse tempo hábil.

> **Nota:** A infraestrutura Docker Compose utilizada neste projeto foi reaproveitada e adaptada de um projeto pessoal anterior, visando agilidade e robustez no setup do ambiente.

---

## 🚀 Tecnologias Utilizadas

- **Laravel 10+** (Backend, API REST, autenticação, filas, eventos)
- **PHP 8.2 (FPM/Alpine)**
- **PostgreSQL 16** (Banco de dados relacional)
- **Redis** (Cache, filas, sessões)
- **Nginx** (Servidor web)
- **Vite + Node.js** (Build e hot reload do frontend)
- **Bootstrap 5** (Estilização e componentes UI)
- **JQuery & Ajax** (Interações dinâmicas)
- **JQuery UI** (Componentes Drag & Drop)
- **Material Icons** (Ícones modernos)
- **MailHog** (SMTP fake para desenvolvimento)
- **Laravel Reverb** (WebSockets para notificações em tempo real)
- **Docker & Docker Compose** (Ambiente isolado e replicável)
- **Supervisor** (Gerenciamento de processos: queue, scheduler)
- **Makefile** (Automação de comandos)

---

## 📋 Funcionalidades Principais

- Gerenciamento de quadros Kanban com múltiplos usuários
- Permissões e papéis customizáveis por board
- Colunas, tarefas, etiquetas, comentários
- Sistema de convites e onboarding
- API RESTful para conexão com frontend desacoplado

---

## 🏗️ Estrutura do Projeto

O projeto já vem pronto para rodar em ambientes de desenvolvimento e produção, com ou sem Docker. Abaixo estão as instruções para cada cenário.

---

## 🐳 Instalação e Execução (Desenvolvimento com Docker)

### Pré-requisitos
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Make](https://www.gnu.org/software/make/) (opcional, mas recomendado)

### Passos Rápidos

1. Clone o repositório:
   ```bash
   git clone https://github.com/Hugobsan/vox-kanban.git
   cd vox-kanban
   ```
2. Instale e suba tudo com um comando:
   ```bash
   make install
   ```
   > Isso irá copiar o .env, buildar a imagem, subir os containers, instalar dependências, rodar migrações, seeders e preparar o ambiente.

3. Acesse:
   - Aplicação: [http://localhost:8000](http://localhost:8000)
   - MailHog: [http://localhost:8025](http://localhost:8025)
   - Vite Dev (frontend): [http://localhost:5173](http://localhost:5173) (se rodando em modo dev)

#### Ambiente de Desenvolvimento com Hot Reload (Vite + Node.js)

Para desenvolvimento frontend com hot reload:

```bash
make setup-dev
```
> Isso sobe o serviço Node.js e ativa o Vite em modo desenvolvimento.

#### Comandos Úteis

- `make shell` — entra no shell do container da aplicação
- `make artisan ARGS="migrate"` — executa comandos do Artisan
- `make composer ARGS="install"` — executa comandos do Composer
- `make test` — executa os testes automatizados
- `make logs` — mostra logs de todos os serviços

Veja mais comandos no próprio [Makefile](Makefile) e no [DOCKER.md](DOCKER.md).

---

## 🏭 Instalação e Execução (Produção com Docker)

1. Configure seu arquivo `.env` de produção (baseie-se no `.env.docker` e ajuste variáveis sensíveis)
2. Faça o build da imagem e suba os serviços:
   ```bash
   make build
   make up
   ```
3. Execute as migrações e seeders (se necessário):
   ```bash
   make artisan ARGS="migrate --force"
   make artisan ARGS="db:seed --force"
   ```
4. Gere as chaves e caches:
   ```bash
   make artisan ARGS="key:generate"
   make artisan ARGS="config:cache"
   make artisan ARGS="route:cache"
   make artisan ARGS="view:cache"
   ```
5. Acesse a aplicação normalmente em [http://localhost:8000](http://localhost:8000) (ou configure seu domínio/SSL conforme necessário).

> **Dicas:**
> - Altere as credenciais padrão do banco e Redis para produção.
> - Configure HTTPS e firewall.
> - Use volumes persistentes para dados.

---

## 💻 Instalação e Execução (Desenvolvimento sem Docker)

> **Recomendado apenas para quem já tem PHP, Composer, Node.js, PostgreSQL e Redis instalados localmente.**

1. Instale as dependências do backend:
   ```bash
   composer install
   ```
2. Instale as dependências do frontend:
   ```bash
   npm install
   ```
3. Copie o arquivo de ambiente:
   ```bash
   cp .env.example .env
   # ou use .env.docker como base
   ```
4. Configure as variáveis de ambiente conforme seu setup local (DB, Redis, etc).
5. Gere a chave da aplicação:
   ```bash
   php artisan key:generate
   ```
6. Rode as migrações e seeders:
   ```bash
   php artisan migrate --seed
   ```
7. Suba o servidor Laravel:
   ```bash
   php artisan serve
   ```
8. Em outro terminal, rode o Vite para o frontend:
   ```bash
   npm run dev
   ```

#### Serviços necessários localmente:
- PHP >= 8.2 com extensões (pdo_pgsql, redis, etc)
- Composer
- Node.js >= 18
- PostgreSQL >= 13
- Redis

---

## 📝 Notas e Dicas

- O ambiente Docker já está pronto para uso, com scripts automatizados para desenvolvimento e produção.
- Para resetar tudo, use `make clean` (remove containers e volumes).
- Para backup/restore do banco: `make backup-db` e `make restore-db FILE=backup.sql`
- Para logs detalhados: `make logs-app`, `make logs-queue`, `make logs-reverb`, etc.
- O WebSocket (Reverb) já está pré-configurado, basta instalar o pacote Laravel Reverb se desejar usar.
- Veja [DOCKER.md](DOCKER.md) para detalhes avançados de infraestrutura.

---

## 📄 Licença

Este projeto segue a licença MIT. Veja o arquivo LICENSE para mais detalhes.