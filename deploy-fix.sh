#!/bin/bash

# Script para corrigir o problema das imagens que desaparecem após deploy
# Este script deve ser executado após cada deploy no servidor

echo "🔧 Corrigindo problema das imagens..."

# 1. Criar o symlink para storage/app/public (imagens públicas)
echo "📦 Criando symlink para storage..."
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo "✅ Symlink criado com sucesso!"
else
    echo "✅ Symlink já existe!"
fi

# 2. Definir permissões corretas
echo "🔐 Ajustando permissões..."
chmod -R 775 storage/
chmod -R 775 public/
chown -R www-data:www-data storage/ 2>/dev/null || echo "⚠️  Não foi possível alterar o proprietário (pode requerer sudo)"
chown -R www-data:www-data public/ 2>/dev/null || echo "⚠️  Não foi possível alterar o proprietário (pode requerer sudo)"

# 3. Verificar se as pastas de upload existem
echo "📁 Verificando pastas de upload..."
mkdir -p storage/app/public/educations
mkdir -p storage/app/public/experiences
mkdir -p storage/app/public/projects
mkdir -p storage/app/public/settings

echo "✅ Pastas criadas!"

# 4. Mover imagens antigas para a nova estrutura (opcional)
echo "🔄 Migrando imagens existentes (se houver)..."

# Mover educations
if [ -d "storage/educations" ] && [ "$(ls -A storage/educations 2>/dev/null)" ]; then
    cp -r storage/educations/* storage/app/public/educations/ 2>/dev/null
    echo "   - Imagens de educations migradas"
fi

# Mover experiences
if [ -d "storage/experiences" ] && [ "$(ls -A storage/experiences 2>/dev/null)" ]; then
    cp -r storage/experiences/* storage/app/public/experiences/ 2>/dev/null
    echo "   - Imagens de experiences migradas"
fi

# Mover projects
if [ -d "storage/projects" ] && [ "$(ls -A storage/projects 2>/dev/null)" ]; then
    cp -r storage/projects/* storage/app/public/projects/ 2>/dev/null
    echo "   - Imagens de projects migradas"
fi

# Mover settings
if [ -d "storage/settings" ] && [ "$(ls -A storage/settings 2>/dev/null)" ]; then
    cp -r storage/settings/* storage/app/public/settings/ 2>/dev/null
    echo "   - Imagens de settings migradas"
fi

echo ""
echo "🎉 Configuração concluída!"
echo ""
echo "⚠️  IMPORTANTE:"
echo "   1. Atualize seu código para salvar as imagens em 'storage/app/public/' ao invés de 'storage/'"
echo "   2. No .gitignore do seu repositório, NÃO inclua as pastas de upload se quiser versioná-las"
echo "   3. Ou melhor: use um serviço de armazenamento externo (AWS S3, Cloudinary, etc.)"
echo "   4. Execute este script após cada deploy no servidor"
echo ""
