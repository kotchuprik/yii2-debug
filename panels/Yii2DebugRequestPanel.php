<?php
/**
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @author Constantin Chuprik <constantinchuprik@gmail.com>
 *
 * @package Yii2Debug
 */
class Yii2DebugRequestPanel extends Yii2DebugPanel
{
    protected $_statusCode;

    public function __construct()
    {
        if (!function_exists('http_response_code')) {
            Yii::app()->attachEventHandler('onException', array($this, '_onException'));
        }
    }

    public function getTitle()
    {
        return 'Request';
    }

    public function getSummary()
    {
        $statusCode = $this->data['statusCode'];
        if ($statusCode >= 200 && $statusCode < 300) {
            $class = 'label-success';
        } elseif ($statusCode >= 100 && $statusCode < 200) {
            $class = 'label-info';
        } else {
            $class = 'label-danger';
        }

        return Yii::app()->controller->renderPartial('panels/_requestSummary', array(
            'url' => $this->getUrl(),
            'statusCode' => $statusCode,
            'class' => $class,
            'action' => $this->data['action'],
            'tag' => $this->tag,
        ));
    }

    public function getDetails()
    {
        $data = array(
            'Route' => $this->data['route'],
            'Action' => $this->data['action'],
            'Parameters' => $this->data['actionParams'],
        );

        return $this->_renderTabs(array(
            array(
                'label' => 'Parameters',
                'content' => $this->_renderDetails('Routing', $data)
                             . $this->_renderDetails('$_GET', $this->data['GET'])
                             . $this->_renderDetails('$_POST', $this->data['POST'])
                             . $this->_renderDetails('$_FILES', $this->data['FILES'])
                             . $this->_renderDetails('$_COOKIE', $this->data['COOKIE']),
                'active' => true,
            ),
            array(
                'label' => 'Headers',
                'content' => $this->_renderDetails('Request Headers', $this->data['requestHeaders'])
                             . $this->_renderDetails('Response Headers', $this->data['responseHeaders']),
            ),
            array(
                'label' => 'Session',
                'content' => $this->_renderDetails('$_SESSION', $this->data['SESSION'])
                             . $this->_renderDetails('Flashes', $this->data['flashes']),
            ),
            array(
                'label' => '$_SERVER',
                'content' => $this->_renderDetails('$_SERVER', $this->data['SERVER']),
            ),
        ));
    }

    public function getDataToSave()
    {
        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
        } elseif (function_exists('http_get_request_headers')) {
            $requestHeaders = http_get_request_headers();
        } else {
            $requestHeaders = array();
        }

        $responseHeaders = array();
        foreach (headers_list() as $header) {
            if (($pos = strpos($header, ':')) !== false) {
                $name = substr($header, 0, $pos);
                $value = trim(substr($header, $pos + 1));
                if (isset($responseHeaders[$name])) {
                    if (!is_array($responseHeaders[$name])) {
                        $responseHeaders[$name] = array($responseHeaders[$name], $value);
                    } else {
                        $responseHeaders[$name][] = $value;
                    }
                } else {
                    $responseHeaders[$name] = $value;
                }
            } else {
                $responseHeaders[] = $header;
            }
        }

        $route = Yii::app()->getUrlManager()->parseUrl(Yii::app()->getRequest());
        $action = null;
        $actionParams = array();
        if (($ca = Yii::app()->createController($route)) !== null) {
            /* @var CController $controller */
            list($controller, $actionID) = $ca;
            if (empty($actionID)) {
                $actionID = $controller->defaultAction;
            }
            if (($a = $controller->createAction($actionID)) !== null) {
                if ($a instanceof CInlineAction) {
                    $action = get_class($controller) . '::action' . ucfirst($actionID) . '()';
                } else {
                    $action = get_class($a) . '::run()';
                }
            }
            $actionParams = $controller->actionParams;
        }

        /* @var CWebUser $user */
        $user = Yii::app()->getComponent('user', false);

        return array(
            'flashes' => $user ? $user->getFlashes(false) : array(),
            'statusCode' => $this->_getStatusCode(),
            'requestHeaders' => $requestHeaders,
            'responseHeaders' => $responseHeaders,
            'route' => $route,
            'action' => $action,
            'actionParams' => $actionParams,
            'SERVER' => empty($_SERVER) ? array() : $_SERVER,
            'GET' => empty($_GET) ? array() : $_GET,
            'POST' => empty($_POST) ? array() : $_POST,
            'COOKIE' => empty($_COOKIE) ? array() : $_COOKIE,
            'FILES' => empty($_FILES) ? array() : $_FILES,
            'SESSION' => empty($_SESSION) ? array() : $_SESSION,
        );
    }

    /**
     * @return int|null
     */
    protected function _getStatusCode()
    {
        if (function_exists('http_response_code')) {
            return http_response_code();
        }

        return $this->_statusCode;
    }

    /**
     * @param CExceptionEvent $event
     */
    protected function _onException($event)
    {
        if ($event->exception instanceof CHttpException) {
            $this->_statusCode = $event->exception->statusCode;
        } else {
            $this->_statusCode = 500;
        }
    }
}
