<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $content */
/** @var string $locale */
/** @var string $title */
/** @var App\Support\Translator $translator */
/** @var string $currentPath */

$currentPath = $currentPath ?? '/';
?>
<!doctype html>
<html lang="<?= Html::escape($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= Html::escape($translator->translate('app.description')) ?>">
    <title><?= Html::escape($title) ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <a class="skip-link" href="#main-content"><?= Html::escape($translator->translate('nav.skip')) ?></a>

    <?php require __DIR__ . '/partials/header.html.php'; ?>

    <main id="main-content" class="site-main">
        <div class="container">
            <?= $content ?>
        </div>
    </main>

    <?php require __DIR__ . '/partials/footer.html.php'; ?>
</body>
</html>
