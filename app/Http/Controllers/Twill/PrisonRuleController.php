<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\BlockEditor;

class PrisonRuleController extends BaseModuleController
{
    protected $moduleName = 'prisonRules';
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
            Medias::make()->name('thumbnail')->label('Ảnh')->required()
        );

        $form->add(
            Input::make()->name('description')->label('Mô tả ngắn')
        );

        $form->add(
                BlockEditor::make()
                ->name('blocks')
                ->label('Nội dung')
                ->blocks([

                     \App\View\Components\Twill\Blocks\Info::class, 
                    \App\View\Components\Twill\Blocks\Image::class,
                
                ])
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
            Text::make()->field('description')->title('Mô tả ngắn')
        );

        return $table;
    }
}
