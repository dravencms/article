<?php declare(strict_types=1);
namespace Dravencms\Article\Access;
use Salamek\Files\Access\{IFileAccessProvider, FileAccess};
use Dravencms\Model\Article\Repository\ArticlePictureRepository;
use Nette\Security\User;

final class PictureFileProvider implements IFileAccessProvider
{
    public function __construct(private ArticlePictureRepository $repository, private User $user) {}
    public static function getId(): string { return 'article.picture'; }
    public function resolve(string $reference): ?FileAccess
    {
        $id = filter_var($reference, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || !($entity = $this->repository->getOneById($id))) { return null; }
        $public = $entity->isActive() && $entity->getArticle()->isActive();
        if (!$public && !$this->user->isAllowed('article', 'edit')) { return null; }
        $attachment = $entity->getStructureFileLink()?->getStructureFile();
        return $attachment ? new FileAccess($attachment->getFile(), $public, $attachment->getBasename()) : null;
    }
}
