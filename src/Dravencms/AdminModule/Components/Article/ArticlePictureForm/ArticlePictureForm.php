<?php declare(strict_types = 1);

/**
 * Copyright (C) 2023 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Article\ArticlePictureForm;

use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\File\File;
use Dravencms\Model\File\Entities\StructureFileLink;
use Dravencms\Model\File\Entities\Structure;
use Dravencms\Model\File\Repository\StructureFileRepository;
use Dravencms\Model\File\Repository\StructureRepository;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Article\Entities\ArticlePicture;
use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Repository\ArticlePictureRepository;
use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\Form;
use Nette\Http\FileUpload;
use Salamek\Files\FileStorage;

class ArticlePictureForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var ArticlePictureRepository */
    private $articlePictureRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var Article */
    private $article;

    /** @var StructureFileRepository */
    private $structureFileRepository;

    /** @var StructureRepository */
    private $structureRepository;

    /** @var FileStorage */
    private $fileStorage;

    /** @var File */
    private $file;

    /** @var null|ArticlePicture */
    private $articlePicture = null;

    /** @var null|callable */
    public $onSuccess = null;

    /**
     * ArticlePictureForm constructor.
     * @param BaseFormFactory $baseForm
     * @param ArticlePictureRepository $articlePictureRepository
     * @param EntityManager $entityManager
     * @param Article $article
     * @param StructureFileRepository $structureFileRepository
     * @param StructureRepository $structureRepository
     * @param FileStorage $fileStorage
     * @param File $file
     * @param ArticlePicture|null $articlePicture
     */
    public function __construct(
        BaseFormFactory $baseForm,
        ArticlePictureRepository $articlePictureRepository,
        EntityManager $entityManager,
        Article $article,
        StructureFileRepository $structureFileRepository,
        StructureRepository $structureRepository,
        FileStorage $fileStorage,
        File $file,
        ArticlePicture $articlePicture = null
    )
    {
        $this->baseFormFactory = $baseForm;
        $this->articlePictureRepository = $articlePictureRepository;
        $this->entityManager = $entityManager;
        $this->articlePicture = $articlePicture;
        $this->file = $file;
        $this->structureFileRepository = $structureFileRepository;
        $this->structureRepository = $structureRepository;
        $this->fileStorage = $fileStorage;
        $this->article = $article;

        $defaultValues = [];
        if ($this->articlePicture)
        {
            $defaultValues['articlePicture'] = ($this->articlePicture->getStructureFileLink() ? $this->articlePicture->getStructureFileLink()->getStructureFile()->getId() : null);
            $defaultValues['isPrimary'] = $this->articlePicture->isPrimary();
            $defaultValues['isActive'] = $this->articlePicture->isActive();
            $defaultValues['position'] = $this->articlePicture->getPosition();
        }
        else{
            $defaultValues['isPrimary'] = false;
            $defaultValues['isActive'] = true;
        }

        $this['form']->setDefaults($defaultValues);
    }

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = $this->baseFormFactory->create();


        $form->addInteger('articlePicture');
        $form->addInteger('position');
        $form->addUpload('file');

        $form->addCheckbox('isPrimary');
        $form->addCheckbox('isActive');

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
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form): void
    {
        $values = $form->getValues();

        if ($values->articlePicture) {
            $structureFile = $this->structureFileRepository->getOneById($values->articlePicture);
        } else {
            $structureFile = null;
        }

        /** @var FileUpload $file */
        $file = $values->file;
        if ($file->isOk()) {
            $structureName = 'Article';
            if (!$structure = $this->structureRepository->getOneByName($structureName)) {
                $structure = new Structure($structureName);
                $this->entityManager->persist($structure);
                $this->entityManager->flush();
            }
            $structureFile = $this->fileStorage->processFile($file, $structure);
        }

        if ($this->articlePicture)
        {
            $articlePicture = $this->articlePicture;
            $articlePicture->setIsPrimary($values->isPrimary);
            $articlePicture->setIsActive($values->isActive);
            $articlePicture->setPosition($values->position);

            if ($articlePicture->getStructureFileLink()) {
                $existingStructureFile = $articlePicture->getStructureFileLink();
                $existingStructureFile->setStructureFile($structureFile);

            } else {
                $existingStructureFile = new StructureFileLink(\Dravencms\Article\Article::PLUGIN_NAME, $structureFile, true, true);
            }

            $this->entityManager->persist($existingStructureFile);
        }
        else
        {
            $structureFileLink = new StructureFileLink(\Dravencms\Article\Article::PLUGIN_NAME, $structureFile, true, true);
            $this->entityManager->persist($structureFileLink);
            $articlePicture = new ArticlePicture($this->article, $structureFileLink, $values->isActive, $values->isPrimary);
        }

        $this->entityManager->persist($articlePicture);


        $this->entityManager->flush();

        $this->onSuccess($articlePicture);
    }

    public function render(): void
    {
        $template = $this->template;
        $template->fileSelectorPath = $this->file->getFileSelectorPath();
        $template->setFile(__DIR__ . '/ArticlePictureForm.latte');
        $template->render();
    }


}