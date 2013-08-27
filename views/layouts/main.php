<?php
/**
 * @var CController $this
 * @var string $content
 */
?>
<!doctype html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="language" content="<?= Yii::app()->language ?>"/>
    <title><?= CHtml::encode($this->pageTitle) ?></title>
</head>
<body>
<?= $content; ?>
</body>
</html>
