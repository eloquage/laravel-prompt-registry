<?php

declare(strict_types=1);

namespace IllumaLaw\PromptRegistry;

use IllumaLaw\PromptRegistry\Contracts\PromptContentStore;
use IllumaLaw\PromptRegistry\ValueObjects\StoredPromptContent;
use Illuminate\Database\Eloquent\Model;

final class EloquentPromptContentStore implements PromptContentStore
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function __construct(
        private readonly string $modelClass,
    ) {}

    public function findByKey(string $key): ?StoredPromptContent
    {
        $modelClass = $this->modelClass;
        // The configured model is only constrained to Model, so PHPStan cannot infer its key column.
        // @phpstan-ignore argument.type
        $record = $modelClass::query()->where('key', $key)->first();

        if (! $record instanceof Model) {
            return null;
        }

        /** @var mixed $content */
        $content = $record->getAttribute('content');

        return new StoredPromptContent(
            key: $key,
            content: is_string($content) ? $content : '',
            exists: true,
        );
    }

    public function upsertByKey(string $key, string $content): StoredPromptContent
    {
        $modelClass = $this->modelClass;

        // The configured model is only constrained to Model, so PHPStan cannot infer its fillable columns.
        // @phpstan-ignore argument.type
        $modelClass::query()->updateOrCreate(
            ['key' => $key],
            ['content' => $content],
        );

        return new StoredPromptContent(
            key: $key,
            content: $content,
            exists: true,
        );
    }

    public function deleteByKey(string $key): void
    {
        $modelClass = $this->modelClass;

        // The configured model is only constrained to Model, so PHPStan cannot infer its key column.
        // @phpstan-ignore argument.type
        $modelClass::query()->where('key', $key)->delete();
    }
}
