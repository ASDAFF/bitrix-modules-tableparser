<?php

class tableParser {

    private $parserObj;

    private function getFileNameAndExt($file) {
        if(!$file) {
            return false;
        }
        if(is_array($file)) { 
            $path_parts = pathinfo($file["name"]); 
            $ext = $path_parts['extension'];  
            $fileName = $file["tmp_name"];
        } else {
            $path_parts = pathinfo($file); 
            $ext = $path_parts['extension'];  
            $fileName = $file;
        } 
        $ext = strtolower($ext);
        return array($fileName, $ext);
    }
    
    function __construct($file) { 
        if($file) {
            list($fileName, $ext) = $this->getFileNameAndExt($file);  
            if(in_array($ext, array('csv', 'txt'))) {
                 include_once 'parsers/' . $ext . '/include.php';
                 $className = $ext . 'TableParser';
                 $this->parserObj = new $className($fileName);  
                 $this->parserObj->read();
            }
        } else {
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
        return $this->Fetch();
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