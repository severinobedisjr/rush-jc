@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 flex flex-col min-h-screen">
        <!-- Filters Section (Side Menu) -->
        <div class="w-1/4 pr-4">
            <h3 class="text-lg font-semibold text-red-500">Advanced Search</h3>
            <form action="{{ route('advance.search') }}" method="GET" class="space-y-4">
                <input type="text" name="author" placeholder="Author..." 
                       class="form-control w-full p-2 border rounded-lg" 
                       value="{{ request('author') }}"> <br>
                <input type="text" name="title" placeholder="Title..." 
                       class="form-control w-full p-2 border rounded-lg" 
                       value="{{ request('title') }}"> <br>
               
                <button type="submit" class="bg-blue-500 text-black px-4 py-2 rounded-lg w-full">Search</button>
                <button type="reset" class="bg-gray-300 text-black px-4 py-2 rounded-lg w-full">Reset</button>
            </form>
    
            @if(request()->has('author') || request()->has('title'))
            @if(isset($items) && count($items))
            <ul>
                @foreach($items as $item)
                    <li class="border-b py-4">
                        <a href="#" class="text-blue-600 font-semibold">{{ $item->research_title }}</a>
                        <p class="text-sm text-green-600">{{ implode(', ', $item->authors) }}</p>
                        <p class="text-sm text-gray-700">{{ Str::limit($item->abstract, 1000) }}</p>
                    </li>
                @endforeach
            </ul>
            @else
                <p class="text-red-500 text-center">No results found.</p>
            @endif
            @endif
        </div>
    </div>
@endsection