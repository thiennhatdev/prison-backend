<?php

namespace App\View\Components\Twill\Blocks;

use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\View\Components\Blocks\TwillBlockComponent;
use Illuminate\Contracts\View\View;
use A17\Twill\Services\Forms\Fields\Medias;

class Image extends TwillBlockComponent
{
         public static function getBlockTitle(): string
    {
        return 'Ảnh full độ rộng'; 
    }


    public function render(): View
    {
        return view('components.twill.blocks.image');
    }

    public function getForm(): Form
    {
        return Form::make([
            Medias::make()->name('image')
            ->label('Thêm ảnh')
            ->max(20)
        ]);
    }
}
