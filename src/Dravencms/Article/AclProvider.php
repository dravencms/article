<?php declare(strict_types=1);

namespace Dravencms\Article;

use Dravencms\User\Acl\{AclPermission, AclProviderInterface};

final class AclProvider implements AclProviderInterface
{
    public function getPermissions(): iterable
    {
        yield new AclPermission('article', 'edit', 'article.acl.permissions.edit.label', resourceLabel: 'article.acl.resource');
        yield new AclPermission('article', 'delete', 'article.acl.permissions.delete.label', resourceLabel: 'article.acl.resource');
    }
}
