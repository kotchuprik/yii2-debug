<?php
/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 * @package Yii2Debug
 * @since 1.1.13
 */
class Yii2DebugModule extends CWebModule
{
    const PACKAGE_ID = 'yii2-debug';

    /**
     * @var Yii2Debug
     */
    public $component;

    protected function init()
    {
        parent::init();

        $this->_registerScripts();
    }

    public function beforeControllerAction($controller, $action)
    {
        if (parent::beforeControllerAction($controller, $action) && $this->component->checkAccess()) {
            // Отключение дебагера на страницах просмотра ранее сохраненных логов
            Yii::app()->detachEventHandler('onEndRequest', array($this->component, '_onEndRequest'));

            return true;
        }

        return false;
    }

    protected function _registerScripts()
    {
        $package = array(
            'baseUrl' => $this->_getAssetsUrl(),
            'js' => array(
                YII_DEBUG ? 'js/bootstrap.js' : 'js/bootstrap.min.js',
                'js/filter.js',
            ),
            'css' => array(
                YII_DEBUG ? 'css/bootstrap.css' : 'css/bootstrap.min.css',
                'css/main.css',
            ),
            'depends' => array('jquery'),
        );

        Yii::app()->getClientScript()->addPackage(self::PACKAGE_ID, $package)->registerPackage(self::PACKAGE_ID);
    }

    protected function _getAssetsUrl()
    {
        return Yii::app()->getAssetManager()->publish(dirname(__FILE__) . '/assets');
    }
}
