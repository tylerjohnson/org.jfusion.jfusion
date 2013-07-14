<?php
/**
* @package JFusion
* @subpackage Elements
* @author JFusion development team
* @copyright Copyright (C) 2008 JFusion. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
* Require the Jfusion plugin factory
*/
require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jfusion'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'model.factory.php';
require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jfusion'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'model.jfusion.php';

/**
* Defines the forum select list for JFusion forum plugins
* @package JFusion
*/
class JFormFieldJFusionUsergroupList extends JFormField
{
    public $type = 'JFusionUsergroupList';

    /**
     * Get an element
     *
     * @return string html
     */
    protected function getInput()
    {
        global $jname;
	    try {
		    if ($jname){
			    if (JFusionFunction::validPlugin($jname)) {
				    $JFusionPlugin = JFusionFactory::getAdmin($jname);

				    $grouptype = (string) $this->element['grouptype'];
				    $usergroups = $JFusionPlugin->getUsergroupList($grouptype);

				    $multiple = ($this->element['multiple']) ? ' MULTIPLE ' : '';
				    if (!empty($usergroups)) {
					    $param_name = ($multiple) ? $this->formControl.'['.$this->group.']['.$this->fieldname.'][]' : $this->formControl.'['.$this->group.']['.$this->fieldname.']';
					    return JHTML::_('select.genericlist', $usergroups, $param_name, $multiple,
						    'id', 'name', $this->value);
				    } else {
					    return '';
				    }
			    } else {
				    return JText::_('SAVE_CONFIG_FIRST');
			    }
		    } else {
			    throw new Exception('Programming error: You must define global $jname before the JParam object can be rendered.');
		    }
	    } catch (Exception $e) {
		    return '<span style="float:left; margin: 5px 0; font-weight: bold;">'.$e->getMessage().'</span>';
	    }
    }
}

