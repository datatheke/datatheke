<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Datatheke\Bundle\CoreBundle\Document\Library;
use Datatheke\Bundle\CoreBundle\Document\Collection;

class MapController extends BaseController
{
    /**
     * @Route("/libraries/{id}/maps", name="library_maps")
     * @Route("/library/{id}/maps")
     *
     * @ParamConverter("library", options={"param"="id"})
     *
     * @Template
     */
    public function mapsAction(Library $library)
    {
        // Redirect to the first map if we have one
        $maps = $this->get('datatheke.manager.collection')->getMaps($library);
        if (count($maps)) {
            $map = current($maps);

            return $this->forward('DatathekeFrontendBundle:Map:map', array('id' => $map->getId()));
        }

        return array(
            'library'   => $library,
            'maps'      => array(),
            'activeMap' => null
            );
    }

    /**
     * @Route("/maps/{id}", name="map")
     * @Route("/map/{id}")
     *
     * @ParamConverter("collection", options={"param"="id"})
     *
     * @Template
     */
    public function mapAction(Collection $collection)
    {
        $library = $collection->getLibrary();
        $maps = $this->get('datatheke.manager.collection')->getMaps($library);

        return array(
            'library'   => $library,
            'maps'      => $maps,
            'activeMap' => $collection
            );
    }

    /**
     * @Route("/maps/{id}/markers", name="map_markers")
     * @Route("/map/{id}/markers")
     *
     * @ParamConverter("collection", options={"param"="id"})
     */
    public function markersAction(Collection $collection)
    {
        $fields = array();
        foreach ($collection->getFields() as $field) {
            if ('coordinates' === $field->getType()) {
                $fields[] = $field;
            }
        }

        $items = $this->get('datatheke.manager.item')->getAdapter($collection)->getItems(0, 1000);

        $response = array();
        foreach ($items as $item) {
            foreach ($fields as $field) {
                $property = '_'.$field->getId();
                $coord =  $item->$property;

                if ($coord) {
                    if (!$field->isMultiple()) {
                        $coord = array($coord);
                    }

                    foreach ($coord as $c) {
                        $response[] = array(
                            'longitude' => $c->getLongitude(),
                            'latitude'  => $c->getLatitude(),
                            'content'   => htmlentities((string) $item)
                        );
                    }
                }
            }
        }

        return new JsonResponse($response);
    }
}
