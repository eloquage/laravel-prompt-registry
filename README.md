<p align="center"><img src="art/banner.png" alt="Laravel Prompt Registry" width="100%"></p>

# Laravel Prompt Registry

[![Tests](https://github.com/eloquage/laravel-prompt-registry/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/laravel-prompt-registry/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/eloquage/laravel-prompt-registry/actions/workflows/phpstan.yml/badge.svg)](https://github.com/eloquage/laravel-prompt-registry/actions/workflows/phpstan.yml)
[![Latest Version](https://img.shields.io/packagist/v/illuma-law/laravel-prompt-registry.svg)](https://packagist.org/packages/illuma-law/laravel-prompt-registry)
[![License](https://img.shields.io/packagist/l/illuma-law/laravel-prompt-registry.svg)](LICENSE)

A Laravel package for registering AI agent metadata, resolving prompt content, and selecting a default `standard` or `extended` model tier.

## Requirements

- PHP 8.3+
- Laravel 11, 12, or 13

## Install

```bash
composer require illuma-law/laravel-prompt-registry
php artisan vendor:publish --tag=laravel-prompt-registry-config
```

Laravel discovers the service provider automatically. The publish command is optional if you only register definitions in a service provider.

## Register prompts

Add definitions to `config/prompt-registry.php`:

```php
return [
    'prompts' => [
        'agents.summary' => [
            'agent' => App\Ai\Agents\SummaryAgent::class,
            'name' => 'Summary agent',
            'description' => 'Summarizes a document.',
            'fallback_view' => 'prompts.agents.summary',
            'default_primary_tier' => 'standard', // Optional: standard or extended.
        ],
    ],
];
```

Create the fallback prompt view at `resources/views/prompts/agents/summary.blade.php`. You can also register definitions at runtime:

```php
use IllumaLaw\PromptRegistry\Facades\PromptRegistry;

PromptRegistry::register('agents.summary', [
    'agent' => App\Ai\Agents\SummaryAgent::class,
    'name' => 'Summary agent',
    'description' => 'Summarizes a document.',
    'fallback_view' => 'prompts.agents.summary',
]);
```

## Look up and resolve prompts

```php
use IllumaLaw\PromptRegistry\Facades\PromptRegistry;
use IllumaLaw\PromptRegistry\PromptBodyResolver;

$definition = PromptRegistry::forKey('agents.summary');
$byAgent = PromptRegistry::forAgent(App\Ai\Agents\SummaryAgent::class);
$tier = PromptRegistry::defaultPrimaryTierForAgent(App\Ai\Agents\SummaryAgent::class);

// Stored content wins; otherwise the registered fallback Blade view is rendered.
$body = app(PromptBodyResolver::class)->resolveForAgent(App\Ai\Agents\SummaryAgent::class);
```

The manager also provides `all()` and `definitionsByKey()`. Unknown keys or agents raise `InvalidArgumentException`.

## Optional prompt persistence

To store editable prompt overrides, set `persistence.model` in the published config to an Eloquent model with `key` and `content` columns. The package does not create a migration for this application-owned table.

```php
use IllumaLaw\PromptRegistry\PromptPersistenceManager;

$prompts = app(PromptPersistenceManager::class);
$prompts->persistByKey('agents.summary', 'Summarize the supplied text in three bullets.');
$body = app(PromptBodyResolver::class)->resolveForAgent(App\Ai\Agents\SummaryAgent::class);
$prompts->revertToFallbackByKey('agents.summary');
```

## Development checks

```bash
composer test
composer analyse
composer format -- --test
```

The current test suite reports 57.4% line coverage; it does not provide complete coverage of every branch or production database configuration.

## License

MIT. See [LICENSE](LICENSE).
