<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Forms\Options;
use A17\Twill\Services\Forms\Option;
use App\Enums\RelationshipEnum;
use A17\Twill\Services\Forms\InlineRepeater;

use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;

class PrisonerController extends BaseModuleController
{
    protected $moduleName = 'prisoners';
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
            Input::make()->name('prisoner_code')->label('Mã giam')
        );

        $form->add(
            Input::make()->name('username')->label('Họ tên')
        );

        $form->add(
            Select::make()
                ->name('prisoner_sex')
                ->label('Giới tính')
                ->options([
                    ['value' => 'MALE', 'label' => 'Nam'],
                    ['value' => 'FEMALE', 'label' => 'Nữ'],
                ])
        ) ;

        
        $form->add(
            Input::make()
            ->name('prisoner_birthday')
            ->label('Năm sinh phạm nhân')
        );

        $form->add(
            Input::make()
            ->name('prisoner_address')
            ->label('Địa chỉ phạm nhân')
        );

        $form->add(
            InlineRepeater::make()->name('phones')->label("SĐT thân thích")
            ->max(2)    
            ->fields([
                    Input::make()->name('name')->label('Tên'),
                    Input::make()->name('phone')->label('SĐT'),
                    Select::make()
                        ->name('relationship')
                        ->label('Mối quan hệ')
                        ->options(
                            Options::make(RelationshipEnum::twillOptions())
                        )
                    
                ]) 
        );

        $form->add(
            Select::make()
            ->name('is_allow_visit')
            ->label('Cho phép thăm')
            ->options(
            Options::make([
                Option::make(false, "Không được phép"),
                Option::make(true, "Được phép"),
            ])
            )
        );

        return $form;
    }

    /**
     * This is an example and can be removed if no modifications are needed to the table.
     */
    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->add(
            Text::make()->field('prisoner_code')->title('Mã giam')
        );

        $table->add(
            Text::make()->field('username')->title('Họ tên')
        );

        $table->add(
            Text::make()->field('prisoner_sex')->title('Giới tính')
        );

        $table->add(
            Text::make()->field('prisoner_birthday')->title('Năm sinh')
        );

        $table->add(
            Text::make()->field('prisoner_address')->title('Địa chỉ')
        );


        return $table;
    }
}
