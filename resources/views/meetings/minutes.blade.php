@extends('layouts.app')

@section('title', '議事録の編集: ' . $meeting->name)

@section('content')
<script src="/js/meetings/minutes-ai.js" defer></script>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card p-4 shadow-sm border-0 my-3">
            <div class="mb-4 border-bottom pb-2">
                <h3 class="fw-bold text-primary-color mb-1">議事録の登録・編集</h3>
                <p class="text-muted small">
                    対象会議: <span class="fw-bold text-dark">{{ $meeting->name }}</span> (開催日: {{ $meeting->held_at->format('Y-m-d H:i') }})
                </p>
            </div>

            <!-- バリデーションエラー -->
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 📷 AIで議事録下書きを生成 -->
            <div class="card p-3 bg-light border-0 shadow-sm rounded-3 mb-4">
                <label class="form-label fw-bold text-secondary-color mb-2">📷 ホワイトボード画像から議事録下書きを生成 (AI)</label>
                <div class="input-group">
                    <input type="file" id="whiteboard_image_ai" class="form-control border-secondary-subtle" accept="image/*">
                    <button type="button" id="btn-analyze-whiteboard" class="btn btn-secondary px-3" disabled 
                            data-url="{{ route('meetings.minutes.analyze', $meeting) }}">
                        AI解析を実行して下書きを作成
                    </button>
                </div>
                <div class="form-text small mt-1">ホワイトボードの写真をアップロードして解析すると、議事録の下書きが生成されます。※解析された画像は保存されず即座に削除されます。</div>
                <div id="ai-loader" class="d-none mt-2 text-primary-color small fw-semibold">
                    <span class="spinner-border spinner-border-sm me-1 text-primary-color" role="status" aria-hidden="true"></span>
                    画像を解析中...（最大30秒程度かかる場合があります）
                </div>
                <div id="ai-error-message" class="d-none mt-2 alert alert-danger py-2 px-3 small mb-0"></div>
            </div>

            <form action="{{ route('meetings.minutes', $meeting) }}" method="POST">
                @csrf
                
                <!-- 議事録本文 (文章化) -->
                <div class="mb-4">
                    <label for="minutes" class="form-label fw-bold text-secondary-color">📝 議事録（決定事項・書き起こし）</label>
                    <textarea name="minutes" id="minutes" rows="12" class="form-control border-secondary-subtle" 
                              placeholder="決定したことや会議の内容を文章化して記述してください。&#10;例：&#10;【決定事項】&#10;・ござ市の出店料は一律5,000円に決定しました。&#10;・通行止めの範囲は例年通りとします。&#10;&#10;【議論内容】&#10;・衛生管理に関して検便の実施期限を7月末に設定しました。" required>{{ old('minutes', $meeting->minutes) }}</textarea>
                    <div class="form-text small">上のAI解析を利用するか、手動で決定事項や会議内容を入力してください。</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('meetings.show', $meeting) }}" class="btn btn-outline-secondary me-md-2 px-4">キャンセル</a>
                    <button type="submit" class="btn btn-primary px-5">議事録を保存し、LINE報告を作成</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
