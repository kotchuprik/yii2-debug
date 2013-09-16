<?php
/**
 * @var array $rows
 */
?>
<table class="table">
    <thead>
    <tr>
        <th style="width: 100px;">Time</th>
        <th style="width: 80px;">Duration</th>
        <th>Query</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td style="width: 100px;"><?= $row['time'] ?></td>
            <td style="width: 80px;"><?= $row['duration'] ?></td>
            <td>
                <pre class="pre-scrollable yii2-debug-pre"><?= $row['procedure'] ?></pre>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
