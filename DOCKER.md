# Vox Kanban - Ambiente Docker

Este projeto utiliza Docker para criar um ambiente de desenvolvimento completo e isolado.

## 🚀 Início Rápido

### Pré-requisitos
- Docker
- Docker Compose
- Make (opcional, mas recomendado)

### Instalação

1. **Clone o repositório**
```bash
git clone <repository-url>
cd vox-kanban
```

2. **Primeira instalação**
```bash
make install
```

**OU sem Make:**
```bash
cp .env.docker .env
docker-compose build
docker-compose up -d
docker exec -it vox-kanban-app composer install --no-dev --optimize-autoloader
docker exec -it vox-kanban-app php artisan key:generate
docker exec -it vox-kanban-app php artisan migrate:fresh --seed
docker exec -it vox-kanban-app php artisan storage:link
```

### Desenvolvimento

Para desenvolvimento com hot-reload do Vite:
```bash
make setup-dev
```

## 📋 Serviços Disponíveis

| Serviço | URL/Porta | Descrição |
|---------|-----------|-----------|
| **Aplicação** | http://localhost:8000 | Laravel + Nginx |
| **MailHog** | http://localhost:8025 | Interface web para emails |
| **PostgreSQL** | localhost:5432 | Banco de dados |
| **Redis** | localhost:6379 | Cache e sessions |
| **Vite Dev** | http://localhost:5173 | Hot reload (apenas em dev) |

### Credenciais do Banco
- **Host:** localhost
- **Porta:** 5432
- **Database:** vox_kanban
- **Username:** vox_user
- **Password:** vox_password

## 🛠️ Comandos Úteis

### Gerenciamento de Containers
```bash
make up              # Inicia todos os serviços
make down            # Para todos os serviços
make restart         # Reinicia todos os serviços
make logs            # Mostra logs de todos os serviços
make logs-app        # Logs apenas da aplicação
make logs-queue      # Logs da queue
```

### Desenvolvimento
```bash
make shell           # Acessa shell da aplicação
make composer ARGS="install"  # Executa Composer
make artisan ARGS="migrate"   # Executa Artisan
make migrate         # Executa migrações
make seed            # Executa seeders
make fresh           # migrate:fresh + seed
make test            # Executa testes
```

### Banco de Dados
```bash
make psql            # Acessa PostgreSQL
make backup-db       # Faz backup do banco
make restore-db FILE=backup.sql  # Restaura backup
```

### Cache e Performance
```bash
make artisan ARGS="config:cache"   # Cache de configuração
make artisan ARGS="route:cache"    # Cache de rotas
make artisan ARGS="view:cache"     # Cache de views
make artisan ARGS="config:clear"   # Limpa cache
```

## 🔧 Estrutura do Docker

### Containers

1. **app** - Aplicação Laravel (PHP 8.2-FPM)
2. **webserver** - Servidor web (Nginx)
3. **postgres** - Banco de dados PostgreSQL 15
4. **redis** - Cache e sessions
5. **queue** - Worker para filas
6. **scheduler** - Agendador de tarefas (cron)
7. **mailhog** - Servidor de email para desenvolvimento
8. **node** - Node.js para assets (apenas em desenvolvimento)

### Volumes
- **postgres_data** - Dados do PostgreSQL
- **redis_data** - Dados do Redis

## 📁 Configurações

### Nginx
- Arquivo: `docker/nginx/default.conf`
- Configurações de proxy, cache e segurança

### PHP
- Arquivo: `docker/php/local.ini`
- Configurações de upload, memory_limit, timezone

### PostgreSQL
- Arquivo: `docker/postgres/init.sql`
- Extensões e configurações iniciais

### Supervisor
- Arquivo: `docker/supervisor/supervisord.conf`
- Gerenciamento de processos (queue, scheduler)

## 🔄 Queue e Scheduler

### Queue Workers
- **2 workers** rodando simultaneamente
- Configurados no Supervisor
- Logs disponíveis em `/var/log/supervisor/laravel-queue.log`

### Scheduler
- Executa `php artisan schedule:run` a cada minuto
- Configurado no container `scheduler`
- Logs disponíveis em `/var/log/supervisor/laravel-schedule.log`

### Comandos manuais
```bash
# Ver status da queue
make artisan ARGS="queue:work --verbose"

# Reiniciar workers
make artisan ARGS="queue:restart"

# Ver jobs falhados
make artisan ARGS="queue:failed"

# Reprocessar jobs falhados
make artisan ARGS="queue:retry all"
```

## 🚨 Troubleshooting

### Problemas de Permissão
```bash
make permissions
```

### Containers não iniciam
```bash
make down
make clean
make build
make up
```

### Banco não conecta
```bash
# Verificar se PostgreSQL está rodando
docker exec vox-kanban-postgres pg_isready -U vox_user

# Verificar logs do PostgreSQL
make logs-postgres
```

### Queue não processa
```bash
# Verificar se Redis está rodando
docker exec vox-kanban-redis redis-cli ping

# Verificar workers
make logs-queue

# Reiniciar queue
make artisan ARGS="queue:restart"
```

### Assets não carregam (desenvolvimento)
```bash
# Iniciar com Node.js
make down
make setup-dev

# Verificar se Vite está rodando
docker logs vox-kanban-node
```

## 🔄 Laravel Reverb (Websockets)

Se você instalar o Laravel Reverb para WebSockets em tempo real:

1. **Instalar Reverb:**
```bash
make composer ARGS="require laravel/reverb"
make artisan ARGS="install:broadcasting"
```

2. **Adicionar serviço no docker-compose.yml:**
```yaml
reverb:
  build:
    context: .
    dockerfile: Dockerfile
  container_name: vox-kanban-reverb
  restart: unless-stopped
  working_dir: /var/www
  volumes:
    - ./:/var/www
  ports:
    - "8080:8080"
  networks:
    - vox-kanban
  depends_on:
    - redis
    - app
  environment:
    - REVERB_HOST=0.0.0.0
    - REVERB_PORT=8080
  command: php artisan reverb:start --host=0.0.0.0 --port=8080
```

3. **Configurar .env:**
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=vox-kanban
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

## 📝 Notas Importantes

1. **Desenvolvimento vs Produção:**
   - Use `make setup-dev` para desenvolvimento (inclui Node.js)
   - Use `make install` para ambientes similares à produção

2. **Dados Persistentes:**
   - PostgreSQL e Redis têm volumes persistentes
   - Para reset completo: `make clean`

3. **Performance:**
   - Em produção, considere ajustar recursos dos containers
   - Monitor logs regularmente: `make logs`

4. **Segurança:**
   - Altere credenciais padrão em produção
   - Configure firewall apropriadamente
   - Use HTTPS em produção
