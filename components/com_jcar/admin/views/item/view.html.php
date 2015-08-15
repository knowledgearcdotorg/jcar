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
class JCarViewItem extends JViewLegacy
{
    protected $form;

    public function display($tpl = null)
    {
        $this->form = $this->get("Form");

        $input = JFactory::getApplication()->input;
        $eName = $input->getCmd('e_name');
        $eName    = preg_replace('#[^A-Z0-9\-\_\[\]]#i', '', $eName);
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_CONTENT_PAGEBREAK_DOC_TITLE'));
        $this->eName = &$eName;

        parent::display($tpl);
    }
}
