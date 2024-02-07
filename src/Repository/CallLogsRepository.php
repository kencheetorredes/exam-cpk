<?php

namespace App\Repository;

use App\Entity\CallLogs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CallLogs>
 *
 * @method CallLogs|null find($id, $lockMode = null, $lockVersion = null)
 * @method CallLogs|null findOneBy(array $criteria, array $orderBy = null)
 * @method CallLogs[]    findAll()
 * @method CallLogs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CallLogsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CallLogs::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(CallLogs $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(CallLogs $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function verifyData($date,$cliend_id)
    {
       
       return  $this->createQueryBuilder('s')
                ->where('s.date  = :date')
                ->andWhere('s.client_id=:cliend_id')
                ->setParameters(['date' => $date,'cliend_id' => $cliend_id])
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function getStatistic() : array
    {

       
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            '
            select e.client_id,
                   count(e.client_id) total_client,
                   sum(e.duration) as total_duration,
                   sum(case when e.is_same_continent = 1 then e.is_same_continent else 0 end) as same_continent,
                   sum(case when e.is_same_continent = 1 then e.duration else 0 end) as same_continent_duration
            from App\Entity\CallLogs e
            group by e.client_id
            '
        );

        return $query->getResult();
    }

    // /**
    //  * @return CallLogs[] Returns an array of CallLogs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CallLogs
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
