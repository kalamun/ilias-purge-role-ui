<?php
//include_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Class ilPurgeRoleUIUIHookGUI
 * @author Kalamun <rp@kalamun.net>
 * @version $Id$
 * @ilCtrl_isCalledBy ilPurgeRoleUIUIHookGUI: ilObjRoleGUI, ilAdministrationGUI, ilPurgeRoleUI
 */

class ilPurgeRoleUIUIHookGUI extends ilUIHookPluginGUI {
  protected $dic;
  protected $plugin;
  protected $lng;
  protected $request;
  protected $user;
  protected $ctrl;
  protected $object;
  
  public function __construct()
  {
    global $DIC;
    $this->dic = $DIC;
    $this->plugin = ilPurgeRoleUIPlugin::getInstance();
    $this->lng = $this->dic->language();
    $this->request = $this->dic->http()->request();
    $this->user = $DIC->user();
    $this->ctrl = $DIC->ctrl();
    $this->object = $DIC->object();

    $role_id = $_REQUEST['obj_id'];

    if($_REQUEST['cmdClass'] === 'ilobjrolegui' && $_REQUEST['baseClass'] === 'ilAdministrationGUI' && !empty($role_id) && isset($_REQUEST["purge"][$role_id])) {
      $settings = [
        "active" => !!$_REQUEST["purge"][$role_id]['active'],
        "day" => $_REQUEST["purge"][$role_id]['day'],
        "month" => $_REQUEST["purge"][$role_id]['month'],
      ];

      global $tpl, $ilCtrl, $lng, $DIC, $ilDB;
      $table_name = "cron_crnhk_xpurgerole";

      $db_query = $ilDB->query("SELECT * FROM " . $table_name . " WHERE role_id=" . intval($role_id));
      $db_row = $ilDB->fetchAssoc($db_query);

      if( !empty($db_row['role_id']) ) {
        // update
        $ilDB->update($table_name, [
          "day" => ["integer", intval($settings['day'])],
          "month" => ["integer", intval($settings['month'])],
          "active" => ["integer", intval($settings['active'])],
        ], [
          "role_id" => ["integer", $role_id],
          "rule_id" => ["integer", 0],
        ]);

      } else {
        // insert new
        $ilDB->insert($table_name, [
          "role_id" => ["integer", $role_id],
          "rule_id" => ["integer", 0],
          "day" => ["integer", intval($settings['day'])],
          "month" => ["integer", intval($settings['month'])],
          "active" => ["integer", intval(!empty($settings['active']))],
        ]);
      }
    }
  }

  public function performCommand(/*string*/ $cmd)/*:void*/
  {
        switch ($cmd)
        {
          default:
              break;
        }
  }

  /**
	 * Modify HTML output of GUI elements. Modifications modes are:
	 * - ilUIHookPluginGUI::KEEP (No modification)
	 * - ilUIHookPluginGUI::REPLACE (Replace default HTML with your HTML)
	 * - ilUIHookPluginGUI::APPEND (Append your HTML to the default HTML)
	 * - ilUIHookPluginGUI::PREPEND (Prepend your HTML to the default HTML)
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param string $a_par array of parameters (depend on $a_comp and $a_part)
	 *
	 * @return array array with entries "mode" => modification mode, "html" => your html
	 */
	function getHTML($a_comp = false, $a_part = false, $a_par = array()) {
    if ($a_part === 'template_get'
    && !empty($_REQUEST['obj_id'])
    && is_array( $a_par )
    && $a_par['tpl_id'] === 'Services/Form/tpl.form.html' ) {

      global $tpl, $ilCtrl, $lng, $DIC, $ilDB;
      $table_name = "cron_crnhk_xpurgerole";

      require_once("./Services/Table/classes/class.ilTableGUI.php");
      require_once("./Services/Form/classes/class.ilFormGUI.php");
  
      $html = $a_par['html'];

      $days = [];
      for($i = 1; $i <= 31; $i++) {
          $days[$i] = $i;
      }

      $months = [];
      for($i = 1; $i <= 12; $i++) {
          $months[$i] = $this->plugin->txt("month_" . $i);
      }

      $role_id = $_REQUEST['obj_id'];
      $db_query = $ilDB->query("SELECT * FROM " . $table_name . " WHERE role_id=" . intval($role_id));
      $db_values = [];
      while($db_row = $ilDB->fetchAssoc($db_query)) {
        $db_values[ $db_row["role_id"] ] = $db_row;
      }
      
      ob_start();
      ?>
      <div class="form-group" id="il_prop_purge">
        <label class="col-sm-3 control-label"><?= $this->plugin->txt("purge_active"); ?></label>
        <div class="col-sm-9">
          <div class="checkbox">
            <?php
            $checkbox_input = new ilCheckboxInputGUI($this->plugin->txt("purge_active"), $class);
            $checkbox_input->setPostVar("purge[" . $role_id . "][active]");
            $checkbox_input->setOptionTitle("");
            $checkbox_input->setChecked(!!$db_values[ $role_id ]['active']);
            $checkbox_input->setValue(true);
            echo $checkbox_input->render();
            ?>
          </div>
        </div>
      </div>
      <div class="form-group" id="il_prop_purge_date">
        <label for="pro" class="col-sm-3 control-label"></label>
        <div class="col-sm-9">
          <table>
            <tr>
              <td style="vertical-align: middle; padding-right: 10px;"><?= $this->plugin->txt("day"); ?></td>
              <td style="vertical-align: middle; padding-right: 30px;">
                <?php
                  $select_input = new ilSelectInputGUI($this->plugin->txt("day"), $class);
                  $select_input->setPostVar("purge[" . $role_id . "][day]");
                  $select_input->setOptions($days);
                  $select_input->setValue($db_values[ $role_id ]['day']);
                  echo $select_input->render();
                ?>
              </td>
              <td style="vertical-align: middle; padding-right: 10px;"><?= $this->plugin->txt("month"); ?></td>
              <td  style="vertical-align: middle; padding-right: 30px;">
                  <?php
                  $select_input = new ilSelectInputGUI($this->plugin->txt("month"), $class);
                  $select_input->setPostVar("purge[" . $role_id . "][month]");
                  $select_input->setOptions($months);
                  $select_input->setValue($db_values[ $role_id ]['month']);
                  echo $select_input->render();
                  ?>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <?php
      $fragment = ob_get_clean();

      $html = str_replace(
        '<div class="ilFormFooter clearfix">', 
        $fragment . '<div class="ilFormFooter clearfix">',
        $html);
      return ["mode" => ilUIHookPluginGUI::REPLACE, "html" => $html];
    }
    return ["mode" => ilUIHookPluginGUI::KEEP, "html" => ""];
  }

  function getTabs() {
    parent::getTabs();
  }
  
  /**
	 * Modify GUI objects, before they generate ouput
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param string $a_par array of parameters (depend on $a_comp and $a_part)
	 */
  function modifyGUI($a_comp, $a_part, $a_par = [])
	{
	}
  
  /**
   * Checks if the received command can be executed and redirects the command into the structure presentation class
   * for further processing
   * @throws Exception
   */
  public function executeCommand()
  {
  }  

  public function setCreationMode($a_mode = false) {
    return false;
  }
}