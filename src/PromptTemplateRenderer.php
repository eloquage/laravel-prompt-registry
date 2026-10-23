<?php

declare(strict_types=1);

namespace IllumaLaw\PromptRegistry;

use Illuminate\Support\Facades\View;
use InvalidArgumentException;

final class PromptTemplateRenderer
{
    public function __construct(
        private readonly PromptRegistryManager $registry,
    ) {}

    /**
     * @param  view-string  $viewName
     * @param  array<string, mixed>  $data
     */
    public function render(string $viewName, array $data = []): string
    {
        if (! View::exists($viewName)) {
            throw new InvalidArgumentException("Prompt view [{$viewName}] was not found.");
        }

        return View::make($viewName, $data)->render();
    }

    /** @param view-string $fallbackView */
    public function renderFallback(string $fallbackView): string
    {
        return trim($this->render($fallbackView));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function renderUserPrompt(string $registryKey, array $data = []): string
    {
        $short = $this->registry->shortKeyFromRegistryKey($registryKey);
        $viewName = 'prompts.agents.user.'.$short;

        if (! View::exists($viewName)) {
            throw new InvalidArgumentException(
                "Missing Blade user prompt view [{$viewName}] for registry key [{$registryKey}]. Expected resources/views/prompts/agents/user/{$short}.blade.php.",
            );
        }

        return $this->render($viewName, $data);
    }

    /**
     * @param  class-string  $agentClass
     * @param  array<string, mixed>  $data
     */
    public function renderUserPromptForAgent(string $agentClass, array $data = []): string
    {
        $definition = $this->registry->forAgent($agentClass);

        return $this->renderUserPrompt($definition['key'], $data);
    }
}
