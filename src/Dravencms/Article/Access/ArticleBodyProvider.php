<?php declare(strict_types=1);
namespace Dravencms\Article\Access;
use Salamek\Files\Access\{IFileAccessProvider, FileAccess, BodyFiles};
use Dravencms\Model\Article\Repository\ArticleTranslationRepository;
use Nette\Security\User;

final class ArticleBodyProvider implements IFileAccessProvider
{
    public function __construct(private ArticleTranslationRepository $repository, private User $user, private BodyFiles $bodyFiles) {}
    public static function getId(): string { return 'article.body'; }
    public function resolve(string $reference): ?FileAccess
    {
        if (!preg_match('/^([1-9][0-9]*)-((?:[a-f0-9]{32}|[a-f0-9]{64}))$/D', $reference, $parts)) { return null; }
        $id = filter_var($parts[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || !($entity = $this->repository->getOneById($id))) { return null; }
        $public = $entity->getArticle()->isActive();
        if (!$public && !$this->user->isAllowed('article', 'edit')) { return null; }
        $file = $this->bodyFiles->resolve($entity->getText() . ' ' . ($entity->getPerex() ?? ''), $parts[2]);
        return $file ? new FileAccess($file, $public) : null;
    }
}
