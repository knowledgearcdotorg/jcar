<?php
/**
 * @package     JCar.Plugin
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Retrieves information from a REST API-enabled DSpace archive.
 */
class PlgJCarDSpace extends JPlugin
{
    /**
     * Gets an item from a REST API-enabled DSpace archive.
     *
     * @param  int    $id  The id of an item to retrieve from the DSpace archive.
     *
     * @param  mixed  An item from the REST API-enabled DSpace archive, or null if nothing could be found.
     */
    public function onJCarItemAfterRetrieve($id)
    {
        $parts = explode(":", $id, 2);

        if (count($parts) == 2) {
            $id = JArrayHelper::getValue($parts, 1);
        }

        return $this->getItem($id);
    }

    /**
     * Gets an item from the REST API-enabled DSpace archive.
     *
     * @param  int    $id  The id of an item to retrieve from the DSpace archive.
     *
     * @param  mixed  An item from the REST API-enabled DSpace archive, or null if nothing could be found.
     */
    private function getItem($id)
    {
        $http = JHttpFactory::getHttp();
        $response = $http->get($this->params->get('rest_url').'/items/'.$id.'.json');

        if ($response->code === 200) {
            $data = json_decode($response->body);

            $array = array();
            foreach ($data->metadata as $metadata) {
                $key = $metadata->schema.'.'.$metadata->element;

                if (isset($metadata->qualifier)) {
                    $key .= '.'.$metadata->qualifier;
                }

                if (!JArrayHelper::getValue($array, $key)) {
                    $array[$key] = array();
                }

                $array[$key][] = $metadata->value;
            }

            $data->metadata = $array;

            $data->bundles = $this->getBundles($id);

            return $data;
        } else {
            throw new Exception("An error has occurred.", $response->code);
        }
    }

    /**
     * Gets bundle information for the specified item from the REST API-enabled DSpace archive.
     *
     * @param  int    $item  The item id of the bundles to retrieve from the DSpace archive.
     *
     * @param  array  Bundle information for the specified item from the REST API-enabled DSpace archive.
     */
    private function getBundles($item)
    {
        $http = JHttpFactory::getHttp();
        $response = $http->get($this->params->get('rest_url').'/items/'.$item.'/bundles.json');

        if ($response->code === 200) {
            $data = json_decode($response->body);

            return $data;
        } else {
            throw new Exception("An error has occurred.", $response->code);
        }
    }
}