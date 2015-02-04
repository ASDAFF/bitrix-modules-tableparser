<?php

/*
   Данные, разделённые знаком табуляции
 */
class txtTableParser extends abstractParser {
   
    function read() {
        $data = file_get_contents($this->filename);
        $cols = explode("\n", $data);
        foreach($cols as $row) {
            $arr = explode("\t", $row);
            $this->write($arr);
        } 
    } 

}
