<?php

declare(strict_types=1);

namespace App\Controller;

final class DefaultController extends AppController
{
    public function index(): void
    {
        $this->render('home', [
            'title' => $this->translator->translate('home.title'),
            'currentPath' => '/',
        ]);
    }
}
