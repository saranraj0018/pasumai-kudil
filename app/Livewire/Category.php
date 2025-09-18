<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Category as CategoryModel;

class Category extends Component
{
    use WithFileUploads;

    public $categories;
    public $name, $image, $status = 1, $category_id;
    public $category_image;
    public $showModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'status' => 'boolean',
        'category_image' => 'nullable|image|max:1024',
    ];

    public function create()
    {
        $this->resetFields();
        $this->showModal = true;
    }


    private function resetFields()
    {
        $this->name = '';
        $this->status = 1;
        $this->category_id = null;
        $this->category_image = null;
    }

    public function render()
    {
        $this->categories = CategoryModel::with('admin')->get();
        return view('livewire.grocery.category');
    }
}
