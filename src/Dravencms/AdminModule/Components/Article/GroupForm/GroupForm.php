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

namespace Dravencms\AdminModule\Components\Article\GroupForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\Article\Entities\GroupTranslation;
use Dravencms\Model\Article\Repository\GroupRepository;
use Dravencms\Model\Article\Repository\GroupTranslationRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;
use Nette\Security\User;
use Nette\Application\UI\Form;

/**
 * Description of GroupForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class GroupForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var GroupRepository */
    private $groupRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var GroupTranslationRepository */
    private $groupTranslationRepository;

    /** @var User */
    private $user;

    /** @var Group|null */
    private $group = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * GroupForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param GroupRepository $groupRepository
     * @param GroupTranslationRepository $groupTranslationRepository
     * @param LocaleRepository $localeRepository
     * @param Group|null $group
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        GroupRepository $groupRepository,
        GroupTranslationRepository $groupTranslationRepository,
        LocaleRepository $localeRepository,
        User $user,
        Group $group = null
    ) {
        $this->group = $group;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->groupRepository = $groupRepository;
        $this->groupTranslationRepository = $groupTranslationRepository;
        $this->localeRepository = $localeRepository;
        $this->user = $user;


        if ($this->group) {

            $defaults = [
                'identifier' => $this->group->getIdentifier(),
                'isShowName' => $this->group->isShowName(),
                'sortBy' => $this->group->getSortBy()
            ];

            foreach ($this->group->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
            }
        }
        else{
            $defaults = [
                'isShowName' => false,
                'sortBy' => Group::SORT_BY_CREATED_AT
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    protected function createComponentForm(): Form
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());
            $container->addText('name')
                ->setRequired('Please enter group name.')
                ->addRule(Form::MAX_LENGTH, 'Group name is too long.', 255);
        }

        $form->addText('identifier')
            ->setRequired('Please fill in an identifier');

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
    public function editFormValidate(Form $form): void
    {
        $values = $form->getValues();
        if (!$this->groupRepository->isIdentifierFree($values->identifier, $this->group)) {
            $form->addError('Tento identifier je již zabrán.');
        }

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->groupTranslationRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->group)) {
                $form->addError('Tento název je již zabrán.');
            }
        }

        if (!$this->user->isAllowed('article', 'edit')) {
            $form->addError('Nemáte oprávění editovat article group.');
        }
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form): void
    {
        $values = $form->getValues();

        if ($this->group) {
            $group = $this->group;
            $group->setIdentifier($values->identifier);
            $group->setIsShowName($values->isShowName);
            $group->setSortBy($values->sortBy);
        } else {
            $group = new Group($values->identifier, $values->isShowName, $values->sortBy);
        }

        $this->entityManager->persist($group);

        $this->entityManager->flush();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($groupTranslation = $this->groupTranslationRepository->getTranslation($group, $activeLocale))
            {
                $groupTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
            }
            else
            {
                $groupTranslation = new GroupTranslation(
                    $group,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->name
                );
            }
            $this->entityManager->persist($groupTranslation);
        }
        $this->entityManager->flush();

        $this->onSuccess();
    }

    public function render(): void
    {
        $template = $this->template;
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/GroupForm.latte');
        $template->render();
    }
}