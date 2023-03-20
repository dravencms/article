<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Article\Repository;

use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Entities\ArticleTranslation;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Database\EntityManager;
use Nette;
use Dravencms\Model\Locale\Entities\ILocale;

class ArticleTranslationRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|ArticleTranslation */
    private $articleTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->articleTranslationRepository = $entityManager->getRepository(ArticleTranslation::class);
    }

    /**
     * @param $name
     * @param ILocale $locale
     * @param Group $group
     * @param Article|null $articleIgnore
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree(string $name, ILocale $locale, Group $group, Article $articleIgnore = null): bool
    {
        $qb = $this->articleTranslationRepository->createQueryBuilder('at')
            ->select('at')
            ->join('at.article', 'a')
            ->where('at.name = :name')
            ->andWhere('a.group = :group')
            ->setParameters([
                'name' => $name,
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
     * @param null $query
     * @param array $tags
     * @param bool $isActive
     * @param null $limit
     * @param null $offset
     * @param Article|null $ignoreArticle
     * @return array
     */
    public function search(ILocale $locale, Group $group = null, string $query = null, array $tags = [], bool $isActive = true, int $limit = null, int $offset = null, Article $ignoreArticle = null)
    {
        $qb = $this->articleTranslationRepository->createQueryBuilder('atr')
            ->select('atr')
            ->join('atr.article', 'a')
            ->where('a.isActive = :isActive')
            ->andWhere('atr.locale = :locale')
            ->setParameters(
                [
                    'isActive' => $isActive,
                    'locale' => $locale
                ]
            );

        if ($group)
        {
            $qb->andWhere('a.group = :group')
                ->setParameter('group', $group);

            if ($group->getSortBy() == Group::SORT_BY_POSITION)
            {
                $qb->orderBy('a.position', 'ASC');
            }
            elseif ($group->getSortBy() == Group::SORT_BY_CREATED_AT)
            {
                $qb->orderBy('a.createdAt', 'DESC');
            }
        }

        if ($query)
        {
            $qb->andWhere('at.name LIKE :query')
                ->orWhere('at.text LIKE :query')
                ->orWhere('at.perex LIKE :query')
                ->setParameter('query', '%'.$query.'%');
        }

        if (!empty($tags))
        {
            $qb->join('a.tags', 'at')
                ->andWhere('at IN (:tags)')
                ->setParameter('tags', $tags);
        }

        if ($limit)
        {
            $qb->setMaxResults($limit);
        }

        if ($offset)
        {
            $qb->setFirstResult($offset);
        }

        if ($ignoreArticle)
        {
            $qb->andWhere('a != :ignoreArticle')
                ->setParameter('ignoreArticle', $ignoreArticle);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Article $article
     * @param ILocale $locale
     * @return null|ArticleTranslation
     */
    public function getTranslation(Article $article, ILocale $locale): ?ArticleTranslation
    {
        return $this->articleTranslationRepository->findOneBy(['article' => $article, 'locale' => $locale]);
    }
}