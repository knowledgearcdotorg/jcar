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
    public function testSef()
    {
            $model = $this->getModel('item', 'jcarmodel', ['ignore_request'=>true]);
            $model->setState("item.id", "dspace:20");
echo "<br/>";
            if ($url = $model->generateSefRoute()) {
                echo 'url=.'.JRoute::_($url);
            }
echo "<br/>";
            $model->setState("item.id", "dspace:21");

            if ($url = $model->generateSefRoute()) {
                echo 'url=.'.JRoute::_($url);
            }
echo "<br/>";
    }

    public function display($cachable = false, $urlparams = array())
    {
        if (JFactory::getApplication()->input->getCmd('view') == 'item') {
            $model = $this->getModel('item');

            if ($url = $model->generateSefRoute()) {
                $this->setRedirect(JRoute::_($url));
            }
        }

        parent::display($cachable, $urlparams);
    }
}
