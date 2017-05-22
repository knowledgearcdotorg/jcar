<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('JCarHelper', JPATH_ROOT.'/administrator/components/com_jcar/helpers/jcar.php');

/**
 * Displays a control panel containing various JCar information.
 */
class JCarViewRoute extends JViewLegacy
{
    protected $item;

    protected $form;

    public function display($tpl = null)
    {
        $this->item = $this->get("Item");

        $this->form = $this->get("Form");

        $input = JFactory::getApplication()->input;
        $eName = $input->getCmd('e_name');
        $eName    = preg_replace('#[^A-Z0-9\-\_\[\]]#i', '', $eName);
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_CONTENT_PAGEBREAK_DOC_TITLE'));
        $this->eName = &$eName;

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user       = JFactory::getUser();
        $userId     = $user->id;
        $isNew      = ($this->item->id == 0);
        $checkedOut = !(
            $this->item->checked_out == 0 ||
            $this->item->checked_out == $userId
        );

        $canDo = JHelperContent::getActions('com_jcar');

        JToolbarHelper::title(
            $isNew ? JText::_('COM_JCAR_ROUTE_NEW') : JText::_('COM_JCAR_ROUTE_EDIT'));

        if ($isNew) {
            if ($isNew) {
                JToolbarHelper::apply('route.apply');
                JToolbarHelper::save('route.save');
                JToolbarHelper::save2new('route.save2new');
            }

            JToolbarHelper::cancel('route.cancel');
        } else {
            $itemEditable = $canDo->get('core.edit') ||
                ($canDo->get('core.edit.own') &&
                $this->item->created_by == $userId);

            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable) {
                JToolbarHelper::apply('route.apply');
                JToolbarHelper::save('route.save');
                JToolbarHelper::save2new('route.save2new');
            }

            JToolbarHelper::cancel('route.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
