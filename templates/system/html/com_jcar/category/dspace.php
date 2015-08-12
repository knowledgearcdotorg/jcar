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

$id = JFactory::getApplication()->input->getInt('id');

$dispatcher = JEventDispatcher::getInstance();
JPluginHelper::importPlugin('jcar', "dspace");

// Trigger the data preparation event.
$response = $dispatcher->trigger('onJCarCommunityRetrieve', $id);
$this->community = JArrayHelper::getValue($response, 0, array());
?>
<section id="jcarCategory">
    <header>
        <h1><?php echo $this->community->name; ?></h1>
        <div><?php echo $this->community->description; ?></div>
    </header>

    <?php
    $this->communities = $this->community->subCommunities;

    echo $this->loadTemplate('communities');

    $this->collections = $this->community->collections;

    echo $this->loadTemplate('collections');
    ?>
</section>