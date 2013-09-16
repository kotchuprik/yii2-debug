<?php
/**
 * Yii2DebugViewRenderer class file.
 *
 * Inspired by {@link https://github.com/malyshev/yii-debug-toolbar}
 *
 * @author Sergey Malyshev <malyshev.php@gmail.com>
 * @author Roman Domrachev <ligser@gmail.com>
 *
 * @package Yii2Debug
 */

class Yii2DebugViewRenderer extends Yii2DebugProxyComponent
{
    protected $abstract = array(
        'fileExtension' => '.php',
    );
    protected $_debugStackTrace = array();

    public function init()
    {
    }

    public function getDebugStackTrace()
    {
        return $this->_debugStackTrace;
    }

    public function renderFile($context, $sourceFile, $data, $return)
    {
        $this->collectDebugInfo($context, $sourceFile, $data);

        if ($this->getIsProxy()) {
            return $this->getInstance()->renderFile($context, $sourceFile, $data, $return);
        }

        return $context->renderInternal($sourceFile, $data, $return);
    }

    protected function collectDebugInfo($context, $sourceFile, $data)
    {
        if (is_array($data)) {
            array_walk_recursive(
                $data,
                function (&$value, $key) {
                    if (is_object($value)) {
                        $value = get_class($value) . '::class';
                    } else {
                        $value = $value;
                    }
                }
            );
        }

        $collectedData = array(
            'context' => get_class($context),
            'contextProperties' => get_object_vars($context),
            'sourceFile' => $sourceFile,
            'viewData' => $data,
        );

        if (is_object($this->getInstance())) {
            $collectedData['renderer'] = get_class($this->getInstance());
        }

        array_push($this->_debugStackTrace, $collectedData);
    }

    public function generateViewFile($sourceFile, $viewFile)
    {
        if ($this->getIsProxy() !== false) {
            return $this->getInstance()->generateViewFile($sourceFile, $viewFile);
        }
    }
}
