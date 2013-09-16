<?php
/**
 * @var array $rows
 */
?>
<table class="table">
    <thead>
    <tr>
        <th>Query</th>
        <th style="width:50px;">Count</th>
        <th style="width:70px;">Total</th>
        <th style="width:70px;">Avg</th>
        <th style="width:70px;">Min</th>
        <th style="width:70px;">Max</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td>
                <pre class="pre-scrollable yii2-debug-pre"><?= $row['query'] ?></pre>
            </td>
            <td><?= $row['count'] ?></td>
            <td><?= $row['total'] ?></td>
            <td><?= $row['avg'] ?></td>
            <td><?= $row['min'] ?></td>
            <td><?= $row['max'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
