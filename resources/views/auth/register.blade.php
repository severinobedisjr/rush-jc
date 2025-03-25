@extends('layouts.app')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form id="registerForm" method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label text-md-start">{{ __('Name') }}</label>

                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name') }}" placeholder="First Name" required autocomplete="given-name" autofocus>
                                        @error('first_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <input id="middle_name" type="text" class="form-control" name="middle_name" value="{{ old('middle_name') }}" placeholder="Middle Name" autocomplete="additional-name">
                                    </div>

                                    <div class="col-md-3">
                                        <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" placeholder="Last Name" required autocomplete="family-name">
                                        @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <input id="suffix" type="text" class="form-control" name="suffix" value="{{ old('suffix') }}" placeholder="Suffix" autocomplete="honorific-suffix">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="id_number" class="col-md-3 col-form-label text-md-start">{{ __('ID Number') }}</label>

                            <div class="col-md-6">
                                <input id="id_number" type="text" class="form-control @error('id_number') is-invalid @enderror" name="id_number" value="{{ old('id_number') }}" placeholder="ID Number" autocomplete="honorific-suffix">

                                @error('id_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="department" class="col-md-3 col-form-label text-md-start">{{ __('Department') }}</label>

                            <div class="col-md-6">
                                <select id="department" class="form-control @error('department') is-invalid @enderror" name="department">
                                    <option value="" selected disabled>Select Department</option>
                                    <option value="Electronics Engineering" {{ old('department') == 'Electronics Engineering' ? 'selected' : '' }}>Electronics Engineering</option>
                                    <option value="Computer Engineering" {{ old('department') == 'Computer Engineering' ? 'selected' : '' }}>Computer Engineering</option>
                                </select>
                                @error('department')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-3 col-form-label text-md-start">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required placeholder="Email Address" autocomplete="email">

                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-3 col-form-label text-md-start">{{ __('Password') }}</label>

                            <div class="col-md-6 position-relative">
                                <input id="password" type="password" class="form-control" name="password" required placeholder="Password" autocomplete="new-password">
                                <span class="password-toggle" id="password-toggle" toggle="#password">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-3 col-form-label text-md-start">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6 position-relative">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="Confirm Password" autocomplete="new-password">
                                <span class="password-toggle" id="password-confirm-toggle" toggle="#password-confirm">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-12 text-md-end">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $(".password-toggle").click(function() {
            var input = $(this).attr("toggle");
            if ($(input).attr("type") == "password") {
                $(input).attr("type", "text");
                $(this).find("i").removeClass("bi-eye-slash").addClass("bi-eye");
            } else {
                $(input).attr("type", "password");
                $(this).find("i").removeClass("bi-eye").addClass("bi-eye-slash");
            }
        });

        $('#registerForm').submit(function(e) {
            e.preventDefault();

            // Validate and handle registration
            if (validate()) {
                register(); // Separate function for AJAX call
            }
        });

         function validate() {
            let isValid = true;
            let errorMessages = [];

            // Password validation
            let password = $('#password').val();
            let confirmPassword = $('#password-confirm').val();

            if (password.length < 8) {
                isValid = false;
                errorMessages.push('Password must be at least 8 characters long.');
            }
            if (!/[a-z]/.test(password)) {
                isValid = false;
                errorMessages.push('Password must contain at least one lowercase letter.');
            }
            if (!/[A-Z]/.test(password)) {
                isValid = false;
                errorMessages.push('Password must contain at least one uppercase letter.');
            }
            if (password !== confirmPassword) {
                isValid = false;
                errorMessages.push('Passwords do not match.');
            }

              // Email validation
            let email = $('#email').val();
            if (!/@jru\.edu$/.test(email) && !/@my\.jru\.edu$/.test(email)) {
                isValid = false;
                errorMessages.push('Email must end with @jru.edu or @my.jru.edu.');
            }


            if (errorMessages.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errorMessages.join('<br>'),
                });
                return false;
            }
            return true;
        }

        function register() {
            $.ajax({
                url: "{{ route('register') }}",
                type: "POST",
                data: $('#registerForm').serialize(),
                dataType: 'json', // Expect JSON response
                beforeSend: function() {
                    $('#submitBtn').prop('disabled', true).text('Registering...');
                },
                success: function(response) {
                    Swal.fire(
                        'Success',
                        response.message,
                        'success'
                    ).then(() => {
                        window.location.href = "{{ route('products.index') }}";
                    });
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var errorString = '';
                        $.each(errors, function(key, value) {
                            errorString += value + '<br>';
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: errorString,
                        });

                    } else {
                        Swal.fire(
                            'Error!',
                            'There was an error with the submition',
                            'error'
                        )
                    }
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Register');
                }
            });
        }
    });
</script>

<style>
    .password-toggle {
        position: absolute;
        top: 50%;
        right: 20px;
        transform: translateY(-50%);
        cursor: pointer;
    }
</style>
@endsection