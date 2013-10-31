<?php

namespace Alchemy\Phrasea\Model\Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * FeedRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FeedRepository extends EntityRepository
{
    /**
     * Returns all the feeds a user can access.
     *
     * @param  User_Adapter                            $user
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAllForUser(\User_Adapter $user)
    {
        $base_ids = array_keys($user->ACL()->get_granted_base());

        $qb = $this
            ->createQueryBuilder('f');

        $qb->where($qb->expr()->isNull('f.baseId'))
            ->orWhere('f.public = true');

        if (count($base_ids) > 0) {
            $qb->orWhere($qb->expr()->in('f.baseId', $base_ids));
        }

        $qb->orderBy('f.updatedOn', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all the feeds from a given array containing their id.
     *
     * @param  array      $feedIds
     * @return Collection
     */
    public function findByIds(array $feedIds)
    {
        $qb = $this->createQueryBuilder('f');

        if (!empty($feedIds)) {
            $qb->Where($qb->expr()->in('f.id', $feedIds));
        }

        $qb->orderBy('f.updatedOn', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
