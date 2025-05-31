<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright (c) 2025, darkfriend
 * @version 0.1.0
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();
$curModuleName = 'dev2fun.draggable';

Loc::loadMessages(__FILE__);

$aTabs = [
    [
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('MAIN_TAB_SET'),
        'ICON' => 'main_settings',
        'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_SET'),
    ],
    [
        "DIV" => "donate",
        "TAB" => Loc::getMessage('D2F_DRAGDROP_SEC_DONATE_TAB'),
        "ICON" => "main_user_edit",
        "TITLE" => Loc::getMessage('D2F_DRAGDROP_SEC_DONATE_TAB_TITLE'),
    ],
];

$tabControl = new CAdminTabControl('tabControl', $aTabs);

if ($request->isPost() && check_bitrix_sessid()) {
    $enable = $request->getPost('common_options');
    Option::set($curModuleName, 'enable', $enable['enable'] ?? 'N');
}

$tabControl->Begin();
?>

<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.cards.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.grid.responsive.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/objects.containers.min.css">
<link rel="stylesheet" href="https://unpkg.com/blaze@4.0.0-6/scss/dist/components.tables.min.css">

<form
    method="post"
    action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>&<?= $tabControl->ActiveTabParam() ?>"
    enctype="multipart/form-data"
    name="editform"
    class="editform"
>
    <?php
    echo bitrix_sessid_post();
    $tabControl->BeginNextTab();
    ?>

    <tr>
        <td width="40%">
            <label for="enable_jpeg">
                <?= Loc::getMessage("D2F_DRAGDROP_ENABLE_LABEL") ?>:
            </label>
        </td>
        <td width="60%">
            <input
                type="checkbox"
                name="common_options[enable]"
                value="Y"
                <?php
                if (Option::get($curModuleName, "enable", 'N') === 'Y') {
                    echo 'checked';
                }
                ?>
            />
        </td>
    </tr>

    <?php include __DIR__.'/tabs/donate.php'?>

    <?php
    $tabControl->Buttons([
        "btnSave" => true,
        "btnApply" => true,
        "btnCancel" => true,
        "back_url" => $APPLICATION->GetCurUri(),
    ]);
    $tabControl->End();
    ?>
</form>