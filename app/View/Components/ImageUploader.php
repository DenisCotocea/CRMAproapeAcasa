<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Image;

class ImageUploader extends Component
{
    public $entityId;
    public $entityType;

    public $images;

    /**
     * Create a new component instance.
     */
    public function __construct($entityId, $entityType)
    {
        $this->entityId = $entityId;
        $this->entityType = $entityType;
        $this->images = Image::where('entity_id', $this->entityId)
            ->where('entity_type', $this->entityType)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.image-uploader');
    }
}
