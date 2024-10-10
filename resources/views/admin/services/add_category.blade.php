@extends('admin.layouts.sidebar')

@push('start-style')
    <link href="{{ asset('assets/assets/css/scrollspyNav.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/file-upload/file-upload-with-preview.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
@push('start-script')
    <script src="{{ asset('assets/assets/js/scrollspyNav.js') }}"></script>
    <script src="{{ asset('assets/plugins/file-upload/file-upload-with-preview.min.js') }}"></script>

    <script>
        //First upload
        var firstUpload = new FileUploadWithPreview('myFirstImage')
        //Second upload
        var secondUpload = new FileUploadWithPreview('mySecondImage')
    </script>
@endpush

@section('content')
    <div id="content" class="main-content">
        <div class="layout-px-spacing">

            <div class="row layout-top-spacing">

                <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                    <div class="widget-content widget-content-area br-6">


                        <form style="padding:5rem;" action="{{ route('add_category') }}" method="post"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="form-group col col-lg-6">
                                    <label for="exampleInputEmail1">name</label>
                                    <input type="text" class="form-control" name="name" id="exampleInputEmail1"
                                        aria-describedby="emailHelp" placeholder="Enter name">
                                </div>
                                <div class="form-group col col-lg-6">
                                    <label for="exampleInputEmail1">description</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" name="description"
                                        aria-describedby="emailHelp" placeholder="Enter description">
                                </div>
                                <div class="form-group col col-lg-6">
                                    <label for="exampleInputEmail1">Parent</label>
                                    <select class="form-control  mb-3" name="parent_id" aria-label="Default select example">
                                        <option selected disabled value>Select Parent</option>
                                        @foreach ($categories as $cat)
                                            <option value={{ $cat->id }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>

                                </div>


                                <div class="form-group col col-lg-6">
                                    <label for="exampleInputPassword1">slug</label>
                                    <input type="text" class="form-control" id="exampleInputPassword1" name="slug"
                                        placeholder="Slug should be unique">
                                </div>

                                <div class="form-group col col-lg-12">
                                    <div class="custom-file-container" data-upload-id="myFirstImage">
                                        <label>Upload (Image) <a href="javascript:void(0)"
                                                class="custom-file-container__image-clear" title="Clear Image">x</a></label>
                                        <label class="custom-file-container__custom-file">
                                            <input type="file"
                                                class="custom-file-container__custom-file__custom-file-input" name="image"
                                                accept="image/*">
                                            {{-- <input type="hidden" name="MAX_FILE_SIZE" value="10485760" /> --}}
                                            <span class="custom-file-container__custom-file__custom-file-control"></span>
                                        </label>
                                        <div class="custom-file-container__image-preview"></div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary col col-lg-2">Submit</button>
                            </div>
                        </form>


                    </div>
                </div>

            </div>

        </div>
        <div class="footer-wrapper">
            <div class="footer-section f-section-1">
                <p class="">Copyright © 2021 <a target="_blank">Saif Ali</a>, All
                    rights reserved.</p>
            </div>
            <div class="footer-section f-section-2">
                <p class="">Coded with <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-heart">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                        </path>
                    </svg></p>
            </div>
        </div>
    </div>
@endsection
