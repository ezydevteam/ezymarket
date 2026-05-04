<div>
    @livewire('comments.comments-counter', [
    'product' => $product,
    'isActive' => request()->routeIs('products.comments')
    ], key('comments-counter-'.$product->id))
</div>
@livewire('comments.comments', ['product' => $product], key('comments-'.$product->id))
