<?php
/**
 * Основной компонент для подключения отладочной панели
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @property array $allowedIPs Список ip и масок, которым разрешен доступ к панели
 * @property array|Yii2DebugPanel[] $panels
 * @property string $logPath Путь для записи логов. По умолчанию /runtime/debug
 * @property int $historySize Максимальное кол-во логов
 * @property boolean $enabled
 * @property string $modelId Id модуля для просмотра отладочной информации
 * @property boolean $highlightCode Подсветка кода на страницах с отладочной информацией
 *
 * @package Yii2Debug
 */
class Yii2Debug extends CApplicationComponent
{
    public $allowedIPs = array('127.0.0.1', '::1');
    public $panels = array();
    public $logPath;
    public $historySize = 50;
    public $enabled = true;
    public $moduleId = 'debug';
    public $highlightCode = true;

    private $_tag;
    private $_proxyMap = array(
        'viewRenderer' => 'Yii2DebugViewRenderer'
    );

    /**
     * Генерируется уникальная метка страницы, подключается модуль просмотра,
     * устанавливается обработчик для сбора отладочной информации, регистрируются
     * скрипты для вывода дебаг-панели
     */
    public function init()
    {
        parent::init();

        if (!$this->enabled) {
            return null;
        }

        Yii::setPathOfAlias('yii2-debug', __DIR__);
        Yii::app()->setImport(array(
            'yii2-debug.*',
            'yii2-debug.panels.*',
            'yii2-debug.helpers.*',
            'yii2-debug.components.*',
        ));

        $this->_initProxyMap();

        if ($this->logPath === null) {
            $this->logPath = Yii::app()->getRuntimePath() . '/debug';
        }

        foreach (array_merge($this->corePanels(), $this->panels) as $id => $config) {
            $config['id'] = $id;
            $config['tag'] = $this->getTag();
            $config['debugComponent'] = $this;
            if (!isset($config['highlightCode'])) {
                $config['highlightCode'] = $this->highlightCode;
            }
            $this->panels[$id] = Yii::createComponent($config);
        }

        Yii::app()->setModules(array_merge(Yii::app()->getModules(), array(
            $this->moduleId => array(
                'class' => 'Yii2DebugModule',
                'debugComponent' => $this,
            ),
        )));
        Yii::app()->attachEventHandler('onEndRequest', array($this, '_onEndRequest'));

        $this->initToolbar();
    }

    /**
     * @return string метка текущей страницы
     */
    public function getTag()
    {
        if ($this->_tag === null) {
            $this->_tag = uniqid();
        }

        return $this->_tag;
    }

    /**
     * @return array страницы по умолчанию
     */
    public function corePanels()
    {
        return array(
            'config' => array(
                'class' => 'Yii2DebugConfigPanel',
            ),
            'phpinfo' => array(
                'class' => 'Yii2DebugPhpinfoPanel',
            ),
            'request' => array(
                'class' => 'Yii2DebugRequestPanel',
            ),
            'log' => array(
                'class' => 'Yii2DebugLogPanel',
            ),
            'profiling' => array(
                'class' => 'Yii2DebugProfilingPanel',
            ),
            'db' => array(
                'class' => 'Yii2DebugDbPanel',
            ),
            'views' => array(
                'class' => 'Yii2DebugViewPanel',
            ),
        );
    }

    /**
     * Регистрация скриптов для загрузки дебаг-панели
     */
    public function initToolbar()
    {
        //@TODO определение того, что мы в модуле находимся и нам не нужны эти скрипты
        if (!$this->checkAccess()) {
            return null;
        }

        /* @var CClientScript $cs */
        $cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');
        $url = Yii::app()->createUrl($this->moduleId . '/default/toolbar', array('tag' => $this->getTag()));
        $cs->registerScript(__CLASS__ . '#toolbar', <<<JS
(function($){
	$('<div>').appendTo('body').load('$url', function(){
		if (window.localStorage && localStorage.getItem('yii2-debug-toolbar') == 'minimized') {
			$('#yii2-debug-toolbar').hide();
			$('#yii2-debug-toolbar-min').show();
		} else {
			$('#yii2-debug-toolbar-min').hide();
			$('#yii2-debug-toolbar').show();
		}
		$('#yii2-debug-toolbar .yii2-debug-toolbar-toggler').click(function(){
			$('#yii2-debug-toolbar').hide();
			$('#yii2-debug-toolbar-min').show();
			if (window.localStorage) {
				localStorage.setItem('yii2-debug-toolbar', 'minimized');
			}
		});
		$('#yii2-debug-toolbar-min .yii2-debug-toolbar-toggler').click(function(){
			$('#yii2-debug-toolbar-min').hide();
			$('#yii2-debug-toolbar').show();
			if (window.localStorage) {
				localStorage.setItem('yii2-debug-toolbar', 'maximized');
			}
		});
	});
})(jQuery);
JS
        );
    }

    /**
     * Проверка доступа
     * @return bool
     */
    public function checkAccess()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip ||
                (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CEvent $event
     */
    protected function _onEndRequest($event)
    {
        $this->_processDebug();
    }

    protected function _initProxyMap()
    {
        foreach ($this->_proxyMap as $name => $class) {
            $instance = Yii::app()->getComponent($name);
            if ($instance !== null) {
                Yii::app()->setComponent($name, null);
            }
            $this->_proxyMap[$name] = array(
                'class' => $class,
                'instance' => $instance
            );
        }
        Yii::app()->setComponents($this->_proxyMap, false);
    }

    /**
     * Запись отладочной информации
     */
    protected function _processDebug()
    {
        $path = $this->logPath;
        if (!is_dir($path)) {
            mkdir($path);
        }

        $indexFile = $path . '/index.json';
        $manifest = array();
        if (is_file($indexFile)) {
            $manifest = json_decode(file_get_contents($indexFile), true);
        }
        $request = Yii::app()->getRequest();
        $manifest[$this->getTag()] = $summary = array(
            'tag' => $this->getTag(),
            'url' => $request->getHostInfo() . $request->getUrl(),
            'ajax' => $request->isAjaxRequest,
            'method' => isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET',
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'time' => time(),
        );
        $this->_resizeHistory($manifest);

        $dataFile = $path . '/' . $this->getTag() . '.json';
        $data = array();
        foreach ($this->panels as $panel) {
            $data[$panel->id] = $panel->getDataToSave();
            $panel->load($data[$panel->id]);
        }
        $data['summary'] = $summary;

        file_put_contents($dataFile, json_encode($data));
        file_put_contents($indexFile, json_encode($manifest));
    }

    /**
     * Удаление ранее сохраненных логов когда общее их кол-во больше historySize
     *
     * @param $manifest
     */
    protected function _resizeHistory(&$manifest)
    {
        if (count($manifest) > $this->historySize + 10) {
            $path = $this->logPath;
            $n = count($manifest) - $this->historySize;
            foreach (array_keys($manifest) as $tag) {
                @unlink($path . '/' . $tag . '.json');
                unset($manifest[$tag]);
                if (--$n <= 0) {
                    break;
                }
            }
        }
    }
}
