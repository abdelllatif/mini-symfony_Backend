<?php

namespace App\User\Repository;

use App\User\Entity\User;
use App\User\Entity\OAuthProvider;
use App\User\Repository\Interface\UserRepositoryInterface;
use App\User\DTO\OAuthUserDataDTO;
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

    public function findById(int $id): ?User
    {
        return $this->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findByOAuthProvider(string $provider, string $providerId): ?User
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('u.oauthProvider', 'op')
            ->where('op.name = :provider')
            ->andWhere('op.providerId = :providerId')
            ->setParameter('provider', $provider)
            ->setParameter('providerId', $providerId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function delete(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    public function createFromOAuth(OAuthUserDataDTO $oauthData): User
    {
        $user = new User();
        $user->setEmail($oauthData->getEmail());
        $user->setAvatar($oauthData->getAvatar());
        $user->setVerified($oauthData->isVerified());

        $oauthProvider = new OAuthProvider($user);
        $oauthProvider->setName($oauthData->getProvider());
        $oauthProvider->setProviderId($oauthData->getProviderId());

        $user->setOauthProvider($oauthProvider);

        $this->save($user);

        return $user;
    }

    public function updateFromOAuth(User $user, OAuthUserDataDTO $oauthData): User
    {
        $user->setAvatar($oauthData->getAvatar());
        $user->setVerified($oauthData->isVerified());

        $oauthProvider = $user->getOauthProvider();
        if ($oauthProvider) {
            $oauthProvider->setProviderId($oauthData->getProviderId());
        } else {
            $oauthProvider = new OAuthProvider($user);
            $oauthProvider->setName($oauthData->getProvider());
            $oauthProvider->setProviderId($oauthData->getProviderId());
            $user->setOauthProvider($oauthProvider);
        }

        $this->save($user);

        return $user;
    }
}
