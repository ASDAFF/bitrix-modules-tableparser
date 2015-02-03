<?php

include_once 'ycsvParser.php';

class csvTableParser extends abstractParser {
   
    function read() { 
        $ycsv = new ycsvParser($this->filename, false); 
        while ($record = $ycsv->getRecord()) {
            $res = $ycsv->parseRecord($record);
            $arr = array();
            $n = 0;
            foreach ($res as $row) {
                $arr[$n++] = $row;
            }
            $this->write($arr);
        }
        $ycsv->close(); 
    } 
    
}
