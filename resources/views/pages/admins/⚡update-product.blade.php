<?php

namespace App\Livewire\Admin;

use App\Jobs\CreateEmbedding;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use ImageKit\ImageKit;

new #[Layout('layouts.admin')] class extends Component {
    use WithFileUploads;

    public Product $product;

    public $category_id;
    public $name;
    public $description;
    public $sell_type;
    public $price_per_piece;
    public $price_per_kg;
    public $is_available;
    public $available_quantity;

    // NEW: Added properties for the new fields
    public $weight_per_piece;
    public $is_mango;

    public $images = [];
    public $existingImages = [];
    public $categories = [];

    public function mount(Product $product)
    {
        $this->product = $product->load('productImages');

        $this->categories      = Category::orderBy('name', 'asc')->get();
        $this->category_id     = $product->category_id;
        $this->name            = $product->name;
        $this->description     = $product->description;
        $this->is_available    = (bool) $product->is_available;
        $this->available_quantity = $product->available_quantity;

        // NEW: Load existing values into the component
        $this->weight_per_piece = $product->weight_per_piece;
        $this->is_mango         = (bool) $product->is_mango;

        $this->sell_type       = $product->sell_by_piece ? 'piece' : 'weight';
        $this->price_per_piece = $product->price_per_piece;
        $this->price_per_kg    = $product->price_per_kg;

        $this->existingImages  = $product->productImages->toArray();
    }

    protected function rules()
    {
        return [
            'category_id'     => 'required|exists:categories,id',
            'name'            => 'required|string|min:3|max:255',
            'description'     => 'required|string|min:5',
            'sell_type'       => 'required|in:piece,weight',
            'price_per_piece' => 'required_if:sell_type,piece|nullable|numeric|min:0',
            'price_per_kg'    => 'required_if:sell_type,weight|nullable|numeric|min:0',
            'available_quantity' => 'required|numeric|min:0',
            'is_available'    => 'boolean',
            'weight_per_piece' => 'nullable|numeric|min:0', // NEW: Validation
            'is_mango'        => 'boolean',                 // NEW: Validation
            'images.*'        => 'image|max:16384',
        ];
    }

    public function removeImage($index)
    {
        array_splice($this->images, $index, 1);
    }

    public function deleteExistingImage($id, $index)
    {
        $image = ProductImage::where('id', $id)->where('product_id', $this->product->id)->first();

        if ($image) {
            // Replaced API call with simple database deletion.
            // Your 4:00 AM Cron Job will clean this up from ImageKit!
            $image->delete();
        }

        array_splice($this->existingImages, $index, 1);
    }

    public function save()
    {
        $this->validate();

        $this->product->update([
            'category_id'     => $this->category_id,
            'name'            => $this->name,
            'description'     => $this->description,
            'available_quantity' => $this->available_quantity,
            'sell_by_piece'   => $this->sell_type === 'piece',
            'sell_by_weight'  => $this->sell_type === 'weight',
            'price_per_piece' => $this->sell_type === 'piece' ? $this->price_per_piece : null,
            'price_per_kg'    => $this->sell_type === 'weight' ? $this->price_per_kg : null,
            'is_available'    => $this->is_available,
            'weight_per_piece' => $this->weight_per_piece ?: null, // NEW: Save field
            'is_mango'        => $this->is_mango,                  // NEW: Save field
        ]);

        if (!empty($this->images)) {
            $imageKit = new ImageKit(
                config('services.imagekit.public_key'),
                config('services.imagekit.private_key'),
                config('services.imagekit.url_endpoint')
            );

            foreach ($this->images as $image) {
                $uploadResult = $imageKit->upload([
                    'file' => base64_encode(file_get_contents($image->getRealPath())),
                    'fileName' => $image->getClientOriginalName(),
                    'folder' => '/products'
                ]);

                if ($uploadResult->error === null) {
                    ProductImage::create([
                        'product_id' => $this->product->id,
                        'image_link' => $uploadResult->result->filePath,
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::error('ImageKit Update Upload Failed: ', (array) $uploadResult->error);
                }
            }
        }

        session()->flash('success', 'Product updated successfully!');
        CreateEmbedding::dispatch($this->product);
        return redirect()->route('admin.show-all-products');
    }
};
?>

<div class="max-w-4xl mx-auto p-6 bg-white rounded-xl shadow-sm border border-gray-100 my-6">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.min.js"></script>

    <style>
        trix-toolbar .trix-button-group { border: 1px solid #e5e7eb !important; border-radius: 0.375rem !important; }
        trix-editor { border: 1px solid #e5e7eb !important; border-radius: 0.5rem !important; padding: 0.75rem !important; min-height: 150px !important; outline: none !important; }
        trix-editor:focus { border-color: #3b82f6 !important; ring: 1px #3b82f6 !important; }
        .trix-button--icon-attach { display: none !important; }
    </style>

    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">Update Product: {{ $name }}</h2>

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                <input type="text" wire:model="name" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-blue-500 @error('name') border-red-500 @enderror">
                @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select wire:model="category_id" class="w-full border border-gray-300 rounded-lg p-2.5 bg-white outline-none focus:border-blue-500 @error('category_id') border-red-500 @enderror">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="w-full" wire:ignore>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
            <div x-data="{ 
                    value: @entangle('description'),
                    init() {
                        this.$refs.trix.editor.loadHTML(this.value);
                    }
                }"
                @trix-change="value = $event.target.value"
                class="w-full">
                
                <input id="x" type="hidden" name="content" :value="value">
                <trix-editor input="x" x-ref="trix" class="prose max-w-none border-gray-300 rounded-lg shadow-sm"></trix-editor>
            </div>
        </div>
        @error('description') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror

        <div class="bg-orange-50 p-4 rounded-xl border border-orange-200">
            <span class="block text-sm font-semibold text-gray-800 mb-3">পণ্যটি কি আম? (Is this product a mango?)</span>
            <div class="flex items-center space-x-6">
                <label class="flex items-center cursor-pointer text-sm font-medium text-gray-700">
                    <input type="radio" wire:model="is_mango" value="1" class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500 mr-2">
                    হ্যাঁ
                </label>
                <label class="flex items-center cursor-pointer text-sm font-medium text-gray-700">
                    <input type="radio" wire:model="is_mango" value="0" class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500 mr-2">
                    না
                </label>
            </div>
            @error('is_mango') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if($sell_type === 'piece')
                <div x-transition>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price Per Piece *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">৳</span>
                        <input type="number" step="0.01" wire:model.live.number="price_per_piece" class="w-full border border-gray-300 rounded-lg pl-8 pr-4 py-2.5 outline-none focus:border-blue-500 @error('price_per_piece') border-red-500 @enderror">
                    </div>
                    @error('price_per_piece') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            @endif

            @if($sell_type === 'weight')
                <div x-transition>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price Per KG *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-500">৳</span>
                        <input type="number" step="0.01" wire:model.live.number="price_per_kg" class="w-full border border-gray-300 rounded-lg pl-8 pr-4 py-2.5 outline-none focus:border-blue-500 @error('price_per_kg') border-red-500 @enderror">
                    </div>
                    @error('price_per_kg') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            @endif

            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Available Quantity *</label>
                    <input type="number" 
                        wire:model="available_quantity" 
                        class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-blue-500 @error('available_quantity') border-red-500 @enderror">
                    @error('available_quantity') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight Per Piece (KG)</label>
                    <input type="number" 
                        step="0.01" 
                        min="0"
                        wire:model="weight_per_piece" 
                        placeholder="e.g. 1.5"
                        class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-blue-500 @error('weight_per_piece') border-red-500 @enderror">
                    @error('weight_per_piece') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex flex-col justify-end">
                <span class="block text-sm font-medium text-gray-700 mb-2">Product Status</span>
                <div class="flex items-center">
                    <button type="button" 
                            wire:click="$set('is_available', {{ !$is_available ? 'true' : 'false' }})"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $is_available ? 'bg-green-600' : 'bg-gray-200' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $is_available ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                    <span class="text-sm text-gray-600 ml-3 font-medium">{{ $is_available ? 'Available for Customers' : 'Unavailable' }}</span>
                </div>
            </div>
        </div>

        @if(count($existingImages) > 0)
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                <span class="block text-sm font-semibold text-gray-700 mb-3">Currently Stored Images</span>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($existingImages as $index => $img)
                        <div wire:key="existing-img-{{ $img['id'] }}" class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 bg-white">
                            <img src="{{ config('services.imagekit.url_endpoint') . $img['image_link'] }}?tr=w-300,h-300,fo-auto" 
                                class="w-full h-full object-cover">
                            <button type="button" 
                                    wire:click="deleteExistingImage({{ $img['id'] }}, {{ $index }})" 
                                    class="absolute top-1.5 right-1.5 bg-red-600 hover:bg-red-700 text-white rounded-full h-6 w-6 flex items-center justify-center transition-colors shadow shadow-red-900/40 z-10">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 bg-gray-50"
             x-data="{
                localPreviews: [],
                handleFiles(event) {
                    const files = event.target.files;
                    for (let i = 0; i < files.length; i++) {
                        const reader = new FileReader();
                        reader.onload = (e) => { this.localPreviews.push(e.target.result); };
                        reader.readAsDataURL(files[i]);
                    }
                },
                removeLocal(index) {
                    this.localPreviews.splice(index, 1);
                    $wire.removeImage(index);
                }
             }"
             @clear-trix.window="localPreviews = []">
            
            <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Additional Images</label>
            <div class="flex items-center justify-center bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <input type="file" wire:model="images" id="file-upload" multiple class="hidden" accept="image/*" @change="handleFiles($event)">
                <label for="file-upload" class="cursor-pointer bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Choose New Images
                </label>
            </div>
            @error('images.*') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror

            <template x-if="localPreviews.length > 0">
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 mt-6">
                    <template x-for="(src, index) in localPreviews" :key="index">
                        <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 shadow-sm bg-white">
                            <img :src="src" class="w-full h-full object-cover">
                            <button type="button" @click="removeLocal(index)" class="absolute top-1.5 right-1.5 bg-red-600 hover:bg-red-700 text-white rounded-full h-6 w-6 flex items-center justify-center transition-colors shadow-md z-10">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <div class="flex justify-end space-x-3 border-t pt-4">
            <a href="{{ route('admin.show-all-products') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-2.5 rounded-lg transition-colors cursor-pointer text-center text-sm shadow-sm border">
                Cancel
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors cursor-pointer text-sm shadow-sm flex items-center">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Update Product Specification
            </button>
        </div>
    </form>
</div>