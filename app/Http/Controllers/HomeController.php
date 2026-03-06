<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $viewData = $this->buildFilterViewData($request);

        return view('home', $viewData);
    }

    public function advancedSearch(Request $request)
    {
        $viewData = $this->buildFilterViewData($request);

        return view('advanced-search', $viewData);
    }

    public function showProfile(Request $request, string $slug)
    {
        $imageCount = 8;
        $videoCount = 7;

        $galleryImages = [
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1504593811423-6dd665756598?w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1506863530036-1efeddceb993?w=900&auto=format&fit=crop',
        ];

        $galleryVideos = [
            'https://storage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'https://storage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
            'https://storage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
            'https://storage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
            'https://storage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
            'https://storage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
            'https://storage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
            'https://storage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
            'https://storage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackOnStreetAndDirt.mp4',
            'https://storage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
        ];

        $profiles = collect($this->baseProfiles())
            ->map(function (array $profile, int $index) use ($galleryImages, $galleryVideos, $imageCount, $videoCount) {
                $profile['slug'] = Str::slug($profile['name']) . '-' . ($index + 1);

                $profile['images'] = collect(range(0, max(0, $imageCount - 1)))
                    ->map(function (int $offset) use ($profile, $galleryImages, $index) {
                        if ($offset === 0) {
                            return $profile['image'];
                        }

                        return $galleryImages[($index + $offset) % count($galleryImages)];
                    })
                    ->values()
                    ->all();

                $profile['videos'] = collect(range(0, max(0, $videoCount - 1)))
                    ->map(fn (int $offset) => $galleryVideos[($index + $offset) % count($galleryVideos)])
                    ->values()
                    ->all();

                return $profile;
            });

        $profile = $profiles->firstWhere('slug', $slug);

        abort_if(!$profile, 404);

        $nearbyProfiles = $profiles
            ->where('slug', '!=', $slug)
            ->take(4)
            ->values();

        $selectedCategoryIds = collect($request->input('categories', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $selectedCategoryNames = $selectedCategoryIds->isNotEmpty()
            ? Category::query()
                ->whereIn('id', $selectedCategoryIds)
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->all()
            : [];

        $selectedCategoriesByGroup = [];

        if ($selectedCategoryIds->isNotEmpty()) {
            $selectedCategories = Category::query()
                ->whereIn('id', $selectedCategoryIds)
                ->get(['id', 'name', 'parent_id']);

            $parentNames = Category::query()
                ->whereIn('id', $selectedCategories->pluck('parent_id')->filter()->unique())
                ->pluck('name', 'id');

            $selectedCategoriesByGroup = $selectedCategories
                ->groupBy(fn ($category) => (int) ($category->parent_id ?? 0))
                ->map(function ($items, $parentId) use ($parentNames) {
                    $heading = (int) $parentId > 0
                        ? (string) ($parentNames->get((int) $parentId) ?? 'Other')
                        : 'Other';

                    return [
                        'heading' => $heading,
                        'items' => $items
                            ->pluck('name')
                            ->filter()
                            ->map(fn ($name) => trim((string) $name))
                            ->unique()
                            ->take(2)
                            ->values()
                            ->all(),
                    ];
                })
                ->filter(fn ($group) => !empty($group['items']))
                ->sortBy('heading')
                ->values()
                ->all();
        }

        return view('profile-show', [
            'profile' => $profile,
            'nearbyProfiles' => $nearbyProfiles,
            'selectedCategoryNames' => $selectedCategoryNames,
            'selectedCategoriesByGroup' => $selectedCategoriesByGroup,
        ]);
    }

    private function buildFilterViewData(Request $request): array
    {
        $filterSlugs = [
            'hair-color',
            'hair-length',
            'ethnicity',
            'body-type',
            'bust-size',
            'your-length',
            'primary-identity',
            'attributes',
            'services-style',
            'services-you-provide',
            'availability',
            'contact-method',
            'phone-contact-preferences',
            'time-waster-shield',
        ];

        $parents = Category::query()
            ->whereIn('slug', $filterSlugs)
            ->where('website_type', 'adult')
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);

        $childrenByParent = Category::query()
            ->whereIn('parent_id', $parents->pluck('id'))
            ->where('website_type', 'adult')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id'])
            ->groupBy('parent_id');

        $filterGroups = $parents
            ->sortBy(fn ($parent) => array_search($parent->slug, $filterSlugs, true))
            ->values()
            ->map(function ($parent) use ($childrenByParent) {
                return [
                    'slug' => $parent->slug,
                    'label' => $parent->name,
                    'options' => ($childrenByParent->get($parent->id) ?? collect())
                        ->map(fn ($child) => ['id' => $child->id, 'name' => $child->name])
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        $allFilterCategories = collect($filterGroups)
            ->flatMap(fn ($group) => $group['options'])
            ->values()
            ->all();

        $selectedCategoryIds = collect($request->input('categories', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $minAge = (int) $request->integer('min_age', 18);
        $maxAge = (int) $request->integer('max_age', 40);
        $minPrice = (int) $request->integer('min_price', 150);
        $maxPrice = (int) $request->integer('max_price', 400);

        if ($minAge > $maxAge) {
            [$minAge, $maxAge] = [$maxAge, $minAge];
        }

        if ($minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        return compact(
            'filterGroups',
            'allFilterCategories',
            'selectedCategoryIds',
            'minAge',
            'maxAge',
            'minPrice',
            'maxPrice'
        );
    }

    private function baseProfiles(): array
    {
        return [
            ['name' => 'Alina', 'age' => 24, 'rate' => '$250 / hour', 'city' => 'Houston', 'height' => "5'6\"", 'service_1' => 'Incall', 'service_2' => 'Outcall', 'date' => '27/05/2024', 'description' => 'Elegant companion with refined style, warm personality and premium experience for upscale dates.', 'active' => true, 'verified' => true, 'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=900&auto=format&fit=crop'],
            ['name' => 'Sofia', 'age' => 26, 'rate' => '$300 / hour', 'city' => 'Chicago', 'height' => "5'7\"", 'service_1' => 'Incall', 'service_2' => 'Travel', 'date' => '16/08/2024', 'description' => 'Luxury model known for classy company, confidence and unforgettable private moments.', 'active' => true, 'verified' => false, 'image' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=900&auto=format&fit=crop'],
            ['name' => 'Mia', 'age' => 22, 'rate' => '$220 / hour', 'city' => 'Boston', 'height' => "5'5\"", 'service_1' => 'Outcall', 'service_2' => 'Dinner Date', 'date' => '30/09/2024', 'description' => 'Friendly and playful vibe with great energy, ideal for fun social and intimate meetups.', 'active' => true, 'verified' => false, 'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=900&auto=format&fit=crop'],
            ['name' => 'Valentina', 'age' => 25, 'rate' => '$280 / hour', 'city' => 'New York', 'height' => "5'8\"", 'service_1' => 'Incall', 'service_2' => 'Overnight', 'date' => '15/07/2024', 'description' => 'Sophisticated beauty offering premium companionship with attention to every detail.', 'active' => true, 'verified' => true, 'image' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=900&auto=format&fit=crop'],
            ['name' => 'Luna', 'age' => 23, 'rate' => '$200 / hour', 'city' => 'Dallas', 'height' => "5'4\"", 'service_1' => 'Outcall', 'service_2' => 'Massage', 'date' => '23/04/2024', 'description' => 'Relaxed and charming personality, great choice for smooth and discreet companionship.', 'active' => true, 'verified' => false, 'image' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=900&auto=format&fit=crop'],
            ['name' => 'Nora', 'age' => 27, 'rate' => '$340 / hour', 'city' => 'Los Angeles', 'height' => "5'9\"", 'service_1' => 'Travel', 'service_2' => 'VIP Date', 'date' => '28/06/2024', 'description' => 'High-end escort with elite presentation and polished etiquette for premium events.', 'active' => true, 'verified' => true, 'image' => 'https://images.unsplash.com/photo-1504593811423-6dd665756598?w=900&auto=format&fit=crop'],
            ['name' => 'Ivy', 'age' => 21, 'rate' => '$180 / hour', 'city' => 'San Jose', 'height' => "5'3\"", 'service_1' => 'Incall', 'service_2' => 'Outcall', 'date' => '19/10/2024', 'description' => 'Young, vibrant and engaging companion with a fun and positive atmosphere.', 'active' => true, 'verified' => false, 'image' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=900&auto=format&fit=crop'],
            ['name' => 'Camila', 'age' => 24, 'rate' => '$260 / hour', 'city' => 'Phoenix', 'height' => "5'6\"", 'service_1' => 'Dinner Date', 'service_2' => 'Overnight', 'date' => '02/06/2024', 'description' => 'Stylish and romantic companion, perfect for private dinners and memorable nights.', 'active' => true, 'verified' => true, 'image' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=900&auto=format&fit=crop'],
            ['name' => 'Elena', 'age' => 25, 'rate' => '$295 / hour', 'city' => 'Philadelphia', 'height' => "5'7\"", 'service_1' => 'Incall', 'service_2' => 'Travel', 'date' => '13/07/2024', 'description' => 'Graceful and discreet companion with premium service and elegant communication.', 'active' => true, 'verified' => false, 'image' => 'https://images.unsplash.com/photo-1506863530036-1efeddceb993?w=900&auto=format&fit=crop'],
        ];
    }
}
