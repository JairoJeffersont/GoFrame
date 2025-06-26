# GoFrame

GoFrame é um framework PHP simples para criação de APIs e aplicações web, focado em produtividade e organização de código. Ele oferece estrutura MVC, roteamento, integração com banco de dados MySQL, validação e helpers prontos para uso.

## Instalação

1. **Clone o repositório:**

   ```sh
   git clone https://github.com/seu-usuario/go-frame.git
   cd go-frame
   ```

2. **Instale as dependências via Composer:**

   ```sh
   composer install
   ```

3. **Configure o arquivo `.env`:**

   ```sh
   DB_HOST=casadojairo.ddns.net
   DB_DATABASE=teste
   DB_USERNAME=jairo
   DB_PASSWORD=intell01
   DB_CHARSET=utf8mb4

   APP_BASE_PATH=/GoFrame

   ```

4. **Configure o servidor web:**

   - Aponte o DocumentRoot para a pasta `public/`.
   - Certifique-se de que o módulo de reescrita (mod_rewrite) está habilitado no Apache.

5. **Acesse no navegador:**
   ```
   http://localhost/GoFrame
   ```

## Estrutura

- `src/` - Código fonte do framework (Controllers, Models, Core, Helpers, etc)
- `public/` - Pasta pública para acesso web (index.php)
- `.env` - Configurações de ambiente (banco de dados, etc)

## Requisitos

- PHP 8.0 ou superior
- Composer
- MySQL

---

Sinta-se à vontade para contribuir ou adaptar o projeto conforme sua necessidade!
