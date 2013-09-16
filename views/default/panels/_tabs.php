<?php
/**
 * @var array $items
 */
?>

<ul id="tabs" class="nav nav-tabs">
    <?php foreach ($items as $num => $item): ?>
        <li class="<?= isset($item['active']) && $item['active'] ? 'active' : '' ?>">
            <a href="#tabs-tab<?= $num ?>" data-toggle="tab"><?= $item['label'] ?></a>
        </li>
    <?php endforeach; ?>
</ul>
<div class="tab-content">
    <?php foreach ($items as $num => $item): ?>
        <div id="tabs-tab<?= $num ?>"
             class="tab-pane <?= isset($item['active']) && $item['active'] ? ' active' : '' ?>">
            <?= $item['content'] ?>
        </div>
    <?php endforeach; ?>
</div>
