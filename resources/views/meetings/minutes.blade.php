@extends('layouts.app')

@section('title', '議事録の編集: ' . $meeting->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card p-4 shadow-sm border-0 my-3">
            <div class="mb-4 border-bottom pb-2">
                <h3 class="fw-bold text-primary-color mb-1">議事録・ホワイトボード写真の登録</h3>
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

            <form action="{{ route('meetings.minutes', $meeting) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- 議事録本文 (文章化) -->
                <div class="mb-4">
                    <label for="minutes" class="form-label fw-bold text-secondary-color">📝 議事録（決定事項・書き起こし）</label>
                    <textarea name="minutes" id="minutes" rows="12" class="form-control border-secondary-subtle" 
                              placeholder="決定したことや会議の内容を文章化して記述してください。&#10;例：&#10;【決定事項】&#10;・ござ市の出店料は一律5,000円に決定しました。&#10;・通行止めの範囲は例年通りとします。&#10;&#10;【議論内容】&#10;・衛生管理に関して検便の実施期限を7月末に設定しました。" required>{{ old('minutes', $meeting->minutes) }}</textarea>
                    <div class="form-text small">スマホで撮ったホワイトボード写真の内容を読み取り、分かりやすくテキストに起こしてください。</div>
                </div>

                <!-- ホワイトボード画像アップロード -->
                <div class="mb-4">
                    <label for="whiteboard_images" class="form-label fw-bold text-secondary-color">📷 ホワイトボード写真の追加</label>
                    <input type="file" name="whiteboard_images[]" id="whiteboard_images" class="form-control border-secondary-subtle" multiple accept="image/*">
                    <div class="form-text small">ホワイトボードを撮影した写真を複数選択できます。(JPG, PNG形式、1枚最大5MBまで)</div>
                </div>

                <!-- 登録済みの画像表示 -->
                @if(is_array($meeting->whiteboard_images) && count($meeting->whiteboard_images) > 0)
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small">現在登録済みの画像 (追加するとこれらに上乗せされます)</label>
                        <div class="row row-cols-2 row-cols-md-4 g-2 bg-light p-3 rounded">
                            @foreach($meeting->whiteboard_images as $image)
                                <div class="col">
                                    <div class="position-relative">
                                        <img src="{{ $image }}" class="img-thumbnail" style="height: 100px; width: 100%; object-fit: cover;">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('meetings.show', $meeting) }}" class="btn btn-outline-secondary me-md-2 px-4">キャンセル</a>
                    <button type="submit" class="btn btn-primary px-5">議事録を保存し、LINE報告を作成</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
