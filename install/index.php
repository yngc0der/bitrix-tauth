<?php
/**
 * Created by RG.
 * Date: 08.02.2018
 */


use Bitrix\Main;
use Bitrix\Main\Localization\Loc;


Loc::loadMessages(__FILE__);

class yngc0der_tauth extends \CModule
{
	function __construct()
	{
		$arModuleVersion = [];
		include(__DIR__ . '/version.php');

		$this->MODULE_ID = 'yngc0der.tauth';
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = Loc::getMessage('RG_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('RG_MODULE_DESC');

		$this->PARTNER_NAME = Loc::getMessage('RG_PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('RG_PARTNER_URI');
	}

    function InstallEvents()
    {
        Main\EventManager::getInstance()->registerEventHandler(
            'socialservices',
            'OnAuthServicesBuildList',
            $this->MODULE_ID,
            '\\RG\\Tauth\\EventHandlers',
            'onAuthServicesBuildList'
        );
    }
    function UnInstallEvents()
    {
        Main\EventManager::getInstance()->unRegisterEventHandler(
            'socialservices',
            'OnAuthServicesBuildList',
            $this->MODULE_ID,
            '\\RG\\Tauth\\EventHandlers',
            'onAuthServicesBuildList'
        );
    }

	function DoInstall($cli_mode = false)
	{
		global $APPLICATION;
		if ($this->isVersionD7()) {
			Main\ModuleManager::registerModule($this->MODULE_ID);
			$this->InstallEvents();
		} else {
		    throw new Main\LoaderException(Loc::getMessage('RG_INSTALL_ERROR_NOT_D7'));
        	}
		if (!$cli_mode) {
			$APPLICATION->IncludeAdminFile(Loc::getMessage('RG_INSTALL_TITLE'), $this->GetPath() . '/install/step.php');
		}
	}

	function DoUninstall()
	{
		global $APPLICATION;

		$context = Main\Application::getInstance()->getContext();
		$request = $context->getRequest();

		if ($request['uninstall'] == 'Y') {
		    $this->UnInstallEvents();
		    Main\Config\Option::delete($this->MODULE_ID);
			Main\ModuleManager::unRegisterModule($this->MODULE_ID);
		} else {
		    $APPLICATION->IncludeAdminFile(Loc::getMessage('RG_UNINSTALL_TITLE'), $this->GetPath() . '/install/unstep.php');
        }
	}

	function isVersionD7()
	{
		return CheckVersion(Main\ModuleManager::getVersion('main'), '14.00.00');
	}

	function GetPath($notDocumentRoot = false)
	{
		if ($notDocumentRoot) {
		    return str_ireplace(Main\Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
		    return dirname(__DIR__);
        }
	}
}
