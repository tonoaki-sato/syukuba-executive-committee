@extends('layouts.app')

@section('title', '新規登録申請')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card p-4 shadow-sm border-0 my-3">
            <div class="mb-4">
                <h3 class="fw-bold text-primary-color mb-1">新規実行委員 登録申請</h3>
                <p class="text-muted">保土ケ谷宿場まつり実行委員会への参加申請（仮登録）を行います。すべての情報はシステム管理者のみに開示され、安全に保管されます。</p>
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

            <form action="{{ route('register') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">1. 基本情報</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold small">氏名（漢字） <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control border-secondary-subtle" value="{{ old('name') }}" placeholder="宿場 太郎" required>
                    </div>
                    <div class="col-md-6">
                        <label for="name_kana" class="form-label fw-semibold small">氏名（かな） <span class="text-danger">*</span></label>
                        <input type="text" name="name_kana" id="name_kana" class="form-control border-secondary-subtle" value="{{ old('name_kana') }}" placeholder="しゅくば たろう" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">メールアドレス（ログインID） <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control border-secondary-subtle" value="{{ old('email') }}" placeholder="tarou@example.com" required>
                    <div class="form-text small">承認完了後にこのアドレスがログインIDとなります。</div>
                </div>



                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">2. 身元と紹介関係（出自の明確化）</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="profession" class="form-label fw-semibold small">本業・職業 <span class="text-danger">*</span></label>
                        <input type="text" name="profession" id="profession" class="form-control border-secondary-subtle" value="{{ old('profession') }}" placeholder="自営業、電気技術会社、公務員など" required>
                    </div>
                    <div class="col-md-6">
                        <label for="affiliation" class="form-label fw-semibold small">所属団体・会社名</label>
                        <input type="text" name="affiliation" id="affiliation" class="form-control border-secondary-subtle" value="{{ old('affiliation') }}" placeholder="〇〇町内会、〇〇青年会など (任意)">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="referrer_id" class="form-label fw-semibold small">紹介者（既存の実行委員から選択）</label>
                    <select name="referrer_id" id="referrer_id" class="form-select border-secondary-subtle">
                        <option value="">-- 紹介者を選択（該当者がいない場合は下記に入力） --</option>
                        @foreach ($activeMembers as $member)
                            <option value="{{ $member->id }}" {{ old('referrer_id') == $member->id ? 'selected' : '' }}>
                                {{ $member->name }} 様 （本業: {{ $member->profession }}）
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3" id="referrer-text-block">
                    <label for="referrer_text" class="form-label fw-semibold small">紹介者名（上記リストにいない場合のみ記入）</label>
                    <input type="text" name="referrer_text" id="referrer_text" class="form-control border-secondary-subtle" value="{{ old('referrer_text') }}" placeholder="紹介者の氏名を記入してください">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small d-block">得意分野（複数選択可）</label>
                    <div class="row">
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="電気工事" id="skill-electricity" {{ is_array(old('skills')) && in_array('電気工事', old('skills')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-electricity">電気工事・配線</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="調理・衛生" id="skill-cook" {{ is_array(old('skills')) && in_array('調理・衛生', old('skills')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-cook">調理・食品衛生</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="設営・運搬" id="skill-setup" {{ is_array(old('skills')) && in_array('設営・運搬', old('skills')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-setup">テント設営・力仕事</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="音響・映像" id="skill-audio" {{ is_array(old('skills')) && in_array('音響・映像', old('skills')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-audio">音響・マイク・機材</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="広報・デザイン" id="skill-design" {{ is_array(old('skills')) && in_array('広報・デザイン', old('skills')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-design">チラシ・広報・デザイン</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="事務・会計" id="skill-office" {{ is_array(old('skills')) && in_array('事務・会計', old('skills')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-office">事務処理・会計業務</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="IT・システム" id="skill-system" {{ is_array(old('skills')) && in_array('IT・システム', old('skills')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-system">IT・システム</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skills[]" value="雑用・その他" id="skill-others" {{ is_array(old('skills')) && in_array('雑用・その他', old('skills')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="skill-others">雑用・その他</label>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">3. LINE アカウント連携（「誰？」問題防止用）</h5>

                <div class="mb-4">
                    <label for="line_display_name" class="form-label fw-semibold small">LINEのグループ内表示名 <span class="text-danger">*</span></label>
                    <input type="text" name="line_display_name" id="line_display_name" class="form-control border-secondary-subtle" value="{{ old('line_display_name') }}" placeholder="例：たろう＠電気屋、T.Shukuba など" required>
                    <div class="form-text small text-danger fw-semibold">※LINEグループ内で実際に発言している際のアカウント名を入力してください。LINEでの発言者が誰であるか実名と紐付けるために必要となります。</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary me-md-2 px-4">戻る</a>
                    <button type="submit" class="btn btn-primary px-5">登録申請を送信</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
