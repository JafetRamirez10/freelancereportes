# Laravel + Filament (freelancer)

Panel de ingresos y gastos para un freelancer. UI en español. Código en inglés.

## Stack

PHP 8.3+, Laravel 13, Filament 4, MySQL, Pest, Pint.

## Arranque

1. Copiar `.env.example` a `.env`, generar `APP_KEY`, configurar MySQL (`DB_*`).
2. Definir `SEED_ADMIN_EMAIL` y `SEED_ADMIN_PASSWORD` (mín. 12 caracteres, mayúsculas, minúsculas y números).
3. `php artisan migrate --seed`
4. Panel: `/admin`. Programar `php artisan schedule:run` cada minuto (el aviso de cobro corre a diario).
5. Tests Feature: extensión PHP `pdo_sqlite` (`php artisan test`).

## Dominio

- Servicios (catálogo): Hosting, Software, Diseño web.
- Ventas con cliente, fecha (puede ser pasada), monto USD, evidencia opcional, recurrencia mensual/anual.
- Compras = gastos del freelancer.
- Dashboard: ingresos, gastos, ganancia neta. Rango por defecto = mes actual.
- Recurrencia: solo avisos por correo al admin (30 y 7 días antes). El cobro se registra como nueva venta.

## Convenciones

- Actions para mutaciones (`StoreSale`, `SendChargeReminder`). Services para métricas (`DashboardMetrics`).
- Policies en cada modelo. `abort_unless(can())` en Actions, no solo en la UI.
- Dinero: `DECIMAL(15,2)`, nunca float. Totales desde SQL.
- Uploads de evidencia en disco privado. `evidence_path` solo lo escribe el Action.
- Credenciales semilla en `.env` (`SEED_ADMIN_*`), nunca en git.
- Pint PSR-12. `declare(strict_types=1)`. `final class` si no se extiende.
