<?php

IncludeModuleLangFile(__FILE__);

class tableparser extends CModule {

    var $MODULE_ID = __CLASS__;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS;
    var $errors = array();

    function __construct() {
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = 'Парсеры прайсов';
        $this->MODULE_DESCRIPTION = 'Актуальную версию всегда можно забрать с <a href="http://github.com/kudin/bitrix-modules-tableparser">http://github.com/kudin/bitrix-modules-tableparser</a>';
    }

    function DoInstall() {
        RegisterModule($this->MODULE_ID);
        COption::SetOptionString('tableparser', 'tableprefix', 'parser_');
    }

    function DoUninstall() {
        UnRegisterModule($this->MODULE_ID);
    }

}
