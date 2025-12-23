@php
    $canDelete = auth()->check() && (auth()->user()->isAdmin() || $comment->report->user_id === auth()->id() || $comment->user_id === auth()->id());
@endphp

<div class="comment-item border-l-2 border-gray-200 pl-4 {{ $level > 0 ? 'ml-8' : '' }}">
    <div class="flex justify-between items-start mb-2">
        <div>
            <p class="font-semibold text-gray-900">{{ $comment->user->nickname }}</p>
            <p class="text-xs text-gray-500">{{ $comment->created_at->format('d.m.Y H:i') }}</p>
        </div>
        @if($canDelete)
            <form action="{{ route('comments.destroy', $comment) }}" method="POST" 
                  onsubmit="return confirm('Вы уверены, что хотите удалить этот комментарий?');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">
                    Удалить
                </button>
            </form>
        @endif
    </div>
    
    <p class="text-gray-700 mb-3 whitespace-pre-wrap">{{ $comment->content }}</p>

    <!-- Форма ответа (только для авторизованных с доступом) -->
    @auth
        @if(\Illuminate\Support\Facades\Gate::allows('view', $comment->report))
            <button type="button" 
                    onclick="toggleReplyForm({{ $comment->id }})" 
                    class="text-blue-600 hover:text-blue-800 text-sm mb-2">
                Ответить
            </button>

            <form id="reply-form-{{ $comment->id }}" 
                  action="{{ route('comments.store', $comment->report) }}" 
                  method="POST" 
                  class="hidden mb-4">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <div class="mb-2">
                    <textarea name="content" 
                              rows="2" 
                              class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                              placeholder="Напишите ответ..."
                              required></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                        Отправить
                    </button>
                    <button type="button" 
                            onclick="toggleReplyForm({{ $comment->id }})" 
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded text-sm">
                        Отмена
                    </button>
                </div>
            </form>
        @endif
    @endauth

    <!-- Вложенные комментарии -->
    @if($comment->replies->count() > 0)
        <div class="mt-3">
            @foreach($comment->replies->sortBy('created_at') as $reply)
                @include('comments.partials.comment', ['comment' => $reply, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>

<script>
    function toggleReplyForm(commentId) {
        const form = document.getElementById('reply-form-' + commentId);
        form.classList.toggle('hidden');
    }
</script>









