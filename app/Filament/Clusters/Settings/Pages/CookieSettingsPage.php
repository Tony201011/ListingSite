<?php

// namespace App\Filament\Clusters\Settings\Pages;

// use App\Models\SiteSetting;
// use Filament\Forms;
// use Filament\Pages\Page;
// use Filament\Forms\Components\Toggle;
// use Filament\Forms\Components\Textarea;

// class CookieSettingsPage extends Page
// {
//     protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
//     protected static ?string $navigationLabel = 'Cookie Settings';
//     protected static ?string $slug = 'cookie-settings';
//     protected static ?string $cluster = \App\Filament\Clusters\Settings::class;
//     protected static ?string $route = '/cookie-settings';

//     public ?bool $enable_cookies = true;
//     public ?string $cookies_text = '';

//     public function mount(): void
//     {
//         $setting = SiteSetting::first();
//         $this->enable_cookies = $setting?->enable_cookies ?? true;
//         $this->cookies_text = $setting?->cookies_text ?? 'We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.';
//     }

//     public function submit(): void
//     {
//         SiteSetting::updateOrCreate(
//             ['id' => 1],
//             [
//                 'enable_cookies' => $this->enable_cookies,
//                 'cookies_text' => $this->cookies_text,
//             ]
//         );
//         $this->notify('success', 'Cookie settings updated!');
//     }

//     protected function getFormSchema(): array
//     {
//         return [
//             Toggle::make('enable_cookies')
//                 ->label('Enable Cookie Consent Banner'),
//             Textarea::make('cookies_text')
//                 ->label('Cookie Consent Text')
//                 ->rows(4),
//         ];
//     }

//     protected function getActions(): array
//     {
//         return [
//             \Filament\Actions\Action::make('save')
//                 ->label('Save')
//                 ->action('submit'),
//         ];
//     }
// }
