<?php
declare(strict_types=1);

namespace Mail;

use Exception;
use Template\SimpleTemplateEngine;

class Sendmail {

    /**
     * Sends a notification mail if set in .env file
     *
     * @param string $shortUrl
     * @param string $targetUrl
     * @param string $identifier
     * @param bool $forceNoHideTarget
     * @return void
     * @throws Exception
     */
    public static function sendNotification(string $shortUrl, string $targetUrl, string $identifier = '', bool $forceNoHideTarget = false): void {
        if ( !self::isNotifyConfigured() ) {
            return;
        }

        $to = NOTIFICATION_EMAIL;
        $subject = NOTIFICATION_SUBJECT;

        // Load Mail template
        $template = new SimpleTemplateEngine();
        $template->setTemplateExtension('.txt');
        $template->loadTemplate('mail');

        // Hide target?
        if ( $forceNoHideTarget === true ) {
            $hideTarget = false;
        } else {
            $hideTarget = defined('NOTIFICATION_HIDE_TARGET') ? (bool)NOTIFICATION_HIDE_TARGET : false;
        }

        // Set template variables
        $template->assignMultiple([
            'IDENTIFIER' => rawurldecode($identifier),
            'SHORT_URL' => $shortUrl,
            'TARGET_URL' => ( $hideTarget ) ? '---HIDDEN---' : $targetUrl
        ]);

        // Render template
        $msg = $template->render();

        // Send mail
        mail($to,$subject,$msg);
    }

    /**
     * Sends a modified notification for freshly created OneTimeLinks
     *
     * @param string $shortUrl
     * @param string $targetUrl
     * @param string $identifier
     * @return void
     * @throws Exception
     */
    public static function sendCreateNotification(string $shortUrl, string $targetUrl, string $identifier = ''): void {
        // Early return checkup
        if ( !defined('NOTIFICATION_ON_CREATION') ) {
            return;
        }

        // Early return checkup
        if ( boolval(NOTIFICATION_ON_CREATION) === false ) {
            return;
        }

        // Modify identifier string, prepend creation mark
        $identifier = 'CREATION NOTIFICATION' . (( strlen(trim($identifier)) > 0 ) ? ': ' . $identifier : '');

        // Send notification without hiding the target
        self::sendNotification($shortUrl, $targetUrl, $identifier, forceNoHideTarget: true);
    }

    /**
     * Checks if constants defined in .env file
     *
     * @return bool
     */
    public static function isNotifyConfigured(): bool {
        return (defined('NOTIFICATION_EMAIL') && defined('NOTIFICATION_SUBJECT'));
    }

}