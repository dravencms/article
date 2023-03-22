<?php declare(strict_types = 1);

/**
 * Copyright (C) 2023 Adam Schubert <adam.schubert@sg1-game.net>.
 */


namespace Dravencms\AdminModule\Components\Article\ArticlePictureGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Repository\ArticlePictureRepository;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Utils\Html;
use Salamek\Files\ImagePipe;
use Nette\Security\User;

class ArticlePictureGrid extends BaseControl
{
    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var ArticlePictureRepository */
    private $articlePictureRepository;

    /** @var EntityManager */
    private $entityManager;
    
    /** @var User */
    private $user;

    /** @var Article */
    private $article;

    /** @var ImagePipe */
    private $imagePipe;

    /** @var array */
    public $onDelete = [];

    /**
     * PictureGrid constructor.
     * @param Article $article
     * @param ArticlePictureRepository $articlePictureRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     * @param ImagePipe $imagePipe
     */
    public function __construct(
        Article $article,
        ArticlePictureRepository $articlePictureRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        User $user,
        ImagePipe $imagePipe
    )
    {
        $this->user = $user;
        $this->baseGridFactory = $baseGridFactory;
        $this->articlePictureRepository = $articlePictureRepository;
        $this->entityManager = $entityManager;
        $this->article = $article;
        $this->imagePipe = $imagePipe;
    }

    /**
     * @param $name
     * @return Grid
     */
    protected function createComponentGrid(string $name): Grid
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);
        $grid->setDataSource($this->articlePictureRepository->getPictureItemsQueryBuilder($this->article));

        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('name', 'article.preview')
            ->setAlign('center')
            ->setRenderer(function ($row) use($grid){
                /** @var Picture $row */
                if ($haveImage = $row->getStructureFileLink()->getStructureFile()) {
                    $img = Html::el('img');
                    $img->src = $this->imagePipe->request($haveImage->getFile(), '200x');
                } else {
                    $img = '';
                }
                if ($row->isPrimary()) {
                    $el = Html::el('span', $grid->getTranslator()->translate('article.primaryPicture'));
                    $el->class = 'label label-info';
                } else {
                    $el = '';
                }

                $container = Html::el('div');
                $container->addHtml($el);
                $container->addHtml('<br>');
                $container->addHtml($img);
                return $container;
            });

        $grid->addColumnNumber('positionShow', 'article.position', 'position')
        ->setAlign('center')
        ->setFilterRange();


        $grid->addColumnBoolean('isActive', 'article.isActive');
        $grid->addColumnBoolean('isPrimary', 'article.isPrimary');


        if ($this->user->isAllowed('article', 'edit')) {
            
            $grid->addColumnPosition('position', 'article.position');

            $grid->addAction('edit', '', 'edit', ['articleId' => 'article.id', 'id'])
                ->setIcon('pencil')
                ->setTitle('article.edit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->user->isAllowed('article', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('article.delete')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirmation(new StringConfirmation('article.doYouReallyWantToDeleteRowIdentifier', 'position'));
            
            $grid->addGroupAction('article.delete')->onSelect[] = [$this, 'gridGroupActionDelete'];
        }

        $grid->addExportCsvFiltered('article.csvExportFiltered', 'article_filtered.csv')
            ->setTitle('article.csvExportFiltered');

        $grid->addExportCsv('article.csvExport', 'article_all.csv')
            ->setTitle('article.csvExport');

        return $grid;
    }

    /**
     * @param array $ids
     */
    public function gridGroupActionDelete(array $ids): void
    {
        $this->handleDelete($ids);
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id): void
    {
        $articlePictures = $this->articlePictureRepository->getById($id);
        foreach ($articlePictures AS $articlePicture)
        {
            $structureFileLink = $articlePicture->getStructureFileLink();
            if ($structureFileLink) {
                $structureFileLink->setIsUsed(false);
                $structureFileLink->setIsAutoclean(true);
                $this->entityManager->persist($structureFileLink);
            }
            $this->entityManager->remove($picture);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    /**
     * @param $id
     */
    public function handleUp(int $id): void
    {
        $articlePictureItem = $this->articlePictureRepository->getOneById($id);
        $articlePictureItem->setPosition($articlePictureItem->getPosition() - 1);
        $this->entityManager->persist($articlePictureItem);
        $this->entityManager->flush();

    }

    /**
     * @param $id
     */
    public function handleDown(int $id): void
    {
        $articlePictureItem = $this->articlePictureRepository->getOneById($id);
        $articlePictureItem->setPosition($articlePictureItem->getPosition() + 1);
        $this->entityManager->persist($articlePictureItem);
        $this->entityManager->flush();
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ArticlePictureGrid.latte');
        $template->render();
    }
}