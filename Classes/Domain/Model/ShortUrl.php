<?php
declare(strict_types=1);

namespace Domain\Model;

use Exception;

/**
 * ShortURL Model
 *
 */
class ShortUrl {

    /**
     * Constructor
     *
     * @param string $shortUrl
     * @param string $targetUrl
     * @param bool $notify
     * @param string $identifier
     * @param int $numViewsLeft
     * @param int $timestamp
     */
    public function __construct(
        private string $shortUrl,
        private string $targetUrl,
        private bool $notify = false,
        private string $identifier = '',
        private int $numViewsLeft = 1,
        private int $timestamp = 0
    ) {
        if ( $timestamp <= 0 ) {
            $this->timestamp = time();
        }
    }

    /**
     * Getter: shortURL
     *
     * @return string
     */
    public function getShortUrl(): string {
        return trim($this->shortUrl);
    }

    /**
     * Setter: shortURL
     *
     * @param string $shortUrl
     * @return void
     */
    public function setShortUrl(string $shortUrl): void {
        $this->shortUrl = trim($shortUrl);
    }

    /**
     * Getter: Timestamp
     * @return int
     */
    public function getTimestamp(): int {
        return $this->timestamp;
    }

    /**
     * Setter: Timestamp
     *
     * @param int $timestamp
     * @return void
     */
    public function setTimestamp(int $timestamp): void {
        $this->timestamp = $timestamp;
    }

    /**
     * Getter: TargetURL
     *
     * @return string
     */
    public function getTargetUrl(): string {
        return $this->targetUrl;
    }

    /**
     * Setter: TargetURL
     *
     * @param string $targetUrl
     * @return void
     */
    public function setTargetUrl(string $targetUrl): void {
        $this->targetUrl = $targetUrl;
    }

    /**
     * Getter: Notification
     *
     * @return bool
     */
    public function isNotify(): bool {
        return $this->notify;
    }

    /**
     * Setter: Notification
     *
     * @param bool $notify
     * @return void
     */
    public function setNotify(bool $notify): void {
        $this->notify = $notify;
    }

    /**
     * Getter Identifier
     *
     * @return string
     */
    public function getIdentifier(): string {
        return $this->identifier;
    }

    /**
     * Setter: Identifier
     *
     * @param string $identifier
     * @return void
     */
    public function setIdentifier(string $identifier): void {
        $this->identifier = $identifier;
    }

    /**
     * Getter: numViewsLeft Count
     *
     * @return int
     */
    public function getNumViewsLeft(): int {
        return $this->numViewsLeft;
    }

    /**
     * Setter: numViewsLeft
     *
     * @param int $numViewsLeft
     * @return void
     */
    public function setNumViewsLeft(int $numViewsLeft): void {
        $this->numViewsLeft = $numViewsLeft;
    }

    /**
     * Transforms this to an array instead of an stdClass object
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'targetUrl' => $this->targetUrl,
            'notify' => $this->notify,
            'identifier' => $this->identifier,
            'numViewsLeft' => $this->numViewsLeft,
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Returns an instance of this Model initiated by an indexed or associative array
     *
     * @param array $array
     * @return ShortUrl
     * @throws Exception
     */
    public static function fromArray(array $array): ShortUrl {
        if ( array_is_list($array) ) {
            if ( !array_key_exists(0, $array) || !array_key_exists(1, $array) ) {
                throw new Exception('Wrong number of Key/Value pair in Array');
            }

            $shortUrl = (string)($array[0]);
            $targetUrl = (string)($array[1]);
            $notify = (bool)($array[2] ?? false);
            $identifier = (string)($array[3] ?? '');
            $numViewsLeft = (int)($array[4] ?? 1);
            $timestamp = (int)($array[5] ?? 0);
        } else { // Associate Array handling
            if ( !array_key_exists('shortUrl', $array) || !array_key_exists('targetUrl', $array) ) {
                throw new Exception('Wrong number of Key/Value pair in Array');
            }

            $shortUrl = (string)($array['shortUrl']);
            $targetUrl = (string)($array['targetUrl']);
            $notify = (bool)($array['notify'] ?? false);
            $identifier = (string)($array['identifier'] ?? '');
            $numViewsLeft = (int)($array['numViewsLeft'] ?? 1);
            $timestamp = (int)($array['timestamp'] ?? 0);
        }

        // Return new instance of ShortURL
        return new self(
            $shortUrl,
            $targetUrl,
            $notify,
            $identifier,
            $numViewsLeft,
            $timestamp
        );
    }

    /**
     * Transforms this object to a JSON String
     *
     * @return string
     */
    public function toJson(): string {
        return json_encode($this->toArray());
    }
}