# VisusAI - Backend & API Gateway (Laravel)

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Container-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/Supabase-PostgreSQL-3ECF8E?style=for-the-badge&logo=supabase&logoColor=white)

Este repositório contém o **Backend** da plataforma VisusAI. Ele atua como um API Gateway e orquestrador, gerenciando autenticação, dados de pacientes e comunicação com o serviço de Inteligência Artificial.

## 🏗️ Arquitetura de Infraestrutura (Edge Computing)

Diferente de deploys tradicionais em nuvem, este backend foi projetado para rodar em **Hardware On-Premise de baixo custo** (Edge Computing), utilizando uma arquitetura containerizada eficiente:

* **Servidor:** TV Box adaptada (Rockchip RK3328, Cortex-A53, 4GB RAM) rodando Linux (Armbian).
* **Containerização:** Docker & Docker Compose otimizados para arquitetura ARM64.
* **Exposição:** Cloudflare Tunnel (HTTP2) para acesso seguro externo sem abrir portas no roteador.
* **Armazenamento:** Integração com Supabase (PostgreSQL para dados e S3 Bucket para imagens de exames).

## 🚀 Funcionalidades

* **Gestão de Usuários:** Autenticação via Token (Sanctum) para médicos e acesso simplificado (CPF) para pacientes.
* **Processamento de Exames:** Upload multipart de imagens de retina de alta resolução (64MB+).
* **Orquestração de IA:** Envio assíncrono de imagens para o microsserviço de inferência (Python).
* **Geração de Laudos:** Criação dinâmica de PDFs com resultados e gráficos de probabilidade.
* **Segurança:** Validação rigorosa de dados e controle de acesso (ACL) para Admins e Profissionais.

## 🛠️ Como Rodar (Docker)

1.  **Clone o repositório:**
    ```bash
    git clone [https://github.com/seu-usuario/visus-backend.git](https://github.com/seu-usuario/visus-backend.git)
    ```
2.  **Configure o ambiente:**
    ```bash
    cp .env.example .env
    # Preencha as credenciais do Supabase, Gmail SMTP e URL da IA
    ```
3.  **Suba os containers:**
    ```bash
    docker-compose up -d --build
    ```

## 🐳 Guia de Deploy: Edge Computing (TV Box / ARM64)
Este backend foi otimizado para rodar em hardware de baixo custo (TV Box RK3328 com Armbian Linux), substituindo a necessidade de servidores cloud caros.

Como o repositório contém o código padrão do Laravel, siga os passos abaixo para configurar o ambiente Docker otimizado para processadores ARM.

1. **Pré-requisitos no Hardware**

* **OS: Linux (Debian/Armbian/Ubuntu).**

* **Pacotes: Git, Docker e Docker Compose instalados.**

* **Rede: Acesso à internet (para baixar imagens e pacotes).**

2. **Setup do Projeto**

Acesse o terminal da TV Box via SSH e clone o projeto:

```bash
mkdir visus-backend
cd visus-backend
git clone https://github.com/Marcelaun/backend_laravel.git app
```
3. **Criação dos Arquivos de Infraestrutura**

Como este ambiente exige configurações específicas de rede e performance, crie os seguintes arquivos na raiz da pasta onde você clonou o projeto (fora da pasta app se usar estrutura aninhada, ou dentro da raiz do Laravel):

**A.** Dockerfile

Otimizado para ARM64, com instalador de extensões e Composer embutido.

```Dockerfile

# Stage 1: Build
FROM composer:2.7 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize

# Stage 2: Production (CLI Alpine)
FROM php:8.2-cli-alpine

# Dependências do Sistema
RUN apk add --no-cache libzip-dev libpng-dev libxml2-dev postgresql-dev oniguruma-dev curl git unzip

# Extensões PHP (PostgreSQL e Imagem)
RUN docker-php-ext-install pdo_pgsql mbstring zip gd opcache

# Configuração PHP (Aumentar limites para upload de exames)
RUN echo "upload_max_filesize = 64M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 600" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Script de Inicialização
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
```

**B.** nginx.conf

Proxy reverso para comunicar com o Laravel na porta 8000.

```
Nginx

server {
    listen 80;
    listen [::]:80;
    server_name _;
    root /var/www/html/public;
    client_max_body_size 64M;

    location / {
        proxy_pass http://app:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Timeouts aumentados para geração de PDF
        proxy_connect_timeout 600;
        proxy_send_timeout 600;
        proxy_read_timeout 600;
        send_timeout 600;
    }
}

```


**C.** start.sh

Script para rodar migrações e iniciar o servidor. Dica: Rode dos2unix start.sh se criar este arquivo no Windows.

```
bash

#!/usr/bin/env sh
set -e
mkdir -p storage/framework/{cache,sessions,views} storage/logs
chmod -R 777 storage bootstrap/cache

echo "Caching configuration..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link || true

echo "Running migrations..."
php artisan migrate --force

echo "Starting Laravel Server..."
php artisan serve --host=0.0.0.0 --port=8000
```


**D.** docker-compose.yml

Orquestração com correção de DNS para rede doméstica.

```

YAML

version: '3'
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: visus-app
    restart: always
    working_dir: /var/www/html
    # Fix de DNS para conectar serviços externos (Supabase/Gmail)
    dns:
      - 8.8.8.8
      - 1.1.1.1
    environment:
      APP_ENV: production
      APP_DEBUG: "false"
      APP_URL: http://localhost
      APP_KEY: "base64:..." # Gere com php artisan key:generate --show
      
      # Configurações do Supabase (Session Pooler - Porta 5432)
      DB_CONNECTION: pgsql
      DB_HOST: aws-0-sa-east-1.pooler.supabase.com
      DB_PORT: 5432
      DB_DATABASE: postgres
      DB_USERNAME: postgres.seu_projeto
      DB_PASSWORD: "sua_senha"
      
      # Configurações de Email, Storage e IA...
      # (Preencher conforme .env local)

  webserver:
    image: nginx:alpine
    container_name: visus-nginx
    restart: always
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
	  
```


**4. Execução**

1. Suba o ambiente:

```

bash

sudo docker compose up -d --build
```

2. O servidor estará disponível na rede local em http://IP-TVBOX:8080.

**5. Exposição para Internet (Cloudflare)**

Para conectar com o Frontend na nuvem (Vercel), utilize um Túnel HTTP2:

```
bash

cloudflared tunnel --protocol http2 --url http://localhost:8080
```

---
**🔗 Links Relacionados:**
* [Frontend (React)](LINK_DO_SEU_REPO_FRONTEND)
* [IA Service (Hugging Face)](LINK_DO_SEU_SPACE)