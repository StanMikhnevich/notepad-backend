<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between text-xl py-8">
            <div class="flex space-x-4 justify-between py-2">
                <h2 class="font-semibold text-2xl leading-10 text-gray-500">{{ $note->title }}</h2>

                @if($note->private)
                    <i class="mdi mdi-lock text-gray-500 my-1"></i>
                @endif

            </div>

                <div class="flex space-x-4 py-2">
                    <span>{{ $note->user_id == auth()->id() ? 'You' : $note->user->name }}</span>

                    <span class="float-end ms-3 text-secondary text-right text-xl font-half">
                        {{ $note->created_at->diffForHumans() }}<br>{{ $note->updated_at->diffForHumans() }}
                    </span>


                @auth
                        @if(($note->user_id ?? '') == (auth()->user()->id ?? ''))
                            <div class="w-12 h-8 bg-white rounded-lg no-underline text-center shadow-md cursor-pointer"
                                 data-modal-toggle="modal" data-modal-target="#NoteShareModal">
                                <i class="mdi mdi-share"></i>
                            </div>

                            <a href="{{ route('notes.edit', $note->uid) }}" class="w-12 h-8 bg-white rounded-lg no-underline text-center shadow-md cursor-pointer">
                                <i class="mdi mdi-pencil"></i>
                            </a>
                        @endif

{{--                        <div class="w-12 h-8 bg-white rounded-lg no-underline text-center shadow-md cursor-pointer"--}}
{{--                             data-modal-toggle="modal" data-modal-target="#NoteCreateModal">--}}
{{--                            <i class="mdi mdi-plus"></i>--}}
{{--                        </div>--}}
                    @endauth

                </div>

        </div>


    </x-slot>


    <div id="NoteItem">

        <div class="bg-white mb-10 p-6 shadow-md rounded-lg note-item">

            <div class="note-text prose max-w-full">
                {!! $note->text_md !!}
            </div>

            @if($note->hasAttachments())
                <div class="note-attachments text-gray-500">

                    <hr class="my-8">
                    <h5 class="font-semibold mb-4">Attachments</h5>
                    <div id="NoteItemAttachments" class="flex space-x-4">
                        @foreach ($note->attachments as $file)
                        <a href="{{ asset('storage/' . $file->path) }}" target="_blank" data-fancybox="gallery" data-type="iframe"
                            class="fancybox iframe bg-gray-100 px-4 py-2 rounded-full shadow no-underline font-medium text-gray-500">
                            <i class="mdi mdi-attachment mr-2"></i> {{ $file->original }}
                        </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        @if($note->hasShared() && ($note->user_id == (auth()->id() ?? '')))
        <div class="note-shares text-gray-500">
            <h5 class="font-semibold mb-4">Shares</h5>
            <div id="NoteItemUsers" class="flex space-x-4 text-gray-500 text-xs">
                @foreach ($note->users as $sharing)
                <span class="bg-white px-4 py-2 rounded-full shadow no-underline font-medium">
                    <i class="mdi mdi-account-outline mr-2"></i> {{ $sharing->name }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        @if(($note->user_id ?? '') == (auth()->user()->id ?? ''))
            @include('notes._share')
        @endif
    </div>

</x-app-layout>
