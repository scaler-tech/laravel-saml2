<?php

namespace Slides\Saml2;

use OneLogin\Saml2\Auth as OneLoginAuth;
use Slides\Saml2\Models\TenantInterface;

/**
 * Class Saml2User
 */
class Saml2User
{
    /**
     * OneLogin authentication handler.
     *
     * @var OneLoginAuth
     */
    protected $auth;

    /**
     * The tenant user belongs to.
     *
     * @var TenantInterface
     */
    protected $tenant;

    /**
     * Parsed SAML attributes mapped by property name.
     *
     * @var array<string, array|null>
     */
    protected $parsedAttributes = [];

    /**
     * Saml2User constructor.
     */
    public function __construct(OneLoginAuth $auth, TenantInterface $tenant)
    {
        $this->auth = $auth;
        $this->tenant = $tenant;
    }

    /**
     * Get the user ID retrieved from assertion processed this request.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->auth->getNameId();
    }

    /**
     * Get the attributes retrieved from assertion processed this request
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->auth->getAttributes();
    }

    /**
     * Returns the requested SAML attribute
     *
     * @param string $name the requested attribute of the user
     *
     * @return array|null requested SAML attribute ($name)
     */
    public function getAttribute($name)
    {
        return $this->auth->getAttribute($name);
    }

    /**
     * The attributes retrieved from assertion processed this request.
     *
     * @return array
     */
    public function getAttributesWithFriendlyName()
    {
        return $this->auth->getAttributesWithFriendlyName();
    }

    /**
     * The SAML assertion processed this request.
     *
     * @return string
     */
    public function getRawSamlAssertion()
    {
        return app('request')->input('SAMLResponse'); // just this request
    }

    /**
     * Get the intended URL.
     */
    public function getIntendedUrl()
    {
        $relayState = app('request')->input('RelayState');

        $url = app('Illuminate\Contracts\Routing\UrlGenerator');

        if ($relayState && $url->full() != $relayState) {
            return $relayState;
        }

        return null;
    }

    /**
     * Parses a SAML property and adds this property to this user or returns the value.
     *
     * @param string $samlAttribute
     * @param string $propertyName
     *
     * @return array|null
     */
    public function parseUserAttribute($samlAttribute = null, $propertyName = null)
    {
        if (empty($samlAttribute)) {
            return null;
        }

        if (empty($propertyName)) {
            return $this->getAttribute($samlAttribute);
        }

        $value = $this->getAttribute($samlAttribute);
        $this->parsedAttributes[$propertyName] = $value;

        return $value;
    }

    /**
     * Parse the SAML attributes and add them to this user.
     *
     * @param array $attributes Array of properties which need to be parsed, like ['email' => 'urn:oid:0.9.2342.19200300.100.1.3']
     *
     * @return void
     */
    public function parseAttributes($attributes = [])
    {
        foreach ($attributes as $propertyName => $samlAttribute) {
            $this->parseUserAttribute($samlAttribute, $propertyName);
        }
    }

    /**
     * Get user's session index.
     *
     * @return string|null
     */
    public function getSessionIndex()
    {
        return $this->auth->getSessionIndex();
    }

    /**
     * Get user's name ID.
     *
     * @return string
     */
    public function getNameId()
    {
        return $this->auth->getNameId();
    }

    /**
     * Set a tenant
     *
     * @return void
     */
    public function setTenant(TenantInterface $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Get a resolved tenant.
     *
     * @return TenantInterface|null
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Read parsed attribute values as virtual properties (for backward compatibility).
     *
     * @param string $name
     *
     * @return array|null
     */
    public function __get($name)
    {
        return $this->parsedAttributes[$name] ?? null;
    }

    /**
     * Determine whether a parsed attribute exists for a virtual property.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->parsedAttributes);
    }
}
