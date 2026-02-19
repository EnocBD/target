@if(isset($mainMenu) && $mainMenu->count() > 0)
    @foreach($mainMenu as $item)
        <a href="{{ $item->slug === '/' ? '/' : url($item->slug) }}" class="{{ $item->slug === '/' ? 'active' : '' }}">{{ $item->title }}</a>
    @endforeach
@endif

