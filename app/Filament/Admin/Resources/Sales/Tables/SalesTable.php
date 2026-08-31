<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Sales\Tables;

use App\Actions\DeleteSale;
use App\Models\Sale;
use App\Models\User;
use App\Support\Money;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sold_at', 'desc')
            ->columns([
                TextColumn::make('sold_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('serviceType.name')
                    ->label('Servicio')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn (string $state): string => Money::formatUsd($state))
                    ->sortable(),
                IconColumn::make('is_recurring')
                    ->label('Recurrente')
                    ->boolean(),
                TextColumn::make('next_charge_at')
                    ->label('Próximo cobro')
                    ->date('d/m/Y'),
                TextColumn::make('evidence_path')
                    ->label('Evidencia')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Ver' : '—')
                    ->url(fn (Sale $record): ?string => $record->hasEvidence()
                        ? route('evidence.sales.show', $record)
                        : null)
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->using(function (Sale $record): bool {
                        /** @var User $user */
                        $user = auth()->user();
                        abort_unless($user->can('delete', $record), 403);
                        app(DeleteSale::class)->execute($user, $record);

                        return true;
                    }),
            ])
            ->toolbarActions([]);
    }
}
