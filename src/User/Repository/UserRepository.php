<?php

namespace App\User\Repository;

use App\User\Entity\OAuthProvider;
use App\User\Entity\User;
use App\User\Repository\Interface\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function find(int $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.oauthProvider', 'op')
            ->addSelect('op')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneBy(array $criteria): ?User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.oauthProvider', 'op')
            ->addSelect('op')
            ->where($this->buildWhereClause($criteria))
            ->setParameters($criteria)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.oauthProvider', 'op')
            ->addSelect('op');

        if (!empty($criteria)) {
            $qb->where($this->buildWhereClause($criteria))
               ->setParameters($criteria);
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy('u.' . $field, $direction);
            }
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.oauthProvider', 'op')
            ->addSelect('op')
            ->getQuery()
            ->getResult();
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function remove(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.oauthProvider', 'op')
            ->addSelect('op')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByOAuthProvider(string $provider, string $providerId): ?User
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.oauthProvider', 'op')
            ->addSelect('op')
            ->where('op.name = :provider')
            ->andWhere('op.providerId = :providerId')
            ->setParameter('provider', $provider)
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function create(User $user): User
    {
        $this->save($user);
        return $user;
    }

    private function buildWhereClause(array $criteria): string
    {
        $clauses = [];
        foreach (array_keys($criteria) as $field) {
            $clauses[] = "u.$field = :$field";
        }
        return implode(' AND ', $clauses);
    }
}
