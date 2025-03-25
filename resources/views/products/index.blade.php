@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">My Research List</div>
    <div class="card-body">
        @can('create-product')
        <a href="{{ route('products.create') }}" class="btn btn-success btn-sm my-2"><i class="bi bi-plus-circle"></i> Add New Research</a>
        @endcan
        <table class="table table-striped table-bordered" id="productsTable">
            <thead>
                <tr>
                    <th scope="col">S#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Abstract</th>
                    <th scope="col">Authors</th>
                  
                    <th scope="col">Keywords</th>
                    <th scope="col">View PDF</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->research_title }}</td>
                    <td>{{ $product->abstract }}</td>
                    <td>
                        @if ($product->authors)
                        @if (is_array($product->authors))
                        <ul>
                            @foreach ($product->authors as $author)
                            <li>{{ $author }}</li>
                            @endforeach
                        </ul>
                        @else
                        {{ $product->authors }}
                        @endif
                        @else
                        No Authors
                        @endif
                    </td>
                    <td>{{ $product->keyword }}</td>
                    <td>
                        @if ($product->pdf_path)
                        <a href="{{ route('products.view_pdf', ['id' => $product->id]) }}" target="_blank">View PDF</a>
                        @else
                        No PDF Available
                        @endif
                    </td>
                    <td> {{ $product->status}}</td>
                    <td>
                        <form id="deleteForm_{{ $product->id }}">
                            @csrf
                            @method('DELETE')

                            @php
                                $auth = Auth::user();
                                $isApproved = false;
                            
                                if ($auth->hasRole('Technical Adviser') && $product->status == 'Approved by Technical Adviser') {
                                $isApproved = true;
                                } elseif($auth->hasRole('Department Chair') && $product->status == 'Approved by Department Chair') {
                                $isApproved = true;
                                } elseif($auth->hasRole('Subject Adviser') && $product->status == 'Approved by Subject Adviser') {
                                $isApproved = true;
                                }
                            @endphp
                            
                            @can('approved-product')
                                <a href="#"
                                class="btn btn-success btn-sm approve-product {{ $isApproved ? 'disabled' : '' }}"
                                data-product-id="{{ $product->id }}"
                                {{ $isApproved ? 'disabled' : '' }}>
                                    <i class="bi bi-pencil-square"></i> Approve
                                </a>
                            @endcan

                            @can('edit-product')
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm
                            {{ $product->status == 'Approved'? 'disabled' : '' }}"><i class="bi bi-pencil-square"></i> Edit</a>
                            @endcan

                            @can('delete-product')
                            <button type="button" class="btn btn-danger btn-sm deleteBtn
                            {{ $product->status == 'Approved'? 'disabled' : '' }}" data-id="{{ $product->id }}"><i class="bi bi-trash"></i> Delete</button>
                            @endcan
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <span class="text-danger">
                            <strong>No Product Found!</strong>
                        </span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{ $products->links() }}

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  $(document).ready(function() {

// Approve button click handler
$('#productsTable').on('click', '.approve-product', function(event) {
    event.preventDefault();
    const productId = $(this).data('product-id');
    const row = $(this).closest('tr'); // Get the parent row

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Send AJAX request to update the status

            
            $.ajax({
                url: "{{ route('products.approve', '') }}/" + productId, // Append product ID to URL
                type: 'POST', // Changed to POST for data update
                data: {
                   
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update the status on the page
                        row.find('td:eq(6)').text(response.newStatus); // Update the status column

                        Swal.fire(
                            'Approved!',
                            'The product has been approved.',
                            'success'
                            ).then(() => { // Added .then()
                            location.reload(); // Reload the page after the user clicks "OK"
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message,
                            'error'
                        )
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire(
                        'Error!',
                        'There was an error approving the product.',
                        'error'
                    )
                }
            });
        }
    });
});

// Delete button click handler
$('#productsTable').on('click', '.deleteBtn', function() {
    var productId = $(this).data('id');
    var url = "{{ route('products.destroy', ':id') }}".replace(':id', productId);

    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to delete this research?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Remove the row from the table
                        location.reload();
                        Swal.fire(
                            'Deleted!',
                            'Your research has been deleted.',
                            'success'
                        )
                    } else {
                        Swal.fire(
                            'Error!',
                            'There was an error deleting the research.',
                            'error'
                        )
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire(
                        'Error!',
                        'There was an error deleting the research.',
                        'error'
                    )
                }
            });
        }
    })
});

});
</script>
@endsection