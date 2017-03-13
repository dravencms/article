<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Article\Repository;

use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Entities\Group;
use Kdyby\Doctrine\EntityManager;
use Nette;

class ArticleRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $articleRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->articleRepository = $entityManager->getRepository(Article::class);
    }

    /**
     * @param $id
     * @return mixed|null|Article
     */
    public function getOneById($id)
    {
        return $this->articleRepository->find($id);
    }

    /**
     * @param $id
     * @return Article[]
     */
    public function getById($id)
    {
        return $this->articleRepository->findBy(['id' => $id]);
    }

    /**
     * @return Article[]
     */
    public function getActive()
    {
        return $this->articleRepository->findBy(['isActive' => true]);
    }

    /**
     * @param $identifier
     * @param Group $group
     * @param Article|null $articleIgnore
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree($identifier, Group $group, Article $articleIgnore = null)
    {
        $qb = $this->articleRepository->createQueryBuilder('a')
            ->select('a')
            ->where('a.identifier = :identifier')
            ->andWhere('a.group = :group')
            ->setParameters([
                'identifier' => $identifier,
                'group' => $group
            ]);

        if ($articleIgnore)
        {
            $qb->andWhere('a != :articleIgnore')
                ->setParameter('articleIgnore', $articleIgnore);
        }

        $query = $qb->getQuery();

        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param Group $group
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getArticleQueryBuilder(Group $group)
    {
        $qb = $this->articleRepository->createQueryBuilder('a')
            ->select('a')
            ->where('a.group = :group')
            ->setParameter('group', $group);
        return $qb;
    }

    /**
     * @param integer $id
     * @param bool $isActive
     * @return mixed|null|Article
     */
    public function getOneByIdAndActive($id, $isActive = true)
    {
        return $this->articleRepository->findOneBy(['id' => $id, 'isActive' => $isActive]);
    }

    /**
     * @param bool $isActive
     * @return Article[]
     * @deprecated do filtering in Repository
     */
    public function getAllByActive($isActive = true)
    {
        return $this->articleRepository->findBy(['isActive' => $isActive]);
    }

    /**
     * @param bool $isActive
     * @param array $parameters
     * @return Article
     * @deprecated
     */
    public function getOneByActiveAndParameters($isActive = true, array $parameters = [])
    {
        $parameters['isActive'] = $isActive;
        return $this->articleRepository->findOneBy($parameters);
    }

    /**
     * @param array $parameters
     * @return Article
     */
    public function getOneByParameters(array $parameters)
    {
        return $this->articleRepository->findOneBy($parameters);
    }
}