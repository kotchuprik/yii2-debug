<?php
/**
 * Yii2DebugProxyComponent class file.
 *
 * Inspired by {@link https://github.com/malyshev/yii-debug-toolbar}
 *
 * @author Sergey Malyshev <malyshev.php@gmail.com>
 * @author Roman Domrachev <ligser@gmail.com>
 *
 * @package Yii2Debug
 */
class Yii2DebugProxyComponent extends CComponent
{
    protected $_abstracts = array();
    private $_instance;
    private $_isProxy;

    public function init()
    {
        // Yii's magic OOP
    }

    public function getInstance()
    {
        return $this->_instance;
    }

    public function setInstance($value)
    {
        if ($this->_instance === null && is_object($value)) {
            $this->_abstracts = array_merge($this->_abstracts, get_object_vars($value));
            $this->_instance = $value;
        }
    }

    public function  __call($name, $parameters)
    {
        if ($this->getIsProxy() && method_exists($this->_instance, $name)) {
            return call_user_func_array(array($this->_instance, $name), $parameters);
        }

        return parent::__call($name, $parameters);
    }

    public function getIsProxy()
    {
        if ($this->_isProxy === null) {
            $this->_isProxy = ($this->_instance !== null && !($this->_instance instanceof $this));
        }

        return $this->_isProxy;
    }

    public function  __get($name)
    {
        $getter = 'get' . $name;

        if (method_exists($this, $getter)) {
            return call_user_func(array($this, $getter));
        } else {
            if (property_exists($this, $name)) {
                return $this->$name;
            } elseif ($this->getIsProxy() === false && array_key_exists($name, $this->_abstracts)) {
                return $this->_abstracts[$name];
            } elseif ($this->getIsProxy()) {
                return $this->_instance->$name;
            }
        }

        return parent::__get($name);
    }

    public function  __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            return call_user_func_array(array($this, $setter), array($value));
        } elseif (property_exists($this, $name)) {
            return $this->$name = $value;
        } elseif ($this->getIsProxy() === false && array_key_exists($name, $this->_abstracts)) {
            return $this->_abstracts[$name] = $value;
        } elseif ($this->getIsProxy()) {
            return $this->_instance->$name = $value;
        }

        return parent::__set($name, $value);
    }
}
