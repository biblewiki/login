<?php
declare(strict_types = 1);

/**
 * Class Session
 *
 * @package ki\kgweb\ki
 */
class Session {
    public $userId = 'guest';
    public $languageId = '';
    public $userRole = null;
    public $loginType = null;

    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------
    public function clear(): void {
        $this->userId = 'guest';
        $this->languageId = '';
        $this->userRole = null;
        $this->loginType = null;
    }
}
