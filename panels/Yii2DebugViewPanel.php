<?php
/**
 * Yii2DebugViewPanel class file.
 *
 * @author Roman Domrachev <ligser@gmail.com>
 *
 * @package Yii2Debug
 */
class Yii2DebugViewPanel extends Yii2DebugPanel
{
    private $_viewRenderer;

    public function __construct()
    {
        $this->_viewRenderer = Yii::app()->getComponent('viewRenderer');
    }

    /**
     * @return string название панели для вывода в меню
     */
    public function getTitle()
    {
        return 'Views rendering';
    }

    /**
     * @return string html-контент для вывода в дебаг-панель
     */
    public function getSummary()
    {
        $url = $this->getUrl();

        return Yii::app()->controller->renderPartial('panels/_viewSummary', array(
            'url' => $url,
            'viewsCount' => $this->_getViewsCount(),
        ));
    }

    /**
     * @return string html-контент для вывода на страницу
     */
    public function getDetails()
    {
        $output = '';
        foreach ((array)$this->data as $viewRecord) {
            $header = $viewRecord['context'] . ' <small>' . $viewRecord['sourceFile'] . '</small>';
            unset($viewRecord['context'], $viewRecord['sourceFile']);
            $output .= $this->_renderDetails($header, $viewRecord);
        }

        return $output;
    }

    /**
     * Базовый метод для сбора отладочной информации
     * @return mixed
     */
    public function getDataToSave()
    {
        $data = array();
        $viewRenderer = $this->_viewRenderer;

        if ($viewRenderer instanceof Yii2DebugViewRenderer) {
            $data = $this->_viewRenderer->debugStackTrace;
        }

        return $data;
    }

    protected function _getViewsCount()
    {
        return count($this->data);
    }
}
