# 📸 Problema: Imagens Desaparecem Após Deploy

## 🔍 Causa do Problema

As imagens inseridas pelo site estão sendo salvas em pastas dentro de `storage/` (como `storage/educations/`, `storage/experiences/`, `storage/projects/`, `storage/settings/`), que **NÃO** são versionadas no Git e **NÃO** estão na estrutura correta do Laravel para arquivos públicos.

Quando você faz deploy, apenas os arquivos versionados no Git são enviados para o servidor. Como as imagens uploadadas não estão no Git, elas desaparecem a cada novo deploy.

## ✅ Soluções

### Solução 1: Usar Armazenamento Externo (RECOMENDADO)

A melhor prática é usar um serviço de armazenamento em nuvem como:
- **AWS S3**
- **Cloudinary**
- **DigitalOcean Spaces**
- **Google Cloud Storage**

**Vantagens:**
- As imagens nunca se perdem em deploys
- Melhor performance com CDN
- Escalabilidade infinita
- Backup automático

**Como implementar:**
1. Configure o disk `s3` no arquivo `config/filesystems.php`
2. Altere `FILESYSTEM_DISK=s3` no `.env`
3. Atualize seu código de upload para usar o disk configurado

---

### Solução 2: Script de Deploy (Solução Imediata)

Criamos o script `deploy-fix.sh` que deve ser executado no servidor após cada deploy:

```bash
# No servidor, após o deploy:
./deploy-fix.sh
```

**O que o script faz:**
1. Cria o symlink `public/storage` → `storage/app/public`
2. Ajusta permissões das pastas
3. Cria as pastas necessárias para uploads
4. Migra imagens antigas para a nova estrutura

**Adicione ao seu processo de deploy:**

```bash
# Exemplo de script de deploy
git pull origin main
composer install --no-dev
npm run build
php artisan migrate --force
php artisan storage:link
./deploy-fix.sh  # ← Adicione esta linha
php artisan cache:clear
php artisan config:cache
```

---

### Solução 3: Corrigir o Código de Upload

Atualmente suas imagens estão sendo salvas em locais incorretos. Você precisa alterar o código para salvar em `storage/app/public/`.

**Exemplo de correção:**

```php
// ❌ JEITO ERRADO (atual)
$path = $request->file('image')->store('educations');

// ✅ JEITO CORRETO
$path = $request->file('image')->store('educations', 'public');
// ou
$path = $request->file('image')->storePublicly('educations');
```

**Para exibir as imagens no Blade:**

```blade
<!-- ❌ JEITO ERRADO -->
<img src="{{ asset('storage/educations/' . $image) }}">

<!-- ✅ JEITO CORRETO -->
<img src="{{ Storage::url($image) }}">
<!-- ou -->
<img src="{{ asset('storage/' . $image) }}">
```

---

### Solução 4: Volume Persistente (Docker)

Se você usa Docker, configure volumes persistentes:

```yaml
# docker-compose.yml
services:
  app:
    volumes:
      - ./storage/app/public:/var/www/html/storage/app/public
```

---

## 🚀 Próximos Passos

1. **Imediato:** Execute o script `deploy-fix.sh` no servidor para recuperar as imagens atuais
2. **Curto prazo:** Atualize o código de upload para usar `storage/app/public/`
3. **Longo prazo:** Migre para armazenamento em nuvem (AWS S3, etc.)

---

## 📁 Estrutura Correta de Pastas

```
storage/
├── app/
│   ├── private/     (arquivos privados)
│   └── public/      (arquivos públicos - IMAGENS DEVEM IR AQUI)
│       ├── educations/
│       ├── experiences/
│       ├── projects/
│       └── settings/
└── logs/

public/
├── storage/         (symlink → storage/app/public)
├── images/
└── index.php
```

---

## ⚠️ Importante sobre .gitignore

Nunca inclua no `.gitignore`:
- ~~`storage/app/public/`~~ (se quiser versionar uploads)

Mas a melhor prática é **NÃO versionar uploads** e usar armazenamento externo.
