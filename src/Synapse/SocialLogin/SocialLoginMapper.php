<?php

namespace Synapse\SocialLogin;

use Synapse\Mapper;

/**
 * User mapper
 */
class SocialLoginMapper extends Mapper\AbstractMapper
{
    /**
     * Use all mapper traits, making this a general purpose mapper
     */
    use Mapper\InserterTrait;
    use Mapper\FinderTrait;
    use Mapper\UpdaterTrait;
    use Mapper\DeleterTrait;

    /**
     * Name of user table
     *
     * @var string
     */
    protected $tableName = 'user_social_logins';

    /**
     * Find a social login by user ID
     *
     * @param  mixed $id
     * @return SocialLoginEntity
     */
    public function findByUserId($id)
    {
        $entity = $this->findBy(['user_id' => $id]);
        return $entity;
    }

    /**
     * Find a social login account given a provider and the provider_user_id of the account
     *
     * @param  string $provider
     * @param  mixed  $id
     * @return SocialLoginEntity
     */
    public function findByProviderUserId($provider, $id)
    {
        $entity = $this->findBy([
            'provider'         => $provider,
            'provider_user_id' => $id
        ]);
        return $entity;
    }
}
