<?php
/**
 * The DSpace template layout provides a DSpace-specific display of
 * communities.
 *
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$dispatcher = JEventDispatcher::getInstance();
JPluginHelper::importPlugin('jcar', "dspace");

// Trigger the data preparation event.
$response = $dispatcher->trigger('onJCarCommunitiesRetrieve');
$response = JArrayHelper::getValue($response, 0);

$this->communities = $response;

echo $this->loadTemplate('communities');