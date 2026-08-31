<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Purchase;
use App\Models\Sale;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class DashboardMetrics
{
    public const DAILY_SERIES_MAX_DAYS = 92;

    public function __construct(
        private CarbonImmutable $from,
        private CarbonImmutable $to,
    ) {
        if ($this->from->gt($this->to)) {
            [$this->from, $this->to] = [$this->to, $this->from];
        }

        $this->from = $this->from->startOfDay();
        $this->to = $this->to->startOfDay();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public static function defaultRange(): array
    {
        $now = CarbonImmutable::now();

        return [$now->startOfMonth(), $now->endOfMonth()->startOfDay()];
    }

    public static function flush(): void
    {
        Cache::put('dash:ver', (string) now()->timestamp);
    }

    /**
     * @return array{
     *     income: string,
     *     expense: string,
     *     net: string,
     *     series: array{labels: list<string>, income: list<string>, expense: list<string>}
     * }
     */
    public function all(): array
    {
        $key = sprintf(
            'dash:%s:%s:%s',
            $this->from->toDateString(),
            $this->to->toDateString(),
            (string) Cache::get('dash:ver', '0'),
        );

        if (app()->environment('testing')) {
            return $this->compute();
        }

        $ttl = (int) config('freelancer.dashboard.cache_seconds', 300);

        try {
            $cached = Cache::get($key);
            if ($this->isFreshPayload($cached)) {
                return $cached;
            }

            $fresh = $this->compute();
            Cache::put($key, $fresh, $ttl);

            return $fresh;
        } catch (\Throwable) {
            return $this->compute();
        }
    }

    private function isFreshPayload(mixed $cached): bool
    {
        return is_array($cached)
            && ! array_key_exists('recent', $cached)
            && isset($cached['income'], $cached['expense'], $cached['net'], $cached['series'])
            && is_array($cached['series']);
    }

    /**
     * @return array{
     *     income: string,
     *     expense: string,
     *     net: string,
     *     series: array{labels: list<string>, income: list<string>, expense: list<string>}
     * }
     */
    private function compute(): array
    {
        $from = $this->from->toDateString();
        $to = $this->to->toDateString();

        $income = number_format((float) Sale::query()
            ->whereBetween('sold_at', [$from, $to])
            ->sum('amount'), 2, '.', '');
        $expense = number_format((float) Purchase::query()
            ->whereBetween('purchased_at', [$from, $to])
            ->sum('amount'), 2, '.', '');
        $net = number_format((float) $income - (float) $expense, 2, '.', '');

        $days = $this->from->diffInDays($this->to) + 1;
        $series = $days > self::DAILY_SERIES_MAX_DAYS
            ? $this->monthlySeries()
            : $this->dailySeries();

        return [
            'income' => $income,
            'expense' => $expense,
            'net' => $net,
            'series' => $series,
        ];
    }

    /**
     * @return array{labels: list<string>, income: list<string>, expense: list<string>}
     */
    private function dailySeries(): array
    {
        $from = $this->from->toDateString();
        $to = $this->to->toDateString();
        $daySql = $this->dateSql('sold_at');
        $purchaseDaySql = $this->dateSql('purchased_at');

        $income = Sale::query()
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw($daySql.' as day')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupByRaw($daySql)
            ->pluck('total', 'day');

        $expense = Purchase::query()
            ->whereBetween('purchased_at', [$from, $to])
            ->selectRaw($purchaseDaySql.' as day')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupByRaw($purchaseDaySql)
            ->pluck('total', 'day');

        $labels = [];
        $incomeSeries = [];
        $expenseSeries = [];

        for ($date = $this->from; $date->lte($this->to); $date = $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $incomeSeries[] = number_format((float) ($income[$key] ?? $income[$key.' 00:00:00'] ?? 0), 2, '.', '');
            $expenseSeries[] = number_format((float) ($expense[$key] ?? $expense[$key.' 00:00:00'] ?? 0), 2, '.', '');
        }

        return [
            'labels' => $labels,
            'income' => $incomeSeries,
            'expense' => $expenseSeries,
        ];
    }

    /**
     * @return array{labels: list<string>, income: list<string>, expense: list<string>}
     */
    private function monthlySeries(): array
    {
        $from = $this->from->toDateString();
        $to = $this->to->toDateString();
        $incomeSql = $this->yearMonthSql('sold_at');
        $expenseSql = $this->yearMonthSql('purchased_at');

        $income = Sale::query()
            ->whereBetween('sold_at', [$from, $to])
            ->selectRaw($incomeSql.' as bucket')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupByRaw($incomeSql)
            ->pluck('total', 'bucket');

        $expense = Purchase::query()
            ->whereBetween('purchased_at', [$from, $to])
            ->selectRaw($expenseSql.' as bucket')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupByRaw($expenseSql)
            ->pluck('total', 'bucket');

        $labels = [];
        $incomeSeries = [];
        $expenseSeries = [];
        $cursor = $this->from->startOfMonth();
        $end = $this->to->startOfMonth();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->format('m/Y');
            $incomeSeries[] = number_format((float) ($income[$key] ?? 0), 2, '.', '');
            $expenseSeries[] = number_format((float) ($expense[$key] ?? 0), 2, '.', '');
            $cursor = $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'income' => $incomeSeries,
            'expense' => $expenseSeries,
        ];
    }

    private function dateSql(string $column): string
    {
        if (! in_array($column, ['sold_at', 'purchased_at'], true)) {
            throw new \InvalidArgumentException('Columna inválida.');
        }

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "date({$column})",
            default => "DATE({$column})",
        };
    }

    private function yearMonthSql(string $column): string
    {
        if (! in_array($column, ['sold_at', 'purchased_at'], true)) {
            throw new \InvalidArgumentException('Columna inválida.');
        }

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }
}
