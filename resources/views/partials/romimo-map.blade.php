<script>
    document.getElementById('openRomimoMapBtn').addEventListener('click', () => {
        const propertyId = {{ $property->id }};
        Swal.fire({
            title: 'Selectează locația pe hartă',
            html: `
                <div id="swalMap" style="height: 300px; margin-bottom: 15px;"></div>
                <div>
                    <p>ATENȚIE! Completați câmpurile proprietății înainte de import. Verificați ca tipul să fie specific (ex.: Apartament, nu Apartament/Garsonieră) și asigurați-vă că toate datele obligatorii sunt completate (inclusiv numărul de telefon din cont/poza de profil). De asemenea, datele proprietății trebuie completate integral.</p>
                </div>
                `,
            width: 600,
            didOpen: () => {
                const map = L.map('swalMap').setView([45.657975, 25.601198], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                const marker = L.marker([45.657975, 25.601198], { draggable: true }).addTo(map);

                window.selectedLatLng = marker.getLatLng();

                marker.on('dragend', function() {
                    window.selectedLatLng = marker.getLatLng();
                });

                map.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    window.selectedLatLng = e.latlng;
                });
            },
            preConfirm: () => {
                return {
                    latitude: window.selectedLatLng.lat,
                    longitude: window.selectedLatLng.lng,
                    property_id: propertyId,
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route('romimo.create') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(result.value)
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.error || 'Unknown error occurred.');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        Swal.fire('Success!', 'Property posted on Romimo!', 'success');
                    })
                    .catch(error => {
                        console.error(error);
                        Swal.fire('Error', error.message, 'error');
                    });
            }
        });
    });
</script>
