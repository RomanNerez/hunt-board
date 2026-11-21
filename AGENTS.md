# AGENTS.md

Guidance for AI coding agents (Claude Code, Codex, Cursor, Gemini, etc.) working in this repository. `CLAUDE.md` imports this file, so keep all shared instructions here.

## Project

Hunt Board is built on **Apiato 13** (Laravel, PHP ^8.2) using the **Porto SAP** architecture, with a **Filament 5** admin panel as the main UI. The stock Apiato API layer (Passport/OAuth, API routes, roles/permissions, web login) has been removed. Authentication now goes through Filament's login at `/admin`. The README is the stock Apiato README and doesn't describe this project.

## Environment (Docker)

The stack is defined in `docker-compose.yml`: `app` (PHP-FPM, Vite on 5173), `nginx` (http://localhost:8000), `db` (Postgres 16, host port 5433, credentials in `.env.db`), `redis`, and `supervisor`, which runs 4 `queue:work database` workers plus `schedule:work`. `docker-compose.local.yml` adds MailHog (UI on 8025). `.env` points at `DB_HOST=db`, so artisan commands that touch the DB have to run inside the container:

```bash
docker compose up -d                                   # add -f docker-compose.yml -f docker-compose.local.yml for mailhog
docker compose exec app php artisan migrate
docker compose exec app php artisan create:admin       # interactive; creates a verified user who can log into /admin
docker compose exec app npm run dev                    # or npm run build
```

`docker-compose.testing.yml` starts an isolated `app.testing` with a tmpfs Postgres (`db.testing`). It mounts `.env.testing`, which doesn't exist yet and has to be created before that stack will work.

## Commands

```bash
vendor/bin/phpunit                                     # all suites: Unit, Functional, Ship-Unit
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --filter CreateAdminActionTest      # single test class/method
vendor/bin/paratest                                    # parallel
vendor/bin/phpstan analyse                             # level max, strict + safe rules, larastan
vendor/bin/psalm
composer fixer                                         # php-cs-fixer (@Symfony + custom rules) on app/
composer ide-helper
```

## Architecture (Porto)

- **`app/Ship/`** is the shared framework layer. `Ship/Parents/*` holds the base classes that every container class must extend (`App\Ship\Parents\Actions\Action`, `...\Tasks\Task`, `...\Models\Model`/`UserModel`, `...\Controllers`, `...\Requests`, `...\Tests\TestCase`, etc.). It also holds Ship-wide providers, configs (`Ship/Configs/apiato.php`), migrations, middleware and helpers.
- **`app/Containers/<Section>/<Container>/`** holds the business logic. Custom code lives in `AppSection` (`User`, `Authentication`). **Never edit `app/Containers/Vendor/*`**: Composer overwrites it. Copy a container into a custom section and rename its namespace if you need to change it.
- **Request flow:** UI (Controller / CLI Command) → **Action** (one use case, orchestrates and wraps work in transactions) → **Tasks** (single reusable operations, e.g. `CreateUserTask`) → Repository/Model. Controllers call Actions only. Tasks shouldn't call other Tasks or Actions.
- **Container layout:** `Actions/`, `Tasks/`, `Models/`, `Data/{Migrations,Repositories,Factories,Collections}`, `UI/{WEB,CLI}/{Controllers,Routes,Requests,Views,Commands}`, `Providers/`, `Configs/`, `Tests/{Unit,Functional}`. Each container has its own `composer.json`, which gets merged into the root via `wikimedia/composer-merge-plugin`. Put a container's own dependencies there.
- **Auto-discovery:** `bootstrap/app.php` uses `Apiato::configure()` to load providers, events, commands, configs, migrations and web routes (`UI/WEB/Routes/*.php`, one route per file) from containers and Ship. You don't need to register them in `config/` or `routes/`, which is empty. Container config files are named `appSection-<container>.php`.
- **Filament:** the panel is configured in `app/Ship/Providers/AdminPanelProvider.php` (id `admin`, path `/admin`). Filament code lives inside containers. See "Filament inside containers" below. The container `User` model implements `FilamentUser`, and `canAccessPanel()` currently returns `true` for everyone. Unauthenticated users are redirected to the Filament login (`bootstrap/app.php`).
- **App-Identifier:** the global `ValidateAppId` middleware reads the `App-Identifier` header (defaulting to `apiato.defaults.app`) and rejects values that aren't in `apiato.apps`. Only `web` is defined. `AppFactory::current()` resolves the matching `App\Ship\Apps\*` class.
- **Strict models:** `Model::shouldBeStrict()` is on outside production, so lazy loading and missing or unfillable attributes throw. Dates use `CarbonImmutable`.

## Code style: no comments, tag-only PHPDoc

- **Don't write comments in code**: no `//`, `#`, `/* */`, and no explanatory text in docblocks (no summary or description lines). Make code self-explanatory through names and small methods. This applies to config files, migrations and tests too.
- **Every method and every property of a class gets a PHPDoc block that contains only tags**, even when the native type already says the same thing:
  - methods: `@param` for each parameter (in signature order), `@return` (including `@return void`), and `@throws` for exceptions the method throws;
  - properties: `@var`;
  - constructor-promoted properties: `@param` in the constructor's docblock;
  - use precise types: `array<string, mixed>`, `list<User>`, `class-string<Model>`, `Collection<int, User>`.

```php
/**
 * @var list<string>
 */
private array $names = [];

/**
 * @param int $id
 * @param array<string, mixed> $data
 *
 * @return User
 *
 * @throws NotFound
 */
public function run(int $id, array $data): User
```

- An inline `/** @var Type $x */` before an assignment is allowed only to narrow a type for PHPStan.
- Apply this to code you write or change. Don't rewrite untouched stock Apiato code only to reformat its docblocks. Generated files (Filament/Artisan stubs) count as code you write, so strip their comments and add the tags.
- `.php-cs-fixer.dist.php` disables `no_superfluous_phpdoc_tags`, so `composer fixer` keeps these tags. Don't re-enable it.

## Filament inside containers

Filament is a UI layer of a container, alongside `UI/WEB` and `UI/CLI`. **Never create anything under `app/Filament/`**, because the panel doesn't discover it. A resource belongs to the container that owns its model:

```
app/Containers/<Section>/<Container>/
├── Actions/                         # all writes go through these
├── Policies/<Model>Policy.php       # Filament authorization
├── UI/Filament/
│   ├── Clusters/<Name>/<Name>Cluster.php   # only clusters used by this container alone
│   ├── Resources/<Models>/          # plural folder, e.g. Users/
│   │   ├── <Model>Resource.php
│   │   ├── Pages/                   # List<Models>, Create<Model>, Edit<Model>, View<Model>; or Manage<Models> for a simple resource
│   │   ├── Schemas/                 # <Model>Form.php, <Model>Infolist.php
│   │   ├── Tables/                  # <Models>Table.php
│   │   └── RelationManagers/
│   ├── Pages/                       # standalone panel pages
│   └── Widgets/
└── Tests/Functional/Filament/       # Livewire::test(...) tests, PHPUnit
```

**Discovery:** `AdminPanelProvider::discoverContainerComponents()` globs `app/Containers/*/*/UI/Filament` and registers its `Clusters`, `Resources`, `Pages` and `Widgets` with namespace `App\Containers\<Section>\<Container>\UI\Filament\...`. It also registers shared clusters from `app/Ship/Filament/Clusters`. A new container needs no provider change.

**Clusters** group resources and pages under one navigation entry and URL prefix (`/admin/<cluster>/<resource>`):

- A cluster is only a navigation container, so it never holds resources or pages. **Resources and pages stay in their owning container's `Resources/` or `Pages/`** and join a cluster through `protected static ?string $cluster = <Name>Cluster::class;`.
- Where the cluster class goes:
  - used by resources from several containers (e.g. Settings): `app/Ship/Filament/Clusters/<Name>/<Name>Cluster.php`, namespace `App\Ship\Filament\Clusters\<Name>`;
  - used by one container only: `UI/Filament/Clusters/<Name>/<Name>Cluster.php` in that container.
- Write the cluster class by hand: `final class <Name>Cluster extends \Filament\Clusters\Cluster` with `$navigationIcon` (`string | BackedEnum | null`, e.g. `Heroicon::OutlinedCog6Tooth`) and optionally `$navigationSort` / `$navigationLabel`. `make:filament-cluster` has no namespace option and prompts when several cluster directories are registered.
- **Don't pass `--cluster` to `make:filament-resource` / `make:filament-page`.** It generates the files inside the cluster directory. Generate into the container as usual, then add the `$cluster` property.

**Generating:** use the Filament generators with explicit namespaces. **Create the target directory first**: if `UI/Filament` doesn't exist yet, its namespace isn't registered and the generator silently falls back to `app/Filament`. Check where the files landed afterwards.

```bash
mkdir -p app/Containers/AppSection/User/UI/Filament/Resources
docker compose exec app php artisan make:filament-resource User \
  --model-namespace="App\Containers\AppSection\User\Models" \
  --resource-namespace="App\Containers\AppSection\User\UI\Filament\Resources" \
  --panel=admin --no-interaction    # add --generate to scaffold fields from DB columns, --view for a view page
```

**Pages or modals:** both are valid, and the choice depends on the task. A resource can have separate `Create<Model>` / `Edit<Model>` pages, or be a simple resource (`--simple`) that creates and edits in modals on one `Manage<Models>` page. Neither is the default. Pick per resource, record the choice in the change's `design.md`, and ask the user when the task doesn't make it clear.

**Writes go through container Actions, not Filament's built-in Eloquent persistence.** That keeps business rules (transactions, events, hashing, notifications) in one place for panel, CLI and future UIs:

- `Create<Model>` page: override `handleRecordCreation(array $data): Model` and return `app(Create<Model>Action::class)->run($data)`.
- `Edit<Model>` page: override `handleRecordUpdate(Model $record, array $data): Model` and return `app(Update<Model>Action::class)->run($record->getKey(), $data)`.
- Modal resource (`Manage<Models>` page, `ManageRecords`): route writes through `->using()`: `CreateAction::make()->using(fn (array $data) => app(Create<Model>Action::class)->run($data))` in the page's `getHeaderActions()`, and `EditAction::make()->using(fn (<Model> $record, array $data) => app(Update<Model>Action::class)->run($record->getKey(), $data))` in the table's `recordActions()`. To give `$data` the type `array<string, mixed>` for PHPStan, assign it to a new variable under an inline `/** @var array<string, mixed> $attributes */`. PHPStan ignores a PHPDoc on the closure, and `composer fixer` turns an `@var` placed right before `return` into a plain comment.
- Delete, bulk delete and custom actions: `DeleteAction::make()->using(fn (<Model> $record) => app(Delete<Model>Action::class)->run($record->getKey()))`. Custom actions call an Action in `->action(...)`.
- If the needed Action doesn't exist, create it in the container's `Actions/`, backed by Tasks. Don't put business logic in Filament classes.
- Reads are fine through Eloquent in Filament (table query, filters, search, `getEloquentQuery()`). Don't route them through API-style list Actions that use request criteria and pagination (e.g. `ListUsersAction`).

**Authorization:** add `<Model>Policy` in the container's `Policies/`. Filament checks `viewAny`, `view`, `create`, `update`, `delete`, etc. automatically. Treat policies as the real access control, because `canAccessPanel()` lets every user in.

## Tests

Tests live inside each container (`Tests/Unit`, `Tests/Functional`) and extend that container's `UnitTestCase` / `FunctionalTestCase`, which build on `App\Ship\Parents\Tests\TestCase`. `phpunit.xml` forces array cache/session/mail and the sync queue, but the DB comes from the environment (the sqlite override is commented out).

**This project uses PHPUnit, not Pest.** Pest isn't installed, so never add it, write `test()`/`it()` closures, or `use function Pest\...`. Write class-based PHPUnit tests (`final class XTest extends UnitTestCase`, methods prefixed `test` or marked `#[Test]`). When a skill or doc gives a Pest example, translate it:

- `livewire(ListUsers::class)` becomes `Livewire::test(ListUsers::class)` (`use Livewire\Livewire;`). The Filament/Livewire test helpers chained after it (`assertCanSeeTableRecords`, `searchTable`, `fillForm`, `call('create')`, `assertHasNoFormErrors`, `callAction`, etc.) stay the same.
- `expect($x)->toBe($y)` becomes `$this->assertSame($y, $x)`. `beforeEach` becomes `setUp()`, and datasets become `#[DataProvider]`.
- Filament panel tests need an authenticated user first: `$this->actingAs(User::factory()->createOne())`.

## Spec-driven workflow (OpenSpec)

This repo uses [OpenSpec](https://github.com/Fission-AI/OpenSpec) (`schema: spec-driven`, CLI `openspec` 2.x). Non-trivial features and behaviour changes should go through a change proposal before you write code.

- `openspec/specs/<capability>/` holds the current source of truth: what the system does now.
- `openspec/changes/<change-name>/` holds in-flight changes: `proposal.md`, `design.md`, `tasks.md`, and delta specs under `specs/`. Finished changes move to `openspec/changes/archive/`.
- `openspec/config.yaml` holds project-wide context and per-artifact rules for OpenSpec. Put constraints here that agents can't infer from the code.

Lifecycle: **explore → propose → apply → (sync) → archive**.

1. **Propose:** create the change folder and all its artifacts, then run `openspec validate <change> --strict`.
2. **Apply:** implement `tasks.md` in order and tick each box (`- [x]`) as you finish it. Keep code in line with the delta specs. If the plan changes, update the artifacts first.
3. **Archive:** once every task is done and the tests pass, run `openspec archive <change>`. This merges the delta specs into `openspec/specs/`.

Useful CLI commands: `openspec list` (`--specs`), `openspec show <item>`, `openspec validate <item> --strict`, `openspec context`, `openspec archive <change>`.

Step-by-step instructions for each stage are packaged as skills (`openspec-explore`, `openspec-propose`, `openspec-apply-change`, `openspec-update-change`, `openspec-sync-specs`, `openspec-archive-change`). If your agent doesn't load skills automatically, read the matching `.agents/skills/<name>/SKILL.md` before running that stage. Regenerate them with `openspec update` rather than editing them by hand.

## Agent skills

All skills live in **`.agents/skills/`**, which Codex, OpenCode, Amp and others read directly. **`.claude/skills/<name>` is only a relative symlink** (`../../.agents/skills/<name>`) for Claude Code, so never create a real directory or file there.

- Generated skills come from OpenSpec (`openspec-*`, regenerate with `openspec update`) and from Laravel Boost (`laravel-best-practices`, `testing-best-practices`, `tailwindcss-development`, `filament-development`, `infer-conventions`). The Boost list is in `boost.json` under `skills`. Third-party package skills also need the package listed under `packages`. `config/boost.php` points Boost's `claude_code` skills path at `.agents/skills`.
- Don't edit generated skills, because the next update overwrites them. Put project-specific rules in this file instead.
- To update Boost skills and guidelines, run `docker compose exec app composer ai:update`. It runs `boost:update` and then `ai:link`, which adds missing `.claude/skills` symlinks and removes dangling ones. After adding a skill to `.agents/skills` any other way, run `composer ai:link`.
- `boost:update` also rewrites the `<laravel-boost-guidelines>` block in `CLAUDE.md`. Don't edit that block by hand.
