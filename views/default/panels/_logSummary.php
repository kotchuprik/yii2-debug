<?php
/**
 * @var string $title
 * @var string $url
 * @var array $messagesCount
 */
?>
<div class="yii2-debug-toolbar-block">
    <a href="<?= $url ?>" title="Logged <?= $title ?>" target="_blank">Log
        <span class="label label-default"><?= $messagesCount['total'] ?></span>
        <?php if ($messagesCount['errors'] > 0): ?>
            <span class="label label-danger"><?= $messagesCount['errors'] ?></span>
        <?php endif; ?>
        <?php if ($messagesCount['warnings'] > 0): ?>
            <span class="label label-warning"><?= $messagesCount['warnings'] ?></span>
        <?php endif; ?>
    </a>
</div>
