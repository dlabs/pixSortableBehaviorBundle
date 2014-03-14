<?php

namespace Pix\SortableBehaviorBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SortableAdminController extends CRUDController
{
    /**
     * Move element
     *
     * @param integer $id
     * @param string $position
     */
    public function moveAction($id, $position)
    {
        $id     = $this->get('request')->get($this->admin->getIdParameter());
        $page   = $this->get('request')->get('page');
        $filters= $this->admin->getFilterParameters();
        $filters['_page'] = $page;
        $object = $this->admin->getObject($id);

        $position_service = $this->get('pix_sortable_behavior.position');
        $position_service->setPositions(get_class($object), $id, $position, $this->admin);

        if ($this->isXmlHttpRequest()) {
            return $this->renderJson(array(
                'result' => 'ok',
                'objectId' => $this->admin->getNormalizedIdentifier($object)
            ));
        }
        $this->get('session')->getFlashBag()->set('sonata_flash_info', 'Position changed');

        return new RedirectResponse($this->admin->generateUrl('list', ['filter' => $filters]));
    }

}