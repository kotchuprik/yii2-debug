<?php
/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @property Yii2Debug $debugComponent
 *
 * @package Yii2Debug
 */
class Yii2DebugModule extends CWebModule
{
    const PACKAGE_ID = 'yii2-debug';
    const BOOTSTRAP_PACKAGE_ID = 'yii2-debug-bootstrap';

    public $debugComponent;

    protected function init()
    {
        parent::init();

        $this->_registerScripts();
    }

    public function beforeControllerAction($controller, $action)
    {
        if (parent::beforeControllerAction($controller, $action) && $this->debugComponent->checkAccess()) {
            // Отключение дебагера на страницах просмотра ранее сохраненных логов
            Yii::app()->detachEventHandler('onEndRequest', array($this->debugComponent, '_onEndRequest'));

            return true;
        }

        return false;
    }

    protected function _registerScripts()
    {
        /** @var CClientScript $clientScript */
        $clientScript = Yii::app()->getClientScript();

        $packageBootstrap = array(
            'baseUrl' => '//netdna.bootstrapcdn.com/bootstrap/3.0.0/',
            'js' => array(
                'js/bootstrap.min.js',
            ),
            'css' => array(
                'css/bootstrap.min.css',
            ),
        );
        $clientScript->addPackage(self::BOOTSTRAP_PACKAGE_ID, $packageBootstrap);

        $package = array(
            'baseUrl' => $this->_getAssetsUrl(),
            'css' => array(
                'css/main.css',
            ),
            'depends' => array('jquery', self::BOOTSTRAP_PACKAGE_ID),
        );

        $clientScript->addPackage(self::PACKAGE_ID, $package)->registerPackage(self::PACKAGE_ID);
    }

    protected function _getAssetsUrl()
    {
        return Yii::app()->getAssetManager()->publish(dirname(__FILE__) . '/assets');
    }
}
