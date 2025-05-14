<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $property->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-white">
                <div class="row">
                    <div class="col-md-4">
                        <div class="relative w-full mb-6" style="height: 400px">
                            <div class="swiper swiper-initialized h-100">
                                <div class="swiper-wrapper">
                                    @if ($property->images->isNotEmpty())
                                        @foreach ($property->images as $image)
                                            <div class="swiper-slide">
                                                <img data-fancybox="gallery" src="{{ asset('storage/' . $image->path) }}" class="w-full h-full object-cover rounded-md" alt="Property Image">
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="swiper-slide">
                                            <img src="{{ asset('images/home.png') }}"  alt="Property Image" class="w-full h-full object-cover rounded-md">
                                        </div>
                                    @endif
                                </div>
                                <div class="swiper-pagination"></div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <ul class="nav nav-tabs mb-4" id="propertyTabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="details-tab" data-bs-toggle="tab" href="#details">Details</a></li>
                            <li class="nav-item"><a class="nav-link" id="logs-tab" data-bs-toggle="tab" href="#logs">Logs</a></li>
                            <li class="nav-item"><a class="nav-link" id="comments-tab" data-bs-toggle="tab" href="#comments">Comments</a></li>
                            <li class="nav-item"><a class="nav-link" id="description-tab" data-bs-toggle="tab" href="#description">Description</a></li>
                            <li class="nav-item"><a class="nav-link" id="description-tab" data-bs-toggle="tab" href="#leads">Leads</a></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="details">
                                <div class="property-details-container">
                                    @role('Admin')
                                    <p class="property-detail"><i class="bi bi-person"></i> <strong>Assigned To:</strong> {{ optional($property->user)->name ?? 'None' }}</p>
                                    @endrole

                                    @if($property->name)
                                        <p class="property-detail"><i class="bi bi-house"></i> <strong>Title:</strong> {{ $property->name }}</p>
                                    @endif

                                    @if(!is_null($property->promoted))
                                        <p class="property-detail"><i class="bi bi-megaphone"></i> <strong>Promoted:</strong> <x-badge :color="$property->promoted ? 'green' : 'red'">{{ $property->promoted ? 'Yes' : 'No' }}</x-badge></p>
                                    @endif

                                    @if($property->type)
                                        <p class="property-detail"><i class="bi bi-building"></i> <strong>Type:</strong> {{ ucfirst($property->type) }}</p>
                                    @endif

                                    @if($property->category)
                                        <p class="property-detail"><i class="bi bi-tags"></i> <strong>Category:</strong> {{ ucfirst($property->category) }}</p>
                                    @endif

                                    @if($property->tranzaction)
                                        <p class="property-detail"><i class="bi bi-currency-exchange"></i> <strong>Transaction:</strong> {{ ucfirst($property->tranzaction) }}</p>
                                    @endif

                                    @if($property->price)
                                        <p class="property-detail"><i class="bi bi-currency-euro"></i> <strong>Price:</strong> {{ number_format($property->price, 2) }}</p>
                                    @endif

                                    @if($property->availability_status)
                                        <p class="property-detail"><i class="bi bi-calendar-check"></i> <strong>Available:</strong> {{ $property->availability_status }}</p>
                                    @endif

                                    @if($property->available_from)
                                        <p class="property-detail"><i class="bi bi-calendar"></i> <strong>Available from:</strong> {{ $property->available_from }}</p>
                                    @endif

                                    @if($property->city)
                                        <p class="property-detail"><i class="bi bi-geo-alt"></i> <strong>City:</strong> {{ $property->city }}, {{ $property->county }}</p>
                                    @endif

                                    @if($property->address)
                                        <p class="property-detail"><i class="bi bi-signpost"></i> <strong>Address:</strong> {{ $property->address }}</p>
                                    @endif

                                    @if($property->construction_year)
                                        <p class="property-detail"><i class="bi bi-calendar3"></i> <strong>Construction Year:</strong> {{ $property->construction_year }}</p>
                                    @endif

                                    @if($property->partitioning)
                                        <p class="property-detail"><i class="bi bi-grid-3x3"></i> <strong>Partitioning:</strong> {{ $property->partitioning }}</p>
                                    @endif

                                    @if($property->interior_condition)
                                        <p class="property-detail"><i class="bi bi-brush"></i> <strong>Interior Condition:</strong> {{ $property->interior_condition }}</p>
                                    @endif

                                    @if(!is_null($property->furnished))
                                        <p class="property-detail"><i class="bi bi-sofa"></i> <strong>Furnished:</strong> <x-badge :color="$property->furnished ? 'green' : 'red'">{{ $property->furnished ? 'Yes' : 'No' }}</x-badge></p>
                                    @endif

                                    @if($property->room_numbers)
                                        <p class="property-detail"><i class="bi bi-door-open"></i> <strong>Room Numbers:</strong> {{ $property->room_numbers }}</p>
                                    @endif

                                    @if($property->floor)
                                        <p class="property-detail"><i class="bi bi-stairs"></i> <strong>Floor:</strong> {{ $property->floor }}</p>
                                    @endif

                                    @if($property->total_floors)
                                        <p class="property-detail"><i class="bi bi-building"></i> <strong>Total Floors:</strong> {{ $property->total_floors }}</p>
                                    @endif

                                    @if($property->surface)
                                        <p class="property-detail"><i class="bi bi-arrows-fullscreen"></i> <strong>Surface:</strong> {{ $property->surface }} sqm</p>
                                    @endif

                                    @if($property->usable_area)
                                        <p class="property-detail"><i class="bi bi-rulers"></i> <strong>Usable Area:</strong> {{ $property->usable_area }} sqm</p>
                                    @endif

                                    @if($property->land_area)
                                        <p class="property-detail"><i class="bi bi-tree"></i> <strong>Land Area:</strong> {{ $property->land_area }} sqm</p>
                                    @endif

                                    @if($property->yard_area)
                                        <p class="property-detail"><i class="bi bi-flower1"></i> <strong>Yard Area:</strong> {{ $property->yard_area }} sqm</p>
                                    @endif

                                    @if($property->heating)
                                        <p class="property-detail"><i class="bi bi-thermometer-sun"></i> <strong>Heating:</strong> {{ $property->heating }}</p>
                                    @endif

                                    @if($property->confort)
                                        <p class="property-detail"><i class="bi bi-stars"></i> <strong>Comfort:</strong> {{ $property->confort }}</p>
                                    @endif

                                    @if(!is_null($property->balcony))
                                        <p class="property-detail"><i class="bi bi-aspect-ratio"></i> <strong>Balcony:</strong> <x-badge :color="$property->balcony ? 'green' : 'red'">{{ $property->balcony ? 'Yes' : 'No' }}</x-badge></p>
                                    @endif

                                    @if($property->balcony_area)
                                        <p class="property-detail"><i class="bi bi-rulers"></i> <strong>Balcony Area:</strong> {{ $property->balcony_area }} sqm</p>
                                    @endif

                                    @if(!is_null($property->parking))
                                        <p class="property-detail"><i class="bi bi-car-front"></i> <strong>Parking:</strong> <x-badge :color="$property->parking ? 'green' : 'red'">{{ $property->parking ? 'Yes' : 'No' }}</x-badge></p>
                                    @endif

                                    @if(!is_null($property->garage))
                                        <p class="property-detail"><i class="bi bi-garage"></i> <strong>Garage:</strong> <x-badge :color="$property->garage ? 'green' : 'red'">{{ $property->garage ? 'Yes' : 'No' }}</x-badge></p>
                                    @endif

                                    @if(!is_null($property->elevator))
                                        <p class="property-detail"><i class="bi bi-lift"></i> <strong>Elevator:</strong> <x-badge :color="$property->elevator ? 'green' : 'red'">{{ $property->elevator ? 'Yes' : 'No' }}</x-badge></p>
                                    @endif

                                    @if($property->scraper_link)
                                        <p class="property-detail"><i class="bi bi-webcam"></i> <strong>Link:</strong> <a href="{{$property->scraper_link}}"> {{$property->scraper_link}} </a></p>
                                    @endif
                                </div>
                            </div>
                            <div class="tab-pane fade" id="logs">
                                <div class="logs-container">
                                    @if($activities->isEmpty())
                                        <p>No activites valabile for this property.</p>
                                    @else
                                        @foreach($activities as $activity)
                                            <div class="log-entry">
                                                <div class="log-header">
                                                    <p><strong>{{ $activity->description }}</strong></p>
                                                    <p class="log-user">Changed by: {{ $activity->causer->name ?? 'N/A' }}</p>
                                                    <p class="log-date">At: {{ $activity->created_at->format('Y-m-d H:i:s') }}</p>
                                                </div>

                                                <h5>Changes:</h5>
                                                <ul class="changes-list">
                                                    @foreach($activity->properties['attributes'] as $key => $newValue)
                                                        @if (isset($activity->properties['old'][$key]))
                                                            <li class="change-item">
                                                                <strong>{{ ucfirst($key) }}:</strong>
                                                                From <span class="old-value">{{ $activity->properties['old'][$key] }}</span>
                                                                to <span class="new-value">{{ $newValue }}</span>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane fade" id="comments">
                                <div class="comment-container">
                                    @if($property->comments->isEmpty())
                                        <p>No comments valabile for this property.</p>
                                    @else
                                        @foreach($property->comments as $comment)
                                            <div class="comment mb-2">
                                                <strong>{{ $comment->user->name }}</strong>
                                                <p>{{ $comment->comment }}</p>
                                                <small>Posted at: {{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="add-comment-container mt-4">
                                        <form action="{{ route('comments.store', ['commentable_type' => 'App\Models\Property', 'commentable_id' => $property->id]) }}" method="POST">
                                            @csrf
                                            <x-textarea label="Leave a comment" name="comment" required>{{ old('comment') }}</x-textarea>
                                            <x-input-error for="comment" />
                                            <x-primary-button class="mt-2">Submit Comment</x-primary-button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="description">
                                <div class="property-description-container">
                                    <h5>Description</h5>
                                    @if($property->description)
                                        <div class="property-description">
                                            <p>{{ $property->description }}</p>
                                        </div>
                                    @else
                                        <p>No description valabile for this property.</p>
                                    @endif
                                </div>
                                <div class="property-description-container">
                                    <h5>Details</h5>
                                    @if($property->details)
                                        <div class="property-description">
                                            <p>{{ $property->details }}</p>
                                        </div>
                                    @else
                                        <p>No details valabile for this property.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane fade" id="leads">
                                <div class="property-leads-container">
                                    @if($property->leads->isEmpty())
                                        <p>No leads valabile for this property.</p>
                                    @else
                                        <x-table>
                                            <x-slot name="thead">
                                                <th class="px-6 py-3">Assigned To</th>
                                                <th class="px-6 py-3">Priority</th>
                                                <th class="px-6 py-3">Status</th>
                                                <th class="px-6 py-3">Document</th>
                                            </x-slot>

                                            @foreach ($property->leads as $lead)
                                                <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <td class="px-6 py-4">{{$lead->user->name}}</td>
                                                    <td class="px-6 py-4">
                                                        <x-badge :color="$lead->priority === 'High' ? 'red' : ($lead->priority === 'Medium' ? 'yellow' : 'green')">
                                                            {{ ucfirst($lead->priority) }}
                                                        </x-badge>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <x-badge :color="$lead->status === 'New' ? 'green' : ($lead->status === 'In Progress' ? 'yellow' : ($lead->status === 'Closed' ? 'blue' : 'red'))">
                                                            {{ ucfirst($lead->status) }}
                                                        </x-badge>
                                                    </td>
                                                    @if($lead->doc_attachment)
                                                        <td class="px-6 py4">
                                                            <x-link-primary-button href="{{ asset('storage/' . $lead->doc_attachment) }}" target="_blank">See document</x-link-primary-button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </x-table>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('beforeunload', function () {
            fetch("{{ route('properties.unlock', $property->id) }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
                keepalive: true
            });
        });
    </script>
</x-app-layout>
