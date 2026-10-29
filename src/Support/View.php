<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class View
{
    public function __construct(
        private readonly string $directory,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = [], string $layout = 'layout'): void
    {
        $templatePath = $this->resolve($template);
        $layoutPath = $this->resolve($layout);

        $translator = $this->translator;
        $locale = $translator->locale();
        $data += ['title' => $translator->translate('app.name')];

        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }

    private function resolve(string $name): string
    {
        if ($name === '' || str_contains($name, '..')) {
            throw new RuntimeException('Invalid view name.');
        }

        $path = $this->directory . '/' . $name . '.html.php';

        if (!is_file($path)) {
            throw new RuntimeException(sprintf('View "%s" was not found.', $name));
        }

        return $path;
    }
}
