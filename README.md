# StethoMeOryAuthBundle

[![Latest Stable Version](https://poser.pugx.org/stethome/ory-auth-bundle/v/stable.svg)](https://packagist.org/packages/stethome/ory-auth-bundle)

This bundle provides Symfony Authenticator for [Ory Kratos](https://www.ory.sh/kratos/)

# Installation

```
composer require stethome/ory-auth-bundle
```

If your project does not use Symfony Flex you need to manually register the bundle in `config/bundles.php`:

```php
<?php

return [
    // your other bundles above
    StethoMe\OryAuthBundle\StethoMeOryAuthBundle::class => ['all' => true],
];
```

----------
# Configuration

To authenticate your users with Ory Kratos you need to enable `ory_kratos` authenticator on your firewall and create a user provider.

## Firewall
### Minimal configuration
```yaml
security:
    providers:
        my_user_provider: { id: App\Security\Service\User\UserProvider }
        
    firewalls:
        main:
            provider: my_user_provider
            ory_kratos:    
                public_url: https://account.mycompany.com
```
### Full configuration example
```yaml
security:
    providers:
        my_user_provider: { id: App\Security\Service\User\UserProvider }
        my_other_user_provider: { id: App\Security\Service\User\OtherUserProvider }
        
    firewalls:
        main:
            provider: my_user_provider
            ory_kratos:
                # The URL where Ory Kratos's Public API is located at.
                # If this app and Ory Kratos are running in the same private network, this should be the private network address (e.g. kratos-public.svc.cluster.local)
                public_url: kratos-public.svc.cluster.local
                # The browser accessible URL where Ory Kratos's public API is located, only needed if it differs from public_url
                browser_url: https://account.mycompany.com
                # Name of the cookie holding Ory Kratos session
                session_cookie: ory_kratos_session
                # User provider used by OryKratosAuthenticator, defaults to firewall user provider
                provider: my_other_user_provider
                # Base authenticator service, the firewall authenticator will be child of this service
                authenticator: ~
```

## User Provider

```php
class UserProvider implements UserProviderInterface
{
    /**
     * @param AppUser $user
     */
    public function refreshUser(UserInterface $user): AppUser
    {
        return $user; // noop
    }

    public function loadUserByIdentifier(string $identifier): AppUser
    {
        // identifier is Ory Kratos Identity UUID
        return new AppUser($identifier);
    }

    public function supportsClass(string $class): bool
    {
        return $class === AppUser::class;
    }
}

class AppUser implements UserInterface
{
    private string $uuid;

    public function __construct(string $identity)
    {
        $this->uuid = $identity;
    }
    
    // UserInterface methods
};
```

----------
# License

This bundle is under the MIT license.  
For the whole copyright, see the [LICENSE](LICENSE) file distributed with this source code.
