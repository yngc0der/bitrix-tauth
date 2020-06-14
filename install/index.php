<?php

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

/**
 * Class yngc0der_tauth
 */
class yngc0der_tauth extends CModule
{
    public $MODULE_ID = 'yngc0der.tauth';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    /**
     * yngc0der_tauth constructor.
     */
    public function __construct()
	{
		$arModuleVersion = [];
		include __DIR__ . '/version.php';

		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = Loc::getMessage('YC_TAUTH_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('YC_TAUTH_MODULE_DESC');
		$this->PARTNER_NAME = Loc::getMessage('YC_TAUTH_PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('YC_TAUTH_PARTNER_URI');
	}

    public function InstallEvents()
    {
        parent::InstallEvents();

        EventManager::getInstance()->registerEventHandler(
            'socialservices',
            'OnAuthServicesBuildList',
            $this->MODULE_ID,
            '\\Yngc0der\\Tauth\\AuthService',
            'onAuthServicesBuildList'
        );
    }

    public function UnInstallEvents()
    {
        parent::UnInstallEvents();

        EventManager::getInstance()->unRegisterEventHandler(
            'socialservices',
            'OnAuthServicesBuildList',
            $this->MODULE_ID,
            '\\Yngc0der\\Tauth\\AuthService',
            'onAuthServicesBuildList'
        );
    }

    /**
     * @throws \Bitrix\Main\LoaderException
     */
    public function DoInstall()
	{
        parent::DoInstall();

        /** @global CMain $APPLICATION */
        global $APPLICATION;

        if ($this->isD7Core()) {
            ModuleManager::registerModule($this->MODULE_ID);
            Loader::includeModule($this->MODULE_ID);

            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();
            $this->InstallTasks();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('YC_TAUTH_INSTALL_ERROR_D7'));
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('YC_TAUTH_INSTALL_TITLE'),
            $this->getPath() . '/install/step.php'
        );
	}

    /**
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public function DoUninstall()
    {
        parent::DoUninstall();

        /** @global CMain $APPLICATION */
        global $APPLICATION;

        $request = Context::getCurrent()->getRequest();

        if (is_null($request->get('step')) || (int) $request->get('step') === 1) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('YC_TAUTH_UNINSTALL_TITLE'),
                $this->getPath() . '/install/unstep.php'
            );
        }

        if ((int) $request->get('step') === 2) {
            Loader::includeModule($this->MODULE_ID);

            if (is_null($request->get('savedata')) || $request->get('savedata') !== 'Y') {
                $this->UnInstallDB();
            }

            $this->UnInstallEvents();
            $this->UnInstallFiles();
            $this->UnInstallTasks();

            Loader::clearModuleCache($this->MODULE_ID);
            ModuleManager::unRegisterModule($this->MODULE_ID);
        }
    }

    /**
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function UnInstallDB()
    {
        parent::UnInstallDB();

        Option::delete($this->MODULE_ID);
    }

    public function InstallFiles()
    {
        parent::InstallFiles();

        CopyDirFiles(
            __DIR__ . '/js',
            Application::getDocumentRoot() . '/bitrix/js',
            true,
            true
        );
    }

    public function UnInstallFiles()
    {
        parent::UnInstallFiles();

        DeleteDirFilesEx('/bitrix/js/yngc0der.tauth/');
    }

    /**
     * @return bool
     */
	function isD7Core()
	{
		return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
	}

    /**
     * @param bool $includeDocumentRoot
     * @return string
     */
    public function getPath($includeDocumentRoot = true)
    {
        return $includeDocumentRoot
            ? dirname(__DIR__)
            : (string) str_ireplace(Application::getDocumentRoot(),'', dirname(__DIR__));
    }
}
