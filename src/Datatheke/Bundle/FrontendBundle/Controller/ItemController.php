<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Datatheke\Bundle\PagerBundle\Pager\Filter;
use Datatheke\Bundle\CoreBundle\Document\Collection;

class ItemController extends BaseController
{
    /**
     * @Route("/collections/{collection}/item/{item}/link/{property}", name="collection_linked")
     * @Route("/collection/{collection}/item/{item}/link/{property}")
     *
     * @ParamConverter("collection")
     *
     * @Template("DatathekeFrontendBundle:Collection:collection.html.twig")
     */
    public function linkedAction(Request $request, Collection $collection, $item, $property)
    {
        $datagrid = $this->get('datatheke.manager.item')->getDataGrid($collection);
        $datagrid->getPager()->getAdapter()->setFilter(new Filter(array('_'.$property), array(new \MongoId($item)), array(Filter::OPERATOR_EQUALS)), 'linked');

        $view = $datagrid
            ->handleRequest($request)
            ->setRoute('collection_linked')
            ->setParameters(array(
                'collection' => $collection->getId(),
                'item'       => $item,
                'property'   => $property
            ));

        return array('collection' => $collection, 'datagrid' => $view);
    }

    /**
     * @Route("/collections/{id}/rest/{connector}", name="collection_rest")
     * @Route("/collection/{id}/rest/{connector}")
     *
     * @ParamConverter("collection", options={"param"="id"})
     *
     * @Template()
     */
    public function restAction(Collection $collection, $connector)
    {
        if ('typeahead' === $connector) {
            $connector = 'bootstrap_typeahead';
        }

        if (!in_array($connector, array('datatheke', 'autocomplete', 'datatables', 'jqgrid', 'slickgrid', 'bootstrap_typeahead'))) {
            throw new \Exception('Invalid REST connector');
        }

        $adapter = $this->get('datatheke.manager.item')->getAdapter($collection);
        $datagrid = $this->get('datatheke.datagrid')->createHttpDataGrid($adapter, array(), $connector);
        $datagrid->getColumn('deleted')->hide();

        return $datagrid->handleRequest($this->getRequest());
    }

    /**
     * @Route("/collections/{id}/add", name="item_add")
     * @Route("/collection/{id}/add")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_WRITER", "param"="id"})
     *
     * @Template("DatathekeFrontendBundle:Item:form.html.twig")
     */
    public function addAction(Request $request, Collection $collection)
    {
        $item = $this->get('datatheke.manager.item')->create($collection);

        return $this->processForm($request, $collection, $item);
    }

    /**
     * @Route("/collections/{id}/edit/{item}", name="item_edit")
     * @Route("/collection/{id}/edit/{item}")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_WRITER", "param"="id"})
     *
     * @Template("DatathekeFrontendBundle:Item:form.html.twig")
     */
    public function editAction(Request $request, Collection $collection, $item)
    {
        $item = $this->get('datatheke.manager.item')->find($collection, $item);
        $form = $this->get('form.factory')->create('datatheke_item', $item, array('collection' => $collection));

        $response = $this->processForm($request, $collection, $item);
        if ($response instanceof Response) {
            return $response;
        }

        $response['prevItem'] = $this->get('datatheke.manager.item')->getPreviousItem($collection, $item);
        $response['nextItem'] = $this->get('datatheke.manager.item')->getNextItem($collection, $item);
        $response['linkedCollections'] = $this->get('datatheke.manager.collection')->getLinkedCollections($collection);

        return $response;
    }

    /**
     *
     */
    protected function processForm(Request $request, Collection $collection, $item)
    {
        $action = $item->getId() ? 'edit' : 'create';
        $form = $this->get('form.factory')->create('datatheke_item', $item, array('collection' => $collection));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('datatheke.manager.item')->save($item);
            $this->get('session')->getFlashBag()->add('item_success', 'Enregistrement effectué');

            return $this->redirect($this->generateUrl('item_edit', array('id' => $collection->getId(), 'item' => $item->getId())));
        }

        return array(
            'form' => $form->createView(),
            'item' => $item,
            'collection' => $collection,
            'action' => $action
        );
    }

    /**
     * @Route("/collections/{id}/view/{item}", name="item_view")
     * @Route("/collection/{id}/view/{item}")
     *
     * @ParamConverter("collection", options={"param"="id"})
     *
     * @Template("DatathekeFrontendBundle:Item:form.html.twig")
     */
    public function viewAction(Collection $collection, $item)
    {
        $item = $this->get('datatheke.manager.item')->find($collection, $item);
        $form = $this->get('form.factory')->create('datatheke_item', $item, array('collection' => $collection, 'read_only' => true));

        $response['prevItem'] = $this->get('datatheke.manager.item')->getPreviousItem($collection, $item);
        $response['nextItem'] = $this->get('datatheke.manager.item')->getNextItem($collection, $item);
        $response['linkedCollections'] = $this->get('datatheke.manager.collection')->getLinkedCollections($collection);

        return array(
            'form' => $form->createView(),
            'item' => $item,
            'collection' => $collection,
            'action' => 'view',
            'prevItem' => $this->get('datatheke.manager.item')->getPreviousItem($collection, $item),
            'nextItem' => $this->get('datatheke.manager.item')->getNextItem($collection, $item),
            'linkedCollections' => $this->get('datatheke.manager.collection')->getLinkedCollections($collection)
        );
    }

    /**
     * @Route("/collections/{id}/delete/{item}", name="item_delete")
     * @Route("/collection/{id}/delete/{item}")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_WRITER", "param"="id"})
     */
    public function deleteAction(Collection $collection, $item)
    {
        $this->get('datatheke.manager.item')->delete($collection, $item);
        $this->get('session')->getFlashBag()->add('item_operation', 'delete');

        return $this->redirect($this->generateUrl('collection', array('id' => $collection->getId())));
    }

    /**
     * @Route("/collections/{id}/export", name="item_export")
     * @Route("/collection/{id}/export")
     *
     * @ParamConverter("collection", options={"param"="id"})
     */
    public function exportAction(Request $request, Collection $collection)
    {
        $datagrid = $this->get('datatheke.manager.item')->getDataGrid($collection);
        $datagrid->getPager()->setItemCountPerPage(1000000);
        $datagrid->getHandler()
            ->setOption('item_count_per_page_param', null)
            ->setOption('current_page_number_param', null)
        ;

        $datagrid = $datagrid->handleRequest($request);

        $phpExcel = new \PHPExcel();
        $objWriter = new \PHPExcel_Writer_Excel2007($phpExcel);
        $sheet = $phpExcel->getActiveSheet();

        // header
        $sheet->getStyle('1')->getFont()->setBold(true);
        $index = 0;
        foreach ($datagrid->getColumns() as $column) {
            $sheet->getColumnDimensionByColumn($index)->setAutosize(true);
            $sheet->setCellValueByColumnAndRow($index, 1, $column->getLabel());
            $index++;
        }

        //Datas
        $row = 2;
        foreach ($datagrid->getPager()->getItems() as $item) {
            $index = 0;
            foreach ($datagrid->getColumns() as $column) {
                $value = (string) $datagrid->getColumnValue($column, $item);
                $sheet->setCellValueByColumnAndRow($index, $row, $value);
                $index++;
            }
            $row++;
        }

        ob_start();
        $objWriter->save('php://output');
        $content = ob_get_clean();

        return new Response($content, 200, array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="export.xlsx"',
            'Cache-Control' => 'max-age=0'
        ));
    }
}
