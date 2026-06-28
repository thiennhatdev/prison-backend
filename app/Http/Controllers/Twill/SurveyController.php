<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;
use A17\Twill\Services\Forms\Fields\Wysiwyg;

class SurveyController extends BaseModuleController
{
    protected $moduleName = 'surveys';
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

        // $form->add(
        //     Input::make()->name('code')->label('Code')
        // );

        $form->add(
            Input::make()->name('point')->label('Điểm đánh giá')
        );

        $form->add(
            Wysiwyg::make()->name('description')->label('Nội dung')
        );

        return $form;
    }

    protected function getIndexTableColumns(): TableColumns
    {
        $columns = TableColumns::make();

        $columns->add(
            Text::make()
                ->field('title')
                ->title('Đánh giá của')
                ->linkToEdit()
        );


        $columns->add(
            Text::make()
                ->field('description')
                ->title('Đánh giá')
        );

        $columns->add(
            Text::make()
                ->field('point')
                ->title('Điểm')
        );

        return $columns;
    }
}
