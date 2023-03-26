<?php declare(strict_types = 1);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\ArticleModule;

use Dravencms\AdminModule\Components\Article\GroupForm\GroupFormFactory;
use Dravencms\AdminModule\Components\Article\GroupForm\GroupForm;
use Dravencms\AdminModule\Components\Article\GroupGrid\GroupGridFactory;
use Dravencms\AdminModule\Components\Article\GroupGrid\GroupGrid;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Flash;
use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Repository\GroupRepository;

/**
 * Description of GroupPresenter
 *
 * @author Adam Schubert
 */
class GroupPresenter extends SecuredPresenter
{
    /** @var GroupRepository @inject */
    public $groupRepository;

    /** @var GroupGridFactory @inject */
    public $groupGridFactory;

    /** @var GroupFormFactory @inject */
    public $groupFormFactory;

    /** @var Article|null */
    private $group = null;

    /**
     * @isAllowed(article,edit)
     */
    public function renderDefault(): void
    {
        $this->template->h1 = $this->translator->translate('article.articleGroups');
    }

    /**
     * @isAllowed(article,edit)
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit(int $id = null): void
    {
        if ($id) {
            $group = $this->groupRepository->getOneById($id);

            if (!$group) {
                $this->error();
            }

            $this->group = $group;

            $this->template->h1 = $this->translator->translate('article.editArticleGroupIdentifier', ['groupIdentifier' => $this->group->getIdentifier()]);
        } else {
            $this->template->h1 = $this->translator->translate('article.newArticleGroup');
        }
    }

    /**
     * @return GroupForm
     */
    protected function createComponentFormGroup(): GroupForm
    {
        $control = $this->groupFormFactory->create($this->group);
        $control->onSuccess[] = function(){
            $this->flashMessage($this->translator->translate('article.articleGroupHasBeenSucessfullySaved'), Flash::SUCCESS);
            $this->redirect('Group:');
        };
        return $control;
    }

    /**
     * @return GroupGrid
     */
    public function createComponentGridGroup(): GroupGrid
    {
        $control = $this->groupGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage($this->translator->translate('article.articleGroupHasBeenSucessfullyDeleted'), Flash::SUCCESS);
            $this->redirect('Group:');
        };
        return $control;
    }
}
