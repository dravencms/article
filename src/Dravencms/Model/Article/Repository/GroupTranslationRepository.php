<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Article\Repository;

use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\Article\Entities\GroupTranslation;
use Dravencms\Model\Locale\Entities\ILocale;
use Kdyby\Doctrine\EntityManager;
use Nette;

class GroupTranslationRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $groupTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->groupTranslationRepository = $entityManager->getRepository(GroupTranslation::class);
    }

    /**
     * @param $name
     * @param ILocale $locale
     * @param Group|null $groupIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, Group $groupIgnore = null)
    {
        $qb = $this->groupTranslationRepository->createQueryBuilder('gt')
            ->select('gt')
            ->join('gt.group', 'g')
            ->where('gt.name = :name')
            ->andWhere('gt.locale = :locale')
            ->setParameters([
                'name' => $name,
                'locale' => $locale
            ]);

        if ($groupIgnore)
        {
            $qb->andWhere('g != :groupIgnore')
                ->setParameter('groupIgnore', $groupIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param Group $group
     * @param ILocale $locale
     * @return null|GroupTranslation
     */
    public function getTranslation(Group $group, ILocale $locale)
    {
        return $this->groupTranslationRepository->findOneBy(['group' => $group, 'locale' => $locale]);
    }
}