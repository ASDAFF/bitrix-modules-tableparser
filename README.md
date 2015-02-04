# bitrix-modules-tableparser
Парсеры прайсов


Пример работы:
<pre>   
    if($_FILES['file']["size"]) { 
        CModule::IncludeModule('tableParser'); 
        $parser = new tableParser($_FILES['file']); 
        while($row = $parser->Fetch()) { 
            var_dump($row);
        }
    }  

    // можно получить название 
    $name = $parser->getTableName(); 
 
    // и инициализировать им для чтения
    $parser = new tableParser();
    $parser->init($name);   
</pre>