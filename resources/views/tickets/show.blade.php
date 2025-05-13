<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $ticket->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-white">
                <div class="ticket-container">
                    <!-- Ticket Information -->
                    <div class="ticket-info mb-6">
                        <p class="ticket-detail"><strong><i class="bi bi-card-text"></i> Title:</strong><span>{{ $ticket->title }}</span></p>
                        <p class="ticket-detail"><strong><i class="bi bi-check-circle"></i> Status:</strong>
                            @if($ticket->status == 'open')
                                <span class="text-green-400">Open</span>
                            @elseif($ticket->status == 'closed')
                                <span class="text-red-400">Closed</span>
                            @else
                                <span class="text-yellow-400">Pending</span>
                            @endif
                        </p>
                        <p class="ticket-detail"><strong><i class="bi bi-person"></i> Created By:</strong> <span>{{ $ticket->user->name }}</span></p>
                        <p class="ticket-detail"><strong><i class="bi bi-calendar-event"></i> Created At:</strong> <span>{{ $ticket->created_at->format('Y-m-d H:i:s') }}</span></p>
                        <p class="ticket-detail"><strong><i class="bi bi-file-earmark-text"></i> Description:</strong><span>{{ $ticket->description }}</span></p>
                    </div>

                    <div class="text-end mb-2">
                        <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-8"></div>
                                <div class="col-md-4">
                                        <x-select class="mb-2 " name="status" label="Status" :options="['open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed']" />
                                        <x-primary-button>Change Status</x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="comment-section mt-2">
                          @if($ticket->comments->isEmpty())
                              <p>No comments valabile for this ticket.</p>
                          @else
                             @foreach($ticket->comments as $comment)
                                  <div class="comment mb-2">
                                        <strong>{{ $comment->user->name }}</strong>
                                        <p>{{ $comment->comment }}</p>
                                        <small>Posted at: {{ $comment->created_at->diffForHumans() }}</small>
                                  </div>
                             @endforeach
                          @endif
                          @if($ticket->status != 'closed')
                             <div class="add-comment-container mt-4">
                                  <form action="{{ route('comments.store', ['commentable_type' => 'App\Models\Ticket', 'commentable_id' => $ticket->id]) }}" method="POST">
                                        @csrf
                                        <x-textarea label="Leave a comment" name="comment" required>{{ old('comment') }}</x-textarea>
                                        <x-input-error for="comment" />
                                        <x-primary-button class="mt-2">Submit Comment</x-primary-button>
                                  </form>
                                 <div class="text-end">
                                     <form action="{{ route('tickets.destroy', $ticket) }}" method="POST">
                                         @csrf
                                         @method('DELETE')
                                         <x-danger-button>Delete Ticket</x-danger-button>
                                     </form>
                                 </div>
                             </div>
                          @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
