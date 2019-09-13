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

namespace Dravencms\AdminModule\Components\Article\GroupGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\Article\Repository\GroupRepository;
use Kdyby\Doctrine\EntityManager;

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
    public function __construct(GroupRepository $groupRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager)
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->groupRepository = $groupRepository;
        $this->entityManager = $entityManager;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->groupRepository->getGroupQueryBuilder());

        $grid->addColumnText('identifier', 'Identifier')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('sortBy', 'Sorted by')
            ->setRenderer(function($row){
                return Group::$sortByList[$row->getSortBy()];
            })
            ->setSortable()
            ->setFilterSelect(Group::$sortByList);

        $grid->addColumnBoolean('isShowName', 'Show name');

        if ($this->presenter->isAllowed('article', 'edit')) {

/*
            $grid->addActionHref('articles', 'Articles')
                ->setCustomHref(function($row){
                    return $this->presenter->link('Article:', ['groupId' => $row->getId()]);
                })
                ->setIcon('bars');*/

            $grid->addAction('articles', 'Articles', 'Article:default', ['groupId' => 'id'])
                ->setIcon('bars')
                ->setTitle('Pictures')
                ->setClass('btn btn-xs btn-default');

            $grid->addAction('edit', '')
                ->setIcon('pencil')
                ->setTitle('Upravit')
                ->setClass('btn btn-xs btn-primary');

        }

        if ($this->presenter->isAllowed('article', 'delete')) {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete row %s?', 'identifier');
            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'handleDelete'];
        }
        $grid->addExportCsvFiltered('Csv export (filtered)', 'article_group_filtered.csv')
            ->setTitle('Csv export (filtered)');
        $grid->addExportCsv('Csv export', 'article_group_all.csv')
            ->setTitle('Csv export');


        return $grid;
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id)
    {
        $groups = $this->groupRepository->getById($id);
        foreach ($groups AS $group)
        {
            $this->entityManager->remove($group);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/GroupGrid.latte');
        $template->render();
    }
}
