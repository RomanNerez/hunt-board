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
- **Filament:** the panel is configured in `app/Ship/Providers/AdminPanelProvider.php` (id `admin`, path `/admin`). It auto-discovers Resources, Pages and Widgets from `app/Filament/{Resources,Pages,Widgets}`, which don't exist yet. The container `User` model implements `FilamentUser`, and `canAccessPanel()` currently returns `true` for everyone. Unauthenticated users are redirected to the Filament login (`bootstrap/app.php`).
- **App-Identifier:** the global `ValidateAppId` middleware reads the `App-Identifier` header (defaulting to `apiato.defaults.app`) and rejects values that aren't in `apiato.apps`. Only `web` is defined. `AppFactory::current()` resolves the matching `App\Ship\Apps\*` class.
- **Strict models:** `Model::shouldBeStrict()` is on outside production, so lazy loading and missing or unfillable attributes throw. Dates use `CarbonImmutable`.

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

Step-by-step instructions for each stage are packaged as skills (`openspec-explore`, `openspec-propose`, `openspec-apply-change`, `openspec-update-change`, `openspec-sync-specs`, `openspec-archive-change`). There are two copies, `.claude/skills/` for Claude Code and `.agents/skills/` for other agents, and the content is the same. If your agent doesn't load skills automatically, read the matching `SKILL.md` before running that stage. Regenerate both copies with `openspec update` rather than editing them by hand.
