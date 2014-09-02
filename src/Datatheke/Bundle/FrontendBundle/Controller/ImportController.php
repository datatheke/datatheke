<?php

namespace Datatheke\Bundle\FrontendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Datatheke\Bundle\CoreBundle\Document\Library;
use Datatheke\Bundle\CoreBundle\Document\Collection;
use Datatheke\Bundle\CoreBundle\Document\Field;

class ImportController extends BaseController
{
    /**
     * @Route("/collections/{id}/import", name="item_import")
     * @Route("/collection/{id}/import")
     *
     * @ParamConverter("collection", options={"right"="COLLECTION_ADMIN", "param"="id"})
     *
     * @Template()
     */
    public function importItemsAction(Request $request, Collection $collection)
    {
        $importPath = $this->container->getParameter('import_path');
        $dm         = $this->get('doctrine.odm.mongodb.document_manager');

        $fileId        = null;
        $datas         = null;
        $id            = $collection->getId();
        $ignoreRows    = (int) $request->get('ignoreRows', 1);
        $ignoreColumns = (int) $request->get('ignoreColumns', 0);

        if ($request->getMethod() === 'POST') {

            if ($request->files->has('importFile')) {
                $file = $request->files->get('importFile');
                $fileId = uniqid();
                $fileName = $id.'_'.$this->getUser()->getId().'_'.$fileId;
                $file->move($importPath, $fileName);
            } else {
                $fileId = $request->get('fileId');
                $fileName = $id.'_'.$this->getUser()->getId().'_'.$fileId;
            }

            $objPHPExcel = \PHPExcel_IOFactory::load($importPath.'/'.$fileName);

            $sheet = $objPHPExcel->getActiveSheet();

            $column    = $ignoreColumns + ord('A');
            $row       = $ignoreRows + 1;
            $imported  = 0;
            $limit     = $sheet->getHighestRowAndColumn();
            $maxRow    = $limit['row'];
            $maxColumn = $limit['column'];

            if ($row > $maxRow || $column > ord($maxColumn)) {
                $datas = array();
            } else {
                $datas = current($sheet->rangeToArray(chr($column).$row.':'.$maxColumn.$row, null, true, true, true));
                foreach ($datas as $key => $value) {
                    $datas[$key] = array('value' => trim($value), 'header' => trim($sheet->getCell($key.'1')->getValue()));
                }
            }

            // Import
            $mapping = $request->get('mapping', array());
            if ($mapping) {

                $datas = $sheet->toArray(null, true, true, true);
                for ($i = $row; $i <= $maxRow; $i++) {

                    $obj = $this->get('datatheke.manager.item')->create($collection);
                    foreach ($mapping as $col => $field) {
                        if ($field) {
                            $field = '_'.$field;
                            $obj->$field = trim($datas[$i][$col]);
                        }
                    }
                    $dm->persist($obj);
                    $imported++;
                }
                $dm->flush();

                $this->get('session')->getFlashBag()->add('crud_success', $imported.' ligne(s) intégrée(s)');
                @unlink($importPath.'/'.$fileName);

                return $this->redirect($this->generateUrl('collection', array('id' => $collection->getId())));
            }
        }

        return array(
                'collection' => $collection,
                'datas' => $datas,
                'fileId' => $fileId,
                'ignoreRows' => $ignoreRows,
                'ignoreColumns' => $ignoreColumns
        );
    }

    /**
     * @Route("/libraries/{id}/import", name="collection_import")
     * @Route("/library/{id}/import")
     *
     * @ParamConverter("library", options={"right"="LIBRARY_ADMIN", "param"="id"})
     *
     * @Template()
     */
    public function importCollectionAction(Request $request, Library $library)
    {
        $importPath = $this->container->getParameter('import_path');
        $dm         = $this->get('doctrine.odm.mongodb.document_manager');

        $id = $library->getId();

        if ($request->getMethod() === 'POST') {
            if ($request->files->has('importFile')) {
                $file = $request->files->get('importFile');
                $fileId = uniqid();
                $fileName = $id.'_'.$this->getUser()->getId().'_'.$fileId;
                $file->move($importPath, $fileName);

                $objPHPExcel = \PHPExcel_IOFactory::load($importPath.'/'.$fileName);

                $sheet = $objPHPExcel->getActiveSheet();
                $limit     = $sheet->getHighestRowAndColumn();
                $maxRow    = $limit['row'];
                $maxColumn = $limit['column'];

                $ignoreRows    = (int) $request->get('ignoreRows', 1);
                $ignoreColumns = (int) $request->get('ignoreColumns', 0);
                $column    = $ignoreColumns + ord('A');
                $row       = $ignoreRows + 1;

                $collection = new Collection();
                $collection->setName($request->get('collectionName', $this->get('translator')->trans('New collection')));
                $collection->setLibrary($library);
                $collection->setOwner($library->getOwner());

                for ($j = $column; $j <= ord($maxColumn); $j++) {
                    $field = new Field();
                    $field->setType('string');
                    $label = trim($sheet->getCell(chr($j).'1')->getValue());
                    if (!$label) {
                        $label = 'Nouveau champ';
                    }
                    $field->setLabel($label);

                    $collection->addAnotherField($field);
                    $dm->persist($field);
                }

                $dm->persist($collection);
                $dm->flush();

                $fields = $collection->getFields();

                $imported  = 0;
                $datas = $sheet->toArray(null, true, true, true);
                for ($i = $row; $i <= $maxRow; $i++) {

                    $obj = $this->get('datatheke.manager.item')->create($collection);
                    $z = 0;
                    for ($j = $column; $j <= ord($maxColumn); $j++) {
                        $field = $fields[$z];
                        $field = '_'.$field->getId();

                        $z++;
                        $obj->$field = trim($datas[$i][chr($j)]);
//                         if ($field) {
//                             $field = '_'.$field;
//                             $obj->$field = $datas[$i][$col];
//                         }
                    }
                    $dm->persist($obj);
                    $imported++;
                }
                $dm->flush();

                $this->get('session')->getFlashBag()->add('crud_success', $imported.' ligne(s) intégrée(s)');
                @unlink($importPath.'/'.$fileName);

                return $this->redirect($this->generateUrl('collection', array('id' => $collection->getId())));
            }
        }

        return array('library' => $library, 'ignoreRows' => 1, 'ignoreColumns' => 0, 'datas' => array());
    }
}
