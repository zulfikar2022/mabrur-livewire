<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\CategoryImage;
use ImageKit\ImageKit;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin')] class extends Component {
    use WithFileUploads;

    public Category $category;
    public $name;
    public $newImage;
    public $existingImageLink;

    public function mount(Category $category)
    {
        $this->category = $category->load('categoryImage');
        $this->name = $category->name;
        $this->existingImageLink = $category->categoryImage ? $category->categoryImage->image_link : null;
    }

    protected function rules()
    {
        return [
            'name'     => 'required|string|min:2|max:255|unique:categories,name,' . $this->category->id,
            'newImage' => 'nullable|image|max:16384',
        ];
    }

    public function save()
    {
        $this->validate();

        // 1. Update Name
        $this->category->update([
            'name' => $this->name,
        ]);

        // 2. Handle New Image Upload (if provided)
        if ($this->newImage) {
            $imageKit = new ImageKit(
                config('services.imagekit.public_key'),
                config('services.imagekit.private_key'),
                config('services.imagekit.url_endpoint')
            );

            $uploadResult = $imageKit->upload([
                'file'     => base64_encode(file_get_contents($this->newImage->getRealPath())),
                'fileName' => $this->newImage->getClientOriginalName(),
                'folder'   => '/category_images'
            ]);

            if ($uploadResult->error === null) {
                // Delete the old record from DB (Let your cron job handle ImageKit server cleanup)
                if ($this->category->categoryImage) {
                    $this->category->categoryImage->delete();
                }

                // Create new image record
                CategoryImage::create([
                    'category_id' => $this->category->id,
                    'image_link'  => $uploadResult->result->filePath,
                ]);
            } else {
                \Illuminate\Support\Facades\Log::error('Category Image Update Failed: ', (array) $uploadResult->error);
                session()->flash('error', 'Category name updated, but new image upload failed.');
                return redirect()->route('admin.show-all-categories');
            }
        }

        session()->flash('success', 'Category updated successfully!');
        return redirect()->route('admin.show-all-categories');
    }
};
?>

<div class="max-w-3xl mx-auto p-4 md:p-6 my-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Update Category</h1>
        <a href="{{ route('admin.show-all-categories') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-colors">
            <i class="fa-solid fa-arrow-left mr-1"></i> 
            Back to List
        </a>
    </div>

    <form wire:submit.prevent="save" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
        
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
            <input type="text" wire:model="name" class="w-full border border-gray-300 rounded-xl p-3 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all @error('name') border-red-500 @enderror">
            @error('name') <span class="text-xs font-semibold text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div x-data="{ 
                photoPreview: null,
                existingPhoto: '{{ $existingImageLink ? config('services.imagekit.url_endpoint') . $existingImageLink . '?tr=w-300,h-300,fo-auto' : '' }}',
                handleFileChange(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (e) => { this.photoPreview = e.target.result; };
                    reader.readAsDataURL(file);
                }
             }">
            
            <label class="block text-sm font-bold text-gray-700 mb-2">Category Image</label>
            
            <div class="flex flex-col md:flex-row items-start gap-6">
                <div class="shrink-0 relative group">
                    <img x-show="photoPreview" :src="photoPreview" class="w-32 h-32 rounded-2xl object-cover shadow-sm border border-gray-200" style="display: none;">
                    
                    <img x-show="!photoPreview && existingPhoto" :src="existingPhoto" class="w-32 h-32 rounded-2xl object-cover shadow-sm border border-gray-200">
                    
                    <div x-show="!photoPreview && !existingPhoto" class="w-32 h-32 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400">
                        <i class="fa-solid fa-image text-3xl"></i>
                    </div>
                </div>

                <div class="flex-1 mt-2">
                    <input type="file" wire:model="newImage" id="category-image" class="hidden" accept="image/*" @change="handleFileChange">
                    <label for="category-image" class="inline-flex items-center justify-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-xl transition-colors cursor-pointer border border-blue-200 shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Choose New Photo
                    </label>
                    <p class="mt-2 text-xs text-gray-500 font-medium">Leave blank to keep the current image.</p>
                    @error('newImage') <span class="text-xs font-semibold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex justify-end">
            <button type="submit" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-md flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk mr-2"></i> Update Category</span>
                <span wire:loading wire:target="save"><i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Processing...</span>
            </button>
        </div>
    </form>
</div>