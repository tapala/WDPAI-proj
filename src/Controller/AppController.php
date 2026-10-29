<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\Translator;
use App\Support\View;

abstract class AppController
{
    public function __construct(
        protected readonly View $view,
        protected readonly Translator $translator,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        $this->view->render($template, $data);
    }

    protected function redirect(string $location, int $status = 303): never
    {
        header('Location: ' . $location, true, $status);
        exit;
    }
}
