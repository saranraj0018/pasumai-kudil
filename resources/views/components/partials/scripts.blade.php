 <script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
 <script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
 <script src="{{ asset('admin/js/adminlte.min.js') }}"></script>
 <script src="{{ asset('admin/js/custom.js') }}"></script>

 <script>
     $.ajaxSetup({
         headers: {
             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
         }
     });
 </script>
