<script>
    document.getElementById('openMapBtn').addEventListener('click', function () {
        Swal.fire({
            title: 'Selectează pe hartă',
            html: `
            <iframe id="mapFrame" src="{{ route('showMap') }}" width="100%" height="100%" frameborder="0"></iframe>
            <br>
            <input type="text"  hidden id="punct" placeholder="Punct" class="swal2-input" />
            <input type="text" hidden id="caroiaj" placeholder="Caroiaj" class="swal2-input" />
        `,
            showCancelButton: true,
            confirmButtonText: 'Salvează',
            preConfirm: () => {
                return {
                    punct: document.getElementById('punct').value,
                    caroiaj: document.getElementById('caroiaj').value
                }
            }
        }).then(result => {
            if (result.isConfirmed) {
                console.log('Coordonate:', result.value);
            }
        });
    });
</script>
