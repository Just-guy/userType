<?
// Подключаем composer
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php"))
	include_once($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php");

// События
Custom\EventHandlers\Iblock::init();
