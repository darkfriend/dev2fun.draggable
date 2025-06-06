<?php
/**
 * @author darkfriend <hi@darkfriend.ru>
 * @copyright dev2fun
 * @version 0.1.0
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
if(!check_bitrix_sessid()) {
    return;
}

use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

Loader::includeModule('main');

CAdminMessage::ShowMessage([
    "MESSAGE" => Loc::getMessage('D2F_DRAGGABLE_INSTALL_SUCCESS'),
    "TYPE" => "OK",
]);
echo BeginNote();
echo Loc::getMessage("D2F_DRAGGABLE_INSTALL_LAST_MSG");
echo EndNote();