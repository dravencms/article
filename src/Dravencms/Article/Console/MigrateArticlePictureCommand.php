<?php declare(strict_types = 1);


namespace Dravencms\Article\Console;

use Dravencms\Article\Article;
use Dravencms\Model\File\Entities\StructureFileLink;
use Dravencms\Model\Article\Entities\ArticlePicture;
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\Model\Article\Repository\ArticlePictureRepository;
use Dravencms\Model\File\Repository\StructureFileLinkRepository;
use Dravencms\Database\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateArticlePictureCommand
 * @package Dravencms\Gallery\Console
 */
class MigrateArticlePictureCommand extends Command
{
    protected static $defaultName = 'article:article:migrate-picture';
    protected static $defaultDescription = 'Migrate from direct fileStructure usage to fileStructureLink in related table';

    /** @var EntityManager */
    private $entityManager;

    /** @var ArticleRepository */
    private $articleRepository;

    /** @var ArticlePictureRepository */
    private $articlePictureRepository;

    /**
     * MigrateLinkGalleryCommand constructor.
     * @param EntityManager $entityManager
     * @param PictureRepository $pictureRepository
     */
    public function __construct(
        EntityManager $entityManager,
        ArticleRepository $articleRepository,
        ArticlePictureRepository $articlePictureRepository,
        StructureFileLinkRepository $structureFileLinkRepository
    )
    {
        parent::__construct(null);

        $this->entityManager = $entityManager;
        $this->articleRepository = $articleRepository;
        $this->articlePictureRepository = $articlePictureRepository;
        $this->structureFileLinkRepository = $structureFileLinkRepository;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $migrated = 0;

            foreach ($this->articleRepository->getAll() AS $article) {
                if ($article->getStructureFile()) {
                    // First check if link exists, if not create
                    $structureFileLink = $this->structureFileLinkRepository->getOneByParameters(['structureFile' => $article->getStructureFile(), 'packageName' => Article::PLUGIN_NAME]);
                    if ($structureFileLink)
                    {
                        $structureFileLink->setIsUsed(true); 
                        $structureFileLink->setIsAutoclean(true);
                    } else {
                        $structureFileLink = new StructureFileLink(Article::PLUGIN_NAME, $article->getStructureFile(), true, true);
                    }
                    $this->entityManager->persist($structureFileLink);

                    // Check if ArticlePicture with this link exists, if not create

                    $articlePicture = $this->articlePictureRepository->getOneByParameters(['article' => $article, 'structureFileLink' => $structureFileLink]);
                    if (!$articlePicture) 
                    {
                        $articlePicture = new ArticlePicture($article, $structureFileLink, true, true);
                        $this->entityManager->persist($articlePicture);
                    }

                    $migrated++;
                }
                
            }

            $this->entityManager->flush();
            
            $output->writeLn(sprintf('%s pictures has been migrated!', $migrated));
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}
