@extends('layouts.public')

@section('title', $page->title)

@section('content')
    @foreach($page->blocks as $block)
        @if(view()->exists('blocks.' . $block->block_type))
            @include('blocks.' . $block->block_type, ['block' => $block])
        @endif
    @endforeach
@endsection
