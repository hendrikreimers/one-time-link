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
        if (
            defined('NOTIFICATION_EMAIL') &&
            defined('NOTIFICATION_SUBJECT') &&
            defined('NOTIFICATION_MESSAGE')
        ) {
            $to = NOTIFICATION_EMAIL;
            $subject = NOTIFICATION_SUBJECT;

            // Load Mail template
            $template = new SimpleTemplateEngine();
            $template->setTemplateExtension('.txt');
            $template->loadTemplate('mail');

            // Set template variables
            $template->assignMultiple([
                'IDENTIFIER' => rawurldecode($identifier),
                'SHORT_URL' => $shortUrl,
                'TARGET_URL' => $targetUrl
            ]);

            // Render template
            $msg = $template->render();

            // Send mail
            mail($to,$subject,$msg);
        }
    }

}