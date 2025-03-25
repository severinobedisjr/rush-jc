@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">

        <div class="card">
            <div class="card-header">
                <div class="float-start">
                    Edit Research
                </div>
                <div class="float-end">
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm">← Back</a>
                </div>
            </div>
            <div class="card-body">
                <form id="researchForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="research_title">Research Title</label>
                        <input type="text" class="form-control @error('research_title') is-invalid @enderror" id="research_title" name="research_title" value="{{ old('research_title', $product->research_title) }}" required>
                        @error('research_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <br>

                    <div class="form-group">
                        <label for="abstract">Abstract</label>
                        <textarea class="form-control @error('abstract') is-invalid @enderror" id="abstract" name="abstract" rows="4" required>{{ $product->abstract }}</textarea>
                        @error('abstract')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <br>
                    <div class="form-group">
                        <label for="keyword">Keyword</label>
                        <input type="text" class="form-control @error('keyword') is-invalid @enderror" id="keyword" name="keyword" value="{{ $product->keyword }}" required>
                        @error('keyword')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <br>

                    <div class="form-group">
                        <label>Authors</label>
                        <div id="authors-container">
                            @if($product->authors)
                            @foreach($product->authors as $index => $author)
                            <div class="input-group mb-2">
                                <input type="text" class="form-control author-input" name="authors[]" value="{{ $author }}" required>
                                <div class="input-group-append">
                                    @if($loop->first)
                                    <button class="btn btn-outline-secondary add-author" type="button">+</button>
                                    @else
                                    <button class="btn btn-outline-danger remove-author" type="button">-</button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="input-group mb-2">
                                <input type="text" class="form-control author-input" name="authors[]" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary add-author" type="button">+</button>
                                </div>
                            </div>
                            @endif
                        </div>
                        <small class="form-text text-muted">Add up to 5 authors.</small>
                    </div>

                    <br>

                    <div class="form-group">
                        <label>Current PDF File:</label>
                        @if($product->pdf_path)
                        <div>
                            <a href="{{ route('products.view_pdf', ['id' => $product->id]) }}" target="_blank">View Current PDF</a>
                            <p>File Name: {{ basename($product->pdf_path) }}</p>
                        </div>
                        @else
                        <div>No PDF currently uploaded.</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="pdf_file">Upload New PDF</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('pdf_file') is-invalid @enderror" id="pdf_file" name="pdf_file" accept=".pdf">
                            <label class="custom-file-label" for="pdf_file">Choose file</label>
                            @error('pdf_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <br>

                    <button type="submit" class="btn btn-primary" id="submitBtn">Update Research</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        let authorCount = $('#authors-container').children().length; // Initial author count
        const maxAuthors = 5; // Maximum number of authors

        // Add Author Button Click
        $('#authors-container').on('click', '.add-author', function() {
            if (authorCount < maxAuthors) {
                const newAuthorInput = `
                    <div class="input-group mb-2">
                        <input type="text" class="form-control author-input" name="authors[]" required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-danger remove-author" type="button">-</button>
                        </div>
                    </div>
                `;
                $('#authors-container').append(newAuthorInput);
                authorCount++;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Maximum Authors Reached',
                    text: 'You can add a maximum of 5 authors.',
                });
            }
        });

        // Remove Author Button Click
        $('#authors-container').on('click', '.remove-author', function() {
            $(this).closest('.input-group').remove();
            authorCount--;
        });

        // Update the label of the file input
        $('.custom-file-input').on('change', function(e) {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        $('#researchForm').submit(function(e) {
            e.preventDefault(); // Prevent the default form submission

            var formData = new FormData(this); // Use FormData to handle file uploads

            // Append CSRF token: Double-check this is working!
            formData.append('_token', "{{ csrf_token() }}");

            // Log FormData contents for debugging
            console.log('FormData contents:');
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }


            // Create URL route, check if well
            var url = "{{ route('products.updates', ['product' => $product->id]) }}";

            $.ajax({
                url: url,
                type: 'POST', // Important: Keep as POST (Laravel will handle the PUT conversion)
                data: formData,
                contentType: false, // Important: Don't set contentType when using FormData
                processData: false, // Important: Don't let jQuery process the data
                dataType: 'json', // Expect JSON response
                beforeSend: function() {
                    $('#submitBtn').prop('disabled', true).text('Updating...');
                },
                success: function(response) {
                    // Handle success
                    console.log(response);

                    // Display success message using SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('products.index') }}"; // Redirect after OK is clicked
                        }
                    });

                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.log(xhr.responseText);
                    var errors = xhr.responseJSON.errors;
                    var errorString = '';
                    $.each(errors, function(key, value) {
                        errorString += value + '<br>';
                    });

                    // Display errors using SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: errorString,
                    });

                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Update Research');
                }
            });
        }); 
    });
</script>
@endsection