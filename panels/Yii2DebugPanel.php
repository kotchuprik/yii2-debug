<?php
/**
 * Yii2DebugPanel - базовый класс для страниц с отладочной информацией.
 * Он определяет как информация будет сохраняться и выводиться на просмотр.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @property string $id Id страницы
 * @property string $tag Метка для просмотра информации
 * @property Yii2Debug $debugComponent
 * @property array $data Массив отладочных данных
 * @property boolean Подсветка кода. По умолчанию Yii2Debug::$highlightCode
 *
 * @package Yii2Debug
 */
abstract class Yii2DebugPanel extends CComponent
{
    public $id;
    public $tag;
    public $debugComponent;
    public $data;
    public $highlightCode;

    /** @var CTextHighlighter */
    private $_hl;

    /**
     * @return string название панели для вывода в меню
     */
    abstract public function getTitle();

    /**
     * @return string html-контент для вывода в дебаг-панель
     */
    abstract public function getSummary();

    /**
     * @return string html-контент для вывода на страницу
     */
    abstract public function getDetails();

    /**
     * Базовый метод для сбора отладочной информации
     * @return mixed
     */
    abstract public function getDataToSave();

    public function load($data)
    {
        $this->data = $data;
    }

    /**
     * @return string URL страницы
     */
    public function getUrl()
    {
        return Yii::app()->createUrl($this->debugComponent->moduleId . '/default/view', array(
            'panel' => $this->id,
            'tag' => $this->tag,
        ));
    }

    /**
     * Подсветка php-кода
     *
     * @param string $code
     *
     * @return string
     */
    public function highlightPhp($code)
    {
        if ($this->_hl === null) {
            $this->_hl = Yii::createComponent(array(
                'class' => 'CTextHighlighter',
                'language' => 'php',
                'showLineNumbers' => false,
            ));
        }
        $html = $this->_hl->highlight($code);

        return strip_tags($html, '<span>');
    }

    /**
     * Рендер блока с массивом key-value
     *
     * @param string $caption
     * @param array $values
     *
     * @return string
     */
    protected function _renderDetails($caption, $values)
    {
        return Yii::app()->controller->renderPartial('panels/_details', array(
            'debugPanel' => $this,
            'caption' => $caption,
            'values' => $values,
        ), true);
    }

    /**
     * Рендер панели с закладками
     * @param array $items
     *
     * @return string
     */
    protected function _renderTabs(array $items)
    {
        return Yii::app()->controller->renderPartial('panels/_tabs', array(
            'items' => $items,
        ), true);
    }
}
