<?php

namespace Ccast\TagixoFilament\Filament\Resources\Mails\Tables;

use Ccast\Tagixo\Facades\Tagixo;
use Ccast\Tagixo\Models\MailTemplate;
use Ccast\TagixoFilament\Filament\Actions\VisualBuilderAction;
use Ccast\TagixoFilament\Filament\Resources\Mails\MailResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
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
                VisualBuilderAction::make(MailResource::class),

                Action::make('sendTest')
                    ->label(__('Send test'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('gray')
                    ->form(function (): array {
                        $needsSmtp = ! Tagixo::mailIsConfigured();

                        return [
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

                            // Shown only when the app has no working mail transport:
                            // the sender supplies their own SMTP just to try delivery.
                            // These values are used for this one send and never stored.
                            Section::make(__('SMTP settings'))
                                ->description(__('Mail is not configured on this server. Enter your own SMTP to send the test — these details are used only for this send and are never saved.'))
                                ->icon('heroicon-o-lock-closed')
                                ->visible($needsSmtp)
                                ->columns(2)
                                ->schema([
                                    TextInput::make('smtp_host')
                                        ->label(__('SMTP host'))
                                        ->required($needsSmtp)
                                        ->placeholder('smtp.gmail.com'),

                                    TextInput::make('smtp_port')
                                        ->label(__('Port'))
                                        ->numeric()
                                        ->default(587)
                                        ->required($needsSmtp),

                                    Select::make('smtp_encryption')
                                        ->label(__('Encryption'))
                                        ->options([
                                            'tls' => 'TLS',
                                            'ssl' => 'SSL',
                                            'none' => __('None'),
                                        ])
                                        ->default('tls')
                                        ->selectablePlaceholder(false),

                                    TextInput::make('smtp_from_address')
                                        ->label(__('From address'))
                                        ->email()
                                        ->required($needsSmtp)
                                        ->placeholder('you@example.com'),

                                    TextInput::make('smtp_username')
                                        ->label(__('Username'))
                                        ->autocomplete('off'),

                                    TextInput::make('smtp_password')
                                        ->label(__('Password'))
                                        ->password()
                                        ->revealable()
                                        ->autocomplete('new-password'),

                                    TextInput::make('smtp_from_name')
                                        ->label(__('From name'))
                                        ->placeholder(config('app.name'))
                                        ->columnSpanFull(),
                                ]),
                        ];
                    })
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

                        // Transient SMTP (only when the app can't deliver): sent
                        // through once, never persisted. Null when mail works.
                        $smtp = Tagixo::mailIsConfigured() ? null : [
                            'host' => $data['smtp_host'] ?? null,
                            'port' => $data['smtp_port'] ?? 587,
                            'encryption' => $data['smtp_encryption'] ?? 'tls',
                            'username' => $data['smtp_username'] ?? null,
                            'password' => $data['smtp_password'] ?? null,
                            'from_address' => $data['smtp_from_address'] ?? null,
                            'from_name' => $data['smtp_from_name'] ?? null,
                        ];

                        try {
                            $pending = Tagixo::mail($record->slug)
                                ->to($data['recipient'])
                                ->subject($subject)
                                ->usingSmtp($smtp);

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
