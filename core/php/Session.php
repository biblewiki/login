<?php
declare(strict_types = 1);

namespace biwi;

/**
 * Class Session
 *
 * @package biwi\edit
 */
class Session {
    public $userId = 0;
    public $languageId = '';
    public $userRole = null;
    public $loginType = null;

    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------
    public function clear(): void {
        $this->userId = 0;
        $this->languageId = '';
        $this->userRole = null;
        $this->loginType = null;
    }
}
