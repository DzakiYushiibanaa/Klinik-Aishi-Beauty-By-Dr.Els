<x-guest-layout title="Forum Konsultasi">
    <div class="min-h-screen bg-[#FAFAFA] pb-20">

        @include('forum._partials._hero')

        @include('forum._partials._search_bar')

        <section class="mx-auto w-full max-w-5xl px-4 py-8 sm:py-12">
            <div class="space-y-4">
                @forelse($questions as $question)
                    
                    @include('forum._partials._question_card')
                @empty
                    @include('forum._partials._empty_state')
                @endforelse
            </div>

            @if($questions->hasPages())
                <div class="mt-8">
                    {{ $questions->links() }}
                </div>
            @endif
        </section>

    </div>
</x-guest-layout>