<?php declare(strict_types=1);
namespace Dravencms\Article;

use Dravencms\Admin\Dashboard\{DashboardDefinition, DashboardMetric, StatisticsComponent};
use Dravencms\Database\EntityManager;

final class DashboardComponent extends StatisticsComponent
{
    public function __construct(private EntityManager $entityManager) {}

    public function getDefinition(): DashboardDefinition
    {
        return new DashboardDefinition('article.overview', 'article.dashboard.title', 50,
            resource: 'article', privilege: 'edit', destination: ':Admin:Article:Group:default', icon: 'fa-bullhorn');
    }

    protected function getMetrics(): iterable
    {
        $query = $this->entityManager->createQuery('SELECT COUNT(a.id) AS total, COALESCE(SUM(CASE WHEN a.isActive = true THEN 1 ELSE 0 END), 0) AS active FROM Dravencms\\Model\\Article\\Entities\\Article a');
        $stats = $query->getSingleResult();
        yield new DashboardMetric('article.dashboard.total', (int) $stats['total']);
        yield new DashboardMetric('article.dashboard.active', (int) $stats['active']);
    }
}
