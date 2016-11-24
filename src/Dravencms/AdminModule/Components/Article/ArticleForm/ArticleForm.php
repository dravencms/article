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

namespace Dravencms\AdminModule\Components\Article\ArticleForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\File\File;
use Dravencms\Model\Article\Entities\Article;
use Dravencms\Model\Article\Entities\Group;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Model\File\Repository\StructureFileRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Tag\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

/**
 * Description of ArticleForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ArticleForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var ArticleRepository */
    private $articleRepository;

    /** @var StructureFileRepository */
    private $structureFileRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var Group */
    private $group;

    /** @var File */
    private $file;

    /** @var Article|null */
    private $article = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * ArticleForm constructor.
     * @param Group $group
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param ArticleRepository $articleRepository
     * @param TagRepository $tagRepository
     * @param StructureFileRepository $structureFileRepository
     * @param LocaleRepository $localeRepository
     * @param File $file,
     * @param Article|null $article
     */
    public function __construct(
        Group $group,
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        ArticleRepository $articleRepository,
        TagRepository $tagRepository,
        StructureFileRepository $structureFileRepository,
        LocaleRepository $localeRepository,
        File $file,
        Article $article = null
    ) {
        parent::__construct();

        $this->group = $group;
        $this->article = $article;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->articleRepository = $articleRepository;
        $this->tagRepository = $tagRepository;
        $this->structureFileRepository = $structureFileRepository;
        $this->localeRepository = $localeRepository;
        $this->file = $file;


        if ($this->article) {
            $tags = [];
            foreach ($this->article->getTags() AS $tag) {
                $tags[$tag->getId()] = $tag->getId();
            }

            $defaults = [
                'structureFile' => ($this->article->getStructureFile() ? $this->article->getStructureFile()->getId() : null),
                'position' => $this->article->getPosition(),
                'isActive' => $this->article->isActive(),
                'isShowName' => $this->article->isShowName(),
                'isAutoDetectTags' => $this->article->isAutoDetectTags(),
                'tags' => $tags
            ];

            $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
            $defaults += $repository->findTranslations($this->article);

            $defaultLocale = $this->localeRepository->getDefault();
            if ($defaultLocale) {
                $defaults[$defaultLocale->getLanguageCode()]['name'] = $this->article->getName();
                $defaults[$defaultLocale->getLanguageCode()]['lead'] = $this->article->getLead();
                $defaults[$defaultLocale->getLanguageCode()]['subtitle'] = $this->article->getSubtitle();
                $defaults[$defaultLocale->getLanguageCode()]['perex'] = $this->article->getPerex();
                $defaults[$defaultLocale->getLanguageCode()]['text'] = $this->article->getText();
            }
        } else {
            $defaults = [
                'isActive' => true,
                'isShowName' => true,
                'isAutoDetectTags' => true
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('name')
                ->setRequired('Please enter article name.')
                ->addRule(Form::MAX_LENGTH, 'Article name is too long.', 255);

            $container->addText('lead')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'Article lead is too long.', 255);

            $container->addText('subtitle')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'Article subtitle is too long.', 255);

            $container->addTextArea('perex');

            $container->addTextArea('text');
        }

        $form->addText('structureFile');

        $form->addText('position')
            ->setDisabled((is_null($this->article)));

        $form->addMultiSelect('tags', null, $this->tagRepository->getPairs());

        $form->addCheckbox('isActive');
        $form->addCheckbox('isShowName');
        $form->addCheckbox('isAutoDetectTags');


        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->articleRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->group, $this->article)) {
                $form->addError('Tento název je již zabrán.');
            }
        }

        if (!$this->presenter->isAllowed('article', 'edit')) {
            $form->addError('Nemáte oprávění editovat article.');
        }
    }

    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        if ($values->isAutoDetectTags) {
            foreach ($this->localeRepository->getActive() AS $activeLocale) {
                foreach ($this->tagRepository->getAll($activeLocale) AS $tag) {
                    if (strpos($values->{$activeLocale->getLanguageCode()}->text, $tag->getName()) !== false && !in_array($tag->getId(), $values->tags)) {
                        $values->tags[$tag->getId()] = $tag->getId();
                    }
                }
            }
        }

        $tags = new ArrayCollection($this->tagRepository->getById($values->tags));

        if ($values->structureFile) {
            $structureFile = $this->structureFileRepository->getOneById($values->structureFile);
        } else {
            $structureFile = null;
        }

        if ($this->article) {
            $article = $this->article;
            //$article->setName($this->cleanName($values->name));
            //$article->setLead($values->lead);
            //$article->setSubtitle($values->subtitle);
            $article->setStructureFile($structureFile);
            //$article->setPerex($values->perex);
            //$article->setText($text);
            $article->setIsActive($values->isActive);
            $article->setIsShowName($values->isShowName);
            $article->setIsAutoDetectTags($values->isAutoDetectTags);
            $article->setPosition($values->position);
        } else {

            $defaultLocale = $this->localeRepository->getDefault();

            $article = new Article($this->group, $this->cleanName($values->{$defaultLocale->getLanguageCode()}->name), $this->cleanText($values->{$defaultLocale->getLanguageCode()}->text), $values->{$defaultLocale->getLanguageCode()}->subtitle, $values->{$defaultLocale->getLanguageCode()}->lead, $values->{$defaultLocale->getLanguageCode()}->perex, $values->isActive, $values->isShowName, $values->isAutoDetectTags,
                $structureFile);
        }
        $article->setTags($tags);

        $repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $repository->translate($article, 'name', $activeLocale->getLanguageCode(), $this->cleanName($values->{$activeLocale->getLanguageCode()}->name))
                ->translate($article, 'lead', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->lead)
                ->translate($article, 'subtitle', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->subtitle)
                ->translate($article, 'perex', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->perex)
                ->translate($article, 'text', $activeLocale->getLanguageCode(), $this->cleanText($values->{$activeLocale->getLanguageCode()}->text));
        }

        $this->entityManager->persist($article);

        $this->entityManager->flush();

        $this->onSuccess();
    }

    private function cleanName($name)
    {
        return Strings::firstUpper(Strings::lower($name));
    }

    /**
     * @param $text
     * @return string
     */
    private function cleanText($text)
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $text = mb_convert_encoding($text, 'HTML-ENTITIES', "UTF-8");
        @$dom->loadHTML($text);

        foreach ($dom->getElementsByTagName('div') as $node) {
            $class = $node->getAttribute("class");
            $classArray = explode(' ', $class);
            $hasNote = false;
            foreach ($classArray AS $k => $classItem) {
                if (strpos($classItem, 'note-') !== false) {
                    $hasNote = true;
                    unset($classArray[$k]);
                }
            }

            if ($hasNote) {
                $node->removeAttribute("id");
                $node->removeAttribute("contenteditable");
            }

            $node->setAttribute("class", implode(' ', $classArray));
        }

        foreach ($dom->getElementsByTagName('iframe') as $node) {
            if ($node->hasAttribute("frameborder")) {
                $node->removeAttribute("frameborder");
            }
        }

        $unstyledElements = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        foreach ($unstyledElements AS $unstyledElement) {
            foreach ($dom->getElementsByTagName($unstyledElement) as $node) {
                if ($node->hasAttribute("style")) {
                    $node->removeAttribute("style");
                }
            }
        }

        $xpath = new \DOMXPath($dom);
        $body = $xpath->query('/html/body');
        return html_entity_decode(strtr($dom->saveHTML($body->item(0)), array('<body>' => '', '</body>' => '')));
    }

    public function render()
    {
        $template = $this->template;
        $template->fileSelectorPath = $this->file->getFileSelectorPath();
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/ArticleForm.latte');
        $template->render();
    }
}