
# Movies API

API REST para gerenciamento de catálogo de filmes, desenvolvida como parte do desafio técnico da Follow55.

## Stack

- **Laravel 13** / PHP 8.5+
- **SQLite** (padrão, zero config) — MySQL/PostgreSQL suportados via `.env`
- **Laravel Sanctum** para autenticação por token

---

## Pré-requisitos

- Composer
- Extensão `fileinfo` habilitada (no Windows pode estar desabilitada por padrão — basta descomentar `extension=fileinfo` no `php.ini`)
- SQLite habilitado (`extension=pdo_sqlite` e `extension=sqlite3` no `php.ini`)

## Como rodar

```bash
git clone https://github.com/bricio-sr/follow55.git
cd follow55

composer install
cp .env.example .env
php artisan key:generate

# Linux/macOS
touch database/database.sqlite

# Windows (PowerShell)
New-Item database/database.sqlite -ItemType File
php artisan migrate --seed

php artisan serve
```

Pronto. A API estará em `http://localhost:8000`.

---

## Credenciais do seed

| Email | Senha | Filmes |
|---|---|---|
| alice@example.com | password | Blade Runner, 2001, The Godfather, Pulp Fiction |
| bob@example.com | password | Inception, The Dark Knight, Interstellar, Parasite |

---

## Endpoints

### Autenticação

```
POST /api/login
Content-Type: application/json

{ "email": "alice@example.com", "password": "password" }
```
POST /api/logout
Authorization: Bearer {token}

### Filmes

| Método | Rota | Auth | Descrição |
|---|---|---|---|
| GET | /api/movies | Não | Lista paginada |
| GET | /api/movies/{id} | Não | Detalhe |
| POST | /api/movies | Sim | Criar |
| PUT | /api/movies/{id} | Sim | Editar (apenas dono) |
| DELETE | /api/movies/{id} | Sim | Remover (apenas dono) |

**Query params disponíveis em `GET /api/movies`:**
?sort=release_year   # title, release_year, rating, created_at
&dir=desc            # asc | desc
&search=blade        # busca parcial no título
&per_page=15         # máximo 100, padrão 15
&page=2

> **Importante:** todos os requests devem incluir `Accept: application/json`. Sem esse header, o Sanctum pode redirecionar para a rota `login` (que não existe em APIs puras) e retornar HTML. Qualquer cliente de API (Postman, front-end, mobile) envia esse header automaticamente.

---

## Decisões técnicas

### Estrutura das respostas JSON

Padrão nativo do Laravel com API Resources:

```json
// Listagem
{
  "data": [ ... ],
  "links": { "first": "...", "next": "...", "last": "..." },
  "meta": { "current_page": 1, "total": 8, "per_page": 15 }
}

// Item único
{
  "data": {
    "id": 1,
    "title": "Blade Runner",
    "release_year": 1982,
    "genre": "Sci-Fi",
    "rating": 8.1,
    "created_by": { "id": 1, "name": "Alice Silva" },
    "created_at": "1982-01-01T00:00:00+00:00"
  }
}

// Erros
{ "message": "Unauthenticated." }
```

Datas em ISO 8601. O `created_by` usa `whenLoaded()` no Resource — só aparece quando o relacionamento foi carregado explicitamente, evitando N+1 por acidente.

---

### Paginação

`paginate(15)` com suporte a `?per_page=` (limitado a 100). Os metadados vêm automaticamente no envelope do Laravel.

Não usei cursor pagination porque o enunciado pede ordenação por múltiplos campos, e cursor pagination fica complexo nesse cenário sem ganho real para um catálogo desse tamanho.

---

### Validação

Dois Form Requests: `StoreMovieRequest` e `UpdateMovieRequest`. O Update usa `sometimes` em todos os campos, permitindo atualização parcial mesmo com o verbo PUT.

A autorização dentro dos Form Requests está em `return true` propositalmente: prefiro centralizar tudo na Policy e chamar `$this->authorize()` no controller, deixando cada camada com uma responsabilidade clara.

---

### Controle de permissão

Usei **Policies** (`MoviePolicy`), registrada explicitamente no `AppServiceProvider`:

```php
Gate::policy(Movie::class, MoviePolicy::class);
```

Preferi registro explícito ao autodiscovery porque fica mais fácil de rastrear — não precisa adivinhar quais policies o framework descobriu automaticamente.

As regras:

- `viewAny` e `view` aceitam usuário nulo (`?User`) — rota pública
- `create` exige autenticação (garantida também pelo middleware)
- `update` e `delete` verificam `$user->id === $movie->created_by` com comparação estrita para evitar coerção de tipos

O controller usa `use AuthorizesRequests` (trait que o Laravel 11+ não inclui por padrão na classe base) e chama `$this->authorize()` antes de cada operação. O 403 é tratado centralizado no exception handler.

---

### Busca e ordenação

Ficaram em Query Scopes no Model:

```php
Movie::query()
    ->with('creator')
    ->search($request->query('search'))
    ->sorted($request->query('sort'), $request->query('dir'))
    ->paginate($perPage);
```

O scope `sorted` tem whitelist explícita de campos permitidos (`title`, `release_year`, `created_at`, `rating`). Qualquer valor fora da lista cai no padrão `release_year desc`, sem explodir e sem permitir injeção de coluna arbitrária.

---

### Organização do código

Controllers finos. Cada controller orquestra: recebe o request, delega validação ao Form Request, delega autorização à Policy, transforma a saída no Resource.
Http/Controllers/   → orquestração (finos)
Http/Requests/      → validação de input
Http/Resources/     → transformação de output
Models/             → scopes, relacionamentos, casts
Policies/           → autorização

---

### Tratamento de erros

Centralizado no `bootstrap/app.php` com `withExceptions()`, o jeito de fazer sem criar um handler separado:

| Exceção | HTTP | Mensagem |
|---|---|---|
| `AuthenticationException` | 401 | Unauthenticated. |
| `AuthorizationException` | 403 | Forbidden. |
| `ModelNotFoundException` | 404 | Resource not found. |
| `NotFoundHttpException` | 404 | Endpoint not found. |
| `ValidationException` | 422 | The given data was invalid. + errors |

O handler de `AuthenticationException` retorna JSON diretamente sem checar `expectsJson()`, garantindo que rotas de API sempre respondam JSON.

---

## Campos extras

O enunciado pede `id`, `title`, `poster_url`, `release_year`, `created_by`. Adicionei:

- `genre` — string, nullable
- `synopsis` — text, nullable
- `rating` — decimal(3,1), nullable (0.0–10.0)

---

## Testes manuais rápidos

> No Windows pode ocorrer scraping json, também o `curl` no PowerShell é um alias para `Invoke-WebRequest`. Use `curl.exe` explicitamente ou prefira o **Postman**.

```bash
# Listar filmes (público)
curl -s http://localhost:8000/api/movies \
  -H "Accept: application/json" | jq .

# Login e captura do token
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"alice@example.com","password":"password"}' \
  | jq -r '.data.token')

# Criar filme
curl -s -X POST http://localhost:8000/api/movies \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Dune","release_year":2021,"genre":"Sci-Fi","rating":8.0}' | jq .

# Tentar editar filme de outro usuário (deve retornar 403)
curl -s -X PUT http://localhost:8000/api/movies/5 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Hackeado"}' | jq .

# Busca e ordenação
curl -s "http://localhost:8000/api/movies?search=blade&sort=rating&dir=desc" \
  -H "Accept: application/json" | jq .
```