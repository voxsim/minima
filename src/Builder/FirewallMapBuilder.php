<?php namespace Minima\Builder;

use psr\log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\Security\Http\Firewall;
use Symfony\Component\Security\Http\FirewallMap;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Http\EntryPoint\BasicAuthenticationEntryPoint;
use Symfony\Component\Security\Http\Firewall\BasicAuthenticationListener;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Firewall\AccessListener;

class FirewallMapBuilder {
  private static function createInMemoryUserProvider($params) {
    $users = array();
    foreach ($params as $name => $user) {
      $users[$name] = array('roles' => (array) $user[0], 'password' => $user[1]);
    }

    return new InMemoryUserProvider($users);
  }

  public static function build($configuration = array(), LoggerInterface $logger, TokenStorageInterface $tokenStorage, EventDispatcherInterface $dispatcher) {
    $defaultConfiguration = array(
			      'security.firewalls' => array(),
			      'security.access_rules' => array(),
			      'security.realm_name' => 'Secured',
			      'security.hide_user_not_found' => true
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);

    $accessMap = new AccessMap();

    foreach ($configuration['security.access_rules'] as $rule) {
	if (is_string($rule[0])) {
	    $rule[0] = new RequestMatcher($rule[0]);
	}

	$map->add($rule[0], (array) $rule[1], isset($rule[2]) ? $rule[2] : null);
    }

    $encoderDigest = new MessageDigestPasswordEncoder();
    $userChecker = new UserChecker();
    $encoderFactory = new EncoderFactory(array(
	'Symfony\Component\Security\Core\User\UserInterface' => $encoderDigest,
    ));
    $trustResolver = new AuthenticationTrustResolver('Symfony\Component\Security\Core\Authentication\Token\AnonymousToken', 'Symfony\Component\Security\Core\Authentication\Token\RememberMeToken');
    $httpUtils = new HttpUtils(null, null);
    $accessDecisionManager = new AccessDecisionManager(array(
	  new RoleHierarchyVoter(new RoleHierarchy(array())),
	  new AuthenticatedVoter($trustResolver),
    ));

    $configs = array();
    foreach ($configuration['security.firewalls'] as $name => $firewall) {
	$pattern = isset($firewall['pattern']) ? $firewall['pattern'] : null;
	$security = isset($firewall['security']) ? (bool) $firewall['security'] : true;
	$protected = false === $security ? false : count($firewall);
	$users = isset($firewall['users']) ? $firewall['users'] : array();
	$userProvider = is_array($users) ? static::createInMemoryUserProvider($users) : $users;
            
	$providerKey = 'basic-authentication-'.$name;

	$authenticationProvider = new DaoAuthenticationProvider(
                    $userProvider,
                    $userChecker,
                    $providerKey,
                    $encoderFactory,
                    $configuration['security.hide_user_not_found']
                );

	$authenticationProviders = array($authenticationProvider);

	$authenticationManager = new AuthenticationProviderManager($authenticationProviders);
	$authenticationManager->setEventDispatcher($dispatcher);

	$listeners = array();

	$exceptionListener = null;

	if ($protected) {
          $entryPoint = new BasicAuthenticationEntryPoint($configuration['security.realm_name']);
	  $listeners[] = new BasicAuthenticationListener(
                    $tokenStorage,
                    $authenticationManager,
                    $providerKey,
                    $entryPoint,
                    $logger
                );

	  $exceptionListener = new ExceptionListener(
                    $tokenStorage,
                    $trustResolver,
                    $httpUtils,
                    $providerKey,
                    $entryPoint,
                    null, // errorPage
                    null, // accessDeniedHandler
                    $logger
                );

          $listeners[] = new AccessListener($tokenStorage, $accessDecisionManager, $accessMap, $authenticationManager);
	}

	$configs[$name] = array('pattern' => $pattern, 'listeners' => $listeners, 'exception-listener' => $exceptionListener);
    }

    $map = new FirewallMap();
    foreach ($configs as $name => $config) {
	$map->add(
	    is_string($config['pattern']) ? new RequestMatcher($config['pattern']) : $config['pattern'],
	    $config['listeners'],
            $config['exception-listener']
	);
    }

    return new Firewall($map, $dispatcher);
  }
}
