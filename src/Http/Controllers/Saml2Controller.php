<?php

namespace Slides\Saml2\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OneLogin\Saml2\Error as OneLoginError;
use Slides\Saml2\Auth;
use Slides\Saml2\Events\SignedIn;

/**
 * Class Saml2Controller
 */
class Saml2Controller extends Controller
{
    /**
     * Render the metadata.
     *
     * @return \Illuminate\Support\Facades\Response
     *
     * @throws OneLoginError
     */
    public function metadata(Auth $auth)
    {
        $metadata = $auth->getMetadata();

        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Process the SAML Response sent by the IdP.
     *
     * Fires "SignedIn" event if a valid user is found.
     *
     * @return \Illuminate\Support\Facades\Redirect
     *
     * @throws OneLoginError
     * @throws \OneLogin\Saml2\ValidationError
     */
    public function acs(Auth $auth)
    {
        $errors = $auth->acs();
        if (!empty($errors)) {
            $error = $auth->getLastErrorReason();
            $uuid = $auth->getTenant()->uuid;

            logger()->error('saml2.error_detail', compact('uuid', 'error'));
            session()->flash('saml2.error_detail', [$error]);

            logger()->error('saml2.error', $errors);
            session()->flash('saml2.error', $errors);

            return $this->redirectToConfiguredUrl(
                config('saml2.errorRoute'),
                config('saml2.loginRoute'),
            );
        }

        $user = $auth->getSaml2User();

        event(new SignedIn($user, $auth));

        $redirectUrl = $user->getIntendedUrl();

        if ($redirectUrl) {
            return $this->redirectToConfiguredUrl($redirectUrl);
        }

        return $this->redirectToConfiguredUrl(
            $auth->getTenant()->relay_state_url ?: config('saml2.loginRoute'),
        );
    }

    /**
     * Process the SAML Logout Response / Logout Request sent by the IdP.
     *
     * Fires 'saml2.logoutRequestReceived' event if its valid.
     *
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     *
     * @return \Illuminate\Support\Facades\Redirect
     *
     * @throws OneLoginError
     * @throws \Exception
     */
    public function sls(Auth $auth)
    {
        $errors = $auth->sls(config('saml2.retrieveParametersFromServer'));

        if (!empty($errors)) {
            $error = $auth->getLastErrorReason();
            $uuid = $auth->getTenant()->uuid;

            logger()->error('saml2.error_detail', compact('uuid', 'error'));
            session()->flash('saml2.error_detail', [$error]);

            logger()->error('saml2.error', $errors);
            session()->flash('saml2.error', $errors);

            return $this->redirectToConfiguredUrl(
                config('saml2.errorRoute'),
                config('saml2.logoutRoute'),
            );
        }

        return $this->redirectToConfiguredUrl(config('saml2.logoutRoute')); // may be set a configurable default
    }

    /**
     * Build a redirect response that never returns Redirector instance.
     *
     * @param string|null $url
     * @param string|null $fallback
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToConfiguredUrl($url = null, $fallback = null)
    {
        return redirect()->to($url ?: $fallback ?: '/');
    }

    /**
     * Initiate a login request.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return void
     *
     * @throws OneLoginError
     */
    public function login(Request $request, Auth $auth)
    {
        $redirectUrl = $auth->getTenant()->relay_state_url ?: config('saml2.loginRoute');

        $auth->login($request->query('returnTo', $redirectUrl));
    }

    /**
     * Initiate a logout request.
     *
     * @param Illuminate\Http\Request $request
     *
     * @return void
     *
     * @throws OneLoginError
     */
    public function logout(Request $request, Auth $auth)
    {
        $auth->logout(
            $request->query('returnTo'),
            $request->query('nameId'),
            $request->query('sessionIndex'),
        );
    }
}
