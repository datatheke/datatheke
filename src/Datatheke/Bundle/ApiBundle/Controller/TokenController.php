<?php

namespace Datatheke\Bundle\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;

use FOS\RestBundle\Controller\FOSRestController;

class TokenController extends FOSRestController
{
    /**
     * @Method({"POST"})
     *
     * @Route("/v1/token.{_format}", requirements={"_format"="json|xml"}, defaults={"_format"="json"})
     */
    public function tokenV1Action()
    {
        $token = $this->get('fos_oauth_server.server')->createAccessToken($this->getClient(), $this->getUser());
        $token['token'] = $token['access_token'];

        return $this->handleView($this->view($token, 200));
    }

    /**
     * @Method({"POST"})
     *
     * @Route("/v2/token.{_format}", requirements={"_format"="json|xml"}, defaults={"_format"="json"})
     */
    public function tokenV2(Request $request)
    {
        try {
            $token = $this->grantAccessToken($request);
        } catch (OAuth2ServerException $e) {
            return $this->handleView($this->view(array('error' => $e->getMessage(), 'error_description' => $e->getDescription()), $e->getHttpCode()));
        }

        return $this->handleView($this->view($token, 200));
    }

    /**
     * Adapted from OAuth2\OAuth2
     */
    protected function grantAccessToken(Request $request = null)
    {
        $filters = array(
            "grant_type" => array("filter" => FILTER_VALIDATE_REGEXP, "options" => array("regexp" => OAuth2::GRANT_TYPE_REGEXP), "flags" => FILTER_REQUIRE_SCALAR),
            "username" => array("flags" => FILTER_REQUIRE_SCALAR),
            "password" => array("flags" => FILTER_REQUIRE_SCALAR),
            "refresh_token" => array("flags" => FILTER_REQUIRE_SCALAR),
        );

        $input = filter_var_array($request->request->all(), $filters);

        switch ($input['grant_type']) {
            case 'password':
                $user = $this->grantAccessTokenUserCredentials($input['username'], $input['password']);
                break;

            case 'refresh_token':
                $user = $this->grantAccessTokenRefreshToken($input['refresh_token']);
                break;

            default:
                throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'Invalid grant_type parameter or parameter missing');
        }

        return $this->get('fos_oauth_server.server')->createAccessToken($this->getClient(), $user);
    }

    /**
     * Adapted from OAuth2\OAuth2
     */
    protected function grantAccessTokenUserCredentials($username, $password)
    {
        if (!$username || !$password) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'Missing parameters. "username" and "password" required');
        }

        $stored = $this->get('fos_oauth_server.storage')->checkUserCredentials($this->getClient(), $username, $password);
        if (false === $stored) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT);
        }

        return $stored['data'];
    }

    /**
     * Adapted from OAuth2\OAuth2
     */
    protected function grantAccessTokenRefreshToken($refreshToken)
    {
        $token = $this->get('fos_oauth_server.storage')->getRefreshToken($refreshToken);
        $client = $this->getClient();

        if (!$refreshToken) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'No "refresh_token" parameter found');
        }

        if (null === $token || $client->getPublicId() !== $token->getClientId()) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Invalid refresh token');
        }

        if ($token->hasExpired()) {
            throw new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_GRANT, 'Refresh token has expired');
        }

        return $token->getData();
    }

    protected function getClient()
    {
        $clientManager = $this->get('fos_oauth_server.client_manager.default');

        if (null === ($client = $clientManager->findClientBy(array('name' => 'datatheke_default')))) {
            $client = $clientManager->createClient();
            $client->setName('datatheke_default');
            $client->setAllowedGrantTypes(array('password', 'refresh_token'));
            $clientManager->updateClient($client);
        }

        return $client;
    }
}
