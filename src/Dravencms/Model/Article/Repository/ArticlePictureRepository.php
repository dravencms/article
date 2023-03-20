<?php declare(strict_types = 1);

namespace Dravencms\Model\Article\Repository;

use Doctrine\ORM\QueryBuilder;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Article\Entities\ArticlePicture;
use Dravencms\Model\Article\Entities\Article;

class ArticlePictureRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $articlePictureRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * CategoryRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->articlePictureRepository = $entityManager->getRepository(ArticlePicture::class);
    }

    /**
     * @return ArticlePicture[]
     */
    public function getAll()
    {
        return $this->articlePictureRepository->findAll();
    }

    /**
     * @param array $parameters
     * @return null|ArticlePicture
     */
    public function getOneByParameters(array $parameters): ?ArticlePicture
    {
        return $this->articlePictureRepository->findOneBy($parameters);
    }

    /**
     * @param $id
     * @return null|ArticlePicture
     */
    public function getOneById($id): ?ArticlePicture
    {
        return $this->articlePictureRepository->find($id);
    }

    /**
     * @param Article $article
     * @return QueryBuilder
     */
    public function getPictureItemsQueryBuilder(Article $article)
    {
        $qb = $this->articlePictureRepository->createQueryBuilder('ap')
            ->select('ap')
            ->where('ap.article = :article')
            ->setParameter('article', $article);

        return $qb;
    }

    /**
     * @param $id
     * @return ArticlePicture[]
     */
    public function getById($id)
    {
        return $this->articlePictureRepository->findBy(['id' => $id]);
    }

    /**
     * @param Article $article
     * @return null|ArticlePicture
     */
    public function getOneByPrimary(Article $article): ?ArticlePicture
    {
        return $this->articlePictureRepository->findOneBy(['article' => $article, 'isPrimary' => true]);
    }
}