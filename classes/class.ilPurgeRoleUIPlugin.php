<?php
/**
 * Class ilPurgeRoleUIPlugin
 * @author  Kalamun <rp@kalamun.net>
 * @version $Id$
 */

 include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");

 class ilPurgeRoleUIPlugin extends ilUserInterfaceHookPlugin
 {
    const CTYPE = "Services";
    const CNAME = "UIComponent";
    const SLOT_ID = "uihk";
    const PLUGIN_NAME = "PurgeRoleUI";

    protected static $instance = null;

    public function __construct()
    {
        parent::__construct();
    }

    public static function getInstance() : ilPurgeRoleUIPlugin
    {
        if (null === self::$instance) {
            return self::$instance = ilPluginAdmin::getPluginObject(
                self::CTYPE,
                self::CNAME,
                self::SLOT_ID,
                self::PLUGIN_NAME
            );
        }

        return self::$instance;
    }

    public function getPluginName() : string
    {
        return self::PLUGIN_NAME;
    }
}
 