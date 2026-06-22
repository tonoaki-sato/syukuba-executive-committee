<?php

namespace App\Http\Controllers;

use App\Models\GozaichiEvent;
use App\Models\GozaichiApplication;
use Illuminate\Http\Request;

class GozaichiController extends Controller
{
    protected function getOrCreateActiveEvent()
    {
        $activeYear = session('active_fiscal_year', date('Y'));
        $event = GozaichiEvent::where('fiscal_year', $activeYear)->first();
        
        if (!$event) {
            $event = GozaichiEvent::create([
                'fiscal_year' => $activeYear,
                'recruitment_status' => 'closed',
                'is_active' => true,
            ]);
            
            $defaults = [
                'member_1st' => 2000,
                'member_general_2nd' => 3000,
                'member_A_2nd' => 4000,
                'member_B_2nd' => 5000,
                'general_1st' => 6000,
                'general_A_1st' => 8000,
                'general_B_1st' => 10000,
                'general_2nd' => 6000,
                'general_A_2nd' => 8000,
                'general_B_2nd' => 10000,
                'tent' => 4500,
                'weight' => 500,
                'desk' => 2500,
                'chair' => 500,
                'trash_45' => 500,
                'trash_70' => 700,
            ];
            
            foreach ($defaults as $key => $val) {
                $event->feeSettings()->create([
                    'fee_key' => $key,
                    'fee_value' => $val,
                ]);
            }
        }
        
        return $event;
    }

    public function index()
    {
        $event = $this->getOrCreateActiveEvent();
        
        $applicationsCount = $event->applications()->count();
        $acceptedCount = $event->applications()->where('status', 'accepted')->count();
        $paidCount = $event->applications()->where('status', 'accepted')->where('is_paid', true)->count();
        
        $recentApplications = $event->applications()
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
            
        return view('goza.dashboard', compact(
            'event',
            'applicationsCount',
            'acceptedCount',
            'paidCount',
            'recentApplications'
        ));
    }
}
