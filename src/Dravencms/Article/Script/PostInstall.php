<?php

namespace Dravencms\Article\Script;

use Dravencms\Model\Admin\Entities\Menu;
use Dravencms\Model\Admin\Repository\MenuRepository;
use Dravencms\Model\User\Entities\AclOperation;
use Dravencms\Model\User\Entities\AclResource;
use Dravencms\Model\User\Repository\AclOperationRepository;
use Dravencms\Model\User\Repository\AclResourceRepository;
use Dravencms\Packager\IPackage;
use Dravencms\Packager\IScript;
use Kdyby\Doctrine\EntityManager;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class PostInstall implements IScript
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var AclResourceRepository */
    private $aclResourceRepository;

    /** @var AclOperationRepository */
    private $aclOperationRepository;

    public function __construct(MenuRepository $menuRepository, EntityManager $entityManager, AclResourceRepository $aclResourceRepository, AclOperationRepository $aclOperationRepository)
    {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
        $this->aclResourceRepository = $aclResourceRepository;
        $this->aclOperationRepository = $aclOperationRepository;
    }

    public function run(IPackage $package)
    {
        if (!$aclResource = $this->aclResourceRepository->getOneByName('article'))
        {
            $aclResource = new AclResource('article', 'Article');
            $this->entityManager->persist($aclResource);
        }

        if (!$aclOperationEdit = $this->aclOperationRepository->getOneByName('edit'))
        {
            $aclOperationEdit = new AclOperation($aclResource, 'edit', 'Allows editation of article');
            $this->entityManager->persist($aclOperationEdit);
        }
        
        if (!$aclOperationDelete = $this->aclOperationRepository->getOneByName('delete'))
        {
            $aclOperationDelete = new AclOperation($aclResource, 'delete', 'Allows deletion of article');
            $this->entityManager->persist($aclOperationDelete);
        }

        if (!$this->menuRepository->getOneByPresenter(':Admin:Article:Group'))
        {
            $adminMenu = new Menu('Article', ':Admin:Article:Group', 'fa-bullhorn', $aclOperationEdit);

            $foundRoot = $this->menuRepository->getOneByName('Site items');

            if ($foundRoot)
            {
                $this->menuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $foundRoot);
            }
            else
            {
                $this->entityManager->persist($adminMenu);
            }
        }
        
        $this->entityManager->flush();

    }
}