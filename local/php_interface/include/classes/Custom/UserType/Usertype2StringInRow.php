<?

namespace Custom\UserType;

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

/**
 * Class Usertype2StringInRow
 * @package Custom\UserType
 */

class Usertype2StringInRow extends \Bitrix\Main\UserField\Types\StringType
{
	public static function GetUserTypeDescription(): array
	{
		return array(
			"USER_TYPE_ID" => "stringwithhtmlfield",
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => '2 строки',
			"BASE_TYPE" => "string",
			'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
		);
	}

	public static function GetEditFormHTML(array $arUserField, ?array $arHtmlControl): string
	{
		$firstEmpty = true;

		$arHtmlControl["VALUE"] = unserialize(htmlspecialchars_decode($arHtmlControl['VALUE']));

		$title = (!empty($arHtmlControl["VALUE"]["TITLE"]) ? $arHtmlControl["VALUE"]["TITLE"] : '');
		$value = (!empty($arHtmlControl["VALUE"]["VALUE"]) ? $arHtmlControl["VALUE"]["VALUE"] : '');

		$html =  '<div class="two-string-in-row">';
		$html .= '<div class="two-string-in-row__field">';
		$html .= '<input type="text" name="' . $arHtmlControl["NAME"] . '[TITLE]' . '" value="' . $title . '" placeholder="Заголовок" size="60"><br>';
		$html .= '</div>';
		$html .= '<div class="two-string-in-row__field">';
		$html .= '<input type="text" name="' . $arHtmlControl["NAME"] . '[VALUE]' . '" value="' . $value . '" placeholder="Значение" size="60"><br>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	public static function OnBeforeSave($arUserField, $value)
	{
		if (empty($value["TITLE"]) && empty($value["VALUE"])) return;
		$valueTmp = [];

		foreach ($value['VALUE'] as $key => $item) {
			if (empty($item)) {
				unset($value['VALUE'][$key]);
			} else if (empty($item['TITLE'])) {
			}
		}


		$value = serialize($value);
		return $value;
	}


	public static function checkFields(array $userField, $value): array
	{
		$fieldName = HtmlFilter::encode(
			$userField['EDIT_FORM_LABEL'] <> ''
				? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME']
		);

		$msg = [];
		if ($value != '' && !empty($value) < $userField['SETTINGS']['MIN_LENGTH']) {
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::GetMessage(
					'USER_TYPE_STRING_MIN_LEGTH_ERROR',
					[
						'#FIELD_NAME#' => $fieldName,
						'#MIN_LENGTH#' => $userField['SETTINGS']['MIN_LENGTH']
					]
				)
			];
		}
		if (
			$userField['SETTINGS']['MAX_LENGTH'] > 0
			&& !empty($value) > $userField['SETTINGS']['MAX_LENGTH']
		) {
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => Loc::GetMessage(
					'USER_TYPE_STRING_MAX_LEGTH_ERROR',
					[
						'#FIELD_NAME#' => $fieldName,
						'#MAX_LENGTH#' => $userField['SETTINGS']['MAX_LENGTH']
					]
				),
			];
		}
		if (
			!empty($userField['SETTINGS']['REGEXP'])
			&& (string) $value !== ''
			&& !preg_match($userField['SETTINGS']['REGEXP'], $value)
		) {
			$msg[] = [
				'id' => $userField['FIELD_NAME'],
				'text' => ($userField['ERROR_MESSAGE'] != '' ?
					$userField['ERROR_MESSAGE'] :
					Loc::GetMessage(
						'USER_TYPE_STRING_REGEXP_ERROR',
						[
							'#FIELD_NAME#' => $fieldName
						]
					)
				),
			];
		}
		return $msg;
	}
}
?>
<style>
	.two-string-in-row {
		background-color: #ececed;
		padding: 15px;
		display: flex;
		gap: 15px;
		max-width: 600px;
		border-radius: 5px;
		border: 1px solid #dfdfdf;
	}

	.two-string-in-row__field {
		display: flex;
	}

	.two-string-in-row__field input[type="text"] {
		width: 100%;
	}
</style>
