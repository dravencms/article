<?php

/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\AdminModule\Components\Article\ArticleGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;

/**
 * Description of ArticleGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ArticleGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var ArticleRepository */
    private $articleRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var ILocale */
    private $currentLocale;

    /** @var Group */
    private $group;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * ArticleGrid constructor.
     * @param Group $group
     * @param ArticleRepository $articleRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     * @param CurrentLocaleResolver $currentLocaleResolver
     */
    public function __construct(
        Group $group,
        ArticleRepository $articleRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        parent::__construct();

        $this->group = $group;
        $this->baseGridFactory = $baseGridFactory;
        $this->articleRepository = $articleRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->entityManager = $entityManager;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->articleRepository->getArticleQueryBuilder($this->group));

        if ($this->group->getSortBy() == Group::SORT_BY_POSITION) {
            $grid->setDefaultSort(['position' => 'ASC']);
        }
        elseif ($this->group->getSortBy() == Group::SORT_BY_CREATED_AT)
        {
            $grid->setDefaultSort(['createdAt' => 'DESC']);
        }

        $grid->addColumnText('identifier', 'Identifier')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnBoolean('isActive', 'Active');
        $grid->addColumnBoolean('isShowName', 'Show name');

        if ($this->group->getSortBy() == Group::SORT_BY_POSITION)
        {
            $grid->addColumnPosition('position', 'Position', 'up!', 'down!');
        }
        elseif ($this->group->getSortBy() == Group::SORT_BY_CREATED_AT)
        {
            $grid->addColumnDateTime('updatedAt', 'Last edit')
                ->setFormat($this->currentLocale->getDateTimeFormat())
                ->setAlign('center')
                ->setSortable()
                ->setFilterDate();
        }

        if ($this->presenter->isAllowed('article', 'edit')) {

            $grid->addAction('edit', 'Upravit', 'edit', ['groupId' => 'group.id', 'id'])
                ->setIcon('pencil')
                ->setTitle('Upravit')
                ->setClass('btn btn-xs btn-primary');

            /*
            $grid->addActionHref('edit', 'Upravit')
                ->setCustomHref(function($row){
                    return $this->presenter->link('edit', ['id' => $row->getId(), 'groupId' => $this->group->getId()]);
                })
                ->setIcon('pencil');
            */
        }

        if ($this->presenter->isAllowed('article', 'delete')) {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete row %s?', 'identifier');
            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'handleDelete'];
        }
        $grid->addExportCsvFiltered('Csv export (filtered)', 'articles_filtered.csv')
            ->setTitle('Csv export (filtered)');
        $grid->addExportCsv('Csv export', 'articlesall.csv')
            ->setTitle('Csv export');

        return $grid;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id)
    {
        $articles = $this->articleRepository->getById($id);
        foreach ($articles AS $article)
        {
            $this->entityManager->remove($article);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    /**
     * @param $id
     */
    public function handleUp($id)
    {
        $articleItem = $this->articleRepository->getOneById($id);
        $articleItem->setPosition($articleItem->getPosition() + 1);
        $this->entityManager->persist($articleItem);
        $this->entityManager->flush();

    }

    /**
     * @param $id
     */
    public function handleDown($id)
    {
        $articleItem = $this->articleRepository->getOneById($id);
        $articleItem->setPosition($articleItem->getPosition() - 1);
        $this->entityManager->persist($articleItem);
        $this->entityManager->flush();
    }
    
    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ArticleGrid.latte');
        $template->render();
    }
}
