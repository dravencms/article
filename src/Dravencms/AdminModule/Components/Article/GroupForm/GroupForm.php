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

namespace Dravencms\AdminModule\Components\Article;

use Dravencms\Components\BaseFormFactory;
use App\Model\Article\Entities\Group;
use App\Model\Article\Repository\GroupRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Description of GroupForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class GroupForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var GroupRepository */
    private $groupRepository;

    /** @var Group|null */
    private $group = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * ArticleForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param GroupRepository $groupRepository
     * @param Group|null $group
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        GroupRepository $groupRepository,
        Group $group = null
    ) {
        parent::__construct();

        $this->group = $group;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->groupRepository = $groupRepository;


        if ($this->group) {

            $defaults = [
                'name' => $this->group->getName(),
                'isShowName' => $this->group->isShowName(),
                'sortBy' => $this->group->getSortBy()
            ];

        }
        else{
            $defaults = [
                'isShowName' => false,
                'sortBy' => Group::SORT_BY_CREATED_AT
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        $form->addText('name')
            ->setRequired('Please enter article name.')
            ->addRule(Form::MAX_LENGTH, 'Article name is too long.', 255);

        $form->addSelect('sortBy', null, Group::$sortByList)
            ->setRequired('Please select sorting mode');

        $form->addCheckbox('isShowName');


        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();
        if (!$this->groupRepository->isNameFree($values->name, $this->group)) {
            $form->addError('Tento název je již zabrán.');
        }

        if (!$this->presenter->isAllowed('article', 'edit')) {
            $form->addError('Nemáte oprávění editovat article group.');
        }
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        if ($this->group) {
            $group = $this->group;
            $group->setName($values->name);
            $group->setIsShowName($values->isShowName);
            $group->setSortBy($values->sortBy);
        } else {
            $group = new Group($values->name, $values->isShowName, $values->sortBy);
        }

        $this->entityManager->persist($group);

        $this->entityManager->flush();

        $this->onSuccess();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/GroupForm.latte');
        $template->render();
    }
}