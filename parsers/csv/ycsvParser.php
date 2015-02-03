<?php

class ycsvParser {

    var $delim;
    var $strDelim;
    var $handle = null;
    var $config = array();

    function __construct($fp, $configLine = false, $delim = ";", $strDelim = "\"") {
        $this->delim = $delim;
        $this->strDelim = $strDelim;
        if (!file_exists($fp) || !is_file($fp) || !is_readable($fp)) {
            return false;
        } 
        $handle = @fopen($fp, "r");
        if (!$handle) {
            return false;
        } 
        $this->handle = $handle;
        if ($configLine) {
            $buffer = $this->getRecord();
            $config = $this->parseRecord($buffer);
            $this->config = $config;
        }
        return true;
    }

    function parseRecord($buffer) {
        $buffer = trim($buffer);
        $buffer = str_replace($this->strDelim . $this->strDelim, '#|#', $buffer);
        $buffer = preg_replace(array('/' . $this->strDelim . '([ *])' . $this->delim . '/', '/' . $this->delim . '([ *])' . $this->strDelim . '/'), 
                               array($this->strDelim . $this->delim, $this->delim . $this->strDelim), $buffer);
        $res = array();
        while (strlen($buffer)) { 
            if ($buffer[0] == $this->strDelim) {
                $buffer_part = $this->explode($buffer, $this->strDelim . $this->delim);
                $buffer = $buffer_part[1];
                $res[] = (string) str_replace("#|#", $this->strDelim, trim($buffer_part[0], " " . $this->strDelim . $this->delim));
            } else {
                $buffer_part = $this->explode($buffer, $this->delim);
                $buffer = $buffer_part[1];
                $res[] = trim($buffer_part[0], " " . $this->delim);
            }
            if ($buffer_part[0] == $this->delim && $buffer_part[1] == "") {
                $res[] = "";
            }
        }
        return $res;
    }

    function getRecord() {
        $isRecord = false;
        $buffer = "";
        while (!feof($this->handle) && !$isRecord) {
            $buffer .= fgets($this->handle, 4096);
            if (substr_count($buffer, $this->strDelim) % 2 == 0) {
                $isRecord = true;                
            } 
        }
        if (empty($buffer)) {
            return false;
        }   
        return $buffer;
    }

    function close() {
        return fclose($this->handle);
    }

    function explode($string, $to) {
        $res = array();
        $p = strpos($string, $to);
        if ($p !== false) {
            $res[0] = substr($string, 0, $p + strlen($to));
            $res[1] = substr($string, $p + strlen($to));
        } else {
            $res[0] = $string;
            $res[1] = "";
        }
        return $res;
    }

}