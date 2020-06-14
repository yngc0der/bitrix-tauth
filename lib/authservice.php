<?php

namespace Yngc0der\Tauth;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Bitrix\Main\Web;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\SystemException;
use CSocServAuth;
use CSocServUtil;
use CSocServAuthManager;

Loc::loadMessages(__FILE__);

/**
 * Class AuthService
 * @package Yngc0der\Tauth
 */
class AuthService extends CSocServAuth
{
    const ID = 'Telegram';
    const WIDGET_URL = 'https://telegram.org/js/telegram-widget.js?9';
    const CALLBACK_DATA_TTL = 86400;

    private static $callbackQueryParams = ['id', 'first_name', 'username', 'photo_url', 'auth_date', 'hash', ];

    /**
     * @return array
     */
    public static function onAuthServicesBuildList()
    {
        /** @global \CMain $APPLICATION */
        global $APPLICATION;

        $APPLICATION->SetAdditionalCSS('/bitrix/js/yngc0der.tauth/css/ss.css');

        return [
            'ID' => self::ID,
            'CLASS' => __CLASS__,
            'NAME' => 'Telegram',
            'ICON' => 'telegram',
        ];
    }

    /**
     * @return array
     */
    public function GetSettings()
    {
        return [
            ['bot_username', Loc::getMessage('YC_TAUTH_BOT_USERNAME'), '', ['text', 40, ], ],
            ['bot_token', Loc::getMessage('YC_TAUTH_BOT_TOKEN'), '', ['text', 40, ], ],
            ['note' => '<a href="https://telegram.org/blog/login" target="_blank">Help</a>', ],
        ];
    }

    /**
     * @param array $params
     * @return string or array
     */
    public function GetFormHtml($params)
    {
        $botUsername = self::GetOption('bot_username');
        $widgetUrl = self::WIDGET_URL;
        $redirectUrl = CSocServUtil::GetCurUrl(
            'auth_service_id=' . self::ID . '&check_key=' . $_SESSION['UNIQUE_KEY']
        );

        return <<<HTML
<script async 
        src="{$widgetUrl}" 
        data-telegram-login="{$botUsername}" 
        data-size="medium" 
        data-auth-url="{$redirectUrl}"
        data-request-access="write"
        data-skip-moving="true">
</script>
HTML;
    }

    /**
     * @throws AccessDeniedException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function Authorize()
    {
        /** @global \CMain $APPLICATION */
        global $APPLICATION;

        $APPLICATION->RestartBuffer();

        $authResult = false;

        if (CSocServAuthManager::CheckUniqueKey()) {
            $request = Context::getCurrent()->getRequest();

            $data = $this->validateCallbackData($request->getQueryList()->getValues());

            $userFields = [
                'EXTERNAL_AUTH_ID' => self::ID,
                'XML_ID' => $data['id'],
                'LOGIN' => $data['username'],
                'EMAIL' => '',
                'NAME'=> $data['first_name'],
                'LAST_NAME'=> '',
            ];

            if (!empty(Context::getCurrent()->getSite())) {
                $userFields['SITE_ID'] = Context::getCurrent()->getSite();
            }

            $authResult = $this->AuthorizeUser($userFields);
        }

        $removeQueryParams = [
            'logout',               'auth_service_error',
            'auth_service_id',      'code',
            'error_reason',         'error',
            'error_description',    'check_key',
            'current_fieldset',
        ];

        $uri = (new Web\Uri($APPLICATION->GetCurPageParam()))
            ->deleteParams(array_merge($removeQueryParams, self::$callbackQueryParams));

        if ($authResult !== true) {
            $uri->addParams([
                'auth_service_id' => self::ID,
                'auth_service_error' => (int) $authResult,
            ]);
        }

        echo <<<HTML
<script type="text/javascript">
	window.location = "{$uri->getUri()}";
</script>
HTML;
        die;
    }

    /**
     * @param array $params
     * @return string
     * @throws SystemException
     */
    private function getHash(array $params)
    {
        $botToken = self::GetOption('bot_token');

        if ($botToken === null || $botToken === false) {
            throw new SystemException('Invalid bot token');
        }

        $hashParams = [];

        foreach ($params as $param => $value) {
            if (!in_array($param, self::$callbackQueryParams)) {
                continue;
            }

            $hashParams[] = "{$param}={$value}";
        }

        sort($hashParams);

        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', implode("\n", $hashParams), $secretKey);

        return $hash;
    }

    /**
     * @param array $requestParams
     * @return array
     * @throws AccessDeniedException
     * @throws SystemException
     */
    private function validateCallbackData(array $requestParams)
    {
        $checkHash = $requestParams['hash'];
        unset($requestParams['hash']);

        $hash = $this->getHash($requestParams);

        if ($hash !== $checkHash) {
            throw new AccessDeniedException('Invalid data');
        }

        if (time() - intval($requestParams['auth_date']) > self::CALLBACK_DATA_TTL) {
            throw new SystemException('Data is outdated');
        }

        return $requestParams;
    }
}
