<?php declare(strict_types = 1);

namespace Dravencms\Article\DI;

use Contributte\Translation\DI\TranslationProviderInterface;
use Dravencms\Article\Article;


use Nette\DI\CompilerExtension;
use Dravencms\Structure\DI\StructureExtension;

/**
 * Class ArticleExtension
 * @package Dravencms\Article\DI
 */
class ArticleExtension extends CompilerExtension implements TranslationProviderInterface
{

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();


        $builder->addDefinition($this->prefix('article'))
            ->setFactory(Article::class);

        if (class_exists(StructureExtension::class)) {
            $this->loadCmsComponents();
            $this->loadCmsModels();
        }
        $this->loadComponents();
        $this->loadModels();
        $this->loadConsole();
    }


    protected function loadCmsComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/cmsComponents.neon') as $i => $command) {
            $cli = $builder->addFactoryDefinition($this->prefix('cmsComponent.' . $i))
                ->addTag(StructureExtension::TAG_COMPONENT);
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadCmsModels(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/cmsModels.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cmsModels.' . $i));
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/components.neon') as $i => $command) {
            $cli = $builder->addFactoryDefinition($this->prefix('components.' . $i));
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadModels(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/models.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('models.' . $i));
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadConsole(): void
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cli.' . $i))
                ->setAutowired(false);

            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    public function getTranslationResources(): array
    {
        return [__DIR__.'/../lang'];
    }
}
