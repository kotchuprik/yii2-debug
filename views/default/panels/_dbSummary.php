<?php
/**
 * @var int $queryCount
 * @var string $queryTime
 * @var string $url
 */
?>
<div class="yii2-debug-toolbar-block">
    <a href="<?= $url ?>" title="Executed <?= $queryCount ?> database queries which took <?= $queryTime ?>." target="_blank">
        DB <span class="label label-default"><?= $queryCount ?></span> <span
                class="label label-default"><?= $queryTime ?></span>
    </a>
</div>
