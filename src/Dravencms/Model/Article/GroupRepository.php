<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace App\Model\Article\Repository;

use App\Model\Article\Entities\Group;
use App\Model\BaseRepository;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\IComponentAction;
use Salamek\Cms\Models\ILocale;

class GroupRepository extends BaseRepository implements ICmsComponentRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $groupRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->groupRepository = $entityManager->getRepository(Group::class);
    }

    /**
     * @param $id
     * @return mixed|null|Group
     */
    public function getOneById($id)
    {
        return $this->groupRepository->find($id);
    }

    /**
     * @param $id
     * @return Group[]
     */
    public function getById($id)
    {
        return $this->groupRepository->findBy(['id' => $id]);
    }
    
    /**
     * @param $name
     * @param Group|null $groupIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, Group $groupIgnore = null)
    {
        $qb = $this->groupRepository->createQueryBuilder('g')
            ->select('g')
            ->where('g.name = :name')
            ->setParameters([
                'name' => $name
            ]);

        if ($groupIgnore)
        {
            $qb->andWhere('g != :groupIgnore')
                ->setParameter('groupIgnore', $groupIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getGroupQueryBuilder()
    {
        $qb = $this->groupRepository->createQueryBuilder('g')
            ->select('g');
        return $qb;
    }

    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction)
        {
            case 'Detail':
            case 'SimpleDetail':
                $return = [];
                /** @var Group $group */
                foreach ($this->groupRepository->findAll() AS $group) {
                    $return[] = new CmsActionOption($group->getName(), ['id' => $group->getId()]);
                }
                break;

            default:
                return false;
                break;
        }
        

        return $return;
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @param ILocale $locale
     * @return null|CmsActionOption
     */
    public function getActionOption($componentAction, array $parameters, ILocale $locale)
    {
        $found = $this->findTranslatedOneBy($this->groupRepository, $locale, $parameters);

        if ($found)
        {
            return new CmsActionOption($found->getName(), $parameters);
        }

        return null;
    }
}