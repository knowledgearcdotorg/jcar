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
 * Models a list of JCar routes.
 *
 * @package     JSpace.Component
 * @subpackage  Administrator
 */
class JCarModelRoutes extends JModelList
{
    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $db->quoteName(
                explode(
                    ', ',
                    $this->getState(
                        'list.select',
                        'a.id, '.
                        'a.title, '.
                        'a.alias, '.
                        'a.item_id, '.
                        'a.checked_out, '.
                        'a.checked_out_time, '.
                        'a.state, '.
                        'a.created'
                    )
                )
            )
        );

        $query->from($db->qn('#__jcar_routes', 'a'));

        // Filter by state state
        $state = $this->getState('filter.state');

        if (is_numeric($state)) {
            $query->where($db->qn('a.state').' = '.(int)$state);
        } elseif ($state === '') {
            $query->where($db->qn('a.state').' IN (0, 1)');
        }

        return $query;
    }
}
