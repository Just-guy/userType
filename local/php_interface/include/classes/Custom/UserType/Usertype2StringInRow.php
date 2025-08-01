<?

namespace Custom\Usertype;

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

/**
 * Class Usertype2StringInRow
 * @package lib\usertype
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
	{ ?>

		<script>
			initializingSortable("<?= $arUserField["FIELD_NAME"] ?>")
		</script>

<? $firstEmpty = true;

		$arHtmlControl["VALUE"] = unserialize(htmlspecialchars_decode($arHtmlControl['VALUE']));

		$title = (!empty($arHtmlControl["VALUE"]["TITLE"]) ? $arHtmlControl["VALUE"]["TITLE"] : '');
		$value = (!empty($arHtmlControl["VALUE"]["VALUE"]) ? $arHtmlControl["VALUE"]["VALUE"] : '');

		$html =  '<div class="two-string-in-row">';
		$html .= '<span class="two-string-in-row__drag-over"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M278.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-64 64c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l9.4-9.4L224 224l-114.7 0 9.4-9.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-64 64c-12.5 12.5-12.5 32.8 0 45.3l64 64c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-9.4-9.4L224 288l0 114.7-9.4-9.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l64 64c12.5 12.5 32.8 12.5 45.3 0l64-64c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-9.4 9.4L288 288l114.7 0-9.4 9.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l64-64c12.5-12.5 12.5-32.8 0-45.3l-64-64c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l9.4 9.4L288 224l0-114.7 9.4 9.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-64-64z"/></svg></span>';
		$html .= '<div class="two-string-in-row__field">';
		$html .= '<input type="text" name="' . $arHtmlControl["NAME"] . '[TITLE]' . '" value="' . $title . '" placeholder="Название" size="60"><br>';
		$html .= '</div>';
		$html .= '<div class="two-string-in-row__field">';
		$html .= '<input type="text" name="' . $arHtmlControl["NAME"] . '[VALUE]' . '" value="' . $value . '" placeholder="Ссылка" size="60"><br>';
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
<script src="../../local/php_interface/node/node_modules/sortablejs/Sortable.min.js"></script>
<script>
	function initializingSortable($fieldName) {
		document.addEventListener('DOMContentLoaded', function() {
			new Sortable(document.querySelector('#table_' + $fieldName + ' > tbody'), {
				animation: 150,
				easing: "cubic-bezier(1, 0, 0, 1)",
				handle: ".two-string-in-row__drag-over",
			})
		});
	}
</script>
<style>
	.two-string-in-row {
		background-color: #ececed;
		padding: 15px;
		display: flex;
		gap: 15px;
		max-width: 600px;
		border-radius: 5px;
		border: 1px solid #dfdfdf;
		align-items: center;
	}

	.two-string-in-row__field {
		display: flex;
	}

	.two-string-in-row__field input[type="text"] {
		width: 100%;
	}

	.two-string-in-row__drag-over {
		height: 20px;
		background-color: rgb(203, 203, 203);
		display: inline-flex;
		align-items: center;
		cursor: pointer;
		padding: 0px 4px;
		border-radius: 4px;
		transition: 0.2s ease-out;
	}

	.two-string-in-row__drag-over:hover {
		background-color: #ababab;
	}

	.two-string-in-row__drag-over>svg {
		height: 12px;
	}
</style>
