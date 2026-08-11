<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Notifications\OrderShippingUpdated;
use App\Services\FonnteService;
use App\Services\Members\ConsultationMembershipService;
use App\Support\PaymentMethodFormatter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

use App\Filament\Resources\Orders\Tables\Concerns\HasOrderColumns;
use App\Filament\Resources\Orders\Tables\Concerns\HasOrderFilters;
use App\Filament\Resources\Orders\Tables\Concerns\HasOrderRowActions;
use App\Filament\Resources\Orders\Tables\Concerns\HasOrderPaymentAction;
use App\Filament\Resources\Orders\Tables\Concerns\HasOrderHelpers;

class OrdersTable
{
    use HasOrderColumns;
    use HasOrderFilters;
    use HasOrderRowActions;
    use HasOrderPaymentAction;
    use HasOrderHelpers;

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order_id', 'desc')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make("No")->rowIndex(),
                self::columnPesananWaktu(),
                self::columnPelanggan(),
                self::columnDetailPengiriman(),
                self::columnAlamat(),
                self::columnTagihan(),
                self::columnStatus(),
            ])
            ->filters(self::filters())
            ->recordActions([
                self::actionConfirmPayment(),   // wallet — hanya untuk pending
                self::actionUpdateResi(),       // truk — input nomor resi
                self::actionView(),              // eye — slide-over detail invoice
                self::actionPrintInvoice(),     // printer
                ActionGroup::make([
                    self::actionApproveCancellation(),
                    self::actionRejectCancellation(),
                    self::actionCancelOrder(),
                ])->label('')->icon('heroicon-o-ellipsis-vertical')->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

}