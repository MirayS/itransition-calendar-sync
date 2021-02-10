<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Calendar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Calendar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calendar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calendar[]    findAll()
 * @method Calendar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Calendar::class);
    }

    public function findByNotificationChannelId(string $channelId): ?Calendar
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->where("JSON_VALUE(t.metaData, '$.notificationId') = :channelId")
            ->setParameter('channelId', $channelId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
