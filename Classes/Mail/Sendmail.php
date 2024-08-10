<?php
declare(strict_types=1);

namespace Mail;

class Sendmail {

    /**
     * Sends a notification mail if set in .env file
     *
     * @param string $shortUrl
     * @param string $targetUrl
     * @param string $identifier
     * @return void
     */
    public static function sendNotification(string $shortUrl, string $targetUrl, string $identifier = ''): void {
        if (
            defined('NOTIFICATION_EMAIL') &&
            defined('NOTIFICATION_SUBJECT') &&
            defined('NOTIFICATION_MESSAGE')
        ) {
            $search = ['###IDENTIFIER###', '###SHORT_URL###', '###TARGET_URL###', '\n'];
            $replace = [$identifier, $shortUrl, $targetUrl, PHP_EOL];
            $to = NOTIFICATION_EMAIL;
            $subject = NOTIFICATION_SUBJECT;
            $txt = str_replace($search, $replace, NOTIFICATION_MESSAGE);

            mail($to,$subject,$txt);
        }
    }

}