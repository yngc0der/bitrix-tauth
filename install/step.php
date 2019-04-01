<?php
/**
 * Created by RG.
 * Date: 08.02.2018
 */


use Bitrix\Main\Localization\Loc;


if (!check_bitrix_sessid()) {
    return false;
}

global $APPLICATION;
if ($ex = $APPLICATION->GetException()) {
    $mess = new \CAdminMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage('MOD_INST_ERR'),
        'DETAILS' => $ex->GetString(),
        'HTML' => true,
    ]);
    echo $mess->show();
} else {
    $mess = new \CAdminMessage([
        'TYPE' => 'OK',
        'MESSAGE' => Loc::getMessage('MOD_INST_OK'),
    ]);
    echo $mess->show();
}

?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<input type="submit" name="" value="<?= Loc::getMessage('MOD_BACK') ?>">
</form>
