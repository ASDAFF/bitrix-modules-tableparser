<?php

class tableParser {

    function __construct($file) { 
        $info = new SplFileInfo($file);
        $ext = strtolower($info->getExtension()); 
        switch ($ext) {
            case 'csv': 
                include_once 'parsers/csv/include.php';
                $parser = new csvTableParser($file);
                break;       
            default:
                return false;
                break;
        } 
        $parser->read();
        return $parser;
    }

}

abstract class abstractParser {
    
    private $lastRow;
    protected $tablename;
    private $filename;
    private $parsed = false;
    static $cnt;

    function __construct($file) {
        $this->filename = $file;
        self::$cnt++;
        $this->tablename = COption::GetOptionString('tableparser', 'tableprefix', 'parser_');
        $this->tablename.=self::$cnt;
        global $DB;
        $DB->Query("DROP TABLE IF EXISTS `{$this->tablename}`");
        $DB->Query("CREATE TABLE `{$this->tablename}` ( "
                 . "   `ID` int(11) NOT NULL AUTO_INCREMENT, "
                 . "   `DATA` text NOT NULL, PRIMARY KEY (`ID`) "
                 . ") ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1; "); 
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
        $result = $DB->Query("SELECT FROM `{$this->tablename}` "
                           . "WHERE ID > {$this->lastRow} "
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
