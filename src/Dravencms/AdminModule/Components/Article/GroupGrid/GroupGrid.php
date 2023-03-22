<?php declare(strict_types = 1);

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

namespace Dravencms\AdminModule\Components\Article\GroupGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\Article\Repository\GroupRepository;
use Dravencms\Database\EntityManager;
use Nette\Security\User;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

/**
 * Description of GroupGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class GroupGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var GroupRepository */
    private $groupRepository;

     /** @var User */
     private $user;

    /** @var EntityManager */
    private $entityManager;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * GroupGrid constructor.
     * @param GroupRepository $groupRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     */
    public function __construct(
        GroupRepository $groupRepository, 
        BaseGridFactory $baseGridFactory, 
        EntityManager $entityManager,
        User $user
    )
    {
        $this->baseGridFactory = $baseGridFactory;
        $this->groupRepository = $groupRepository;
        $this->entityManager = $entityManager;
        $this->user = $user;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid(string $name): Grid
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->groupRepository->getGroupQueryBuilder());

        $grid->addColumnText('identifier', 'article.identifier')
            ->setSortable()
            ->setFilterText();


        $translatedSotringModes = [];
        foreach(Group::$sortByList AS $k => $v)
        {
            $translatedSotringModes[$k] = $grid->getTranslator()->translate($v);
        }

        $grid->addColumnText('sortBy', 'article.sortingMode')
            ->setRenderer(function($row) use ($grid){
                return $grid->getTranslator()->translate(Group::$sortByList[$row->getSortBy()]);
            })
            ->setSortable()
            ->setFilterSelect($translatedSotringModes);

        $grid->addColumnBoolean('isShowName', 'article.isShowName');

        if ($this->user->isAllowed('article', 'edit')) {

            $grid->addAction('articles', 'article.articles', 'Article:default', ['groupId' => 'id'])
                ->setIcon('bars')
                ->setTitle('article.articles')
                ->setClass('btn btn-xs btn-default');

            $grid->addAction('edit', '')
                ->setIcon('pencil')
                ->setTitle('article.edit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->user->isAllowed('article', 'delete')) {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('article.delete')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirmation(new StringConfirmation('article.doYouReallyWantToDeleteRowIdentifier', 'identifier'));
            $grid->addGroupAction('article.delete')->onSelect[] = [$this, 'handleDelete'];
        }
        $grid->addExportCsvFiltered('article.csvExportFiltered', 'article_group_filtered.csv')
            ->setTitle('article.csvExportFiltered');
        $grid->addExportCsv('article.csvExport', 'article_group_all.csv')
            ->setTitle('article.csvExport');


        return $grid;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id): void
    {
        $groups = $this->groupRepository->getById($id);
        foreach ($groups AS $group)
        {
            $this->entityManager->remove($group);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/GroupGrid.latte');
        $template->render();
    }
}
