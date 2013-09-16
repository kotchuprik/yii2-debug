<?php
/**
 * @var array $rows
 */
?>
<table class="table"
       style="table-layout: fixed;">
    <thead>
    <tr>
        <th style="width: 100px;">Time</th>
        <th style="width: 65px;">Level</th>
        <th style="width: 250px;">Category</th>
        <th>Message</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
        <tr class="<?= $row['class'] ?>">
            <td><?= $row['time'] ?></td>
            <td><?= $row['level'] ?></td>
            <td><?= $row['category'] ?></td>
            <td>
                <div>
                    <?= $row['message'] ?>
                    <?php if (count($row['traces'])): ?>
                        <ul class="trace">
                            <?php foreach ($row['traces'] as $trace): ?>
                                <li><?= CHtml::encode($trace) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
