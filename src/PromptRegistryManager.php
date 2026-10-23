<?php

declare(strict_types=1);

namespace IllumaLaw\PromptRegistry;

use InvalidArgumentException;

class PromptRegistryManager
{
    /** @var array<string, array{agent: class-string, key: string, name: string, description: string, fallback_view: string, default_primary_tier?: 'standard'|'extended'}> */
    protected array $prompts = [];

    /**
     * @param  array{agent: class-string, name: string, description: string, fallback_view: string, default_primary_tier?: 'standard'|'extended'}  $definition
     */
    public function register(string $key, array $definition): void
    {
        $this->prompts[$key] = array_merge(['key' => $key], $definition);
    }

    /**
     * @param  array<string, array{agent: class-string, name: string, description: string, fallback_view: string, default_primary_tier?: 'standard'|'extended'}>  $definitions
     */
    public function registerMany(array $definitions): void
    {
        foreach ($definitions as $key => $definition) {
            $this->register($key, $definition);
        }
    }

    /**
     * @return array<string, array{agent: class-string, key: string, name: string, description: string, fallback_view: string, default_primary_tier?: 'standard'|'extended'}>
     */
    public function definitionsByKey(): array
    {
        return $this->prompts;
    }

    /**
     * @return array<int, array{agent: class-string, key: string, name: string, description: string, fallback_view: string, default_primary_tier?: 'standard'|'extended'}>
     */
    public function all(): array
    {
        return array_values($this->prompts);
    }

    /**
     * @param  class-string  $agentClass
     * @return array{agent: class-string, key: string, name: string, description: string, fallback_view: string, default_primary_tier?: 'standard'|'extended'}
     *
     * @throws InvalidArgumentException
     */
    public function forAgent(string $agentClass): array
    {
        foreach ($this->prompts as $definition) {
            if ($definition['agent'] === $agentClass) {
                return $definition;
            }
        }

        throw new InvalidArgumentException("No prompt definition was registered for [{$agentClass}].");
    }

    /**
     * @return array{agent: class-string, key: string, name: string, description: string, fallback_view: string, default_primary_tier?: 'standard'|'extended'}
     *
     * @throws InvalidArgumentException
     */
    public function forKey(string $key): array
    {
        if (! isset($this->prompts[$key])) {
            throw new InvalidArgumentException("No prompt definition was registered for key [{$key}].");
        }

        return $this->prompts[$key];
    }

    /**
     * @param  class-string  $agentClass
     * @return 'standard'|'extended'
     */
    public function defaultPrimaryTierForAgent(string $agentClass): string
    {
        try {
            $definition = $this->forAgent($agentClass);
            $tier = $definition['default_primary_tier'] ?? 'standard';

            return $tier === 'extended' ? 'extended' : 'standard';
        } catch (InvalidArgumentException) {
            return 'standard';
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function shortKeyFromRegistryKey(string $registryKey): string
    {
        $prefix = 'agents.';

        if (! str_starts_with($registryKey, $prefix)) {
            throw new InvalidArgumentException("Unexpected agent registry key [{$registryKey}].");
        }

        return substr($registryKey, strlen($prefix));
    }
}
