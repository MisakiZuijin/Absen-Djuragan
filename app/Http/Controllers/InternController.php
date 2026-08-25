<?php

namespace App\Http\Controllers;

use App\Services\InternService;
use App\Http\Requests\EditInternRequest;
use Illuminate\Http\Request;
use App\Models\HandRaise;
use App\Models\PrayerRequest;
use Illuminate\Support\Facades\Auth;

class InternController extends Controller
{
    protected $internService;

    public function __construct(InternService $internService)
    {
        $this->internService = $internService;
    }

    public function adminUpdateInternAction(EditInternRequest $editInternRequest)
    {
        $result = $this->internService->update($editInternRequest);

        if ($result->isSuccess()) {
            return redirect()->back()->with('success', 'Data intern berhasil diperbarui.');
        }

        return redirect()->back()->with('error', $result->getMessage());
    }

    // Di InternController.php
    public function raiseHandToggle()
    {
        $user = auth()->user();
        
        $handRaise = HandRaise::firstOrCreate(
            ['user_id' => $user->id],
            ['is_raised' => false, 'project_id' => null]
        );
        
        // Toggle status
        $handRaise->is_raised = !$handRaise->is_raised;
        $handRaise->save();
        
        \Log::info('Raise hand toggled', [
            'user_id' => $user->id,
            'is_raised' => $handRaise->is_raised
        ]);
        
        $status = $handRaise->is_raised ? 'diangkat' : 'diturunkan';
        
        return redirect()->back()->with('success', "Tangan berhasil {$status}!");
    }

}
