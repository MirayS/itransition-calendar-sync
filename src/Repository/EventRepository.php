<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[]|null
     */
    public function findAllInDateTimeRange(\DateTime $start, \DateTime $end): ?array
    {
        $qb = $this->createQueryBuilder('t');

        return $qb
            ->where($qb->expr()->between('t.startTime', ':min', ':max'))
            ->join('t.calendar', 'c')
            ->andWhere('c.isShow = 1')
            ->setParameter('min', $start)
            ->setParameter('max', $end)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
