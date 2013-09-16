<?php
/**
 * @var Yii2DebugPanel $debugPanel
 * @var string $caption
 * @var array $values
 */
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?= $caption ?></h4>
    </div>
    <?php if (count($values) == 0): ?>
        <div class="panel-body">
            <p>Empty.</p>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th style="width: 300px;">Name</th>
                <th>Value</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($values as $name => $value): ?>
                <tr>
                    <th style="width: 300px; word-break: break-all;"><?= CHtml::encode($name) ?></th>
                    <td>
                        <div style="overflow: auto">
                            <?php if (is_string($value)): ?>
                                <?= CHtml::encode($value) ?>
                            <?php else: ?>
<!-- Внутри pre отсупы нам не нужны, поэтому по левому краю -->
<pre class="pre-scrollable yii2-debug-pre"><?php if ($debugPanel->highlightCode): ?>
<?= $debugPanel->highlightPhp(Yii2DebugVarExporter::export($value)) ?>
<?php else: ?>
<?= CHtml::encode(var_export($value, true)) ?>
<?php endif; ?></pre>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
