@extends('layouts.app', ['title' => 'Login', 'classBody' => 'hold-transition login-page'])
@section('body')
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <div style="font-size:24px"><b>Trade</b>Exchange</div>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Silahkan Login terlebih dahulu</p>

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Username" name="username" value="{{ old('username') ?? '' }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user-alt"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Password" name="password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>


                    <div class="social-auth-links text-center mt-2 mb-3">
                        <button type="submit" class="btn btn-block btn-primary">
                            Login
                        </button>
                    </div>
                    <!-- /.social-auth-links -->
                </form>

                <p class="mb-1">
                    <a href="forgot-password.html">I forgot my password</a>
                </p>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.login-box -->
    @if (session()->has('errors'))
        <div id="toastsContainerTopRight" class="toasts-top-right fixed">
            <div class="toast bg-danger fade show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header"><strong class="mr-auto">Error!</strong><small>validasi</small><button data-dismiss="toast" type="button" class="ml-2 mb-1 close" aria-label="Close"><span aria-hidden="true">×</span></button></div>
                <div class="toast-body">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('input[name="username"]').focus();
            $('input[name="username"]').on('input', function() {
                let UpperCase = $(this).val().toUpperCase();
                $(this).val(UpperCase);
            });

        });
    </script>
    
@endpush