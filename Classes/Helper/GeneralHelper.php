<?php
declare(strict_types=1);

namespace Helper;

class GeneralHelper {

    /**
     * Returns the directory of the calling script
     *
     * @return string
     */
    public static function getCallerPath(): string {
        $caller = $_SERVER['SCRIPT_FILENAME'];
        return realpath(dirname($caller));
    }

}