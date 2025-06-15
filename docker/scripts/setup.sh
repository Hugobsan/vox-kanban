#!/bin/sh

echo "=== Configurando ambiente Laravel para Docker ==="

# Aguardar os serviços ficarem prontos
echo "Aguardando PostgreSQL e Redis ficarem prontos..."
sleep 10

# Copiar .env.docker para .env se não existir
if [ ! -f /var/www/.env ]; then
    echo "Copiando .env.docker para .env..."
    cp /var/www/.env.docker /var/www/.env
fi

# Gerar chave da aplicação se não existir
if ! grep -q "APP_KEY=base64:" /var/www/.env; then
    echo "Gerando chave da aplicação..."
    php artisan key:generate --no-interaction
fi

# Executar migrações
echo "Executando migrações..."
php artisan migrate --no-interaction --force

# Limpar caches
echo "Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Testar conexão com Redis
echo "Testando conexão com Redis..."
php artisan tinker --execute="Redis::connection()->ping();"

# Testar se Reverb está disponível
echo "Verificando se Laravel Reverb está disponível..."
if php artisan list | grep -q "reverb:start"; then
    echo "✅ Laravel Reverb está instalado e pronto para uso"
else
    echo "❌ Laravel Reverb não encontrado"
fi

echo "=== Configuração concluída ==="
