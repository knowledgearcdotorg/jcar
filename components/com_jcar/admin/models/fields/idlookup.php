<?php
/**
 * @package     JCar.Component
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JLoader::import('joomla.form.helper');
JFormHelper::loadFieldClass('plugins');

use \Joomla\Utilities\ArrayHelper;

/**
 * Id lookup field.
 */
class JCarFormFieldIdLookup extends JFormFieldPlugins
{
    protected $type = 'JCar.IdLookup';

    protected function getInput()
    {
        $this->lookup = "";

        $idLookupPlugin = "";

        $parts = explode(":", $this->value, 2);

        if (count($parts) == 2) {
            $idLookupPlugin = ArrayHelper::getValue($parts, 0);
            $this->lookup = ArrayHelper::getValue($parts, 1);
        }

        $this->options = parent::getOptions();

        $this->plugin = $idLookupPlugin;

        $html = JLayoutHelper::render(
            "jcar.form.fields.idlookup",
            $this,
            JPATH_ROOT."/administrator/components/com_jcar/layouts");

        return $html;
    }
}