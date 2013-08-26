<?php

/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class DefaultController extends CController
{
    public $layout = 'main';
    public $summary;

    /**
     * @return Yii2Debug
     */
    public function getComponent()
    {
        return $this->getModule()->component;
    }

    /**
     * Общий список логов
     */
    public function actionIndex()
    {
        $this->render('index', array(
            'manifest' => $this->getManifest(),
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
            $tags = array_keys($this->getManifest());
            $tag = reset($tags);
        }
        $this->loadData($tag);
        if (isset($this->component->panels[$panel])) {
            $activePanel = $this->getComponent()->panels[$panel];
        } else {
            $activePanel = $this->getComponent()->panels['request'];
        }
        $this->render('view', array(
            'tag' => $tag,
            'summary' => $this->summary,
            'manifest' => $this->getManifest(),
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
        $this->loadData($tag);
        $this->renderPartial('toolbar', array(
            'tag' => $tag,
            'panels' => $this->getComponent()->panels,
        ));
    }

    public function actionPhpinfo()
    {
        phpinfo();
    }

    private $_manifest;

    protected function getManifest()
    {
        if ($this->_manifest === null) {
            $path = $this->getComponent()->logPath;
            $indexFile = "$path/index.json";
            if (is_file($indexFile)) {
                $this->_manifest = array_reverse(json_decode(file_get_contents($indexFile), true), true);
            } else {
                $this->_manifest = array();
            }
        }

        return $this->_manifest;
    }

    protected function loadData($tag)
    {
        $manifest = $this->getManifest();
        if (isset($manifest[$tag])) {
            $path = $this->getComponent()->logPath;
            $dataFile = "$path/$tag.json";
            $data = json_decode(file_get_contents($dataFile), true);
            foreach ($this->getComponent()->panels as $id => $panel) {
                if (isset($data[$id])) {
                    $panel->tag = $tag;
                    $panel->load($data[$id]);
                } else {
                    // remove the panel since it has not received any data
                    unset($this->getComponent()->panels[$id]);
                }
            }
            $this->summary = $data['summary'];
        } else {
            throw new CHttpException(404, "Unable to find debug data tagged with '$tag'.");
        }
    }
}
