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

// If the category is a collection, redirect to the default template.
// The collection layout can be overridden using standard Joomla! html
// overrides.
$parts = explode(":", JCarHelper::parseId($this->item->id));

if (count($parts) == 1) {
    $this->setLayout('default');

    echo $this->loadTemplate();

    return null;
}
?>
<section id="jcarCategory">
    <header>
        <h1><?php echo $this->item->name; ?></h1>
        <div><?php echo $this->item->description; ?></div>
    </header>

    <?php
    $this->communities = $this->item->subCommunities;

    echo $this->loadTemplate('communities');

    $this->collections = $this->item->collections;

    echo $this->loadTemplate('collections');
    ?>
</section>