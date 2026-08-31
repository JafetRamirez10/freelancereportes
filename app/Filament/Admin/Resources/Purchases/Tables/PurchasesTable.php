<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Purchases\Tables;

use App\Actions\DeletePurchase;
use App\Models\Purchase;
use App\Models\User;
use App\Support\Money;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('purchased_at', 'desc')
            ->columns([
                TextColumn::make('purchased_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Precio')
                    ->formatStateUsing(fn (string $state): string => Money::formatUsd($state))
                    ->sortable(),
                TextColumn::make('evidence_path')
                    ->label('Evidencia')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Ver' : '—')
                    ->url(fn (Purchase $record): ?string => $record->hasEvidence()
                        ? route('evidence.purchases.show', $record)
                        : null)
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->using(function (Purchase $record): bool {
                        /** @var User $user */
                        $user = auth()->user();
                        abort_unless($user->can('delete', $record), 403);
                        app(DeletePurchase::class)->execute($user, $record);

                        return true;
                    }),
            ])
            ->toolbarActions([]);
    }
}
