<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Displays a list of JCar Routes.
 */
class JCarViewRoutes extends JViewLegacy
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * An instance of the JPagination class.
     *
     * @var  JPagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  object
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  JForm
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        $this->addToolbar();

        JCarHelper::addSubmenu('routes');

        $this->sidebar = JHtmlSidebar::render();

        return parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {
        $canDo = JHelperContent::getActions('com_jcar');

        $user  = JFactory::getUser();

        JToolbarHelper::title(JText::_('COM_JCAR_ROUTES_HEADING'));

        if ($canDo->get('core.create')) {
            JToolbarHelper::addNew('route.add');
        }

        if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own'))) {
            JToolbarHelper::editList('route.edit');
        }

        if ($canDo->get('core.edit.state')) {
            JToolbarHelper::publish('routes.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::checkin('routes.checkin');
        }

        if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'routes.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($canDo->get('core.edit.state')) {
            JToolbarHelper::trash('routes.trash');
        }

        if ($user->authorise('core.admin', 'com_jcar') || $user->authorise('core.options', 'com_jcar')) {
            JToolbarHelper::preferences('com_jcar');
        }

        JToolbarHelper::help('JHELP_COMPONENTS_JCAR_ROUTES');

        JHtmlSidebar::setAction('index.php?option=com_jcar');
    }
}
