<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Article\Repository;

use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Database\EntityManager;
use Nette;

class GroupRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|Group */
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
     * @return null|Group
     */
    public function getOneById(int $id): ?Group
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
     * @return Group[]
     */
    public function getAll()
    {
        return $this->groupRepository->findAll();
    }

    /**
     * @param $identifier
     * @param Group|null $groupIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree(string $identifier, Group $groupIgnore = null): bool
    {
        $qb = $this->groupRepository->createQueryBuilder('g')
            ->select('g')
            ->where('g.identifier = :identifier')
            ->setParameters([
                'identifier' => $identifier
            ]);

        if ($groupIgnore)
        {
            $qb->andWhere('g != :groupIgnore')
                ->setParameter('groupIgnore', $groupIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @return QueryBuilder
     */
    public function getGroupQueryBuilder()
    {
        $qb = $this->groupRepository->createQueryBuilder('g')
            ->select('g');
        return $qb;
    }

    /**
     * @param $identifier
     * @return Article|null
     */
    public function getOneByIdentifier(string $identifier): ?Article
    {
        return $this->groupRepository->findOneBy(['identifier' => $identifier]);
    }

    /**
     * @param array $parameters
     * @return null|Article
     */
    public function getOneByParameters(array $parameters): ?Article
    {
        return $this->groupRepository->findOneBy($parameters);
    }
}