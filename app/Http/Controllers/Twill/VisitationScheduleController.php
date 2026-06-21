<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\DatePicker;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\InlineRepeater;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Options;
use A17\Twill\Services\Forms\Option;
use App\Repositories\PrisonerRepository;
use App\Models\Customer;
use A17\Twill\Services\Listings\Filters\TableFilters;
use A17\Twill\Services\Listings\Filters\BasicFilter;
use Illuminate\Support\Collection;

use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

class VisitationScheduleController extends BaseModuleController
{
    protected $moduleName = 'visitationSchedules';
    /**
     * This method can be used to enable/disable defaults. See setUpController in the docs for available options.
     */
    protected function setUpController(): void
    {
    }

    /**
     * See the table builder docs for more information. If you remove this method you can use the blade files.
     * When using twill:module:make you can specify --bladeForm to use a blade form instead.
     */
    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->add(
            Input::make()
            ->name('prisoner_name')
            ->label('Tên phạm nhân')
        );
        $form->add(
            Input::make()
            ->name('prisoner_address')
            ->label('Địa chỉ phạm nhân')
        );
        $form->add(
            Input::make()
            ->name('prisoner_birthday')
            ->label('Năm sinh phạm nhân')
        );
        $prisoners = app()->make(PrisonerRepository::class)->listAll();
 
        $arrPrisoner= [];
        foreach ($prisoners->toArray() as $key => $value) {
            array_push($arrPrisoner, Option::make($key, $value));
        }

        $form->add(
            InlineRepeater::make()->name('relatives')->label("thân nhân")
                ->fields([
                    Input::make()->name('username')->label('Họ tên'),
                    Input::make()->name('phone')->label('Số điện thoại'),
                    Input::make()->name('address')->label('Địa chỉ'),
                    Select::make()
                        ->name('relationship')
                        ->label('Mối quan hệ')
                        ->options(
                            Options::make([
                                Option::make('FATHER', 'Bố'),
                                Option::make('MOTHER', 'Mẹ'),
                                Option::make('WIFE', 'Vợ'),
                                Option::make('HUSBAND', 'Chồng'),
                                Option::make('CHILD', 'Con'),
                                Option::make('NURTURER', 'Người nuôi dưỡng'),
                            ])
                        )
                    
                ]) 
        );
        
        $form->add(
            DatePicker::make()
            ->name('visitDate')
            ->withoutTime()
            ->altFormat('d/m/Y')
            ->label('Ngày')
        );

        $form->add(
            DatePicker::make()
                ->name('visitTime')
                ->timeOnly()
                ->time24Hr()
                ->altFormat('H:i')
                ->label('Khung giờ')

        );

        $form->add(
            Input::make()
            ->type('number')
            ->name('count')
            ->label('Số người thăm')
        );

        $form->add(
            Select::make()
            ->name('prisoner_id')
            ->label('Gắn phạm nhân')
            ->options(
            Options::make($arrPrisoner)
            )
        );

        $form->add(
        Select::make()
            ->name('customer_id')
            ->label('Thân nhân')
            ->options(
                Customer::query()
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($customer) => [
                        'value' => $customer->id,
                        'label' => $customer->name ?? "Không tên" ,
                    ])
                    ->toArray()
            )
        );

        $form->add(
            Select::make()
                ->name('status')
                ->label('Trạng thái')
                ->options([
                    ['value' => 'DONE', 'label' => 'Đã thăm'],
                    ['value' => 'NOT_YET', 'label' => 'Sắp tới'],
                ])
        ) ;

        $form->add(
            Select::make()
                ->name('refuse')
                ->label('Lý do từ chối')
                ->options([
                    ['value' => 'Sai thông tin phạm nhân', 'label' => 'Sai thông tin phạm nhân'],
                    ['value' => 'Phạm nhân bị cơ quan tố tụng cấm thăm gặp', 'label' => 'Phạm nhân bị cơ quan tố tụng cấm thăm gặp'],
                    ['value' => 'Trùng thời gian thăm gặp', 'label' => 'Trùng thời gian thăm gặp'],
                    ['value' => 'Lý do khác', 'label' => 'Lý do khác'],
                    ['value' => '', 'label' => 'TRỐNG'],
                ])
                ->note("* Lưu ý: Trường này để trống thì lịch thăm mới không bị từ chối")
        ) ;

        return $form;
    }

    /**
     * This is an example and can be removed if no modifications are needed to the table.
     */
    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->add(
            Text::make()
            ->field('status_label')
            ->title('Trạng thái')
        );

        $table->add(
            Text::make()->field('refuse')->title('Lý do từ chối')
        );

        $table->add(
            Text::make()->field('visitTime')->title('Giờ thăm')
        );

        $table->add(
            Text::make()->field('visitDate')->title('Ngày thăm')
        );

        $table->add(
            Text::make()->field('visitTime')->title('Giờ thăm')
        );

        return $table;
    }



public function filters(): TableFilters
{
    return TableFilters::make([
        BasicFilter::make()
            ->label('Ngày thăm')
            ->queryString('date_filter')
            ->options(collect([
                'tomorrow' => 'Ngày mai',
                'today' => 'Hôm nay',
                'yesterday' => 'Hôm qua',
                'this_week' => 'Tuần này',
                'this_month' => 'Tháng này',
            ]))
            ->apply(function ($query, $value) {
                switch ($value) {
                    case 'tomorrow':
                        $query->whereDate('visitDate', now()->addDay());
                        break;

                    case 'today':
                        $query->whereDate('visitDate', now());
                        break;

                    case 'yesterday':
                        $query->whereDate('visitDate', now()->subDay());
                        break;

                    case 'this_week':
                        $query->whereBetween('visitDate', [
                            now()->startOfWeek(\Carbon\Carbon::MONDAY),
                            now()->endOfWeek(\Carbon\Carbon::MONDAY),
                        ]);
                        break;

                    case 'this_month':
                        $query->whereYear('visitDate', now()->year)
                              ->whereMonth('visitDate', now()->month);
                        break;
                }
            }),
    ]);
}
}
