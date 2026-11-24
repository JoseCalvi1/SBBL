<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpgradePaymentsTreasuryRafflesAndShop extends Migration
{
    public function up()
    {
        // 1. Tabla de Rifas / Campañas de Tickets
        if (!Schema::hasTable('raffles')) {
            Schema::create('raffles', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->text('prizes_info')->nullable();
                $table->longText('banner_b64')->nullable();
                $table->decimal('ticket_price', 8, 2);
                $table->unsignedInteger('max_tickets')->nullable();
                $table->unsignedInteger('max_per_user')->default(10);
                $table->string('ticket_prefix', 20)->default('RIFA');
                $table->unsignedTinyInteger('winners_count')->default(3);
                $table->boolean('is_active')->default(false)->index();
                $table->dateTime('ends_at')->nullable();
                $table->dateTime('drawn_at')->nullable();
                $table->json('winners_json')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 2. Configuración global de Tienda / Merch (Recogida vs Envío)
        if (!Schema::hasTable('shop_settings')) {
            Schema::create('shop_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('pickup_enabled')->default(true);
                $table->string('pickup_label')->default('Recogida Presencial en Evento Oficial SBBL');
                $table->text('pickup_instructions')->nullable();
                $table->boolean('shipping_enabled')->default(false);
                $table->decimal('shipping_cost', 8, 2)->default(4.95);
                $table->decimal('free_shipping_min', 8, 2)->nullable();
                $table->timestamps();
            });

            DB::table('shop_settings')->insert([
                'pickup_enabled'      => true,
                'pickup_label'        => 'Recogida Presencial en Evento Oficial SBBL',
                'pickup_instructions' => 'Tu pedido quedará registrado a tu nombre y correo. Preséntalo en la mesa de organización del evento para recoger tu merch.',
                'shipping_enabled'    => false,
                'shipping_cost'       => 4.95,
                'free_shipping_min'   => null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        // 3. Limpieza solicitada de registros antiguos de tickets y carritos (respetando orden de FKs)
        if (Schema::hasTable('carrito_productos')) {
            DB::table('carrito_productos')->delete();
        }
        if (Schema::hasTable('carritos')) {
            DB::table('carritos')->delete();
        }
        if (Schema::hasTable('tickets')) {
            DB::table('tickets')->delete();
        }

        // 4. Evolución de tabla tickets
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'raffle_id')) {
                $table->foreignId('raffle_id')->nullable()->after('user_id')->constrained('raffles')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tickets', 'paypal_order_id')) {
                $table->string('paypal_order_id')->nullable()->after('identifier')->index();
            }
            if (!Schema::hasColumn('tickets', 'paypal_capture_id')) {
                $table->string('paypal_capture_id')->nullable()->after('paypal_order_id')->index();
            }
            if (!Schema::hasColumn('tickets', 'price_paid')) {
                $table->decimal('price_paid', 8, 2)->default(0.00)->after('paypal_capture_id');
            }
        });

        // 5. Evolución de tabla carritos (Pedidos de Merch)
        Schema::table('carritos', function (Blueprint $table) {
            if (!Schema::hasColumn('carritos', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('carritos', 'telefono')) {
                $table->string('telefono', 30)->nullable()->after('email');
            }
            if (!Schema::hasColumn('carritos', 'tipo_entrega')) {
                $table->enum('tipo_entrega', ['recogida', 'envio'])->default('recogida')->after('direccion');
            }
            if (!Schema::hasColumn('carritos', 'gastos_envio')) {
                $table->decimal('gastos_envio', 8, 2)->default(0.00)->after('total');
            }
            if (!Schema::hasColumn('carritos', 'paypal_order_id')) {
                $table->string('paypal_order_id')->nullable()->after('referencia')->index();
            }
            if (!Schema::hasColumn('carritos', 'paypal_capture_id')) {
                $table->string('paypal_capture_id')->nullable()->unique()->after('paypal_order_id');
            }
        });

        // Ampliar enum estado_pago en carritos para contemplar reembolsado
        DB::statement("ALTER TABLE `carritos` MODIFY COLUMN `estado_pago` ENUM('pendiente','pagado','cancelado','reembolsado') NOT NULL DEFAULT 'pendiente'");

        // 6. Evolución de tabla events (Eventos de Pago)
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'is_paid')) {
                $table->boolean('is_paid')->default(false)->after('max_players');
            }
            if (!Schema::hasColumn('events', 'entry_fee')) {
                $table->decimal('entry_fee', 8, 2)->nullable()->default(null)->after('is_paid');
            }
        });

        // 7. Blindaje y Corte Contable de treasury_logs
        Schema::table('treasury_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('treasury_logs', 'source')) {
                $table->enum('source', ['paypal_auto', 'manual'])->default('manual')->after('type');
            }
            if (!Schema::hasColumn('treasury_logs', 'paypal_order_id')) {
                $table->string('paypal_order_id')->nullable()->after('reference_id')->index();
            }
            if (!Schema::hasColumn('treasury_logs', 'paypal_capture_id')) {
                $table->string('paypal_capture_id')->nullable()->unique()->after('paypal_order_id');
            }
            if (!Schema::hasColumn('treasury_logs', 'raffle_id')) {
                $table->foreignId('raffle_id')->nullable()->after('event_id')->constrained('raffles')->onDelete('set null');
            }
            if (!Schema::hasColumn('treasury_logs', 'carrito_id')) {
                $table->foreignId('carrito_id')->nullable()->after('raffle_id')->constrained('carritos')->onDelete('set null');
            }
            if (!Schema::hasColumn('treasury_logs', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('carrito_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('treasury_logs', 'refund_requested')) {
                $table->boolean('refund_requested')->default(false)->after('status');
            }
            if (!Schema::hasColumn('treasury_logs', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_requested');
            }
            if (!Schema::hasColumn('treasury_logs', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
            if (!Schema::hasColumn('treasury_logs', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->index()->after('updated_at');
            }
        });

        // Archivar todos los apuntes previos de treasury_logs para empezar el saldo activo en 0,00 € sin perder el histórico en BD
        DB::table('treasury_logs')->whereNull('archived_at')->update([
            'archived_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::table('treasury_logs', function (Blueprint $table) {
            $cols = [];
            foreach (['raffle_id', 'carrito_id', 'created_by'] as $fk) {
                if (Schema::hasColumn('treasury_logs', $fk)) {
                    $table->dropForeign([$fk]);
                    $cols[] = $fk;
                }
            }
            foreach (['source', 'paypal_order_id', 'paypal_capture_id', 'refund_requested', 'refunded_at', 'notes', 'archived_at'] as $col) {
                if (Schema::hasColumn('treasury_logs', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('events', function (Blueprint $table) {
            $cols = [];
            foreach (['is_paid', 'entry_fee'] as $col) {
                if (Schema::hasColumn('events', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('carritos', function (Blueprint $table) {
            if (Schema::hasColumn('carritos', 'user_id')) {
                $table->dropForeign(['user_id']);
            }
            $cols = [];
            foreach (['user_id', 'telefono', 'tipo_entrega', 'gastos_envio', 'paypal_order_id', 'paypal_capture_id'] as $col) {
                if (Schema::hasColumn('carritos', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'raffle_id')) {
                $table->dropForeign(['raffle_id']);
            }
            $cols = [];
            foreach (['raffle_id', 'paypal_order_id', 'paypal_capture_id', 'price_paid'] as $col) {
                if (Schema::hasColumn('tickets', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::dropIfExists('shop_settings');
        Schema::dropIfExists('raffles');
    }
}
