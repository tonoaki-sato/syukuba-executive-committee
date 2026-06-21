@extends('layouts.app')

@section('title', '会議の新規スケジュール')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card p-4 shadow-sm border-0 my-3">
            <div class="mb-4 border-bottom pb-2">
                <h3 class="fw-bold text-primary-color mb-1">会議スケジュールの新規登録</h3>
                <p class="text-muted small">新しく会議の予定を登録します。登録と同時に、会議タイプに応じた参加資格者に向けた出欠管理シートが自動生成されます。</p>
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

            <form action="{{ route('meetings.store') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                
                <!-- 対象年 (非表示送信 or 選択) -->
                <div class="mb-3">
                    <label for="fiscal_year" class="form-label fw-semibold small">活動年（開催年） <span class="text-danger">*</span></label>
                    <input type="number" name="fiscal_year" id="fiscal_year" class="form-control border-secondary-subtle" 
                           value="{{ old('fiscal_year', $activeYear) }}" min="2000" max="2100" required>
                    <div class="form-text small">通常は、ヘッダーで選択中の年が自動セットされます。</div>
                </div>

                <!-- 会議タイプ -->
                <div class="mb-3">
                    <label for="type" class="form-label fw-semibold small">会議タイプ <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select border-secondary-subtle" required>
                        <option value="board" {{ old('type') == 'board' ? 'selected' : '' }}>幹事会（幹事のみ自動アサイン）</option>
                        <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>総会（全正式会員を自動アサイン）</option>
                        <option value="subcommittee" {{ old('type') == 'subcommittee' ? 'selected' : '' }}>部会（全正式会員を自動アサイン）</option>
                    </select>
                </div>

                <!-- 会議名 -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold small">会議名 <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control border-secondary-subtle" 
                           value="{{ old('name') }}" placeholder="例：第4回 ござ市部会、7月度定例幹事会" required>
                </div>

                <!-- 開催日時 -->
                <div class="mb-3">
                    <label for="held_at" class="form-label fw-semibold small">開催日時 <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="held_at" id="held_at" class="form-control border-secondary-subtle" 
                           value="{{ old('held_at') }}" required>
                </div>

                <!-- 開催場所 -->
                <div class="mb-3">
                    <label for="location" class="form-label fw-semibold small">開催場所 <span class="text-danger">*</span></label>
                    <input type="text" name="location" id="location" class="form-control border-secondary-subtle" 
                           value="{{ old('location', '実行委員会事務所') }}" required>
                </div>

                <!-- アジェンダ -->
                <div class="mb-4">
                    <label for="agenda" class="form-label fw-semibold small">アジェンダ・議題内容</label>
                    <textarea name="agenda" id="agenda" rows="5" class="form-control border-secondary-subtle" 
                              placeholder="1. 前回議事録の確認&#10;2. ござ市区画割りについて&#10;3. その他連絡事項">{{ old('agenda') }}</textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('meetings.index') }}" class="btn btn-outline-secondary me-md-2 px-4">キャンセル</a>
                    <button type="submit" class="btn btn-primary px-5">スケジュールを確定</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
