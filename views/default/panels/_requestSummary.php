<?php
/**
 * @var string $url
 * @var string $statusCode
 * @var string $class
 * @var string $tag
 * @var string $action
 */
?>
<div class="yii2-debug-toolbar-block">
    <a href="<?= $url ?>" title="Status code: $statusCode" target="_blank">Status <span
                class="label <?= $class ?>"><?= $statusCode ?></span></a>
</div>
<div class="yii2-debug-toolbar-block">
    <a href="<?= $url ?>" target="_blank">Action
        <span class="label label-default"><?= $action ?></span>
        <span class="label label-default"><?= $tag ?></span>
    </a>
</div>
