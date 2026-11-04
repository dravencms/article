<?php declare(strict_types=1);
namespace Dravencms\Article\Access;
use Salamek\Files\Access\{IFileAccessProvider, FileAccess};
use Dravencms\Model\Article\Repository\ArticleRepository;
use Dravencms\File\Access\AdminFileAccess;

final class ArticleFileProvider implements IFileAccessProvider
{
    public function __construct(private ArticleRepository $repository, private AdminFileAccess $user) {}
    public static function getId(): string { return 'article.image'; }
    public function resolve(string $reference): ?FileAccess
    {
        $id = filter_var($reference, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || !($entity = $this->repository->getOneById($id))) { return null; }
        $public = $entity->isActive();
        if (!$public && !$this->user->isAllowed('article', 'edit')) { return null; }
        $attachment = $entity->getStructureFile();
        return $attachment ? new FileAccess($attachment->getFile(), $public, $attachment->getBasename()) : null;
    }
}
