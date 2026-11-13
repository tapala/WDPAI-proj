<?php

declare(strict_types=1);

namespace App\Controller;

use App\Security\CsrfToken;
use App\Support\FlashMessage;
use App\Support\FormValidator;
use App\Support\Translator;
use App\Support\View;

/**
 * Demo sign-in form.
 *
 * The form validates input, protects the POST with a CSRF token and follows
 * Post/Redirect/Get. Authentication itself arrives in Lab 9, so a valid
 * submission never creates a user session — the page says so explicitly.
 */
final class AuthController extends AppController
{
    private const PASSWORD_MIN_LENGTH = 8;

    public function __construct(
        View $view,
        Translator $translator,
        private readonly CsrfToken $csrf,
        private readonly FlashMessage $flash,
    ) {
        parent::__construct($view, $translator);
    }

    public function showLogin(): void
    {
        $this->render('auth/login', $this->viewData());
    }

    public function login(): void
    {
        if (!$this->csrf->isValid($_POST['_token'] ?? null)) {
            $this->render('auth/login', $this->viewData([
                'formError' => 'auth.login.csrf_invalid',
            ]), 403);

            return;
        }

        $validator = (new FormValidator($_POST))
            ->required('email', 'validation.required')
            ->email('email', 'validation.email')
            ->required('password', 'validation.required')
            ->minLength('password', self::PASSWORD_MIN_LENGTH, 'auth.login.password_min');

        if ($validator->hasErrors()) {
            $this->render('auth/login', $this->viewData([
                'email' => $validator->value('email'),
                'errors' => $validator->errors(),
            ]), 422);

            return;
        }

        $this->flash->set([
            'type' => 'info',
            'message' => 'auth.login.demo_success',
        ]);

        $this->redirect('/login');
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function viewData(array $overrides = []): array
    {
        return array_merge([
            'title' => $this->translator->translate('auth.login.title'),
            'currentPath' => '/login',
            'csrfToken' => $this->csrf->token(),
            'email' => '',
            'errors' => [],
            'formError' => null,
            'flash' => $this->flash->pull(),
        ], $overrides);
    }
}
