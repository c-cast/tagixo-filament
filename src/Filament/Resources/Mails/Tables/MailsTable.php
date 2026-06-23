<?php

namespace Ccast\TagixoFilament\Filament\Resources\Mails\Tables;

use Ccast\Tagixo\Facades\Tagixo;
use Ccast\Tagixo\Models\MailTemplate;
use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Throwable;

class MailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable()
                    ->toggleable()
                    ->color('gray'),

                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->searchable()
                    ->placeholder(__('No subject'))
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof \BackedEnum ? $state->value : $state)
                    ->color(fn ($state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'archived' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'published' => __('Published'),
                        'scheduled' => __('Scheduled'),
                        'archived' => __('Archived'),
                    ]),
            ])
            ->actions([
                Action::make('visualBuilder')
                    ->label(__('Visual Builder'))
                    ->icon('heroicon-o-paint-brush')
                    ->color('primary')
                    ->url(fn (MailTemplate $record): string => MailResource::getUrl('build', ['record' => $record]))
                    ->tooltip(__('Open the visual mail builder')),

                Action::make('sendTest')
                    ->label(__('Send test'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->form([
                        TextInput::make('recipient')
                            ->label(__('Recipient'))
                            ->email()
                            ->required()
                            ->placeholder('you@example.com'),

                        Textarea::make('test_vars')
                            ->label(__('Test variables (JSON)'))
                            ->rows(4)
                            ->placeholder('{"name": "John"}')
                            ->helperText(__('Optional JSON object of variables interpolated into the template.')),
                    ])
                    ->action(function (MailTemplate $record, array $data): void {
                        $vars = [];
                        $raw = trim((string) ($data['test_vars'] ?? ''));

                        if ($raw !== '') {
                            $decoded = json_decode($raw, true);
                            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                                Notification::make()
                                    ->title(__('Invalid JSON in test variables'))
                                    ->body(json_last_error_msg())
                                    ->danger()
                                    ->send();

                                return;
                            }
                            $vars = $decoded;
                        }

                        $subject = $record->subject ?: '[TEST] '.$record->name;

                        try {
                            $pending = Tagixo::mail($record->slug)
                                ->to($data['recipient'])
                                ->subject($subject);

                            if (! empty($vars)) {
                                $pending->with($vars);
                            }

                            $pending->send();
                        } catch (Throwable $e) {
                            report($e);

                            Notification::make()
                                ->title(__('Send failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(__('Test email sent to :recipient', ['recipient' => $data['recipient']]))
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
