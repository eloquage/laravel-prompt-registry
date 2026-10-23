# Laravel Prompt Registry

[![Run Tests](https://github.com/eloquage/laravel-prompt-registry/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/laravel-prompt-registry/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/eloquage/laravel-prompt-registry/actions/workflows/phpstan.yml/badge.svg)](https://github.com/eloquage/laravel-prompt-registry/actions/workflows/phpstan.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/illuma-law/laravel-prompt-registry.svg?style=flat-square)](https://packagist.org/packages/illuma-law/laravel-prompt-registry)
[![Total Downloads](https://img.shields.io/packagist/dt/illuma-law/laravel-prompt-registry.svg?style=flat-square)](https://packagist.org/packages/illuma-law/laravel-prompt-registry)
[![License](https://img.shields.io/packagist/l/illuma-law/laravel-prompt-registry.svg?style=flat-square)](https://packagist.org/packages/illuma-law/laravel-prompt-registry)

A lightweight, dynamic registry for AI agent prompt definitions in Laravel.

This package provides a centralized system to register and look up AI prompt metadata across your Laravel application. Instead of hardcoding AI agent configurations, you can register an agent's associated class, name, description, fallback Blade views, and LLM tiers (Standard vs Extended) either via configuration or programmatically. 

## Features

- **Centralized Configuration:** Define all your application's AI prompts in a single config file.
- **Dynamic Registration:** Register prompts on the fly from Service Providers or external packages.
- **Bidirectional Lookup:** Fetch prompt definitions by their string key, or look up the configuration using the Agent's class name.
- **Tier Routing:** Built-in support for defining whether an agent requires an 'extended' (smarter/larger) model tier or can operate on a 'standard' tier by default.
- **Dependency Injection & Facade:** Use the `PromptRegistry` facade for quick access, or inject `PromptRegistryManager` for robust testing.

## Installation

Install the package via Composer:

```bash
composer require illuma-law/laravel-prompt-registry
```

Publish the config file:

```bash
php artisan vendor:publish --tag="laravel-prompt-registry-config"
```

## Configuration

The published `config/prompt-registry.php` holds an array where each key is a dot-notation identifier for the prompt definition:

```php
return [
    'prompts' => [
        'agents.content_creator' => [
            'agent'                => \App\Ai\Agents\ContentCreatorAgent::class,
            'name'                 => 'Content Creator Agent',
            'description'          => 'Generates marketing social content.',
            'fallback_view'        => 'prompts.agents.content_creator',
            'default_primary_tier' => 'standard', // Optional: 'standard' | 'extended'
        ],
        'agents.legal_analyst' => [
            'agent'                => \App\Ai\Agents\LegalAnalystAgent::class,
            'name'                 => 'Legal Analyst',
            'description'          => 'Analyzes complex legal documents.',
            'fallback_view'        => 'prompts.agents.legal_analyst',
            'default_primary_tier' => 'extended',
        ],
    ],
];
```

## Usage & Integration

### Programmatic Registration

While configuration is the easiest method, you can register prompts anywhere (like in a package's `ServiceProvider`):

```php
use IllumaLaw\PromptRegistry\Facades\PromptRegistry;

// Register a single prompt
PromptRegistry::register('agents.custom', [
    'agent'         => \App\Ai\Agents\CustomAgent::class,
    'name'          => 'Custom Agent',
    'description'   => 'A custom agent for specialized tasks.',
    'fallback_view' => 'prompts.agents.custom',
]);

// Register multiple prompts
PromptRegistry::registerMany([
    // ...
]);
```

### Retrieving Prompts

The package provides multiple ways to fetch the registered definitions:

```php
use IllumaLaw\PromptRegistry\Facades\PromptRegistry;
use App\Ai\Agents\ContentCreatorAgent;

// Look up by registry key
$definition = PromptRegistry::forKey('agents.content_creator');
// Returns array: ['agent' => '...', 'name' => '...', 'description' => '...', ...]

// Look up by Agent class name
$definition = PromptRegistry::forAgent(ContentCreatorAgent::class);

// Get the default primary tier directly for routing (returns 'standard' or 'extended')
$tier = PromptRegistry::defaultPrimaryTierForAgent(ContentCreatorAgent::class);

// Get all registered prompts (flat list)
$all = PromptRegistry::all();

// Get all registered prompts, keyed by their registry key
$byKey = PromptRegistry::definitionsByKey();
```

### Exception Handling

If you attempt to retrieve a prompt that hasn't been registered, the registry will throw an `\InvalidArgumentException`. This enforces strict configuration correctness.

```php
use IllumaLaw\PromptRegistry\Facades\PromptRegistry;

try {
    $definition = PromptRegistry::forKey('agents.unknown');
} catch (\InvalidArgumentException $e) {
    // "No prompt definition was registered for key [agents.unknown]."
}
```

### Dependency Injection

You can resolve the underlying `PromptRegistryManager` from the Laravel container directly if you prefer dependency injection over Facades:

```php
namespace App\Services;

use IllumaLaw\PromptRegistry\PromptRegistryManager;

class PromptService
{
    public function __construct(
        protected PromptRegistryManager $registry
    ) {}

    public function handle(): void
    {
        $definition = $this->registry->forKey('agents.content_creator');
    }
}
```

## Testing

The test suite uses Pest and maintains 100% code coverage.

```bash
# Run tests
composer test

# Run static analysis
composer analyse
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
