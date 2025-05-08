<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $lead->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-white">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs mb-4" id="LeadsTabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="details-tab" data-bs-toggle="tab" href="#details">Details</a></li>
                            <li class="nav-item"><a class="nav-link" id="logs-tab" data-bs-toggle="tab" href="#logs">Logs</a></li>
                            <li class="nav-item"><a class="nav-link" id="comments-tab" data-bs-toggle="tab" href="#comments">Comments</a></li>
                            <li class="nav-item"><a class="nav-link" id="description-tab" data-bs-toggle="tab" href="#description">Notes</a></li>
                            <li class="nav-item"><a class="nav-link" id="properties-tab" data-bs-toggle="tab" href="#properties">Properties</a></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="details">
                                <div class="lead-details-container">
                                    @if($lead->name)
                                        <p class="lead-detail"><i class="bi bi-person"></i> <strong>Name:</strong> {{ $lead->name }}</p>
                                    @endif

                                    @if($lead->email)
                                        <p class="lead-detail"><i class="bi bi-envelope"></i> <strong>Email:</strong> {{ $lead->email }}</p>
                                    @endif

                                    @if($lead->phone)
                                        <p class="lead-detail"><i class="bi bi-telephone"></i> <strong>Phone:</strong> {{ $lead->phone }}</p>
                                    @endif

                                    @if($lead->last_contact)
                                        <p class="lead-detail"><i class="bi bi-telephone-fill"></i> <strong>Last contact:</strong> {{ $lead->last_contact }}</p>
                                    @endif

                                    @if(!is_null($lead->has_company))
                                        <p class="lead-detail"><i class="bi bi-building"></i> <strong>Is Company:</strong> <x-badge :color="$lead->has_company ? 'green' : 'red'">{{ $lead->has_company ? 'Yes' : 'No' }}</x-badge></p>
                                    @endif

                                    @if($lead->company_name)
                                        <p class="lead-detail"><i class="bi bi-building"></i> <strong>Company Name:</strong> {{ $lead->company_name }}</p>
                                    @endif

                                    @if($lead->company_email)
                                        <p class="lead-detail"><i class="bi bi-envelope"></i> <strong>Company Email:</strong> {{ $lead->company_email }}</p>
                                    @endif

                                    @if($lead->cui)
                                        <p class="lead-detail"><i class="bi bi-file-earmark-text"></i> <strong>CUI:</strong> {{ $lead->cui }}</p>
                                    @endif

                                    @if($lead->company_address)
                                        <p class="lead-detail"><i class="bi bi-geo-alt"></i> <strong>Company Address:</strong> {{ $lead->company_address }}</p>
                                    @endif

                                    @if($lead->cnp)
                                        <p class="lead-detail"><i class="bi bi-card-list"></i> <strong>CNP:</strong> {{ $lead->cnp }}</p>
                                    @endif

                                    @if($lead->date_of_birth)
                                        <p class="lead-detail"><i class="bi bi-calendar"></i> <strong>Date of Birth:</strong> {{ $lead->date_of_birth }}</p>
                                    @endif

                                    <p class="lead-detail"><i class="bi bi-geo-alt"></i> <strong>City:</strong> {{ $lead->city }}, {{ $lead->county }}</p>

                                    @if($lead->priority)
                                        <p class="lead-detail"><i class="bi bi-star"></i> <strong>Priority:</strong> {{ $lead->priority }}</p>
                                    @endif

                                    @if($lead->status)
                                        <p class="lead-detail"><i class="bi bi-check-circle"></i> <strong>Status:</strong> {{ $lead->status }}</p>
                                    @endif

                                    <p class="lead-detail"><i class="bi bi-clock"></i> <strong>Created At:</strong> {{ $lead->created_at }}</p>

                                    <p class="lead-detail"><i class="bi bi-clock"></i> <strong>Updated At:</strong> {{ $lead->updated_at }}</p>

                                    {{--  <p><i class="bi bi-paperclip"></i> <strong>Document:</strong> {{ $lead->doc_attachment }}</p> --}}
                                </div>
                            </div>
                            <div class="tab-pane fade" id="description">
                                <div class="lead-description-container">
                                    <h5>Notes</h5>
                                    @if($lead->notes)
                                        <div class="lead-description">
                                            <p>{{ $lead->notes }}</p>
                                        </div>
                                    @else
                                        <p>No notes valabile for this lead.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane fade" id="logs">
                                <div class="logs-container">
                                    @if($activities->isEmpty())
                                        <p>No activites valabile for this lead.</p>
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
                                    @if($lead->comments->isEmpty())
                                        <p>No comments valabile for this lead.</p>
                                    @else
                                        @foreach($lead->comments as $comment)
                                            <div class="comment mb-2">
                                                <strong>{{ $comment->user->name }}</strong>
                                                <p>{{ $comment->comment }}</p>
                                                <small>Posted at: {{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="add-comment-container mt-4">
                                        <form action="{{ route('comments.store', ['commentable_type' => 'App\Models\Lead', 'commentable_id' => $lead->id]) }}" method="POST">
                                            @csrf
                                            <x-textarea label="Leave a comment" name="comment" required>{{ old('comment') }}</x-textarea>
                                            <x-input-error for="comment" />
                                            <x-primary-button>Submit Comment</x-primary-button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="properties">
                                <div class="lead-properties-container">
                                    @if($lead->properties->isEmpty())
                                        <p>No properties valabile for this lead.</p>
                                    @else
                                        <x-table>
                                            <x-slot name="thead">
                                                <th class="px-6 py-3">Photo</th>
                                                <th class="px-6 py-3">Name</th>
                                                <th class="px-6 py-3">Price</th>
                                                <th class="px-6 py-3">Type</th>
                                                <th class="px-6 py-3">Transaction</th>
                                                <th class="px-6 py-3">City</th>
                                                <th class="px-6 py-3">Status</th>
                                                <th class="px-6 py-3">Actions</th>
                                            </x-slot>

                                            @foreach ($lead->properties as $property)
                                                <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-700" style="{{ $property->locked_by_user_id ? 'background-color: #897e7f;' : '' }}">
                                                    <td class="px-6 py-4">
                                                        @if ($property->images->isNotEmpty())
                                                            <img src="{{ asset('storage/' . $property->images->first()->path) }}" alt="Property Image" class="w-16 h-16 object-cover rounded">
                                                        @else
                                                            <span class="text-gray-500">No image</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 font-medium">{{ $property->name }}</td>
                                                    <td class="px-6 py-4">
                                                    <span class="font-semibold {{ $property->promoted ? 'text-green-600' : '' }}">
                                                        €{{ number_format($property->price, 2) }}
                                                    </span>
                                                        @if ($property->promoted)
                                                            <span class="ml-2 px-2 py-1 text-xs text-white bg-green-500 rounded-full">Promoted</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">{{ ucfirst($property->type) }}</td>
                                                    <td class="px-6 py-4">{{ ucfirst($property->tranzaction) }}</td>
                                                    <td class="px-6 py-4">{{ $property->city }}</td>
                                                    <td class="px-6 py-4">
                                                        <x-badge :color="$property->availability_status === 'available' ? 'green' : ($property->availability_status === 'reserved' ? 'yellow' : 'red')">
                                                            {{ ucfirst($property->availability_status) }}
                                                        </x-badge>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="d-flex justify-between mb-2">
                                                            <x-link-primary-button href="{{ route('properties.show', $property->id) }}">
                                                                {{ __('Show') }}
                                                            </x-link-primary-button>
                                                            <x-link-primary-button href="{{ route('properties.edit', $property->id) }}">
                                                                {{ __('Edit') }}
                                                            </x-link-primary-button>
                                                        </div>
                                                        <div class="text-center">
                                                            <form action="{{ route('properties.destroy', $property->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                                                            </form>
                                                        </div>
                                                    </td>
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
</x-app-layout>
