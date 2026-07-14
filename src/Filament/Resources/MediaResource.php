<?php

namespace Ccast\TagixoFilament\Filament\Resources;

use Ccast\Tagixo\MediaGallery\Models\Media;
use Ccast\Tagixo\MediaGallery\Services\MediaService;
use Ccast\TagixoFilament\Filament\Resources\Media\Pages\ListMedia;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string | null | \BackedEnum $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): string | \UnitEnum | null
    {
        return __('Content');
    }

    public static function getNavigationLabel(): string
    {
        return __('Media Gallery');
    }

    public static function getModelLabel(): string
    {
        return __('Media');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Media');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Media Details'))
                ->columnSpanFull()
                ->schema(static::mediaDetailsComponents()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Media::query()->originals()->latest())
            ->contentGrid(['sm' => 2, 'md' => 3, 'xl' => 4])
            ->columns([
                Stack::make([
                    ImageColumn::make('path')
                        ->label(__('Preview'))
                        ->disk(fn (Media $record) => $record->disk)
                        ->height('160px')
                        ->width('100%')
                        ->extraImgAttributes(['class' => 'object-cover w-full rounded-t-lg']),

                    Stack::make([
                        TextColumn::make('filename')
                            ->label(__('Filename'))
                            ->searchable()
                            ->sortable()
                            ->limit(24)
                            ->weight(\Filament\Support\Enums\FontWeight::Medium)
                            ->tooltip(fn (Media $record) => $record->original_filename),

                        TextColumn::make('type')
                            ->label(__('Type'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'image' => 'success',
                                'video' => 'info',
                                'document' => 'warning',
                                default => 'gray',
                            }),

                        TextColumn::make('formatted_size')
                            ->label(__('Size'))
                            ->color('gray'),

                        TextColumn::make('folder')
                            ->label(__('Folder'))
                            ->placeholder('—')
                            ->color('gray'),
                    ])->space(1)->extraAttributes(['class' => 'p-3']),
                ]),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'image' => __('Images'),
                        'video' => __('Videos'),
                        'document' => __('Documents'),
                        'other' => __('Other'),
                    ]),

                SelectFilter::make('folder')
                    ->label(__('Folder'))
                    ->options(function () {
                        return Media::query()
                            ->originals()
                            ->select('folder')
                            ->whereNotNull('folder')
                            ->distinct()
                            ->pluck('folder', 'folder')
                            ->toArray();
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn (Media $record) => $record->title ?: $record->filename)
                    ->modalContent(fn (Media $record) => view('media-gallery::components.media-preview', ['media' => $record])),

                EditAction::make()
                    ->schema([
                        Section::make(__('Media Details'))
                            ->columnSpanFull()
                            ->schema(static::mediaDetailsComponents()),
                    ])
                    ->mutateDataUsing(function (array $data): array {
                        return [
                            'title' => $data['title'] ?? null,
                            'alt_text' => $data['alt_text'] ?? null,
                            'description' => $data['description'] ?? null,
                            'folder' => $data['folder'] ?? null,
                        ];
                    }),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Media deleted'))
                            ->body(__('The media item has been deleted successfully.'))
                    ),
            ])
            ->headerActions([
                Action::make('upload')
                    ->label(__('Upload Media'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->schema([
                        FileUpload::make('files')
                            ->label(__('Files'))
                            ->multiple()
                            ->maxFiles(10)
                            ->disk(config('tagixo.media_gallery.disk', 'public'))
                            ->directory(config('tagixo.media_gallery.storage_path', 'media'))
                            ->acceptedFileTypes(config('tagixo.media_gallery.allowed_types', []))
                            ->maxSize(config('tagixo.media_gallery.max_file_size', 10240))
                            ->required()
                            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, Get $get) {
                                $mediaService = app(MediaService::class);

                                $mediaService->upload(
                                    file: $file,
                                    disk: config('tagixo.media_gallery.disk', 'public'),
                                    folder: $get('folder') ?? null,
                                );
                            }),

                        TextInput::make('folder')
                            ->label(__('Folder'))
                            ->placeholder(__('Optional')),
                    ])
                    ->modalWidth('lg'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title(__('Media deleted'))
                                ->body(__('Selected media items have been deleted successfully.'))
                        ),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
        ];
    }

    protected static function mediaDetailsComponents(): array
    {
        return [
            Grid::make(2)
                ->schema([
                    TextInput::make('title')
                        ->label(__('Title'))
                        ->maxLength(255),

                    TextInput::make('alt_text')
                        ->label(__('Alt Text'))
                        ->maxLength(255)
                        ->helperText(__('Important for accessibility and SEO')),

                    Select::make('folder')
                        ->label(__('Folder'))
                        ->options(function () {
                            return Media::query()
                                ->originals()
                                ->select('folder')
                                ->whereNotNull('folder')
                                ->distinct()
                                ->pluck('folder', 'folder')
                                ->toArray();
                        })
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('folder')
                                ->label(__('New Folder Name'))
                                ->required(),
                        ])
                        ->createOptionUsing(fn (string $name) => $name),

                    Textarea::make('description')
                        ->label(__('Description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }
}
