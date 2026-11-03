# eloquage/laravel-prompt-registry

Dynamic registry for AI agent prompt definitions. Register metadata (agent, name, view, tier) via config or programmatically.

## Usage

### Registration (ServiceProvider)

```php
use IllumaLaw\PromptRegistry\Facades\PromptRegistry;

PromptRegistry::register('agents.legal_advisor', [
    'agent' => \App\Ai\Agents\LegalAdvisor::class,
    'name' => 'Legal Advisor',
    'description' => 'Legal guidance.',
    'fallback_view' => 'prompts.legal_advisor',
]);
```

### Lookup

```php
// Find by key
$definition = PromptRegistry::forKey('agents.legal_advisor');

// Find by agent class
$definition = PromptRegistry::forAgent(\App\Ai\Agents\LegalAdvisor::class);

// Get default primary tier
$tier = PromptRegistry::defaultPrimaryTierForAgent($agentClass);
```

## Configuration

Publish config: `php artisan vendor:publish --tag="laravel-prompt-registry-config"`

Register prompts in `config/prompt-registry.php`:
```php
'prompts' => [
    'agents.content_creator' => [
        'agent' => \App\Ai\Agents\ContentCreatorAgent::class,
        'name' => 'Content Creator Agent',
        'fallback_view' => 'prompts.agents.content_creator',
        'default_primary_tier' => 'standard',
    ],
],
```
