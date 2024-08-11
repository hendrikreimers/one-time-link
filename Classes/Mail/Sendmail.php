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
     * @return void
     * @throws Exception
     */
    public static function sendNotification(string $shortUrl, string $targetUrl, string $identifier = ''): void {
        if ( self::isNotifyConfigured() ) {
            $to = NOTIFICATION_EMAIL;
            $subject = NOTIFICATION_SUBJECT;

            // Load Mail template
            $template = new SimpleTemplateEngine();
            $template->setTemplateExtension('.txt');
            $template->loadTemplate('mail');

            // Hide target?
            $hideTarget = defined('NOTIFICATION_HIDE_TARGET') ? (bool)NOTIFICATION_HIDE_TARGET : false;

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