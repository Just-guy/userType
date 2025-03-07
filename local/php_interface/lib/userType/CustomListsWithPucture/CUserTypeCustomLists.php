<?

namespace lib\customClass\usertype;

use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Iblock;

/**
 * Class CUserTypeCustomLists
 * @package lib\usertype
 */

class CUserTypeCustomLists extends \Bitrix\Main\UserField\Types\StringType
{
	public static function GetUserTypeDescription(): array
	{
		return array(
			"USER_TYPE_ID" => "customlists",
			"CLASS_NAME" => "customUserType\CUserTypeCustomLists",
			"DESCRIPTION" => "Список с изображением (CUSTOM)",
			"BASE_TYPE" => "string",
			'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
		);
	}

	public static function GetEditFormHTML(array $arUserField, ?array $arHtmlControl): string
	{
		$arHtmlControl["VALUE"] = unserialize(htmlspecialchars_decode($arHtmlControl["VALUE"]));

		preg_match('/\[([0-9]+)\]/', $arHtmlControl['NAME'], $matches);
 		$keySection = $matches[1];
		$keyElement = 0;
		$arHtmlControl['NAME'] = $arHtmlControl['NAME'] . '[ICON]';

		if ($arHtmlControl["VALUE"]["CONDITION"] == 'close') {
			$condition = 'close';
			$conditionButton = 'Развернуть';
		} else {
			$condition = 'open';
			$conditionButton = 'Свернуть';
		}

		$html = '<div class="custom-lists">';

		// === File

		$html .= '<div class="custom-lists__group' . ($condition == 'close' ? ' custom-lists__group_close' : '') . '">';
		$html .= '<a href onclick="changeOfState(event, ' . $keySection . ', `' . $arUserField["FIELD_NAME"] . '`)">' . $conditionButton . '</a>';
		$html .= '<div class="custom-lists__top-block">';
		$html .= '<div class="custom-lists__image-block">';

		$p = mb_strpos($arHtmlControl['NAME'], '[');
		$strOldIdName = mb_substr($arHtmlControl['NAME'], 0, $p) . '_old_id' . mb_substr($arHtmlControl['NAME'], $p);
		if (strlen($arHtmlControl["VALUE"]["ICON"]["ID"]) > 0):
			$html .= '<div class="custom-lists__image">';
			$html .= '<img src="' . \CFile::GetPath($arHtmlControl["VALUE"]["ICON"]["ID"]) . '">';
			$html .= '</div>';
		endif;
		$html .= '<div class="custom-lists__image-data">';
		$html .= \CFile::InputFile("$arUserField[FIELD_NAME][$keySection][ICON]", 20, $arHtmlControl["VALUE"]["ICON"]["ID"]);
		$html .= '<input type="hidden" name="' . $strOldIdName . '" value="' . $arHtmlControl["VALUE"]["ICON"]["ID"] . '">';
		$html .= '</div>';

		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="custom-lists__middle-block">';
		$html .= '<div class="custom-lists__section-input">';
		$html .= '<input class="custom-lists__section-name" type="text" name="' . $arUserField["FIELD_NAME"] . '[' . $keySection . '][TITLE_SECTION]" value="' . $arHtmlControl["VALUE"]["TITLE_SECTION"] . '" placeholder="Название раздела" size="30">';
		$html .= '<input class="custom-lists__section-url" type="text" name="' . $arUserField["FIELD_NAME"] . '[' . $keySection . '][URL_SECTION]" value="' . $arHtmlControl["VALUE"]["URL_SECTION"] . '" placeholder="Ссылка для раздела" size="30">';
		$html .= '<input class="custom-lists__delete-node" type="button" value="x" onclick="deleteNode(this.closest(`.custom-lists`))">';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="custom-lists__bottom-block">';
		$html .= '<div class="custom-lists__list-elements">';
		if (!empty($arHtmlControl["VALUE"]["ELEMENT"]))
			foreach ($arHtmlControl["VALUE"]["ELEMENT"] as $keyElement => $value) {
				$html .= '<div class="custom-lists__element" data-position="' . $keyElement . '">';
				$html .= '<input type="text" class="custom-lists__element-name" name="' . $arUserField["FIELD_NAME"] . '[' . $keySection . '][ELEMENT][' . $keyElement . '][TITLE]" value="' . $value["TITLE"] . '" placeholder="Название элемента" size="30">';
				$html .= '<input type="text" class="custom-lists__element-url" name="' . $arUserField["FIELD_NAME"] . '[' . $keySection . '][ELEMENT][' . $keyElement . '][URL]" value="' . $value["URL"] . '" placeholder="Ссылка элемента" size="30">';
				$html .= '<input class="custom-lists__delete-node" type="button" value="x">';
				$html .= '</div>';
			}
		$html .= '<input type="button" value="Добавить услугу" onclick="addNewNode(this.closest(`.custom-lists__list-elements`), `' . $arUserField["FIELD_NAME"] . '`, this, ' . $keySection . ')">';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '</a>';
		$html .= '<input type="hidden" name="' . $arUserField["FIELD_NAME"] . '[' . $keySection . '][SECTION_NUMBER]" value="' . $keySection . '">';
		$html .= '<input type="hidden" name="' . $arUserField["FIELD_NAME"] . '[' . $keySection . '][CONDITION]" value="' . $condition . '">';

		$html .= '</div>';

		return $html; ?>

<? }

	public static function OnBeforeSave($arUserField, $value)
	{
		$emptyElements = false;

		$value['MODULE_ID'] = 'main';

		if ($_POST[$arUserField['FIELD_NAME'] . "_del"][$value["SECTION_NUMBER"]]["ICON"] == 'Y') {
			unset($value["ICON"]['ID']);
		} else if (!empty($value["ICON"]["name"])) {
			$value["ICON"]['ID'] = \CFile::SaveFile($value["ICON"], 'uf');
		} else if (!empty($_POST[$arUserField['FIELD_NAME'] . "_old_id"][$value["SECTION_NUMBER"]])) {
			$value["ICON"]['ID'] = $_POST[$arUserField['FIELD_NAME'] . "_old_id"][$value["SECTION_NUMBER"]]["ICON"];
		}

		if (empty($value["TITLE_ELEMENT"])) $emptyElements = true;

		foreach ($value["TITLE_ELEMENT"] as $valueElement) {
			$emptyElements = true;
			if (!empty($valueElement)) {
				$emptyElements = false;
				continue;
			}
		}

		if (empty($value["TITLE_SECTION"]) && $emptyElements) return;

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

<script>
	BX.bind(document, 'click', function(event) {
		if (BX.hasClass(event.target, 'custom-lists__delete-node')) deleteNode(event.target.closest('.custom-lists__element'));
	});

	function addNewNode(parentNode, fieldName, currentButton, keySection = 0, positionElement = 0) {
		//debugger
		lastElement = parentNode.querySelector('.custom-lists__element:last-of-type');
		if (lastElement != null) {
			positionElement = Number(BX.data(lastElement, 'position')) + 1;
		}
		//debugger
		currentButton.before(
			BX.create('div', {
				attrs: {
					className: 'custom-lists__element'
				},
				dataset: {
					position: positionElement
				},
				children: [
					BX.create({
						tag: 'input',
						attrs: {
							className: 'custom-lists__element-name'
						},
						props: {
							type: 'text',
							name: fieldName + '[' + keySection + '][ELEMENT][' + positionElement + '][TITLE]',
							value: '',
							placeholder: 'Название элемента'
						},
					}),
					BX.create({
						tag: 'input',
						attrs: {
							className: 'custom-lists__element-url'
						},
						props: {
							type: 'text',
							name: fieldName + '[' + keySection + '][ELEMENT][' + positionElement + '][URL]',
							value: '',
							placeholder: 'Ссылка элемента'
						},
					}),
					BX.create({
						tag: 'input',
						props: {
							type: 'button',
							className: 'custom-lists__delete-node',
							value: 'x',
						},
					}),
				]
			})
		);
	}

	function deleteNode(node) {
		node.remove();
	}

	function changeOfState(event, keySection, fieldName) {
		//debugger
		event.preventDefault();
		let customlistsGroup = event.target.closest('.custom-lists__group'),
			condition = document.querySelector('[name="' + fieldName + '[' + keySection + '][CONDITION]"]').value;

		customlistsGroup.classList.toggle('custom-lists__group_close');
		event.target.text = (condition == 'close' ? "Свернуть" : "Развернуть");
		document.querySelector('[name="' + fieldName + '[' + keySection + '][CONDITION]"]').value = (condition == 'close' ? "open" : "close");
	}
</script>

<style>
	.custom-lists {
		margin-bottom: 20px;
	}

	.custom-lists__group {
		display: flex;
		align-items: normal;
		flex-direction: column;
		padding: 20px;
		border: 1px solid #dddddd;
		border-radius: 3px;
		background-color: #f7f7f7;
		gap: 15px;
		transition: .2s ease-out;
	}

	.custom-lists__group.custom-lists__group_close {
		padding: 10px;
		gap: 5px;
	}

	.custom-lists__top-block {
		display: flex;
		align-items: center;
		gap: 20px;
	}

	.custom-lists__image-block {
		display: flex;
		gap: 10px;
		flex: 1;
	}

	.custom-lists__image>img {
		max-width: 100px;
	}

	.custom-lists__image {
		flex-basis: 100px;
		align-items: center;
		display: flex;
		justify-content: center;
	}

	.custom-lists__image-data {
		display: flex;
		flex-direction: column;
		align-items: baseline;
	}

	.custom-lists__image-block .bx-input-file-desc {
		word-break: break-all;
	}

	.custom-lists__group.custom-lists__group_close .custom-lists__section-input {
		margin: 0;
	}

	.custom-lists__section-input {
		margin-bottom: 5px;
		display: flex;
		align-items: center;
		transition: .2s ease-out;
	}

	input.custom-lists__section-name,
	input.custom-lists__section-url {
		flex: 1;
	}

	.custom-lists__middle-block>.custom-lists__section-input>.custom-lists__section-name,
	.custom-lists__list-elements>.custom-lists__element>.custom-lists__element-name {
		margin-right: 5px;
	}

	.custom-lists__list-elements {
		display: flex;
		justify-content: end;
		flex-direction: column;
		align-items: end;
	}

	.custom-lists__element {
		width: 85%;
		display: flex;
		align-items: center;
	}

	.custom-lists__element input[type="text"] {
		width: 100%;
	}

	.custom-lists__group input[type="button"].custom-lists__delete-node {
		margin: 0 0 0 10px;
	}

	.custom-lists__group_close>.custom-lists__top-block,
	.custom-lists__group_close>.custom-lists__bottom-block {
		display: none;
	}
</style>
