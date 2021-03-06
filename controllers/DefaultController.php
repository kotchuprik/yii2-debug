<?php
/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @package Yii2Debug
 */
class DefaultController extends CController
{
    public $layout = 'main';
    public $summary;
    public $pageTitle = 'Yii Debugger';

    private $_manifest;

    /**
     * @return Yii2Debug
     */
    public function getComponent()
    {
        return $this->getModule()->debugComponent;
    }

    /**
     * Общий список логов
     */
    public function actionIndex()
    {
        $this->render('index', array(
            'manifest' => $this->_getManifest(),
        ));
    }

    /**
     * Страница для просмотра отладочной информации
     * @param null $tag сохраненного лога
     * @param null $panel id страницы
     */
    public function actionView($tag = null, $panel = null)
    {
        if ($tag === null) {
            $tags = array_keys($this->_getManifest());
            $tag = reset($tags);
        }
        $this->_loadData($tag);
        if (isset($this->getComponent()->panels[$panel])) {
            $activePanel = $this->getComponent()->panels[$panel];
        } else {
            $activePanel = $this->getComponent()->panels['request'];
        }
        $this->render('view', array(
            'tag' => $tag,
            'summary' => $this->summary,
            'manifest' => $this->_getManifest(),
            'panels' => $this->getComponent()->panels,
            'activePanel' => $activePanel,
        ));
    }

    /**
     * Генерирует код дебаг-панели по ajax-запросу
     *
     * @param $tag
     */
    public function actionToolbar($tag)
    {
        $this->_loadData($tag);

        $this->renderPartial('_toolbar', array(
            'panels' => $this->getComponent()->panels,
        ));
    }

    public function actionPhpinfo($tag = null)
    {
        if ($tag === null) {
            $tags = array_keys($this->_getManifest());
            $tag = reset($tags);
        }
        $this->_loadData($tag);
        $activePanel = $this->getComponent()->panels['request'];

        $this->render('phpinfo', array(
            'tag' => $tag,
            'panels' => $this->getComponent()->panels,
            'summary' => $this->summary,
            'manifest' => $this->_getManifest(),
            'activePanel' => $activePanel,
        ));
    }

    protected function _getManifest()
    {
        if ($this->_manifest === null) {
            $path = $this->getComponent()->logPath;
            $indexFile = $path . '/index.json';
            if (is_file($indexFile)) {
                $this->_manifest = array_reverse(json_decode(file_get_contents($indexFile), true), true);
            } else {
                $this->_manifest = array();
            }
        }

        return $this->_manifest;
    }

    protected function _loadData($tag)
    {
        $manifest = $this->_getManifest();
        if (isset($manifest[$tag])) {
            $path = $this->getComponent()->logPath;
            $dataFile = $path . '/' . $tag . '.json';
            $data = json_decode(file_get_contents($dataFile), true);

            foreach ($this->getComponent()->panels as $id => $panel) {
                // If array data isset, but is null
                if (array_key_exists($id, $data)) {
                    $panel->tag = $tag;
                    $panel->load($data[$id]);
                } else {
                    // Remove the panel since it has not received any data
                    unset($this->getComponent()->panels[$id]);
                }
            }
            $this->summary = $data['summary'];
        } else {
            throw new CHttpException(404, 'Unable to find debug data tagged with \'' . $tag . '\'.');
        }
    }
}
