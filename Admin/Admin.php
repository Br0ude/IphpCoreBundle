<?php

namespace Iphp\CoreBundle\Admin;

use Sonata\AdminBundle\Admin\Admin as BaseAdmin;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class Admin extends BaseAdmin
{
    public function prePersist($entity)
    {
        if (method_exists($entity, 'setUpdatedBy')) $entity->setUpdatedBy($this->getCurrentUser());
        if (method_exists($entity, 'setCreatedBy')) $entity->setCreatedBy($this->getCurrentUser());
        if (method_exists($entity, 'setCreatedAt')) $entity->setCreatedAt(new \DateTime);
        if (method_exists($entity, 'setUpdatedAt')) $entity->setUpdatedAt(new \DateTime);
    }


    public function preUpdate($entity)
    {
        if (method_exists($entity, 'setUpdatedBy')) $entity->setUpdatedBy($this->getCurrentUser());
        if (method_exists($entity, 'setUpdatedAt')) $entity->setUpdatedAt(new \DateTime);
    }

    protected function getCurrentUser()
    {
        return $this->getConfigurationPool()->getContainer()->get('security.context')->getToken()->getUser();
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     * @param $action
     * @param null|\Sonata\AdminBundle\Admin\Admin $childAdmin
     *
     * @return void
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }
        $admin = $this->isChild() ? $this->getParent() : $this;

        if (method_exists($admin->getSubject(), 'getCreatedAt')) {
            $menu->addChild($this->trans('Created At') . ':');


            $createdAt = $admin->getSubject()->getCreatedAt();


            $menu->addChild($createdAt && $createdAt->format ('Y') != '-0001'?
                $createdAt->format('d.m.Y H:i:s')  : $this->trans('n/a'))
                ->setLabelAttributes(array('style' => 'font-weight: bold'));

        }

        if (method_exists($admin->getSubject(), 'getUpdatedAt')) {
            $menu->addChild($this->trans('Updated At') . ':');
            $menu->addChild($admin->getSubject()->getUpdatedAt() ?
                $admin->getSubject()->getUpdatedAt()->format('d.m.Y H:i:s') : $this->trans('n/a'))
                ->setLabelAttributes(array('style' => 'font-weight: bold'));
        }

        if (method_exists($admin->getSubject(), 'getUpdatedBy')) {
            $menu->addChild($this->trans('Updated By') . ':');
            $updatedBy = (string)$admin->getSubject()->getUpdatedBy();
            $menu->addChild((string)$updatedBy ? $updatedBy : $this->trans('n/a'))
                ->setLabelAttributes(array('style' => 'font-weight: bold'));
        }

        if (method_exists($admin->getSubject(), 'getCreatedBy')) {
            $menu->addChild($this->trans('Created By') . ':');
            $createdBy = (string)$admin->getSubject()->getCreatedBy();


            $menu->addChild(strlen($createdBy) > 1 ? $createdBy : ' '.$this->trans('n/a'))
                ->setLabelAttributes(array('style' => 'font-weight: bold'));
        }


        if (method_exists($admin->getSubject(), 'getSitePath'))
            $menu->addChild(
                $this->trans('View on site'), array(
                    'uri' => $this->getConfigurationPool()->getContainer()->get('iphp.core.entity.router')
                        ->entitySitePath($admin->getSubject()),
                    'linkAttributes' => array('target' => '_blank'))
            );


    }

}