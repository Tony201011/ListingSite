<?php

// namespace App\Filament\Pages;

// use Filament\Pages\Page;
// use App\Models\SiteSetting;
// use Illuminate\Support\Facades\DB;

// class SiteSettingsPage extends Page
// {
//     protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog';
//     protected static ?string $navigationLabel = 'Site Settings';
//     protected static ?string $slug = 'site-settings';

//     public ?string $meta_key = '';
//     public ?string $meta_description = '';

//     public function mount(): void
//     {
//         $setting = \App\Models\SiteSetting::first();
//         $this->meta_key = $setting?->meta_key ?? '';
//         $this->meta_description = $setting?->meta_description ?? '';
//     }

//     public function submit(): void
//     {
//         \App\Models\SiteSetting::updateOrCreate(
//             ['id' => 1],
//             [
//                 'meta_key' => $this->meta_key,
//                 'meta_description' => $this->meta_description,
//             ]
//         );
//         $this->notify('success', 'Meta settings updated!');
//     }

//     protected function getFormSchema(): array
//     {
//         return [
//             \Filament\Forms\Components\TextInput::make('meta_key')
//                 ->label('Meta Keyword'),
//             \Filament\Forms\Components\Textarea::make('meta_description')
//                 ->label('Meta Description'),
//         ];
//     }

//     // protected function getActions(): array
//     // {
//     //     return [
//     //             Action::make('save')
//     //             ->label('Save')
//     //             ->action('submit'),
//     //     ];
//     // }
// }
