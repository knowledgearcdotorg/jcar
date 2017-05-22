<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/helpers/route.php';

$controller = JControllerLegacy::getInstance('JCar');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
