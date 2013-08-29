<?php
/**
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2DebugPhpinfoPanel extends Yii2DebugPanel
{
    /**
     * @return string название панели для вывода в меню
     */
    public function getTitle()
    {
        return 'Phpinfo';
    }

    /**
     * @return string html-контент для вывода на страницу
     */
    public function getDetails()
    {
        return $this->data['phpinfo'];
    }

    /**
     * @return string html-контент для вывода в дебаг-панель
     */
    public function getSummary()
    {
        return '<div class="yii2-debug-toolbar-block">' .
               CHtml::link('PHP ' . PHP_VERSION, $this->getUrl()) .
               '</div>';
    }

    /**
     * Базовый метод для сбора отладочной информации
     * @return mixed
     */
    public function getDataToSave()
    {
        return array(
            'phpinfo' => $this->_getPhpinfoTable(),
        );
    }

    protected function _getPhpinfoTable()
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();

        // Get body content
        $phpinfo = preg_replace('/^.*<body>(.*)<\/body>.*$/ms', '$1', $phpinfo);

        // Remove all attributes
        $phpinfo = preg_replace('/<\s*(\w+).*?>/', '<$1>', $phpinfo);

        // Remove img
        $phpinfo = preg_replace('/<img>/', '', $phpinfo);

        // Remove all links
        $phpinfo = preg_replace('/<a>(.*)<\/a>/i', '$1', $phpinfo);

        // Change H2 to H3
        $phpinfo = preg_replace('/<h2>(.*)<\/h2>/i', '<h3>$1</h3>', $phpinfo);

        // Change H1 to H2
        $phpinfo = preg_replace('/<h1>(.*)<\/h1>/i', '<h2>$1</h2>', $phpinfo);

        // Add css classes to tables
        $phpinfo = preg_replace(
            '/table/si',
            'table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;"',
            $phpinfo
        );

        return $phpinfo;
    }
}
