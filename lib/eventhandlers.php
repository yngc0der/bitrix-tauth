<?php
/**
 * Created by RG.
 * Date: 08.02.2018
 */


namespace Yngc0der\Tauth;


class EventHandlers
{
    /**
     * @return array
     */
    public static function onAuthServicesBuildList()
    {
        $auth_item = [
            'ID' => Main::AUTH_SERVICE_ID,
            'CLASS' => '\\Yngc0der\\Tauth\\AuthService',
            'NAME' => 'Telegram',
            'ICON' => 'openid',
        ];

        return $auth_item;
    }
}
