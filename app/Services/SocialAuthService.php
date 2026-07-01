<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class SocialAuthService
{
    /**
     * @return array{url:string,state:string}
     */
    public function redirectUrl(string $provider): array
    {
        $provider = strtolower($provider);
        $state = Str::random(40);

        Session::put("social_oauth_state.{$provider}", $state);

        return [
            'url' => $this->buildAuthorizeUrl($provider, $state),
            'state' => $state,
        ];
    }

    /**
     * @return array{name:string,email:string,id:string,avatar_url:?string}
     */
    public function handleCallback(string $provider, Request $request): array
    {
        $provider = strtolower($provider);
        $expectedState = Session::get("social_oauth_state.{$provider}");
        $submittedState = (string) $request->input('state', '');

        if ($expectedState === null || ! hash_equals((string) $expectedState, $submittedState)) {
            abort(403, 'Invalid social login state.');
        }

        Session::forget("social_oauth_state.{$provider}");

        $code = (string) $request->input('code', '');

        if ($code === '') {
            abort(400, 'Social login failed: no code returned.');
        }

        $tokenResponse = $this->exchangeCode($provider, $code);
        $accessToken = (string) ($tokenResponse['access_token'] ?? '');

        if ($accessToken === '') {
            abort(400, 'Social login failed: no access token returned.');
        }

        return $this->fetchUserProfile($provider, $accessToken);
    }

    protected function buildAuthorizeUrl(string $provider, string $state): string
    {
        $config = $this->getProviderConfig($provider);

        return match ($provider) {
            'facebook' => $this->buildUrl('https://www.facebook.com/v19.0/dialog/oauth', [
                'client_id' => $config['client_id'] ?? '',
                'redirect_uri' => $config['redirect'] ?? '',
                'response_type' => 'code',
                'scope' => 'email,public_profile',
                'state' => $state,
            ]),
            'twitter' => $this->buildUrl('https://twitter.com/i/oauth2/authorize', [
                'response_type' => 'code',
                'client_id' => $config['client_id'] ?? '',
                'redirect_uri' => $config['redirect'] ?? '',
                'scope' => 'users.read tweet.read',
                'state' => $state,
            ]),
            'instagram' => $this->buildUrl('https://api.instagram.com/oauth/authorize', [
                'client_id' => $config['client_id'] ?? '',
                'redirect_uri' => $config['redirect'] ?? '',
                'response_type' => 'code',
                'scope' => 'user_profile',
                'state' => $state,
            ]),
            default => abort(404),
        };
    }

    protected function exchangeCode(string $provider, string $code): array
    {
        $config = $this->getProviderConfig($provider);

        return match ($provider) {
            'facebook' => Http::asForm()->post('https://graph.facebook.com/v19.0/oauth/access_token', [
                'client_id' => $config['client_id'] ?? '',
                'client_secret' => $config['client_secret'] ?? '',
                'redirect_uri' => $config['redirect'] ?? '',
                'code' => $code,
            ])->throw()->json(),
            'twitter' => Http::asForm()->withBasicAuth($config['client_id'] ?? '', $config['client_secret'] ?? '')
                ->post('https://api.twitter.com/2/oauth2/token', [
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'client_id' => $config['client_id'] ?? '',
                    'redirect_uri' => $config['redirect'] ?? '',
                    'code_verifier' => 'default',
                ])
                ->throw()
                ->json(),
            'instagram' => Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                'client_id' => $config['client_id'] ?? '',
                'client_secret' => $config['client_secret'] ?? '',
                'grant_type' => 'authorization_code',
                'redirect_uri' => $config['redirect'] ?? '',
                'code' => $code,
            ])->throw()->json(),
            default => abort(404),
        };
    }

    protected function getProviderConfig(string $provider): array
    {
        $settings = Schema::hasTable('site_settings') ? SiteSetting::query()->first() : null;
        $config = config("services.{$provider}", []);

        return match ($provider) {
            'facebook' => [
                'client_id' => $this->settingValue($settings, 'facebook_client_id', $config['client_id'] ?? ''),
                'client_secret' => $this->settingValue($settings, 'facebook_client_secret', $config['client_secret'] ?? ''),
                'redirect' => $this->settingValue($settings, 'facebook_redirect_uri', $config['redirect'] ?? ''),
            ],
            'twitter' => [
                'client_id' => $this->settingValue($settings, 'twitter_client_id', $config['client_id'] ?? ''),
                'client_secret' => $this->settingValue($settings, 'twitter_client_secret', $config['client_secret'] ?? ''),
                'redirect' => $this->settingValue($settings, 'twitter_redirect_uri', $config['redirect'] ?? ''),
            ],
            'instagram' => [
                'client_id' => $this->settingValue($settings, 'instagram_client_id', $config['client_id'] ?? ''),
                'client_secret' => $this->settingValue($settings, 'instagram_client_secret', $config['client_secret'] ?? ''),
                'redirect' => $this->settingValue($settings, 'instagram_redirect_uri', $config['redirect'] ?? ''),
            ],
            default => abort(404),
        };
    }

    protected function settingValue(?SiteSetting $settings, string $key, string $default = ''): string
    {
        if ($settings && ! blank($settings->{$key})) {
            return (string) $settings->{$key};
        }

        return $default;
    }

    protected function fetchUserProfile(string $provider, string $accessToken): array
    {
        return match ($provider) {
            'facebook' => $this->fetchFacebookUser($accessToken),
            'twitter' => $this->fetchTwitterUser($accessToken),
            'instagram' => $this->fetchInstagramUser($accessToken),
            default => abort(404),
        };
    }

    protected function fetchFacebookUser(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get('https://graph.facebook.com/v19.0/me', [
            'fields' => 'id,name,email,picture.type(large)',
        ])->throw()->json();

        return [
            'id' => (string) ($response['id'] ?? ''),
            'name' => (string) ($response['name'] ?? ''),
            'email' => (string) ($response['email'] ?? ''),
            'avatar_url' => (string) data_get($response, 'picture.data.url', ''),
        ];
    }

    protected function fetchTwitterUser(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get('https://api.twitter.com/2/users/me', [
            'user.fields' => 'profile_image_url,name,username',
        ])->throw()->json();

        $user = $response['data'] ?? [];

        return [
            'id' => (string) ($user['id'] ?? ''),
            'name' => (string) ($user['name'] ?? ''),
            'email' => '',
            'avatar_url' => (string) ($user['profile_image_url'] ?? ''),
        ];
    }

    protected function fetchInstagramUser(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get('https://graph.instagram.com/me', [
            'fields' => 'id,username,account_type',
        ])->throw()->json();

        return [
            'id' => (string) ($response['id'] ?? ''),
            'name' => (string) ($response['username'] ?? ''),
            'email' => '',
            'avatar_url' => '',
        ];
    }

    protected function buildUrl(string $baseUrl, array $query): string
    {
        return $baseUrl.'?'.http_build_query($query);
    }
}
