<script>
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        background: '#1f2937',
        color:'#FFFFFF',
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        background: '#1f2937',
        color:'#FFFFFF',
    });
    @endif

    @if(session('warning'))
    Swal.fire({
        icon: 'warning',
        title: 'Warning!',
        text: '{{ session('warning') }}',
        background: '#1f2937',
        color:'#FFFFFF',
    });
    @endif
</script>
