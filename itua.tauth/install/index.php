<?php
/**
 * Created by RG.
 * Date: 08.02.2018
 */

use \Bitrix\Main,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class itua_tauth extends \CModule
{
	function __construct()
	{
		$arModuleVersion = [];
		include(__DIR__ . '/version.php');

		$this->MODULE_ID = 'itua.tauth';
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = Loc::getMessage('ITUA_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('ITUA_MODULE_DESC');

		$this->PARTNER_NAME = Loc::getMessage('ITUA_PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('ITUA_PARTNER_URI');
	}

    function InstallEvents()
    {
        Main\EventManager::getInstance()->registerEventHandler(
            'socialservices',
            'OnAuthServicesBuildList',
            $this->MODULE_ID,
            '\\Itua\\Tauth\\EventHandlers',
            'onAuthServicesBuildList'
        );
    }
    function UnInstallEvents()
    {
        Main\EventManager::getInstance()->unRegisterEventHandler(
            'socialservices',
            'OnAuthServicesBuildList',
            $this->MODULE_ID,
            '\\Itua\\Tauth\\EventHandlers',
            'onAuthServicesBuildList'
        );
    }

	function DoInstall()
	{
		global $APPLICATION;
		if ($this->isVersionD7()) {
			Main\ModuleManager::registerModule($this->MODULE_ID);
			$this->InstallEvents();
		} else {
		    throw new Main\LoaderException(Loc::getMessage('ITUA_INSTALL_ERROR_NOT_D7'));
        }

		$APPLICATION->IncludeAdminFile(Loc::getMessage('ITUA_INSTALL_TITLE'), $this->GetPath() . '/install/step.php');
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
		    $APPLICATION->IncludeAdminFile(Loc::getMessage('ITUA_UNINSTALL_TITLE'), $this->GetPath() . '/install/unstep.php');
        }
	}

	function isVersionD7()
	{
		return CheckVersion(Main\ModuleManager::getVersion('main'), '14.00.00');
	}

	function GetPath($notDocumentRoot = false)
	{
		if ($notDocumentRoot) {
		    return str_ireplace(Main\Application::getDocumentRoot(),'', dirname(__DIR__));
        } else {
		    return dirname(__DIR__);
        }
	}
}
