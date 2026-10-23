<?php

declare(strict_types=1);

namespace IllumaLaw\PromptRegistry;

use IllumaLaw\PromptRegistry\Contracts\PromptContentStore;
use IllumaLaw\PromptRegistry\ValueObjects\StoredPromptContent;

final class PromptPersistenceManager
{
    public function __construct(
        private readonly PromptRegistryManager $registry,
        private readonly PromptContentStore $contentStore,
        private readonly PromptBodyResolver $bodyResolver,
    ) {}

    /**
     * @return array{
     *     key: string,
     *     name: string,
     *     description: string,
     *     fallback_view: string,
     *     content: string,
     *     source: 'database'|'fallback_view',
     *     persisted: bool,
     *     empty_persisted_override: bool
     * }
     */
    public function editableByKey(string $key): array
    {
        return $this->bodyResolver->editableByKey($key);
    }

    public function persistByKey(string $key, string $content): StoredPromptContent
    {
        $definition = $this->registry->forKey($key);
        $registryKey = $definition['key'];
        $trimmed = trim($content);

        if ($trimmed === '') {
            $this->contentStore->deleteByKey($registryKey);

            return StoredPromptContent::missing($registryKey);
        }

        return $this->contentStore->upsertByKey($registryKey, $trimmed);
    }

    public function revertToFallbackByKey(string $key): void
    {
        $definition = $this->registry->forKey($key);
        $this->contentStore->deleteByKey($definition['key']);
    }
}
