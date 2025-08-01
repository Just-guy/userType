<?

namespace Custom\EventHandlers;

use Bitrix\Main\EventManager;

class Iblock
{
	public static function init(): void
	{
		$eventManager = EventManager::getInstance();
		
		$eventManager->addEventHandler('main', 'OnUserTypeBuildList', ['Custom\UserType\CustomListsWithPucture', 'GetUserTypeDescription']);
	}
}
