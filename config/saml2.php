<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    |
    | This will allow you to override the tenant model with your own.
    |
    */

    'tenantModel' => Slides\Saml2\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Use built-in routes
    |--------------------------------------------------------------------------
    |
    | If "useRoutes" set to true, the package defines five new routes:
    |
    | Method | URI                             | Name
    | -------|---------------------------------|------------------
    | POST   | {routesPrefix}/{uuid}/acs       | saml.acs
    | GET    | {routesPrefix}/{uuid}/login     | saml.login
    | GET    | {routesPrefix}/{uuid}/logout    | saml.logout
    | GET    | {routesPrefix}/{uuid}/metadata  | saml.metadata
    | GET    | {routesPrefix}/{uuid}/sls       | saml.sls
    | POST   | {routesPrefix}/{uuid}/sls       | saml.sls
    |
    */

    'useRoutes' => true,

    /*
    |--------------------------------------------------------------------------
    | Built-in routes prefix
    |--------------------------------------------------------------------------
    |
    | Here you may define the prefix for built-in routes.
    |
    */

    'routesPrefix' => '/saml2',

    /*
    |--------------------------------------------------------------------------
    | Middle groups to use for the SAML routes
    |--------------------------------------------------------------------------
    |
    | Note, Laravel 5.2 requires a group which includes StartSession
    |
    */

    'routesMiddleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Signature validation
    |--------------------------------------------------------------------------
    |
    | Set to true if you want to use parameters from $_SERVER to validate the signature.
    |
    */

    'retrieveParametersFromServer' => false,

    /*
    |--------------------------------------------------------------------------
    | Login redirection URL.
    |--------------------------------------------------------------------------
    |
    | The redirection URL after successful login.
    |
    */

    'loginRoute' => env('SAML2_LOGIN_URL'),

    /*
    |--------------------------------------------------------------------------
    | Logout redirection URL.
    |--------------------------------------------------------------------------
    |
    | The redirection URL after successful logout.
    |
    */

    'logoutRoute' => env('SAML2_LOGOUT_URL'),


    /*
    |--------------------------------------------------------------------------
    | Login error redirection URL.
    |--------------------------------------------------------------------------
    |
    | The redirection URL after login failing.
    |
    */

    'errorRoute' => env('SAML2_ERROR_URL'),

    /*
    |--------------------------------------------------------------------------
    | Strict mode.
    |--------------------------------------------------------------------------
    |
    | If 'strict' is True, then the PHP Toolkit will reject unsigned
    | or unencrypted messages if it expects them signed or encrypted
    | Also will reject the messages if not strictly follow the SAML
    | standard: Destination, NameId, Conditions... are validated too.
    |
    */

    'strict' => true,

    /*
    |--------------------------------------------------------------------------
    | Debug mode.
    |--------------------------------------------------------------------------
    |
    | When enabled, errors must be printed.
    |
    */

    'debug' => env('SAML2_DEBUG', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Whether to use `X-Forwarded-*` headers to determine port/domain/protocol.
    |--------------------------------------------------------------------------
    |
    | If 'proxyVars' is True, then the Saml lib will trust proxy headers
    | e.g X-Forwarded-Proto / HTTP_X_FORWARDED_PROTO. This is useful if
    | your application is running behind a load balancer which terminates SSL.
    |
    */

    'proxyVars' => false,

    /*
    |--------------------------------------------------------------------------
    | Whether to force the server self port
    |--------------------------------------------------------------------------
    |
    | If 'serverPort' is setted will get this one - Good in case your Laravel 
    | application is behind a docker container using another port.
    |
    */

    'serverPort' => env('APP_PORT'),	

    /*
    |--------------------------------------------------------------------------
    | Service Provider configuration.
    |--------------------------------------------------------------------------
    |
    | General setting of the service provider.
    |
    */

    'sp' => [

        /*
        |--------------------------------------------------------------------------
        | NameID format.
        |--------------------------------------------------------------------------
        |
        | Specifies constraints on the name identifier to be used to
        | represent the requested subject.
        |
        */

        'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

        /*
        |--------------------------------------------------------------------------
        | SP Certificates.
        |--------------------------------------------------------------------------
        |
        | Usually x509cert and privateKey of the SP are provided by files placed at
        | the certs folder. But we can also provide them with the following parameters.
        |
        */

        'x509cert' => env('SAML2_SP_CERT_x509', ''),
        'privateKey' => env('SAML2_SP_CERT_PRIVATEKEY', ''),

        /*
        |--------------------------------------------------------------------------
        | Identifier (URI) of the SP entity.
        |--------------------------------------------------------------------------
        |
        | Leave blank to use the 'saml.metadata' route.
        |
        */

        'entityId' => env('SAML2_SP_ENTITYID', ''),

        /*
        |--------------------------------------------------------------------------
        | The Assertion Consumer Service (ACS) URL.
        |--------------------------------------------------------------------------
        |
        | URL Location where the <Response> from the IdP will be returned, using HTTP-POST binding.
        | Leave blank to use the 'saml.acs' route.
        |
        */

        'assertionConsumerService' => [
            'url' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | The Single Logout Service URL.
        |--------------------------------------------------------------------------
        |
        | Specifies info about where and how the <Logout Response> message MUST be
        | returned to the requester, in this case our SP.
        |
        | URL Location where the <Response> from the IdP will be returned, using HTTP-Redirect binding.
        | Leave blank to use the 'saml.sls' route.
        |
        */

        'singleLogoutService' => [
            'url' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OneLogin security settings.
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'security' => [

        /*
        |--------------------------------------------------------------------------
        | NameId encryption
        |--------------------------------------------------------------------------
        |
        | Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
        | will be encrypted.
        |
        */

        'nameIdEncrypted' => false,

        /*
        |--------------------------------------------------------------------------
        | AuthnRequest signage
        |--------------------------------------------------------------------------
        |
        | Indicates whether the <samlp:AuthnRequest> messages sent by
        | this SP will be signed. The Metadata of the SP will offer this info
        |
        */

        'authnRequestsSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Logout request signage
        |--------------------------------------------------------------------------
        |
        | Indicates whether the <samlp:logoutRequest> messages sent by this SP
        | will be signed.
        |
        */

        'logoutRequestSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Logout response signage
        |--------------------------------------------------------------------------
        |
        | Indicates whether the <samlp:logoutResponse> messages sent by this SP
        | will be signed.
        |
        */

        'logoutResponseSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Whether need to sign metadata.
        |--------------------------------------------------------------------------
        |
        | The possible values:
        | - false
        | - true (use certs)
        | - array:
        |   ```
        |   [
        |       'keyFileName' => 'metadata.key',
        |       'certFileName' => 'metadata.crt'
        |   ]
        |   ```
        |
        */

        'signMetadata' => false,

        /*
        |--------------------------------------------------------------------------
        | Requirement to sign messages.
        |--------------------------------------------------------------------------
        |
        | Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
        | <samlp:LogoutResponse> elements received by this SP to be signed.
        |
        */

        'wantMessagesSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Requirement to sign assertion elements.
        |--------------------------------------------------------------------------
        |
        | Indicates a requirement for the <saml:Assertion> elements received by
        | this SP to be signed.
        |
        */

        'wantAssertionsSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Requirement to encrypt NameID.
        |--------------------------------------------------------------------------
        |
        | Indicates a requirement for the NameID received by this SP to be encrypted.
        |
        */

        'wantNameIdEncrypted' => false,

        /*
        |--------------------------------------------------------------------------
        | Authentication context.
        |--------------------------------------------------------------------------
        |
        | Set to false and no AuthContext will be sent in the AuthNRequest,
        |
        | Set true or don't present this parameter and you will get an
        | AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'
        |
        | Set an array with the possible auth context values:
        | ['urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509']
        |
        */

        'requestedAuthnContext' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact information.
    |--------------------------------------------------------------------------
    |
    | It is recommended to supply a technical and support contacts.
    |
    */

    'contactPerson' => [
        'technical' => [
            'givenName' => env('SAML2_CONTACT_TECHNICAL_NAME', 'name'),
            'emailAddress' => env('SAML2_CONTACT_TECHNICAL_EMAIL', 'no@reply.com'),
        ],
        'support' => [
            'givenName' => env('SAML2_CONTACT_SUPPORT_NAME', 'Support'),
            'emailAddress' => env('SAML2_CONTACT_SUPPORT_EMAIL', 'no@reply.com'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization information.
    |--------------------------------------------------------------------------
    |
    | The info in en_US lang is recommended, add more if required.
    |
    */

    'organization' => [
        'en-US' => [
            'name' => env('SAML2_ORGANIZATION_NAME', 'Name'),
            'displayname' => env('SAML2_ORGANIZATION_NAME', 'Display Name'),
            'url' => env('SAML2_ORGANIZATION_URL', 'http://url'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Load default migrations
    |--------------------------------------------------------------------------
    |
    | This will allow you to disable or enable the default migrations of the package.
    |
    */
    'load_migrations' => true,
];
