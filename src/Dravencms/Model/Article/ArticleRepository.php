<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace App\Model\Article\Repository;

use App\Model\Article\Entities\Article;
use App\Model\Article\Entities\Group;
use App\Model\BaseRepository;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Gedmo\Translatable\TranslatableListener;
use Salamek\Cms\Models\ILocale;

class ArticleRepository extends BaseRepository implements ICmsComponentRepository
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
     * @param $name
     * @param ILocale $locale
     * @param Group $group
     * @param Article|null $articleIgnore
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, Group $group, Article $articleIgnore = null)
    {
        $qb = $this->articleRepository->createQueryBuilder('a')
            ->select('a')
            ->where('a.name = :name')
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

        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale->getLanguageCode());

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
     * @param Group $group
     * @param null $query
     * @param array $tags
     * @param bool $isActive
     * @param null $limit
     * @param null $offset
     * @param Article|null $ignoreArticle
     * @return array
     */
    public function search(Group $group = null, $query = null, array $tags = [], $isActive = true, $limit = null, $offset = null, Article $ignoreArticle = null)
    {
        $qb = $this->articleRepository->createQueryBuilder('a')
            ->select('a')
            ->where('a.isActive = :isActive')
            ->setParameters(
                [
                    'isActive' => $isActive,
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
                ->orWhere('a.text LIKE :query')
                ->orWhere('a.name LIKE :query')
                ->orWhere('a.perex LIKE :query')
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
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction)
        {
            case 'Detail':
            case 'OverviewDetail':
                $return = [];
                /** @var Article $article */
                foreach ($this->articleRepository->findBy(['isActive' => true]) AS $article) {
                    $return[] = new CmsActionOption($article->getName(), ['id' => $article->getId()]);
                }
                break;

            case 'Overview':
            case 'SimpleOverview':
            case 'Navigation':
                return null;
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
        /** @var Article $found */
        $found = $this->findTranslatedOneBy($this->articleRepository, $locale, $parameters + ['isActive' => true]);
        
        if ($found)
        {
            return new CmsActionOption(($found->getLead() ? $found->getLead() . ' ' : '') . $found->getName(), $parameters);
        }

        return null;
    }
}