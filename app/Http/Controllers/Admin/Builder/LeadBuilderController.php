<?php

namespace App\Http\Controllers\Admin\Builder;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadBuilderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Builder/Leads', [
            'items' => Lead::query()
                ->with('designSession.environment')
                ->latest()
                ->get(),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:new,contacted,quoted,won,lost'],
            'message' => ['nullable', 'string'],
        ]);

        $lead->update($data);

        return back()->with('success', 'Lead actualizado correctamente.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return back()->with('success', 'Lead eliminado correctamente.');
    }
}
