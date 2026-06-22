@extends('layouts.app')

@section('title', 'プロフィール編集')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card p-4 shadow-sm border-0 my-3">
            <div class="mb-4 border-bottom pb-2">
                <h3 class="fw-bold text-primary-color mb-1">プロフィールを編集する</h3>
                <p class="text-muted">あなたのアカウント情報を編集します。メールアドレスはログインIDとして共通で使われます。</p>
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

            <form action="{{ route('mypage.update') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">1. 基本情報</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold small">氏名（漢字） <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control border-secondary-subtle" value="{{ old('name', $user->name) }}" placeholder="宿場 太郎" required>
                    </div>
                    <div class="col-md-6">
                        <label for="name_kana" class="form-label fw-semibold small">氏名（かな） <span class="text-danger">*</span></label>
                        <input type="text" name="name_kana" id="name_kana" class="form-control border-secondary-subtle" value="{{ old('name_kana', $user->name_kana) }}" placeholder="しゅくば たろう" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">メールアドレス（ログインID） <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control border-secondary-subtle" value="{{ old('email', $user->email) }}" placeholder="tarou@example.com" required>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">2. 身元と得意分野（出自の明確化）</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="profession" class="form-label fw-semibold small">本業・職業 <span class="text-danger">*</span></label>
                        <input type="text" name="profession" id="profession" class="form-control border-secondary-subtle" value="{{ old('profession', $user->profession) }}" placeholder="自営業、電気技術会社、公務員など" required>
                    </div>
                    <div class="col-md-6">
                        <label for="affiliation" class="form-label fw-semibold small">所属団体・会社名</label>
                        <input type="text" name="affiliation" id="affiliation" class="form-control border-secondary-subtle" value="{{ old('affiliation', $user->affiliation) }}" placeholder="〇〇町内会、〇〇青年会など (任意)">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small d-block">得意分野（複数選択可）</label>
                    @php
                        $userSkills = is_array($user->skills) ? $user->skills : [];
                    @endphp
                    <div class="row">
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="電気工事" id="skill-electricity" {{ in_array('電気工事', old('skills', $userSkills)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-electricity">電気工事・配線</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="調理・衛生" id="skill-cook" {{ in_array('調理・衛生', old('skills', $userSkills)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-cook">調理・食品衛生</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="設営・運搬" id="skill-setup" {{ in_array('設営・運搬', old('skills', $userSkills)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-setup">テント設営・力仕事</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="音響・映像" id="skill-audio" {{ in_array('音響・映像', old('skills', $userSkills)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-audio">音響・マイク・機材</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="広報・デザイン" id="skill-design" {{ in_array('広報・デザイン', old('skills', $userSkills)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-design">チラシ・広報・デザイン</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="事務・会計" id="skill-office" {{ in_array('事務・会計', old('skills', $userSkills)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-office">事務処理・会計業務</label>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">3. LINE アカウント連携（「誰？」問題防止用）</h5>

                <div class="mb-4">
                    <label for="line_display_name" class="form-label fw-semibold small">LINEのグループ内表示名 <span class="text-danger">*</span></label>
                    <input type="text" name="line_display_name" id="line_display_name" class="form-control border-secondary-subtle" value="{{ old('line_display_name', $user->line_display_name) }}" placeholder="例：たろう＠電気屋、T.Shukuba など" required>
                    <div class="form-text small text-danger fw-semibold">※LINEグループ内で実際に発言している際のアカウント名を入力してください。</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                    <a href="{{ route('mypage') }}" class="btn btn-outline-secondary me-md-2 px-4">キャンセル</a>
                    <button type="submit" class="btn btn-primary px-5">更新する</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
