<?php

namespace App\Actions;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetProfileShowData
{
    public function execute(string $slug, array $validated): array
    {
        $profiles = $this->buildProfiles();

        $profileIndex = $profiles->search(fn ($profile) => $profile['slug'] === $slug);
        abort_if($profileIndex === false, 404);

        $profile = $profiles[$profileIndex];

        $prevIndex = $profileIndex === 0 ? $profiles->count() - 1 : $profileIndex - 1;
        $nextIndex = $profileIndex === $profiles->count() - 1 ? 0 : $profileIndex + 1;

        $prevProfile = [
            'slug' => $profiles[$prevIndex]['slug'],
            'name' => $profiles[$prevIndex]['name'],
        ];

        $nextProfile = [
            'slug' => $profiles[$nextIndex]['slug'],
            'name' => $profiles[$nextIndex]['name'],
        ];

        $nearbyProfiles = $profiles
            ->where('slug', '!=', $slug)
            ->take(4)
            ->values();

        $selectedCategoryIds = collect($validated['categories'] ?? [])
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
                ->filter(fn ($group) => ! empty($group['items']))
                ->sortBy('heading')
                ->values()
                ->all();
        }

        $profileStats = [
            ['label' => 'Age', 'value' => $profile['age'] ?? '25'],
            ['label' => 'Height', 'value' => $profile['height'] ?? '170cm'],
            ['label' => 'Weight', 'value' => $profile['weight'] ?? '60kg'],
            ['label' => 'Bust', 'value' => $profile['bust'] ?? 'C'],
            ['label' => 'Waist', 'value' => $profile['waist'] ?? '60cm'],
            ['label' => 'Hips', 'value' => $profile['hips'] ?? '90cm'],
            ['label' => 'Dress size', 'value' => $profile['dress_size'] ?? '8'],
            ['label' => 'Eye color', 'value' => $profile['eye_color'] ?? 'Brown'],
            ['label' => 'Orientation', 'value' => $profile['orientation'] ?? 'Straight'],
            ['label' => 'Available to', 'value' => $profile['available_to'] ?? 'Men, Women, Couples'],
        ];

        return [
            'profile' => $profile,
            'nearbyProfiles' => $nearbyProfiles,
            'selectedCategoryNames' => $selectedCategoryNames,
            'selectedCategoriesByGroup' => $selectedCategoriesByGroup,
            'profileStats' => $profileStats,
            'prevProfile' => $prevProfile,
            'nextProfile' => $nextProfile,
        ];
    }

    protected function buildProfiles(): Collection
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

        return collect($this->baseProfiles())
            ->map(function (array $profile, int $index) use ($galleryImages, $galleryVideos, $imageCount, $videoCount) {
                $profile['slug'] = Str::slug($profile['name']).'-'.($index + 1);

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

                $profile['phone'] = $profile['phone'] ?? sprintf('+61 4%08d', 10000000 + $index);
                $profile['whatsapp'] = $profile['whatsapp'] ?? $profile['phone'];
                $profile['price_list'] = $profile['price_list'] ?? [
                    ['label' => '30 Minutes', 'price' => '$180'],
                    ['label' => '1 Hour', 'price' => $profile['rate']],
                    ['label' => '2 Hours', 'price' => '$500'],
                    ['label' => 'Overnight', 'price' => 'By arrangement'],
                ];
                $profile['availability_list'] = $profile['availability_list'] ?? [
                    ['day' => 'Monday', 'time' => '10:00 AM - 10:00 PM'],
                    ['day' => 'Tuesday', 'time' => '10:00 AM - 10:00 PM'],
                    ['day' => 'Wednesday', 'time' => '10:00 AM - 10:00 PM'],
                    ['day' => 'Thursday', 'time' => '10:00 AM - 11:00 PM'],
                    ['day' => 'Friday', 'time' => '10:00 AM - Late'],
                    ['day' => 'Saturday', 'time' => '11:00 AM - Late'],
                    ['day' => 'Sunday', 'time' => 'By appointment'],
                ];

                return $profile;
            });
    }

    protected function baseProfiles(): array
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
