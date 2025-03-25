

@extends('layouts.app')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .pagination-button {
        text-decoration: none;
        display: inline-block;
        padding: 8px 16px;
        border-radius: 10%;
        font-weight: bold;
        text-align: center; /* Center the text horizontally */
        min-width: 50px; /* Ensure a minimum width */
    }

    .pagination-button:hover {
        background-color: #ddd;
        color: black;
    }

    .previous {
        background-color: #f1f1f1;
        color: black;
    }

    .next {
        background-color: #04AA6D;
        color: white;
    }

    /* Styles for the keyword suggestions dropdown */
    #keyword_suggestions {
        position: absolute;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        z-index: 1;
        width: 90%; /* Match the width of the input */
       /* max-height: 200px;  Limit the size */
       /* overflow-y: auto; */
       /* box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);  optional shadow */
    }

    .suggestion-item {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        cursor: pointer;
    }

    .suggestion-item:hover {
        background-color: #ddd;
    }
</style>

@section('content')
    <div class="container mx-auto p-4 flex flex-col items-center justify-content-center min-h-screen">
        <div class="w-full max-w-md rounded-lg">
            <h3 class="text-lg font-semibold text-red-500 text-center">Search</h3>
            <form action="{{ route('items.search') }}" method="GET" class="space-y-4">
                <div class="position-relative"> <!-- Added position-relative here -->
                    <input type="text" id="search_term" name="query" placeholder="Search by title, author, keyword, or year..."
                           class="form-control w-full p-2 border rounded-lg dropdown-toggle"
                           value="{{ request('query') }}" autocomplete="off" data-bs-toggle="dropdown" aria-expanded="false">

                    <ul class="dropdown-menu" id="keyword_suggestions"></ul>
                </div>
                <br>
                <button type="submit" class="bg-blue-500 text-black px-4 py-2 rounded-lg w-full">Search</button>
                <button type="button" class="bg-gray-300 text-black px-4 py-2 rounded-lg w-full" onclick="clearSearch()">Reset</button>
            </form>

            @if(request()->has('query'))
            @if(isset($items) && $items->count())
                <div class="mt-4 w-full">
                    @foreach($items->slice(($page - 1) * $perPage, $perPage) as $item)
                        <div class="bg-white rounded-lg shadow-lg p-6 mb-4" style="padding: 48px; margin-bottom: 48px;">
                            <h1 class="text-xl font-bold text-gray-900">{{ $item->research_title }}</h1>
                            <b> Abstract: </b>
                            <p class="text-gray-700 mb-2" style="text-align: justify">{{ $item->abstract }}</p>
                            <div class="text-sm text-gray-600 mb-2">
                                <b> Authors: </b>
                                <span>{{ implode(', ', $item->authors) }}</span>
                            </div>
                            @php
                                $keywordsArray = explode(',', $item->keyword);
                            @endphp
                            <div class="text-sm text-gray-500 mb-4">
                                <b>Keywords: </b> <i> {{ implode(', ', $keywordsArray) }} </i>
                            </div>

                            <a href="{{ route('products.view_pdf', ['id' => $item->id]) }}" target="_blank"
                                style="display: inline-block; padding: 8px 16px; background-color: #4CAF50;
                                color: white; text-decoration: none; border-radius: 5px;">
                                 View File
                             </a>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-center mt-4">
                    @if ($page > 1)
                        <a href="{{ route('items.search', ['query' => request('query'), 'page' => $page - 1]) }}"
                           class="pagination-button previous mr-2">
                            ‹
                        </a>
                    @endif

                    @if (($page * $perPage) < $items->count())
                        <a href="{{ route('items.search', ['query' => request('query'), 'page' => $page + 1]) }}"
                           class="pagination-button next">
                            ›
                        </a>
                    @endif
                </div>

            @else
                <p class="mt-4 text-red-500 text-center">No results found.</p>
            @endif
        @endif
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function clearSearch() {
            // Clear the query input
            document.querySelector('input[name="query"]').value = '';
        }

        jQuery.noConflict();
    (function($) {
        $(document).ready(function() {
            $('#search_term').on('keyup', function() {
                let searchTerm = $(this).val();

                if (searchTerm.length < 2) {
                    $('#keyword_suggestions').empty();
                    return;
                }

                $.ajax({
                    url: '/products/keywords/autocomplete',
                    type: 'GET',
                    data: {query: searchTerm},
                    success: function(data) {
                        let suggestions = '';
                        data.forEach(function(keyword) {
                            suggestions += `<li><a class="dropdown-item suggestion-item" data-keyword="${keyword}" href="#">${keyword}</a></li>`;
                        });
                        $('#keyword_suggestions').html(suggestions);

                        // Show the dropdown
                        if (suggestions) {
                            $('#keyword_suggestions').dropdown('show');
                        } else {
                            $('#keyword_suggestions').dropdown('hide');
                        }
                    }
                });
            });

            $(document).on('click', '.suggestion-item', function(event) {
                $('#search_term').val($(this).data('keyword'));
                $('#keyword_suggestions').empty();
                $(this).closest('form').submit();
                event.preventDefault();
            });
        });
    })(jQuery);
    </script>
@endsection