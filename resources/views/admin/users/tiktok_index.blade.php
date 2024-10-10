@extends('admin.layouts.sidebar')

@push('start-style')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/table/datatable/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/table/datatable/dt-global_style.css') }}">
    <link href="{{ asset('assets/assets/css/scrollspyNav.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/file-upload/file-upload-with-preview.min.css') }}" rel="stylesheet"
        type="text/css" />
@endpush
@push('start-script')
    <script src="{{ asset('assets/plugins/table/datatable/datatables.js') }}"></script>
    <script src="{{ asset('assets/assets/js/scrollspyNav.js') }}"></script>
    <script src="{{ asset('assets/plugins/file-upload/file-upload-with-preview.min.js') }}"></script>
    <script>
        //First upload
        var firstUpload = new FileUploadWithPreview('myFirstImage')
        //Second upload
        var secondUpload = new FileUploadWithPreview('mySecondImage')
    </script>
    <script>
        var table = $('#default-ordering').DataTable({
            ajax: "{{ route('get_all_users_students') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'nick_name',
                    name: 'nick_name'
                },

                {
                    data: 'email',
                    name: 'email'
                },

                {
                    data: 'phone_number',
                    name: 'phone_number'
                },

                {
                    data: 'user_name',
                    name: 'user_name'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                // {
                //     data: 'action',
                //     name: 'action'
                // },


            ],
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            "oLanguage": {
                "oPaginate": {
                    "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                    "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                "sSearchPlaceholder": "Search...",
                "sLengthMenu": "Results :  _MENU_",
            },
            "stripeClasses": [],
            "lengthMenu": [7, 10, 20, 50],
            "pageLength": 7
        });
    </script>


    <script>
        function delete_user(id) {
            swal.fire({
                title: "Are you sure?",
                text: "User will not be deleted! please disable user if needed",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "GET",
                        url: '{{ url('admin/user/delete') }}/' + id,

                        success: function(data) {
                            $('#default-ordering').DataTable().ajax.reload();
                            swal.fire("Success", " User delted successfully", "success");
                        },
                        error: function(error) {
                            Snackbar.show({
                                text: 'Server error',
                                pos: 'top-right',
                                actionTextColor: '#fff',
                                backgroundColor: '#e7515a'
                            });
                        }
                    });
                } else {
                    Swal.fire('Student not deleted', '', 'info')
                }
            });


        }

        function change_status(id) {
            swal.fire({
                title: "Are you sure?",
                text: "User may not be able to use app any more if disabled!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, change it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "GET",
                        url: '{{ url('admin/user/status') }}/' + id,

                        success: function(data) {
                            $('#default-ordering').DataTable().ajax.reload();
                            swal.fire("Success", " Student status change successfully", "success");
                        },
                        error: function(error) {
                            Snackbar.show({
                                text: 'Server error',
                                pos: 'top-right',
                                actionTextColor: '#fff',
                                backgroundColor: '#e7515a'
                            });
                        }
                    });
                }
            });


        }

        function edit_product(id) {
            $.ajax({
                type: "GET",
                url: '{{ url('admin/product/get') }}/' + id,

                success: function(data) {
                    $('#product_modal').modal('show');
                    $('#product_id').val(data.id);
                    $('#name').val(data.name);
                    $('#description').val(data.description);
                    $('#price').val(data.price);
                    $('#quantity').val(data.quantity);
                    $('#features').val(data.features);
                    $('#color').val(data.color);
                    $('#size').val(data.size);
                },
                error: function(error) {
                    Snackbar.show({
                        text: 'Server error',
                        pos: 'top-right',
                        actionTextColor: '#fff',
                        backgroundColor: '#e7515a'
                    });
                }
            });

        }
    </script>
@endpush

@section('content')
    <div id="content" class="main-content">
        <div class="layout-px-spacing">

            <div class="row layout-top-spacing">
                {{-- <a type="button" class="btn btn-warning ml-3 mb-3" href="{{ route('add_products_index') }}">Add
                    Product</a> --}}
                <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                    <div class="widget-content widget-content-area br-6">

                        <table id="default-ordering" class="table dt-table-hover" style="width:100%">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>UserName</th>
                                    <th>Status</th>
                                    {{-- <th class="no-content">Actions</th> --}}
                                </tr>
                            </thead>

                        </table>
                    </div>
                </div>

            </div>

        </div>


        <div class="footer-wrapper">
            <div class="footer-section f-section-1">
                <p class="">Copyright © 2021 <a target="_blank">Anawakhti</a>, All
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
