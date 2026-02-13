<?php

namespace App\User\Repository;

use App\User\Entity\OAuthProvider;
use App\User\Repository\Interface\OAuthProviderRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OAuthProvider>
 */
class OAuthProviderRepository extends ServiceEntityRepository implements OAuthProviderRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OAuthProvider::class);
    }

    public function findById(int $id): ?OAuthProvider
    {
        return $this->find($id);
    }

    public function findByProviderAndId(string $provider, string $providerId): ?OAuthProvider
    {
        return $this->findOneBy([
            'name' => $provider,
            'providerId' => $providerId
        ]);
    }

    public function save(OAuthProvider $oauthProvider): void
    {
        $this->getEntityManager()->persist($oauthProvider);
        $this->getEntityManager()->flush();
    }

    public function delete(OAuthProvider $oauthProvider): void
    {
        $this->getEntityManager()->remove($oauthProvider);
        $this->getEntityManager()->flush();
    }
}
