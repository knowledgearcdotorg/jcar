<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Model for assisting the JCar lookup form field.
 */
class JCarModelItem extends JModelForm
{
    /**
     * Method to get a form object.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_jcar.item',
            'configuration',
            array('control'=>'jform', 'load_data'=>$loadData));

        if (empty($form)) {
            return false;
        }

        $plugins = JPluginHelper::getPlugin('jcar');

        foreach ($plugins as $plugin) {
            $path =
                JPATH_ROOT.
                "/plugins/jcar/".
                $plugin->name.
                "/forms/configuration.xml";

            JFactory::getLanguage()->load('plg_jcar_'.$plugin->name);

            $form->loadFile($path, false);
        }

        return $form;
    }
}
