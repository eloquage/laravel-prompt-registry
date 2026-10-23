<?php

declare(strict_types=1);

use IllumaLaw\PromptRegistry\Contracts\PromptContentStore;
use IllumaLaw\PromptRegistry\PromptBodyResolver;
use IllumaLaw\PromptRegistry\PromptPersistenceManager;
use IllumaLaw\PromptRegistry\PromptRegistryManager;
use IllumaLaw\PromptRegistry\PromptTemplateRenderer;
use IllumaLaw\PromptRegistry\ValueObjects\StoredPromptContent;

it('persists trimmed content and reverts empty content to fallback state', function (): void {
    $registry = new PromptRegistryManager;
    $registry->register('agents.example', [
        'agent' => stdClass::class,
        'name' => 'Example',
        'description' => 'Example prompt',
        'fallback_view' => 'welcome',
    ]);

    $store = new MemoryPromptContentStore;
    $renderer = new PromptTemplateRenderer($registry);
    $bodyResolver = new PromptBodyResolver($registry, $store, $renderer);
    $persistence = new PromptPersistenceManager($registry, $store, $bodyResolver);

    $stored = $persistence->persistByKey('agents.example', "  Custom body  \n");

    expect($stored->exists)->toBeTrue()
        ->and($stored->content)->toBe('Custom body')
        ->and($persistence->editableByKey('agents.example')['source'])->toBe('database');

    $missing = $persistence->persistByKey('agents.example', " \n\t ");

    expect($missing->exists)->toBeFalse()
        ->and($missing->content)->toBe('')
        ->and($persistence->editableByKey('agents.example')['source'])->toBe('fallback_view');
});

final class MemoryPromptContentStore implements PromptContentStore
{
    /**
     * @var array<string, string>
     */
    private array $storage = [];

    public function findByKey(string $key): ?StoredPromptContent
    {
        if (! array_key_exists($key, $this->storage)) {
            return null;
        }

        return new StoredPromptContent($key, $this->storage[$key], true);
    }

    public function upsertByKey(string $key, string $content): StoredPromptContent
    {
        $this->storage[$key] = $content;

        return new StoredPromptContent($key, $content, true);
    }

    public function deleteByKey(string $key): void
    {
        unset($this->storage[$key]);
    }
}
