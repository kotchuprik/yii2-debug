<?php
/**
 * @var DefaultController $this
 * @var array $summary
 * @var string $tag
 * @var array $manifest
 * @var Yii2DebugPanel[] $panels
 * @var Yii2DebugPanel $activePanel
 */
$this->pageTitle = 'Yii Debugger';
?>
<div class="default-view">
    <div class="navbar">
        <div class="navbar-inner">
            <div class="container">
                <div class="yii2-debug-toolbar-block title">Yii Debugger</div>
                <?php foreach ($panels as $panel): ?>
                    <?= $panel->getSummary() ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span2">
                <ul class="nav nav-tabs nav-stacked">
                    <?php foreach ($panels as $id => $panel): ?>
                        <li <?= $panel === $activePanel ? 'class="active"' : '' ?>>
                            <?= CHtml::link(CHtml::encode($panel->getTitle()), array('view', 'tag' => $tag, 'panel' => $id)) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="span10">
                <div class="meta alert alert-info">
                    <div class="btn-group">
                        <?= CHtml::link('All', array('index'), array('class' => 'btn')) ?>
                        <button class="btn dropdown-toggle" data-toggle="dropdown">
                            Last 10
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <?php
                            $count = 0;
                            foreach ($manifest as $meta) {
                                $label = $meta['tag'] . ': ' . $meta['method'] . ' ' . $meta['url'] .
                                         ($meta['ajax'] ? ' (AJAX)' : '')
                                         . ', ' . date('Y-m-d h:i:s', $meta['time'])
                                         . ', ' . $meta['ip'];
                                $url = array('view', 'tag' => $meta['tag'], 'panel' => $activePanel->id);
                                if ($meta['tag'] == $tag) {
                                    echo '<li class="disabled">';
                                } else {
                                    echo '<li>';
                                }
                                echo CHtml::link(CHtml::encode($label), $url);
                                echo '</li>';
                                if (++$count >= 10) {
                                    break;
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <?= $summary['method'] ?>
                    <?= CHtml::link(CHtml::encode($summary['url']), $summary['url'], array('class' => 'label')) ?>
                    <?= $summary['ajax'] ? ' (AJAX)' : '' ?> at <?= date('Y-m-d h:i:s', $summary['time']) ?>
                    by <?= $summary['ip'] ?>
                </div>
                <?= $activePanel->getDetails() ?>
            </div>
        </div>
    </div>
</div>
