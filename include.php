<?php

class tableParser {

    private $parserObj;

    private function getFileInfo($file) {
        switch (true) {
            case !$file:
                return false;
                break;
            case is_array($file):
                $path_parts = pathinfo($file["name"]);
                $ext = $path_parts['extension'];
                $fileName = $file["tmp_name"];
                break;
            case file_exists($file):
                $path_parts = pathinfo($file);
                $ext = $path_parts['extension'];
                $fileName = $file;
                break;
            case is_numeric($file):
                $file_info = CFile::GetFileArray($file);
                if (is_array($file_info)) {
                    return $this->getFileInfo($_SERVER['DOCUMENT_ROOT'] . $file_info['SRC']);
                }
                break;
            default:
                $path_parts = pathinfo($file);
                $fileName = ini_get('upload_tmp_dir') . '/' . $path_parts["basename"];
                file_put_contents($fileName, file_get_contents($file));
                return $this->getFileInfo($fileName);
                break;
        }
        $ext = strtolower($ext);
        return array('FILE_NAME' => $fileName, 'EXT' => $ext);
    }

    function __construct($file) { 
        if($file) { 
            $fileinfo = $this->getFileInfo($file);  
            $fileName = $fileinfo['FILE_NAME'];
            $ext = $fileinfo['EXT'];
            if(in_array($ext, array('csv', 'txt'))) {
                 include_once 'parsers/' . $ext . '/include.php';
                 $className = $ext . 'TableParser';
                 $this->parserObj = new $className($fileName);  
                 $this->parserObj->read();
            }
        }
        if(!is_object($this->parserObj)) {
            $this->parserObj = new allTableParser();
        }
    }

    function __call($name, $arguments) { 
        if(method_exists($this->parserObj, $name)) { 
            return $this->parserObj->$name($arguments[0], $arguments[1]); 
        }
    }

}

/*
    Парсеры всех типов файлов наследуются от этого класса
    чтобы всё зафурыкало нужно переопределить метод read(), в котором
    передать в цикле методу write($arr) каждую строку таблицы
 */

abstract class abstractParser {

    protected $lastRow;
    protected $tablename;
    protected $filename;
    protected $parsed = false;
    protected $firstRow;
    static $cnt;

    function __construct($file) {   
        $this->filename = $file;
        self::$cnt++;
        $this->tablename = COption::GetOptionString('tableparser', 'tableprefix', 'parser_');
        $this->tablename.=self::$cnt;
        global $DB;
        $DB->Query("DROP TABLE IF EXISTS `{$this->tablename}`");
        $DB->Query("CREATE TABLE `{$this->tablename}` ( "
                 . "   `ID` int(11) NOT NULL AUTO_INCREMENT,"
                 . "   `DATA` text NOT NULL, PRIMARY KEY (`ID`)"
                 . ") ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");  
    }

    public function init($table) { 
        $this->tablename = $table;
    }

    public function getTableName() {
        return $this->tablename;
    }

    private function clearTable() {
        global $DB;
        $DB->Query("DELETE FROM `{$this->tablename}`");
    }

    abstract function read();

    protected function write($arr) {
        global $DB;
        $data = serialize($arr); 
        $DB->Query("INSERT INTO `{$this->tablename}` (`ID`, `DATA`) "
                 . "VALUES (NULL, '".$DB->ForSql($data)."');");
    }

    public function GetNext() { 
        if(!$this->lastRow) {
            $this->lastRow = 1;
        }
        if(!$this->firstRow) {
            global $DB;
            $result = $DB->Query("SELECT `DATA` FROM `{$this->tablename}` WHERE `ID` = 1");
            if($row = $result->Fetch()) {
                $this->firstRow = unserialize($row['DATA']);
            } else {
                return false;
            }
        }
        if($arr = $this->Fetch()) {  
            return array_combine($this->firstRow, $arr); 
        } else {
            return false;
        }  
    } 
    
    public function Fetch() {
        global $DB;
        if(!$this->lastRow) {
            $this->lastRow = 0;
        }
        $result = $DB->Query("SELECT `DATA` FROM `{$this->tablename}` "
                           . "WHERE `ID` > {$this->lastRow} "
                           . "ORDER BY `ID` ASC "
                           . "LIMIT 1");
        if($row = $result->Fetch()) {
            $data = unserialize($row['DATA']);
            $this->lastRow++;
            return $data;
        } else {
            return false;
        }
    }

}

/*
    Класс для чтения с таблиц
    он ничего сам не читает с файлов при создании
*/
class allTableParser extends abstractParser {

    function __construct() {
        return false;
    }

    function read() {
        return false;   
    }

}