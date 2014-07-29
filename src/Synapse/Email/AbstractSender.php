<?php

namespace Synapse\Email;

use Synapse\Stdlib\Arr;

abstract class AbstractSender implements SenderInterface
{
    /**
     * Email configuration
     *
     * @var array
     */
    protected $config;

    /**
     * @param array $config [description]
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function send(EmailEntity $email);

    /**
     * Filter an email address through the domain and email address whitelists
     *
     * If the email address is valid according to the whitelists, it is returned unchanged.
     * Otherwise the whitelist trap email address is returned.
     *
     * @param  string $address Email address to filter
     * @return string          Filtered email address
     */
    protected function filterThroughWhitelist($address)
    {
        $whitelist   = Arr::path($this->config, 'whitelist.list');
        $trapAddress = Arr::path($this->config, 'whitelist.trap');

        if (! is_array($whitelist)) {
            return $address;
        }

        list($name, $domain) = explode('@', $address);

        $addressWhitelisted = in_array($address, $whitelist);
        $domainWhitelisted  = in_array($domain, $whitelist);

        $whitelisted = ($addressWhitelisted or $domainWhitelisted);

        return $whitelisted ? $address : $this->injectIntoTrapAddress($address);
    }

    /**
     * Return the whitelist trap address with the specified email injected as a suffix
     *
     * Example:
     *     Email to transform : smith@email.com
     *     Trap email         : foo@bar.com
     *     Result             : foo+smith+email.com@bar.com
     *
     * @param  string $address Email address to transform
     * @return string          Transformed address
     */
    protected function injectIntoTrapAddress($address)
    {
        $trapAddress = Arr::path($this->config, 'whitelist.trap');

        $address = str_ireplace('@', '+', $address);

        list($trapName, $trapDomain) = explode('@', $trapAddress);

        return sprintf(
            '%s+%s@%s',
            $trapName,
            $address,
            $trapDomain
        );
    }
}
