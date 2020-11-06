<?php

namespace UserInput;

use Iterator;
use Exception;
use ReflectionClass;


class Sanitizer implements Iterator {

    private $typeInput = null;
    private $typeFilter = UserInput::RAW;
    private $data = [];
    private $dataIterator = [];
    private $iteratorIdx = 0;

    public function __construct($typeInput) {
        $this->typeInput = $typeInput;
        $this->refreshDataIterator();
    }

    /**
     * Interator implement rewind
     *
     * @return void
     */
    public function rewind() {
        $this->iteratorIdx = 0;
    }

    /**
     * Interator implement next
     *
     * @return void
     */
    public function next() {
        $this->iteratorIdx++;
    }

    /**
     * Interator implement key
     *
     * @return array
     */
    public function key() {
        return $this->dataIterator[$this->iteratorIdx];
    }

    /**
     * Interator implement current
     *
     * @return array
     */
    public function current() {
        $key = $this->dataIterator[$this->iteratorIdx];
        return $this->__get($key);
    }

    /**
     * Interator implement valid
     *
     * @return int
     */
    public function valid() {
        return $this->iteratorIdx < count($this->dataIterator);
    }

    /**
     * Set filter type
     *
     * @param integer $typeFilter
     * @return void
     */
    public function setFilter(int $typeFilter) {
        $this->typeFilter = $typeFilter;
    }

    /**
     * get var code & sanitize him
     *
     * @param string $name
     * @return array
     */
    public function __get(string $name) {
        $varCode = $this->getVarCode($name);
        if (!isset($this->data[$varCode])) {
            $this->sanitize($name, $this->typeFilter);
        }
        return $this->data[$varCode];
    }

    /**
     * Set var and cached him
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value) {
        switch ($this->typeInput) {
            case INPUT_GET:
                $_GET[$name] = $value;
                break;
            case INPUT_POST:
                $_POST[$name] = $value;
                break;
            default:
                throw new Exception('Incorrect input type');
                break;
        }
        $this->cleanCacheData($name);
        $this->refreshDataIterator();
    }

    /**
     * Isset var
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name) {
        switch ($this->typeInput) {
            case INPUT_GET:
                return isset($_GET[$name]);
                break;
            case INPUT_POST:
                return isset($_POST[$name]);
                break;
            default:
                throw new Exception('Incorrect input type');
                break;
        }
    }

    /**
     * Unset a var
     *
     * @param string $name
     */
    public function __unset(string $name) {
        switch ($this->typeInput) {
            case INPUT_GET:
                unset($_GET[$name]);
                break;
            case INPUT_POST:
                unset($_POST[$name]);
                break;
            default:
                throw new Exception('Incorrect input type');
                break;
        }
        $this->cleanCacheData($name);
        $this->refreshDataIterator();
    }

    /**
     * Set all
     *
     * @param array $arr
     * @return void
     */
    public function setAll(array $arr) {
        $_GET = $arr;
        $this->cleanCacheData();
        $this->refreshDataIterator();
    }

    /**
     * Get all var
     *
     * @return array
     */
    public function getAll() {
        return iterator_to_array($this);
    }

    /**
     * Count var into an GET or a POST
     *
     * @return int
     */
    public function count() {
        switch ($this->typeInput) {
            case INPUT_GET:
                return count($_GET);
                break;
            case INPUT_POST:
                return count($_POST);
                break;
            default:
                throw new Exception('Incorrect input type');
                break;
        }
    }

    /**
     * Sanitize vars
     *
     * @param string $name
     * @param int $typeFilter
     * @return array
     */
    public function sanitize(string $name, int $typeFilter) {
        switch ($this->typeInput) {
            case INPUT_GET:
                $data = $_GET[$name];
                break;
            case INPUT_POST:
                $data = $_POST[$name];
                break;
            default:
                throw new Exception('Incorrect input type');
                break;
        }
        $data = $this->applyRecursiveTransform($data, $typeFilter);
        $varCode = $this->getVarCode($name);
        $this->data[$varCode] = $data;
    }

    /**
     * Apply tranform
     *
     * @param array|string $data
     * @param int $typeFilter
     * @return array|string
     */
    private function applyRecursiveTransform($data, int $typeFilter) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->applyRecursiveTransform($value, $typeFilter);
            }
        } else {
            $data = $this->applyTransform($data, $typeFilter);
        }
        return $data;
    }

    /**
     * Apply tranformation
     *
     * @param string $data
     * @param int $type
     * @return string
     */
    private function applyTransform(string $data, int $type) {
        switch($type) {
            case UserInput::RAW:
                break;
            case UserInput::FILTER_TXT:
                $data = filter_var($data, FILTER_SANITIZE_STRING); 
                break;
            case UserInput::FILTER_MAIL:
                $data =  filter_var($data, FILTER_SANITIZE_EMAIL);
                break;
            case UserInput::FILTER_URL:
                $data = filter_var($data, FILTER_SANITIZE_URL);
                break;
            case UserInput::FILTER_INT:
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT); 
                break;
            case UserInput::FILTER_FLOAT:
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;
            case UserInput::FILTER_SHELL:
                $data = escapeshellcmd($data);
                break;
            case UserInput::FILTER_HTML:
                $data = htmlentities($data);
                break;
            default:
                throw new Exception('Incorrect input type');
                break;
        }

        return $data;
    }

    /**
     * get variable code
     *
     * @param string $name
     * @param int|null $transfo
     * @return string
     */
    private function getVarCode($name, $transfo = null) {
        $transfo = is_null($transfo) ? $this->typeFilter : $transfo;
        return '--' . $transfo. '--' . $name;
    }

    /**
     * Clean cache data
     *
     * @param string $name
     * @return array
     */
    private function cleanCacheData($name = null) {
        if ($name !== null) {
            $ui = new ReflectionClass('Core\Input\UserInput');
            $transfos = $ui->getConstants();
            foreach ($transfos as $transfo) {
                $varCode = $this->getVarCode($name, $transfo);
                unset($this->data[$varCode]);
            }
        } else {
            $this->data = [];
        }
    }

    /**
     * Refresh data of iterator
     *
     * @return array
     */
    private function refreshDataIterator() {
        switch ($this->typeInput) {
            case INPUT_GET:
                $this->dataIterator = array_keys($_GET);
                break;
            case INPUT_POST:
            $this->dataIterator = array_keys($_POST);
                break;
            default:
                throw new Exception('Incorrect input type');
                break;
        }
    }

}
