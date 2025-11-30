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

---
**🔗 Links Relacionados:**
* [Frontend (React)](LINK_DO_SEU_REPO_FRONTEND)
* [IA Service (Hugging Face)](LINK_DO_SEU_SPACE)