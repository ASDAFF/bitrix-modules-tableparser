Установка:
<pre>
cd site/bitrix/modules/ 
git clone https://github.com/kudin/bitrix-modules-tableparser tableparser 
</pre>
Пример работы:
<pre>
    if($_FILES['file']["size"]) { 
        CModule::IncludeModule('tableParser'); 
        $parser = new tableParser($_FILES['file']); 
        while($row = $parser->Fetch()) { // или $parser->GetNext(); вытянет с ключами соотв. 1 строке
            var_dump($row);
        }
    }  

    // можно получить название 
    $name = $parser->getTableName(); 
 
    // и инициализировать им для чтения
    $parser = new tableParser();
    $parser->init($name);   
</pre>
 