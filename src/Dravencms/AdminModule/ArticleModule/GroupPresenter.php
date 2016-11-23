<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\ArticleModule;

use Dravencms\AdminModule\Components\Article\GroupFormFactory;
use Dravencms\AdminModule\Components\Article\GroupGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use App\Model\Article\Entities\Article;
use App\Model\Article\Repository\GroupRepository;

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
    public function renderDefault()
    {
        $this->template->h1 = 'Article groups';
    }

    /**
     * @isAllowed(article,edit)
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit($id)
    {
        if ($id) {
            $group = $this->groupRepository->getOneById($id);

            if (!$group) {
                $this->error();
            }

            $this->group = $group;

            $this->template->h1 = sprintf('Edit article group „%s“', $group->getName());
        } else {
            $this->template->h1 = 'New article group';
        }
    }

    /**
     * @return \AdminModule\Components\Article\GroupForm
     */
    protected function createComponentFormGroup()
    {
        $control = $this->groupFormFactory->create($this->group);
        $control->onSuccess[] = function(){
            $this->flashMessage('Article group has been successfully saved', 'alert-success');
            $this->redirect('Group:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Article\GroupGrid
     */
    public function createComponentGridGroup()
    {
        $control = $this->groupGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Article group has been successfully deleted', 'alert-success');
            $this->redirect('Group:');
        };
        return $control;
    }
}
