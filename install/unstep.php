<?php
/**
 * Created by RG.
 * Date: 08.02.2018
 */


use Bitrix\Main\Localization\Loc;


if (!check_bitrix_sessid()) {
    return false;
}
?>

<form action="<?= $APPLICATION->GetCurPage(); ?>">
	<?= bitrix_sessid_post(); ?>
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID; ?>">
	<input type="hidden" name="id" value="itua.tauth">
	<input type="hidden" name="uninstall" value="Y">
	<input type="submit" name="" value="<?= Loc::getMessage('MOD_UNINST_DEL'); ?>">
</form>
