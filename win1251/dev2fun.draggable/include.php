<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright (c) 2025, darkfriend
 * @version 0.1.0
 */
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

if (class_exists('Dev2funDraggableModule')) return;

class Dev2funDraggableModule
{
    const MODULE_ID = 'dev2fun.draggable';

    public static function onPrologEvent()
    {
        static::init();
    }

    /**
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function init()
    {
        global $APPLICATION;

//        if (strpos($APPLICATION->GetCurPage(), '/bitrix/admin/iblock_element_edit.php') === false) {
//            return;
//        }
        $supportPages = [
            '/bitrix/admin/iblock_element_edit.php',
//            '/bitrix/admin/iblock_section_edit.php',
        ];
        if (!in_array($APPLICATION->GetCurPage(), $supportPages)) {
            return;
        }

        $enabled = \Bitrix\Main\Config\Option::get(self::MODULE_ID, 'enable', 'N');
        if ($enabled === 'N') {
            return;
        }

        \Bitrix\Main\Loader::includeModule('iblock');

        $properties = static::getIBlockParameters();
//        $propertiesId = array_column($properties, 'ID');

        $scriptStr = [];
        foreach ($properties as $property) {

            if (empty($property['USER_TYPE'])) {
                $propType = 'input';
            } else {
                $propType = 'select';
            }

//            switch ($property['PROPERTY_TYPE']) {
//                case \Bitrix\Iblock\PropertyTable::TYPE_STRING:
//                case \Bitrix\Iblock\PropertyTable::TYPE_NUMBER:
//                    $propType = 'input';
//                    break;
//                case \Bitrix\Iblock\PropertyTable::TYPE_ELEMENT:
//                    $propType = 'select';
//                    break;
//            }
            $scriptStr[] = "globalInitDragAndDrop({$property['ID']}, '{$propType}')";
        }
        $scriptStr = implode("\n", $scriptStr);

//        $css = file_get_contents(__DIR__.'/css/style.css');
        $script = file_get_contents(__DIR__.'/js/script.js');
        echo <<<SCRIPT
            <script async defer>
                {$script}
                document.addEventListener('DOMContentLoaded', function() {
                    {$scriptStr}
                });
            </script>
        SCRIPT;

        //        echo "<style>{$css}</style>";
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getIBlocks(): array
    {
        return \Bitrix\Iblock\IblockTable::getList([
            'select' => [
                'ID', 'ACTIVE', 'NAME',
            ],
            'filter' => [
                'ACTIVE' => 'Y',
            ],
        ])->fetchAll();
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getIBlockParameters()
    {
        $properties = \Bitrix\Iblock\PropertyTable::getList([
//            'select' => [
//                'ID', 'NAME', 'PROPERTY_TYPE', 'USER_TYPE',
//            ],
            'filter' => [
                '=IBLOCK_ID' => \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('IBLOCK_ID'),
                'ACTIVE' => 'Y',
                'MULTIPLE' => 'Y',
                '=PROPERTY_TYPE' => [
                    \Bitrix\Iblock\PropertyTable::TYPE_LIST,
                    \Bitrix\Iblock\PropertyTable::TYPE_STRING,
                    \Bitrix\Iblock\PropertyTable::TYPE_ELEMENT,
                    \Bitrix\Iblock\PropertyTable::TYPE_NUMBER,
//                    \Bitrix\Iblock\PropertyTable::TYPE_SECTION,
                ],
            ],
        ])->fetchAll();

        $propertyTypes = [
            \Bitrix\Iblock\PropertyTable::TYPE_LIST,
            \Bitrix\Iblock\PropertyTable::TYPE_STRING,
            \Bitrix\Iblock\PropertyTable::TYPE_ELEMENT,
            \Bitrix\Iblock\PropertyTable::TYPE_NUMBER,
//            \Bitrix\Iblock\PropertyTable::TYPE_FILE,
//                    \Bitrix\Iblock\PropertyTable::TYPE_SECTION,
        ];
        return array_filter($properties, static function($property) use (&$propertyTypes) {
            return in_array($property['PROPERTY_TYPE'], $propertyTypes);
        });
    }
}