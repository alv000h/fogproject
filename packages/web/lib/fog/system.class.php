<?php
/**
 * System, the basic system layout.
 *
 * PHP Version 5
 *
 * This just presents the system variables
 *
 * @category System
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * System, the basic system layout.
 *
 * @category System
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class System
{
    const PHP_REQUIRED = '5.3.0';
    /**
     * Checks the php version against what we require.
     *
     * @return void
     */
    private static function _versionCompare()
    {
        $msg = '';
        if (false === version_compare(PHP_VERSION, PHP_REQUIRED, '>=')) {
            $msg = sprintf(
                '%s. %s %s, %s %s %s.',
                _('Your system PHP Version is not sufficient'),
                _('You have version'),
                PHP_VERSION,
                _('version'),
                PHP_REQUIRED,
                _('is required')
            );
        }
        if ($msg) {
            die($msg);
        }
    }
    /**
     * Constructs the system variables.
     */
    public function __construct()
    {
        self::_versionCompare();
        define('FOG_VERSION', '1.3.1');
        define('FOG_SCHEMA', 245);
        define('FOG_BCACHE_VER', 112);
        define('FOG_SVN_REVISION', 6053);
        define('FOG_CLIENT_VERSION', '0.11.8');
    }
}
