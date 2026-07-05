<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserYear;
use Illuminate\Database\Seeder;

class UserMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userData = [
            [
                'id' => '1',
                'name' => '佐藤 外亮',
                'kana' => 'サトウ トノアキ',
                'email' => 'no-10.sato@docomo.ne.jp',
                'email_verified_at' => '2024-08-29 10:12:07',
                'password' => '$2y$12$CXyVlz3j2jslrthX9G70IeapQ2bVZEO6KsntZA0p9rM/YWyBKU7zy',
                'section' => 'manager',
                'reason' => '山形さんの紹介',
                'created_at' => '2024-09-04 07:40:30',
                'updated_at' => '2025-02-04 05:51:35',
            ],
            [
                'id' => '2',
                'name' => '長澤 友希',
                'kana' => 'ナガサワ トモキ',
                'email' => 'nagatomo090257@icloud.com',
                'email_verified_at' => '2025-03-06 20:50:04',
                'password' => '$2y$12$E71GytTY8rKZaFQR/irr1u2dbEX647xbMjy9x9vHj4LnGepwZHlFK',
                'section' => 'normal',
                'reason' => 'ヤスミチ大久保さんのご紹介',
                'created_at' => '2025-03-06 20:50:05',
                'updated_at' => '2025-03-30 15:36:13',
            ],
            [
                'id' => '3',
                'name' => '松木 新太郎',
                'kana' => 'マツキ シンタロウ',
                'email' => 'shintaro.mtk@gmail.com',
                'email_verified_at' => '2025-03-08 18:03:21',
                'password' => '$2y$12$URyICRYeGh8n.6uM/7mls.vwHogZSbW8yDfBfRnbo/nnrccdJm8ay',
                'section' => 'secretary',
                'reason' => '山形さんの紹介',
                'created_at' => '2025-03-08 18:03:22',
                'updated_at' => '2025-03-08 18:07:41',
            ],
            [
                'id' => '4',
                'name' => '岡本 睦良',
                'kana' => 'オカモト ムツヨシ',
                'email' => 'mu2yoshi@gmail.com',
                'email_verified_at' => '2025-03-08 18:03:41',
                'password' => '$2y$12$tEG9NgGPZWqBL.OKh03ZuuW5/Oy6SeLalbKhb6lNOKNjH12PRoM6W',
                'section' => 'secretary',
                'reason' => '帷子町２丁目自治会 副会長',
                'created_at' => '2025-03-08 18:03:42',
                'updated_at' => '2025-03-08 18:06:33',
            ],
            [
                'id' => '5',
                'name' => '舩越 彩香',
                'kana' => 'フナコシ アヤカ',
                'email' => 'ayaka.j8@gmail.com',
                'email_verified_at' => '2025-03-08 18:03:56',
                'password' => '$2y$12$onb4Ruf7bBM1zJTESmNsMOIF9QfQEEHN3pAoOijlVhYHJFyW2Z.5y',
                'section' => 'secretary',
                'reason' => '山道先生からの紹介',
                'created_at' => '2025-03-08 18:03:57',
                'updated_at' => '2025-03-08 19:17:50',
            ],
            [
                'id' => '6',
                'name' => '萩原 繁夫',
                'kana' => 'ハギワラ シゲオ',
                'email' => 'hagikou-sige.29221@docomo.ne.jp',
                'email_verified_at' => '2025-03-08 18:04:10',
                'password' => '$2y$12$I961PcttndDpFjNGHRXOheWbQTjPPEHNE9W8EzbW5WT4y4TL79D2y',
                'section' => 'secretary',
                'reason' => '商店街',
                'created_at' => '2025-03-08 18:04:11',
                'updated_at' => '2025-03-08 18:07:00',
            ],
            [
                'id' => '7',
                'name' => '山道 在明',
                'kana' => 'ヤマミチ アリアキ',
                'email' => 'ikaira.amay@gmail.com',
                'email_verified_at' => '2025-03-08 18:04:25',
                'password' => '$2y$12$4d9j7125N5wS5iZ7sNsBhOJDRQWfseokALLX8alfA2nYKhcLr1jzK',
                'section' => 'secretary',
                'reason' => '保土ケ谷駅西口商店街会員',
                'created_at' => '2025-03-08 18:04:26',
                'updated_at' => '2025-03-08 18:07:15',
            ],
            [
                'id' => '8',
                'name' => '斎藤 寿美江',
                'kana' => 'サイトウ スミエ',
                'email' => 'hmy-su-san@docomo.ne.jp',
                'email_verified_at' => '2025-03-08 18:04:42',
                'password' => '$2y$12$m.Qx45n23twY8VjeeTb.OeFnURLMx7rHDiNQCuTYAYam/I.gUevhi',
                'section' => 'secretary',
                'reason' => '山道先生からスカウトされました♥',
                'created_at' => '2025-03-08 18:04:43',
                'updated_at' => '2025-03-09 01:48:45',
            ],
            [
                'id' => '9',
                'name' => '大久保 安洋',
                'kana' => 'オオクボ ヤスヒロ',
                'email' => 'taketatekaketarakigakidenai@gmail.com',
                'email_verified_at' => '2025-03-08 18:05:21',
                'password' => '$2y$12$bbN8WMEvtBvq3U0VBCDOwee1EHbA7rvo4BYt2kzCjtxnde5IG/syC',
                'section' => 'secretary',
                'reason' => '岡本さんの勧誘',
                'created_at' => '2025-03-08 18:05:22',
                'updated_at' => '2025-03-08 18:07:58',
            ],
            [
                'id' => '10',
                'name' => '堀 祐典',
                'kana' => 'ホリ ユウスケ',
                'email' => 'y.hori@taiyog.com',
                'email_verified_at' => '2025-03-08 18:05:38',
                'password' => '$2y$12$zggMfQz82IIxcWKXXmKyXeopNa4psSTmsl2pq.xv99oR6bWLG9N7a',
                'section' => 'secretary',
                'reason' => ',商店街加盟',
                'created_at' => '2025-03-08 18:05:39',
                'updated_at' => '2025-03-08 18:08:20',
            ],
            [
                'id' => '11',
                'name' => '漆原功',
                'kana' => 'ウルシバラ イサオ',
                'email' => 'urupyon0719@i.softbank.jp',
                'email_verified_at'  => '2025-03-08 19:53:54',
                'password' => '$2y$12$X5HIkLLawutt2uWxYGyLqe9uNrOqPkg6IBka59mi5ebTFjEVReOyO',
                'section' => 'secretary',
                'reason' => '商店街加盟',
                'created_at' => '2025-03-08 19:53:55',
                'updated_at' => '2025-03-08 19:56:45',
            ],
            [
                'id' => '13',
                'name' => '宮永 彰夫',
                'kana' => 'ミヤナガ アキオ',
                'email' => 'aris10teles24@gmail.com',
                'email_verified_at' => '2025-03-29 18:56:09',
                'password' => '$2y$12$CvpmGbARN0XkwZQHSpYYpuxQMu49sXYaRqaJ7HXALpMjkCxQHJ4xu',
                'section' => 'normal',
                'reason' => '誘われて……',
                'created_at' => '2025-03-29 18:56:10',
                'updated_at' => '2025-03-30 15:36:51',
            ],
            [
                'id' => '14',
                'name' => '高林 和幸',
                'kana' => 'タカバヤシ カズユキ',
                'email' => 'oicyan-s43.19681209-keroro1209@docomo.ne.jp',
                'email_verified_at' => '2025-03-29 18:56:43',
                'password' => '$2y$12$QX9Ekg.VCgGwzo1SksqypOw1zENhsQLi4IBvnQQLnSMkZ/1fjv26i',
                'section' => 'normal',
                'reason' => 'I LOVE YOKOHAMAの関連グループのオフ会の際実行委員会への入会をお勧め頂いたので。',
                'created_at' => '2025-03-29 18:56:44',
                'updated_at' => '2025-03-30 15:37:07',
            ],
            [
                'id' => '15',
                'name' => '有泉 哲也',
                'kana' => 'アリイズミ テツヤ',
                'email' => 't28.ariizumi@gmail.com',
                'email_verified_at' => '2025-03-29 18:57:16',
                'password' => '$2y$12$qSMCeXQf3aqvTh0VLaeqwuhj1TZQ8fRH6jPw2XsQhiYs05YMuX3wO',
                'section' => 'normal',
                'reason' => '山形さんの紹介',
                'created_at' => '2025-03-29 18:57:17',
                'updated_at' => '2025-04-26 18:37:43',
            ],
            [
                'id' => '16',
                'name' => '寺井 智之',
                'kana' => 'テライ トモユキ',
                'email' => 'tomo-terai@abeam.ocn.ne.jp',
                'email_verified_at' => '2025-03-29 18:57:46',
                'password' => '$2y$12$Rqcx89ztsORS7X.myYzuMurYoo40IobFNb1UkFRplEJLGvcp5hjhy',
                'section' => 'secretary',
                'reason' => '商店街',
                'created_at' => '2025-03-29 18:57:47',
                'updated_at' => '2025-03-29 18:58:11',
            ],
            [
                'id' => '17',
                'name' => '田島 実',
                'kana' => 'タジマ ミノル',
                'email' => 'alohawauiaoe.michael.2332@gmail.com',
                'email_verified_at' => '2025-03-29 19:20:04',
                'password' => '$2y$12$EhXqluHQatE6odfJ3B.9NewU1GdtTm7qc.M0OAz3IFGo82Spv5pQO',
                'section' => 'normal',
                'reason' => '実行委員長の要望があり',
                'created_at' => '2025-03-29 19:20:05',
                'updated_at' => '2025-03-30 15:37:36',
            ],
            [
                'id' => '18',
                'name' => '山本 能之（ボブ）',
                'kana' => 'ヤマモト ヨシユキ',
                'email' => 'sas.boomans@i.softbank.jp',
                'email_verified_at' => '2025-03-29 19:20:22',
                'password' => '$2y$12$/huwiilehgJ/s2MpJZo7GuY4rLsI8Ercp/4MZa2PHlFhdURorbx9C',
                'section' => 'normal',
                'reason' => '田村さんの紹介',
                'created_at' => '2025-03-29 19:20:23',
                'updated_at' => '2025-03-30 15:37:48',
            ],
            [
                'id' => '19',
                'name' => '木原 繁',
                'kana' => 'キハラ シゲル',
                'email' => 'sige1115@gmail.com',
                'email_verified_at' => '2025-03-29 19:20:47',
                'password' => '$2y$12$KAkQXCC0odUrSLRo0jPttutIvc7gm.tyUeSXewvKZOE8WM6ocUOWm',
                'section' => 'normal',
                'reason' => '友達の紹介',
                'created_at' => '2025-03-29 19:20:48',
                'updated_at' => '2025-03-30 15:38:01',
            ],
            [
                'id' => '20',
                'name' => '九十九澤 ほのか',
                'kana' => 'ツクモサワ ホノカ',
                'email' => 'hononikoboo@gmail.com',
                'email_verified_at' => '2025-03-29 19:21:03',
                'password' => '$2y$12$cntiIv2Tw.iNPx6CIo995eR7y86v2qfPK0I/z0yaxOPNpbKoyfdWS',
                'section' => 'normal',
                'reason' => '斎藤寿美江の娘です！',
                'created_at' => '2025-03-29 19:21:04',
                'updated_at' => '2025-06-28 18:09:58',
            ],
            [
                'id' => '21',
                'name' => '髙野 直子',
                'kana' => 'タカノ ナオコ',
                'email' => 'aki.haru.ka-san@ezweb.ne.jp',
                'email_verified_at' => '2025-03-29 19:21:16',
                'password' => '$2y$12$w.I85T06N7KpHvV4bxkWq.qcW/N2cnWP2fIPyvUQpBCwybOcbzltK',
                'section' => 'normal',
                'reason' => 'NPO法人　ぎんがむらに勤務',
                'created_at' => '2025-03-29 19:21:17',
                'updated_at' => '2025-03-30 15:38:31',
            ],
            [
                'id' => '22',
                'name' => '荘司 稔',
                'kana' => 'ショウジ ミノル',
                'email' => 'mino.sho.4028-ys@ezweb.ne.jp',
                'email_verified_at' => '2025-03-29 19:21:40',
                'password' => '$2y$12$TtpTPcGcSPsQFgWPo3d89.eff8eSq7bTB7TcguKhOqdGHrOtsKpKa',
                'section' => 'normal',
                'reason' => '帷子一丁目自治会にて',
                'created_at' => '2025-03-29 19:21:41',
                'updated_at' => '2025-03-30 15:38:52',
            ],
            [
                'id' => '23',
                'name' => '佐藤 俊二',
                'kana' => 'サトウ シュンジ',
                'email' => 'shun197074@docomo.ne.jp',
                'email_verified_at' => '2025-03-29 19:22:13',
                'password' => '$2y$12$EKX481.r8Km6lOSwhNKGZOLXyH66lg0OQzC/uEiOyKUVYfZ1slQlm',
                'section' => 'normal',
                'reason' => '大久保さんの紹介',
                'created_at' => '2025-03-29 19:22:14',
                'updated_at' => '2025-03-30 15:39:06',
            ],
            [
                'id' => '24',
                'name' => '原 美知子',
                'kana' => 'ハラ ミチコ',
                'email' => 'michiko2951.52@gmail.com',
                'email_verified_at' => '2025-03-29 19:22:32',
                'password' => '$2y$12$VXoZVN1J7px4Jon5w.x8yeKsEsjd2KJYmgRoMcNW5D8rCrKCFsx0q',
                'section' => 'normal',
                'reason' => '帷子町2丁目青年部',
                'created_at' => '2025-03-29 19:22:34',
                'updated_at' => '2025-03-30 15:39:17',
            ],
            [
                'id' => '26',
                'name' => '諏訪間 一希',
                'kana' => 'スワマ カズキ',
                'email' => 'kazuki-12448yurukou@t.vodafone.ne.jp',
                'email_verified_at' => '2025-03-29 19:23:09',
                'password' => '$2y$12$HYQ0EIw4qzwOeV0gMh9CHuAGpWnREoVfRtLZJZr9gm8BEhh0jiDCG',
                'section' => 'normal',
                'reason' => '原さんの紹介',
                'created_at' => '2025-03-29 19:23:11',
                'updated_at' => '2025-03-30 15:41:08',
            ],
            [
                'id' => '27',
                'name' => '原 瞳',
                'kana' => 'ハラ ヒトミ',
                'email' => 'h216t104h33ha@icloud.com',
                'email_verified_at' => '2025-03-29 19:23:28',
                'password' => '$2y$12$kkDPMx9gcACo0qQcOD15G.Uk.QlDKcPpwJoBpifDaiYosvFz3kzQO',
                'section' => 'normal',
                'reason' => '地元',
                'created_at' => '2025-03-29 19:23:29',
                'updated_at' => '2025-03-30 15:39:58',
            ],
            [
                'id' => '28',
                'name' => '斎藤 みずほ',
                'kana' => 'サイトウ ミズホ',
                'email' => 'zumidekokerochan@docomo.ne.jp',
                'email_verified_at' => '2025-03-29 19:23:41',
                'password' => '$2y$12$DupJ/Wt9.vgJ.yuPIHzN3.K42mFH06ylqsKbGDYxGMkNimonI6agC',
                'section' => 'normal',
                'reason' => '元ステージ参加者',
                'created_at' => '2025-03-29 19:23:42',
                'updated_at' => '2025-03-30 15:40:11',
            ],
            [
                'id' => '29',
                'name' => '川瀨 郁子',
                'kana' => 'カワセ イクコ',
                'email' => 'beloved04iku@gmail.com',
                'email_verified_at' => '2025-03-29 19:25:31',
                'password' => '$2y$12$DgGzABfKXaUfhZikqALabuaT4ZEVWaKz6bZniwmXg2j1ycO2Mxv6W',
                'section' => 'normal',
                'reason' => '昔(？)　萩原様などからお手伝いをお願いされて',
                'created_at' => '2025-03-29 19:25:32',
                'updated_at' => '2025-03-30 15:40:27',
            ],
            [
                'id' => '31',
                'name' => '志田 勝信',
                'kana' => 'シダ カツノブ',
                'email' => 'da.sea79@gmail.com',
                'email_verified_at' => '2025-04-26 18:29:48',
                'password' => '$2y$12$Y8/gnS7..Ud4XmLiNMFlX.8ojbnJT.kRkxeuZuBgiYK4NC98chAzq',
                'section' => 'normal',
                'reason' => 'お酒の席で',
                'created_at' => '2025-04-26 18:29:49',
                'updated_at' => '2025-04-29 14:12:19',
            ],
            [
                'id' => '32',
                'name' => '有江 喜一郎',
                'kana' => 'アリエ キイチロウ',
                'email' => 'Kiichiroarie@gmail.com',
                'email_verified_at' => '2025-04-26 18:30:07',
                'password' => '$2y$12$PGdNxXzA15wa.OqtU7.33.WiKGXKiri.m.sEf8dqWQ7NoZQ/tj5SK',
                'section' => 'secretary',
                'reason' => 'やまがたさんからのお誘い',
                'created_at' => '2025-04-26 18:30:09',
                'updated_at' => '2025-04-29 14:12:40',
            ],
            [
                'id' => '34',
                'name' => '田村 信義',
                'kana' => 'タムラ ノブヨシ',
                'email' => 'tomtamura@me.com',
                'email_verified_at' => '2025-04-26 18:30:51',
                'password' => '$2y$12$Buumb1JH4RKApbLOyQzuje08cqXAdFUNuslRd9QgyJLpxvU1qKRea',
                'section' => 'normal',
                'reason' => '山形さん',
                'created_at' => '2025-04-26 18:30:52',
                'updated_at' => '2025-04-29 14:12:59',
            ],
            [
                'id' => '35',
                'name' => '望月 聖子',
                'kana' => 'モチヅキ セイコ',
                'email' => '1120seiko@ezweb.ne.jp',
                'email_verified_at' => '2025-04-26 18:31:07',
                'password' => '$2y$12$dRDtCMniaiK37D.DZV1LgOXMvcp.OdfbPhD6pPlC195qMMbNRI/wC',
                'section' => 'normal',
                'reason' => '皆が集まる宿場まつりを続けていきたいから。',
                'created_at' => '2025-04-26 18:31:08',
                'updated_at' => '2025-04-29 14:13:19',
            ],
            [
                'id' => '36',
                'name' => '太川 莉子',
                'kana' => 'タガワ リコ',
                'email' => 'tagawa.riko.02@gmail.com',
                'email_verified_at' => '2025-04-29 14:36:30',
                'password' => '$2y$12$CcyDvQ.x0.XsaTavEZV2F.GSi3hosisJbyaanRTzOC1LRLw1oE6O2',
                'section' => 'normal',
                'reason' => '近藤さんの紹介・kikcafe',
                'created_at' => '2025-04-29 14:36:31',
                'updated_at' => '2025-04-29 14:36:49',
            ]
        ];

        $this->command->info(count($userData) . " 件のユーザーデータを移行中...");

        foreach ($userData as $row) {
            // 氏名の全角スペースを半角スペースに標準化
            $name = str_replace('　', ' ', $row['name']);
            $name = trim($name);

            // 名義カナを全角カタカナから全角ひらがなへ変換し、スペースを半角に標準化
            $kana = mb_convert_kana($row['kana'], "c", "UTF-8");
            $kana = str_replace('　', ' ', $kana);
            $kana = trim($kana);

            // ロールのマッピング設定 (section => roles)
            $roles = ['general'];
            if ($row['section'] === 'manager') {
                $roles = ['admin', 'general'];
            } elseif ($row['section'] === 'secretary') {
                $roles = ['kanji', 'general'];
            }

            // 本業・職業を一律で「有志」に設定
            $profession = '有志';

            // comittee_users の登録・更新 (email をユニークキーとして UPSERT)
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $name,
                    'name_kana' => $kana,
                    'password' => app()->environment('testing') ? bcrypt('password') : $row['password'], // テスト環境ではハッシュエラー回避のためダミー、通常は引き継ぎ
                    'profession' => $profession,
                    'affiliation' => null,
                    'skills' => null,
                    'roles' => $roles,
                    'referrer_text' => $row['reason'],
                    'line_display_name' => $name, // 初期値として氏名を設定
                    'status' => 'active',
                    'approved_by' => 1, // 初期管理者のID
                    'approved_at' => $row['email_verified_at'] ?? now(),
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );

            // comittee_user_years (2026年度所属) の登録・更新
            UserYear::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'fiscal_year' => 2026
                ],
                [
                    'roles' => $roles,
                    'status' => 'active',
                ]
            );
        }

        $this->command->info("ユーザーデータの移行が完了しました。");
    }
}
