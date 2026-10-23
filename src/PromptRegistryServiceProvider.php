<?php

declare(strict_types=1);

namespace IllumaLaw\PromptRegistry;

use IllumaLaw\PromptRegistry\Contracts\PromptContentStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PromptRegistryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-prompt-registry')
            ->hasConfigFile('prompt-registry');
    }

    public function registeringPackage(): void
    {
        $this->app->singleton('prompt-registry', function (): PromptRegistryManager {
            return new PromptRegistryManager;
        });

        $this->app->singleton(PromptRegistryManager::class, function (Application $app): PromptRegistryManager {
            $manager = $app->make('prompt-registry');
            if (! ($manager instanceof PromptRegistryManager)) {
                throw new \LogicException('The prompt registry manager binding is invalid.');
            }

            return $manager;
        });

        $persistenceModel = null;
        if ($this->app->bound('config')) {
            /** @var mixed $resolvedPersistenceModel */
            $resolvedPersistenceModel = $this->app->make('config')->get('prompt-registry.persistence.model');
            $persistenceModel = $resolvedPersistenceModel;
        }

        if (is_string($persistenceModel) && is_subclass_of($persistenceModel, Model::class)) {
            $this->app->singleton(PromptContentStore::class, function () use ($persistenceModel): PromptContentStore {
                return new EloquentPromptContentStore($persistenceModel);
            });
        } else {
            $this->app->bindIf(PromptContentStore::class, NullPromptContentStore::class);
        }

        $this->app->singleton(PromptTemplateRenderer::class, function (Application $app): PromptTemplateRenderer {
            return new PromptTemplateRenderer(
                registry: $app->make(PromptRegistryManager::class),
            );
        });

        $this->app->singleton(PromptBodyResolver::class, function (Application $app): PromptBodyResolver {
            return new PromptBodyResolver(
                registry: $app->make(PromptRegistryManager::class),
                contentStore: $app->make(PromptContentStore::class),
                renderer: $app->make(PromptTemplateRenderer::class),
            );
        });

        $this->app->singleton(AgentKeyResolver::class, function (Application $app): AgentKeyResolver {
            return new AgentKeyResolver(
                registry: $app->make(PromptRegistryManager::class),
            );
        });

        $this->app->singleton(PromptPersistenceManager::class, function (Application $app): PromptPersistenceManager {
            return new PromptPersistenceManager(
                registry: $app->make(PromptRegistryManager::class),
                contentStore: $app->make(PromptContentStore::class),
                bodyResolver: $app->make(PromptBodyResolver::class),
            );
        });
    }

    public function bootingPackage(): void
    {
        /** @var PromptRegistryManager $manager */
        $manager = $this->app->make('prompt-registry');

        /** @var array<string, array{agent: class-string, name: string, description: string, fallback_view: string, default_primary_tier?: 'standard'|'extended'}> $prompts */
        $prompts = config('prompt-registry.prompts', []);

        if ($prompts !== []) {
            $manager->registerMany($prompts);
        }
    }
}
