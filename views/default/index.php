<?php
/**
 * @var DefaultController $this
 * @var array $manifest
 */
?>
<nav class="navbar navbar-default navbar-static-top">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <span class="navbar-brand">Yii Debugger</span>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3>Available Debug Data</h3>
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th style="width: 120px;">Tag</th>
                    <th style="width: 160px;">Time</th>
                    <th style="width: 120px;">IP</th>
                    <th style="width: 60px;">Method</th>
                    <th>URL</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($manifest as $tag => $data): ?>
                    <tr>
                        <td><?= CHtml::link($tag, array('view', 'tag' => $tag)) ?></td>
                        <td><?= date('Y-m-d h:i:s', $data['time']) ?></td>
                        <td><?= $data['ip'] ?></td>
                        <td><?= $data['method'] ?></td>
                        <td style="word-break:break-all;"><?= $data['url'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
