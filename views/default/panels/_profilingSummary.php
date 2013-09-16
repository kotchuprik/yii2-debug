<?php
/**
 * @var string $url
 * @var string $time
 * @var string $memory
 */
?>
<div class="yii2-debug-toolbar-block">
    <a href="<?= $url ?>" title="Total request processing time was <?= $time ?>" target="_blank">Time <span
                class="label label-default"><?= $time ?></span></a>
</div>
<div class="yii2-debug-toolbar-block">
    <a href="<?= $url ?>" title="Peak memory consumption" target="_blank">Memory <span
                class="label label-default"><?= $memory ?></span></a>
</div>
