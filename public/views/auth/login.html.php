<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $csrfToken */
/** @var string $email */
/** @var array<string, list<string>> $errors */
/** @var string|null $formError */
/** @var array<string, string>|null $flash */
/** @var App\Support\Translator $translator */

$fieldMeta = [
    'email' => ['id' => 'login-email', 'label' => $translator->translate('auth.login.email')],
    'password' => ['id' => 'login-password', 'label' => $translator->translate('auth.login.password')],
];

$errorItems = [];

foreach ($errors as $field => $messages) {
    foreach ($messages as $message) {
        $errorItems[] = ['field' => $field, 'message' => $message];
    }
}

$emailError = isset($errors['email']) ? $translator->translate($errors['email'][0]) : null;
$passwordError = isset($errors['password']) ? $translator->translate($errors['password'][0]) : null;
$flashMessage = $flash['message'] ?? null;
?>
<section class="auth-card" aria-labelledby="login-title">
    <h1 id="login-title"><?= Html::escape($translator->translate('auth.login.title')) ?></h1>

    <?php if (is_string($flashMessage) && $flashMessage !== ''): ?>
        <p class="form-flash form-flash--info" role="status"><?= Html::escape($translator->translate($flashMessage)) ?></p>
    <?php endif; ?>

    <p class="notice"><?= Html::escape($translator->translate('auth.login.demo_notice')) ?></p>

    <?php if ($formError !== null || $errorItems !== []): ?>
        <div class="form-errors" role="alert">
            <p class="form-errors__title"><?= Html::escape($translator->translate('auth.login.form_errors')) ?></p>
            <ul>
                <?php if ($formError !== null): ?>
                    <li><?= Html::escape($translator->translate($formError)) ?></li>
                <?php endif; ?>
                <?php foreach ($errorItems as $item): ?>
                    <?php $meta = $fieldMeta[$item['field']] ?? null; ?>
                    <li>
                        <?php if ($meta !== null): ?>
                            <a href="#<?= Html::escape($meta['id']) ?>"><?= Html::escape($meta['label']) ?>:</a>
                        <?php endif; ?>
                        <?= Html::escape($translator->translate($item['message'])) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="/login" novalidate>
        <input type="hidden" name="_token" value="<?= Html::escape($csrfToken) ?>">

        <div class="field">
            <label for="login-email"><?= Html::escape($translator->translate('auth.login.email')) ?></label>
            <input
                id="login-email"
                name="email"
                type="email"
                autocomplete="username"
                value="<?= Html::escape($email) ?>"
                required
                <?= $emailError !== null ? 'aria-invalid="true" aria-describedby="login-email-error"' : '' ?>
            >
            <?php if ($emailError !== null): ?>
                <p class="field__error" id="login-email-error"><?= Html::escape($emailError) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="login-password"><?= Html::escape($translator->translate('auth.login.password')) ?></label>
            <input
                id="login-password"
                name="password"
                type="password"
                autocomplete="current-password"
                minlength="8"
                required
                aria-describedby="login-password-hint<?= $passwordError !== null ? ' login-password-error' : '' ?>"
                <?= $passwordError !== null ? 'aria-invalid="true"' : '' ?>
            >
            <p class="field__hint" id="login-password-hint"><?= Html::escape($translator->translate('auth.login.password_hint')) ?></p>
            <?php if ($passwordError !== null): ?>
                <p class="field__error" id="login-password-error"><?= Html::escape($passwordError) ?></p>
            <?php endif; ?>
        </div>

        <button class="button button--block" type="submit">
            <?= Html::escape($translator->translate('auth.login.submit')) ?>
        </button>
    </form>

    <p class="auth-card__back">
        <a href="/rooms"><?= Html::escape($translator->translate('auth.login.back')) ?></a>
    </p>
</section>
