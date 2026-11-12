<?php

namespace App\Security\Voter;

use App\Entity\Store;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

final class StoreVoter extends Voter
{
    public const MANAGE = 'STORE_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::MANAGE === $attribute && $subject instanceof Store;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        return $user instanceof User
            && in_array('ROLE_OWNER', $token->getRoleNames(), true)
            && null !== $user->getId()
            && $subject->getCreatedBy()?->getId() === $user->getId();
    }
}
