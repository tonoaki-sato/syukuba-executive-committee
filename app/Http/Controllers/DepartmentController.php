<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /**
     * 組織図一覧・管理画面の表示
     */
    public function index(Request $request)
    {
        // リクエストされた年度、またはデフォルトで現在のセッションの年度（無ければ2026）を取得
        $fiscalYear = $request->input('fiscal_year', session('fiscal_year', 2026));

        // ツリー構築のため、最上位部門（parent_id が NULL）を eager loading を用いて取得
        $departments = Department::where('fiscal_year', $fiscalYear)
            ->whereNull('parent_id')
            ->with(['children', 'members.user'])
            ->orderBy('sort_order')
            ->get();

        // メンバー割り当て時の検索用に全ユーザーを取得
        $users = User::orderBy('name_kana')->get();

        // 年度切り替えのドロップダウン用に存在する年度を取得
        $availableYears = Department::select('fiscal_year')
            ->distinct()
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year')
            ->toArray();

        // もし2026年などが存在しない場合に備え、現在の年度を含める
        if (!in_array($fiscalYear, $availableYears)) {
            $availableYears[] = $fiscalYear;
            rsort($availableYears);
        }

        // 部門選択肢（親部門の指定用）: その年度のすべての部門
        $allDepartments = Department::where('fiscal_year', $fiscalYear)
            ->orderBy('sort_order')
            ->get();

        return view('admin.departments.index', compact(
            'departments',
            'users',
            'fiscalYear',
            'availableYears',
            'allDepartments'
        ));
    }

    /**
     * 部門の新規作成
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|integer',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:comittee_departments,id',
            'sort_order' => 'nullable|integer',
        ]);

        // 同一年度内に同じ部門コードが存在しないことをチェック
        $exists = Department::where('fiscal_year', $validated['fiscal_year'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => '指定された部門コードは今年度すでに登録されています。']);
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Department::create($validated);

        return back()->with('success', '部門を作成しました。');
    }

    /**
     * 部門の更新
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'code' => 'sometimes|required|string|max:50',
            'category' => 'sometimes|required|string|max:50',
            'parent_id' => 'nullable|exists:comittee_departments,id',
            'sort_order' => 'sometimes|integer',
        ]);

        // コードの重複チェック（コードが変更された場合のみ）
        if (isset($validated['code']) && $validated['code'] !== $department->code) {
            $exists = Department::where('fiscal_year', $department->fiscal_year)
                ->where('code', $validated['code'])
                ->exists();

            if ($exists) {
                return back()->withErrors(['code' => '指定された部門コードは今年度すでに登録されています。']);
            }
        }

        // 親部門のループ参照防止（自身や自身の子孫を親に設定できないようにする）
        if (isset($validated['parent_id']) && $validated['parent_id'] == $department->id) {
            return back()->withErrors(['parent_id' => '自分自身を親部門に設定することはできません。']);
        }

        $department->update($validated);

        // AJAX/Fetchでの並び替えリクエストの場合
        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', '部門を更新しました。');
    }

    /**
     * 部門の削除
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return back()->with('success', '部門を削除しました。');
    }

    /**
     * 前年度の組織図データをコピー
     */
    public function copyFromPreviousYear(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|integer',
        ]);

        $fiscalYear = (int)$request->input('fiscal_year');
        $prevYear = $fiscalYear - 1;

        // すでにデータが存在する場合はエラーとするか、または上書きするか。
        // ここでは安全のため、対象年度にすでに部門データが存在する場合はエラーとする。
        $exists = Department::where('fiscal_year', $fiscalYear)->exists();
        if ($exists) {
            return back()->withErrors(['copy' => "すでに{$fiscalYear}年度の組織図データが存在するため、コピーできません。一度既存の部門をすべて削除してください。"]);
        }

        $prevDepartments = Department::where('fiscal_year', $prevYear)->get();

        if ($prevDepartments->isEmpty()) {
            return back()->withErrors(['copy' => "前年度（{$prevYear}年度）の組織図データが見つかりませんでした。"]);
        }

        DB::transaction(function () use ($prevDepartments, $fiscalYear) {
            $mapping = []; // [old_id => new_id]

            // 1. 部門データのコピー（parent_id は一旦 NULL で登録）
            foreach ($prevDepartments as $oldDept) {
                $newDept = Department::create([
                    'fiscal_year' => $fiscalYear,
                    'code' => $oldDept->code,
                    'name' => $oldDept->name,
                    'category' => $oldDept->category,
                    'parent_id' => null,
                    'sort_order' => $oldDept->sort_order,
                ]);
                $mapping[$oldDept->id] = $newDept->id;
            }

            // 2. parent_id の紐付け更新
            foreach ($prevDepartments as $oldDept) {
                if ($oldDept->parent_id && isset($mapping[$oldDept->parent_id])) {
                    Department::where('id', $mapping[$oldDept->id])->update([
                        'parent_id' => $mapping[$oldDept->parent_id],
                    ]);
                }
            }

            // 3. メンバーデータのコピー
            foreach ($prevDepartments as $oldDept) {
                $newDeptId = $mapping[$oldDept->id] ?? null;
                if (!$newDeptId) {
                    continue;
                }

                $oldMembers = DB::table('comittee_department_members')
                    ->where('department_id', $oldDept->id)
                    ->get();

                foreach ($oldMembers as $oldMember) {
                    DB::table('comittee_department_members')->insert([
                        'department_id' => $newDeptId,
                        'user_id' => $oldMember->user_id,
                        'custom_name' => $oldMember->custom_name,
                        'role_name' => $oldMember->role_name,
                        'is_leader' => $oldMember->is_leader,
                        'sort_order' => $oldMember->sort_order,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return back()->with('success', "前年度（{$prevYear}年度）から組織図データとメンバーをコピーしました。");
    }
}
