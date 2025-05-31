<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright (c) 2025, darkfriend
 * @version 0.1.0
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

IncludeModuleLangFile(__FILE__);

if (class_exists("dev2fun_draggable")) {
    return;
}

use Bitrix\Main\ModuleManager,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager,
    Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    "dev2fun.draggable",
    [
        "Dev2funDraggableModule" => 'include.php',
    ]
);

class dev2fun_draggable extends CModule
{
    var $MODULE_ID = "dev2fun.draggable";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "Y";

    public function __construct()
    {
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        } else {
            $this->MODULE_VERSION = '1.0.0';
            $this->MODULE_VERSION_DATE = '2025-06-01 01:00:00';
        }
        $this->MODULE_NAME = Loc::getMessage("DEV2FUN_DRAGGABLE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("DEV2FUN_DRAGGABLE_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = "dev2fun";
        $this->PARTNER_URI = "https://dev2fun.com";
    }

    /**
     * @return void
     */
    public function DoInstall()
    {
        global $APPLICATION;
        if (!check_bitrix_sessid()) {
            return;
        }

        try {
            $this->registerEvents();

            ModuleManager::registerModule($this->MODULE_ID);

        } catch (Exception $e) {
            $GLOBALS['D2F_DRAGGABLE_ERROR'] = $e->getMessage();
            $GLOBALS['D2F_DRAGGABLE_ERROR_NOTES'] = Loc::getMessage('D2F_DRAGGABLE_ERROR_CHECK_NOFOUND_NOTES');
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("D2F_MODULE_DRAGGABLE_STEP_ERROR"),
                __DIR__ . "/error.php"
            );
            return;
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("D2F_MODULE_DRAGGABLE_STEP_FINAL"),
            __DIR__ . "/final.php"
        );
    }

    /**
     * @return void
     */
    public function DoUninstall()
    {
        global $APPLICATION;
        if (!check_bitrix_sessid()) {
            return;
        }

        $this->unRegisterEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        CAdminMessage::ShowMessage([
            "MESSAGE" => Loc::getMessage('D2F_DRAGGABLE_UNINSTALL_SUCCESS'),
            "TYPE" => "OK",
        ]);
        echo BeginNote();
        echo Loc::getMessage("D2F_DRAGGABLE_UNINSTALL_LAST_MSG");
        echo EndNote();
    }

    /**
     * @return void
     */
    public function registerEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            "main",
            "OnProlog",
            $this->MODULE_ID,
            "Dev2funDraggableModule",
            "onPrologEvent"
        );
    }

    /**
     * @return void
     */
    public function unRegisterEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler('main', 'OnProlog', $this->MODULE_ID);
    }
}
