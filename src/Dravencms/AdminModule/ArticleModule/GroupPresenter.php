<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\ArticleModule;

use Dravencms\AdminModule\Components\Article\GroupForm\GroupFormFactory;
use Dravencms\AdminModule\Components\Article\GroupGrid\GroupGridFactory;
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

            $this->template->h1 = sprintf('Edit article group „%s“', $group->getIdentifier());
        } else {
            $this->template->h1 = 'New article group';
        }
    }

    /**
     * @return \Dravencms\AdminModule\Components\Article\GroupForm\GroupForm
     */
    protected function createComponentFormGroup()
    {
        $control = $this->groupFormFactory->create($this->group);
        $control->onSuccess[] = function(){
            $this->flashMessage('Article group has been successfully saved', Flash::SUCCESS);
            $this->redirect('Group:');
        };
        return $control;
    }

    /**
     * @return \Dravencms\AdminModule\Components\Article\GroupGrid\GroupGrid
     */
    public function createComponentGridGroup()
    {
        $control = $this->groupGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Article group has been successfully deleted', Flash::SUCCESS);
            $this->redirect('Group:');
        };
        return $control;
    }
}
