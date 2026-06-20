# Praxis

## Security 🔒

**NEVER read/parse:** `.env*`, `*credentials*`, `*secrets*`, `*token*`, `*.key`, `*.pem`, `*.crt`, `*.p12`, `*.pfx`, `.npmrc`, `pypirc`, `*.keystore`, `.aws/*`, `.azure/*`, `gcloud-service-key.json`. Warn user if requested, get confirmation. See `@reference/standards/security-patterns.md` for full list.

## Communication Principles

- **Ask clarification questions** when needed or before starting big tasks
- **Never hallucinate or assume information** without supporting facts
- **When you don't know, say so** — honesty over speculation
- Begin your response with my name : Yassir.

## Git Commits

**Conventional Commits:** `type(scope): description`
- Scope: project folder name
- Types: feat|fix|docs|refactor|test|chore|perf|build|ci
- Deliverables (analysis/specs/research) = `feat`, not `docs`
- NEVER add Claude/Anthropic attribution or co-author tags

**Pre-commit (OVERRIDE: NEVER auto-commit):**
1. Show `git status` + `git diff --staged`
2. List files + proposed message
3. **WAIT for user confirmation**

## Rules

- `**/.in/`: initial context for project bootstrap

## Alias Paths

Shortcuts: gtd=gtd-pcm, agt=agent-skills

- **gtd-pcm** (gtd): control `/praxis/repos/gtd-pcm` | data `/repos/gtd-pcm`
- **agent-skills** (agt): control `/praxis` | data `/repos/agent-skills`

(Control: instructions, OpenSpec, context. Data: project files in separate git repo. Used by `/commit-repo` to resolve repo aliases.)

## Planning

- **Boulder → Pebbles**: Break complex features into trackable stories
- Ask user upfront if OpenSpec should be used for the task
- Use OpenSpec for boulders (multi-step, architectural, ambiguous scope)
- Skip OpenSpec for pebbles (single file, obvious implementation)

## Style

**Human output (responses, docs, explanations):**
- **Visual-first:** Mermaid diagrams by default for technical (e.g., architecture/flows/data) AND non-technical (e.g., decisions/processes/comparisons). Colorize with classDef/fill/stroke. **ALWAYS use black text** (`color:#000`) for nodes with colored backgrounds.
- **Structured data:** YAML/JSON with emoji (✅❌🎯⚠️🚀)
- **OVERRIDE: Use emojis liberally** ✅❌🎯🚀🔍💾🧪📦⚠️✨🎨🔧

**LLM context (`*-llm.md`, specs, agent prompts):**
- **Token-optimized:** All `-llm.md` files must be optimized for tokens. Strip prose, keep directives/facts/structure only.
- **Visual structure:** Mermaid diagrams for complex flows/hierarchies/relationships. More efficient than prose explanations.
- **Structured data:** Clean YAML/JSON without emoji. Optimized for parsing.
- **Markdown:** Semantic headings, bullets, code blocks. No decorative formatting.

## Mermaid

- Wrap labels in quotes: `["Label"]`
- Multi-target connections: use `&` operator: `A -->|label| B & C & D`
- Define nodes inside subgraphs before referencing them
- **Black text mandatory:** ALL classDef with colored fills MUST include `color:#000` for readability
  - Example: `classDef success fill:#90EE90,stroke:#333,color:#000`

# Instructions: ALWAYS use `rtk` instead of standard Unix commands

`rtk` compresses command output to save 70-90% tokens. **MANDATORY** for all Bash tool calls.

**Chaining:** Always prefix each command: `rtk git add && rtk git commit -m "msg" && rtk git push`

## Substitution table — ALWAYS use right column

| Instead of | Use |
|---|---|
| `ls`, `tree` | `rtk ls <path>` |
| `cat`, `head`, `tail` | `rtk read <file>` |
| `cat` (code understanding) | `rtk read <file> -l aggressive` |
| `find`, `fd` | `rtk find <pattern>` |
| `git status` | `rtk git status` |
| `git log` | `rtk git log` |
| `git diff` | `rtk git diff` |
| `git add` | `rtk git add` |
| `git commit -m "msg"` | `rtk git commit -m "msg"` |
| `git push` | `rtk git push` |
| `git pull` | `rtk git pull` |
| `grep -rn`, `rg` | `rtk grep <pattern>` |
| `pytest`, `npm test`, etc. | `rtk test <cmd>` |
| `<cmd> 2>&1 \| grep error` | `rtk err <cmd>` |
| `cat file.log` | `rtk log <file>` |
| `cat *.json` | `rtk json <file>` |
| `env`, `printenv` | `rtk env` |
| `docker ps/images/logs` | `rtk docker ps/images/logs <c>` |
| `kubectl get pods/logs` | `rtk kubectl pods/logs <pod>` |
| any long command | `rtk summary <cmd>` |

**Additional commands:** `rtk smart <file>` (2-line summary), `rtk deps` (dependency summary), `rtk kubectl services`

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/reverb (REVERB) - v1
- laravel/wayfinder (WAYFINDER) - v0
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
