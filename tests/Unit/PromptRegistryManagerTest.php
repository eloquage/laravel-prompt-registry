<?php

declare(strict_types=1);

use IllumaLaw\PromptRegistry\Facades\PromptRegistry;
use IllumaLaw\PromptRegistry\PromptRegistryManager;

it('can register and retrieve a prompt definition by key', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agent */
    $agent = 'App\Ai\Agents\TestAgent';

    $manager->register('agents.test', [
        'agent' => $agent,
        'name' => 'Test Agent',
        'description' => 'Test description',
        'fallback_view' => 'prompts.test',
    ]);

    $retrieved = $manager->forKey('agents.test');

    expect($retrieved)->not->toBeNull()
        ->and($retrieved['key'])->toBe('agents.test')
        ->and($retrieved['agent'])->toBe('App\Ai\Agents\TestAgent')
        ->and($retrieved['name'])->toBe('Test Agent')
        ->and($retrieved['description'])->toBe('Test description')
        ->and($retrieved['fallback_view'])->toBe('prompts.test');
});

it('merges the key into the definition when registering', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agent */
    $agent = 'SomeAgent';

    $manager->register('agents.merged', [
        'agent' => $agent,
        'name' => 'Some Agent',
        'description' => 'Desc',
        'fallback_view' => 'view',
    ]);

    expect($manager->forKey('agents.merged')['key'])->toBe('agents.merged');
});

it('overwrites an existing registration with the same key', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $originalAgent */
    $originalAgent = 'OriginalAgent';
    $manager->register('agents.dup', [
        'agent' => $originalAgent, 'name' => 'Original', 'description' => '', 'fallback_view' => '',
    ]);
    /** @var class-string $replacedAgent */
    $replacedAgent = 'ReplacedAgent';
    $manager->register('agents.dup', [
        'agent' => $replacedAgent, 'name' => 'Replaced', 'description' => '', 'fallback_view' => '',
    ]);

    expect($manager->forKey('agents.dup')['agent'])->toBe('ReplacedAgent');
});

it('throws InvalidArgumentException for a missing key', function (): void {
    $manager = new PromptRegistryManager;

    $manager->forKey('non.existent');
})->throws(InvalidArgumentException::class, 'No prompt definition was registered for key [non.existent].');

it('can register many prompts at once', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agentOne */
    $agentOne = 'App\Ai\Agents\One';
    /** @var class-string $agentTwo */
    $agentTwo = 'App\Ai\Agents\Two';

    $manager->registerMany([
        'agents.one' => [
            'agent' => $agentOne, 'name' => 'One', 'description' => 'Desc one', 'fallback_view' => 'view.one',
        ],
        'agents.two' => [
            'agent' => $agentTwo, 'name' => 'Two', 'description' => 'Desc two', 'fallback_view' => 'view.two',
        ],
    ]);

    expect($manager->all())->toHaveCount(2)
        ->and($manager->forKey('agents.one')['agent'])->toBe('App\Ai\Agents\One')
        ->and($manager->forKey('agents.two')['agent'])->toBe('App\Ai\Agents\Two');
});

it('returns an empty array from all() when nothing is registered', function (): void {
    $manager = new PromptRegistryManager;

    expect($manager->all())->toBe([]);
});

it('returns an empty array from definitionsByKey() when nothing is registered', function (): void {
    $manager = new PromptRegistryManager;

    expect($manager->definitionsByKey())->toBe([]);
});

it('returns definitions keyed by registry key from definitionsByKey()', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agentAlpha */
    $agentAlpha = (string) 'AlphaAgent';
    /** @var class-string $agentBeta */
    $agentBeta = (string) 'BetaAgent';

    $manager->registerMany([
        'agents.alpha' => ['agent' => $agentAlpha, 'name' => 'Alpha', 'description' => '', 'fallback_view' => ''],
        'agents.beta' => ['agent' => $agentBeta,  'name' => 'Beta',  'description' => '', 'fallback_view' => ''],
    ]);

    $byKey = $manager->definitionsByKey();

    expect($byKey)->toHaveKeys(['agents.alpha', 'agents.beta'])
        ->and($byKey['agents.alpha']['agent'])->toBe('AlphaAgent')
        ->and($byKey['agents.beta']['agent'])->toBe('BetaAgent');
});

it('all() returns a list (not associative)', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agentX */
    $agentX = 'XAgent';
    $manager->register('agents.x', ['agent' => $agentX, 'name' => 'X', 'description' => '', 'fallback_view' => '']);

    $all = $manager->all();

    expect(array_is_list($all))->toBe(true)
        ->and($all[0]['key'])->toBe('agents.x');
});

it('can retrieve a definition by agent class', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agent */
    $agent = 'App\Ai\Agents\TestAgent';

    $manager->register('agents.test', [
        'agent' => $agent, 'name' => 'Test Agent', 'description' => '', 'fallback_view' => '',
    ]);

    expect($manager->forAgent($agent)['key'])->toBe('agents.test');
});

it('throws InvalidArgumentException for an unknown agent class', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agent */
    $agent = 'Non\Existent\Agent';

    $manager->forAgent($agent);
})->throws(InvalidArgumentException::class, 'No prompt definition was registered for [Non\Existent\Agent].');

it('returns standard tier when no tier is specified', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agent */
    $agent = 'StandardAgent';

    $manager->register('agents.standard', [
        'agent' => $agent, 'name' => 'Standard', 'description' => '', 'fallback_view' => '',
    ]);

    expect($manager->defaultPrimaryTierForAgent($agent))->toBe('standard');
});

it('returns extended tier when default_primary_tier is extended', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agent */
    $agent = 'ExtendedAgent';

    $manager->register('agents.extended', [
        'agent' => $agent, 'name' => 'Extended', 'description' => '', 'fallback_view' => '',
        'default_primary_tier' => 'extended',
    ]);

    expect($manager->defaultPrimaryTierForAgent($agent))->toBe('extended');
});

it('returns standard tier for an unregistered agent', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agent */
    $agent = 'UnknownAgent';

    expect($manager->defaultPrimaryTierForAgent($agent))->toBe('standard');
});

it('treats an invalid tier value as standard', function (): void {
    $manager = new PromptRegistryManager;

    /** @var class-string $agent */
    $agent = 'WeirdAgent';

    $manager->register('agents.weird', [
        'agent' => $agent, 'name' => 'Weird', 'description' => '', 'fallback_view' => '',
        'default_primary_tier' => 'premium',
    ]);

    expect($manager->defaultPrimaryTierForAgent($agent))->toBe('standard');
});

it('extracts the short key from a valid agents.* registry key', function (): void {
    $manager = new PromptRegistryManager;

    expect($manager->shortKeyFromRegistryKey('agents.content_creator'))->toBe('content_creator');
});

it('throws InvalidArgumentException when the agents. prefix is missing', function (): void {
    $manager = new PromptRegistryManager;

    $manager->shortKeyFromRegistryKey('wrong.prefix');
})->throws(InvalidArgumentException::class, 'Unexpected agent registry key [wrong.prefix].');

it('is bound to the container as a singleton', function (): void {
    $a = app('prompt-registry');
    $b = app('prompt-registry');

    if (! ($a instanceof PromptRegistryManager) || ! ($b instanceof PromptRegistryManager)) {
        throw new RuntimeException('The prompt registry manager must be bound.');
    }

    expect($a)->toBe($b);
});

it('can be resolved via the PromptRegistry facade', function (): void {
    /** @var class-string $agent */
    $agent = 'FacadeAgent';
    PromptRegistry::register('agents.facade', [
        'agent' => $agent, 'name' => 'Facade', 'description' => '', 'fallback_view' => '',
    ]);

    expect(PromptRegistry::forKey('agents.facade')['agent'])->toBe('FacadeAgent');
});

it('auto-registers prompts defined in the config', function (): void {
    /** @var class-string $agent */
    $agent = 'ConfigAgent';
    config(['prompt-registry.prompts' => [
        'agents.config_driven' => [
            'agent' => $agent,
            'name' => 'Config Agent',
            'description' => 'Loaded from config',
            'fallback_view' => 'prompts.config',
        ],
    ]]);

    /** @var PromptRegistryManager $manager */
    $manager = new PromptRegistryManager;
    /** @var array<string, array{agent: class-string, name: string, description: string, fallback_view: string, default_primary_tier?: 'standard'|'extended'}> $prompts */
    $prompts = config('prompt-registry.prompts', []);
    $manager->registerMany($prompts);

    expect($manager->forKey('agents.config_driven')['agent'])->toBe('ConfigAgent');
});

it('handles empty config prompts array without error', function (): void {
    config(['prompt-registry.prompts' => []]);

    $manager = new PromptRegistryManager;
    $prompts = config('prompt-registry.prompts', []);

    if ($prompts !== []) {
        $manager->registerMany($prompts);
    }

    expect($manager->all())->toBe([]);
});
