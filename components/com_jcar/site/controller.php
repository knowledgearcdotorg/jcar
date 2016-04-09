<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * The root controller.
 */
class JCarController extends JControllerLegacy
{
    /**
     * Reroutes a request based on the item id.
     */
    public function reroute()
    {
        $itemId = JFactory::getApplication()->input->getInt('Itemid');
        $this->setRedirect(JRoute::_("index.php?Itemid=".$itemId));
    }

    public function display($cachable = false, $urlparams = array())
    {
        if (JFactory::getApplication()->input->getCmd('view') == 'item') {
            $model = $this->getModel('item');

            if ($menuItemId = $model->generateSefMenuItem()) {
                $this->setRedirect("index.php?option=com_jcar&task=reroute&Itemid=".$menuItemId);
            }
        }

        parent::display($cachable, $urlparams);
    }
}
