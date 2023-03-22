<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 27.2.17
 * Time: 5:32
 */

namespace Dravencms\AdminModule\ArticleModule;


use Dravencms\Flash;
use Dravencms\AdminModule\Components\Article\ArticlePictureForm\ArticlePictureFormFactory;
use Dravencms\AdminModule\Components\Article\ArticlePictureForm\ArticlePictureForm;
use Dravencms\AdminModule\Components\Article\ArticlePictureGrid\ArticlePictureGridFactory;
use Dravencms\AdminModule\Components\Article\ArticlePictureGrid\ArticlePictureGrid;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Article\Entities\ArticlePicture;
use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Repository\ArticlePictureRepository;
use Dravencms\Model\Article\Repository\ArticleRepository;

/**
 * Description of ArticlePicturePresenter
 *
 * @author Adam Schubert
 */
class ArticlePicturePresenter extends SecuredPresenter
{
    /** @var ArticleRepository @inject */
    public $articleRepository;

    /** @var ArticlePictureRepository @inject */
    public $articlePictureRepository;
    
    /** @var ArticlePictureGridFactory @inject */
    public $articlePictureGridFactory;

    /** @var ArticlePictureFormFactory @inject */
    public $articlePictureFormFactory;

    /** @var Article */
    private $article;

    /** @var ArticlePicture|null */
    private $articlePicture = null;

    /**
     * @param integer $articleId
     * @isAllowed(article,edit)
     */
    public function actionDefault(int $articleId): void
    {
        $article = $this->articleRepository->getOneById($articleId);
        if (!$article)
        {
            $this->error();
        }

        $this->article = $article;
        $this->template->article = $article;
        $this->template->h1 = $this->translator->translate('article.articlePictures');
    }

    /**
     * @isAllowed(article,edit)
     * @param integer $articleId
     * @param integer|null $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit(int $articleId, int $id = null): void
    {
        $article = $this->articleRepository->getOneById($articleId);
        if (!$article)
        {
            $this->error();
        }

        $this->article = $article;

        if ($id) {
            $articlePicture = $this->articlePictureRepository->getOneById($id);

            if (!$articlePicture) {
                $this->error();
            }

            $this->articlePicture = $articlePicture;

            $this->template->h1 = $this->translator->translate('article.editArticlePicture');
        } else {
            $this->template->h1 = $this->translator->translate('article.newArticlePicture');
        }
    }

    /**
     * @return ArticlePictureForm
     */
    protected function createComponentArticlePictureForm(): ArticlePictureForm
    {
        $control = $this->articlePictureFormFactory->create($this->article, $this->articlePicture);
        $control->onSuccess[] = function() {
            $this->flashMessage($this->translator->translate('article.articlePictureHasBeenSucessfullySaved'), Flash::SUCCESS);
            $this->redirect('ArticlePicture:', ['articleId' => $this->article->getId()]);
        };
        return $control;
    }

    /**
     * @return ArticlePictureGrid
     */
    public function createComponentArticlePictureGrid(): ArticlePictureGrid
    {
        $control = $this->articlePictureGridFactory->create($this->article);
        $control->onDelete[] = function()
        {
            $this->flashMessage($this->translator->translate('article.articlePictureHasBeenSucessfullyDeleted'), Flash::SUCCESS);
            $this->redirect('ArticlePicture:', ['articleId' => $this->article->getId()]);
        };
        return $control;
    }
}