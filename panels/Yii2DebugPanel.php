<?php
/**
 * Yii2DebugPanel - базовый класс для страниц с отладочной информацией.
 * Он определяет как информация будет сохраняться и выводиться на просмотр.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @package Yii2Debug
 * @since 1.1.13
 */
abstract class Yii2DebugPanel extends CComponent
{
    /**
     * @var string id страницы
     */
    public $id;
    /**
     * @var string метка для просмотра информации
     */
    public $tag;
    /**
     * @var Yii2Debug
     */
    public $component;
    /**
     * @var array массив отладочных данных
     */
    public $data;
    /**
     * @var bool|null подчветка кода. По умолчанию Yii2Debug::$highlightCode
     */
    public $highlightCode;

    /**
     * @var CTextHighlighter
     */
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
        return Yii::app()->createUrl($this->component->moduleId . '/default/view', array(
            'panel' => $this->id,
            'tag' => $this->tag,
        ));
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
        if (empty($values)) {
            return '<h3>' . $caption . '</h3>' . PHP_EOL . '<p>Empty.</p>';
        }
        $rows = '';
        foreach ($values as $name => $value) {
            if (is_string($value)) {
                $value = CHtml::encode($value);
            } elseif ($this->highlightCode) {
                $value = '<pre class="pre-scrollable yii2-debug-pre">' . $this->_highlightPhp(Yii2DebugVarExporter::export($value)) . '</pre>';
            } else {
                $value = '<pre class="pre-scrollable yii2-debug-pre">' . CHtml::encode(var_export($value, true)) . '</pre>';
            }
            $rows .= '<tr><th style="width:300px;word-break:break-all;">'
                     . CHtml::encode($name)
                     . '</th><td><div style="overflow:auto">'
                     . $value
                     . '</div></td></tr>';
        }

        return <<<HTML
<h3>$caption</h3>
<table class="table table-condensed table-bordered table-striped table-hover" style="table-layout: fixed;">
<thead><tr><th style="width: 300px;">Name</th><th>Value</th></tr></thead>
<tbody>
$rows
</tbody>
</table>
HTML;
    }

    /**
     * Подсветка php-кода
     *
     * @param string $code
     *
     * @return string
     */
    protected function _highlightPhp($code)
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
     * Рендер панели с закладками
     * @param array $items
     *
     * @return string
     */
    protected function _renderTabs($items)
    {
        static $counter = 0;
        $counter++;
        $id = 'tabs' . $counter;

        $tabs = '';
        foreach ($items as $num => $item) {
            $tabs .= CHtml::tag('li', array(
                    'class' => isset($item['active']) && $item['active'] ? 'active' : ''
                ), CHtml::link($item['label'], '#' . $id . '-tab' . $num, array('data-toggle' => 'tab'))
            );
        }

        $details = '';
        foreach ($items as $num => $item) {
            $details .= CHtml::tag('div', array(
                    'id' => $id . '-tab' . $num,
                    'class' => 'tab-pane' . (isset($item['active']) && $item['active'] ? ' active' : ''),
                ), $item['content']
            );
        }

        return <<<HTML
<ul id="tabs{$counter}" class="nav nav-tabs">$tabs</ul>
<div class="tab-content">$details</div>
HTML;
    }
}
