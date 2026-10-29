<?php

declare(strict_types=1);

use App\Support\Html;

/** @var App\Support\Translator $translator */
?>
<footer class="site-footer">
    <div class="container footer-inner">
        <p>© <?= date('Y') ?> Roomly. <?= Html::escape($translator->translate('footer.note')) ?></p>
        <a href="https://www.figma.com/design/gJzJ7034mSXKZHaxMkkB1H" target="_blank" rel="noopener noreferrer">
            <?= Html::escape($translator->translate('footer.figma')) ?>
        </a>
    </div>
</footer>
