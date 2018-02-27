<?php
/**
 * Created by RG.
 * Date: 08.02.2018
 */

namespace Itua\Tauth;

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

class AuthService extends \CSocServAuth
{
    const TELEGRAM_WIDGET_LINK = 'https://telegram.org/js/telegram-widget.js?2';

    private $hash_params = [
        'id', 'first_name', 'username',
        'photo_url', 'auth_date', 'hash',
    ];

    /**
     * @return array
     */
    public function GetSettings()
    {
        $settings = [
            [
                'bot_username',
                \Bitrix\Main\Localization\Loc::getMessage('ITUA_TAUTH_BOT_USERNAME'),
                '',
                [
                    'text',
                    40
                ]
            ],
            [
                'bot_token',
                \Bitrix\Main\Localization\Loc::getMessage('ITUA_TAUTH_BOT_TOKEN'),
                '',
                [
                    'text',
                    40
                ]
            ],
            [
                'note' => '<a href="https://telegram.org/blog/login" target="_blank">help</a>'
            ]
        ];

        return $settings;
    }

    /**
     * @param $params
     * @return string or array
     */
    public function GetFormHtml($params)
    {
        $bot_username = self::GetOption('bot_username');
        $widget_link = self::TELEGRAM_WIDGET_LINK;
        $auth_url = \CSocServUtil::GetCurUrl('auth_service_id=' . Main::AUTH_SERVICE_ID . '&check_key=' . $_SESSION['UNIQUE_KEY']);

        return <<<HTML
<script async 
        src="$widget_link" 
        data-telegram-login="$bot_username" 
        data-size="medium" 
        data-auth-url="$auth_url"
        data-request-access="write">
</script>
HTML;
    }

    public function Authorize()
    {
        $GLOBALS['APPLICATION']->RestartBuffer();
        if (\CSocServAuthManager::CheckUniqueKey()) {
            try {
                $auth_data = $this->checkTelegramAuthorization($_GET);
                self::saveTelegramUserData($auth_data);
                $arFields = array(
                    'EXTERNAL_AUTH_ID' => Main::AUTH_SERVICE_ID,
                    'XML_ID' => $auth_data['id'],
                    'LOGIN' => $auth_data['username'],
                    'EMAIL' => '',
                    'NAME'=> $auth_data['first_name'],
                    'LAST_NAME'=> '',
                );
                if (!empty(SITE_ID)) {
                    $arFields['SITE_ID'] = SITE_ID;
                }
                $bSuccess = $this->AuthorizeUser($arFields);
            } catch (\Exception $e) {
                die ($e->getMessage());
            }
        }
        $aRemove = [
            'logout', 'auth_service_error', 'auth_service_id',
            'check_key', 'answer_secret', 'name', 'last_name',
            'email', 'login', 'user_id',
        ];
        $aRemove = array_merge($aRemove, $this->hash_params);
        $url = $GLOBALS['APPLICATION']->GetCurPageParam(($bSuccess === true ? '' : 'auth_service_id=' . Main::AUTH_SERVICE_ID . '&auth_service_error=' . $bSuccess), $aRemove);
        $url = \CUtil::JSEscape($url);
        echo <<<HTML
<script type="text/javascript">
	window.location = '$url';
</script>
HTML;
        die();
    }

    private function checkTelegramAuthorization($auth_data)
    {
        $bot_token = self::GetOption('bot_token');
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);
        $data_check_arr = [];
        foreach ($auth_data as $key => $value) {
            if (in_array($key, $this->hash_params)) {
                $data_check_arr[] = $key . '=' . $value;
            }
        }
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $bot_token, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);
        if (strcmp($hash, $check_hash) !== 0) {
            throw new \Exception('Data is NOT from Telegram');
        }
        if ((time() - $auth_data['auth_date']) > 86400) {
            throw new \Exception('Data is outdated');
        }

        return $auth_data;
    }

    private function saveTelegramUserData($auth_data)
    {
        $auth_data_json = json_encode($auth_data);
        setcookie('tg_user', $auth_data_json);
    }
}
