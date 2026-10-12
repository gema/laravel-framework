# Manual de upgrade da frota Laravel (`gemadigital/framework`)

> Registo completo do trabalho feito entre **22/07/2026 e 12/10/2026** para levar os projetos
> Laravel da frota a **Laravel 13 + Backpack 7 + framework v17 + Vite 8**, e guia passo a passo
> para os próximos upgrades.
>
> Junta três fontes: o handoff original (`LARAVEL-FLEET-UPGRADE-HANDOFF.md`, escrito por uma
> primeira sessão de Claude e que já não existe em disco), tudo o que se aprendeu na conversa
> seguinte, e as correções feitas depois de cada deploy.
>
> Última atualização: 2026-10-12.

## Como ler este manual

| Parte | Para quê |
|---|---|
| [I — Resumo compacto](#parte-i--resumo-compacto) | Perceber em 5 minutos o que foi feito e o que mais custou |
| [II — Guia passo a passo](#parte-ii--guia-passo-a-passo) | Fazer o próximo upgrade, do diagnóstico ao deploy |
| [III — Catálogo de armadilhas](#parte-iii--catálogo-de-armadilhas) | Procurar um sintoma e encontrar a causa e a correção |
| [IV — Pendentes na framework](#parte-iv--pendentes-na-própria-framework-v17) | Bugs e melhorias a fazer neste repositório |
| [V — Histórico detalhado](#parte-v--histórico-detalhado-da-conversa) | Cronologia de tudo o que se passou, fase a fase, e estado por projeto |

---

# Parte I — Resumo compacto

## 1. De onde se partiu e onde se chegou

- Ponto de partida: cerca de 40 projetos entre **Laravel 8 e 12**, `gemadigital/framework` entre a
  **8.17 e a v15**, Backpack entre **4.1 e 6**, frontend em **laravel-mix/webpack**.
- Dificuldade central: as breaking changes da framework **não estavam documentadas**; foi preciso
  obtê-las por diff das branches (`v11` … `v17`) deste repositório. O mapa está na [secção 8](#8-saltos-da-framework-mapa-de-breaking-changes).
- Ordem escolhida pelo António: dos projetos mais recentes para os mais antigos
  (L13 → L12 → L11 → L10 → L9 → L8), para o playbook ir crescendo com casos cada vez mais difíceis.
- Ponto de chegada: toda a frota em **Laravel 13**, framework **v17**, Backpack **7**, **Vite 8**,
  phpunit 13, com phpstan/pint verdes onde existem.

## 2. Cronologia resumida

| Data | Fase | Projetos | Salto | Resultado |
|---|---|---|---|---|
| até 22/07 | 0 — handoff (outra sessão) | 14 projetos (kopke, centro-acolhimento, alandroal, azap, cards, api, commed-ia, onecore, …) | L12→13, fw v15→v17, Backpack 6→7, Filament 4→5 | feito |
| 22/07 | 1 | federacao-portuguesa-futebol-backend | L12→13.21, fw 14.0→v17, Backpack 6→7; depois phpunit 13, Livewire 4, mix→Vite 8 | feito; 2 incidentes pós-deploy (guard, views compiladas) |
| 23/07 | 2 | pixel-backend, pixel-rumos-backend | L11→13.21 + framework v17 instalada de raiz | feito; auditoria formal L11→12→13 |
| 23/07 | 3 | fisgas, vacaria, panike, adrimag, edia, museu-imigracao | L11→13.21, fw 11–13→v17 | feito (pipeline em scripts) |
| 23–24/07 | 4 | neonia, missao, centro-artes, qatar (fw 11) + psp, melgaco, ivdp, carrazeda (fw 10 = Backpack 5) | L10→13.21 | feito; retrofit do lote 3 (pagemanager, phpstan, CI) |
| 24/07 | — | portaldasaude, faculdade-direito-filament, apram | instalar a framework em projetos Filament | cancelado e revertido |
| 24/07 | 5 | ci-atouguia-baleia, museu-aveiro, museu-mineiro | L9→13.21, fw 9→v17, Backpack 5→7 | feito; 3 incidentes pós-deploy |
| 29/07 | 6 — lote de dependências | 13 projetos | `composer update` + `npm update`, intervention 2→4, pico 1→2, vite 8, openai (npm) 6→7 | feito (todos em L13.23) |
| 29–30/09 | 7 | imagaia.pt | L11→13.34 | feito; descoberta do problema `AuthenticateSession` |
| 11–12/10 | 8 | paulomerlini.com | L8→13.35, fw 8.17→v17, Backpack 4.1→7, mix→Vite 8 | feito; 2 rondas de correção de bugs antigos |

## 3. Stack alvo (versões resolvidas a 12/10/2026)

| Componente | Versão |
|---|---|
| PHP | `^8.3\|^8.4` no `composer.json`; o lock exige **≥ 8.4.1** (Symfony 8.1, PHPUnit 13) |
| laravel/framework | `^13.0` → 13.35 |
| gemadigital/framework | `v17.x-dev` (branch `v17`; exige `minimum-stability: dev` + `prefer-stable: true`) |
| backpack/crud · pro · theme-tabler | 7.1.22 · 3.0.31 · 2.1.0 |
| phpunit · collision | 13.4 · 8.9 |
| intervention/image | 4.3 |
| vite · laravel-vite-plugin · eslint · sass | 8.3 · 3.2 · 10.12 · 1.105 |

## 4. As lições que mais custaram

1. **Guard `web`.** Na v17 o gate de admin só olha para o guard por defeito. Com `'guard' => 'backpack'`
   o login funciona mas o painel fica inacessível, mesmo com `is_admin = true`. ([§8](#8-saltos-da-framework-mapa-de-breaking-changes))
2. **Verificar com o login HTTP real**, nunca com `Auth::login()`. Foi por testar o atalho que o ponto 1
   chegou a produção. ([§15](#15-verificação))
3. **Views compiladas sobrevivem ao deploy.** `view:clear` no `post-install-cmd` de todos os projetos. ([§16](#16-entrega-e-deploy))
4. **Terminar com um `composer install` local.** O `post-install-cmd` só corre no `install`; um
   `composer update` não o exercita e o deploy parte. ([§16](#16-entrega-e-deploy))
5. **A v16 deixou de trazer os addons.** Tudo o que a app usa do Backpack/Spatie tem de estar no
   `composer.json`, incluindo `backpack/pagemanager` em quase todos os projetos. ([§7](#7-pipeline-base))
6. **`AuthenticateSession` do Backpack ≤ 7.1.20 + Laravel ≥ 13.3x** expulsa todos os logins.
   Garantir `backpack/crud` ≥ 7.1.22 em qualquer `composer update`. ([§9.5](#95-configbackpackbasephp))
7. **Views publicadas de eras antigas** (`backpack::` já não existe): `vendor/elfinder/elfinder.blade.php`,
   `errors/layout.blade.php`, `vendor/backpack/base/*`. Rebentam só quando alguém lá clica. ([§9.4](#94-views-publicadas-a-rever))
8. **Assinaturas `void` da v17.** Overrides sem tipo de `ComposerScripts::postInstall()` e
   `CrudController::sync()` dão fatal. ([§8](#8-saltos-da-framework-mapa-de-breaking-changes))
9. **`theme-tabler` ≥ 2.0.8** exige `options.showPasswordVisibilityToggler` no config publicado, senão
   `/admin/login` dá 500. ([§9.1](#91-backpack-6--7))
10. **Auditar os upgrade guides oficiais com greps**, além dos testes empíricos. ([§10](#10-laravel-checklist-de-breaking-changes))
11. **Projetos sem testes e muito antigos**: montar o stack antigo ao lado do novo e comparar
    (HTML, base de dados, estilos). Foi o que deu confiança no salto L8→13. ([§15.2](#152-nível-2--baseline-antigo-vs-novo))
12. **Bugs antigos encontrados pelo caminho**: reportar e corrigir só a pedido, para não misturar
    com o upgrade. ([§5](#5-regras-de-trabalho))

---

# Parte II — Guia passo a passo

## 5. Regras de trabalho

Preferências do António, a respeitar sempre:

- **Ele revê o diff, faz commit e push.** Nunca fazer commit nem push. A build é automática no push.
- **Evitar pacotes desnecessários.** Não adicionar dependências sem necessidade; remover as que ficam sem uso.
- **Uma alteração de cada vez** em apps em produção. Bugs antigos encontrados durante o upgrade
  reportam-se no relatório; só se corrigem a pedido.
- Comunicação em **português (PT-PT)**.
- Antes de começar um projeto: `git checkout .` e `git pull`. ⚠️ Só no arranque, com a árvore sem
  trabalho por commitar; a meio de um upgrade estes comandos apagam o trabalho pendente.
- Atualizar **tudo**: composer, npm, phpunit, Livewire, CI, e migrar laravel-mix para Vite.
- **phpstan e pint**: correr e corrigir os erros **onde estiverem instalados**; não instalar onde não existem.
- Verificar com **fluxos HTTP reais** e terminar cada projeto com um **`composer install` local**.
- O relatório final começa pelo resultado, diz como foi verificado e lista as decisões que ficam com ele.

## 6. Ambiente e diagnóstico inicial

### 6.1 Ambiente

Máquina Windows 11 com Laragon, sem WSL (o handoff original usava WSL; deixou de se aplicar).

| Ferramenta | Onde |
|---|---|
| PHP 8.4.5 | `C:\laragon\bin\php\php-8.4.5-Win32-vs17-x64\php.exe` (não está no PATH) |
| PHP 8.3.16 | `C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe` — para correr stacks antigos; precisa de `-d extension=zip` |
| Composer | `php C:\laragon\bin\composer\composer.phar` |
| Node / npm | Node 24, npm 11 (globais) |
| MySQL 8.4.3 | `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin` — para sandboxes isoladas |
| mailpit | `C:\laragon\bin\mailpit` — para testar envio de email |
| Projetos | `C:\Users\proma\projects\` (até setembro: `C:\Users\Claude\Documents\projects\`, que já não existe) |

- **Credenciais do Composer**: ficam no `auth.json` **global** do Composer (`http-basic` para
  `repo.backpackforlaravel.com`; `satis.ralphjsmit.com` nos projetos que o usam). Nunca no repositório.
  Sem elas, `backpack/pro` dá 401.
- **git**: `git config --global --add safe.directory <caminho>` por projeto (a pasta é de outro utilizador
  do Windows). `core.autocrlf=true`: os ficheiros estão em CRLF no disco.
- Se o PHP local for mais antigo que o pin do projeto: `composer update --ignore-platform-req=php`.

Particularidades de shell que fizeram perder tempo:

- Git Bash converte argumentos que começam por `/` em caminhos Windows; usar `MSYS_NO_PATHCONV=1`
  **só** em comandos que recebem caminhos de URL.
- `sed -i` estraga ficheiros CRLF. Para substituições em massa, um script PHP com `str_replace`
  (regras num ficheiro JSON) é mais seguro.
- `php artisan tinker <ficheiro>` fica à espera de input; usar `tinker --execute="..."`.
- `php artisan test` com phpunit 13 e `zend.assertions=1` (php.ini de desenvolvimento) dá fatal no
  arranque; correr `php -d zend.assertions=-1 artisan test`. Em CI e produção não afeta.
- O servidor embutido (`php -S`) deve correr com a pasta `public/` como diretório atual
  (`cd public && php -S 127.0.0.1:8082 ../server.php`): há código antigo com caminhos relativos.
- O `php -S` no Windows segura os ficheiros estáticos que serviu: **parar o servidor antes de apagar
  uploads de teste**, senão ficam em "delete pending" e dão `Permission denied`.

### 6.2 Diagnóstico inicial

Antes de mexer, identificar a "era" do projeto:

```bash
grep -E '"(php|laravel/framework|gemadigital/framework|backpack/[a-z-]+|livewire/livewire|phpunit/phpunit|intervention/[a-z-]+)"' composer.json
ls app/Http/Kernel.php bootstrap/app.php     # estrutura legacy (Kernel) ou nova
ls config/backpack/                          # sem ui.php = Backpack 4 ou 5
ls resources/views/vendor/backpack/          # pasta base/ = views publicadas de eras antigas
ls webpack.mix.js vite.config.js             # mix ou Vite
ls .github/workflows; grep -n "pint\|larastan\|phpstan" composer.json
grep -n "'guard'" config/backpack/base.php   # 'backpack' tem de passar a 'web'
ls app/User.php app/Models/User.php          # modelo ainda em App\User?
```

| Era | Sinais | Trabalho esperado |
|---|---|---|
| L12, fw v15/v16, Backpack 6 | já usa `GemaDigital\`, macros | bumps + `is_admin` + guard `web` (§8, §9.1) |
| L11–L10, fw 11–13, Backpack 6 | imports `GemaDigital\Framework\app\`, `json_response()` | rename de namespace, macros, pipeline completo (§8) |
| L10–L9, fw 9–10, **Backpack 5** | `config/backpack/base.php` enorme, sem `ui.php`, `sidebar_content.blade.php` | receita Backpack 5→7 (§9.2) |
| L9 | `App\User`, `fruitcake/laravel-cors`, `EnumHelper` em migrações | o anterior + §10 |
| L8, fw 8, **Backpack 4.1** | `addField($campo, 'create')`, dropzone e `SaveMedia` da framework, langfilemanager | receita Backpack 4.1→7 (§9.3) + baseline antigo-vs-novo (§15.2) |

A estrutura legacy (`app/Http/Kernel.php`, `RouteServiceProvider` com `map()`) **funciona em Laravel 13**
e não se migra: mantém-se como está.

## 7. Pipeline base

A ordem que funcionou em todos os projetos:

1. **Preparar**: `git checkout .` + `git pull`, diagnóstico (§6.2). Em projetos muito antigos, montar
   primeiro o baseline do stack antigo (§15.2).
2. **`composer.json`**: pôr as versões alvo (abaixo) e declarar explicitamente os addons que a app usa.
3. **`composer update -W`** e resolver os bloqueadores (tabela abaixo).
4. **Código**: framework (§8) → Backpack (§9) → Laravel (§10).
5. **Frontend e dependências com major** (§11, §12).
6. **Qualidade**: phpstan, pint e CI, onde existirem (§13).
7. **Verificação** (§15).
8. **`composer install` final**, limpeza da sandbox e relatório (§16).

### 7.1 `composer.json` alvo (projeto com Backpack)

```jsonc
"require": {
    "php": "^8.3|^8.4",
    "backpack/crud": "^7.0",
    "backpack/pro": "^3.0",                  // obrigatório: o UserCrudController da framework usa enableExportButtons()
    "backpack/theme-tabler": "^2.0",
    "backpack/permissionmanager": "^7.3",
    "backpack/language-switcher": "^2.1",
    "backpack/logmanager": "^5.1",
    "backpack/translation-manager": "^1.1",
    "backpack/pagemanager": "^3.4",          // quase sempre: App\Models\Page estende o do package
    "backpack/filemanager": "^4.0",          // se usar o gestor de ficheiros ou campos browse
    "backpack/settings": "^3.2",             // se usar Setting::get()
    "backpack/activity-log": "^2.1",         // se usar
    "gemadigital/framework": "v17.x-dev",
    "laravel/framework": "^13.0",
    "laravel/tinker": "^3.0",
    "spatie/laravel-translatable": "^6.1",   // senão "Trait ... not found" no arranque
    "spatie/laravel-cookie-consent": "^3.2"  // se o Kernel usar o CookieConsentMiddleware
},
"require-dev": {
    "backpack/generators": "^4.0",
    "barryvdh/laravel-debugbar": "^4.0",
    "fakerphp/faker": "^1.23",
    "mockery/mockery": "^1.6",
    "nunomaduro/collision": "^8.6",          // sem ele não existe `artisan test`
    "phpunit/phpunit": "^13",
    "spatie/laravel-ignition": "^2.0"
},
"minimum-stability": "dev",
"prefer-stable": true,
"repositories": [
    { "type": "composer", "url": "https://repo.backpackforlaravel.com/" }
],
"scripts": {
    "post-update-cmd": ["@php artisan vendor:publish --tag=laravel-assets --ansi"],
    "post-install-cmd": [
        "App\\Helpers\\ComposerScripts::postInstall",
        "@php artisan storage:link --quiet",
        "@php artisan view:clear --quiet",
        "@php artisan cache:clear --quiet"
    ]
}
```

Notas:

- Se faltar `backpack/pro`: `BackpackProRequiredException` ("Export buttons") ao abrir a lista de utilizadores.
  Não o remover "por parecer não usado".
- `backpack/pagemanager`: o `PageCrudController` da framework estende o do package e o `Route::crud`
  instancia-o no arranque; sem o package dá fatal. Não registar `Route::crud('page')` na app (o package
  já regista e duplicaria os nomes das rotas).
- `storage:link` é necessário porque os assets do admin (Basset) são servidos de `/storage/basset`.
- `cache:clear` no deploy evita que objetos serializados por uma versão antiga do Laravel sobrevivam em
  caches `rememberForever`.
- O boilerplate já traz `storage:link` e `view:clear` no `post-install-cmd`.

### 7.2 Dependências que bloqueiam ou mudam no caminho

| Pacote | De → Para | Notas |
|---|---|---|
| `barryvdh/laravel-debugbar` | `^3` → `^4.0` | a v3 fica presa ao L12 |
| `cviebrock/eloquent-sluggable` | → `^13.0` | os modelos precisam de `sluggable(): array` |
| `laravel/sanctum` | `^3` → `^4` | |
| `laravel/tinker` | `^2` → `^3.0` | |
| `livewire/livewire` | → `^4` | atenção à propriedade `$slots` (§12) |
| `intervention/image` | `^2`/`^3` → `^4` | API diferente (§12) |
| `intervention/image-laravel` | `^1.5` → `^4.1` | config renomeada (§12) |
| `openai-php/laravel` | `^0.16` → `^0.20` | a 0.16 exige Laravel ≤ 12 |
| `endroid/qr-code` | `^5` → `^6` | só muda a API `Builder` |
| `zanysoft/laravel-zip` | → `^3.1` | |
| `spatie/laravel-translatable` | → `^6.1` | grava JSON com unicode não escapado |
| `nunomaduro/larastan` | → `larastan/larastan ^3` | corrigir o `include` no `phpstan.neon` |
| `facade/ignition` | → `spatie/laravel-ignition ^2.0` | |
| `fzaninotto/faker` | → `fakerphp/faker ^1.23` | |
| `fruitcake/laravel-cors` | remover | usar `\Illuminate\Http\Middleware\HandleCors` no Kernel |
| `immera/analytics` | remover | a 0.1.1 só suporta illuminate ≤ 10; repor exige novo tag no package |
| `backpack/langfilemanager` | remover | sem versão para Backpack 7; usar translation-manager |
| `jeroen-g/laravel-packager`, `laracasts/generators` | remover (dev) | sem versão para L13 |
| `laravel/ui` | `^4.0`, ou remover se ficar sem uso | só serve `Auth::routes()` e os controladores de `app/Http/Controllers/Auth` |
| `filament/filament` | `~4.0` | já suporta L13; v5 é opcional (§12) |
| `laravel/octane`, `maatwebsite/excel`, `twilio/sdk` | — | resolvem para versões compatíveis sem alterações |

## 8. Saltos da framework (mapa de breaking changes)

Obtido por diff das branches deste repositório. As tags só vão até à 14.2; **v15, v16 e v17 são
branches**, por isso o constraint do Composer é `"v16.x-dev"` / `"v17.x-dev"`.

### 8.1 Por versão

**8.x → 11** (não se fez o diff versão a versão; lista do que um projeto em 8.17 usava e já não existe na v17):

- `EnumHelper` e o merge de `config/enums.php`; `FileHelper`; o helper/operação de upload **dropzone**;
  `Admin\APICrudController`; a diretiva Blade `@svg`; o alias de componente `responsive-picture`;
  o merge de `config/filesystems.php`; os assets publicados em `public/gemadigital`;
  `Model::findById()` e scopes associados.
- A framework trazia como dependências o `backupmanager`, `langfilemanager`, `pagemanager`, `settings`
  e `permissionmanager`. Na v17 nenhum vem: declara-se o que se usa.

**11.x → 12.0** — rename de namespace. O psr-4 passou de `GemaDigital\Framework\` → `src/` para
`GemaDigital\` → `src/app/`. Nas apps: substituir `GemaDigital\Framework\app\` por `GemaDigital\`
em todos os imports (20 a 36 ficheiros por projeto).

**12.0 → 13.1** — `barryvdh/laravel-elfinder` saiu da framework (vem via `backpack/filemanager`).

**13.1 → 14.0** — `RunCommand`/terminal removidos; compatibilidade com Laravel 12.

**14.0 → 14.2** — `ViewAs` passou a `Impersonate`; middlewares novos. Não afetou nenhuma app.

**14.2 → v15**

- `CommonHelper` removido: desaparecem `json_response()`, `json_response_raw()`, `json_error()`,
  `json_status()` e `json_response_pagination()`. Os substitutos são macros com assinaturas 1:1:
  `response()->api()`, `apiRaw()`, `apiError()`, `apiStatus()`, `apiPagination()`.
  **A app tem de os registar** no `AppServiceProvider::boot()`; a framework não o faz.
- Removidos também: `api()`, `hasRole()`, `hasPermission()`, `restrictTo()`, `FileHelper`, `RedisHelper`,
  e `UnityHelper` (passa a `stripTagsForUnity()`).
- `Helpers\Composer\ComposerScripts` → `Helpers\ComposerScripts`.
- `CheckIfAdmin` da framework → `AdminPanelAccess`.
- `aurl()` **trocou os argumentos**: `aurl($disk, $path)` → `aurl($path, $disk = null)`.

**v15 → v16** (Laravel 12, Backpack 6)

- **Unbundling**: os addons do Backpack e as dependências Spatie deixam de vir com a framework. O
  `composer update` remove-os e a app deixa de arrancar. Declarar tudo no `composer.json` (§7.1).
  (O handoff original atribuía isto à v17; é na v16.)
- `GeneralHelpers.php` passa a ter **namespace `GemaDigital`**: `aurl()`, `sized_image()`,
  `stripTagsForUnity()`, `welcomeRoute()` etc. chamam-se com `use function GemaDigital\aurl;` ou
  `\GemaDigital\aurl(...)`. Os helpers globais (`user()`, `isAdmin()`, `is()`, `debugMode()`,
  `__fallback()`) continuam globais.
- A framework deixa de registar a rota CRUD `user`: apps com `UserCrudController` próprio precisam do
  binding em `AppServiceProvider::$bindings` (§8.2).

**v16 → v17** (Laravel 13, Backpack 7)

- `user()` perdeu o fallback `backpack_user()`: é só `Auth::user()`. O `AdminPanelAccess` verifica
  `$request->user()`, ou seja, **só o guard por defeito (`web`)**. Projetos com `'guard' => 'backpack'`
  partem em silêncio: o login funciona, mas `isAdmin()` devolve false.
- O gate de admin passa do role Spatie para a coluna **`is_admin`**: `admin()` → `isAdmin()`;
  migração com backfill a partir do role `admin` (anexo A).
- `user()` é tipado `?App\Models\User`: projetos com o modelo em `App\User` dão fatal no dashboard.
- Assinaturas com tipo de retorno: `ComposerScripts::postInstall(): void` e
  `CrudController::sync(string $operation): void`. Overrides da app sem tipo dão fatal.
- `CrudController::authorize(int $id)` recebe o **último parâmetro da rota** como id: rotas de
  operações próprias não podem ter outros parâmetros (mandá-los no corpo do pedido).
- Config `framework.php` → `gemadigital.php` (via `mergeConfigFrom`; overrides parciais funcionam).

### 8.2 O que a v17 espera da app

`app/Providers/AppServiceProvider.php`:

```php
use GemaDigital\Macros\{BuilderMacros, DBMacros, RequestMacros, ResponseMacros};

public $bindings = [
    \Backpack\PermissionManager\app\Http\Controllers\UserCrudController::class
        => \App\Http\Controllers\Admin\UserCrudController::class,
];

public function boot()
{
    ResponseMacros::register();
    RequestMacros::register();
    BuilderMacros::register();
    DBMacros::register();

    // só se o modelo mudou de App\User para App\Models\User: os registos antigos
    // (model_has_roles, activity log) guardam o nome antigo da classe
    Relation::morphMap(['App\User' => \App\Models\User::class]);
}
```

Restante lista de verificação:

- `config/backpack/base.php`: `'guard' => 'web'` e `AdminPanelAccess` no `middleware_class` (§9.5).
- Blades da app: `@auth('backpack')` → `@auth`; `@if(admin())` → `@if(isAdmin())`.
- Migração `is_admin` + `'is_admin'` no `$fillable` e no `$casts` (boolean) do `User`.
- `app/Helpers/ComposerScripts.php`: `extends \GemaDigital\Helpers\ComposerScripts` e `postInstall(): void`.
- `App\Http\Controllers\Admin\UserCrudController`: os métodos `setup*()` levam `: void`.
- Apagar `app/Http/Middleware/CheckIfAdmin.php` (e `composer dump-autoload -o`, senão o classmap
  otimizado dá fatal).
- Seeders: `guard_name` dos roles → `'web'`; `SettingsSeeder`/`LanguagesSeeder` que referenciam
  packages extintos apagam-se.
- `RolesAndPermissionsSeeder` e migrações antigas que usem `EnumHelper::values('…')` passam a ter os
  valores inline (`['publish', 'draft']`; roles `admin`/`editor`/`member`).

### 8.3 Rotas que a framework regista sempre

| Grupo | Rotas |
|---|---|
| `admin/*` (middleware `web` + alias `admin`) | `actions`, `build`, `impersonate/{id}`, `impersonate/leave`, `cache/*`, `maintenance/up`, `maintenance/down` |
| públicas (`web`) | `session/flush`, `lang/{locale}`, `auth/redirect/{driver}`, `auth/callback/{driver}`, `vitals` |

O alias de middleware `admin` vem do Backpack. Projetos sem Backpack têm de o criar (§14).

## 9. Backpack

### 9.1 Backpack 6 → 7

Obrigatório para Laravel 13. Num projeto já em framework v17 é quase só bumps:

```
backpack/crud ^6.8 → ^7.0      backpack/pro ^2 → ^3.0
backpack/theme-tabler ^1 → ^2.0   backpack/filemanager ^3 → ^4.0
backpack/generators → ^4.0 (dev)
```

Alterações de código, só se a app usar:

- Editores `wysiwyg`/`ckeditor` → **`summernote`** (o core 7 traz as views de campo e de coluna).
- `config/backpack/operations/show.php`: `'component' => 'bp-datalist'`.
- `config/backpack/ui.php`: `mix_styles`/`mix_scripts` → `vite_styles`/`vite_scripts` (§11).
- **`config/backpack/theme-tabler.php`**: a partir da 2.0.8 o array `options` publicado tem de incluir
  `showPasswordVisibilityToggler`. O array publicado substitui o do package por inteiro; sem a chave,
  `/admin/login` dá 500 ("Could not find config key").
- Os campos `browse` continuam a existir no filemanager 4; não precisam de migração.
- Os controladores de Auth da app (se existirem) precisam de `protected ?string $redirectTo`.

### 9.2 Backpack 5 → 7

Feito em psp, melgaco, ivdp, carrazeda, atouguia, aveiro e mineiro. Referência: **psp-backend** depois do upgrade.

1. Os configs são de Backpack 5 (`base.php` enorme, sem `ui.php`): **substituir o conjunto inteiro**
   pelo canónico da frota e portar a identidade (`project_name`, logótipo) do `base.php` antigo para o `ui.php`.
2. Menu: `resources/views/vendor/backpack/base/inc/sidebar_content.blade.php` converte-se à mão para
   `resources/views/vendor/backpack/ui/inc/menu_items.blade.php` com os componentes
   `<x-backpack::menu-item>`, `<x-backpack::menu-separator>` e `<x-backpack::menu-dropdown>` (anexo C).
   Manter o `@include('gemadigital::vendor.backpack.base.inc.sidebar_content_admin')`.
   ⚠️ Não envolver títulos novos em `__()` se o texto coincidir com um **grupo** de tradução
   (ex.: `'texts'` com `lang/xx/texts.php`): `__()` devolve um array e o `ucfirst` rebenta a página.
3. Apagar as restantes views `vendor/backpack/base/*` (mortas com o theme-tabler) e portar o dashboard
   para `vendor/backpack/theme-tabler/dashboard.blade.php`.
4. `protected ?string $redirectTo` nos controladores de Auth (senão o login dá fatal).
5. `EnumHelper` e `config('enums.*')` extintos: valores inline nos seeders e migrações.
6. `Admin\APICrudController` da framework extinto: apagar a subclasse morta da app.
7. `UserCrudController` da app: `: void` nos métodos `setup*()`.
8. Menus que apontam para `language/texts` (langfilemanager) passam a `backpack_url('translation-manager')`.
9. `resources/views/vendor/elfinder/elfinder.blade.php` publicado: substituir pela view do filemanager 4 (§9.4).

### 9.3 Backpack 4.1 → 7

Feito no paulomerlini.com. Além de tudo o que está em 9.2:

- **`addField($campo, 'create')` / `'update'`** (2.º parâmetro) desapareceu. O Backpack 7 funde campos com o
  mesmo nome e o último ganha. Caso real: um campo de data "hoje por defeito ao criar" passou a repor
  a data em cada gravação. Usar um só campo com `'default'`.
- **Nomes dos campos de formulário**: `locale` → `_locale`, `http_referrer` → `_http_referrer`,
  `save_action` → `_save_action`. Qualquer código que leia `request()->locale` deixa de funcionar.
- **Repeatable** passa a enviar arrays (`quotes[0][author]`) em vez de JSON. O
  `ConvertEmptyStringsToNull` passa a atuar por dentro: `""` vira `null` e `{"pt":[]}` vira
  `{"pt":null}`. As views têm de aguentar `null`.
- **`enum`**: usar o campo nativo com `'options' => [...]`; apagar a view `fields/enum.blade.php` da app.
  O campo nativo acrescenta a opção vazia sozinho quando a coluna é nullable.
- **Listagens**: o endpoint de pesquisa só aceita `length` do menu de paginação (10/25/50/100); outro
  valor dá 400 "Unknown page length".
- **Operações e campos próprios** (ex.: "arrange", dropzone): reescrever as views sobre
  `backpack_view('blank')`. O Bootstrap 5 dá padding a `.row > *`, o que desalinha grelhas calculadas
  em píxeis.
- **Assets de campos próprios**: `@basset('https://cdn…')` e `@bassetBlock('app/…js')` em vez de
  `@push('crud_fields_scripts')` com ficheiros em `public/`.
- **Guard**: o Backpack 7 regista sempre guard, provider e broker `backpack`. Roles antigos com
  `guard_name = 'backpack'` continuam atribuíveis depois de passar o admin para o guard `web`; não é
  preciso migrar dados.
- **elFinder**: `/admin/elfinder/popup/{id}` exige o parâmetro `mimes` cifrado (403 sem ele); só os
  campos `browse` geram o URL certo.
- **Tradução do site**: `langfilemanager` → `translation-manager`, que guarda na tabela
  `language_lines` (criar a migração). O que o cliente editou no sistema antigo está nos ficheiros
  `lang/` do servidor antigo.

### 9.4 Views publicadas a rever

O hint `backpack::` deixou de existir no Backpack 6. Qualquer view publicada que o use dá
`No hint path defined for [backpack]`, mas só quando alguém abre essa página.

| Ficheiro na app | Problema | Correção |
|---|---|---|
| `resources/views/vendor/elfinder/elfinder.blade.php` | estende `backpack::layouts.top_left` | substituir pela view do filemanager 4 (`@extends(backpack_view('blank'))`) |
| `resources/views/errors/layout.blade.php` | estende `backpack::layouts.*`; em produção o 404 cai no genérico e regista um erro por pedido | `@extends(backpack_view('errors.layout'))` |
| `resources/views/vendor/backpack/base/**` | login, register, head, topbars, menu do utilizador de eras antigas | apagar; o theme-tabler serve os defaults |
| `resources/views/vendor/backpack/crud/fields/*`, `columns/*` | cópias de campos de Backpack 4 (`range`, `browse_table`, `enum`, `url`, `tel`) | apagar as que têm equivalente nativo; reescrever as próprias |

### 9.5 `config/backpack/base.php`

```php
'middleware_class' => [
    \GemaDigital\Http\Middleware\AdminPanelAccess::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    \Backpack\CRUD\app\Http\Middleware\AuthenticateSession::class,
    \Backpack\LanguageSwitcher\Http\Middleware\LanguageSwitcherMiddleware::class,
],

'guard' => 'web',
```

Sobre o `AuthenticateSession`:

- O Laravel ≥ 13.3x guarda o `password_hash_<guard>` na sessão como **digest sha256**. O middleware do
  Backpack **≤ 7.1.20** comparava com o hash bcrypt cru: mismatch em todos os pedidos, sessão limpa,
  utilizador de volta ao login com "A sua sessão expirou".
- Está **corrigido no `backpack/crud` 7.1.22** (verificado com Laravel 13.35). Em qualquer
  `composer update`, confirmar no lock que o crud sobe para ≥ 7.1.22 junto com o Laravel.
- Se o crud ficar preso numa versão anterior: trocar pelo nativo
  `\Illuminate\Session\Middleware\AuthenticateSession::class` (foi o que se fez no imagaia).
- Diagnóstico rápido: comparar `session('password_hash_web')` com `$user->getAuthPassword()`. Se o
  primeiro for uma string hexadecimal e o segundo começar por `$2y$`, é este problema.

## 10. Laravel: checklist de breaking changes

Além de arrancar e testar, **cruzar os upgrade guides oficiais de cada salto com greps no código**.
A lista usada na frota:

**8 → 9**

- `CheckForMaintenanceMode` → `PreventRequestsDuringMaintenance` (classe da app e Kernel).
- `TrustProxies` da app passa a estender `Illuminate\Http\Middleware\TrustProxies` (o `fideloper/proxy` acabou).
- `fruitcake/laravel-cors` → `\Illuminate\Http\Middleware\HandleCors::class` no Kernel.
- `facade/ignition` → `spatie/laravel-ignition`; `fzaninotto/faker` → `fakerphp/faker`.
- Flysystem 3 e Symfony Mailer: `config/mail.php` no formato `default` + `mailers` (ver "12 → 13").
- `App\User` → `App\Models\User`: atualizar `config/auth.php`, `config/backpack/permissionmanager.php`,
  factories, seeders e `routes/channels.php`; `Relation::morphMap` para os registos antigos (§8.2).
- `config/app.php`: `ServiceProvider::defaultProviders()->merge([...])` e `Facade::defaultAliases()->merge([])`.
- `public/index.php`: usar a versão que carrega `storage/framework/maintenance.php`.
- Kernel: `$routeMiddleware` → `$middlewareAliases`; o alias `bindings` do grupo `api` passa a
  `\Illuminate\Routing\Middleware\SubstituteBindings::class`; remover overrides antigos de `$middlewarePriority`.

**9 → 10**

- `$dates` removido dos modelos → `$casts` (`'date' => 'date'`). Na frota estava quase sempre comentado.
- `DB::raw` em casts de string, `dispatchNow`: zero ocorrências na frota.

**10 → 11**

- A estrutura nova (`bootstrap/app.php`) é opcional; a legacy mantém-se.
- `float`/`double` e colunas espaciais em migrações; Sanctum 3 → 4.
- Carbon 2 → 3: ver a versão no lock **antigo**; se já era 3.x não há salto.
- `Lang::has()` passa a devolver true para valores JSON vazios. Um `__fallback('app.x')` passa a
  imprimir a chave se `lang/*.json` tiver `"app.x": ""`. Apagar as entradas vazias.

**11 → 12**

- `HasUuids` gera UUIDv7 (usar `HasVersion4Uuids` para manter).
- O disco `local` muda a raiz por defeito para `storage/app/private` se não estiver definido no `filesystems.php`.
- A regra de validação `image` deixa de aceitar SVG (`image:allow_svg`).
- `Schema::getTables/getViews/getTableListing`, `mergeIfMissing`, `Concurrency::run`,
  `DatabaseTokenRepository`, rotas com nomes duplicados.

**12 → 13**

- CSRF: `PreventRequestForgery` verifica `Sec-Fetch-Site`/Origin (o `VerifyCsrfToken` continua como alias).
  Só afeta POSTs web.
- `cache.serializable_classes`: endurece o unserialize de objetos em cache. Sem a chave não há restrição;
  definir uma allow-list se a app guardar objetos em cache.
- `upsert()` exige `uniqueBy` não vazio.
- Prefixos de cache e cookie de sessão passam a usar hífens (só afeta quem depende dos defaults).
- Rehash da password no login e, a partir do 13.3x, `password_hash_<guard>` guardado na sessão como
  digest (os dois estão na origem dos problemas com o `AuthenticateSession`, §9.5).
- DELETE + JOIN + ORDER/LIMIT em MySQL, `new static` dentro de `boot()`, funções próprias
  `array_first`/`array_last`, `Js::from`, views `pagination::default`, eventos `JobAttempted`/`QueueBusy`,
  `::extend(` de managers, rotas com `->domain()`, pivots polimórficos.
- Mail: `encryption` / `MAIL_ENCRYPTION` **deixou de ser lido**. Porta 465 usa TLS implícito; as restantes
  fazem STARTTLS automático. Para `.env` antigos: `'default' => env('MAIL_MAILER', env('MAIL_DRIVER', 'smtp'))`.
- `artisan down --allow=IP` não existe desde o Laravel 8; o equivalente é `--secret`.

Greps de arranque:

```bash
grep -rnE "HasUuids|->upsert\(|Schema::get(Tables|Views|TableListing)|mergeIfMissing|Js::from|new static|array_(first|last)\(|\\\$dates|dispatchNow|::extend\(" app database routes
grep -rnE "Cache::|cache\(\)|rememberForever" app      # objetos em cache → serializable_classes
grep -rn "env(" app resources/views routes               # env() fora de config parte com config:cache
grep -rnE "'image'|\|image\b" app/Http                  # regra de validação image
```

## 11. Frontend: laravel-mix → Vite 8

Referência: `laravel-backpack-boilerplate`.

1. **`package.json`**: `"type": "module"`; scripts `"build": "vite build"`, `"dev": "vite"` e manter o
   alias `"prod": "vite build"` (deploys antigos chamam `npm run prod`). Dependências: `vite ^8`,
   `laravel-vite-plugin ^3`, `eslint ^10`, `@eslint/js`, `@stylistic/eslint-plugin`, `globals`, e `sass`
   se o projeto usa scss. Remover `laravel-mix`, `webpack`, `sass-loader`, `resolve-url-loader`,
   `sass-resources-loader`, `postcss`.
2. **`optionalDependencies`** `@rollup/rollup-linux-x64-gnu` e `lightningcss-linux-x64-gnu`: o servidor
   de build é Linux e o lock é gerado em Windows.
3. **`vite.config.js`** com as entradas do antigo `webpack.mix.js`:

   ```js
   import { defineConfig } from 'vite';
   import laravel from 'laravel-vite-plugin';

   export default defineConfig({
     plugins: [
       laravel({
         input: ['resources/sass/app.scss', 'resources/sass/admin.scss', 'resources/js/app.js', 'resources/js/admin.js'],
         refresh: ['resources/views/**/*'],
       }),
     ],
     css: {
       preprocessorOptions: {
         scss: { loadPaths: ['node_modules'], quietDeps: true },
       },
     },
   });
   ```

4. **Sass**: `@import` → **`@use`** nos ficheiros da app (`@use "common";`; `@use "x" as *;` quando o
   parcial define variáveis ou mixins usados a seguir). O `@import` desaparece no Dart Sass 3.
   - Imports de packages: o sass-loader resolvia `@import "@picocss/pico"` para o CSS compilado; no Vite
     importar a fonte (`@use "@picocss/pico/scss/pico";`).
   - Avisos vindos de dentro dos packages silenciam-se com `quietDeps: true`.
   - `@import url(...)` de Google Fonts sai do scss e passa a `<link rel="stylesheet">` no `<head>` das
     blades, antes do `@vite`. No bundle ficaria depois de outras regras e os browsers ignoram-no.
5. **Blades**: `mix('css/app.css')`, `mix('js/app.js')` e os preloads passam a um único
   `@vite(['resources/sass/app.scss', 'resources/js/app.js'])` no `<head>`. Os scripts do Vite são módulos
   ES (diferidos), por isso podem sair do fundo do `<body>`.
6. **`config/backpack/ui.php`**: `'vite_styles' => ['resources/sass/admin.scss']`,
   `'vite_scripts' => ['resources/js/admin.js']`.
7. **Limpeza**: apagar `webpack.mix.js` e `.eslintrc.json`; `eslint.config.mjs` em formato flat (copiar do
   boilerplate se não existir); `.gitignore` com `/public/build` e `/storage/basset`.
8. **Verificar**: `npm run build`; a página pública carrega `build/assets/app-*` e o dashboard
   `build/assets/admin-*`.

Variantes encontradas:

| Caso | Solução |
|---|---|
| scss com imports estilo webpack (`~normalize.css`, `~milligram/src/…`) | alias no Vite: `resolve: { alias: [{ find: /^~/, replacement: '' }] }` |
| parciais de terceiros que só funcionam com `@import` (milligram) | manter o `@import` nesse ficheiro e `silenceDeprecations: ['import']` |
| `import Packery from 'packery/dist/packery.pkgd.min'` | importar a entrada do package (`'packery'`, `'draggabilly'`); o rolldown não empacota os `.pkgd` |
| entrada extra no mix (ex.: `ledwall.scss`) | acrescentar ao `input` e um `@vite('resources/sass/ledwall.scss')` na blade |
| sass da própria framework importado via `vendor/` | manter o `@import` nesse ficheiro + alias `~` |
| pico v1 em uso | não subir para a v2 sem auditar: o CSS muda |
| service worker com cache-first para o mesmo domínio | excluir `/storage` (os URLs do Basset não têm versão) |

Não copiar às cegas o `package.json` de outro projeto: cada um tem as suas dependências (Bootstrap,
milligram, pico…). O lightningcss do Vite 8 reescreve `calc(100vh / 3)` como `33.3333vh`; a diferença é
inferior a 1/64 px e aceita-se.

## 12. Dependências com salto de major

### 12.1 `intervention/image` → 4

De **v2** (API totalmente diferente):

| v2 | v4 |
|---|---|
| `Image::make($x)` | `ImageManager::usingDriver(Driver::class)->decode($x)` (ou `decodeDataUri()`, `decodeSplFileInfo()`, `decodePath()`) |
| `->resize(W, null, fn ($c) => $c->aspectRatio()->upsize())` | `->scaleDown(width: W)` |
| `->stream()` / `->encode('jpg', 85)` | `(string) $img->encodeUsingFileExtension('jpg', quality: 85)` |

De **v3**: `read()` foi removido (usar `decode()`, `decodePath()`, `decodeBinary()`);
`encodeByExtension()` → `encodeUsingFileExtension()`, `encodeByMediaType()` → `encodeUsingMediaType()`,
`encodeByPath()` → `encodeUsingPath()`.

`intervention/image-laravel` 1.x → 4.x: a facade `Image` segue os mesmos renames e o **config muda de
`config/image.php` para `config/intervention-image.php`**; o ficheiro antigo é ignorado em silêncio.

A v4 aplica a orientação EXIF por defeito. Provar sempre em runtime: mesmas dimensões, proporção
mantida e sem ampliar imagens pequenas.

### 12.2 Livewire → 4

O `Component` base ganhou `protected array $slots`. Um componente da app que declare `$slots` próprio dá
**fatal** se for `private`, ou conflito silencioso se for público. Grep obrigatório:
`grep -rn '\$slots' app/Livewire app/Http/Livewire` e renomear (ex.: `$availableSlots`).

### 12.3 phpunit → 13

- `^13` resolve com `nunomaduro/collision ^8.6`.
- `phpunit.xml`: tirar os atributos antigos (`backupStaticAttributes`, `convertErrorsToExceptions`, …),
  trocar `<filter><whitelist>` por `<source><include>`, e `MAIL_DRIVER` por `MAIL_MAILER`.
- O `Tests\Feature\ExampleTest` (`get('/')` espera 200) dá 302 nos projetos em que `/` é o
  `welcomeRoute()` da framework. É pré-existente e inofensivo.

### 12.4 Outras

| Pacote | Salto | O que muda |
|---|---|---|
| `endroid/qr-code` | 5 → 6 | só a API `Builder`; `new QrCode(...)` + `PngWriter` ficam iguais |
| `openai-php/laravel` | → 0.20 | bump; `openai` (npm) 6 → 7 no commed, onde o package nem é importado |
| `@picocss/pico` | 1 → 2 | auditar o uso; no qatar só `.container` e `data-theme`, compatíveis; as cores do tema mudam ligeiramente |
| `laravel/sanctum` | 3 → 4 | sem alterações de código na frota |
| `spatie/laravel-permission` | → 6 | o `config/permission.php` antigo continua a servir |
| `filament/filament` | 4 → 5 (opcional) | `composer require filament/upgrade:"^5.0" -W --dev`, `{ echo app; yes; } \| vendor/bin/filament-v5`, `composer require filament/filament:"^5.0" -W --no-update`, `composer update`, `composer remove filament/upgrade --dev`; exige Livewire 4 e Tailwind 4 |
| `webpack` (só se o projeto ficar em laravel-mix) | pin `5.89.0` em `devDependencies` e `overrides` | o 5.90+ removeu `SizeFormatHelpers`, que o mix importa |
| `cantil` (npm) | ≥ 1.1.5 | `swipeable.init()` e `observable.init()` rebentam (ver Parte IV) |

## 13. Qualidade: phpstan, pint, CI

Correr só onde estão instalados; o CI corre-os no push.

**phpstan** (`vendor/bin/phpstan analyse` ou `composer analyse-stan`). O `composer update` traz
larastan/phpstan mais recentes, que acusam erros novos em código antigo. Padrões que se repetiram:

1. Seeders mortos (`SettingsSeeder`, `LanguagesSeeder`) → apagar e tirar do `DatabaseSeeder`.
2. `App\Models\Page` estende o modelo do pagemanager → declarar `backpack/pagemanager ^3.4`.
3. Traits de operações e `PageTemplates` → `/** @phpstan-ignore trait.unused */`.
4. `Authenticate::redirectTo()` → `return null;` no fim.
5. `user()->tokens()` → `user()?->tokens()` (a v17 tipa `user()` como nullable).
6. API mágica do Backpack (`->suffix()`, `->width()`, `->options()`, `->attribute()`) → entrada em
   `ignoreErrors` do `phpstan.neon`.
7. Relações sem generics → `/** @return BelongsTo<Model, $this> */`.
8. `env()` fora de configs → mover para `config/services.php`.
9. `nunomaduro/larastan` → `larastan/larastan ^3` e corrigir o `include` do `phpstan.neon`.
10. `tests/Unit/ExampleTest` com `assertTrue(true)` → `/* @phpstan-ignore-next-line */`.

**pint**: `--test` só nos ficheiros alterados, a menos que seja pedido o projeto inteiro. Em ficheiros
CRLF, `line_ending`, `unary_operator_spaces` e `not_operator_with_successor_space` são falsos positivos:
normalizar para LF e voltar a testar antes de "corrigir". Se só se mexeu no `composer.json`, não reformatar.

**CI** (`.github/workflows`): `actions/checkout@v3` → `@v4` (o v3 corre em node16, descontinuado) e a
matriz do Laravel para `^13.0`.

**rector**: no imagaia o `--dry-run` propunha `declare(strict_types=1)` em cerca de 97 ficheiros. Não se
aplicou: é uma mudança de comportamento que fica para decisão.

## 14. Instalar a framework num projeto sem boilerplate

Feito no pixel-backend e no pixel-rumos-backend (APIs sem Backpack).

1. Composer: `"gemadigital/framework": "v17.x-dev"` com `"minimum-stability": "dev"` e `"prefer-stable": true`.
   A v17 exige Laravel `^12.49|^13.1`, por isso obriga a subir o Laravel em conjunto. Não são precisos
   os repositórios do Backpack.
2. `AppServiceProvider::boot()`: registar os quatro macros (§8.2).
3. As rotas da framework registam-se sempre (§8.3). Sem Backpack não existe o alias de middleware
   `admin`: criar `App\Http\Middleware\AdminAccess`
   (`abort_unless($request->user() !== null && isAdmin($request->user()), 403)`) e registá-lo como
   `'admin'` nos aliases do Kernel (ou no `bootstrap/app.php`).
4. Migração `is_admin` (default false, sem backfill) + `$fillable`/`$casts` no `User`. O gate fica fechado
   até alguém ser marcado à mão na base de dados.
5. Verificar: arranque, `/vitals` → 200, `/admin/actions` como convidado → 403, macros em runtime.

Em projetos Filament (portaldasaude, faculdade-direito, apram) a instalação chegou a ser começada e
foi cancelada a pedido; ficou tudo revertido.

## 15. Verificação

### 15.1 Nível 1 — sandbox rápida (todos os projetos)

1. `.env` a partir do `.env.example` com `DB_CONNECTION=sqlite` e
   `DB_DATABASE=<caminho absoluto>\database\database.sqlite` (caminho vazio falha), `php artisan key:generate`,
   `php artisan migrate` e seed.
2. Arranque: `php artisan about --only=environment`. (Em alguns projetos da frota o `route:list`
   abortava ao enumerar as rotas; se acontecer, não é sinal de que a app não arranca.)
3. **Login HTTP real**, com cookies:

   ```bash
   curl -s -c jar -b jar http://127.0.0.1:8000/admin/login -o login.html
   TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' login.html)
   curl -s -c jar -b jar -o /dev/null -w "%{http_code} %{redirect_url}\n" \
        --data-urlencode "_token=$TOKEN" --data-urlencode "email=…" --data-urlencode "password=…" \
        http://127.0.0.1:8000/admin/login                       # 302 → /admin/dashboard
   curl -s -c jar -b jar -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/admin/dashboard   # 200
   curl -s -c jar -b jar -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/admin/dashboard   # 200 outra vez
   ```

   O segundo pedido ao dashboard é o que apanha os problemas de sessão. Repetir com um utilizador
   sem `is_admin`: tem de ser barrado.
4. `npm run build`; página pública e dashboard a carregar os assets de `build/assets`.
5. Testes, phpstan e pint (onde existem).
6. Dependências mexidas: um script PHP que exercite a cadeia real (gerar um QR, decode + encode de uma
   imagem). Em scripts standalone que arrancam o Kernel, definir `define('LARAVEL_START', microtime(true));`
   no topo, senão os macros `api` rebentam em modo debug.
7. **`composer install`** completo no fim.

O que abrir sempre, porque foi onde apareceram surpresas: lista de utilizadores (botões de exportação
do pro), uma página pública servida por `/{page}` (pagemanager), `/admin/elfinder`, uma página 404, um
formulário com editor e upload.

### 15.2 Nível 2 — baseline antigo-vs-novo

Para projetos sem testes e com saltos grandes (usado no paulomerlini.com, L8 → 13). A ideia: pôr o
**stack antigo a correr ao lado do novo, com os mesmos dados**, e comparar.

**Montagem**

1. Clone do `HEAD` para uma pasta temporária. `composer install --ignore-platform-reqs` com PHP 8.3
   (`-d extension=zip`) e o build antigo do frontend.
2. Um `mysqld` próprio, noutra porta e com datadir temporário (`--no-defaults --datadir=… --port=3317
   --bind-address=127.0.0.1`), com duas bases de dados (antiga e nova). Nunca usar nem parar as
   instâncias do Laragon.
3. Servir o antigo e o novo em portas diferentes, cada um a partir da sua pasta `public/`.
4. **Semear os dados pelo admin antigo, por HTTP**: um script com cookies que preenche e submete os
   formulários. Os dados ficam no formato real de produção (JSON das traduções, galerias, ficheiros,
   tamanhos de imagem), coisa que factories não garantem.
5. Dump da base antiga → importar na nova → `php artisan migrate`. Prova as migrações novas sobre
   dados antigos. Copiar também os uploads.

**Provas**

| Prova | O que demonstra |
|---|---|
| Snapshots HTML das páginas públicas (todas as línguas, a frio e com cache), normalizados e comparados | o site público é igual |
| "Abrir e gravar sem mexer" em todos os formulários do admin novo, com diff da base de dados | o admin novo não corrompe dados antigos |
| Suite de CRUD: criar, editar, traduzir, apagar, clonar, reordenar, uploads, permissões, CSRF | as operações funcionam |
| Crawl de todas as rotas GET do admin (`route:list --json`) | não há páginas partidas |
| Estilos calculados do CSS antigo vs novo no browser, em várias resoluções | a migração mix → Vite não mudou o aspeto |
| JS real no browser: interações do site e campos com JS (dropzone, cropper, editor, drag and drop) | o que o HTTP não vê |
| `php artisan optimize` e repetir os pedidos | compatível com as caches de produção |
| Reset de password contra o mailpit | o email sai com o `.env` antigo |
| Link check de todos os recursos referenciados pelas páginas | não há imagens ou assets em falta |
| `php -l` em 8.3 e 8.4 com deprecations; `php artisan view:cache` | sintaxe e todas as blades compilam |

Notas técnicas:

- Para ler os formulários do Backpack usar `Dom\HTMLDocument` (PHP 8.4); o libxml antigo fecha o `<form>`
  a meio. Emular o que o JS do Backpack faz: a linha-modelo `[-1]` do repeatable não é submetida e o
  hidden do checkbox vazio é normalizado para `0`.
- O normalizador de snapshots tira o que muda legitimamente: tokens CSRF, host, e as tags de assets
  (mix de um lado, Vite do outro).
- Schema dumps (`database/schema/mysql-schema.sql`) podem estar desatualizados em relação às migrações.

**Limpeza**: parar o servidor PHP **antes** de apagar uploads de teste; remover `.env`, uploads de teste,
sessões, logs, `storage/debugbar` e cache do Basset; desligar o `mysqld` próprio; confirmar que o
`git status` não mostra restos.

## 16. Entrega e deploy

**Antes de entregar**

- `composer install` local: é o caminho exato do deploy e o único que corre o `post-install-cmd`.
- Sandbox removida. Ficam `vendor/`, `node_modules/` e `public/build` (ignorados pelo git).
- Nada commitado. O relatório diz o resultado, como foi verificado, as decisões tomadas e o que fica
  por decidir.

**Checklist de deploy**

1. `composer install` (corre `storage:link`, `view:clear`, `cache:clear`).
2. `php artisan migrate` (`is_admin` com backfill; `language_lines` se entrou o translation-manager).
3. `npm ci && npm run build` (o alias `npm run prod` continua a existir).
4. As **sessões ativas caem** (mudança de guard e de formato de sessão): é preciso voltar a entrar.
5. PHP **≥ 8.4.1** no servidor.
6. Assets do admin (Basset): na primeira visita o servidor vai buscar os ficheiros aos CDNs; se não
   tiver acesso de saída, correr `php artisan basset:cache` no deploy.
7. Depois do primeiro deploy com Vite, apagar `public/js`, `public/css` e `public/mix-manifest.json`.
8. Ao mudar de servidor, copiar o que não está no git: `public/uploads`, vídeos, e ficheiros `lang/`
   editados em produção.
9. Projetos do mesmo lote com retrofits pendentes vão para produção em conjunto.

Se algo falhar depois do deploy, procurar o sintoma na Parte III.

---

# Parte III — Catálogo de armadilhas

Todas apareceram de facto, em sandbox ou em produção.

## A. Composer e arranque

| Sintoma | Causa | Correção |
|---|---|---|
| `BackpackProRequiredException` ("Export buttons") na lista de utilizadores | falta `backpack/pro` | declarar `backpack/pro ^3.0` |
| `Trait … not found` no arranque | falta `spatie/laravel-translatable` | declarar `^6.1` |
| `Class "Backpack\PageManager\app\Models\Page" not found` ao abrir uma página pública | `App\Models\Page` estende o modelo do pagemanager, removido pelo unbundling | `backpack/pagemanager ^3.4` |
| `Route [page.edit] not defined` no menu | o mesmo | o mesmo |
| Deploy falha: `Declaration of App\Helpers\ComposerScripts::postInstall() must be compatible with … : void` | assinatura da v17; só corre no `composer install` | `postInstall(): void` |
| Fatal no arranque num CRUD com `sync($operation)` | assinatura da v17 | `sync(string $operation): void` |
| Fatal no dashboard em projetos com `App\User` | `user()` da v17 é tipado `?App\Models\User` | mover o modelo + `Relation::morphMap` |
| Fatal no login: tipo de `$redirectTo` | Backpack 7 tipa `?string` | `protected ?string $redirectTo` |
| `Call to undefined function json_response()` | helpers removidos na v15 | macros `response()->api*()` + `ResponseMacros::register()` |
| `Call to undefined function stripTagsForUnity()` / `aurl()` / `sized_image()` | helpers com namespace desde a v16 | `use function GemaDigital\…` ou `\GemaDigital\…()` |
| Classe `CheckIfAdmin` em falta depois de a apagar | classmap otimizado | `composer dump-autoload -o` |
| Migração antiga falha em `EnumHelper::values(...)` | helper e `config/enums` extintos | valores inline |
| Composer devolve 401 em `backpack/pro` | falta o `auth.json` global | configurar as credenciais |
| A resolução do Composer bloqueia em `immera/analytics` | a 0.1.1 só suporta illuminate ≤ 10 | remover (sem uso) |
| `vendor/` incompleto depois de um `composer update` | timeout de rede a meio | reinstalar e voltar a verificar |

## B. Admin e sessão

| Sintoma | Causa | Correção |
|---|---|---|
| Login com sucesso mas sem acesso ao painel, com `is_admin = true` | `'guard' => 'backpack'`; a v17 só olha para o guard `web` | `'guard' => 'web'` + `@auth` nas blades |
| Entra e é logo expulso com "A sua sessão expirou", em loop | `AuthenticateSession` do Backpack ≤ 7.1.20 com Laravel ≥ 13.3x | `backpack/crud` ≥ 7.1.22 (§9.5) |
| A sessão perde-se num pedido `/api/*` feito de dentro do admin, sem nada nos logs (commed: biblioteca de media em loading infinito) | o mesmo middleware a limpar a sessão | na altura comentou-se o middleware; hoje, crud ≥ 7.1.22. Diagnóstico: rota temporária que devolve o estado da sessão em JSON |
| `/admin/login` dá 500 "Could not find config key" | theme-tabler ≥ 2.0.8 | `options.showPasswordVisibilityToggler` no config publicado |
| `No hint path defined for [backpack]` em `/admin/elfinder` ou numa página 404 | views publicadas de Backpack 4/5 | §9.4 |
| `Call to undefined function admin()` vindo de `storage/framework/views/*.php`, com o vendor já correto | view compilada antiga; o Blade só recompila se a fonte for mais recente e os zips do Composer preservam datas antigas | `php artisan view:clear`; pô-lo no `post-install-cmd` |
| `Call to undefined function aurl()` na view `components/slides` da framework | bug da framework (Parte IV) | override em `resources/views/vendor/gemadigital/components/slides.blade.php` com `\GemaDigital\aurl($img, 'uploads')` |
| `ucfirst(): … array given` ao desenhar o menu | `__('texts')` devolve o grupo de tradução inteiro | não envolver esse título em `__()` |
| A data de um registo muda para hoje sempre que se grava | `addField($campo, 'create')` de Backpack 4 | um só campo com `'default'` |
| Upload do dropzone dá 400: `authorize(): Argument #1 ($id) must be of type int` | a v17 usa o último parâmetro da rota como id | rotas da operação sem parâmetros |
| Editar uma página no admin não atualiza o site | `PageCrudController::update()` da framework lê `request()->locale` | override na app que esquece a cache de todos os locales (anexo B) |
| Botão "manutenção" dá 400: `The "--allow" option does not exist` | bug da framework (Parte IV) | controlador próprio com `--secret` (anexo D) |
| Botão "Impersonar" não faz nada | falta `ImpersonateMiddleware` no grupo `web` | registar o middleware ou esconder o botão |
| Assets do admin dão 404 em `/storage/basset/…` | falta o link `public/storage` | `php artisan storage:link` |
| `og:description` mostra `app.description` | `Lang::has()` com valor JSON vazio | apagar as entradas vazias de `lang/*.json` |
| Admin com JS/CSS antigos depois de um update | service worker cache-first + URLs do Basset sem versão | excluir `/storage` do service worker |

## C. Dados

| Sintoma | Causa | Correção |
|---|---|---|
| Campos repeatable passam de `""`/`[]` a `null` depois de gravar | Backpack 7 envia arrays e o `ConvertEmptyStringsToNull` atua por dentro | benigno; as views têm de aguentar `null` |
| Roles com `guard_name = 'backpack'` | herança do guard antigo | não migrar; continuam atribuíveis |
| `model_type = 'App\User'` em `model_has_roles` | modelo mudou de namespace | `Relation::morphMap`; o backfill do `is_admin` considera os dois nomes |
| Formulário falha numa base criada a partir do schema dump | `database/schema/mysql-schema.sql` desatualizado | recriar a tabela pelas migrações; sinalizar |
| Imagens rodadas de forma diferente | intervention 4 aplica a orientação EXIF | esperado |

## D. Frontend

| Sintoma | Causa | Correção |
|---|---|---|
| `Cannot find module 'webpack/lib/SizeFormatHelpers'` | webpack ≥ 5.90 com laravel-mix | migrar para Vite (ou pin `5.89.0`) |
| Avisos `Sass @import rules are deprecated` | Dart Sass | `@use`; `quietDeps` para os packages |
| O build falha em `@use "@picocss/pico" as *` | o Vite não resolve o CSS compilado do package | `@use "@picocss/pico/scss/pico" as *` |
| O build falha em `packery.pkgd.min` | o rolldown não empacota os `.pkgd` | importar `'packery'` e `'draggabilly'` |
| Todo o JS do site morto: `ReferenceError: Cannot access 'e' before initialization` | cantil ≥ 1.1.5: `swipeable.init()` | implementação local até o package ser corrigido |
| A fonte do Google deixa de carregar | `@import url()` a meio do CSS | `<link>` na blade |
| Grelha de uma operação própria desalinhada | Bootstrap 5 dá padding a `.row > *` | tirar a `.row` e fixar `box-sizing`/`padding` |

## E. Verificação e ambiente

| Sintoma | Causa | Correção |
|---|---|---|
| A verificação passou e o admin falhou em produção | testou-se com `Auth::login()` | usar sempre o login HTTP real |
| A verificação passou e o deploy falhou no Composer | só se correu `composer update` | `composer install` no fim |
| `artisan test` dá fatal no arranque local | phpunit 13 com `zend.assertions=1` | `php -d zend.assertions=-1 artisan test` |
| Páginas dão 500 no servidor de desenvolvimento (`getimagesize` com caminho relativo) | o `php -S` não está a correr em `public/` | `cd public && php -S … ../server.php` |
| `Permission denied` ao apagar ou recriar uploads | o `php -S` segura os ficheiros que serviu | parar o servidor primeiro |
| O pint acusa `line_ending` em quase tudo | ficheiros em CRLF | normalizar os ficheiros editados para LF |
| `route:list` aborta com "Os utilizadores não têm um endereço de e-mail associado" | visto no handoff em alguns projetos; causa não investigada | usar `about --only=environment` |
| 500 na homepage da sandbox | falta a linha `pages` na base de dados de teste | semear; não é bug |
| `ExampleTest` devolve 302 | `/` é o `welcomeRoute()` | pré-existente |
| Listagem do admin devolve 400 "Unknown page length" em scripts de teste | o Backpack 7 só aceita `length` do menu | usar 10, 25, 50 ou 100 |
| `/admin/elfinder/popup/x` devolve 403 em testes | falta o parâmetro `mimes` cifrado | só os campos `browse` geram o URL certo |

---

# Parte IV — Pendentes na própria framework (v17)

Confirmados no código da branch `v17` (commit `0c684c5`) a 12/10/2026.

| # | Onde | Problema | Sugestão |
|---|---|---|---|
| 1 | `src/app/Http/Controllers/Admin/MaintenanceController.php` | `down()` passa `--allow` ao `artisan down`, opção removida no Laravel 8; o botão dá sempre 400 | `--secret` aleatório e devolver o cookie `MaintenanceModeBypassCookie` ao admin (anexo D). Exige `laravel_maintenance` fora do `EncryptCookies` da app |
| 2 | `src/app/Http/Controllers/Admin/PageCrudController.php` | `update()` lê `request()->locale`; o Backpack 7 envia `_locale`. A cache `page_{slug}_{locale}` não é invalidada | esquecer a cache de todos os locales de `config('backpack.crud.locales')` |
| 3 | `src/app/Models/Traits/SaveMedia.php` | usa `Intervention\Image\Image::make()` (API v2) e `string $value`, que rejeita `null` | portar para a API v4 (anexo B tem a versão usada no paulomerlini) |
| 4 | `src/resources/views/components/slides.blade.php` | `aurl('uploads', $img)`: sem namespace e com a ordem de argumentos anterior à v15 | `\GemaDigital\aurl($img, 'uploads')` |
| 5 | `src/resources/views/components/responsive-picture.blade.php` | `sized_image()` sem namespace | `\GemaDigital\sized_image()` |
| 6 | `src/resources/views/admin/flagpicker.blade.php` | view morta (nada a inclui); era o seletor de idioma de Backpack 4 | apagar |
| 7 | `AdminPanelAccess` + `user()` | só olham para o guard por defeito; projetos com guard próprio partem em silêncio | documentar, ou usar `backpack_user()` |
| 8 | `ImpersonateMiddleware` | o botão "Impersonar" do `UserCrudController` aparece sempre, mas só funciona se a app registar o middleware (o boilerplate regista; os projetos de Kernel legacy não) | registar no provider ou esconder o botão quando o middleware não está ativo |
| 9 | `changelog.md` | só tem "Version 1.0"; as breaking changes viviam apenas no histórico do git | usar a secção 8 deste manual como base |

**Noutros repositórios**

| Onde | O quê |
|---|---|
| `cantil` (npm) ≥ 1.1.5 | `swipeable.init()` e `observable.init()` rebentam com `ReferenceError` (`for (const swipeable of swipeable.elements)`). O carrazeda e o ivdp chamam-nos com a 1.1.6 |
| Projetos vindos de Backpack 4/5 | rever `resources/views/errors/layout.blade.php` (§9.4). O `elfinder.blade.php` já foi corrigido em todos |
| Projetos com `PageTrait` (carrazeda, missao, cupav, …) | afetados pelo ponto 2 até a framework ser corrigida |
| Projetos onde entrou o translation-manager | confirmar que existe a migração `language_lines` |
| Projetos de julho (Laravel 13.21/13.23, crud ≤ 7.1.20) | no próximo `composer update`, garantir crud ≥ 7.1.22 |
| centro-acolhimento-visitante | lockado em theme-tabler 2.0.6; vai precisar de `showPasswordVisibilityToggler` quando atualizar |
| imagaia.pt | ficou com o `AuthenticateSession` nativo; pode voltar ao do Backpack com crud ≥ 7.1.22 (opcional) |
| `immera/analytics` | precisa de um tag com illuminate `^13` para voltar a ser instalado |
| paulomerlini.com | por decidir: backups (`backpack/backupmanager ^5.1` ou apagar a agenda `backup:*`), botão "Impersonar", dependências npm sem uso (jquery, easing-utils), schema dump desatualizado |

---

# Parte V — Histórico detalhado da conversa

Cronologia do que foi pedido, do que se fez e do que correu mal, pela ordem em que aconteceu.

## Fase 0 — O handoff (até 22/07/2026)

Uma primeira sessão de Claude, noutra conta e em WSL, atualizou 14 projetos e deixou um documento de
passagem com os playbooks de então: framework v15 → v17, Laravel 12 → 13, Backpack 6 → 7, Filament 4 → 5,
intervention/image 3 → 4, endroid/qr-code 5 → 6, o pin do webpack, a metodologia de verificação em
sqlite e o caso do commed-ia (sessão perdida por causa do `AuthenticateSession`). Tudo isso está
integrado na Parte II. Estado que vinha no handoff:

| Projeto | O que foi feito |
|---|---|
| doritos-backend | re-adicionado `backpack/pro ^3.0` (tinha sido removido por engano) |
| admin.kopke.gemadigital.com | Laravel 13, tinker 3, phpunit 13.2, endroid/qr-code 6.1 |
| centro-acolhimento-visitante | framework v15 → v17 completo, npm update (vite 8, eslint 10); o commit `fe5f935` serviu de modelo para o resto da frota |
| alandroal-backend | framework v15 → v17 completo, npm update |
| azap.immera.io | Laravel 13, Filament 4 → 5 |
| cards.immera.io | Laravel 13, Backpack 6 → 7, intervention/image 3 → 4 |
| api.immera.io, profitecla, admin.mogadouro, admin.immera.io, admin.holobox | Laravel 13 |
| onecore-laravel | Laravel 13, webpack pinado em 5.89.0 |
| commed-ia-backend | Laravel 13, Backpack 6 → 7, correção da sessão, intervention/image-laravel 1 → 4 |
| laravel-backpack-boilerplate | `knuckleswtf/scribe` passou de `require` para `suggest` |

## Fase 1 — federacao-portuguesa-futebol-backend (22/07)

**Pedido.** Era o último projeto em Laravel 12, mas com a framework na 14.0. A indicação foi subir
primeiro a framework até à 16 e depois seguir o playbook, vendo as breaking changes no próprio
repositório, porque não estavam documentadas.

**O que se fez**

1. Mapa das breaking changes por diff das branches `v14` → `v15` → `v16` → `v17`.
2. Fase v16: `json_response*` → macros `response()->api*` em 9 ficheiros, com `ResponseMacros::register()`
   no `AppServiceProvider`; `UnityHelper::stripTags` → `stripTagsForUnity()` com `use function` em 7
   ficheiros; novo namespace do `ComposerScripts`; addons do Backpack e Spatie declarados (incluindo
   `spatie/laravel-cookie-consent`, que o arranque provou ser necessário); binding do `UserCrudController`.
3. Fase v17 + Laravel 13, com o commit `fe5f935` do centro-acolhimento-visitante como modelo:
   migração `is_admin` com backfill, `User` com fillable e cast, `CheckIfAdmin` → `AdminPanelAccess`,
   `admin()` / `is('admin')` → `isAdmin()`, e `ckeditor`/`wysiwyg` → `summernote` em 15 controladores
   (cerca de 95 ocorrências).
4. Verificação: `about`, migrações, testes, `npm run prod`, `/admin/login` 200, pint nos ficheiros
   alterados e um script de runtime.

**Incidente 1 — login sem acesso ao admin.** Depois do deploy, o António entrava mas não via o painel,
com `is_admin = true` na base de dados. Causa: `'guard' => 'backpack'` no `base.php`; a v17 só verifica o
guard `web`. Correção de duas linhas (`'guard' => 'web'` e `@auth` no `welcome.blade.php`). O erro tinha
escapado porque a verificação usou `Auth::login()` em vez do login HTTP. A partir daqui, toda a
verificação passou a usar curl com cookies.

**Incidente 2 — `Call to undefined function admin()`.** O vendor em produção já estava certo; o erro
vinha de uma view compilada antiga em `storage/framework/views`. O Blade só recompila quando a fonte é
mais recente que o compilado, e os zips do Composer chegam com datas antigas. Resolveu-se no servidor
com `php artisan view:clear` e ficou a regra: `view:clear` no `post-install-cmd` de todos os projetos
(e do boilerplate).

**Pedidos seguintes no mesmo projeto**

- phpunit para a última versão, Livewire (não usado) para a 4, e **webpack → Vite**, com ESLint
  atualizado, tomando o boilerplate como referência, "e adiciona isto ao playbook". Ficou a receita da
  secção 11. O larastan mais recente acusou dois erros em código antigo, corrigidos.
- O build mostrava avisos do Sass. `@import` → `@use`, `quietDeps` para o aviso que vinha de dentro do
  pico, e a fonte do Google passou para `<link>`. O `admin.css` saiu igual byte a byte.

## Fase 2 — pixel-backend e pixel-rumos-backend (23/07)

**Pedido.** Dois projetos gémeos fora do boilerplate e sem a framework: migrar de Laravel 11 para 13 e
instalar a framework.

**O que se fez.** Laravel 13.21, tinker 3, phpunit 13, octane 2.18, Vite 7 → 8. A framework v17 foi
instalada de raiz com a receita da secção 14: macros, middleware `AdminAccess` para o alias `admin`, e
migração `is_admin` sem promover ninguém (os projetos têm o seu próprio sistema de roles). O
intervention/image ficou na v2 nesse dia e foi migrado para a v4 no lote de dependências.

**Pergunta do António: "Confirmaste se não havia breaking changes do Laravel 11 > 12 e 12 > 13?"**
A resposta honesta foi que só tinha havido validação empírica. Fez-se então a auditoria formal dos dois
guias oficiais contra o código, item a item: zero itens aplicáveis. A lista de greps passou a fazer
parte do processo (secção 10).

## Fase 3 — Lote Laravel 11 (23/07)

**Projetos**: adrimag-backoffice, fisgas-de-ermelo-backend, vacaria-backend, panike-backend,
museu-imigracao-backend, edia-backend. Todos do boilerplate, com a framework entre a 11 e a 13.

**O que se fez.** O pipeline foi posto em três scripts PowerShell (texto, estrutura, verificação), que se
perderam com a pasta antiga dos projetos; este manual substitui-os. Cada projeto levou: rename de
namespace (os quatro em framework 11, 20 a 36 ficheiros cada), `json_*` → macros, `UnityHelper`,
`AdminPanelAccess`, guard `web` (**os seis tinham o problema do FPF**), `is_admin`, summernote, binding
do UserCrud, mix → Vite com `@use`, e `view:clear`.

**Surpresas**

- `/admin/login` dava 500 em todos: chave `showPasswordVisibilityToggler` do theme-tabler 2.0.8.
- adrimag: o menu usava `route('page.edit')`, que vinha do pagemanager; faltava também o
  `nunomaduro/collision`.
- museu: `@use "@picocss/pico" as *` partia o build do Vite.
- O `AppServiceProvider` tinha três formas na frota (sem macros; com três macros; com três macros e
  `#[\Override]`); em todas acrescentou-se o `ResponseMacros` e o binding.

## Fase 4 — Lote Laravel 10 (23–24/07)

**Pedido.** Oito projetos em Laravel 10, com `git pull` antes, CIs atualizados, e phpstan e pint
corridos e corrigidos onde estivessem instalados.

**Fase A — framework 11** (neonia-backend, missao.portugalparaafrica.org,
centro-artes-criatividade-backoffice, qatar-backend): o pipeline do lote anterior, mais:

- `immera/analytics` removido em três (sem versão para Laravel 13 e sem uso).
- missao usa Bootstrap; centro-artes importa o sass da framework a partir de `vendor/`, com imports
  `~` (alias no Vite); o qatar ficou em pico v1 nesse dia.
- Sanctum 3 → 4, Livewire 4, `zanysoft/laravel-zip` 3.1, larastan 3.
- missao: `env('ACCESS_TOKEN')` passou para `config('services.forms.access_token')`.

**Fase B — framework 10, ou seja, Backpack 5** (psp-backend, melgaco-api, ivdp-vinhos-backoffice,
carrazeda-ansiaes-backoffice): a receita da secção 9.2 nasceu aqui, com o psp como referência. Configs
substituídos, menus convertidos à mão, dashboards portados, views antigas apagadas. No carrazeda, o
menu "Site texts" passou do langfilemanager para o translation-manager.

**Transversal**

- CIs: `actions/checkout@v3` → `@v4` e matriz `^13.0`.
- phpstan verde em todos, **incluindo um retrofit aos seis do lote anterior**: `backpack/pagemanager ^3.4`
  (o `App\Models\Page` estendia um modelo que já não estava instalado), seeders mortos removidos e os
  padrões da secção 13.

## Interlúdio — framework em projetos Filament (24/07)

Pedido para instalar a framework v17 em portaldasaude.immera.io, faculdade-direito-filament e
apram-backend. A meio, o António interrompeu: "vamos esquecer a framework nesses projectos para já".
Ficou tudo revertido; só ficaram os `git pull` do remoto.

## Fase 5 — Lote Laravel 9 (24/07)

**Projetos**: ci-atouguia-baleia-backoffice, museu-aveiro-backoffice, museu-mineiro-backoffice.
Laravel 9 → 13, framework 9 → v17, Backpack 5 → 7 (com o pro da v1 para a v3).

**O que se fez**

- Auditoria dos guias 9 → 10 e 10 → 11: o `$dates` estava comentado em todos os modelos; o resto sem ocorrências.
- atouguia: menu de 119 linhas convertido; `fruitcake/laravel-cors` → `HandleCors`; phpstan verde.
- aveiro: menu de 290 linhas, 78 editores → summernote, entrada extra do mix (`ledwall.scss`),
  **`App\User` → `App\Models\User`** com `Relation::morphMap`, uma migração de 2015 com `EnumHelper`, e
  pint aplicado ao projeto inteiro (o único do lote com pint).
- mineiro: sem views publicadas; menu mínimo criado.

**Incidentes pós-deploy**

1. **27/07 — deploy do mineiro falha** com `Declaration of App\Helpers\ComposerScripts::postInstall() must
   be compatible … : void`. O método só corre no `composer install`, e a verificação local usava
   `composer update`. Corrigido no aveiro e no mineiro, varrimento à frota, e nasceu a regra do
   `composer install` final.
2. **28/07 — edia: `Class "Backpack\PageManager\app\Models\Page" not found`.** A correção já existia no
   diff do retrofit (dia 24), ainda por commitar; produção estava com o deploy do dia 23. O mesmo valia
   para fisgas, vacaria e panike: recomendou-se o deploy dos quatro em conjunto.
3. **29/07 — aveiro: dois erros.** `No hint path defined for [backpack]` (o `elfinder.blade.php` publicado
   era de Backpack 5) e `Call to undefined function aurl()` (bug da framework na view `slides`). O
   primeiro foi corrigido também nos outros sete projetos vindos de Backpack 5; o segundo com um
   override na app.

## Fase 6 — Lote de dependências (29/07)

**Pedido.** Alguns projetos já atualizados tinham dependências desatualizadas; em cada um, fazer também
`composer update` e `npm update` de tudo, com atenção às breaking changes dos majors.

| Projeto | O que se fez |
|---|---|
| pixel-backend, pixel-rumos-backend | intervention/image 2.7 → 4.2 (um único método, `imageThumbnail`); provado em runtime: 900×600 → 300×200 e sem ampliar imagens pequenas |
| qatar-backend | pico v1 → v2, depois de auditar o uso (`.container` e `data-theme`) |
| commed-ia-backend e branch `show` (show.immera.io) | `openai-php/laravel` já estava na última (0.20). O pedido era afinal o package **npm** `openai`, 6.34 → 7.1 no `master`; descobriu-se que não é importado em lado nenhum |
| fep.immera.io | `qrcode-generator` (npm) 1.5.2 |
| obidos-backoffice, cards, api, mogadouro, kopke, holobox, ci-montado-backend | vite 8, eslint 10, laravel-vite-plugin 3, `npm update` |
| todos | `composer update`; ficaram em Laravel 13.23 |

O branch `show` foi tratado num worktree à parte, que acabou por ser removido a pedido.

## Fase 7 — imagaia.pt (29–30/09)

**Pedido.** "Tenho mais um site que ficou perdido no Laravel 11", com composer e npm atualizados.
A frota tinha mudado entretanto para `C:\Users\proma\projects`.

**O que se fez.** Laravel 11 → 13.34, Backpack 7.1.20 com pagemanager e settings (ambos usados),
framework v17, Livewire 4.4, phpunit 13, twilio 8, mix → Vite 8, e o pipeline habitual (`is_admin`, guard
`web`, macros, summernote, `view:clear`, CI). O `ComposerScripts` ainda apontava para o namespace da v14.

**Descobertas**

- Foi o primeiro projeto a apanhar **Laravel ≥ 13.3x**: o login entrava e era logo expulso. Causa e
  correção na secção 9.5. Na altura trocou-se pelo middleware nativo do Laravel; o Backpack corrigiu
  depois na 7.1.22.
- Livewire 4: `private ?Collection $slots` num componente dava fatal. Quem o denunciou foi o phpstan,
  porque as páginas testadas usavam outro componente.
- O repositório foi normalizado para LF por causa do pint: o `git status` mostrava cerca de 180
  ficheiros, mas o diff real eram 22.

## Fase 8 — paulomerlini.com (11–12/10)

**Pedido.** "Este projeto estava perdido, noutro servidor, e está no Laravel 8. Precisa de mega
atualização para o colocarmos em Laravel 13 […] garante que todas as dependências ficam atualizadas,
incluindo npm, e garante que tudo funciona como é suposto."

**Ponto de partida.** Laravel 8.83, framework 8.17, Backpack 4.1.71, intervention/image 2.7, phpunit 9.6,
laravel-mix 6. Site público em três línguas (páginas com cache `rememberForever`, projetos, imprensa,
pesquisa) e admin Backpack. Sem testes úteis, sem pint, phpstan ou CI.

**Passos, pela ordem em que se fizeram**

1. **Baseline do stack antigo** (§15.2): clone com o vendor antigo em PHP 8.3, MySQL isolado, dados
   semeados pelo admin antigo por HTTP, dumps guardados.
2. **Composer**: versões alvo; `facade/ignition` → `spatie/laravel-ignition`, `fzaninotto/faker` →
   `fakerphp/faker`; removidos `jeroen-g/laravel-packager` e `laracasts/generators`. Entraram o
   language-switcher (o admin antigo tinha seletor de idioma) e o translation-manager (substitui o
   langfilemanager). Não entraram activity-log, cookie-consent (o middleware estava comentado),
   socialite nem backups.
3. **Estrutura Laravel**: Kernel (`PreventRequestsDuringMaintenance`, `$middlewareAliases`,
   `SubstituteBindings`), `TrustProxies`, `Authenticate`, `App\User` → `App\Models\User` com `morphMap`,
   `config/app.php`, `config/mail.php` com os nomes antigos de variáveis como fallback, `public/index.php`,
   `phpunit.xml`, `.env.example`.
4. **Framework v17**: namespaces e macros; e as peças que a v17 já não tem ou tem partidas, refeitas na app:
   `App\Helpers\EnumHelper`, `App\Models\Traits\SaveMedia` (intervention v4), operação e campo
   **dropzone**, diretiva `@svg`, override do `PageCrudController::update()`. O `config/filesystems.php`
   perdeu o merge com o config da framework.
5. **Backpack 7**: configs do boilerplate adaptados (nome, logótipo, locales pt/en/fr, guard `web`), menu
   convertido, views do theme-tabler, `elfinder.blade.php` e `errors/layout.blade.php` novos, operação
   "arrange" reescrita, `enum` nativo, summernote, correção do campo de data, migrações `is_admin` e
   `language_lines`.
6. **Laravel**: `$dates` → `$casts`, `sluggable(): array`, entradas vazias do `en.json`.
7. **Frontend**: Vite 8 com alias `~` e `silenceDeprecations` (milligram), `@use`, fonte do Google na
   blade, imports do packery e draggabilly, `admin.scss` refeito para o tabler, e um `swipeable` local por
   causa do bug do cantil.
8. **Verificação** (resultados abaixo), `composer install` final e limpeza.

**Resultados da verificação**

| Prova | Resultado |
|---|---|
| Snapshots das páginas públicas, antigo vs novo | 30 de 36 idênticos; os 6 diferentes são as páginas 404, com o layout de erro novo |
| Estilos calculados | iguais em 4 páginas e 4 resoluções |
| Abrir e gravar sem mexer | base de dados intacta, salvo `""` → `null` e `[]` → `null` nas citações |
| Suite do admin | 58 testes sem falhas |
| Crawl das rotas GET do admin | 70 a responder; 3 respostas 403 por desenho |
| Browser com JS real | dropzone, cropper, summernote, "arrange" com drag e gravação, seletor de idioma, gestor de ficheiros |
| Reset de password por SMTP | 15 passos sem falhas, com os nomes antigos das variáveis `MAIL_*` |
| Editor de traduções | 12 passos sem falhas |
| `php artisan optimize`, PHPUnit, lint 8.3/8.4 | sem erros |

**Alterações fora do upgrade estrito** (assinaladas para poderem ser revertidas): os discos `uploads` e
`public` aceitam `APP_URL` com ou sem barra final; o service worker deixa de guardar em cache os assets
do admin; `cache:clear` no `post-install-cmd`.

**Ronda 2 — bugs antigos (12/10, madrugada).** A pedido, corrigiram-se os cinco bugs antigos listados no
relatório, com arranque agendado para as 03:35 por causa do limite de tokens da sessão:

1. `og:image` apontava para um ficheiro que não existe.
2. O campo "Tipo" dos projetos nunca gravava (`type` fora do `$fillable`).
3. Rotas de registo herdadas do laravel/ui, que não tinham views, desativadas.
4. Rota e método mortos da newsletter removidos (o formulário estava comentado e o package não instalado).
5. Botão "manutenção" do admin (anexo D).

Provado com 34 testes HTTP, também com `php artisan optimize` ativo.

**Ronda 3 — autenticação pública (12/10).** "Redireciona o /login para o admin e apaga o resto":
`Auth::routes()` deu lugar a `Route::redirect('/login', '/admin/login')->name('login')` (o middleware
`Authenticate` usa `route('login')`), os seis controladores de `app/Http/Controllers/Auth` foram apagados
e o `laravel/ui` saiu do Composer por ter ficado sem uso. O login, o logout e a recuperação de password
do admin são do Backpack e não foram tocados.

**Notas de deploy deste projeto**: copiar do servidor antigo `public/uploads/` e o vídeo da homepage, que
não estão no git (as páginas de projeto dão 500 se faltar uma imagem da galeria); as traduções editadas
no langfilemanager antigo estão nos ficheiros `lang/` desse servidor.

## Estado por projeto (12/10/2026)

| Projeto | Fase | Ponto de partida | Notas |
|---|---|---|---|
| federacao-portuguesa-futebol-backend | 1 | L12, fw 14.0 | primeiro em Vite; guard e `view:clear` descobertos aqui |
| pixel-backend, pixel-rumos-backend | 2, 6 | L11, sem framework | framework instalada de raiz; intervention 4 |
| fisgas-de-ermelo-backend, vacaria-backend, panike-backend, adrimag-backoffice | 3 | L11, fw 11 | rename de namespace; retrofit do pagemanager |
| edia-backend | 3 | L11, fw 12 | retrofit do pagemanager |
| museu-imigracao-backend | 3 | L11, fw 13 | |
| neonia-backend, missao.portugalparaafrica.org, centro-artes-criatividade-backoffice, qatar-backend | 4 | L10, fw 11 | `immera/analytics` removido em três; qatar em pico v2 |
| psp-backend, melgaco-api, ivdp-vinhos-backoffice, carrazeda-ansiaes-backoffice | 4 | L10, fw 10, Backpack 5 | psp é a referência de Backpack 5 → 7 |
| ci-atouguia-baleia-backoffice, museu-aveiro-backoffice, museu-mineiro-backoffice | 5 | L9, fw 9, Backpack 5 | aveiro: `App\User` movido, override da view `slides` |
| commed-ia-backend (+ branch `show`), cards, api, kopke, mogadouro, holobox | 0, 6 | L12 | feitos no handoff; dependências atualizadas no lote 6 (L13.23) |
| obidos-backoffice, ci-montado-backend, fep.immera.io | 6 | já em L13 | só dependências (vite 8, eslint 10, `qrcode-generator`) |
| centro-acolhimento-visitante, alandroal-backend, azap, profitecla, onecore-laravel, admin.immera.io, doritos-backend | 0 | — | feitos no handoff; não voltaram a ser tocados |
| imagaia.pt | 7 | L11 | L13.34; `AuthenticateSession` nativo |
| paulomerlini.com | 8 | L8, fw 8.17, Backpack 4.1 | L13.35, crud 7.1.22; entregue por commitar a 12/10 |
| portaldasaude.immera.io, faculdade-direito-filament, apram-backend | — | Filament | instalação da framework cancelada |

---

# Anexos

## Anexo A — Migração `is_admin` com backfill

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_admin')->default(false)->after('password');
    });

    // Backfill: promote existing users who hold the Spatie "admin" role,
    // since the admin gate moved from the role to the is_admin attribute.
    $rolesTable = config('permission.table_names.roles', 'roles');
    $pivotTable = config('permission.table_names.model_has_roles', 'model_has_roles');
    $morphKey = config('permission.column_names.model_morph_key', 'model_id');

    if (Schema::hasTable($rolesTable) && Schema::hasTable($pivotTable)) {
        $adminRoleIds = DB::table($rolesTable)->where('name', 'admin')->pluck('id');

        // the roles may have been stored for App\User, before the model moved to App\Models
        $adminUserIds = DB::table($pivotTable)
            ->whereIn('role_id', $adminRoleIds)
            ->whereIn('model_type', array_unique(['App\User', User::class, (new User)->getMorphClass()]))
            ->pluck($morphKey);

        if ($adminUserIds->isNotEmpty()) {
            DB::table('users')->whereIn('id', $adminUserIds)->update(['is_admin' => true]);
        }
    }
}
```

No `User`: `'is_admin'` no `$fillable` e `'is_admin' => 'boolean'` no `$casts`.

## Anexo B — Contornos na app para bugs da framework

**Cache das páginas** (`App\Http\Controllers\Admin\PageCrudController`):

```php
public function update(): JsonResponse|RedirectResponse
{
    $result = parent::update();

    // The pages are cached forever, one entry per locale. The parent only forgets the "locale"
    // of the request, a field Backpack renamed to "_locale", so forget them all.
    foreach (array_keys(config('backpack.crud.locales')) as $locale) {
        Cache::forget('page_'.request()->input('slug').'_'.$locale);
    }

    return $result;
}
```

**`SaveMedia` com intervention/image 4** (núcleo do `saveImage()`):

```php
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

if (Str::startsWith((string) $value, 'data:image')) {
    $format = Str::between($value, 'data:image/', ';');
    $format = $format === 'jpeg' ? 'jpg' : $format;
    $filename = Str::slug($name.' '.Carbon::now()->timestamp).'.'.$format;

    $image = ImageManager::usingDriver(Driver::class)->decodeDataUri($value);
    Storage::disk($disk)->put($path.$filename, (string) $image->encodeUsingFileExtension($format, quality: $quality));

    rsort($sizes); // do maior para o menor: cada tamanho é reduzido a partir do anterior
    foreach ($sizes as $size) {
        $image = $image->scaleDown(width: $size);
        Storage::disk($disk)->put("$path/$size/$filename", (string) $image->encodeUsingFileExtension($format, quality: $quality));
    }
}
```

**View `slides`**: copiar para `resources/views/vendor/gemadigital/components/slides.blade.php` e trocar
`aurl('uploads', $slide->image)` por `\GemaDigital\aurl($slide->image, 'uploads')`.

## Anexo C — Menu do Backpack 7

`resources/views/vendor/backpack/ui/inc/menu_items.blade.php`:

```blade
<x-backpack::menu-item :title="trans('backpack::base.dashboard')" icon="la la-dashboard" :link="backpack_url('dashboard')" />

<x-backpack::menu-separator :title="__('Content')" />
<x-backpack::menu-item :title="ucfirst(__('projects'))" icon="la la-book" :link="backpack_url('project')" />

<x-backpack::menu-dropdown :title="ucfirst(__('press'))" icon="la la-newspaper">
    <x-backpack::menu-dropdown-item :title="__('Video')" icon="la la-youtube" :link="backpack_url('press').'?type=video'" />
</x-backpack::menu-dropdown>

{{-- entradas de administração da framework (utilizadores, ficheiros, traduções, logs…) --}}
@include('gemadigital::vendor.backpack.base.inc.sidebar_content_admin')
```

As entradas de administração ligam-se e desligam-se em `config/gemadigital.php` (`sidebar`).

## Anexo D — Modo de manutenção a funcionar pelo admin

`app/Http/Controllers/Admin/MaintenanceController.php`:

```php
class MaintenanceController extends \GemaDigital\Http\Controllers\Admin\MaintenanceController
{
    public function down(Request $request): Response
    {
        $secret = Str::random(32);

        $status = ! Artisan::call('down', ['--secret' => $secret]);

        return response()->apiStatus($status)
            ->withCookie(MaintenanceModeBypassCookie::create($secret));
    }
}
```

`routes/web.php` (tem de ser aqui: é o último ficheiro de rotas a carregar, por isso substitui a rota da
framework; em `routes/backpack/custom.php` seria a da framework a ganhar):

```php
Route::post(config('gemadigital.routes.prefix', 'admin') . '/maintenance/down', [MaintenanceController::class, 'down'])
    ->middleware(config('gemadigital.routes.middleware', 'admin'));
```

`app/Http/Middleware/EncryptCookies.php`: `'laravel_maintenance'` no `$except`. O cookie é lido pela
verificação de manutenção antes de os cookies serem decifrados; se sair cifrado, o próprio admin fica de fora.

`storage/framework/.gitignore`: acrescentar `maintenance.php`.

Comportamento: o admin que clicou continua a ver o site e o backoffice durante 12 horas; os restantes
veem 503. Se perder o cookie, o site só volta com `php artisan up` no servidor.
