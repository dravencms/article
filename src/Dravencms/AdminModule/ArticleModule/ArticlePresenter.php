<?php declare(strict_types = 1);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\ArticleModule;

use Dravencms\AdminModule\Components\Article\ArticleForm\ArticleFormFactory;
use Dravencms\AdminModule\Components\Article\ArticleForm\ArticleForm;
use Dravencms\AdminModule\Components\Article\ArticleGrid\ArticleGridFactory;
use Dravencms\AdminModule\Components\Article\ArticleGrid\ArticleGrid;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Flash;
use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Model\Article\Repository\GroupRepository;
use Dravencms\Model\Tag\Repository\TagRepository;

/**
 * Description of ArticlePresenter
 *
 * @author Adam Schubert
 */
class ArticlePresenter extends SecuredPresenter
{

    /** @var ArticleRepository @inject */
    public $articleRepository;

    /** @var GroupRepository @inject */
    public $groupRepository;

    /** @var TagRepository @inject */
    public $tagRepository;

    /** @var ArticleGridFactory @inject */
    public $articleGridFactory;

    /** @var ArticleFormFactory @inject */
    public $articleFormFactory;

    /** @var Group */
    private $group;

    /** @var Article|null */
    private $article = null;

    /**
     * @param integer $groupId
     * @isAllowed(article,edit)
     */
    public function actionDefault(int $groupId): void
    {
        $this->group = $this->groupRepository->getOneById($groupId);
        $this->template->group = $this->group;
        $this->template->h1 = 'Articles in group '.$this->group->getIdentifier();
    }

    /**
     * @isAllowed(article,edit)
     * @param $groupId
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit(int $groupId, int $id = null): void
    {
        $this->group = $this->groupRepository->getOneById($groupId);
        if ($id) {
            $article = $this->articleRepository->getOneById($id);

            if (!$article) {
                $this->error();
            }

            $this->article = $article;

            $this->template->h1 = sprintf('Edit article „%s“', $article->getIdentifier());
        } else {
            $this->template->h1 = 'New article in group '.$this->group->getIdentifier();
        }
    }

    /**
     * @return ArticleForm
     */
    protected function createComponentFormArticle(): ArticleForm
    {
        $control = $this->articleFormFactory->create($this->group, $this->article);
        $control->onSuccess[] = function(){
            $this->flashMessage('Article has been successfully saved', Flash::SUCCESS);
            $this->redirect('Article:', ['groupId' => $this->group->getId()]);
        };
        return $control;
    }

    /**
     * @return ArticleGrid
     */
    public function createComponentGridArticle(): ArticleGrid
    {
        $control = $this->articleGridFactory->create($this->group);
        $control->onDelete[] = function()
        {
            $this->flashMessage('Article has been successfully deleted', Flash::SUCCESS);
            $this->redirect('Article:', ['groupId' => $this->group->getId()]);
        };
        return $control;
    }
}
