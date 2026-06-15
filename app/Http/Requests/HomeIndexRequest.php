<?php

namespace App\Http\Requests;

use App\Services\LocationSlugService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HomeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /** @var LocationSlugService $locationSlugService */
        $locationSlugService = app(LocationSlugService::class);

        $routeSearchName = trim((string) $this->route('search_name', ''));
        $seoSearchName = $routeSearchName !== ''
            ? str_replace('-', ' ', urldecode($routeSearchName))
            : '';
        $escortName = trim((string) $this->input('escort_name', ''));
        $routeGirls = trim((string) $this->route('type', ''));
        $requestGirls = trim((string) $this->input('girls', ''));
        $girlsMode = $requestGirls !== '' ? $requestGirls : ($routeGirls !== '' ? $routeGirls : 'all');

        $routeLocationData = null;
        $directRouteLocation = trim((string) $this->route('location', ''));
        if ($directRouteLocation !== '') {
            $routeLocationData = $locationSlugService->fromLocationText(urldecode($directRouteLocation));
        }

        $routeLocationSlug = trim((string) $this->route('location_slug', ''));
        if ($routeLocationSlug !== '') {
            $routeLocationData = $locationSlugService->parseSlug($routeLocationSlug);
        }

        $legacyRouteLocationSlug = trim((string) $this->route('legacy_location_slug', ''));
        if ($routeLocationData === null && $legacyRouteLocationSlug !== '') {
            $routeLocationData = $locationSlugService->parseSlug($legacyRouteLocationSlug);
        }

        $routeSuburb = trim((string) $this->route('suburb', ''));
        $routeState = trim((string) $this->route('state', ''));
        if ($routeLocationData === null && $routeSuburb !== '') {
            $routeLocationData = $locationSlugService->fromSuburbAndState(
                urldecode($routeSuburb),
                $routeState !== '' ? urldecode($routeState) : null
            );
        }

        $locationFromRoute = (string) ($routeLocationData['location'] ?? '');
        $location = $locationFromRoute !== '' ? $locationFromRoute : trim((string) $this->input('location', ''));
        $locationState = trim((string) $this->input('location_state', ''));
        if ($routeLocationData !== null && ! empty($routeLocationData['state'])) {
            $locationState = (string) $routeLocationData['state'];
        }

        $this->merge([
            'categories' => is_array($this->input('categories')) ? $this->input('categories') : [],
            'min_age' => $this->input('min_age', 18),
            'max_age' => $this->input('max_age', 40),
            'min_price' => $this->input('min_price', 150),
            'max_price' => $this->input('max_price', 400),
            'location' => $location,
            'location_state' => $locationState,
            'location_slug' => $routeLocationData['slug'] ?? null,
            'location_from_route' => $routeLocationData !== null,
            'escort_name' => $escortName !== '' ? $escortName : $seoSearchName,
            'user_lat' => $this->input('user_lat'),
            'user_lng' => $this->input('user_lng'),
            'distance' => $this->input('distance'),
            'girls' => $girlsMode,
            'per_page' => $this->input('per_page'),
        ]);
    }

    public function rules(): array
    {
        return [
            'categories' => ['nullable', 'array'],
            'categories.*' => [
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'min_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'max_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'location' => ['nullable', 'string', 'max:255'],
            'location_state' => ['nullable', 'string', 'max:255'],
            'location_slug' => ['nullable', 'string', 'max:255'],
            'location_from_route' => ['nullable', 'boolean'],
            'escort_name' => ['nullable', 'string', 'max:255'],
            'user_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'user_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'distance' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'girls' => ['nullable', Rule::in(['all', 'new', 'popular'])],
            'per_page' => ['nullable', 'integer', Rule::in([12, 24, 48])],
        ];
    }

    public function messages(): array
    {
        return [
            'categories.array' => 'Categories must be a valid array.',
            'categories.*.integer' => 'Each category must be a valid ID.',
            'categories.*.exists' => 'One or more selected categories are invalid.',
            'min_age.integer' => 'Minimum age must be a valid number.',
            'max_age.integer' => 'Maximum age must be a valid number.',
            'min_price.integer' => 'Minimum price must be a valid number.',
            'max_price.integer' => 'Maximum price must be a valid number.',
        ];
    }
}
