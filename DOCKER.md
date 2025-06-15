# Vox Kanban - Ambiente Docker

Este projeto utiliza Docker para criar um ambiente de desenvolvimento completo e isolado com **Alpine Linux** para otimização de tamanho e performance.

## 🚀 Início Rápido

### Pré-requisitos
- Docker
- Docker Compose
- Make (opcional, mas recomendado)

### Instalação Completa

1. **Clone o repositório**
```bash
git clone <repository-url>
cd vox-kanban
```

2. **Instalação completa (recomendado)**
```bash
make install
```

**OU sem Make:**
```bash
cp .env.docker .env
docker build --build-arg user=www --build-arg uid=1000 -t vox-kanban-app:latest .
docker-compose up -d
sleep 15
docker exec -it vox-kanban-app composer install --optimize-autoloader
docker exec -it vox-kanban-app php artisan key:generate
docker exec -it vox-kanban-app php artisan migrate:fresh --seed
docker exec -it vox-kanban-app php artisan storage:link
docker exec -it vox-kanban-app php artisan config:cache
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
| **Redis** | localhost:6379 | Cache, sessions e filas |
| **Reverb WebSocket** | ws://localhost:8080 | WebSockets (se instalado) |
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
make build           # Build da imagem base
make up              # Inicia todos os serviços
make down            # Para todos os serviços
make restart         # Reinicia todos os serviços
make rebuild         # Rebuild completo (down + build + up)
make status          # Mostra status dos serviços
make logs            # Mostra logs de todos os serviços
make logs-app        # Logs apenas da aplicação
make logs-queue      # Logs da queue
make logs-reverb     # Logs do Reverb WebSocket
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

### Redis e Reverb

```bash
make redis-cli       # Acessa Redis CLI
make setup-redis     # Configura e testa Redis
make setup-reverb    # Configura Laravel Reverb
make test-connections # Testa todas as conexões (DB, Redis, etc)
make reverb-start    # Inicia servidor Reverb manualmente
make queue-work      # Inicia worker da queue manualmente
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

### Limpeza

```bash
make clean           # Remove containers e volumes
make permissions     # Corrige permissões de arquivos
```

## 🔧 Estrutura do Docker

### Arquitetura Otimizada

O projeto utiliza uma **imagem base única** (`vox-kanban-app:latest`) baseada em **PHP 8.2-FPM Alpine** para todos os serviços PHP, otimizando:

- **Tamanho**: Alpine reduz o tamanho da imagem em ~60%
- **Performance**: Menos tempo de build e deploy
- **Manutenção**: Uma única imagem para manter
- **Recursos**: Menor uso de CPU e memória

### Containers

1. **app** - Aplicação Laravel (PHP 8.2-FPM Alpine)
2. **webserver** - Servidor web (Nginx Alpine)
3. **postgres** - Banco de dados PostgreSQL 16 Alpine
4. **redis** - Cache, sessions e filas (Redis Alpine)
5. **queue** - Worker para filas (usa mesma imagem do app)
6. **scheduler** - Agendador de tarefas (usa mesma imagem do app)
7. **reverb** - WebSocket server Laravel Reverb (usa mesma imagem do app)
8. **mailhog** - Servidor de email para desenvolvimento
9. **node** - Node.js para assets (apenas em desenvolvimento)

### Imagem Compartilhada

Todos os serviços PHP (`app`, `queue`, `scheduler`, `reverb`) usam a **mesma imagem base** mas executam comandos diferentes:

- **app**: `php-fpm` (servidor PHP)
- **queue**: `php artisan queue:work` (processa filas)
- **scheduler**: `cron + php artisan schedule:run` (tarefas agendadas)
- **reverb**: `php artisan reverb:start` (WebSocket server)

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

## 🔄 Laravel Reverb (WebSockets)

### Configuração Automática

O Laravel Reverb já está **pré-configurado** no ambiente Docker! Quando você instalar o Reverb, ele será automaticamente detectado e iniciado.

### Instalação do Reverb

```bash
# Instalar Reverb
make composer ARGS="require laravel/reverb"
make artisan ARGS="install:broadcasting"

# Reinstalar para aplicar configurações
make rebuild
```

### Configuração Automática

O serviço `reverb` no docker-compose já está configurado com:

- **Auto-detecção**: só executa se o Reverb estiver instalado
- **Porta**: 8080 (WebSocket)
- **Host**: 0.0.0.0 (aceita conexões externas)
- **Configuração**: variáveis de ambiente já definidas

### Variáveis de Ambiente (.env.docker)

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=vox-kanban
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
```

### Testando WebSockets

```bash
# Verificar se Reverb está rodando
make logs-reverb

# Testar manualmente
make reverb-start

# Testar conexões
make test-connections
```

### Frontend (JavaScript)

```javascript
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});
```

## 📝 Notas Importantes

### Melhorias do Alpine Linux

Este projeto usa **Alpine Linux** como base, oferecendo:

- **Tamanho reduzido**: ~60% menor que imagens padrão
- **Segurança**: Menos vulnerabilidades, atualizações frequentes
- **Performance**: Menos overhead, inicialização mais rápida
- **Recursos**: Menor uso de CPU e memória

### Imagem Compartilhada

- **Uma imagem**: `vox-kanban-app:latest` para todos os serviços PHP
- **Build único**: `make build` constrói uma vez, usa em todos os serviços
- **Eficiência**: Menos tempo de build e menor uso de disco
- **Manutenção**: Uma única imagem para atualizar

### Ambiente e Desenvolvimento

1. **Desenvolvimento vs Produção:**
   - Use `make setup-dev` para desenvolvimento (inclui Node.js)
   - Use `make install` para ambientes similares à produção

2. **Dados Persistentes:**
   - PostgreSQL e Redis têm volumes persistentes
   - Para reset completo: `make clean`

3. **Performance:**
   - Alpine otimiza uso de recursos automaticamente
   - Monitor logs regularmente: `make logs`
   - Use `make test-connections` para verificar saúde dos serviços

4. **Segurança:**
   - Altere credenciais padrão em produção
   - Configure firewall apropriadamente
   - Use HTTPS em produção
   - Alpine recebe atualizações de segurança frequentes

### Redis e Cache

- **Redis como backend**: Cache, sessions e filas
- **Extensão PHP Redis**: Instalada automaticamente no build
- **Conexão**: Testada automaticamente no `make install`
- **Commands**: Use `make redis-cli` para debug

### WebSockets (Reverb)

- **Auto-configurado**: Detecta automaticamente se instalado
- **Tolerante a falhas**: Não quebra se não estiver instalado
- **Pronto para uso**: Basta instalar o pacote Laravel Reverb

### Comandos Importantes

```bash
# Instalação completa (primeira vez)
make install

# Rebuild quando necessário
make rebuild

# Verificar status dos serviços
make status

# Testar todas as conexões
make test-connections

# Ver logs em tempo real
make logs
```
