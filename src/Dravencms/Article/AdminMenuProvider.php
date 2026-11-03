<?php declare(strict_types=1);

namespace Dravencms\Article;

use Dravencms\Admin\Menu\{AdminMenuItem, AdminMenuProviderInterface};

final class AdminMenuProvider implements AdminMenuProviderInterface
{
    public function getItems(): iterable
    {
        yield new AdminMenuItem('article.overview', 'article.menu.overview', destination: ':Admin:Article:Group:default', parent: 'content', icon: 'fa-bullhorn', order: 10, resource: 'article', privilege: 'edit', activeDestinations: [':Admin:Article:Article:*', ':Admin:Article:ArticlePicture:*']);
    }
}
