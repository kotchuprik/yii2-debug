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
<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <span class="navbar-brand">Yii Debugger</span>
        </div>
        <div class="collapse navbar-collapse navbar-ex1-collapse">
            <ul class="nav navbar-nav">
                <li>
                    <?php foreach ($panels as $panel): ?>
                        <?= $panel->getSummary() ?>
                    <?php endforeach; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-2">
            <ul class="nav nav-pills nav-stacked">
                <?php foreach ($panels as $id => $panel): ?>
                    <li <?= $panel === $activePanel ? 'class="active"' : '' ?>>
                        <?= CHtml::link(CHtml::encode($panel->getTitle()), array('view', 'tag' => $tag, 'panel' => $id)) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-10">
            <div class="alert alert-info">
                <?= CHtml::link('All', array('index'), array('class' => 'alert-link')) ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                            data-toggle="dropdown">
                        Last 10
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <?php
                        // label-default
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
                <?= CHtml::link(CHtml::encode($summary['url']), $summary['url'], array('class' => 'label label-default')) ?>
                <?= $summary['ajax'] ? ' (AJAX)' : '' ?> at <?= date('Y-m-d h:i:s', $summary['time']) ?>
                by <?= $summary['ip'] ?>
            </div>
            <?= $activePanel->getDetails() ?>
        </div>
    </div>
</div>
