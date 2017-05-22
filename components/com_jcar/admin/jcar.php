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

if (!JFactory::getUser()->authorise('core.manage', 'com_jcar')) {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller = JControllerLegacy::getInstance('jcar');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
