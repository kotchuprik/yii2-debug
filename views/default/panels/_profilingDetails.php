<?php
/**
 * @var array $rows
 * @var string $time
 * @var string $memory
 */
?>
<p>Total processing time: <b><?= $time ?></b>; Peak memory: <b><?= $memory ?></b>.</p>

<table class="table">
    <thead>
    <tr>
        <th style="width: 80px;">Time</th>
        <th style="width: 220px;">Category</th>
        <th>Procedure</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td><?= $row['time'] ?></td>
            <td><?= $row['category'] ?></td>
            <td><?= $row['procedure'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
