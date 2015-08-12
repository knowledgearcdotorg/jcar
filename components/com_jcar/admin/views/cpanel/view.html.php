<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('JCarHelper', JPATH_ROOT.'/administrator/components/com_jcar/helpers/jcar.php');

/**
 * Displays a control panel containing various JCar information.
 */
class JCarViewCPanel extends JViewLegacy
{
    protected $option;
    protected $item;

    public function display($tpl = null)
    {
        $this->option = JFactory::getApplication()->input->getCmd('option');
        $this->item = $this->get('Item');

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        $user  = JFactory::getUser();

        JToolbarHelper::title(JText::_('COM_JCAR_CPANEL_TITLE'), 'stack article');

        if ($user->authorise('core.admin', $this->option)) {
            JToolbarHelper::preferences($this->option);
        }
    }
}